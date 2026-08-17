<?php

namespace App\Http\Controllers;

use App\Models\BreakdownRequest;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return match ($user->role->code) {
            'it_head' => $this->itHeadDashboard(),
            'assign_officer' => $this->assignOfficerDashboard($user),
            'technical_officer' => $this->technicalOfficerDashboard($user),
            'ministry_user' => $this->ministryUserDashboard($user),
            default => abort(403),
        };
    }

    protected function itHeadDashboard()
    {
        $counts = [
            'new' => BreakdownRequest::where('status', 'New')->count(),
            'assigned' => BreakdownRequest::where('status', 'Assigned')->count(),
            'in_progress' => BreakdownRequest::where('status', 'In Progress')->count(),
            'resolved' => BreakdownRequest::where('status', 'Resolved')->count(),
            'closed' => BreakdownRequest::where('status', 'Closed')->count(),
            'reopened' => BreakdownRequest::where('status', 'Reopened')->count(),
        ];

        $recent = BreakdownRequest::with(['department', 'assignedTo'])
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard.it_head', compact('counts', 'recent'));
    }

    protected function assignOfficerDashboard($user)
    {
        $assignmentIds = $user->assignmentsAsOfficer()->pluck('id');

        $counts = [
            'pending' => \App\Models\Assignment::where('assign_officer_id', $user->id)->where('status', 'Pending')->count(),
            'in_progress' => \App\Models\OfficerAssignment::whereIn('assignment_id', $assignmentIds)->where('status', 'In Progress')->count(),
            'completed' => \App\Models\OfficerAssignment::whereIn('assignment_id', $assignmentIds)->where('status', 'Done')->count(),
            'overdue' => \App\Models\OfficerAssignment::whereIn('assignment_id', $assignmentIds)
                ->where('status', '!=', 'Done')
                ->whereDate('due_date', '<', now())
                ->count(),
        ];

        $myAssignments = \App\Models\Assignment::with(['request.department', 'latestOfficerAssignment.technicalOfficer'])
            ->where('assign_officer_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard.assign_officer', compact('counts', 'myAssignments'));
    }

    protected function technicalOfficerDashboard($user)
    {
        $jobs = \App\Models\OfficerAssignment::with(['assignment.request.department'])
            ->where('technical_officer_id', $user->id);

        $counts = [
            'my_jobs' => (clone $jobs)->count(),
            'in_progress' => (clone $jobs)->where('status', 'In Progress')->count(),
            'completed' => (clone $jobs)->where('status', 'Done')->count(),
            'overdue' => (clone $jobs)->where('status', '!=', 'Done')->whereDate('due_date', '<', now())->count(),
        ];

        $myJobs = $jobs->latest()->limit(10)->get();

        return view('dashboard.technical_officer', compact('counts', 'myJobs'));
    }

    protected function ministryUserDashboard($user)
    {
        $base = BreakdownRequest::where('requested_by', $user->id);

        $counts = [
            'my_requests' => (clone $base)->count(),
            'open' => (clone $base)->whereIn('status', ['New', 'Assigned', 'In Progress'])->count(),
            'resolved' => (clone $base)->where('status', 'Resolved')->count(),
            'closed' => (clone $base)->where('status', 'Closed')->count(),
        ];

        $myRequests = (clone $base)->latest()->limit(10)->get();

        return view('dashboard.ministry_user', compact('counts', 'myRequests'));
    }
}
