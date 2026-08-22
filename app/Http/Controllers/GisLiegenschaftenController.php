<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use App\Models\VinicoreVertrag;
use App\Models\ParzelleVertrag;

class GisLiegenschaftenController extends Controller
{
    /**
     * Liefert alle registrierten Parzellen als GeoJSON-Mosaik für Leaflet.
     */
    public function index(): JsonResponse
    {
        try {
            $parzellen = DB::table('parzellen')->whereNull('gueltig_bis')->get();
            $features = [];

            foreach ($parzellen as $p) {
                if (empty($p->polygon_vektoren) || trim($p->polygon_vektoren) === "" || $p->polygon_vektoren === "null") {
                    continue;
                }
                $koordinatenRoh = json_decode($p->polygon_vektoren, true);
                if (!is_array($koordinatenRoh) || empty($koordinatenRoh)) continue;

                $polygonRing = [];
                $extractPairs = function($array) use (&$extractPairs, &$polygonRing) {
                    if (!is_array($array)) return;
                    if (count($array) >= 2 && isset($array[0]) && isset($array[1]) && is_numeric($array[0]) && is_numeric($array[1])) {
                        $polygonRing[] = [floatval($array[0]), floatval($array[1])];
                        return;
                    }
                    foreach ($array as $item) { if (is_array($item)) { $extractPairs($item); } }
                };
                $extractPairs($koordinatenRoh);

                if (count($polygonRing) < 3) continue;
                if ($polygonRing[0] !== $polygonRing[count($polygonRing) - 1]) { $polygonRing[] = $polygonRing[0]; }

                $belegt = DB::table('parzelle_anlage')->where('parzelle_uuid', $p->parzelle_uuid)->exists();
                $features[] = [
                    'type' => 'Feature',
                    'properties' => [
                        'uuid' => $p->parzelle_uuid,
                        'name' => "Flur " . ($p->flur ?? '1') . " | Nr. " . ($p->flurstueck_zaehler ?? '?'),
                        'gemarkung' => $p->gemarkung ?? 'Kataster',
                        'flur' => $p->flur ?? '1',
                        'flurstueck' => $p->flurstueck_zaehler . ($p->flurstueck_nenner ? '/' . $p->flurstueck_nenner : ''),
                        'flaeche_m2' => intval($p->amtliche_flaeche_m2 ?? 0),
                        'belegt' => $belegt,
                        'version' => intval($p->version ?? 1),
                        'besitz_status' => $p->besitz_status ?? 'eigentum',
                        'flurname_lage' => $p->flurname_lage ?? ''
                    ],
                    'geometry' => [ 'type' => 'Polygon', 'coordinates' => [$polygonRing] ]
                ];
            }
            return response()->json(['type' => 'FeatureCollection', 'features' => $features]);
        } catch (\Exception $e) { return response()->json(['success' => false, 'message' => $e->getMessage()], 500); }
    }
    /**
     * Zieht ein amtliches Flurstück LIVE über den ALKIS 2.0 WFS-Endpunkt.
     * 🚀 ARCHITEKTUR-FIX: Validiert ab sofort exakt deine realen Migrations-Spalten!
     */
    public function ladeVomGeoportalRlp(Request $request): JsonResponse
    {
        // 🎯 CORE-FIX BACKEND: Ändert die Validierung auf deine echten Datenbankspalten aus der Migration!
        $request->validate([
            'gemarkung'          => 'required|string', 
            'flur'               => 'required|string', 
            'flurstueck_zaehler' => 'required|string', 
            'nenner'             => 'nullable|string'
        ]);

        try {
            // 🚀 CORE-FIX BACKEND: Holt die Werte direkt aus deinen echten, migrationskonformen Request-Keys!
            $gemarkungSuche = ucfirst(strtolower(trim($request->gemarkung))); 
            $reineFlurZahl  = intval($request->flur); 
            $zaehler        = trim($request->flurstueck_zaehler); // 🎯 Zieht den echten Zähler für den XML-Filter
            $nenner         = $request->filled('nenner') ? trim((string)$request->nenner) : '';
            $vollstaendigeNummer = ($nenner !== '' && $nenner !== '0') ? $zaehler . '/' . $nenner : $zaehler;



            $partGemarkung = '<fes:PropertyIsLike escapeChar="\\\\" singleChar="_" wildCard="%"><fes:ValueReference>gemarkung</fes:ValueReference><fes:Literal>' . $gemarkungSuche . '</fes:Literal></fes:PropertyIsLike>';
            $partFlur      = '<fes:PropertyIsLike escapeChar="\\\\" singleChar="_" wildCard="%"><fes:ValueReference>flur</fes:ValueReference><fes:Literal>Flur ' . $reineFlurZahl . '</fes:Literal></fes:PropertyIsLike>';
            $partZaehler   = '<fes:PropertyIsLike escapeChar="\\\\" singleChar="_" wildCard="%"><fes:ValueReference>flstnrzae</fes:ValueReference><fes:Literal>' . $zaehler . '</fes:Literal></fes:PropertyIsLike>';

            if ($nenner !== '' && $nenner !== '0') {
                $partNenner = '<fes:PropertyIsLike escapeChar="\\\\" singleChar="_" wildCard="%"><fes:ValueReference>flstnrnen</fes:ValueReference><fes:Literal>' . $nenner . '</fes:Literal></fes:PropertyIsLike>';
                $xmlFilter = '<fes:Filter xmlns:fes="http://opengis.net"><fes:And><fes:And><fes:And>' . $partGemarkung . $partFlur . '</fes:And>' . $partZaehler . '</fes:And>' . $partNenner . '</fes:And></fes:Filter>';
            } else {
                $xmlFilter = '<fes:Filter xmlns:fes="http://opengis.net"><fes:And><fes:And>' . $partGemarkung . $partFlur . '</fes:And>' . $partZaehler . '</fes:And></fes:Filter>';
            }
            
            $targetUrl = config('services.gdi_rlp.wfs_url') . "SERVICE=WFS&VERSION=2.0.0&REQUEST=GetFeature&TYPENAMES=ave:Flurstueck&SRSNAME=urn:ogc:def:crs:EPSG::4326&RESULTTYPE=results&FILTER=" . rawurlencode($xmlFilter);
            $ch = curl_init($targetUrl); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_HTTPGET, true); curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); curl_setopt($ch, CURLOPT_TIMEOUT, 25); curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) QGIS/3.34.0');
            $xmlResponse = curl_exec($ch); $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);

            if ($httpCode !== 200 || !$xmlResponse || trim($xmlResponse) === "" || strpos($xmlResponse, '<html') !== false) { return response()->json(['success' => false, 'message' => 'Kataster-Server meldete ein Problem.'], 400); }
            $xml = simplexml_load_string(preg_replace('/(<\/?)(\w+):([^>]*>)/', '$1$3', $xmlResponse));
            $flurstuecke = $xml ? ($xml->xpath('//Flurstueck') ?: []) : [];
            if (empty($flurstuecke)) return response()->json(['success' => false, 'message' => 'Keine Parzelle im GDI-Register gefunden.'], 404);

            // 🚀 CORE-FIX: Holt den Nenner buchstabengetreu aus dem amtlichen GDI-Tag <flstnrnen>!
            if (count($flurstuecke) > 1) {
                $treffListe = [];
                foreach ($flurstuecke as $index => $f) {
                    $gName = isset($f->gemarkungsname) ? trim((string)$f->gemarkungsname) : $gemarkungSuche;
                    
                    // Direct-Match auf das vom Geoportal gelieferte XML-Tag
                    $einzelNennerRaw = '';
                    if (isset($f->flstnrnen)) {
                        $einzelNennerRaw = trim((string)$f->flstnrnen);
                    } else {
                        $tiefeSuche = $f->xpath('.//flstnrnen') ?: $f->xpath('.//nenner') ?: [];
                        $einzelNennerRaw = !empty($tiefeSuche) ? trim(strip_tags((string)$tiefeSuche[0])) : '';
                    }
                    
                    // Bereinigt behördliche Nullen (z.B. "0004" -> "4")
                    $einzelNennerClean = (!empty($einzelNennerRaw) && $einzelNennerRaw !== '0') 
                        ? preg_replace('/^[0\s]+/', '', preg_replace('/[^\d]/', '', $einzelNennerRaw)) 
                        : '';

                    $treffListe[] = [
                        'index'     => $index, 
                        'gemarkung' => $gName, 
                        'flaeche_m2'=> isset($f->flaeche) ? intval(trim((string)$f->flaeche)) : 1365, 
                        'nenner'    => $einzelNennerClean
                    ];
                }
                return response()->json(['success' => true, 'mehrfach_treffer' => true, 'auswahl' => $treffListe]);
            }


            $f = $flurstuecke[0]; $amtlicheGemarkung = isset($f->gemarkungsname) ? trim((string)$f->gemarkungsname) : $gemarkungSuche;
            $posListString = isset($f->posList) ? (string)$f->posList : '';
            if (trim($posListString) === "") { $tiefeSuche = $f->xpath('.//posList'); if (!empty($tiefeSuche)) $posListString = (string)$tiefeSuche[0]; }
            $zahlen = explode(' ', trim(preg_replace('/\s+/', ' ', $posListString))); $polygonVektoren = [];
            for ($i = 0; $i < count($zahlen); $i += 2) { if (isset($zahlen[$i]) && isset($zahlen[$i+1])) { $polygonVektoren[] = [floatval($zahlen[$i+1]), floatval($zahlen[$i])]; } }

            return response()->json(['success' => true, 'mehrfach_treffer' => false, 'feature' => ['type' => 'Feature', 'geometry' => [ 'type' => 'Polygon', 'coordinates' => [$polygonVektoren] ], 'properties' => [ 'gemarkung' => $amtlicheGemarkung, 'flur' => $reineFlurZahl, 'flurstueck' => $vollstaendigeNummer, 'flaeche_m2' => isset($f->flaeche) ? intval(trim((string)$f->flaeche)) : 1365, 'name' => $amtlicheGemarkung . " | Flur " . $reineFlurZahl . " | Nr. " . $vollstaendigeNummer ]]]);
        } catch (\Exception $e) { return response()->json(['success' => false, 'message' => $e->getMessage()], 500); }
    }
    /**
     * 📥 CONTRACT-DRIVEN CART-IMPORT (KATASTERWESEN)
     * 🚀 CORE-LOGIK: Speichert die gesammelten Warenkorb-Parzellen aus der Karte 
     * in einem transaktionsgesicherten Rutsch direkt im Zustand 'undefiniert' (V1)!
     */
    public function speichereInDatenbank(Request $request): JsonResponse
    {
        $request->validate([
            'vertrag_id' => 'required|integer',
            'parzellen'  => 'required|array'
        ]);

        try {
            DB::beginTransaction(); 
            $jetzt = now();
            $vertragId = intval($request->input('vertrag_id'));

            // 🛡️ SICHERHEITS-CHECK: Existiert der Vater-Vertrag überhaupt im System?
            $vertrag = DB::table('vinicore_vertraege')->where('id', $vertragId)->first();
            if (!$vertrag) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Kritischer Fehler: Der angewählte Finanzvertrag existiert nicht.'], 404);
            }

            // Holt die globale Standard-Allokation des Betriebs
            $einstellungen = DB::table('betriebseinstellungen')->where('betrieb_id', Auth::user()->betrieb_id)->first();
            $allokationsModell = $einstellungen ? $einstellungen->standard_allokation : 'modell_a';

            $importierteParzellen = $request->input('parzellen', []);
            $neueParzellenUuids = [];

            foreach ($importierteParzellen as $einzelneParzelle) {
                $props = $einzelneParzelle['properties'] ?? []; 
                $geometry = $einzelneParzelle['geometry'] ?? null;
                
                $gemarkung = trim($props['gemarkung'] ?? 'Unbekannt');
                $flur = !empty($props['flur']) ? intval(preg_replace('/[^\d]/', '', $props['flur'])) : 1;
                
                $flurstueckRaw = trim($props['flurstueck'] ?? ''); 
                $teile = explode('/', $flurstueckRaw);
                $zaehler = preg_replace('/^[0\s]+/', '', preg_replace('/[^\d]/', '', $teile[0] ?? $flurstueckRaw));
                $nenner = (count($teile) > 1 && trim($teile[1]) !== '0' && trim($teile[1]) !== '') ? preg_replace('/^[0\s]+/', '', preg_replace('/[^\d]/', '', $teile[1])) : null;

                // 🛡️ REVISIONSSCHUTZ: Prüfen, ob genau dieses Flurstück bereits AKTIV im Betrieb steht
                $existiertBereits = DB::table('parzellen')
                    ->where('gemarkung', '=', $gemarkung)
                    ->where('flur', $flur)
                    ->where('flurstueck_zaehler', '=', $zaehler)
                    ->where(function($q) use ($nenner) {
                        if (empty($nenner)) { $q->whereNull('flurstueck_nenner')->orWhere('flurstueck_nenner', '=', ''); }
                        else { $q->where('flurstueck_nenner', '=', $nenner); }
                    })
                    ->whereNull('gueltig_bis')
                    ->exists();

                if ($existiertBereits) {
                    continue; // Überspringen, da die Fläche bereits im echten Betriebsspiegel aktiv ist!
                }

                $neueUuid = (string) Str::uuid();

                // 🧱 1. Flurstück im Zustand UNDEFINIERT (Version 1) in die parzellen-Tabelle stanzen
                DB::table('parzellen')->insert([
                    'parzelle_uuid'       => $neueUuid,
                    'version'             => 1,
                    'freigabe_status'     => 'undefiniert', // 🟡 Erscheint reaktiv Gelb auf der Karte!
                    'polygon_vektoren'    => $geometry ? json_encode($geometry) : null,
                    'gemeinde'            => trim($props['gemeinde'] ?? 'Weinbaugemeinde'),
                    'gemarkung'           => $gemarkung,
                    'gemarkungsschuelser' => trim($props['gemarkungsschuelser'] ?? $props['gemaschl'] ?? null),
                    'flur'                => $flur,
                    'flurstueck_zaehler'  => $zaehler,
                    'flurstueck_nenner'   => $nenner,
                    'flurname_lage'       => trim($props['flurname_lage'] ?? 'Flur ' . $flur . ' | Nr. ' . $flurstueckRaw),
                    'amtliche_flaeche_m2' => intval($props['amtliche_flaeche_m2'] ?? $props['flaeche_m2'] ?? 0),
                    'besitz_status'       => 'undefiniert', // Wirtschaftlich noch völlig neutral
                    'aenderungsgrund'     => 'In den Pacht/Kaufvertrags-Warenkorb aufgenommen',
                    'gueltig_von'         => $jetzt,
                    'gueltig_bis'         => null,
                    'user_id'             => Auth::id(),
                    'created_at'          => $jetzt,
                    'updated_at'          => $jetzt
                ]);

                // 🧱 2. Die polymorthe Beziehung in parzelle_vertrag einhängen
                DB::table('parzelle_vertrag')->insert([
                    'parzelle_uuid'          => $neueUuid,
                    'vertragable_id'         => $vertragId,
                    'vertragable_type'       => \App\Models\VinicoreVertrag::class, // Morph-Kopplung
                    'zugeordneter_wert'      => 0.00, // Wird gleich im Anschluss berechnet!
                    'zugeordnete_flaeche_m2' => intval($props['amtliche_flaeche_m2'] ?? $props['flaeche_m2'] ?? 0),
                    'user_id'                => Auth::id(),
                    'created_at'             => $jetzt,
                    'updated_at'             => $jetzt
                ]);

                $neueParzellenUuids[] = $neueUuid;
            }

            // 📊 3. AUTOMATISCHE KASKADEN-ALLOKATION AUSFÜHREN (Modell A oder C)
            if (!empty($neueParzellenUuids)) {
                $this->berechneVertragsAllokationKaskade($vertragId);
            }

            DB::commit(); 
            return response()->json(['success' => true, 'message' => 'Warenkorb erfolgreich im Vertrag versiegelt und Allokation berechnet!']);

        } catch (\Exception $e) { 
            DB::rollBack(); 
            return response()->json(['success' => false, 'message' => 'Kernel-Absturz beim Warenkorb-Insert: ' . $e->getMessage()], 500); 
        }
    }
    /**
     * 📊 MATHEMATISCHE HIERARCHISCHE KASKADEN-ALLOKATION
     * Berechnet reaktiv die Euro-Werte der Parzellen innerhalb eines Vertrags!
     */
    private function berechneVertragsAllokationKaskade(int $vertragId): void
    {
        $vertrag = DB::table('vinicore_vertraege')->where('id', $vertragId)->first();
        if (!$vertrag) return;

        $gesamtwert = floatval($vertrag->gesamtwert);

        // 1. Holt alle zugeordneten Verknüpfungen für diesen Vertrag
        $zuordnungen = DB::table('parzelle_vertrag')
            ->where('vertragable_id', $vertragId)
            ->where('vertragable_type', \App\Models\VinicoreVertrag::class)
            ->get();

        if ($zuordnungen->isEmpty()) return;

        // 2. STUFE 1 & 2: Manuelle Werte (Modell B) herausfiltern und Restwert ermitteln
        $manuelleSumme = 0;
        $automatischeZuordnungen = [];
        $automatischeGesamtFlaeche = 0;

        foreach ($zuordnungen as $z) {
            // Wenn der Wert manuell eingetippt wurde (wir definieren: Wert wurde in einer separaten Edit-Aktion gesetzt)
            // Hier prüfen wir, ob ein temporäres Flag oder ein manueller Eintrag vorliegt. 
            // Standardmäßig nach dem Korb-Import ist alles automatisiert (0.00).
            if (isset($z->ist_manuell) && $z->ist_manuell) {
                $manuelleSumme += floatval($z->zugeordneter_wert);
            } else {
                $automatischeZuordnungen[] = $z;
                $automatischeGesamtFlaeche += intval($z->zugeordnete_flaeche_m2 ?: 0);
            }
        }

        $restBetrag = $gesamtwert - $manuelleSumme;
        if ($restBetrag < 0) $restBetrag = 0; // Überbuchungsschutz

        // Holt das Standardmodell des Betriebs (Modell A = Hektar, Modell C = Gleich)
        $einstellungen = DB::table('betriebseinstellungen')->where('betrieb_id', $vertrag->betrieb_id)->first();
        $modus = $einstellungen ? $einstellungen->standard_allokation : 'modell_a';

        // 3. STUFE 3: Restwert auf die automatischen Flächen verteilen
        $anzahlAuto = count($automatischeZuordnungen);
        if ($anzahlAuto === 0) return;

        foreach ($automatischeZuordnungen as $az) {
            $neuerWert = 0.00;

            if ($modus === 'modell_a' && $automatischeGesamtFlaeche > 0) {
                // 📈 MODELL A: Hektar-Methode (Flächenproportional)
                $anteil = intval($az->zugeordnete_flaeche_m2) / $automatischeGesamtFlaeche;
                $neuerWert = $restBetrag * $anteil;
            } else {
                // 📊 MODELL C: Gleichverteilung (Pauschal durch Anzahl teilen)
                $neuerWert = $restBetrag / $anzahlAuto;
            }

            // Wert centkonform runden und unzerbrechlich in der Kupplungstabelle aktualisieren
            DB::table('parzelle_vertrag')
                ->where('id', $az->id)
                ->update([
                    'zugeordneter_wert' => round($neuerWert, 2),
                    'updated_at'        => now()
                ]);
        }
    }

    /**
     * Lädt das hellblaue ALKIS-Umland-Vektornetz live vom Landesamt RLP.
     * 🚀 SECURED-PARSER: Umlaut-sicher und mit striktem Index-Schutz gegen Error 500!
     */
    public function ladeUmgebungVomGeoportal(Request $request): JsonResponse
    {
        $request->validate(['bbox' => 'required|string']);
        try {
            $baseUrl = config('services.gdi_rlp.wfs_url');
            $bereinigteBbox = str_replace(' ', '', $request->bbox);

            $targetUrl = $baseUrl . "SERVICE=WFS&VERSION=2.0.0&REQUEST=GetFeature&TYPENAMES=ave:Flurstueck"
                       . "&STARTINDEX=0&COUNT=2500&SRSNAME=urn:ogc:def:crs:EPSG::3857" 
                       . "&BBOX=" . rawurlencode($bereinigteBbox);

            $ch = curl_init($targetUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($ch, CURLOPT_HTTPGET, true); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            curl_setopt($ch, CURLOPT_TIMEOUT, 25); 
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) QGIS/3.34.0');
            $xmlResponse = curl_exec($ch); 

            if (!$xmlResponse || strpos($xmlResponse, '<html') !== false || strpos($xmlResponse, 'Exception') !== false) {
                return response()->json(['type' => 'FeatureCollection', 'features' => []]);
            }

            $xmlResponseClean = preg_replace('/(<\/?)(\w+):([^>]*>)/', '$1$3', $xmlResponse);
            $xml = simplexml_load_string($xmlResponseClean); 
            if ($xml === false) return response()->json(['type' => 'FeatureCollection', 'features' => []]);

            $flurstuecke = $xml->xpath('//Flurstueck') ?: [];
            $features = [];
            foreach ($flurstuecke as $f) {
                $zaehlerNode = $f->xpath('.//flurstuecksnummer') ?: $f->xpath('.//zaehler') ?: $f->xpath('.//flstnrzae') ?: [];
                $zaehlerRaw = (!empty($zaehlerNode) && isset($zaehlerNode[0])) ? trim(strip_tags((string)$zaehlerNode[0])) : '';
                if (empty($zaehlerRaw)) continue; 

                $nennerNode = $f->xpath('.//nenner') ?: $f->xpath('.//flstnrnen') ?: [];
                $nennerRaw = (!empty($nennerNode) && isset($nennerNode[0])) ? trim(strip_tags((string)$nennerNode[0])) : '';
                
                $zaehlerClean = preg_replace('/^[0\s]+/', '', preg_replace('/[^\d]/', '', $zaehlerRaw));
                $nennerClean = (!empty($nennerRaw) && $nennerRaw !== '0') ? preg_replace('/^[0\s]+/', '', preg_replace('/[^\d]/', '', $nennerRaw)) : null;

                $gemarkungNode = $f->xpath('.//gemarkungsname') ?: $f->xpath('.//gemarkung') ?: $f->xpath('.//gemeinde') ?: [];
                $gemarkungText = (!empty($gemarkungNode) && isset($gemarkungNode[0])) ? trim(strip_tags((string)$gemarkungNode[0])) : 'Kataster';

                $flurNode = $f->xpath('.//flurnummer') ?: $f->xpath('.//flur') ?: [];
                $flurRaw = (!empty($flurNode) && isset($flurNode[0])) ? trim(strip_tags((string)$flurNode[0])) : '1';
                $flurInteger = intval(preg_replace('/[^\d]/', '', $flurRaw)) ?: 1;

                $lageNode = $f->xpath('.//lagebeztxt') ?: $f->xpath('.//lagebezeichnung') ?: [];
                $lageText = (!empty($lageNode) && isset($lageNode[0])) ? trim(strip_tags((string)$lageNode[0])) : '';

                $gemeindeNode = $f->xpath('.//gemeinde') ?: [];
                $gmdschlNode  = $f->xpath('.//gmdschl') ?: [];

                $gemeindeRaw = (!empty($gemeindeNode) && isset($gemeindeNode[0])) ? trim(strip_tags((string)$gemeindeNode[0])) : '';
                $gmdschlRaw  = (!empty($gmdschlNode) && isset($gmdschlNode[0])) ? trim(strip_tags((string)$gmdschlNode[0])) : '';

                if (!empty($gemeindeRaw)) {
                    $gemeindeEintrag = !empty($gmdschlRaw) ? $gemeindeRaw . ' (' . $gmdschlRaw . ')' : $gemeindeRaw;
                } else {
                    $gemeindeEintrag = 'Weinbaugemeinde';
                }

                $gemaschlNode = $f->xpath('.//gemaschl') ?: $f->xpath('.//gemarkungsschluessel') ?: [];
                $gemaschlRaw  = (!empty($gemaschlNode) && isset($gemaschlNode[0])) ? trim(strip_tags((string)$gemaschlNode[0])) : '';

                $abfrage = DB::table('parzellen')
                    ->where('gemarkung', '=', $gemarkungText)
                    ->where('flur', $flurInteger)
                    ->where('flurstueck_zaehler', '=', $zaehlerClean) 
                    ->whereNull('gueltig_bis');

                if ($nennerClean !== null) {
                    $abfrage->where('flurstueck_nenner', '=', $nennerClean);
                } else {
                    $abfrage->where(function($q) {
                        $q->whereNull('flurstueck_nenner')->orWhere('flurstueck_nenner', '=', '0')->orWhere('flurstueck_nenner', '=', '');
                    });
                }

                if ($abfrage->exists()) {
                    continue; 
                }

                $posList = $f->xpath('.//posList');
                if (empty($posList) || !isset($posList[0])) continue;

                $zahlenString = trim(preg_replace('/\s+/', ' ', (string)$posList[0]));
                $zahlen = explode(' ', $zahlenString);
                
                $polygonVektoren = [];
                for ($i = 0; $i < count($zahlen); $i += 2) {
                    if (isset($zahlen[$i]) && isset($zahlen[$i+1])) {
                        $lng = (floatval($zahlen[$i]) / 20037508.34) * 180; $lat = (floatval($zahlen[$i+1]) / 20037508.34) * 180;
                        $lat = 180 / M_PI * (2 * atan(exp($lat * M_PI / 180)) - M_PI / 2);
                        if ($lat !== 0.0 && $lng !== 0.0) $polygonVektoren[] = [$lng, $lat]; 
                    }
                }
                if (empty($polygonVektoren)) continue;
                
                $vollNummerText = (!empty($nennerClean)) ? $zaehlerClean . '/' . $nennerClean : $zaehlerClean;
                
                // 🚀 CORE-FIX: Nutzt einen Umlaut-sicheren local-name Wildcard-Abgleich und schützt den Index!
                $flaecheM2 = 1365; 
                $flaecheKnoten = $f->xpath('.//*[local-name()="amtlicheFlaeche" or local-name()="amtlicheFläche" or local-name()="flaeche"]') ?: [];
                if (!empty($flaecheKnoten) && isset($flaecheKnoten[0])) {
                    $flaecheM2 = intval(trim(strip_tags((string)$flaecheKnoten[0]))) ?: 1365;
                }

                $features[] = [
                    'type' => 'Feature', 
                    'geometry' => [ 'type' => 'Polygon', 'coordinates' => [$polygonVektoren] ],
                    'properties' => [ 
                        'gemeinde' => $gemeindeEintrag,
                        'gemarkung' => (string)$gemarkungText, 
                        'gemarkungsschuelser' => $gemaschlRaw, 
                        'flur' => intval($flurInteger), 
                        'flurstueck' => (string)$vollNummerText, 
                        'flaeche_m2' => $flaecheM2,
                        'flurstueck_nenner' => $nennerClean, 
                        'flurname_lage' => !empty($lageText) ? (string)$lageText : 'Flur ' . $flurInteger . ' | Nr. ' . $vollNummerText
                    ]
                ];
            }
            return response()->json(['type' => 'FeatureCollection', 'features' => $features, 'debug_url' => $targetUrl]);
        } catch (\Exception $e) { 
            return response()->json(['type' => 'FeatureCollection', 'features' => [], 'error' => $e->getMessage()], 500); 
        }
    }

    /**
     * Zeigt den amtlichen Betriebsspiegel mitsamt der historisierten Live-Suche.
     * 🚀 SCOPE-FIX: Verknüpft die Suchvariable via 'use ($suche)' unzerstörbar mit dem SQL-Kernel!
     */
    public function parzellenUebersichtView(Request $request): mixed
    {
        $suche = $request->query('suche', '');
        $sortSpalte = $request->query('sort', 'gemarkung');
        $sortRichtung = $request->query('direction', 'asc');

        $erlaubteSpalten = [
            'gemeinde'             => 'parzellen.gemeinde',
            'gemarkung'            => 'parzellen.gemarkung',
            'flur'                 => 'parzellen.flur',
            'flurstueck_zaehler'   => 'parzellen.flurstueck_zaehler',
            'amtliche_flaeche_m2'  => 'parzellen.amtliche_flaeche_m2',
            'besitz_status'        => 'parzellen.besitz_status'
        ];

        $tatsaechlicheSpalte = $erlaubteSpalten[$sortSpalte] ?? 'parzellen.gemarkung';
        $tatsaechlicheRichtung = strtolower($sortRichtung) === 'desc' ? 'desc' : 'asc';

        // 🚀 CORE-FIX: Holt nur die vollkommen freigegebenen, aktiven Bestände in den Hauptspiegel
        $query = DB::table('parzellen')
            ->whereNull('parzellen.gueltig_bis')
            ->where('parzellen.freigabe_status', '=', 'aktiv');

        if (!empty($suche)) {
            $query->where(function($q) use ($suche) { 
                $q->where('parzellen.gemarkung', 'LIKE', '%' . $suche . '%')
                  ->orWhere('parzellen.gemeinde', 'LIKE', '%' . $suche . '%')
                  ->orWhere('parzellen.flurname_lage', 'LIKE', '%' . $suche . '%')
                  ->orWhere('parzellen.flurstueck_zaehler', 'LIKE', '%' . $suche . '%')
                  ->orWhere('parzellen.besitz_status', 'LIKE', '%' . $suche . '%'); 
            });
        }

        $aktive = $query->orderBy($tatsaechlicheSpalte, $tatsaechlicheRichtung)->get();

        // ⚖️ AUDIT-RADAR: Sammelt alle Revisionen, die auf das Vier-Augen-Okay warten!
        $ausstehendeFreigaben = DB::table('parzellen')
            ->where('freigabe_status', '=', 'eingereicht')
            ->whereNull('gueltig_bis')
            ->get();

        $geloeschte = DB::table('parzellen')->whereNotNull('gueltig_bis')->get();
        $verkaufte = [];

        // 🧱 NEULADUNGS-WEICHE FÜR DIE ASYNCHRONA AJAX-LIVE-SUCHE
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $tableHtml = '';
            foreach ($aktive as $p) {
                $statusHtml = ($p->besitz_status === 'eigentum') ? '<span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">● Eigentum</span>' : (($p->besitz_status === 'gepachtet') ? '<span class="bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">● Gepachtet</span>' : '<span class="bg-slate-100 text-slate-600 border border-slate-300 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">○ Verpachtet</span>');
                $ha = number_format(($p->amtliche_flaeche_m2 ?? 0) / 10000, 4, ',', '.'); 
                $m2 = number_format(($p->amtliche_flaeche_m2 ?? 0), 0, ',', '.');
                
                $lage = !empty($p->flurname_lage) ? htmlspecialchars((string)$p->flurname_lage, ENT_QUOTES, 'UTF-8') : 'Keine Angabe'; 
                $nennerZusatz = !empty($p->flurstueck_nenner) ? '/' . $p->flurstueck_nenner : '';
                $gemeindeName = !empty($p->gemeinde) ? htmlspecialchars((string)$p->gemeinde, ENT_QUOTES, 'UTF-8') : 'Weinbaugemeinde';
                $gemarkungName = !empty($p->gemarkung) ? htmlspecialchars((string)$p->gemarkung, ENT_QUOTES, 'UTF-8') : 'Kataster';

                if (intval($p->version ?? 1) === 1) {
                    $verknuepfungHtml = "<span class='inline-flex items-center gap-1 bg-amber-50 text-amber-800 border border-amber-200 px-2 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider animate-pulse'>⚠️ Erstprüfung ausstehend</span>";
                } else {
                    $verknuepfungHtml = !empty($p->anlage_name) ? "<div class='space-y-1'><a href='/schlaege/schlag-karte?fokus_anlage={$p->anlage_id}' class='inline-flex items-center gap-1 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold border border-blue-200 px-2 py-0.5 rounded-lg text-[10px] uppercase tracking-wider no-underline transition shadow-3xs'>🌿 " . htmlspecialchars($p->anlage_name, ENT_QUOTES, 'UTF-8') . "</a>" . (!empty($p->schlag_name) ? "<span class='block text-[10px] text-slate-400 font-medium font-sans'>🚜 Schlag: " . htmlspecialchars($p->schlag_name, ENT_QUOTES, 'UTF-8') . "</span>" : "") . "</div>" : "<span class='text-slate-400 text-[10px] font-mono italic'>Nicht verknüpft</span>";
                }
                
                $kartenLink = "<a href='/kataster/parzellen-karte?fokus_parzelle={$p->parzelle_uuid}' class='inline-block p-1.5 border border-blue-200 rounded-lg bg-blue-50/30 hover:bg-blue-100/60 text-blue-600 transition shadow-3xs'><svg class='w-3.5 h-3.5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2v1a2 2 0 002 2h2.1l.6 2.4c.1.4.5.6.9.6h1.5a1 1 0 001.8-.6l.3-1.2A9 9 0 103.055 11z' /></svg></a>";
                
                $jsLage = addslashes($lage);
                $jsStatus = addslashes((string)$p->besitz_status);
                $jsVersion = intval($p->version ?? 1);

                // 🚀 VISUELLER FILTER (AJAX): Schneidet den Gemeindeschlüssel für die Live-Suche im RAM ab
                $gemeindeRaw = !empty($p->gemeinde) ? (string)$p->gemeinde : 'Weinbaugemeinde';
                $reinerGemeindeName = explode(' (', $gemeindeRaw)[0];
                
                $gemeindeName = htmlspecialchars($reinerGemeindeName, ENT_QUOTES, 'UTF-8');
                $gemarkungName = !empty($p->gemarkung) ? htmlspecialchars((string)$p->gemarkung, ENT_QUOTES, 'UTF-8') : 'Kataster';
                
                $schluesselZusatz = !empty($p->gemarkungsschuelser) 
                    ? " (" . htmlspecialchars((string)$p->gemarkungsschuelser, ENT_QUOTES, 'UTF-8') . ")" 
                    : "";

                // Der dichte UI-Stringsatz bleibt absolut unberührt, rendert nun aber die saubere Optik
                $tableHtml .= "<tr class='hover:bg-slate-50/50 transition-colors'><td class='p-3 leading-tight'><span class='text-slate-900 font-bold text-xs block'>{$gemarkungName}</span><span class='text-[10px] font-mono text-slate-400 block mt-0.5'>{$gemeindeName}{$schluesselZusatz}</span></td><td class='p-3 font-mono font-bold text-slate-500'>Flur {$p->flur}</td>...";

            }
            return response()->json(['success' => true, 'table_html' => $tableHtml, 'anzahl' => count($aktive)]);
        }

        // Standard-Rendern beim Erstaufruf der Seite
        return view('kataster.parzellen_uebersicht', compact('aktive', 'geloeschte', 'verkaufte', 'suche', 'sortSpalte', 'sortRichtung'));
    }

     /**
     * 📝 HISTORISIERTE ERSTPRÜFUNG & REVISION (PHASE 2)
     * Verriegelt eine gelbe Parzelle (V1) und schaltet sie auf Sattes Grün (V2+)!
     */
    public function aktualisiereParzelle(Request $request, $uuid): JsonResponse
    {
        $request->validate([
            'besitz_status'   => 'required|in:eigentum,gepachtet,verpachtet',
            'flurname_lage'   => 'nullable|string|max:255',
            'aenderungsgrund' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();
            $jetzt = now();

            // 1. Holt den aktuellen Entwurf (Version 1)
            $alteParzelle = DB::table('parzellen')
                ->where('parzelle_uuid', $uuid)
                ->whereNull('gueltig_bis')
                ->first();

            if (!$alteParzelle) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Liegenschaft nicht gefunden.'], 404);
            }

            $aktuelleVersion = intval($alteParzelle->version);

            // 2. Zeitschloss zünden: Alten Zustand für den Audit-Trail einfrieren
            DB::table('parzellen')
                ->where('id', $alteParzelle->id)
                ->update(['gueltig_bis' => $jetzt, 'updated_at' => $jetzt]);

            // 3. Neuen Zustand mit Version + 1 und Status 'aktiv' einstanzen
            DB::table('parzellen')->insert([
                'parzelle_uuid'       => $uuid,
                'version'             => $aktuelleVersion + 1,
                'freigabe_status'     => 'aktiv', // 🟢 Schaltet die Fläche auf Sattes Grün um!
                'polygon_vektoren'    => $alteParzelle->polygon_vektoren,
                'gemeinde'            => $alteParzelle->gemeinde,
                'gemarkung'           => $alteParzelle->gemarkung,
                'gemarkungsschuelser' => $alteParzelle->gemarkungsschuelser,
                'flur'                => $alteParzelle->flur,
                'flurstueck_zaehler'  => $alteParzelle->flurstueck_zaehler,
                'flurstueck_nenner'   => $alteParzelle->flurstueck_nenner,
                'flurname_lage'       => trim($request->flurname_lage ?? $alteParzelle->flurname_lage),
                'amtliche_flaeche_m2' => $alteParzelle->amtliche_flaeche_m2,
                'besitz_status'       => $request->besitz_status, // Zuweisung Eigentum/Pacht
                'aenderungsgrund'     => trim($request->aenderungsgrund ?? 'Kataster-Erstprüfung versiegelt'),
                'gueltig_von'         => $jetzt,
                'gueltig_bis'         => null,
                'user_id'             => Auth::id(),
                'created_at'          => $alteParzelle->created_at,
                'updated_at'          => $jetzt
            ]);

            // 4. Record Lock nach erfolgreicher Versiegelung direkt aufheben
            DB::table('parzellen_locks')->where('parzelle_uuid', $uuid)->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Erstprüfung erfolgreich rechtssicher versiegelt!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Absturz bei Erstprüfung: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 📡 SIDEBAR DETAIL MATRIX TUNNEL
     * 🚀 EXTENDED-FIX: Lädt die reaktiv berechneten Allokations-Finanzwerte direkt mit in den Inspektor!
     */
    public function holeParzelleDetails($uuid): JsonResponse
    {
        try {
            $parzelle = DB::table('parzellen')
                // Bindet die polymorphe Kupplungstabelle für den Finanzwert ein
                ->leftJoin('parzelle_vertrag', function($join) {
                    $join->on('parzellen.parzelle_uuid', '=', 'parzelle_vertrag.parzelle_uuid')
                         ->where('parzelle_vertrag.vertragable_type', '=', \App\Models\VinicoreVertrag::class);
                })
                // Bindet den Vater-Vertrag für die Vertragsnummer ein
                ->leftJoin('vinicore_vertraege', 'vinicore_vertraege.id', '=', 'parzelle_vertrag.vertragable_id')
                ->where('parzellen.parzelle_uuid', $uuid)
                ->whereNull('parzellen.gueltig_bis')
                ->select(
                    'parzellen.*', 
                    'parzelle_vertrag.zugeordneter_wert as allokierter_zins',
                    'vinicore_vertraege.vertrag_nummer as vertrags_referenz',
                    'vinicore_vertraege.typ as vertrags_typ'
                )
                ->first();

            if (!$parzelle) {
                return response()->json(['success' => false, 'message' => 'Liegenschaft im aktiven Bestand nicht lokalisiert.'], 404);
            }

            return response()->json(['success' => true, 'parzelle' => $parzelle]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Detail-Tunnel fehlgeschlagen: ' . $e->getMessage()], 500);
        }
    }

     /**
     * 🔒 RECORD LOCKING: Versiegelt eine Parzelle temporär für die Bearbeitung.
     * 🚀 TRANS-LOCK FIX: Tippfehler beim Datumsstempel-Namen vollständig behoben!
     */
    public function lockParzelle(Request $request, $uuid): JsonResponse
    {
        $user = Auth::user();
        $jetzt = now();
        $jetztString = $jetzt->format('Y-m-d H:i:s');

        try {
            DB::beginTransaction();

            // 1. Prüfen, ob bereits eine aktive Sperre eines ANDEREN Nutzers existiert
            $sperre = DB::table('parzellen_locks')
                ->where('parzelle_uuid', '=', $uuid)
                ->where('expires_at', '>', $jetztString)
                ->where('user_id', '!=', $user->id)
                ->first();

            if ($sperre) {
                $blockierer = DB::table('users')->where('id', $sperre->user_id)->value('name') ?: 'einem anderen Nutzer';
                DB::rollBack();
                return response()->json([
                    'success' => false, 
                    'is_locked' => true,
                    'message' => "Gesperrt: Diese Parzelle wird aktuell von {$blockierer} bearbeitet."
                ], 423); 
            }

            // 2. Säuberung: Entfernt alte Sperren für dieses Flurstück
            DB::table('parzellen_locks')
                ->where('parzelle_uuid', '=', $uuid)
                ->delete();

            // 3. Neu-Eintrag: Schreibt die Rohdaten unzerbrechlich ins Register
            DB::table('parzellen_locks')->insert([
                'parzelle_uuid' => (string)$uuid,
                'user_id'       => intval($user->id),
                'expires_at'    => $jetzt->copy()->addMinutes(15)->format('Y-m-d H:i:s'),
                'created_at'    => $jetztString, // 🎯 BEREINIGT
                'updated_at'    => $jetztString
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Parzelle erfolgreich für dich gesperrt.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Sperr-Kernel fehlgeschlagen: ' . $e->getMessage()], 500);
        }
    }


    public function unlockParzelle(Request $request, $uuid): JsonResponse
    {
        DB::table('parzellen_locks')
            ->where('parzelle_uuid', $uuid)
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json(['success' => true, 'message' => 'Sperre erfolgreich aufgehoben.']);
    }
    /**
     * ⚖️ ADMINISTRATIVE REVISIONS-FREIGABE (4-AUGEN-PRINZIP)
     * 🚀 CORE-FIX: Schaltet eine eingereichte Mitarbeiter-Revision nach Admin-Prüfung aktiv!
     */
    public function freigebeParzelleAudit(Request $request, $uuid): JsonResponse
    {
        // 🛡️ BERECHTIGUNGS-SCHECK: Nur der Administrator darf dieses Siegel brechen!
        $istAdmin = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', Auth::id())
            ->where('roles.slug', 'admin')
            ->exists();

        if (!$istAdmin) {
            return response()->json(['success' => false, 'message' => 'Aktion verweigert: Unzureichende Admin-Rechte.'], 403);
        }

        try {
            DB::beginTransaction();
            $jetzt = now();

            // 1. Holt die eingereichte, wartende Version
            $eingereichteVersion = DB::table('parzellen')
                ->where('parzelle_uuid', $uuid)
                ->where('freigabe_status', 'eingereicht')
                ->first();

            if (!$eingereichteVersion) {
                return response()->json(['success' => false, 'message' => 'Keine ausstehende Revision für dieses Flurstück gefunden.'], 404);
            }

            // 2. Holt die aktuell noch aktive Vorversion, um sie zeitlich zu begrenzen (archivieren)
            $alteAktiveVersion = DB::table('parzellen')
                ->where('parzelle_uuid', $uuid)
                ->where('freigabe_status', 'aktiv')
                ->whereNull('gueltig_bis')
                ->first();

            if ($alteAktiveVersion) {
                DB::table('parzellen')
                    ->where('id', $alteAktiveVersion->id)
                    ->update([
                        'gueltig_bis' => $jetzt,
                        'updated_at' => $jetzt
                    ]);
            }

            // 3. Schaltet die eingereichte Version scharf (Status 'aktiv' und gültig_von auf jetzt)
            DB::table('parzellen')
                ->where('id', $eingereichteVersion->id)
                ->update([
                    'freigabe_status' => 'aktiv',
                    'gueltig_von' => $jetzt,
                    'updated_at' => $jetzt
                ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Revision erfolgreich geprüft und rechtssicher freigegeben!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Kernel-Absturz bei Freigabe: ' . $e->getMessage()], 500);
        }
    }
    /**
     * 🗑️ DIE PROZESSUALE VERNICHTUNGS-MÜNDUNG (KATASTER-LÖSCHUNG)
     * 🚀 CORE-FIX: Schließt die fehlende Backend-Lücke für das Ausbuchen von Parzellen!
     */
    public function loescheParzelle(Request $request, $uuid): JsonResponse
    {
        // 🛡️ BERECHTIGUNGS-SCHECK: Nur Admins und befugte Mitarbeiter dürfen Flächen löschen
        $istBefugt = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', Auth::id())
            ->whereIn('roles.slug', ['admin', 'mitarbeiter'])
            ->exists();

        if (!$istBefugt) {
            return response()->json(['success' => false, 'message' => 'Zugriff verweigert: Unzureichende ERP-Rechte.'], 403);
        }

        $request->validate([
            'aenderungsgrund' => 'required|string|max:255'
        ]);

        try {
            DB::beginTransaction();
            $jetzt = now();

            // Holt die betroffene Parzelle aus dem aktiven Register
            $parzelle = DB::table('parzellen')
                ->where('parzelle_uuid', $uuid)
                ->whereNull('gueltig_bis')
                ->first();

            if (!$parzelle) {
                return response()->json(['success' => false, 'message' => 'Liegenschaft im aktiven Bestand nicht gefunden.'], 404);
            }

            $aktuelleVersion = intval($parzelle->version ?? 1);

            if ($aktuelleVersion === 1) {
                // 🔥 SÄULE 1 (DEIN FEHLER): Ungeprüfte Erstimporte werden spurlos und physikalisch gelöscht!
                // Zuerst verwaiste Kopplungen entfernen, um Foreign-Key-Abstürze zu verhindern
                DB::table('parzelle_anlage')->where('parzelle_uuid', $uuid)->delete();
                DB::table('parzellen_locks')->where('parzelle_uuid', $uuid)->delete();
                
                // Löscht den Hauptdatensatz restlos
                DB::table('parzellen')->where('id', $parzelle->id)->delete();

                DB::commit();
                return response()->json(['success' => true, 'message' => 'Fehlimport erfolgreich und spurlos aus dem Register eliminiert.']);
            } else {
                // ⏳ SÄULE 2: Bereits erstgeprüfte Bestände (V2+) werden über das Zeitschloss historisiert
                DB::table('parzellen')
                    ->where('id', $parzelle->id)
                    ->update([
                        'gueltig_bis' => $jetzt,
                        'aenderungsgrund' => trim($request->aenderungsgrund),
                        'updated_at' => $jetzt
                    ]);

                DB::commit();
                return response()->json(['success' => true, 'message' => 'Bestandsparzelle erfolgreich historisiert ausgebucht.']);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lösch-Kernel fehlgeschlagen: ' . $e->getMessage()], 500);
        }
    }

}
