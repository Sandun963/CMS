<?php

namespace App\Console\Commands;

use App\Models\OfficerAssignment;
use App\Models\Role;
use App\Notifications\TechnicalOfficerOverdueEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendTechnicalOfficerOverdueEmails extends Command
{
    protected $signature = 'fixit:send-technical-overdue';

    protected $description =
        'Send overdue task emails to Technical Officers';

    public function handle(): int
    {
        if (!config(
            'fixit_notifications.technical_overdue_email_enabled',
            false
        )) {
            $this->info('Technical overdue emails are disabled.');
            return self::SUCCESS;
        }

        $testRecipient = config(
            'fixit_notifications.test_recipient'
        );

        $testMode = !empty($testRecipient);
        $mode = $testMode ? 'test' : 'live';

        $sent = 0;
        $failed = 0;
        $skipped = 0;

        OfficerAssignment::query()
            ->with([
                'assignment.request',
                'technicalOfficer.role',
            ])
            ->whereIn('status', ['Pending', 'In Progress'])
            ->whereNotNull('due_date')
            ->whereDate(
                'due_date',
                '<',
                \Carbon\Carbon::today('Asia/Colombo')->toDateString()
            )
            ->whereHas('assignment.request', function ($query) {
                $query->whereNotIn('status', [
                    'Resolved',
                    'Closed',
                    'Outsource Required',
                    'Pending Reassignment',
                ]);
            })
            ->whereHas('technicalOfficer', function ($query) {
                $query->where('is_active', true)
                    ->whereNotNull('email')
                    ->where('email', '<>', '')
                    ->whereHas('role', function ($roleQuery) {
                        $roleQuery->where(
                            'code',
                            Role::TECHNICAL_OFFICER
                        );
                    });
            })
            ->chunkById(100, function ($jobs) use (
                $testMode,
                $testRecipient,
                $mode,
                &$sent,
                &$failed,
                &$skipped
            ) {
                foreach ($jobs as $job) {
                    $technician = $job->technicalOfficer;

                    if (
                        !$technician ||
                        !filter_var(
                            $technician->email,
                            FILTER_VALIDATE_EMAIL
                        )
                    ) {
                        $skipped++;
                        continue;
                    }

                    /*
                     * Reserve one email per assignment and mode.
                     * The unique database constraint prevents
                     * normal duplicate processing.
                     */
                    $inserted = DB::table('overdue_email_logs')
                        ->insertOrIgnore([
                            'officer_assignment_id' => $job->id,
                            'delivery_mode' => $mode,
                            'recipient_email' => $testMode
                                ? $testRecipient
                                : $technician->email,
                            'sent_at' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                    if (!$inserted) {
                        $skipped++;
                        continue;
                    }

                    try {
                        $email = new TechnicalOfficerOverdueEmail(
                            $job
                        );

                        if ($testMode) {
                            Notification::route(
                                'mail',
                                $testRecipient
                            )->notify($email);
                        } else {
                            $technician->notify($email);
                        }

                        DB::table('overdue_email_logs')
                            ->where('officer_assignment_id', $job->id)
                            ->where('delivery_mode', $mode)
                            ->update([
                                'sent_at' => now(),
                                'updated_at' => now(),
                            ]);

                        $sent++;

                        Log::info(
                            'FixIT technical overdue email sent.',
                            [
                                'assignment_id' => $job->id,
                                'mode' => $mode,
                            ]
                        );

                    } catch (\Throwable $exception) {
                        /*
                         * Remove the reservation so the next
                         * scheduler run can retry.
                         */
                        DB::table('overdue_email_logs')
                            ->where('officer_assignment_id', $job->id)
                            ->where('delivery_mode', $mode)
                            ->whereNull('sent_at')
                            ->delete();

                        $failed++;

                        Log::error(
                            'FixIT technical overdue email failed.',
                            [
                                'assignment_id' => $job->id,
                                'error' => $exception->getMessage(),
                            ]
                        );
                    }
                }
            });

        $this->info(
            "Overdue emails: {$sent} sent, " .
            "{$skipped} skipped, {$failed} failed."
        );

        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
