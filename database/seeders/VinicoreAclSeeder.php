<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VinicoreAclSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Standard-Rollen in 'vinicore_rollen' einspielen
        $rollenIds = [];
        $rollen = [
            'admin' => 'Administrator',
            'winzer' => 'Winzer / Betriebsleiter',
            'saisonkraft' => 'Saisonkraft / Lesehelfer'
        ];

        foreach ($rollen as $name => $anzeigeName) {
            $id = DB::table('vinicore_rollen')->updateOrInsert(
                ['name' => $name],
                [
                    'anzeige_name' => $anzeigeName,
                    'beschreibung' => "Standard-Systemrolle für {$anzeigeName}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
            // Holt die ID für die spätere Rechteverknüpfung
            $rollenIds[$name] = DB::table('vinicore_rollen')->where('name', $name)->value('id');
        }

        // 2. Ersten System-Rechte-Katalog in 'vinicore_berechtigungen' anlegen
        $rechte = [
            ['slug' => 'kataster_ansehen', 'modul' => 'kataster', 'aktion' => 'ansehen'],
            ['slug' => 'kataster_bearbeiten', 'modul' => 'kataster', 'aktion' => 'bearbeiten'],
            ['slug' => 'finanzen_verwalten', 'modul' => 'finanzen', 'aktion' => 'verwalten'],
        ];

        $rechteIds = [];
        foreach ($rechte as $recht) {
            DB::table('vinicore_berechtigungen')->updateOrInsert(
                ['slug' => $recht['slug']],
                array_merge($recht, ['created_at' => now(), 'updated_at' => now()])
            );
            $rechteIds[$recht['slug']] = DB::table('vinicore_berechtigungen')->where('slug', $recht['slug'])->value('id');
        }

        // 3. Rechte mit den Rollen verknüpfen in 'berechtigung_rolle'
        // Der Winzer darf Kataster ansehen und bearbeiten
        if (isset($rollenIds['winzer'])) {
            DB::table('berechtigung_rolle')->updateOrInsert([
                'rolle_id' => $rollenIds['winzer'],
                'berechtigung_id' => $rechteIds['kataster_ansehen']
            ]);
            DB::table('berechtigung_rolle')->updateOrInsert([
                'rolle_id' => $rollenIds['winzer'],
                'berechtigung_id' => $rechteIds['kataster_bearbeiten']
            ]);
        }

        // Die Saisonkraft darf das Kataster NUR ansehen
        if (isset($rollenIds['saisonkraft'])) {
            DB::table('berechtigung_rolle')->updateOrInsert([
                'rolle_id' => $rollenIds['saisonkraft'],
                'berechtigung_id' => $rechteIds['kataster_ansehen']
            ]);
        }
    }
}
