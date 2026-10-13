<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('fixit:send-technical-overdue')
    ->dailyAt('09:35')
    ->timezone('Asia/Colombo')
    ->withoutOverlapping();

Schedule::command('fixit:send-assign-overdue')
    ->dailyAt('09:35')
    ->timezone('Asia/Colombo')
    ->withoutOverlapping();

Schedule::command('fixit:send-admin-overdue-summary')
    ->dailyAt('09:35')
    ->timezone('Asia/Colombo')
    ->withoutOverlapping();