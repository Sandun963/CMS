<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BreakdownRequest;
use App\Models\Escalation;
use Barryvdh\DomPDF\Facade\Pdf;
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

        $this->loadCompleteEscalationData(
            $escalation
        );

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
        | Map Admin Decision -> Request Status
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

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLog::log(
            $breakdownRequest->id,
            $user->id,
            $data['admin_decision'],
            $data['admin_remarks']
        );

        /*
        |--------------------------------------------------------------------------
        | Return to same escalation
        |--------------------------------------------------------------------------
        |
        | Important:
        | We return to the escalation details page instead of the list.
        | The user can immediately see the saved decision and the
        | Formal PDF Report button.
        |
        */

        return redirect()
            ->route(
                'escalations.show',
                $escalation
            )
            ->with(
                'success',
                'IT Administrator decision saved successfully. The formal report is now available.'
            );
    }


    /**
     * Generate the formal escalation report as PDF.
     *
     * The report can only be generated after the
     * IT Administrator has submitted the final decision.
     */
    public function exportFormalReport(
        Escalation $escalation
    ) {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Authorization
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $user->isItAdministrator(),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Decision must be completed first
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $escalation->status === 'Reviewed'
            && !empty($escalation->admin_decision)
            && !empty($escalation->reviewed_at),
            403,
            'The formal report is available only after the IT Administrator decision has been submitted.'
        );

        /*
        |--------------------------------------------------------------------------
        | Load Complete Complaint / Escalation Data
        |--------------------------------------------------------------------------
        */

        $this->loadCompleteEscalationData(
            $escalation
        );

        /*
        |--------------------------------------------------------------------------
        | Generate PDF
        |--------------------------------------------------------------------------
        */

        $pdf = Pdf::loadView(
            'escalations.report-pdf',
            compact('escalation')
        )
            ->setPaper(
                'a4',
                'portrait'
            );

        /*
        |--------------------------------------------------------------------------
        | Safe File Name
        |--------------------------------------------------------------------------
        */

        $requestNumber =
            $escalation
                ->breakdownRequest
                ->request_number
                ?? 'request';

        $safeRequestNumber =
            str_replace(
                ['/', '\\', ' '],
                '-',
                $requestNumber
            );

        return $pdf->download(
            'Complaint_Report_' .
            $safeRequestNumber .
            '.pdf'
        );
    }


    /**
     * Load all information required by:
     *
     * - Escalated case details page
     * - Formal PDF report
     *
     * Keeping this in one method prevents the HTML page
     * and PDF report from loading different information.
     */
    private function loadCompleteEscalationData(
        Escalation $escalation
    ): void {
        $escalation->load([

            /*
            |--------------------------------------------------------------------------
            | Request
            |--------------------------------------------------------------------------
            */

            'breakdownRequest.floor',
            'breakdownRequest.division',
            'breakdownRequest.areaLocation',
            'breakdownRequest.category',
            'breakdownRequest.requestedBy',
            'breakdownRequest.attachments',

            /*
            |--------------------------------------------------------------------------
            | Complete Assignment Chain
            |--------------------------------------------------------------------------
            */

            'breakdownRequest.assignments.assignedBy',
            'breakdownRequest.assignments.assignOfficer',

            'breakdownRequest.assignments.officerAssignments.technicalOfficer',
            'breakdownRequest.assignments.officerAssignments.assignedBy',

            /*
            |--------------------------------------------------------------------------
            | Technician Reports + Confirmation
            |--------------------------------------------------------------------------
            */

            'breakdownRequest.assignments.officerAssignments.workReport.attachments',

            'breakdownRequest.assignments.officerAssignments.workReport.confirmation.confirmedBy',

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            'breakdownRequest.activityLogs.user',

            /*
            |--------------------------------------------------------------------------
            | Escalation
            |--------------------------------------------------------------------------
            */

            'forwardedBy',
            'reviewedBy',
        ]);
    }
}