<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Esegue il controllo della posta ogni 5 minuti
Schedule::command('emails:fetch --limit=50')->everyFiveMinutes();

// Esegue la scansione dei bounce ogni ora
Schedule::command('emails:process-bounces')->hourly();
