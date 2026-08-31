<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRoleId = DB::table('vinicore_rollen')->updateOrInsert(
            ['name' => 'admin'],
            [
                'anzeige_name' => 'Administrator',
                'beschreibung' => 'Systemadministrator',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        )['id'] ?? DB::table('vinicore_rollen')->where('name', 'admin')->value('id');

        $permissions = [
            'ernte_ansehen',
            'ernte_bearbeiten',
            'keller_ansehen',
            'keller_bearbeiten',
            'personal_ansehen',
            'personal_bearbeiten',
            'kataster_ansehen',
            'kataster_bearbeiten',
        ];

        foreach ($permissions as $slug) {
            $parts = explode('_', $slug);
            $module = $parts[0];
            $action = $parts[1] ?? 'ansehen';

            $permissionId = DB::table('vinicore_berechtigungen')->updateOrInsert(
                ['slug' => $slug],
                [
                    'modul' => $module,
                    'aktion' => $action,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            if (is_array($permissionId)) {
                $permissionId = $permissionId['id'] ?? DB::table('vinicore_berechtigungen')->where('slug', $slug)->value('id');
            }

            DB::table('berechtigung_rolle')->updateOrInsert(
                ['rolle_id' => $adminRoleId, 'berechtigung_id' => $permissionId],
                ['rolle_id' => $adminRoleId, 'berechtigung_id' => $permissionId]
            );
        }

        DB::table('betriebseinstellungen')->updateOrInsert(
            ['betrieb_id' => 1],
            [
                'vier_augen_kataster' => false,
                'standard_allokation' => 'modell_a',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $userId = DB::table('users')->updateOrInsert(
            ['username' => 'admin_winzer'],
            [
                'betrieb_id' => 1,
                'name' => 'JG',
                'password' => Hash::make('vinicore2026!'),
                'is_hauptnutzer' => true,
                'vinicore_rolle_id' => $adminRoleId,
                'ist_aktiv' => true,
                'erlaubte_gemarkungen' => json_encode(['*']),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        if (is_array($userId)) {
            $userId = $userId['id'] ?? DB::table('users')->where('username', 'admin_winzer')->value('id');
        }
    }
}

