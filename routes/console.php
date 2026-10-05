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

// SLA 72h Data Breach: controllo frequente vista la finestra stretta (Art. 33 GDPR)
Schedule::command('breach:deadline-check')->hourly()->withoutOverlapping();

// Data Retention: applica anonimizzazione/cancellazione automatica alla scadenza (Art. 5.1.e)
Schedule::command('retention:enforce')->dailyAt('03:00')->withoutOverlapping();

// Filiera fornitori: promemoria su audit e DPA in scadenza (Art. 28 GDPR)
Schedule::command('vendor:audit-reminders')->dailyAt('08:30');

// Salute delle caselle di posta ogni 6 ore
Schedule::command('mail:health-check')->everySixHours();
