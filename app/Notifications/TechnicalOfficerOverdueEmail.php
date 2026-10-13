<?php

namespace App\Notifications;

use App\Models\OfficerAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TechnicalOfficerOverdueEmail extends Notification
{
    use Queueable;

    public function __construct(
        public OfficerAssignment $job
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $job = $this->job->loadMissing([
            'assignment.request',
            'technicalOfficer',
        ]);

        $request = $job->assignment?->request;

        $daysOverdue = (int) $job->due_date
            ->startOfDay()
            ->diffInDays(
                \Carbon\Carbon::now('Asia/Colombo')->startOfDay()
            );

        $mail = (new MailMessage)
            ->subject(
                'FixIT - Overdue Task Reminder - ' .
                ($request?->request_number ?? 'IT Request')
            )
            ->greeting(
                'Dear ' .
                ($job->technicalOfficer?->name ?? 'Technical Officer') .
                ','
            )
            ->line(
                'Your assigned IT task has passed its due date and is still awaiting completion.'
            )
            ->line(
                'Request Number: ' .
                ($request?->request_number ?? 'N/A')
            )
            ->line(
                'Issue: ' .
                ($request?->title ?? 'N/A')
            )
            ->line(
                'Assignment Status: ' . $job->status
            )
            ->line(
                'Due Date: ' .
                $job->due_date->format('d F Y')
            )
            ->line(
                'Days Overdue: ' . $daysOverdue
            )
            ->line(
                'Assignment Note: ' .
                ($job->note ?: 'No note provided')
            );

        if ($request) {
            $mail->action(
                'View Assigned Task',
                route('requests.show', $request)
            );
        }

        return $mail
            ->line(
                'Please attend to this task as soon as possible and update its progress in FixIT.'
            )
            ->salutation(
                'FixIT - IT Issue Resolution System'
            )
            ->markdown('emails.fixit-notification');
    }
}
