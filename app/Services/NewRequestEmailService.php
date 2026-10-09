<?php

namespace App\Services;

use App\Models\BreakdownRequest;
use App\Models\Role;
use App\Models\User;
use App\Notifications\NewBreakdownRequestEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NewRequestEmailService
{
    public static function send(BreakdownRequest $breakdown): void
    {
        if (! config('fixit_notifications.new_request_email_enabled')) {
            return;
        }

        $officers = User::query()
            ->where('is_active', true)
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->whereHas('role', function ($query) {
                $query->where(
                    'code',
                    Role::ASSIGN_OFFICER
                );
            })
            ->get();

        $testRecipient = config(
            'fixit_notifications.test_recipient'
        );

        // In test mode, send only ONE email to the test inbox.
        if ($testRecipient) {
            if ($officers->isEmpty()) {
                Log::warning(
                    'FixIT: No active Assign Officers found for new request email.',
                    ['request_id' => $breakdown->id]
                );

                return;
            }

            try {
                Notification::route('mail', $testRecipient)
                    ->notify(
                        new NewBreakdownRequestEmail($breakdown)
                    );
            } catch (\Throwable $exception) {
                Log::error(
                    'FixIT test new request email failed.',
                    [
                        'request_id' => $breakdown->id,
                        'error' => $exception->getMessage(),
                    ]
                );
            }

            return;
        }

        // Live mode: each active Assign Officer receives an email.
        foreach ($officers as $officer) {
            try {
                $officer->notify(
                    new NewBreakdownRequestEmail($breakdown)
                );
            } catch (\Throwable $exception) {
                Log::error(
                    'FixIT new request email failed.',
                    [
                        'request_id' => $breakdown->id,
                        'recipient_user_id' => $officer->id,
                        'error' => $exception->getMessage(),
                    ]
                );
            }
        }
    }
}