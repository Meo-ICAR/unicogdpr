<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Accoda la scansione delle caselle email/PEC ogni 5 minuti
Schedule::command('emails:fetch')->everyFiveMinutes()->withoutOverlapping();

// Scansione delle caselle bounce ogni ora
Schedule::command('emails:process-bounces')->hourly()->withoutOverlapping();

// Retention della posta in arrivo, una volta al giorno
Schedule::command('inbox:prune')->dailyAt('02:30');

// Scadenzario DSAR: avvisa il DPO ogni mattina
Schedule::command('dsar:deadline-check')->dailyAt('08:00');

// Salute delle caselle di posta ogni 6 ore
Schedule::command('mail:health-check')->everySixHours();
