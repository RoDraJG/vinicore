<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * 🚀 INSTALLATIONS-INJEKTION: Synchronisiert mit deiner 'betriebsdaten'-Migration.
     */
    public function run(): void
    {

        // 1. ANLEGEN DES ADMIN-WINZERS MIT VOR- UND NACHNAME
        $adminUser = User::create([
            'username' => 'JG',
            'vorname' => 'J',    // 🚀 Dein echter Vorname
            'nachname' => 'G',    // 🚀 Dein echter Nachname
            'email' => 'admin@vinicore.de',
            'password' => Hash::make('Weinbau2026!'),
            'email_verified_at' => now(),
        ]);



        // 2. VERKNÜPFUNG MIT DEM BEREITS EXISTIERENDEN WEINGUT (ID: 1)
        // Da deine Migration den Datensatz schon anlegt, verknüpfen wir ihn hier direkt!
        DB::table('betrieb_user')->insert([
            'user_id' => $adminUser->id,
            'betrieb_id' => 1, // ID des von deiner Migration erzeugten Weinguts
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
