<?php

namespace App\Console\Commands;

use App\Models\OfficerAssignment;
use App\Models\User;
use App\Notifications\ITAdminOverdueSummaryEmail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;

class SendITAdminOverdueSummary extends Command
{
    protected $signature = 'fixit:send-admin-overdue-summary';

    protected $description =
        'Send a daily overdue assignment summary to IT Administrators';

    public function handle(): int
    {
        if (!config(
            'fixit_notifications.it_admin_overdue_summary_enabled',
            false
        )) {
            $this->info('IT Administrator overdue summary is disabled.');
            return self::SUCCESS;
        }

        $today = Carbon::today('Asia/Colombo');
        $date = $today->toDateString();

        /*
         * Find incomplete technical assignments
         * whose due dates are before today.
         */
        $assignments = OfficerAssignment::query()
            ->with([
                'assignment.request',
                'assignment.assignOfficer',
                'technicalOfficer',
            ])
            ->whereIn('status', ['Pending', 'In Progress'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $date)
            ->whereHas('assignment.request', function ($query) {
                $query->whereNotIn('status', [
                    'Resolved',
                    'Closed',
                    'Outsource Required',
                    'Pending Reassignment',
                ]);
            })
            ->orderBy('due_date')
            ->get();

        if ($assignments->isEmpty()) {
            $this->info('No overdue assignments. No summary sent.');
            return self::SUCCESS;
        }

        /*
         * Prepare the summary table data.
         */
        $jobs = $assignments->map(function ($job) use ($today) {
            $request = $job->assignment?->request;
            $dueDate = Carbon::parse($job->due_date)
                ->startOfDay();

            return [
                'request_number' =>
                    $request?->request_number ?? 'N/A',

                'title' =>
                    $request?->title ?? 'N/A',

                'technical_officer' =>
                    $job->technicalOfficer?->name ?? 'N/A',

                'assign_officer' =>
                    $job->assignment?->assignOfficer?->name ?? 'N/A',

                'due_date' =>
                    $dueDate->format('d M Y'),

                'days_overdue' =>
                    (int) $dueDate->diffInDays($today, false),

                'status' => $job->status,

                'url' => $request && Route::has('requests.show')
                    ? route('requests.show', $request->id)
                    : null,
            ];
        })->all();

        /*
         * Only active IT-scoped Administrators.
         * Other-scoped Administrators are excluded.
         */
        $administrators = User::query()
            ->where('is_active', true)
            ->where('admin_scope', 'IT')
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->whereHas('role', function ($query) {
                $query->where('code', 'administrator');
            })
            ->get()
            ->filter(fn ($user) =>
                filter_var($user->email, FILTER_VALIDATE_EMAIL)
            );

        if ($administrators->isEmpty()) {
            $this->warn('No active IT Administrator with a valid email.');
            return self::SUCCESS;
        }

        $testRecipient = config(
            'fixit_notifications.test_recipient'
        );

        $testMode = !empty($testRecipient);
        $mode = $testMode ? 'test' : 'live';

        if (
            $testMode &&
            !filter_var($testRecipient, FILTER_VALIDATE_EMAIL)
        ) {
            $this->error('Invalid test recipient email.');
            return self::FAILURE;
        }

        /*
         * Test mode sends ONE email to the test inbox.
         * Live mode sends ONE email per IT Administrator.
         */
        $recipients = $testMode
            ? collect([[
                'key' => 'test',
                'email' => $testRecipient,
                'user' => null,
            ]])
            : $administrators->map(fn ($admin) => [
                'key' => 'user:' . $admin->id,
                'email' => $admin->email,
                'user' => $admin,
            ]);

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($recipients as $recipient) {
            $key = $recipient['key'];

            try {
                /*
                 * Unique database constraint prevents
                 * duplicate summaries on the same date.
                 */
                $inserted = DB::table(
                    'it_admin_overdue_summary_logs'
                )->insertOrIgnore([
                    'summary_date' => $date,
                    'delivery_mode' => $mode,
                    'recipient_key' => $key,
                    'recipient_email' => $recipient['email'],
                    'sent_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (!$inserted) {
                    $skipped++;
                    continue;
                }

                $notification = new ITAdminOverdueSummaryEmail(
                    $jobs,
                    $date
                );

                if ($testMode) {
                    Notification::route(
                        'mail',
                        $recipient['email']
                    )->notify($notification);
                } else {
                    $recipient['user']->notify($notification);
                }

                DB::table('it_admin_overdue_summary_logs')
                    ->where('summary_date', $date)
                    ->where('delivery_mode', $mode)
                    ->where('recipient_key', $key)
                    ->update([
                        'sent_at' => now(),
                        'updated_at' => now(),
                    ]);

                $sent++;

                Log::info('FixIT IT Admin overdue summary sent', [
                    'date' => $date,
                    'recipient' => $recipient['email'],
                    'overdue_count' => count($jobs),
                    'mode' => $mode,
                ]);

            } catch (\Throwable $exception) {
                /*
                 * Clear an unsent reservation so
                 * the command can retry later.
                 */
                DB::table('it_admin_overdue_summary_logs')
                    ->where('summary_date', $date)
                    ->where('delivery_mode', $mode)
                    ->where('recipient_key', $key)
                    ->whereNull('sent_at')
                    ->delete();

                $failed++;

                Log::error('FixIT IT Admin overdue summary failed', [
                    'date' => $date,
                    'recipient' => $recipient['email'],
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $this->info(
            "IT Admin overdue summary: {$sent} sent, " .
            "{$skipped} skipped, {$failed} failed. " .
            count($jobs) . ' overdue assignments.'
        );

        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
