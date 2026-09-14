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

        $breakdownRequest =
            $workReport
                ->officerAssignment
                ->assignment
                ->request;


        /*
        |--------------------------------------------------------------------------
        | Authorization
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $user->isMinistryUser()
            && $breakdownRequest->requested_by === $user->id,
            403
        );


        /*
        |--------------------------------------------------------------------------
        | Validate Department Confirmation
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([

            'is_resolved' => [
                'required',
                'in:Yes,No',
            ],

            'feedback' => [
                'nullable',
                'string',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | Save Confirmation
        |--------------------------------------------------------------------------
        */

        DepartmentConfirmation::updateOrCreate(

            [
                'work_report_id' =>
                    $workReport->id,
            ],

            [
                'confirmed_by' =>
                    $user->id,

                'is_resolved' =>
                    $data['is_resolved'],

                'feedback' =>
                    $data['feedback'] ?? null,

                'confirmed_at' =>
                    now(),
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Department User Confirms Resolved
        |--------------------------------------------------------------------------
        */

        if ($data['is_resolved'] === 'Yes') {

            $breakdownRequest->update([
                'status' => 'Closed',
                'assigned_to' => null,
            ]);


            ActivityLog::log(
                $breakdownRequest->id,
                $user->id,
                'Verified & closed request',
                $data['feedback'] ?? null
            );


            return back()->with(
                'success',
                'Feedback submitted. Request closed successfully.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Department User Says NOT RESOLVED
        |--------------------------------------------------------------------------
        |
        | Do NOT escalate directly.
        |
        | Send the request back to Assign Officer exactly like
        | Technician "Not Done".
        |
        */

        $breakdownRequest->update([

            'status' =>
                'Pending Reassignment',

            'assigned_to' =>
                null,

        ]);


        /*
        |--------------------------------------------------------------------------
        | Preserve Previous Technician Result
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | The technician already submitted a "Done" work report.
        | Do NOT change that previous officer assignment back to Pending.
        |
        | Keep it as Done so the history remains accurate.
        |
        | When Assign Officer reassigns the request, a NEW
        | OfficerAssignment will be created for the next technician.
        |
        */


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLog::log(
            $breakdownRequest->id,
            $user->id,
            'Department reported issue not resolved',
            $data['feedback']
                ?? 'Request sent for reassignment.'
        );


        return redirect()
            ->route('dashboard')
            ->with(
                'success',
                'Feedback submitted. Request sent to the Assign Officer for reassignment or IT Administrator review.'
            );
    }
}
