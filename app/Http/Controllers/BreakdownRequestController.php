<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\BreakdownRequest;
use App\Models\Category;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BreakdownRequestController extends Controller
{
    /**
     * All Requests view - visible to IT Head, Assign Officer (their scope), Technical Officer (their jobs).
     * Ministry Users see only their own via "My Requests".
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = BreakdownRequest::with(['department', 'requestedBy', 'category', 'assignedTo']);

        if ($user->isMinistryUser()) {
            $query->where('requested_by', $user->id);
        } elseif ($user->isTechnicalOfficer()) {
            $query->where('assigned_to', $user->id);
        } elseif ($user->isAssignOfficer()) {
            $assignmentRequestIds = $user->assignmentsAsOfficer()->pluck('request_id');
            $query->whereIn('id', $assignmentRequestIds);
        }
        // IT Head sees everything.

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('request_number', 'like', "%{$q}%")
                  ->orWhere('title', 'like', "%{$q}%");
            });
        }

        $requests = $query->latest()->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('requests.index', compact('requests', 'departments'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        $ministries = Department::where('is_active', true)
            ->whereNotNull('ministry_name')
            ->where('ministry_name', '!=', '')
            ->select('ministry_name')
            ->distinct()
            ->orderBy('ministry_name')
            ->pluck('ministry_name');

        $departments = Department::where('is_active', true)
            ->whereNotNull('ministry_name')
            ->orderBy('name')
            ->get(['id', 'name', 'ministry_name']);

        return view(
            'requests.create',
            compact('categories', 'ministries', 'departments')
        );
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'ministry_name' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'attachments.*' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $breakdown = BreakdownRequest::create([
            'request_number' => $this->generateRequestNumber(),
            'department_id' => $data['department_id'],
            'requested_by' => $user->id,
            'category_id' => $data['category_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => 'New',
            'received_at' => now(),
        ]);

        $this->storeAttachments($request, $breakdown, $user);

        ActivityLog::log($breakdown->id, $user->id, 'Submitted request', $breakdown->title);

        return redirect()->route('requests.show', $breakdown)
            ->with('success', "Request {$breakdown->request_number} submitted successfully.");
    }

    public function show(BreakdownRequest $breakdownRequest)
    {
        $user = Auth::user();
        $this->authorizeView($user, $breakdownRequest);

        $breakdownRequest->load([
            'department', 'requestedBy', 'category', 'assignedTo', 'attachments',
            'assignments.assignedBy', 'assignments.assignOfficer',
            'assignments.officerAssignments.technicalOfficer',
            'assignments.officerAssignments.workReport.confirmation',
            'assignments.officerAssignments.workReport.attachments',
            'activityLogs.user',
        ]);

        $assignOfficers = User::whereHas('role', fn ($r) => $r->where('code', 'assign_officer'))
            ->where('is_active', true)->get();

        $technicalOfficers = User::whereHas('role', fn ($r) => $r->where('code', 'technical_officer'))
            ->where('is_active', true)->get();

        return view('requests.show', compact('breakdownRequest', 'assignOfficers', 'technicalOfficers'));
    }

    protected function authorizeView(User $user, BreakdownRequest $breakdownRequest): void
    {
        if ($user->isItHead()) return;
        if ($user->isMinistryUser() && $breakdownRequest->requested_by === $user->id) return;
        if ($user->isTechnicalOfficer() && $breakdownRequest->assigned_to === $user->id) return;
        if ($user->isAssignOfficer() && $breakdownRequest->assignments()->where('assign_officer_id', $user->id)->exists()) return;

        abort(403, 'You do not have access to this request.');
    }

    protected function generateRequestNumber(): string
    {
        $year = now()->year;
        $count = BreakdownRequest::whereYear('created_at', $year)->count() + 1;
        return sprintf('BRK-%d-%04d', $year, $count);
    }

    protected function storeAttachments(Request $request, BreakdownRequest $breakdown, User $user): void
    {
        if (! $request->hasFile('attachments')) return;

        foreach ($request->file('attachments') as $file) {
            $path = $file->store('attachments/requests/' . $breakdown->id, 'public');

            Attachment::create([
                'attachable_id' => $breakdown->id,
                'attachable_type' => BreakdownRequest::class,
                'uploaded_by' => $user->id,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }
}
