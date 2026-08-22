<?php

namespace App\Http\Controllers;

use App\Models\Anlage;
use App\Models\Pflanzmatrix;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MatrixController extends Controller
{
    /**
     * Route: /api/matrix/anlage/{anlageId}/topologie (GET)
     * Berechnet das geografische Mosaik und liefert die Vektorketten.
     */
    public function getAnlageTopologie(int $anlageId): JsonResponse
    {
        try {
            $anlage = Anlage::find($anlageId);
            if (!$anlage) {
                return response()->json(['success' => false, 'message' => 'Anlage nicht gefunden.'], 404);
            }

            // 1. Holt alle verknüpften ALKIS-Flurstücke mit ihren schrägen Geometrie-Polygonen
            $parzellenMosaik = DB::table('parzelle_anlage')
                ->join('parzellen', 'parzelle_anlage.parzelle_uuid', '=', 'parzellen.parzelle_uuid')
                ->where('parzelle_anlage.anlage_id', $anlageId)
                ->whereNull('parzellen.gueltig_bis')
                ->select([
                    'parzellen.flurstueck_zaehler', 'parzellen.flurstueck_nenner', 'parzellen.polygon_vektoren',
                    'parzelle_anlage.anteil_prozent', 'parzelle_anlage.pflanz_richtung'
                ])->get();

            // 2. Lädt den aktuellen Vektor-Zustand aus der Pflanzmatrix (bevorzugt den Entwurf!)
            $zeilenDaten = Pflanzmatrix::where('anlage_id', $anlageId)
                ->whereNull('gueltig_bis')
                ->orderBy('zeile_nummer')
                ->orderBy('position_index')
                ->get()
                ->groupBy('zeile_nummer');

            $topologie = [];
            foreach ($zeilenDaten as $zNr => $elemente) {
                $verlauf = [];
                foreach ($elemente as $el) {
                    $verlauf[] = [
                        'id' => $el->id,
                        'typ' => $el->objekt_typ,
                        'status' => $el->status,
                        'x' => floatval($el->x_meter),
                        'y' => floatval($el->y_meter),
                        'abstand_cm' => $el->abstand_zum_vorherigen_cm,
                        'eigenschaften' => [
                            'rebsorte' => $el->varietaet_rebsorte,
                            'klon' => $el->varietaet_klon,
                            'unterlage' => $el->varietaet_unterlage,
                            'unterlage_klon' => $el->varietaet_unterlage_klon,
                            'jahr' => $el->nachpflanz_jahr
                        ]
                    ];
                }
                $topologie[] = ['zeile_nummer' => $zNr, 'verlauf' => $verlauf];
            }

            // Liefert alle Parameter, damit das JavaScript den Trichter millimetergenau rendern kann
            return response()->json([
                'success' => true,
                'plan_status' => $anlage->plan_status,
                'locked_by' => $anlage->locked_by_user_id,
                'parameter' => [
                    'vorgewende_start_cm' => $anlage->vorgewende_start_cm,
                    'vorgewende_ende_cm' => $anlage->vorgewende_ende_cm,
                    'randabstand_links_cm' => $anlage->randabstand_links_cm,
                    'randabstand_rechts_cm' => $anlage->randabstand_rechts_cm,
                    'stockabstand_cm' => $anlage->stockabstand_cm
                ],
                'topologie' => $topologie,
                'parzellen_mosaik' => $parzellenMosaik
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    /**
     * Route: /api/matrix/speichern (POST)
     * Sichert den gezeichneten Zustand atomar als Entwurf ab.
     */
    public function speichereMatrixEntwurf(Request $request): JsonResponse
    {
        $request->validate([
            'anlage_id' => 'required|exists:anlagen,id',
            'matrix_daten' => 'required|array'
        ]);

        $anlageId = $request->anlage_id;

        DB::beginTransaction();
        try {
            // 1. Löscht ausschließlich die alten ENTWÜRFE dieser Anlage, berührt NIEMALS die aktive Historie!
            DB::table('pflanz_matrix')
                ->where('anlage_id', $anlageId)
                ->where('ist_entwurf', true)
                ->delete();

            // 2. Durchläuft alle vom Browser übermittelten Trichterzeilen-Koordinaten
            foreach ($request->matrix_daten as $zeileNummer => $elemente) {
                $indexCounter = 1;
                
                foreach ($elemente as $el) {
                    // Strikte Validierung: Unvollständige Vektordaten werden hart blockiert
                    if (!isset($el['x']) || !isset($el['y'])) {
                        throw new \Exception("Geometrischer Riss in Reihe {$zeileNummer} erkannt: Fehlende X/Y-Meter-Koordinaten!");
                    }

                    DB::table('pflanz_matrix')->insert([
                        'anlage_id'                 => $anlageId,
                        'zeile_nummer'              => $zeileNummer,
                        'position_index'            => $indexCounter,
                        'x_meter'                   => floatval($el['x']),
                        'y_meter'                   => floatval($el['y']),
                        'abstand_zum_vorherigen_cm' => intval($el['abstand_cm'] ?? 100),
                        'ist_entwurf'               => true, // Verbleibt sicher in der Planungsphase
                        'objekt_typ'                => $el['typ'] ?? 'rebe',
                        'status'                    => $el['status'] ?? 'gesund',
                        
                        // Tiefenbiologische Einzel-Genetik bei Ausbesserungen
                        'varietaet_rebsorte'        => $el['eigenschaften']['rebsorte'] ?? null,
                        'varietaet_klon'            => $el['eigenschaften']['klon'] ?? null,
                        'varietaet_unterlage'       => $el['eigenschaften']['unterlage'] ?? null,
                        'varietaet_unterlage_klon'  => $el['eigenschaften']['unterlage_klon'] ?? null,
                        'nachpflanz_jahr'           => isset($el['eigenschaften']['jahr']) ? intval($el['eigenschaften']['jahr']) : null,
                        
                        'created_at'                => now(),
                        'updated_at'                => now()
                    ]);
                    $indexCounter++;
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Vektorentwurf erfolgreich in der Staging-Zentrale gesichert!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
