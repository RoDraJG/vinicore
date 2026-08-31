<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registriert die betrieblichen ERP-Berechtigungen.
     * 🚀 CORE-FIX: Native DB-Abfragen verhindern jeglichen Namespace- oder Reflection-Absturz!
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability, $arguments = []) {
            if (!$user) {
                return null;
            }

            $roleId = $user->vinicore_rolle_id ?? null;

            if ($roleId !== null) {
                $roleName = DB::table('vinicore_rollen')->where('id', $roleId)->value('name');

                if ($roleName === 'admin') {
                    return true;
                }
            }

            return null;
        });

        Gate::define('check-permission', function ($user, $permissionSlug) {
            if (!$user) {
                return false;
            }

            $roleId = $user->vinicore_rolle_id ?? null;

            if ($roleId === null) {
                return false;
            }

            return DB::table('berechtigung_rolle as br')
                ->join('vinicore_berechtigungen as b', 'b.id', '=', 'br.berechtigung_id')
                ->where('br.rolle_id', $roleId)
                ->where('b.slug', $permissionSlug)
                ->exists();
        });
    }

}

