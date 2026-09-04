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

        // Alle Zählwerke geladen und nach Modul & Kreis-Key gruppiert
        $allKreise = CRMEinstellung::where('typ', 'nummernkreis')->orderBy('gueltig_von', 'asc')->get();
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

        // 2. Neuen historisierten Zeitraum hinzufügen
        if ($request->filled('neu.kreis_key')) {
            CRMEinstellung::create([
                'typ' => 'nummernkreis',
                'modul_key' => $request->input('neu.modul_key'),
                'kreis_key' => $request->input('neu.kreis_key'),
                'label' => $request->input('neu.label'),
                'muster' => $request->input('neu.muster', '{ZAEHLER}'),
                'zaehlerstand' => intval($request->input('neu.zaehlerstand', 0)),
                'fuehrende_nullen' => intval($request->input('neu.fuehrende_nullen', 0)),
                'gueltig_von' => $request->input('neu.gueltig_von') ? Carbon::parse($request->input('neu.gueltig_von')) : null,
                'gueltig_bis' => $request->input('neu.gueltig_bis') ? Carbon::parse($request->input('neu.gueltig_bis')) : null,
            ]);
        }

        return redirect()->route('admin.einstellungen', ['tab' => 'nummernkreise'])->with('success', 'Parameter permanent versiegelt!');
    }
}
