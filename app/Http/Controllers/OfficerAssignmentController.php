<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Assignment;
use App\Models\OfficerAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficerAssignmentController extends Controller
{
    /**
     * Step 3: Assign Officer reviews and forwards to a specific Technical Officer.
     */
    public function store(Request $request, Assignment $assignment)
    {
        $user = Auth::user();
        abort_unless($user->isAssignOfficer() && $assignment->assign_officer_id === $user->id, 403);

        $data = $request->validate([
            'technical_officer_id' => ['required', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
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

        $assignment->update(['status' => 'Forwarded']);

        $breakdownRequest = $assignment->request;
        $breakdownRequest->update(['assigned_to' => $data['technical_officer_id']]);

        ActivityLog::log(
            $breakdownRequest->id,
            $user->id,
            'Forwarded to Technical Officer',
            $officerAssignment->technicalOfficer->name ?? null
        );

        return back()->with('success', 'Job forwarded to Technical Officer.');
    }
}
