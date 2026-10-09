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
            ->greeting(
                'Dear ' .
                ($job->technicalOfficer?->name ?? 'Technical Officer') .
                ','
            )
            ->line('A new IT job has been assigned to you.')
            ->line('Request No: ' . ($request?->request_number ?? 'N/A'))
            ->line('Issue: ' . ($request?->title ?? 'N/A'))
            ->line(
                'Assigned By: ' .
                ($job->assignment?->assignOfficer?->name ?? 'Assign Officer')
            )
            ->line(
                'Due Date: ' .
                ($job->due_date?->format('d F Y') ?? 'Not specified')
            )
            ->line('Assignment Note: ' . ($job->note ?: 'None'))
            ->action(
                'View Assigned Job',
                route('requests.show', $request->id)
            )
            ->line('Please review and complete the assigned work before the due date.')
            ->salutation('FixIT - IT Issue Resolution System');
    }
}
