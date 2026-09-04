<?php

namespace App\Modules\Configuration\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Models\CRMEinstellung; // Nutzt dein vorhandenes Modell
use Illuminate\Http\Request;
use Carbon\Carbon;

class GlobalConfigController extends Controller
{
    /**
     * Baut das zentrale Einstellungs-Cockpit im Configuration-Modul auf
     */
    public function index(Request $request)
    {
        if (!auth()->user() || (!auth()->user()->can('betrieb verwalten') && !auth()->user()->can('nummernkreise bearbeiten') && !auth()->user()->can('dropdowns verwalten'))) {
            abort(403, 'ZUGRIFF VERWEIGERT.');
        }

        // 🎯 DYNAMISCHER START-TAB: Öffnet das erste Register, für das der Winzer reale Rechte besitzt
        $aktivesTab = $request->get('tab');
        if (empty($aktivesTab)) {
            if (auth()->user()->can('betrieb verwalten')) { $aktivesTab = 'betrieb'; }
            elseif (auth()->user()->can('nummernkreise bearbeiten')) { $aktivesTab = 'nummernkreise'; }
            else { $aktivesTab = 'dropdowns'; }
        }

        // Absicherung der einzelnen Tabs nach Rechten
        if ($aktivesTab === 'betrieb' && !auth()->user()->can('betrieb verwalten')) abort(403);
        if ($aktivesTab === 'nummernkreise' && !auth()->user()->can('nummernkreise bearbeiten')) abort(403);
        if ($aktivesTab === 'dropdowns' && !auth()->user()->can('dropdowns verwalten')) abort(403);

        // STECKBRIEF-PFLEGE: Hier definierst du zentral, welche Zählwerke es im ERP geben soll
        $modulSteckbriefe = [
            'crm' => [
                'name' => 'CRM & Partnerverwaltung',
                'kreise' => [
                    'kunde' => ['label' => 'Kunden-Muster', 'default_muster' => 'K-{ZAEHLER}', 'default_zaehler' => 10000],
                    'lieferant' => ['label' => 'Lieferanten-Muster', 'default_muster' => 'L-{ZAEHLER}', 'default_zaehler' => 50000],
                ]
            ],
            'keller' => [
                'name' => 'Kellerwirtschaft & Weinlager',
                'kreise' => [
                    'wein' => ['label' => 'Wein- / AP-Nummern-Muster', 'default_muster' => '{ZAEHLER}/{JJ}', 'default_zaehler' => 0]
                ]
            ]
        ];

        // Automatische Selbstheilung fehlender Zählwerke in der Datenbank
        foreach ($modulSteckbriefe as $modulKey => $modulInfo) {
            foreach ($modulInfo['kreise'] as $kreisKey => $default) {
                if (!CRMEinstellung::where('typ', 'nummernkreis')->where('kreis_key', $kreisKey)->exists()) {
                    CRMEinstellung::create([
                        'typ' => 'nummernkreis',
                        'code' => $kreisKey, 
                        'wert' => strval($default['default_zaehler']),
                        'modul_key' => $modulKey,
                        'kreis_key' => $kreisKey,
                        'label' => $default['label'],
                        'muster' => $default['default_muster'],
                        'zaehlerstand' => $default['default_zaehler'],
                        'fuehrende_nullen' => 0,
                        'gueltig_von' => null,
                        'gueltig_bis' => null,
                        'sortierung' => 0
                    ]);
                }
            }
        }
                // 🎯 SYSTEM-DROPDOWN: Automatische Selbstheilung für die globalen Anreden
        $standardAnreden = [
            'herr' => 'Herr',
            'frau' => 'Frau',
            'familie' => 'Familie',
            'firma' => 'Firma'
        ];

        foreach ($standardAnreden as $code => $wert) {
            if (!CRMEinstellung::where('typ', 'anrede')->where('code', $code)->exists()) {
                CRMEinstellung::create([
                    'typ' => 'anrede',
                    'code' => $code,
                    'wert' => $wert,
                    'sortierung' => 0
                ]);
            }
        }

        // Alle Zählwerke laden und nach Gültigkeit markieren
        $allKreise = CRMEinstellung::where('typ', 'nummernkreis')
            ->orderBy('gueltig_von', 'asc')
            ->get()
            ->map(function ($kreis) {
                // 🎯 NEU: Prüft mathematisch, ob das Zeitfenster bereits abgelaufen ist
                $kreis->ist_historisch = $kreis->gueltig_bis && $kreis->gueltig_bis->isPast();
                return $kreis;
            });

        $nummernkreise = $allKreise->groupBy(['modul_key', 'kreis_key']);


        // 🎯 GIS-FARBEN: Lädt die gespeicherten Kartenfarben aus der Tabelle für das Register
        $einstellungen = CRMEinstellung::whereIn('typ', ['kartenfarbe', 'system'])
            ->pluck('wert', 'code')
            ->toArray();

        return view('Configuration::einstellungen_hub', compact('aktivesTab', 'modulSteckbriefe', 'nummernkreise', 'einstellungen'));
    }

