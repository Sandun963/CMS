<?php

namespace App\Notifications;

use App\Models\Escalation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EscalationReceivedEmail extends Notification
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
            'breakdownRequest.category',
            'breakdownRequest.division',
            'breakdownRequest.floor',
            'forwardedBy',
        ]);

        $breakdown = $escalation->breakdownRequest;

        $mail = (new MailMessage)
            ->subject(
                'FixIT - New Escalation Received - ' .
                ($breakdown?->request_number ?? 'IT Request')
            )
            ->greeting('Dear IT Administrator,')
            ->line(
                'A new IT issue escalation has been forwarded to you for review.'
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
                'Category: ' .
                ($breakdown?->category?->name ?? 'N/A')
            )
            ->line(
                'Division: ' .
                ($breakdown?->division?->name ?? 'N/A')
            )
            ->line(
                'Forwarded By: ' .
                ($escalation->forwardedBy?->name ?? 'N/A')
            )
            ->line(
                'Escalation Reason: ' .
                ($escalation->reason ?? 'Not specified')
            )
            ->line(
                'Forwarded Date: ' .
                (
                    $escalation->forwarded_at
                        ? $escalation->forwarded_at->format('d F Y h:i A')
                        : 'N/A'
                )
            )
            ->line('Escalation Status: Pending Review');

        if ($escalation->exists) {
            $mail->action(
                'Review Escalated Case',
                route('escalations.show', $escalation->id)
            );
        }

        return $mail
            ->line(
                'Please review this escalation and submit your decision.'
            )
            ->salutation(
                'FixIT - IT Issue Resolution System'
            );
    }
}
