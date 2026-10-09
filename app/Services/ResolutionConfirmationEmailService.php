<?php

namespace App\Services;

use App\Models\WorkReport;
use App\Notifications\ResolutionConfirmationEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ResolutionConfirmationEmailService
{
    public static function send(WorkReport $workReport): void
    {
        if (!config(
            'fixit_notifications.resolution_confirmation_email_enabled',
            false
        )) {
            Log::info('FixIT resolution confirmation email disabled.', [
                'work_report_id' => $workReport->id,
            ]);

            return;
        }

        try {
            $workReport->loadMissing([
                'officerAssignment.assignment.request.requestedBy.role',
                'confirmation',
            ]);

            $job = $workReport->officerAssignment;
            $breakdown = $job?->assignment?->request;
            $ministryUser = $breakdown?->requestedBy;

            // Only send for completed jobs awaiting confirmation.
            if (
                $workReport->completion_status !== 'Done' ||
                $job?->status !== 'Done' ||
                $breakdown?->status !== 'Resolved' ||
                $workReport->confirmation !== null
            ) {
                Log::info(
                    'FixIT resolution email skipped: confirmation not pending.',
                    ['work_report_id' => $workReport->id]
                );

                return;
            }

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
                    'FixIT resolution email skipped: no eligible Ministry User.',
                    ['work_report_id' => $workReport->id]
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
                        new ResolutionConfirmationEmail($workReport)
                    );

                Log::info(
                    'FixIT resolution confirmation test email sent.',
                    [
                        'work_report_id' => $workReport->id,
                        'ministry_user_id' => $ministryUser->id,
                    ]
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | LIVE MODE
            |--------------------------------------------------------------------------
            */
            $ministryUser->notify(
                new ResolutionConfirmationEmail($workReport)
            );

            Log::info(
                'FixIT resolution confirmation email sent.',
                [
                    'work_report_id' => $workReport->id,
                    'ministry_user_id' => $ministryUser->id,
                ]
            );

        } catch (\Throwable $exception) {
            Log::error(
                'FixIT resolution confirmation email failed.',
                [
                    'work_report_id' => $workReport->id,
                    'error' => $exception->getMessage(),
                ]
            );
        }
    }
}
