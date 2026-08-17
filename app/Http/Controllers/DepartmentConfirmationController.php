<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DepartmentConfirmation;
use App\Models\WorkReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentConfirmationController extends Controller
{
    /**
     * Step 5: Ministry User verifies the fix and closes (or reopens) the request.
     */
    public function store(Request $request, WorkReport $workReport)
    {
        $user = Auth::user();
        $breakdownRequest = $workReport->officerAssignment->assignment->request;

        abort_unless($user->isMinistryUser() && $breakdownRequest->requested_by === $user->id, 403);

        $data = $request->validate([
            'is_resolved' => ['required', 'in:Yes,No'],
            'feedback' => ['nullable', 'string'],
        ]);

        DepartmentConfirmation::updateOrCreate(
            ['work_report_id' => $workReport->id],
            [
                'confirmed_by' => $user->id,
                'is_resolved' => $data['is_resolved'],
                'feedback' => $data['feedback'] ?? null,
                'confirmed_at' => now(),
            ]
        );

        if ($data['is_resolved'] === 'Yes') {
            $breakdownRequest->update(['status' => 'Closed']);
            ActivityLog::log($breakdownRequest->id, $user->id, 'Verified & closed request', $data['feedback'] ?? null);
        } else {
            $breakdownRequest->update(['status' => 'Reopened']);
            $workReport->officerAssignment->update(['status' => 'Pending']);
            ActivityLog::log($breakdownRequest->id, $user->id, 'Reopened request', $data['feedback'] ?? null);
        }

        return back()->with('success', 'Feedback submitted.');
    }
}
