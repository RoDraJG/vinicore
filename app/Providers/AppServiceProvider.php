<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 🛡️ 1. GLOBALER TÜRSTEHER FÜR ADMINISTRATOREN
        Gate::before(function (User $user, string $ability) {
            $rolle = DB::table('vinicore_rollen')
                ->where('id', $user->vinicore_rolle_id)
                ->first();

            if ($rolle && $rolle->name === 'admin') {
                return true;
            }
        });

        // 🔑 2. DYNAMISCHE BERECHTIGUNGS-PRÜFUNG FÜR SLUGS
        Gate::define('check-permission', function (User $user, string $permissionSlug) {
            if (!$user->vinicore_rolle_id) {
                return false;
            }

            return DB::table('berechtigung_rolle')
                ->join('vinicore_berechtigungen', 'berechtigung_rolle.berechtigung_id', '=', 'vinicore_berechtigungen.id')
                ->where('berechtigung_rolle.rolle_id', $user->vinicore_rolle_id)
                ->where('vinicore_berechtigungen.slug', $permissionSlug)
                ->exists();
        });

        // 🚀 3. ANMELDUNG DER MODULAREN CRM-VIEWS
            if (is_dir(app_path('Modules/CRM/Views'))) {
            $this->loadViewsFrom(app_path('Modules/CRM/Views'), 'CRM');
        }
    }
}

