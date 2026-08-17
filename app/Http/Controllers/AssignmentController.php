<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Assignment;
use App\Models\BreakdownRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    /**
     * Step 2: IT Head reviews a New request and assigns it to an Assign Officer.
     */
    public function store(Request $request, BreakdownRequest $breakdownRequest)
    {
        abort_unless(Auth::user()->isItHead(), 403);

        $data = $request->validate([
            'assign_officer_id' => ['required', 'exists:users,id'],
            'note' => ['nullable', 'string'],
        ]);

        $assignment = Assignment::create([
            'request_id' => $breakdownRequest->id,
            'assigned_by' => Auth::id(),
            'assign_officer_id' => $data['assign_officer_id'],
            'note' => $data['note'] ?? null,
            'assigned_at' => now(),
            'status' => 'Pending',
        ]);

        $breakdownRequest->update(['status' => 'Assigned']);

        ActivityLog::log(
            $breakdownRequest->id,
            Auth::id(),
            'Assigned to Assign Officer',
            $assignment->assignOfficer->name ?? null
        );

        return back()->with('success', 'Request assigned to Assign Officer.');
    }
}
