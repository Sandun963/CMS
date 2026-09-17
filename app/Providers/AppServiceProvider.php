<?php

namespace App\Providers;

use App\Models\NotificationRead;
use App\Services\NotificationService;
use DateTimeInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(
            'layouts.app',
            function ($view) {

                $notifications = [];

                if (Auth::check()) {

                    $user = Auth::user();

                    if (
                        $user->isTechnicalOfficer()
                        ||
                        $user->isAssignOfficer()
                        ||
                        $user->isItAdministrator()
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | Generate Current Notifications
                        |--------------------------------------------------------------------------
                        */

                        $notifications =
                            NotificationService::forUser(
                                $user
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Give Every Notification a Stable Unique Key
                        |--------------------------------------------------------------------------
                        |
                        | Example stages:
                        |
                        | Due Soon
                        | Due Tomorrow
                        | Due Today
                        | Overdue
                        |
                        | The title is included in the key, therefore:
                        |
                        | Due Soon being read does NOT hide a future
                        | Due Tomorrow notification for the same job.
                        |
                        */

                        $notifications = collect(
                            $notifications
                        )
                            ->map(
                                function ($notification) {

                                    $dateValue = '';

                                    if (
                                        ! empty(
                                            $notification['date']
                                        )
                                    ) {

                                        if (
                                            $notification['date']
                                            instanceof DateTimeInterface
                                        ) {

                                            $dateValue =
                                                $notification['date']
                                                    ->format(
                                                        'Y-m-d H:i:s'
                                                    );

                                        } else {

                                            $dateValue =
                                                (string)
                                                $notification['date'];
                                        }
                                    }


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Use URL path instead of full domain
                                    |--------------------------------------------------------------------------
                                    |
                                    | This keeps the notification key stable when
                                    | using localhost, LAN IP, etc.
                                    |
                                    */

                                    $notificationUrl =
                                        $notification['url']
                                        ?? '';

                                    $notificationPath =
                                        parse_url(
                                            $notificationUrl,
                                            PHP_URL_PATH
                                        )
                                        ?? $notificationUrl;


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Build Notification Key
                                    |--------------------------------------------------------------------------
                                    */

                                    $notification[
                                        'notification_key'
                                    ] = hash(
                                        'sha256',
                                        implode(
                                            '|',
                                            [
                                                $notification[
                                                    'type'
                                                ] ?? '',

                                                $notification[
                                                    'title'
                                                ] ?? '',

                                                $notificationPath,

                                                $dateValue,
                                            ]
                                        )
                                    );


                                    return $notification;
                                }
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Get Notifications Already Read by this User
                        |--------------------------------------------------------------------------
                        */

                        $readKeys =
                            NotificationRead::where(
                                'user_id',
                                $user->id
                            )
                            ->pluck(
                                'notification_key'
                            )
                            ->flip();


                        /*
                        |--------------------------------------------------------------------------
                        | Show Only Unread Notifications
                        |--------------------------------------------------------------------------
                        */

                        $notifications =
                            $notifications
                                ->reject(
                                    function (
                                        $notification
                                    ) use ($readKeys) {

                                        return $readKeys
                                            ->has(
                                                $notification[
                                                    'notification_key'
                                                ]
                                            );
                                    }
                                )
                                ->values()
                                ->all();
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Send Notifications to Navbar
                |--------------------------------------------------------------------------
                */

                $view->with(
                    'navbarNotifications',
                    $notifications
                );


                /*
                |--------------------------------------------------------------------------
                | Unread Count Only
                |--------------------------------------------------------------------------
                */

                $view->with(
                    'navbarNotificationCount',
                    count($notifications)
                );
            }
        );
    }
}