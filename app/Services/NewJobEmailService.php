<?php

namespace App\Services;

use App\Models\OfficerAssignment;
use App\Notifications\NewJobAssignedEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NewJobEmailService
{
    public static function send(OfficerAssignment $job): void
    {
        if (! config('fixit_notifications.new_job_email_enabled')) {
            return;
        }

        $job->loadMissing([
            'technicalOfficer.role',
            'assignment.request',
            'assignment.assignOfficer',
        ]);

        $technician = $job->technicalOfficer;

        if (
            ! $technician ||
            ! $technician->is_active ||
            ! $technician->isTechnicalOfficer() ||
            ! filter_var($technician->email, FILTER_VALIDATE_EMAIL)
        ) {
            Log::warning(
                'FixIT: Assigned technician is not eligible for email.',
                ['officer_assignment_id' => $job->id]
            );

            return;
        }

        $testRecipient = config(
            'fixit_notifications.test_recipient'
        );

        try {
            if ($testRecipient) {
                // Test mode: send to test inbox only.
                Notification::route('mail', $testRecipient)
                    ->notify(new NewJobAssignedEmail($job));

                return;
            }

            // Live mode: email the actual assigned technician.
            $technician->notify(
                new NewJobAssignedEmail($job)
            );

        } catch (\Throwable $exception) {
            Log::error(
                'FixIT new job assignment email failed.',
                [
                    'officer_assignment_id' => $job->id,
                    'technician_id' => $technician->id,
                    'error' => $exception->getMessage(),
                ]
            );
        }
    }
}
