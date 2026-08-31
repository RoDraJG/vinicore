<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VinicoreAclSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('rollen')->insertOrIgnore([
            [
                'name' => 'admin',
                'anzeige_name' => 'Administrator',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'winzer',
                'anzeige_name' => 'Winzer / Betriebsleiter',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'saisonkraft',
                'anzeige_name' => 'Saisonkraft / Lesehelfer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
