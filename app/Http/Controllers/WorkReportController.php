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
    public function startWork(OfficerAssignment $officerAssignment)
    {
        $user = Auth::user();

        abort_unless(
            $user->isTechnicalOfficer()
            && $officerAssignment->technical_officer_id === $user->id,
            403
        );

        if ($officerAssignment->status !== 'Pending') {
            return back()->with('error', 'This job has already been started.');
        }

        $officerAssignment->update([
            'status' => 'In Progress',
        ]);

        $breakdownRequest = $officerAssignment->assignment->request;

        $breakdownRequest->update([
            'status' => 'In Progress',
        ]);

        ActivityLog::log(
            $breakdownRequest->id,
            $user->id,
            'Started work',
            'Technical Officer started working on the breakdown.'
        );

        return back()->with('success', 'Work started successfully.');
    }
    /**
     * Step 4: Technical Officer visits, fixes the issue, and files a work report.
     */
    public function store(Request $request, OfficerAssignment $officerAssignment)
    {
        $user = Auth::user();
        abort_unless($user->isTechnicalOfficer() && $officerAssignment->technical_officer_id === $user->id, 403);

        $data = $request->validate([
            'problem_identified' => ['nullable', 'string'],
            'work_performed' => ['required', 'string'],
            'parts_used' => ['nullable', 'string'],
            'completion_status' => ['required', 'in:Done,Not Done'],
            'remarks' => ['nullable', 'string'],
            'photos.*' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png'],
        ]);

        $workReport = WorkReport::updateOrCreate(
            ['officer_assignment_id' => $officerAssignment->id],
            [
                'problem_identified' => $data['problem_identified'] ?? null,
                'work_performed' => $data['work_performed'],
                'parts_used' => $data['parts_used'] ?? null,
                'completion_status' => $data['completion_status'],
                'remarks' => $data['remarks'] ?? null,
                'attended_at' => now(),
                'reported_at' => now(),
            ]
        );

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $file->store('attachments/work_reports/' . $workReport->id, 'public');
                Attachment::create([
                    'attachable_id' => $workReport->id,
                    'attachable_type' => WorkReport::class,
                    'uploaded_by' => $user->id,
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        $officerAssignment->update([
            'status' => $data['completion_status'] === 'Done' ? 'Done' : 'In Progress',
        ]);

        $breakdownRequest = $officerAssignment->assignment->request;
        $breakdownRequest->update([
            'status' => $data['completion_status'] === 'Done' ? 'Resolved' : 'In Progress',
        ]);

        ActivityLog::log(
            $breakdownRequest->id,
            $user->id,
            'Filed work report (' . $data['completion_status'] . ')',
            $data['work_performed']
        );

        return back()->with('success', 'Work report submitted.');
    }
}
