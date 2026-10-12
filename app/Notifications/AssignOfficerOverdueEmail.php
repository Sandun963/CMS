<?php

namespace App\Notifications;

use App\Models\OfficerAssignment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignOfficerOverdueEmail extends Notification
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
            'assignment.assignOfficer',
            'technicalOfficer',
        ]);

        $breakdown = $job->assignment?->request;
        $assignOfficer = $job->assignment?->assignOfficer;

        $dueDate = Carbon::parse($job->due_date)
            ->startOfDay();

        $today = Carbon::today('Asia/Colombo');

        $daysOverdue = (int) $dueDate->diffInDays(
            $today,
            false
        );

        $mail = (new MailMessage)
            ->subject(
                'FixIT - Overdue Case Alert - ' .
                ($breakdown?->request_number ?? 'IT Request')
            )
            ->greeting(
                'Dear ' .
                ($assignOfficer?->name ?? 'Assign Officer') .
                ','
            )
            ->line(
                'A task assigned under your responsibility has passed its due date and remains incomplete.'
            )
            ->line(
                'Request Number: ' .
                ($breakdown?->request_number ?? 'N/A')
            )
            ->line(
                'Issue: ' .
                ($breakdown?->title ?? 'N/A')
            )
            ->line(
                'Technical Officer: ' .
                ($job->technicalOfficer?->name ?? 'N/A')
            )
            ->line(
                'Task Status: ' . $job->status
            )
            ->line(
                'Due Date: ' .
                $dueDate->format('d F Y')
            )
            ->line(
                'Days Overdue: ' . $daysOverdue
            )
            ->line(
                'Assignment Note: ' .
                ($job->note ?: 'No note provided')
            );

        if ($breakdown) {
            $mail->action(
                'Review Overdue Case',
                route('requests.show', $breakdown->id)
            );
        }

        return $mail
            ->line(
                'Please follow up with the assigned Technical Officer and take the necessary action.'
            )
            ->salutation(
                'FixIT - IT Issue Resolution System'
            );
    }
}
