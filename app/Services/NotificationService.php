<?php

namespace App\Services;

use App\Models\Escalation;
use App\Models\OfficerAssignment;
use App\Models\User;
use Carbon\Carbon;

class NotificationService
{
    public static function forUser(User $user): array
    {
        $notifications = [];

        /*
        |--------------------------------------------------------------------------
        | Technical Officer
        |--------------------------------------------------------------------------
        |
        | Only show THEIR active due-date assignments.
        |
        */
        if ($user->isTechnicalOfficer()) {

            $assignments = OfficerAssignment::with([
                'assignment.request'
            ])
                ->where(
                    'technical_officer_id',
                    $user->id
                )
                ->whereIn(
                    'status',
                    [
                        'Pending',
                        'In Progress',
                    ]
                )
                ->whereNotNull('due_date')
                ->get();

            foreach ($assignments as $assignment) {

                $notification =
                    self::dueDateNotification(
                        $assignment
                    );

                if ($notification) {
                    $notifications[] = $notification;
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Assign Officer
        |--------------------------------------------------------------------------
        |
        | Show due-date alerts for technician assignments
        | created/managed by this Assign Officer.
        |
        */
        if ($user->isAssignOfficer()) {

            $assignments = OfficerAssignment::with([
                'assignment.request',
                'technicalOfficer',
            ])
                ->whereHas(
                    'assignment',
                    function ($query) use ($user) {

                        $query->where(
                            'assign_officer_id',
                            $user->id
                        );
                    }
                )
                ->whereIn(
                    'status',
                    [
                        'Pending',
                        'In Progress',
                    ]
                )
                ->whereNotNull('due_date')
                ->get();

            foreach ($assignments as $assignment) {

                $notification =
                    self::dueDateNotification(
                        $assignment,
                        true
                    );

                if ($notification) {
                    $notifications[] = $notification;
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | IT Administrator
        |--------------------------------------------------------------------------
        |
        | IT Admin receives:
        |
        | 1. ALL active due-date alerts
        | 2. Pending escalation alerts
        |
        */
        if ($user->isItAdministrator()) {

            /*
            |--------------------------------------------------------------------------
            | Due Date Alerts
            |--------------------------------------------------------------------------
            */

            $assignments = OfficerAssignment::with([
                'assignment.request',
                'technicalOfficer',
            ])
                ->whereIn(
                    'status',
                    [
                        'Pending',
                        'In Progress',
                    ]
                )
                ->whereNotNull('due_date')
                ->get();

            foreach ($assignments as $assignment) {

                $notification =
                    self::dueDateNotification(
                        $assignment,
                        true
                    );

                if ($notification) {
                    $notifications[] = $notification;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Escalation Alerts
            |--------------------------------------------------------------------------
            */

            $escalations = Escalation::with([
                'breakdownRequest',
                'forwardedBy',
            ])
                ->where(
                    'status',
                    'Pending Review'
                )
                ->latest('forwarded_at')
                ->get();

            foreach ($escalations as $escalation) {

                $request =
                    $escalation->breakdownRequest;

                if (! $request) {
                    continue;
                }

                $notifications[] = [

                    'type' =>
                        'escalation',

                    'title' =>
                        'Escalated Case',

                    'message' =>
                        $request->request_number .
                        ' is awaiting IT Administrator review.',

                    'request_number' =>
                        $request->request_number,

                    'technician' =>
                        null,

                    'url' =>
                        route(
                            'escalations.show',
                            $escalation
                        ),

                    'priority' =>
                        1,

                    'date' =>
                        $escalation->forwarded_at,
                ];
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Sort Notifications
        |--------------------------------------------------------------------------
        |
        | Priority:
        |
        | 1 = Escalation / Overdue
        | 2 = Due Today
        | 3 = Due Tomorrow
        | 4 = Due Soon
        |
        */

        usort(
            $notifications,
            function ($a, $b) {

                return
                    $a['priority']
                    <=>
                    $b['priority'];
            }
        );


        return $notifications;
    }


    /**
     * Build due-date notification.
     */
    private static function dueDateNotification(
        OfficerAssignment $assignment,
        bool $showTechnician = false
    ): ?array {

        if (! $assignment->due_date) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Use date-only comparison
        |--------------------------------------------------------------------------
        */

        $today = Carbon::today();

        $dueDate =
            $assignment
                ->due_date
                ->copy()
                ->startOfDay();


        $days =
            $today->diffInDays(
                $dueDate,
                false
            );


        /*
        |--------------------------------------------------------------------------
        | More than 2 days remaining
        |--------------------------------------------------------------------------
        */

        if ($days > 2) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Request
        |--------------------------------------------------------------------------
        */

        $request =
            $assignment
                ->assignment
                ?->request;

        if (! $request) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Determine Notification Level
        |--------------------------------------------------------------------------
        */

        if ($days < 0) {

            $overdueDays =
                abs($days);

            $title =
                'Overdue';

            $message =
                $request->request_number .
                ' is overdue by ' .
                $overdueDays .
                ($overdueDays === 1
                    ? ' day.'
                    : ' days.');

            $priority = 1;

        } elseif ($days === 0) {

            $title =
                'Due Today';

            $message =
                $request->request_number .
                ' is due today.';

            $priority = 2;

        } elseif ($days === 1) {

            $title =
                'Due Tomorrow';

            $message =
                $request->request_number .
                ' is due tomorrow.';

            $priority = 3;

        } else {

            $title =
                'Due Soon';

            $message =
                $request->request_number .
                ' is due in 2 days.';

            $priority = 4;
        }


        /*
        |--------------------------------------------------------------------------
        | Show technician name to Assign Officer / IT Admin
        |--------------------------------------------------------------------------
        */

        if (
            $showTechnician
            &&
            $assignment->technicalOfficer
        ) {

            $message .=
                ' Technician: ' .
                $assignment
                    ->technicalOfficer
                    ->name .
                '.';
        }


        return [

            'type' =>
                'due',

            'title' =>
                $title,

            'message' =>
                $message,

            'request_number' =>
                $request->request_number,

            'technician' =>
                $assignment
                    ->technicalOfficer
                    ?->name,

            'url' =>
                route(
                    'requests.show',
                    $request
                ),

            'priority' =>
                $priority,

            'date' =>
                $assignment->due_date,
        ];
    }
}