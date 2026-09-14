<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BreakdownRequest;
use App\Models\Escalation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EscalationController extends Controller
{
    /**
     * Assign Officer forwards an unresolved request
     * to the IT Administrator.
     */
    public function forward(
        Request $request,
        BreakdownRequest $breakdownRequest
    ) {
        $user = Auth::user();

        abort_unless(
            $user->isAssignOfficer(),
            403
        );

        abort_unless(
            $breakdownRequest->status ===
                'Pending Reassignment',
            400,
            'Only pending reassignment requests can be forwarded.'
        );

        $data = $request->validate([
            'reason' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Pending Escalations
        |--------------------------------------------------------------------------
        */

        $alreadyEscalated =
            Escalation::where(
                'breakdown_request_id',
                $breakdownRequest->id
            )
            ->where(
                'status',
                'Pending Review'
            )
            ->exists();

        if ($alreadyEscalated) {

            return back()->with(
                'error',
                'This request has already been forwarded to the IT Administrator.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Create Escalation Record
        |--------------------------------------------------------------------------
        */

        Escalation::create([
            'breakdown_request_id' =>
                $breakdownRequest->id,

            'forwarded_by' =>
                $user->id,

            'trigger_type' =>
                'Pending Reassignment Escalation',

            'reason' =>
                $data['reason'],

            'status' =>
                'Pending Review',

            'forwarded_at' =>
                now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Request remains actively handled
        |--------------------------------------------------------------------------
        */

        $breakdownRequest->update([
            'status' => 'In Progress',
            'assigned_to' => null,
        ]);

        ActivityLog::log(
            $breakdownRequest->id,
            $user->id,
            'Forwarded to IT Administrator',
            $data['reason']
        );

        return redirect()
            ->route('requests.index')
            ->with(
                'success',
                'Request forwarded to IT Administrator successfully.'
            );
    }


    /**
     * IT Administrator - Escalated Cases list.
     */
    public function index()
    {
        $user = Auth::user();

        abort_unless(
            $user->isItAdministrator(),
            403
        );

        $escalations = Escalation::with([
            'breakdownRequest.floor',
            'breakdownRequest.division',
            'breakdownRequest.areaLocation',
            'breakdownRequest.category',
            'forwardedBy',
            'reviewedBy',
        ])
            ->latest('forwarded_at')
            ->get();

        return view(
            'escalations.index',
            compact('escalations')
        );
    }


    /**
     * IT Administrator - View one escalated case.
     */
    public function show(Escalation $escalation)
    {
        $user = Auth::user();

        abort_unless(
            $user->isItAdministrator(),
            403
        );

        $escalation->load([

            // Request
            'breakdownRequest.floor',
            'breakdownRequest.division',
            'breakdownRequest.areaLocation',
            'breakdownRequest.category',
            'breakdownRequest.requestedBy',
            'breakdownRequest.attachments',

            // Complete assignment chain
            'breakdownRequest.assignments.assignedBy',
            'breakdownRequest.assignments.assignOfficer',

            'breakdownRequest.assignments.officerAssignments.technicalOfficer',
            'breakdownRequest.assignments.officerAssignments.assignedBy',

            // All technician reports
            'breakdownRequest.assignments.officerAssignments.workReport.attachments',
            'breakdownRequest.assignments.officerAssignments.workReport.confirmation.confirmedBy',

            // Activity log
            'breakdownRequest.activityLogs.user',

            // Escalation
            'forwardedBy',
            'reviewedBy',
        ]);

        return view(
            'escalations.show',
            compact('escalation')
        );
    }


    /**
     * IT Administrator submits final decision.
     */
    public function decide(
        Request $request,
        Escalation $escalation
    ) {
        $user = Auth::user();

        abort_unless(
            $user->isItAdministrator(),
            403
        );

        abort_if(
            $escalation->status === 'Reviewed',
            400,
            'This escalation has already been reviewed.'
        );

        $data = $request->validate([
            'admin_decision' => [
                'required',
                'in:Outsource Recommended,Closed - Unable to Resolve',
            ],

            'admin_remarks' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        $breakdownRequest =
            $escalation->breakdownRequest;

        /*
        |--------------------------------------------------------------------------
        | Map Admin Decision → Request Status
        |--------------------------------------------------------------------------
        */

        $requestStatus =
            $data['admin_decision']
                === 'Outsource Recommended'
                ? 'Outsource Required'
                : 'Closed';

        /*
        |--------------------------------------------------------------------------
        | Update Escalation
        |--------------------------------------------------------------------------
        */

        $escalation->update([
            'status' =>
                'Reviewed',

            'admin_decision' =>
                $data['admin_decision'],

            'admin_remarks' =>
                $data['admin_remarks'],

            'reviewed_by' =>
                $user->id,

            'reviewed_at' =>
                now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Update Breakdown Request
        |--------------------------------------------------------------------------
        */

        $breakdownRequest->update([
            'status' =>
                $requestStatus,

            'assigned_to' =>
                null,
        ]);

        ActivityLog::log(
            $breakdownRequest->id,
            $user->id,
            $data['admin_decision'],
            $data['admin_remarks']
        );

        return redirect()
            ->route('escalations.index')
            ->with(
                'success',
                'IT Administrator decision saved successfully.'
            );
    }
}