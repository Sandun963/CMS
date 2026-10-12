<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ITAdminOverdueSummaryEmail extends Notification
{
    use Queueable;

    public function __construct(
        public array $jobs,
        public string $summaryDate
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(
                'FixIT - Daily Overdue Summary - ' .
                $this->summaryDate
            )
            ->view('emails.it-admin-overdue-summary', [
                'jobs' => $this->jobs,
                'summaryDate' => $this->summaryDate,
                'total' => count($this->jobs),
            ]);
    }
}
