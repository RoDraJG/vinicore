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
        // 🏢 1. Globalen Standard-Betrieb mit der ID 1 in den Einstellungen anlegen
        DB::table('betriebseinstellungen')->updateOrInsert(
            ['betrieb_id' => 1],
            [
                'vier_augen_kataster' => false,
                'standard_allokation' => 'modell_a',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // 👤 2. Deinen unzerstörbaren HAUPTNUTZER-Zugang über Username erschaffen
        DB::table('users')->updateOrInsert(
            ['username' => 'admin_winzer'], // 🎯 Dein neuer, eindeutiger Login-Name!
            [
                'betrieb_id' => 1,
                'name' => 'JG',
                'password' => Hash::make('vinicore2026!'), // Dein sicheres Passwort
                'is_hauptnutzer' => true, // 🛡️ Erhält als einziger das Recht zur Nutzerverwaltung!
                'erlaubte_gemarkungen' => json_encode(['*']),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }
}

