<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemEinstellungenSeeder extends Seeder
{
    public function run(): void
    {
        $einstellungen = [
            [
                'schluessel' => 'farbe_eigentum',
                'wert' => '#059669', // Sattes Smaragd-Grün
                'beschreibung' => 'HEX-Farbcode für Eigentumsflächen auf der Katasterkarte',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'schluessel' => 'farbe_gepachtet',
                'wert' => '#2563eb', // Kräftiges Königs-Blau
                'beschreibung' => 'HEX-Farbcode für gepachtete Flächen auf der Katasterkarte',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'schluessel' => 'farbe_verpachtet',
                'wert' => '#64748b', // Neutrales Schiefer-Grau
                'beschreibung' => 'HEX-Farbcode für verpachtete Eigenflächen auf der Katasterkarte',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Verhindert Duplikate, falls der Seeder mehrfach ausgeführt wird
        foreach ($einstellungen as $einstellung) {
            DB::table('system_einstellungen')->updateOrInsert(
                ['schluessel' => $einstellung['schluessel']],
                $einstellung
            );
        }
    }
}
