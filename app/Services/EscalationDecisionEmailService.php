<?php

namespace App\Services;

use App\Models\Escalation;
use App\Notifications\EscalationDecisionEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class EscalationDecisionEmailService
{
    public static function send(Escalation $escalation): void
    {
        if (!config(
            'fixit_notifications.escalation_decision_email_enabled',
            false
        )) {
            Log::info('FixIT decision email disabled.', [
                'escalation_id' => $escalation->id,
            ]);

            return;
        }

        if (
            $escalation->status !== 'Reviewed' ||
            empty($escalation->admin_decision)
        ) {
            Log::warning('FixIT decision email skipped: no final decision.', [
                'escalation_id' => $escalation->id,
            ]);

            return;
        }

        try {
            $escalation->loadMissing(
                'breakdownRequest.requestedBy.role'
            );

            $breakdown = $escalation->breakdownRequest;
            $ministryUser = $breakdown?->requestedBy;

            if (
                !$ministryUser ||
                !$ministryUser->is_active ||
                !$ministryUser->isMinistryUser() ||
                !filter_var(
                    $ministryUser->email,
                    FILTER_VALIDATE_EMAIL
                )
            ) {
                Log::warning(
                    'FixIT decision email skipped: no eligible Ministry User.',
                    ['escalation_id' => $escalation->id]
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | TEST MODE
            |--------------------------------------------------------------------------
            */
            $testRecipient = config(
                'fixit_notifications.test_recipient'
            );

            if (!empty($testRecipient)) {
                Notification::route('mail', $testRecipient)
                    ->notify(
                        new EscalationDecisionEmail($escalation)
                    );

                Log::info('FixIT decision test email sent.', [
                    'escalation_id' => $escalation->id,
                    'original_user_id' => $ministryUser->id,
                ]);

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | LIVE MODE
            |--------------------------------------------------------------------------
            */
            $ministryUser->notify(
                new EscalationDecisionEmail($escalation)
            );

            Log::info('FixIT decision email sent.', [
                'escalation_id' => $escalation->id,
                'ministry_user_id' => $ministryUser->id,
            ]);

        } catch (\Throwable $exception) {
            Log::error('FixIT decision email failed.', [
                'escalation_id' => $escalation->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
