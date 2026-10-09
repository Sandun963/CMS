<?php

namespace App\Notifications;

use App\Models\Escalation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EscalationDecisionEmail extends Notification
{
    use Queueable;

    public function __construct(
        public Escalation $escalation
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $escalation = $this->escalation->loadMissing([
            'breakdownRequest',
        ]);

        $breakdown = $escalation->breakdownRequest;

        $decision = $escalation->admin_decision
            ?? 'Not specified';

        $status = $breakdown?->status ?? 'N/A';

        $mail = (new MailMessage)
            ->subject(
                'FixIT - Decision Update - ' .
                ($breakdown?->request_number ?? 'IT Request')
            )
            ->greeting(
                'Dear ' .
                ($notifiable->name ?? 'Ministry User') .
                ','
            )
            ->line(
                'The IT Administrator has reviewed the escalated IT issue you submitted.'
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
                'Administrator Decision: ' . $decision
            )
            ->line(
                'Current Request Status: ' . $status
            )
            ->line(
                'Administrator Remarks: ' .
                ($escalation->admin_remarks ?: 'No remarks provided')
            )
            ->line(
                'Decision Date: ' .
                ($escalation->reviewed_at
                    ? $escalation->reviewed_at->format('d F Y h:i A')
                    : 'N/A')
            );

        if ($breakdown) {
            $mail->action(
                'View My Request',
                route('requests.show', $breakdown->id)
            );
        }

        if ($status === 'Outsource Required') {
            $mail->line(
                'Your request has been marked as requiring external technical assistance. Please refer to the administrator remarks for further information.'
            );
        } elseif ($status === 'Closed') {
            $mail->line(
                'Your request has been closed following the IT Administrator review.'
            );
        }

        return $mail
            ->line(
                'Thank you for using the FixIT IT Issue Resolution System.'
            )
            ->salutation(
                'FixIT - IT Issue Resolution System'
            );
    }
}
