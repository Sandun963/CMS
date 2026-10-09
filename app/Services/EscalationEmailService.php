<?php

namespace App\Services;

use App\Models\Escalation;
use App\Models\Role;
use App\Models\User;
use App\Notifications\EscalationReceivedEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class EscalationEmailService
{
    public static function send(Escalation $escalation): void
    {
        // Check whether escalation email is enabled.
        if (! config('fixit_notifications.escalation_email_enabled')) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Find all active IT Administrators
        |--------------------------------------------------------------------------
        */
        $administrators = User::query()
            ->where('is_active', true)
            ->where('admin_scope', 'IT')
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->whereHas('role', function ($query) {
                $query->where(
                    'code',
                    Role::ADMINISTRATOR
                );
            })
            ->get()
            ->filter(function ($user) {
                return filter_var(
                    $user->email,
                    FILTER_VALIDATE_EMAIL
                );
            });

        if ($administrators->isEmpty()) {
            Log::warning(
                'FixIT: No active IT Administrators found for escalation email.',
                [
                    'escalation_id' => $escalation->id,
                ]
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Test Mode
        |--------------------------------------------------------------------------
        |
        | Send only one email to the configured test inbox.
        |
        */
        $testRecipient = config(
            'fixit_notifications.test_recipient'
        );

        if ($testRecipient) {
            try {
                Notification::route('mail', $testRecipient)
                    ->notify(
                        new EscalationReceivedEmail($escalation)
                    );

                Log::info(
                    'FixIT escalation test email sent.',
                    [
                        'escalation_id' => $escalation->id,
                    ]
                );

            } catch (\Throwable $exception) {
                Log::error(
                    'FixIT escalation test email failed.',
                    [
                        'escalation_id' => $escalation->id,
                        'error' => $exception->getMessage(),
                    ]
                );
            }

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Live Mode
        |--------------------------------------------------------------------------
        |
        | Send an individual email to each active IT Administrator.
        |
        */
        foreach ($administrators as $administrator) {
            try {
                $administrator->notify(
                    new EscalationReceivedEmail($escalation)
                );

                Log::info(
                    'FixIT escalation email sent.',
                    [
                        'escalation_id' => $escalation->id,
                        'administrator_id' => $administrator->id,
                    ]
                );

            } catch (\Throwable $exception) {
                Log::error(
                    'FixIT escalation email failed.',
                    [
                        'escalation_id' => $escalation->id,
                        'administrator_id' => $administrator->id,
                        'error' => $exception->getMessage(),
                    ]
                );
            }
        }
    }
}
