<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\OfficerAssignment;
use App\Models\WorkReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkReportController extends Controller
{
    /**
     * Technical Officer starts working on an assigned job.
     */
    public function startWork(OfficerAssignment $officerAssignment)
    {
        $user = Auth::user();

        abort_unless(
            $user->isTechnicalOfficer()
            && $officerAssignment->technical_officer_id === $user->id,
            403
        );

        if ($officerAssignment->status !== 'Pending') {
            return back()->with(
                'error',
                'This job has already been started.'
            );
        }

        $officerAssignment->update([
            'status' => 'In Progress',
        ]);

        $breakdownRequest =
            $officerAssignment->assignment->request;

        $breakdownRequest->update([
            'status' => 'In Progress',
        ]);

        ActivityLog::log(
            $breakdownRequest->id,
            $user->id,
            'Started work',
            'Technical Officer started working on the breakdown.'
        );

        return back()->with(
            'success',
            'Work started successfully.'
        );
    }

    /**
     * Technical Officer submits the work report.
     */
    public function store(
        Request $request,
        OfficerAssignment $officerAssignment
    ) {
        $user = Auth::user();

        abort_unless(
            $user->isTechnicalOfficer()
            && $officerAssignment->technical_officer_id === $user->id,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Validate Work Report
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([
            'problem_identified' => [
                'nullable',
                'string',
            ],

            'work_performed' => [
                'required',
                'string',
            ],

            'parts_used' => [
                'nullable',
                'string',
            ],

            'completion_status' => [
                'required',
                'in:Done,Not Done',
            ],

            'remarks' => [
                'nullable',
                'string',
            ],

            'photos.*' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create / Update Work Report
        |--------------------------------------------------------------------------
        */

        $workReport = WorkReport::updateOrCreate(
            [
                'officer_assignment_id' =>
                    $officerAssignment->id,
            ],
            [
                'problem_identified' =>
                    $data['problem_identified'] ?? null,

                'work_performed' =>
                    $data['work_performed'],

                'parts_used' =>
                    $data['parts_used'] ?? null,

                'completion_status' =>
                    $data['completion_status'],

                'remarks' =>
                    $data['remarks'] ?? null,

                'attended_at' => now(),
                'reported_at' => now(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Upload Photos
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('photos')) {

            foreach ($request->file('photos') as $file) {

                $path = $file->store(
                    'attachments/work_reports/' . $workReport->id,
                    'public'
                );

                Attachment::create([
                    'attachable_id' =>
                        $workReport->id,

                    'attachable_type' =>
                        WorkReport::class,

                    'uploaded_by' =>
                        $user->id,

                    'original_name' =>
                        $file->getClientOriginalName(),

                    'path' =>
                        $path,

                    'mime_type' =>
                        $file->getClientMimeType(),

                    'size' =>
                        $file->getSize(),
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Get Breakdown Request
        |--------------------------------------------------------------------------
        */

        $breakdownRequest =
            $officerAssignment
                ->assignment
                ->request;

        /*
        |--------------------------------------------------------------------------
        | DONE
        |--------------------------------------------------------------------------
        |
        | Technical Officer completed the work successfully.
        |
        | Officer Assignment = Done
        | Breakdown Request  = Resolved
        |
        | Next step:
        | Department Confirmation
        |
        */

        if ($data['completion_status'] === 'Done') {

            $officerAssignment->update([
                'status' => 'Done',
            ]);

            $breakdownRequest->update([
                'status' => 'Resolved',
            ]);

            ActivityLog::log(
                $breakdownRequest->id,
                $user->id,
                'Filed work report (Done)',
                $data['work_performed']
            );

            return back()->with(
                'success',
                'Work report submitted successfully. Request marked as resolved.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | NOT DONE
        |--------------------------------------------------------------------------
        |
        | Technical Officer could not complete the work.
        |
        | Officer Assignment = Not Done
        | Breakdown Request  = Pending Reassignment
        | assigned_to        = NULL
        |
        | Next step:
        | Assign Officer assigns another / same Technical Officer or Inform and forward to IT Head.
        |
        */

        $officerAssignment->update([
            'status' => 'Not Done',
        ]);

        $breakdownRequest->update([
            'status' => 'Pending Reassignment',
            'assigned_to' => null,
        ]);

        ActivityLog::log(
            $breakdownRequest->id,
            $user->id,
            'Filed work report (Not Done)',
            $data['work_performed']
        );

        /*
        |--------------------------------------------------------------------------
        | Important Redirect
        |--------------------------------------------------------------------------
        |
        | Because assigned_to is now NULL, this Technical Officer may no longer
        | have permission to view this request.
        |
        | Therefore redirect to dashboard instead of return back().
        |
        */

        return redirect()
            ->route('dashboard')
            ->with(
                'success',
                'Work report submitted. Request sent for reassignment.'
            );
    }
}