<?php

namespace App\Http\Controllers;

use App\Models\NotificationRead;
use Illuminate\Http\Request;

class NotificationReadController extends Controller
{
    /**
     * Mark one notification as read
     * and redirect the user to its related page.
     */
    public function markAsRead(Request $request)
    {
        $user = $request->user();

        abort_unless(
            $user->isTechnicalOfficer()
            || $user->isAssignOfficer()
            || $user->isItAdministrator(),
            403
        );

        $data = $request->validate([
            'notification_key' => [
                'required',
                'string',
                'size:64',
            ],

            'redirect_url' => [
                'required',
                'string',
                'max:2048',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Save notification as read
        |--------------------------------------------------------------------------
        */

        NotificationRead::updateOrCreate(
            [
                'user_id' =>
                    $user->id,

                'notification_key' =>
                    $data['notification_key'],
            ],
            [
                'read_at' =>
                    now(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Safe Redirect
        |--------------------------------------------------------------------------
        |
        | Notification links in this system currently lead to:
        | - /requests/{id}
        | - /escalations/{id}
        |
        | We redirect using the local URL path instead of trusting
        | an arbitrary external URL.
        |
        */

        $path = parse_url(
            $data['redirect_url'],
            PHP_URL_PATH
        );

        $query = parse_url(
            $data['redirect_url'],
            PHP_URL_QUERY
        );

        abort_unless(
            is_string($path)
            &&
            (
                str_starts_with($path, '/requests/')
                ||
                str_starts_with($path, '/escalations/')
            ),
            400,
            'Invalid notification destination.'
        );

        $target = $path;

        if (! empty($query)) {
            $target .= '?' . $query;
        }

        return redirect($target);
    }
}