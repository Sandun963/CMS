<?php

namespace App\Console\Commands;

use App\Models\OfficerAssignment;
use App\Models\Role;
use App\Notifications\AssignOfficerOverdueEmail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendAssignOfficerOverdueEmails extends Command
{
    protected $signature = 'fixit:send-assign-overdue';

    protected $description =
        'Send overdue case emails to responsible Assign Officers';

    public function handle(): int
    {
        if (!config(
            'fixit_notifications.assign_officer_overdue_email_enabled',
            false
        )) {
            $this->info('Assign Officer overdue emails are disabled.');
            return self::SUCCESS;
        }

        $testRecipient = config(
            'fixit_notifications.test_recipient'
        );

        $testMode = !empty($testRecipient);
        $mode = $testMode ? 'ao_test' : 'ao_live';

        $today = Carbon::today('Asia/Colombo')
            ->toDateString();

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        OfficerAssignment::query()
            ->with([
                'assignment.request',
                'assignment.assignOfficer.role',
                'technicalOfficer',
            ])
            ->whereIn('status', [
                'Pending',
                'In Progress',
            ])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->whereHas('assignment.request', function ($query) {
                $query->whereNotIn('status', [
                    'Resolved',
                    'Closed',
                    'Outsource Required',
                    'Pending Reassignment',
                ]);
            })
            ->whereHas('assignment.assignOfficer', function ($query) {
                $query
                    ->where('is_active', true)
                    ->whereNotNull('email')
                    ->where('email', '<>', '')
                    ->whereHas('role', function ($roleQuery) {
                        $roleQuery->where(
                            'code',
                            Role::ASSIGN_OFFICER
                        );
                    });
            })
            ->chunkById(100, function ($jobs) use (
                $testMode,
                $testRecipient,
                $mode,
                &$sent,
                &$skipped,
                &$failed
            ) {
                foreach ($jobs as $job) {
                    $assignOfficer =
                        $job->assignment?->assignOfficer;

                    if (
                        !$assignOfficer ||
                        !filter_var(
                            $assignOfficer->email,
                            FILTER_VALIDATE_EMAIL
                        )
                    ) {
                        $skipped++;
                        continue;
                    }

                    /*
                     * Reserve this overdue notification.
                     * A separate delivery mode prevents
                     * conflict with Technical Officer emails.
                     */
                    try {
                        $inserted = DB::table('overdue_email_logs')
                            ->insertOrIgnore([
                                'officer_assignment_id' => $job->id,
                                'delivery_mode' => $mode,
                                'recipient_email' => $testMode
                                    ? $testRecipient
                                    : $assignOfficer->email,
                                'sent_at' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                        if (!$inserted) {
                            $skipped++;
                            continue;
                        }

                        $email = new AssignOfficerOverdueEmail(
                            $job
                        );

                        if ($testMode) {
                            Notification::route(
                                'mail',
                                $testRecipient
                            )->notify($email);
                        } else {
                            $assignOfficer->notify($email);
                        }

                        DB::table('overdue_email_logs')
                            ->where(
                                'officer_assignment_id',
                                $job->id
                            )
                            ->where(
                                'delivery_mode',
                                $mode
                            )
                            ->update([
                                'sent_at' => now(),
                                'updated_at' => now(),
                            ]);

                        $sent++;

                        Log::info(
                            'FixIT Assign Officer overdue email sent.',
                            [
                                'assignment_id' => $job->id,
                                'assign_officer_id' =>
                                    $assignOfficer->id,
                                'mode' => $mode,
                            ]
                        );

                    } catch (\Throwable $exception) {
                        /*
                         * Allow retry on a future run
                         * if SMTP or rendering failed.
                         */
                        DB::table('overdue_email_logs')
                            ->where(
                                'officer_assignment_id',
                                $job->id
                            )
                            ->where(
                                'delivery_mode',
                                $mode
                            )
                            ->whereNull('sent_at')
                            ->delete();

                        $failed++;

                        Log::error(
                            'FixIT Assign Officer overdue email failed.',
                            [
                                'assignment_id' => $job->id,
                                'error' => $exception->getMessage(),
                            ]
                        );
                    }
                }
            });

        $this->info(
            "Assign Officer overdue emails: {$sent} sent, " .
            "{$skipped} skipped, {$failed} failed."
        );

        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
