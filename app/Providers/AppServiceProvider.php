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
        // Dynamischer ERP-Türsteher für globale Administratoren
        Gate::before(function ($user, $ability) {
            // Holt die Rolle absolut ausfallsicher direkt über die Pivot-Tabelle
            $istAdmin = DB::table('role_user')
                ->join('roles', 'roles.id', '=', 'role_user.role_id')
                ->where('role_user.user_id', $user->id)
                ->where('roles.slug', '=', 'admin')
                ->exists();

            if ($istAdmin) {
                return true;
            }
        });

        // Definiert die dynamische Berechtigungs-Prüfung für Module (z.B. @can('kataster.edit'))
        Gate::define('check-permission', function ($user, $permissionSlug) {
            return DB::table('role_user')
                ->join('permission_role', 'permission_role.role_id', '=', 'role_user.role_id')
                ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                ->where('role_user.user_id', $user->id)
                ->where('permissions.slug', '=', $permissionSlug)
                ->exists();
        });
    }

}

