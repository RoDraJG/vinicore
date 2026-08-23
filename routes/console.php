<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// 🧼 AUTOMATISCHER ENTWURFS-REINIGER (Laravel 11)
// Fegt alle temporären UUID-Entwürfe, die älter als 60 Minuten sind, rückstandslos vom Server!
Schedule::call(function () {
    DB::table('vertrags_entwuerfe')
        ->where('created_at', '<', now()->subHours(1))
        ->delete(); // Kaskadiert automatisch in parzelle_entwurf
})->hourly();
