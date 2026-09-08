<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Assignment;
use App\Models\BreakdownRequest;
use App\Models\OfficerAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficerAssignmentController extends Controller
{
    /**
     * Assign Officer directly assigns a New/Reopened request
     * to a Technical Officer.
     */
    public function store(Request $request, BreakdownRequest $breakdownRequest)
    {
        $user = Auth::user();

        abort_unless($user->isAssignOfficer(), 403);

        abort_unless(
            in_array(
                $breakdownRequest->status,
                [
                    'New',
                    'Reopened',
                    'Pending Reassignment',
                ],
                true
            ),
            400,
            'This request cannot be assigned.'
        );

        $data = $request->validate([
            'technical_officer_id' => ['required', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        /*
         * Create the internal Assignment record automatically.
         *
         * The Assign Officer is now both:
         * - the person receiving/managing the request
         * - the person assigning the technician
         */
        $assignment = Assignment::create([
            'request_id' => $breakdownRequest->id,

            // There is no IT Head assignment anymore,
            // therefore store the current Assign Officer.
            'assigned_by' => $user->id,

            'assign_officer_id' => $user->id,
            'note' => $data['note'] ?? null,
            'assigned_at' => now(),
            'status' => 'Forwarded',
        ]);

        $officerAssignment = OfficerAssignment::create([
            'assignment_id' => $assignment->id,
            'technical_officer_id' => $data['technical_officer_id'],
            'assigned_by' => $user->id,
            'due_date' => $data['due_date'] ?? null,
            'note' => $data['note'] ?? null,
            'assigned_at' => now(),
            'status' => 'Pending',
        ]);

        $breakdownRequest->update([
            'assigned_to' => $data['technical_officer_id'],
            'status' => 'Assigned',
        ]);

        ActivityLog::log(
            $breakdownRequest->id,
            $user->id,
            'Assigned to Technical Officer',
            $officerAssignment->technicalOfficer->name ?? null
        );

        return back()->with(
            'success',
            'Request assigned to Technical Officer successfully.'
        );
    }
}