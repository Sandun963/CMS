<?php

namespace App\Notifications;

use App\Models\OfficerAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotDoneCaseEmail extends Notification
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
            'workReport',
        ]);

        $breakdown = $job->assignment?->request;
        $report = $job->workReport;

        $mail = (new MailMessage)
            ->subject(
                'FixIT - Not Done Case - ' .
                ($breakdown?->request_number ?? 'IT Request')
            )
            ->greeting(
                'Dear ' . ($notifiable->name ?? 'Assign Officer') . ','
            )
            ->line(
                'A Technical Officer has submitted a Not Done work report.'
            )
            ->line(
                'Request No: ' .
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
                'Problem Identified: ' .
                ($report?->problem_identified ?: 'Not specified')
            )
            ->line(
                'Work Performed: ' .
                ($report?->work_performed ?: 'Not specified')
            )
            ->line(
                'Remarks / Reason: ' .
                ($report?->remarks ?: 'Not specified')
            )
            ->line('Request Status: Pending Reassignment')
            ->line(
                'Please review the case and take the necessary action.'
            );

        if ($breakdown) {
            $mail->action(
                'Review Breakdown Request',
                route('requests.show', $breakdown->id)
            );
        }

        return $mail->salutation(
            'FixIT - IT Issue Resolution System'
        );
    }
}
