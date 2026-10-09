<?php

namespace App\Notifications;

use App\Models\WorkReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResolutionConfirmationEmail extends Notification
{
    use Queueable;

    public function __construct(
        public WorkReport $workReport
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $report = $this->workReport->loadMissing([
            'officerAssignment.assignment.request.requestedBy',
            'officerAssignment.technicalOfficer',
        ]);

        $job = $report->officerAssignment;
        $breakdown = $job?->assignment?->request;
        $ministryUser = $breakdown?->requestedBy;

        $mail = (new MailMessage)
            ->subject(
                'FixIT - Please Confirm Resolution - ' .
                ($breakdown?->request_number ?? 'IT Request')
            )
            ->greeting(
                'Dear ' .
                ($ministryUser?->name ?? 'Ministry User') .
                ','
            )
            ->line(
                'The Technical Officer has reported that your IT issue has been resolved.'
            )
            ->line(
                'Please review the completed work and confirm whether the issue has been resolved satisfactorily.'
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
                ($job?->technicalOfficer?->name ?? 'N/A')
            )
            ->line(
                'Problem Identified: ' .
                ($report->problem_identified ?: 'Not specified')
            )
            ->line(
                'Work Performed: ' .
                ($report->work_performed ?: 'Not specified')
            )
            ->line(
                'Technician Remarks: ' .
                ($report->remarks ?: 'No remarks provided')
            )
            ->line('Current Request Status: Resolved')
            ->line(
                'Your confirmation is still required before the request can be closed.'
            );

        if ($breakdown) {
            $mail->action(
                'Review and Confirm Resolution',
                route('requests.show', $breakdown->id)
            );
        }

        return $mail
            ->line(
                'If the issue is resolved, select Yes in the confirmation section. If the issue remains unresolved, select No and provide feedback.'
            )
            ->line(
                'Please sign in to FixIT to submit your confirmation.'
            )
            ->salutation(
                'FixIT - IT Issue Resolution System'
            );
    }
}
