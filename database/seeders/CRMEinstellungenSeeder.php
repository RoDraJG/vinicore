<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CRMEinstellungenSeeder extends Seeder
{
    public function run(): void
    {
        $daten = [
            // Kunden-Segmente
            ['typ' => 'segment', 'code' => 'privat', 'wert' => '🍷 Privatkunde / Endverbraucher', 'sortierung' => 1],
            ['typ' => 'segment', 'code' => 'gastro', 'wert' => '🍽️ Gastronomie & Hotellerie', 'sortierung' => 2],
            ['typ' => 'segment', 'code' => 'handel', 'wert' => '🛒 Groß- & Fachhandel', 'sortierung' => 3],

            // Steuer-Zonen
            ['typ' => 'steuerzone', 'code' => 'inland', 'wert' => '🇩🇪 Inland (Normal)', 'sortierung' => 1],
            ['typ' => 'steuerzone', 'code' => 'eu_steuerfrei', 'wert' => '🇪🇺 EU (Steuerfrei / Reverse-Charge)', 'sortierung' => 2],
            ['typ' => 'steuerzone', 'code' => 'drittland', 'wert' => '🌐 Drittland (Export z.B. USA/Schweiz)', 'sortierung' => 3],

            // Incoterms
            ['typ' => 'incoterm', 'code' => 'ab_hof', 'wert' => '🏡 Ab Hof', 'sortierung' => 1],
            ['typ' => 'incoterm', 'code' => 'frei_haus', 'wert' => '🚚 Frei Haus', 'sortierung' => 2],
            ['typ' => 'incoterm', 'code' => 'dhl', 'wert' => '📦 Paketdienst', 'sortierung' => 3],

            // Logistiker
            ['typ' => 'logistiker', 'code' => 'dhl', 'wert' => 'DHL Paket', 'sortierung' => 1],
            ['typ' => 'logistiker', 'code' => 'ups', 'wert' => 'UPS Express', 'sortierung' => 2],
            ['typ' => 'logistiker', 'code' => 'spedition', 'wert' => 'Haus-Spedition', 'sortierung' => 3],
            ['typ' => 'logistiker', 'code' => 'eigen', 'wert' => 'Eigen-Fahrzeug', 'sortierung' => 4],

            // Weinstilistiken
            ['typ' => 'stilistik', 'code' => 'trocken', 'wert' => 'Weißwein trocken', 'sortierung' => 1],
            ['typ' => 'stilistik', 'code' => 'feinherb', 'wert' => 'Feinherb & Halbtrocken', 'sortierung' => 2],
            ['typ' => 'stilistik', 'code' => 'edelsuess', 'wert' => 'Frucht- & Edelsüß', 'sortierung' => 3],
            ['typ' => 'stilistik', 'code' => 'rotwein', 'wert' => 'Kräftiger Rotwein', 'sortierung' => 4],

            // Akquise-Kanäle
            ['typ' => 'kanal', 'code' => 'direkt', 'wert' => 'Hofbesuch / Vinothek', 'sortierung' => 1],
            ['typ' => 'kanal', 'code' => 'messe', 'wert' => 'Weinmesse / Präsentation', 'sortierung' => 2],
            ['typ' => 'kanal', 'code' => 'online', 'wert' => 'Online-Shop', 'sortierung' => 3],
            ['typ' => 'kanal', 'code' => 'empfehlung', 'wert' => 'Mundpropaganda', 'sortierung' => 4],

            // 🎯 NEU: System-Nummernkreise für Kunden und Lieferanten (Vollständig dynamisch anpassbar)
            ['typ' => 'nummernkreis', 'code' => 'kunde', 'wert' => '10000', 'sortierung' => 1],
            ['typ' => 'nummernkreis', 'code' => 'lieferant', 'wert' => '50000', 'sortierung' => 2],

        ];

        foreach ($daten as $zeile) {
            DB::table('crm_einstellungen')->updateOrInsert(
                ['typ' => $zeile['typ'], 'code' => $zeile['code']],
                ['wert' => $zeile['wert'], 'sortierung' => $zeile['sortierung'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
