<?php

namespace App\Notifications;

use App\Models\OfficerAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewJobAssignedEmail extends Notification
{
    use Queueable;

    public function __construct(
        public OfficerAssignment $officerAssignment
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }


    public function toMail(object $notifiable): MailMessage
    {
        $job = $this->officerAssignment->loadMissing([
            'assignment.request',
            'assignment.assignOfficer',
            'technicalOfficer',
        ]);

        $request = $job->assignment?->request;

        return (new MailMessage)
            ->subject(
                'FixIT - New Job Assigned - ' .
                ($request?->request_number ?? 'IT Request')
            )
            ->markdown('emails.new-job-assigned', [
                'officerName' =>
                    $job->technicalOfficer?->name
                    ?? 'Technical Officer',

                'requestNumber' =>
                    $request?->request_number ?? 'N/A',

                'issue' =>
                    $request?->title ?? 'N/A',

                'assignedBy' =>
                    $job->assignment?->assignOfficer?->name
                    ?? 'Assign Officer',

                'dueDate' =>
                    $job->due_date?->format('F j, Y')
                    ?? 'Not specified',

                'note' =>
                    $job->note ?: 'No additional notes',

                'taskUrl' =>
                    $request
                        ? route('requests.show', $request->id)
                        : config('app.url'),
            ]);
    }

}