    /**
     * Speichert Modifikationen und erlaubt das Hinzufügen neuer historisierter Zeiträume
     */
    public function speichereNummernkreise(Request $request)
    {
        if (!auth()->user() || !auth()->user()->can('nummernkreise bearbeiten')) abort(403);

        // 1. Bestehende Zeiträume aktualisieren oder löschen
        $konfig = $request->input('kreis', []);
        foreach ($konfig as $id => $daten) {
            $kreis = CRMEinstellung::find($id);
            if ($kreis) {
                if (isset($daten['loeschen'])) {
                    $kreis->delete();
                } else {
                    $kreis->update([
                        'muster' => $daten['muster'] ?? '{ZAEHLER}',
                        'zaehlerstand' => intval($daten['zaehlerstand']),
                        'fuehrende_nullen' => intval($daten['fuehrende_nullen'] ?? 0),
                        'gueltig_von' => $daten['gueltig_von'] ? Carbon::parse($daten['gueltig_von']) : null,
                        'gueltig_bis' => $daten['gueltig_bis'] ? Carbon::parse($daten['gueltig_bis']) : null,
                    ]);
                }
            }
        }

        // ======================================================================
        // 2. NEUE HISTORISIERTE ZEITRÄUME (SCHLEIFEN-GETRIEBE FÜR KREIS-KEYS)
        // ======================================================================
        $neueKreise = $request->input('neu', []);
        
        if (is_array($neueKreise)) {
            foreach ($neueKreise as $kreisKey => $daten) {
                // Ein neuer Zeitraum wird NUR dann validiert und gebaut, wenn real ein Zählerstand eingetippt wurde!
                if (!empty($daten['zaehlerstand']) || $daten['zaehlerstand'] === '0' || !empty($daten['muster'])) {
                    
                    $modulKey = $daten['modul_key'] ?? 'crm';
                    
                    // Prüfen, ob für dieses spezifische Zählwerk bereits Einträge in der DB existieren
                    $existiertBereits = \App\Modules\CRM\Models\CRMEinstellung::where('typ', 'nummernkreis')
                        ->where('kreis_key', $kreisKey)
                        ->exists();

                    // Wenn bereits ein Kreis da ist, wird das "Gültig von" zum harten Pflichtfeld!
                    $regeln = [
                        'zaehlerstand' => 'required|integer|min:0',
                        'muster' => 'nullable|string|max:255',
                        'gueltig_von' => $existiertBereits ? 'required|date' : 'nullable|date',
                        'gueltig_bis' => 'nullable|date',
                    ];

                    // Validierung für diesen spezifischen Kreis-Datensatz ausführen
                    $validator = \Illuminate\Support\Facades\Validator::make($daten, $regeln);
                    if ($validator->fails()) {
                        return redirect()->back()->withErrors($validator)->withInput();
                    }

                    $startZaehler = intval($daten['zaehlerstand']);
                    $musterFallback = !empty($daten['muster']) ? $daten['muster'] : '{ZAEHLER}';

                    // 🎯 UNZERSTÖRBAR: Wenn "Gültig von" leer ist, greift das System auf das heutige Datum (Carbon::now) zurück
                    $vonInput = $daten['gueltig_von'] ?? null;
                    $gueltigVon = !empty($vonInput) ? Carbon::parse($vonInput) : Carbon::now()->startOfDay();

                    // Wenn "Gültig bis" leer ist, gilt es unbegrenzt bis zum fernen Zukunftshorizont
                    $bisInput = $daten['gueltig_bis'] ?? null;
                    $gueltigBis = !empty($bisInput) ? Carbon::parse($bisInput) : Carbon::parse('2099-12-31 23:59:59');


                    // Datums-Parameter bestimmen: Leer = für immer (Carbon::now bis 2099)
                    $gueltigVon = !empty($daten['gueltig_von']) ? Carbon::parse($daten['gueltig_von']) : Carbon::now();
                    $gueltigBis = !empty($daten['gueltig_bis']) ? Carbon::parse($daten['gueltig_bis']) : Carbon::parse('2099-12-31 23:59:59');

                    // 🎯 REVISIONS-SCHUTZ: Vorherigen offenen Zeitraum suchen und auf HEUTE deckeln!
                    // Findet den bisher unbegrenzten oder aktuellsten Eintrag desselben Zählwerks
                    \App\Modules\CRM\Models\CRMEinstellung::where('typ', 'nummernkreis')
                        ->where('kreis_key', $kreisKey)
                        ->where('gueltig_bis', '>=', Carbon::now()->endOfDay())
                        ->get()
                        ->each(function ($altesFenster) {
                            $altesFenster->update([
                                // 📅 Wird auf den heutigen Tag am absoluten Ende der Sekunde gesetzt
                                'gueltig_bis' => Carbon::now()->endOfDay()
                            ]);
                        });

                    // Neuen historisierten Zeitraum sauber einfügen
                    \App\Modules\CRM\Models\CRMEinstellung::create([
                        'typ' => 'nummernkreis',
                        'code' => $kreisKey, 

                        'wert' => strval($startZaehler),
                        'modul_key' => $modulKey,
                        'kreis_key' => $kreisKey,
                        'label' => $daten['label'] ?? 'Zählwerk ' . $kreisKey,
                        'muster' => $musterFallback,
                        'zaehlerstand' => $startZaehler,
                        'fuehrende_nullen' => 0,
                        'gueltig_von' => $gueltigVon,
                        'gueltig_bis' => $gueltigBis,
                        'sortierung' => 0
                    ]);
                }
            }
        }

        // Rücksprung zielt punktgenau auf den success-Flash-Key deiner globalen app.blade.php
        return redirect()->route('admin.einstellungen', ['tab' => 'nummernkreise'])
            ->with('success', 'Parameter permanent versiegelt und Zeitfenster harmonisiert!');
    }
        /**
     * 🎯 NEU: Versiegelt die visuellen GIS-Kartenfarben in der crm_einstellungen Tabelle
     */
    public function speichereBetriebsdaten(Request $request)
    {
        if (!auth()->user() || !auth()->user()->can('betrieb verwalten')) abort(403);

        $farbFelder = ['farbe_eigentum', 'farbe_gepachtet', 'farbe_verpachtet'];

        foreach ($farbFelder as $feld) {
            if ($request->has($feld)) {
                // Sichert oder aktualisiert den Farbwert in deiner Universal-Tabelle
                \App\Modules\CRM\Models\CRMEinstellung::updateOrCreate(
                    ['typ' => 'kartenfarbe', 'code' => $feld],
                    [
                        'wert' => $request->input($feld),
                        'sortierung' => 0,
                        'modul_key' => 'kataster',
                        'kreis_key' => null,
                        'muster' => '{ZAEHLER}',
                        'zaehlerstand' => 0,
                        'fuehrende_nullen' => 0,
                        'gueltig_von' => null,
                        'gueltig_bis' => null
                    ]
                );
            }
        }

        return redirect()->route('admin.einstellungen', ['tab' => 'betrieb'])->with('success', 'Visuelle GIS-Kartenparameter erfolgreich im ERP versiegelt!');
    }

}
