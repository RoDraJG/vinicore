<?php

namespace App\Http\Controllers;

use App\Models\Anlage;
use App\Models\Schlag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnlageController extends Controller
{
    /**
     * Holt alle biologischen Anlagen eines bestimmten organisatorischen Schlags.
     */
    public function indexPerSchlag(int $schlagId): JsonResponse
    {
        try {
            $anlagen = Anlage::where('schlag_id', $schlagId)->get();
            return response()->json(['success' => true, 'data' => $anlagen]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Erstellt eine neue biologische Anlage (Teilstück) innerhalb eines Schlags.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'schlag_id' => 'required|exists:schlaege,id',
            'name' => 'required|string|max:255'
        ]);

        try {
            $anlage = Anlage::create([
                'schlag_id' => $request->schlag_id,
                'name' => $request->name,
                'plan_status' => 'in_planung',
                'vorgewende_start_cm' => intval($request->input('vorgewende_start_cm', 400)),
                'vorgewende_ende_cm' => intval($request->input('vorgewende_ende_cm', 400)),
                'randabstand_links_cm' => intval($request->input('randabstand_links_cm', 120)),
                'randabstand_rechts_cm' => intval($request->input('randabstand_rechts_cm', 120)),
                'abstand_anker_endpfahl_cm' => intval($request->input('abstand_anker_endpfahl_cm', 150)),
                'abstand_endpfahl_rebe_cm' => intval($request->input('abstand_endpfahl_rebe_cm', 80)),
                'ziel_gassenbreite_cm' => intval($request->input('ziel_gassenbreite_cm', 200)),
                'stockabstand_cm' => intval($request->input('stockabstand_cm', 100)),
                'reihenpfahl_abstand_cm' => intval($request->input('reihenpfahl_abstand_cm', 450))
            ]);

            return response()->json(['success' => true, 'data' => $anlage], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Zeigt eine spezifische Anlage inklusive ihrer gekoppelten Teilflächen-Parzellen.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $anlage = Anlage::with('parzellen')->find($id);
            if (!$anlage) return response()->json(['success' => false, 'message' => 'Anlage fehlt.'], 404);
            return response()->json(['success' => true, 'data' => $anlage]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    /**
     * Aktualisiert die Parameter und koppelt die ALKIS-Parzellen über die Pivot-Tabelle.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $anlage = Anlage::find($id);
            if (!$anlage) return response()->json(['success' => false, 'message' => 'Anlage fehlt.'], 404);

            // 1. Parameter updaten
            $anlage->update($request->only([
                'name', 'vorgewende_start_cm', 'vorgewende_ende_cm', 'randabstand_links_cm',
                'randabstand_rechts_cm', 'abstand_anker_endpfahl_cm', 'abstand_endpfahl_rebe_cm',
                'ziel_gassenbreite_cm', 'stockabstand_cm', 'reihenpfahl_abstand_cm'
            ]));

            // 2. Parzellen-Teilflächen krisenfest koppeln (Ersetzt unzuverlässiges sync)
            if ($request->has('parzellen_anteile')) {
                DB::table('parzelle_anlage')->where('anlage_id', $id)->delete();
                $anteile = $request->input('parzellen_anteile');

                if (is_array($anteile)) {
                    foreach ($anteile as $p) {
                        $uuid = $p['parzelle_id'] ?? null;
                        if (!$uuid) continue;

                        // Schutz vor Doppelbelegung durch Fremdanlagen
                        $besetzt = DB::table('parzelle_anlage')->where('parzelle_uuid', $uuid)->where('anlage_id', '!=', $id)->exists();
                        if ($besetzt) throw new \Exception("Parzelle {$uuid} wird bereits aktiv genutzt!");

                        DB::table('parzelle_anlage')->insert([
                            'anlage_id' => $id,
                            'parzelle_uuid' => $uuid,
                            'anteil_prozent' => floatval($p['anteil'] ?? 100.00),
                            'pflanz_richtung' => $p['pflanz_richtung'] ?? 'horizontal',
                            'created_at' => now(), 'updated_at' => now()
                        ]);
                    }
                }
            }

            return response()->json(['success' => true, 'message' => 'Anlagen-Parameter und Teilflächen gesichert.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Sperrt den Planmodus für einen Benutzer (Pessimistic Locking).
     */
    public function lockPlanmodus(Request $request, int $id): JsonResponse
    {
        // Dummy-ID falls Auth-System noch aussteht, sonst: auth()->id()
        $userId = $request->input('user_id', 1); 
        try {
            $anlage = Anlage::find($id);
            if (!$anlage) return response()->json(['success' => false, 'message' => 'Anlage fehlt.'], 404);

            if ($anlage->locked_by_user_id && $anlage->locked_by_user_id != $userId && now()->lessThan($anlage->locked_until)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Diese Anlage wird aktuell von einem anderen Winzer bearbeitet und ist blockiert!'
                ], 423);
            }

            // Sperre für 15 Minuten mieten
            $anlage->update([
                'locked_by_user_id' => $userId,
                'locked_until' => now()->addMinutes(15)
            ]);

            return response()->json(['success' => true, 'message' => 'Planmodus exklusiv für dich reserviert.']);
        } catch (\Exception $e) { return response()->json(['success' => false, 'message' => $e->getMessage()], 500); }
    }

    /**
     * Gibt die Anlage wieder für das Team frei.
     */
    public function unlockPlanmodus(int $id): JsonResponse
    {
        try {
            Anlage::where('id', $id)->update(['locked_by_user_id' => null, 'locked_until' => null]);
            return response()->json(['success' => true, 'message' => 'Anlage für das Team freigegeben.']);
        } catch (\Exception $e) { return response()->json(['success' => false, 'message' => $e->getMessage()], 500); }
    }

    /**
     * Schaltet den Entwurf scharf und startet die offizielle Historie!
     */
    public function planerAktivieren(int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $anlage = Anlage::find($id);
            if (!$anlage) return response()->json(['success' => false, 'message' => 'Anlage fehlt.'], 404);

            // 1. Alte historische Generation dieser Anlage einfrieren (Zeitstempel setzen)
            DB::table('pflanz_matrix')->where('anlage_id', $id)->where('ist_entwurf', false)->whereNull('gueltig_bis')->update(['gueltig_bis' => now()]);

            // 2. Alle Entwürfe dieser Planungsphase in den echten Produktions-Datenstamm heben
            DB::table('pflanz_matrix')->where('anlage_id', $id)->where('ist_entwurf', true)->update([
                'ist_entwurf' => false,
                'gueltig_von' => now(),
                'updated_at' => now()
            ]);

            // 3. Anlagen-Status auf aktiv bewirtschaftet setzen und Sperren kappen
            $anlage->update(['plan_status' => 'aktiv_bewirtschaftet', 'locked_by_user_id' => null, 'locked_until' => null]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Planungsphase beendet! Vektordaten sind live und historisiert.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
