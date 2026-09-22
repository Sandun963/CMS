<?php

namespace App\Http\Controllers;

use App\Models\BreakdownRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return match ($user->role->code) {
            'sup_admin' => $this->superAdminDashboard(),
            'administrator' => $this->administratorDashboard(),
            'assign_officer' => $this->assignOfficerDashboard($user),
            'technical_officer' => $this->technicalOfficerDashboard($user),
            'ministry_user' => $this->ministryUserDashboard($user),
            default => abort(403),
        };
    }

    protected function superAdminDashboard()
    {
        $userCounts = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
        ];

        $requestCounts = [
            'total' => BreakdownRequest::count(),
            'new' => BreakdownRequest::where('status', 'New')->count(),
            'in_progress' => BreakdownRequest::where('status', 'In Progress')->count(),
            'resolved' => BreakdownRequest::where('status', 'Resolved')->count(),
            'closed' => BreakdownRequest::where('status', 'Closed')->count(),
        ];

        $recentRequests = BreakdownRequest::with([
            'assignedTo',
            'floor',
            'division',
            'areaLocation'
        ])
            ->latest()
            ->limit(8)
            ->get();

        $recentUsers = User::with('role')
            ->latest()
            ->limit(5)
            ->get();

        return view(
            'dashboard.super_admin',
            compact(
                'userCounts',
                'requestCounts',
                'recentRequests',
                'recentUsers'
            )
        );
    }


    protected function administratorDashboard()
    {
        $counts = [
            'new' => BreakdownRequest::where('status', 'New')->count(),
            'assigned' => BreakdownRequest::where('status', 'Assigned')->count(),
            'in_progress' => BreakdownRequest::where('status', 'In Progress')->count(),
            'resolved' => BreakdownRequest::where('status', 'Resolved')->count(),
            'closed' => BreakdownRequest::where('status', 'Closed')->count(),
            'reopened' => BreakdownRequest::where('status', 'Reopened')->count(),
        ];

        $recent = BreakdownRequest::with([
            'department',
            'assignedTo',

            // Keep historical technician assignments available
            // even after the request has been closed.
            'assignments.officerAssignments.technicalOfficer',
        ])
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard.administrator', compact('counts', 'recent'));
    }

    protected function assignOfficerDashboard($user)
    {
        $newRequests = BreakdownRequest::with('division')
            ->whereIn('status', [
                'New',
                'Reopened',
                'Pending Reassignment'
            ])
            ->latest();

        $assignmentIds = $user->assignmentsAsOfficer()->pluck('id');

        $counts = [
            'pending' => (clone $newRequests)->count(),

            'in_progress' => \App\Models\OfficerAssignment::whereIn(
                'assignment_id',
                $assignmentIds
            )
            ->where('status', 'In Progress')
            ->count(),

            'completed' => \App\Models\OfficerAssignment::whereIn(
                'assignment_id',
                $assignmentIds
            )
            ->where('status', 'Done')
            ->count(),

            'overdue' => \App\Models\OfficerAssignment::whereIn(
                'assignment_id',
                $assignmentIds
            )
            ->whereIn('status',['Pending','In Progress',])
            ->whereDate('due_date', '<', now())
            ->count(),
        ];

        $pendingRequests = $newRequests
            ->limit(10)
            ->get();

        $myAssignments = \App\Models\Assignment::with([
            'request.division',
            'latestOfficerAssignment.technicalOfficer'
        ])
            ->where('assign_officer_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();

        return view(
            'dashboard.assign_officer',
            compact(
                'counts',
                'pendingRequests',
                'myAssignments'
            )
        );
    }

    protected function technicalOfficerDashboard($user)
    {
        $jobs = \App\Models\OfficerAssignment::with([
            'assignment.request.division'
        ])
            ->where('technical_officer_id', $user->id);

        $counts = [
            'my_jobs' => (clone $jobs)->count(),
            'in_progress' => (clone $jobs)->where('status', 'In Progress')->count(),
            'completed' => (clone $jobs)->where('status', 'Done')->count(),
            'overdue' => (clone $jobs)->whereIn('status', ['Pending','In Progress',])->whereDate('due_date', '<', now())->count(),];

        $myJobs = $jobs->latest()->limit(10)->get();

        return view('dashboard.technical_officer', compact('counts', 'myJobs'));
    }

    protected function ministryUserDashboard($user)
    {
        $base = BreakdownRequest::where('requested_by', $user->id);

        $counts = [
            'my_requests' => (clone $base)->count(),
            'open' => (clone $base) ->whereIn('status', ['New','Assigned','In Progress','Pending Reassignment'])->count(),
            'resolved' => (clone $base)->where('status', 'Resolved')->count(),
            'closed' => (clone $base)->where('status', 'Closed')->count(),
        ];

        $myRequests = (clone $base)->latest()->limit(10)->get();

        return view('dashboard.ministry_user', compact('counts', 'myRequests'));
    }
}
