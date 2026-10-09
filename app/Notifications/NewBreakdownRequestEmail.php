<?php

namespace App\Notifications;

use App\Models\BreakdownRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBreakdownRequestEmail extends Notification
{
    use Queueable;

    public function __construct(
        public BreakdownRequest $breakdown
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $breakdown = $this->breakdown;

        return (new MailMessage)
            ->subject(
                'FixIT - New IT Request - ' .
                $breakdown->request_number
            )
            ->greeting('Dear Assign Officer,')
            ->line(
                'A new IT issue request has been submitted and is awaiting assignment.'
            )
            ->line('Request No: ' . $breakdown->request_number)
            ->line('Issue: ' . $breakdown->title)
            ->line('Submitted By: ' . (
                $breakdown->requestedBy?->name ?? 'Ministry User'
            ))
            ->line('Status: ' . $breakdown->status)
            ->action(
                'View Breakdown Request',
                route('requests.show', $breakdown)
            )
            ->line(
                'Please review the request and assign a Technical Officer.'
            )
            ->salutation('FixIT - IT Issue Resolution System');
    }
}