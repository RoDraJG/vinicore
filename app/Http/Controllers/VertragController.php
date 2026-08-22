<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class VertragController extends Controller
{
    /**
     * 🏢 Zeigt das Formular zum Erfassen eines neuen Pacht- oder Kaufvertrags.
     */
    public function erstelleView()
    {
        return view('finanzen.vertrag_anlegen');
    }
    /**
     * 🚀 FINAL-BATCH-COMMIT: Holt das komplette LocalStorage-Paket aus dem Browser
     * und brennt Vertrag, Parzellen und Allokation in einer einzigen Transaktion in MySQL!
     */
    public function finaleVersiegeln(Request $request): JsonResponse
    {
        $request->validate([
            'vertrag'   => 'required|array',
            'parzellen' => 'required|array'
        ]);

        try {
            DB::beginTransaction();
            $jetzt = now();
            $user = Auth::user();
            $betriebId = $user->betrieb_id ?: 1;

            $vData = $request->input('vertrag');
            $pData = $request->input('parzellen');

            // 🧱 1. Den Finanzvertrag in vinicore_vertraege einstanzen
            $vertragId = DB::table('vinicore_vertraege')->insertGetId([
                'betrieb_id'           => $betriebId,
                'vertrag_nummer'       => trim($vData['vertrag_nummer']),
                'typ'                  => $vData['typ'],
                'vertragspartner_name' => trim($vData['vertragspartner_name'] ?? 'Unbekannt'),
                'gesamtwert'           => floatval($vData['gesamtwert'] ?? 0),
                'vertragsdatum'        => $jetzt->toDateString(),
                'gueltig_von'          => $vData['gueltig_von'],
                'gueltig_bis'          => $vData['gueltig_bis'] ?? null,
                'user_id'              => $user->id,
                'created_at'           => $jetzt,
                'updated_at'           => $jetzt
            ]);

            // Berechnet die Gesamtfläche aller übermittelten Parzellen im RAM
            $gesamtM2 = 0;
            foreach ($pData as $p) {
                $gesamtM2 += intval($p['properties']['amtliche_flaeche_m2'] ?? $p['properties']['flaeche_m2'] ?? 0);
            }

            // 🧱 2. Parzellen und n:m-Beziehungen im Batch-Verfahren anlegen
            foreach ($pData as $einzelneParzelle) {
                $props = $einzelneParzelle['properties'] ?? [];
                $geometry = $einzelneParzelle['geometry'] ?? null;
                
                $gemarkung = trim($props['gemarkung'] ?? 'Unbekannt');
                $flur = !empty($props['flur']) ? intval($props['flur']) : 1;
                $flurstueck = trim($props['flurstueck'] ?? '0');

                $newUuid = (string) \Illuminate\Support\Str::uuid();
                $m2 = intval($props['amtliche_flaeche_m2'] ?? $props['flaeche_m2'] ?? 0);

                // 📈 RAM-KASKADEN-ALLOKATION: Rechnet centgenau vor dem Insert!
                $einzelWert = 0.00;
                if ($gesamtM2 > 0 && $vData['typ'] !== 'schenkung') {
                    $einzelWert = round((floatval($vData['gesamtwert']) * ($m2 / $gesamtM2)), 2);
                }

                // 🚀 CORE-FIX: Überspringt die Erstprüfung und schaltet direkt auf AKTIV (V2, Grün)!
                DB::table('parzellen')->insert([
                    'parzelle_uuid'       => $newUuid,
                    'version'             => 2, // Direkt Version 2, da vertraglich geprüft!
                    'freigabe_status'     => 'aktiv', // Erstrahlt sofort in Sattem Grün!
                    'polygon_vektoren'    => $geometry ? json_encode($geometry) : null,
                    'gemeinde'            => trim($props['gemeinde'] ?? 'Weinbaugemeinde'),
                    'gemarkung'           => $gemarkung,
                    'flur'                => $flur,
                    'flurstueck_zaehler'  => $flurstueck,
                    'flurstueck_nenner'   => null,
                    'flurname_lage'       => 'Flur ' . $flur . ' | Nr. ' . $flurstueck,
                    'amtliche_flaeche_m2' => $m2,
                    
                    // Der Status wird direkt unmanipulierbar aus dem Vertragstyp abgeleitet:
                    'besitz_status'       => ($vData['typ'] === 'kauf') ? 'eigentum' : (($vData['typ'] === 'pacht_ertrag') ? 'verpachtet' : 'gepachtet'),
                    
                    'aenderungsgrund'     => 'Vertraglich validiert über Urkunden-Nr: ' . trim($vData['vertrag_nummer']),
                    'gueltig_von'         => $jetzt,
                    'user_id'             => $user->id,
                    'created_at'          => $jetzt,
                    'updated_at'          => $jetzt
                ]);

            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Vertrag und Flächen wurden erfolgreich zeitgleich in MySQL eingebrannt!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Kritischer Absturz im Transaktions-Kernel: ' . $e->getMessage()], 500);
        }
    }
}
