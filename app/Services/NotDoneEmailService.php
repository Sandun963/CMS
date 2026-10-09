<?php

namespace App\Services;

use App\Models\OfficerAssignment;
use App\Notifications\NotDoneCaseEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotDoneEmailService
{
    public static function send(OfficerAssignment $job): void
    {
        if (! config('fixit_notifications.not_done_email_enabled')) {
            return;
        }

        $job->loadMissing([
            'assignment.assignOfficer.role',
            'assignment.request',
            'technicalOfficer',
            'workReport',
        ]);

        // Find the Assign Officer responsible for this assignment.
        $assignOfficer = $job->assignment?->assignOfficer;

        if (
            ! $assignOfficer ||
            ! $assignOfficer->is_active ||
            ! $assignOfficer->isAssignOfficer() ||
            ! filter_var(
                $assignOfficer->email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            Log::warning(
                'FixIT: No eligible Assign Officer for Not Done email.',
                [
                    'officer_assignment_id' => $job->id,
                ]
            );

            return;
        }

        $testRecipient = config(
            'fixit_notifications.test_recipient'
        );

        try {
            if ($testRecipient) {
                // Test mode: email only the test inbox.
                Notification::route('mail', $testRecipient)
                    ->notify(new NotDoneCaseEmail($job));

                return;
            }

            // Live mode: email the responsible Assign Officer.
            $assignOfficer->notify(
                new NotDoneCaseEmail($job)
            );

        } catch (\Throwable $exception) {
            Log::error(
                'FixIT Not Done email failed.',
                [
                    'officer_assignment_id' => $job->id,
                    'assign_officer_id' => $assignOfficer->id,
                    'error' => $exception->getMessage(),
                ]
            );
        }
    }
}
