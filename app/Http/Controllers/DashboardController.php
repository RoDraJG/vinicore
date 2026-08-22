<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Die zentrale Schaltstation des vinicore ERP.
 * 🚀 ARCHITEKTUR: Aggregiert modular die Datenströme aller Betriebsbereiche.
 */
class DashboardController extends Controller
{
       /**
     * Sammelt die Kennzahlen aller Module und rendert das Haupt-Dashboard.
     * 🚀 REPARATUR: Vollständig auf deine realen Tabellen 'parzellen' und 'schlaege' kalibriert!
     */
    public function index()
    {
        $user = auth()->user();

        // 🛡️ SECURITY-GUARD: Falls kein User eingeloggt ist, sofort zum Login umleiten!
        if (!$user) {
            return redirect()->route('login'); 
        }

        $betrieb = $user->aktuellerBetrieb;

        if (!$betrieb) {
            return redirect('/betrieb/anlegen')->with('error', 'Bitte legen Sie zuerst einen Betrieb an.');
        }

        // ==========================================================================
        // 🏛️ 1. DATA-PACKAGE: KATASTER (ALKIS) - Nutzt deine echten Migrations-Tabellen!
        // ==========================================================================
        // 🚀 CORE-FIX: Nutzt die reale Tabelle 'parzellen' und das Feld 'amtliche_flaeche_m2'
        $summeFlaecheM2 = \DB::table('parzellen')->sum('amtliche_flaeche_m2');
        $anzahlParzellen = \DB::table('parzellen')->count();
        
        // Berechnet den Hektarspiegel (m² geteilt durch 10.000)
        $summeHa = number_format($summeFlaecheM2 / 10000, 2, ',', '.');

        $kataster = [
            'anzahl'   => $anzahlParzellen,
            'summe_ha' => $summeHa
        ];

        // ==========================================================================
        // 1. 2. DATA-PACKAGE: AUSSENBETRIEB (Zieht die echten Daten deiner Schlag-Migration!)
        // ==========================================================================
        // 🚀 CORE-FIX: Nutzt deine reale Tabelle 'schlaege' und berechnet dort die Gesamtfläche
        $anzahlSchlaege = \DB::table('schlaege')->count();
        $summeSchlagHa = \DB::table('schlaege')->sum('flaeche_ha');
        
        $weinbau = [
            'anzahl_schlaege' => $anzahlSchlaege,
            'ertrag_prognose' => $anzahlSchlaege > 0 
                ? 'Bewirtschaftet: ' . number_format($summeSchlagHa, 2, ',', '.') . ' ha' 
                : 'Ertragsschätzung ausstehend'
        ];

        // ==========================================================================
        // 🍾 3. DATA-PACKAGE: INNENBETRIEB (Vorbereitetes Daten-Skelett)
        // ==========================================================================
        $keller = [
            'liter_im_ausbau'  => '42.500',
            'aktive_gaerungen' => 3
        ];

        // ==========================================================================
        // 💰 4. DATA-PACKAGE: FINANZEN & VERTRIEB (Vorbereitetes Daten-Skelett)
        // ==========================================================================
        $finanzen = [
            'monats_umsatz'     => '12.480',
            'offene_rechnungen' => 5
        ];

        return view('dashboard', compact('betrieb', 'kataster', 'weinbau', 'keller', 'finanzen'));
    }

}
