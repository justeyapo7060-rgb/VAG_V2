<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('app:terminer-cohortes')->everyMinute();

// Rappel de solde : le dernier jour de chaque mois, à 08:00.
Schedule::command('paiements:rappel')->lastDayOfMonth('08:00');
