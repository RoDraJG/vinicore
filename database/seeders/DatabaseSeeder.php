<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * 🚀 VINICORE MASTER-SEEDER
     * Initialisiert alle Core-Parameter, den Betriebssitz und deinen Hauptnutzer.
     */
    public function run(): void
    {
        // 1. Erst die Basistabellen befüllen (Rollen und Kartenfarben)
        $this->call([
            VinicoreAclSeeder::class,
            SystemEinstellungenSeeder::class,
        ]);

        // 🏢 2. Globalen Standard-Betrieb mit der ID 1 UND BETRIEBSSITZ anlegen
        DB::table('betriebseinstellungen')->updateOrInsert(
            ['betrieb_id' => 1],
            [
                'betriebs_name' => 'Weingut vinicore Hauptsitz',
                'latitude' => 49.8163,  // Zentriert das Satellitenbild exakt über deinem Hof
                'longitude' => 6.8837,
                'zoom_stufe' => 16,
                'vier_augen_kataster' => false,
                'standard_allokation' => 'modell_a',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // 👤 3. Deinen unzerstörbaren HAUPTNUTZER-Zugang über Username erschaffen
        DB::table('users')->updateOrInsert(
            ['username' => 'admin_winzer'], 
            [
                'betrieb_id' => 1,
                'name' => 'JG',
                'password' => Hash::make('vinicore2026!'), 
                'ist_aktiv' => true, 
                'erlaubte_gemarkungen' => json_encode(['*']),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // 🛡️ 4. EXAKTE ACL-ROLLE: Bindet den User über 'vinicore_rolle_id'
        $userId = DB::table('users')->where('username', 'admin_winzer')->value('id');
        $roleId = DB::table('vinicore_rollen')->where('name', 'admin')->value('id');

        if ($userId && $roleId) {
            DB::table('users')->where('id', $userId)->update([
                'vinicore_rolle_id' => $roleId
            ]);
        }
    }
}



