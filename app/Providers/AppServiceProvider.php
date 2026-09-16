<?php

namespace App\Providers;

use App\Services\NotificationService;
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

                        $notifications =
                            NotificationService::forUser(
                                $user
                            );
                    }
                }


                $view->with(
                    'navbarNotifications',
                    $notifications
                );

                $view->with(
                    'navbarNotificationCount',
                    count($notifications)
                );
            }
        );
    }
}