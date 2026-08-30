<?php

namespace App\Modules\Kataster\Controllers;

use App\Http\Controllers\Controller;
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
     * 🛰️ UUID-ENTWURF INITIALISIEREN
     * Parkt die ersten Stammdaten und liefert eine flüchtige Transaktions-UUID zurück.
     */

        public function index()
    {
        // Lädt die parzellen_karte.blade.php aus resources/views/kataster/
        return view('kataster.parzellen_karte'); 
    }
    public function initialisiereEntwurf(Request $request): JsonResponse
    {
        $uuid = (string) Str::uuid();

        DB::table('vertrags_entwuerfe')->insert([
            'id'                   => $uuid,
            'vertrag_nummer'       => $request->vertrag_nummer,
            'typ'                  => $request->typ,
            'vertragspartner_name' => $request->vertragspartner_name,
            'gesamtwert'           => $request->gesamtwert ?? 0,
            'gueltig_von'          => $request->gueltig_von,
            'gueltig_bis'          => $request->gueltig_bis,
            'created_at'           => now(),
            'updated_at'           => now()
        ]);

        return response()->json(['success' => true, 'entwurf_id' => $uuid]);
    }

    /**
     * 🗺️ LIVE-KARTEN-SYNCHRONISATION
     * Speicher-Riegel: Schreibt die gewählten GeoJSON-Kacheln direkt unter der UUID in MySQL.
     */
    public function synchronisiereEntwurfsParzellen(Request $request): JsonResponse
    {
        $entwurfId = $request->entwurf_id;
        $parzellen = $request->parzellen ?? [];

        if (!$entwurfId) {
            return response()->json(['success' => false, 'message' => 'Keine gültige Entwurfs-ID übermittelt.']);
        }

        DB::table('parzelle_entwurf')->where('entwurf_id', $entwurfId)->delete();

        foreach ($parzellen as $p) {
            $props = $p['properties'] ?? [];
            
            DB::table('parzelle_entwurf')->insert([
                'entwurf_id'          => $entwurfId,
                'gemarkung'           => $props['gemarkung'] ?? 'Umland',
                'flur'                => intval($props['flur'] ?? 1),
                'flurstueck_zaehler'  => $props['flurstueck_zaehler'] ?? $props['zaehler'] ?? '0',
                'flurstueck_nenner'   => $props['flurstueck_nenner'] ?? $props['nenner'] ?? null,
                'amtliche_flaeche_m2' => intval($props['amtliche_flaeche_m2'] ?? $props['flaeche_m2'] ?? 0),
                'raw_geojson'         => json_encode($p),
                'created_at'          => now(),
                'updated_at'          => now()
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Kartenflächen servergesichert.']);
    }

    /**
     * 🏢 THE ENTERPRISE COMMIT: DIE GEBURTSSTUNDE DER ECHTEN NUMERISCHEN ID
     * Überführt den schwebenden UUID-Entwurf final in den produktiven Echt-Bestand.
     */
    public function finalVersiegeln(Request $request): JsonResponse
    {
        $entwurfId = $request->entwurf_id;
        $jetzt = now();

        $entwurf = DB::table('vertrags_entwuerfe')->where('id', $entwurfId)->first();
        if (!$entwurf) {
            return response()->json(['success' => false, 'message' => 'Der Entwurf ist abgelaufen oder nicht auffindbar.']);
        }

        // 🚀 NUMERISCHER NUMMERNKREIS: Erst jetzt entsteht die fortlaufende vertrag_id!
        $echteVertragId = DB::table('vertraege')->insertGetId([
            'vertrag_nummer'       => $request->input('vertrag.vertrag_nummer') ?? $entwurf->vertrag_nummer,
            'typ'                  => $request->input('vertrag.typ') ?? $entwurf->typ,
            'vertragspartner_name' => $request->input('vertrag.vertragspartner_name') ?? $entwurf->vertragspartner_name,
            'gesamtwert'           => $request->input('vertrag.gesamtwert') ?? $entwurf->gesamtwert,
            'gueltig_von'          => $request->input('vertrag.gueltig_von') ?? $entwurf->gueltig_von,
            'gueltig_bis'          => $request->input('vertrag.gueltig_bis') ?? $entwurf->gueltig_bis,
            'status'               => 'aktiv',
            'created_at'           => $jetzt,
            'updated_at'           => $jetzt
        ]);

        $schwebendeFlächen = DB::table('parzelle_entwurf')->where('entwurf_id', $entwurfId)->get();

        foreach ($schwebendeFlächen as $f) {
            $neueUuid = (string) Str::uuid();
            $geojson = json_decode($f->raw_geojson, true);

            DB::table('parzellen')->insert([
                'parzelle_uuid'       => $neueUuid,
                'version'             => 1,
                'freigabe_status'     => 'undefiniert',
                'polygon_vektoren'    => isset($geojson['geometry']) ? json_encode($geojson['geometry']) : null,
                'gemarkung'           => $f->gemarkung,
                'flur'                => $f->flur,
                'flurstueck_zaehler'  => $f->flurstueck_zaehler,
                'flurstueck_nenner'   => $f->flurstueck_nenner,
                'amtliche_flaeche_m2' => $f->amtliche_flaeche_m2,
                'besitz_status'       => 'undefiniert',
                'aenderungsgrund'     => 'In Kauf/Pachtvertrag aufgenommen',
                'gueltig_von'         => $jetzt,
                'user_id'             => Auth::id(),
                'created_at'          => $jetzt,
                'updated_at'          => $jetzt
            ]);

            DB::table('parzelle_vertrag')->insert([
                'parzelle_uuid'          => $neueUuid,
                'vertragable_id'         => $echteVertragId,
                'vertragable_type'       => 'App\Models\VinicoreVertrag',
                'zugeordnete_flaeche_m2' => $f->amtliche_flaeche_m2,
                'user_id'                => Auth::id(),
                'created_at'             => $jetzt,
                'updated_at'             => $jetzt
            ]);
        }

        // Vernichtet den temporären UUID-Eintrag restlos aus den Puffertabellen
        DB::table('vertrags_entwuerfe')->where('id', $entwurfId)->delete();

        return response()->json(['success' => true, 'message' => 'Vertrag mitsamt Katasterflächen erfolgreich revisionssicher eingebucht!']);
    }
/**
     * 🛰️ THE GLOBAL GEOJSON GENERATOR (REGISTER-SPIEGEL)
     * 🚀 REVISIONS-FIX: Generiert ein absolut standardkonformes GeoJSON-FeatureCollection-Objekt!
     */
public function holeGespeicherteParzellenAusDatenbank(Request $request): JsonResponse
{
    $parzellen = DB::table('parzellen')
        ->whereIn('freigabe_status', ['undefiniert', 'aktiv'])
        ->whereNull('gueltig_bis')
        ->get();

    $geoJson = [
        'type' => 'FeatureCollection',
        'features' => []
    ];

    foreach ($parzellen as $p) {
        if (empty($p->polygon_vektoren)) continue;
        
        $geometry = json_decode($p->polygon_vektoren, true);
        if ($geometry) {
            $geoJson['features'][] = [
                'type' => 'Feature',
                'geometry' => $geometry,
                'properties' => [
                    'id' => $p->id,
                    'parzelle_uuid' => $p->parzelle_uuid,
                    'gemarkung' => $p->gemarkung,
                    'flur' => $p->flur,
                    'flurstueck' => $p->flurstueck_zaehler . ($p->flurstueck_nenner ? '/' . $p->flurstueck_nenner : ''),
                    'amtliche_flaeche_m2' => $p->amtliche_flaeche_m2,
                    'nutzungs_art' => $p->nutzungs_art ?? '',
                    'boden_schaetz_wert' => $p->boden_schaetz_wert ?? '',
                    'rebsorte' => $p->rebsorte ?? '',
                    'vortrag_pacht' => $p->vortrag_pacht ?? '',
                    'freigabe_status' => $p->freigabe_status,
                    'besitz_status' => $p->besitz_status ?? 'eigentum'
                ]
            ];
        }
    }

    return response()->json($geoJson);
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
                // 🪐 BACK TO THE ROOTS: Nutzt wieder zu 100% deinen originalen, funktionierenden GeoJSON-Parser!
                $props = $einzelneParzelle['properties'] ?? []; 
                $geometry = $einzelneParzelle['geometry'] ?? null;
                
                $gemarkung = trim($props['gemarkung'] ?? 'Unbekannt');
                $flur = !empty($props['flur']) ? intval(preg_replace('/[^\d]/', '', $props['flur'])) : 1;
                
                $flurstueckRaw = trim($props['flurstueck'] ?? ''); 
                $amtlicheFlaeche = intval($props['amtliche_flaeche_m2'] ?? $props['flaeche_m2'] ?? 0);
                $flurnameLage = trim($props['flurname_lage'] ?? 'Flur ' . $flur . ' | Nr. ' . $flurstueckRaw);
                $gemeinde = trim($props['gemeinde'] ?? 'Weinbaugemeinde');
                $gemarkungsschluessel = trim($props['gemarkungsschuelser'] ?? $props['gemaschl'] ?? null);

                // 🧱 AB HIER LÄUFT DEIN ORIGINALER SPEICHER-PROZESS UNBERÜHRT WEIDER:
                $teile = explode('/', $flurstueckRaw);


                // 🧱 AB HIER NUTZT DIE STRUKTUR DEINE BEREINIGTEN VARIABLEN UNZERSTÖRBAR WEITER:
                $teile = explode('/', $flurstueckRaw);
                $zaehler = preg_replace('/^[0\s]+/', '', preg_replace('/[^\d]/', '', $teile[0] ?? $flurstueckRaw));
                $nenner = (count($teile) > 1 && trim($teile[1]) !== '0' && trim($teile[1]) !== '') ? preg_replace('/^[0\s]+/', '', preg_replace('/[^\d]/', '', $teile[1])) : null;

                // REVISIONSSCHUTZ: Prüfen, ob genau dieses Flurstück bereits AKTIV im Betrieb steht
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
                    continue; 
                }

                $neueUuid = (string) Str::uuid();

                // Flurstück im Zustand UNDEFINIERT (Version 1) in die parzellen-Tabelle stanzen
                DB::table('parzellen')->insert([
                    'parzelle_uuid'       => $neueUuid,
                    'version'             => 1,
                    'freigabe_status'     => 'undefiniert',
                    'polygon_vektoren'    => $geometry ? json_encode($geometry) : null,
                    'gemeinde'            => $gemeinde,
                    'gemarkung'           => $gemarkung,
                    'gemarkungsschuelser' => $gemarkungsschluessel,
                    'flur'                => $flur,
                    'flurstueck_zaehler'  => $zaehler,
                    'flurstueck_nenner'   => $nenner,
                    'flurname_lage'       => $flurnameLage,
                    'amtliche_flaeche_m2' => $amtlicheFlaeche,
                    'besitz_status'       => 'undefiniert',
                    'aenderungsgrund'     => 'In den Pacht/Kaufvertrags-Warenkorb aufgenommen',
                    'gueltig_von'         => $jetzt,
                    'gueltig_bis'         => null,
                    'user_id'             => Auth::id(),
                    'created_at'          => $jetzt,
                    'updated_at'          => $jetzt
                ]);

                // Die polymorphe Beziehung in parzelle_vertrag einhängen
                DB::table('parzelle_vertrag')->insert([
                    'parzelle_uuid'          => $neueUuid,
                    'vertragable_id'         => $vertragId,
                    'vertragable_type'       => \App\Models\VinicoreVertrag::class,
                    'zugeordneter_wert'      => 0.00,
                    'zugeordneter_wert'      => 0.00,
                    'zugeordnete_flaeche_m2' => $amtlicheFlaeche,
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
                       . "&STARTINDEX=0&COUNT=10000&SRSNAME=urn:ogc:def:crs:EPSG::3857" 
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

                // 🚀 ORIGINAL-FILTER VON HEUTE MITTAG: Prüft, ob das Flurstück bereits im grünen Bestand existiert
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

                // ❌ LÖSCH-ZÜNDER: Wenn die Fläche bei uns im Bestand ist, überspringen wir sie radikal!
                // Sie wird erst gar nicht in das blaue Array ($features) geschrieben und blockiert im Browser nichts mehr!
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
     * 📊 DER DIGITALE KATASTERSPIEGEL (TABELLEN-COCKPIT)
     * 🚀 REVISIONS-FIX: Holt Entwürfe & Aktive, unterstützt Live-Sortierung sowie Suche
     * und liefert alle vom Template geforderten Variablen fehlerfrei aus!
     */
    public function parzellenUebersichtView(Request $request): \Illuminate\Contracts\View\View
    {
        // 🔍 Such- und Sortierparameter aus dem Request abfangen
        $suche = $request->input('suche', '');
        $sortSpalte = $request->input('sort', 'gemarkung');
        $sortRichtung = $request->input('direction', 'asc');

        // Validierungs-Schutz, damit niemand falsche Spalten injizieren kann
        if (!in_array($sortSpalte, ['gemarkung', 'flur', 'flurstueck_zaehler', 'amtliche_flaeche_m2', 'besitz_status'])) {
            $sortSpalte = 'gemarkung';
        }
        if (!in_array($sortRichtung, ['asc', 'desc'])) {
            $sortRichtung = 'asc';
        }

        // Basis-Query aufbauen (Entwürfe & Aktive des aktuellen Bestands)
        $query = DB::table('parzellen')
            ->whereIn('freigabe_status', ['undefiniert', 'aktiv'])
            ->whereNull('gueltig_bis');

        // Falls eine Filter-Suche eingetippt wurde, schränken wir die Query ein
        if (!empty($suche)) {
            $query->where(function($q) use ($suche) {
                $q->where('gemarkung', 'like', '%' . $suche . '%')
                  ->orWhere('flurstueck_zaehler', 'like', '%' . $suche . '%')
                  ->orWhere('flurname_lage', 'like', '%' . $suche . '%');
            });
        }

        // Dynamische Sortierung und Paginierung anwenden
        $parzellen = $query->orderBy($sortSpalte, $sortRichtung)->paginate(10);

        // Holt den aktuell angemeldeten User für das Layout
        $user = auth()->user();

        // 🎯 COMPACT-FIX: Liefert alle vom HTML-Tabellenkopf verlangten Variablen unzerbrechlich aus!
        return view('kataster.parzellen_uebersicht', compact(
            'parzellen', 
            'user', 
            'suche', 
            'sortSpalte', 
            'sortRichtung'
        ));
    }

    /**
     * 📡 UNIVERSAL DETAIL-API FÜR DEN GLOBALEN INSPEKTOR
     * 🛡️ REVISIONS-REINHEIT: Keine Abfrage auf nicht existierende Agrar-Tabellen!
     */
    public function holeParzelleDetails($uuid): JsonResponse
    {
        try {
            // Holt das Flurstück starr und ohne Joins direkt aus der parzellen-Tabelle
            $parzelle = DB::table('parzellen')
                ->where('parzelle_uuid', $uuid)
                ->whereNull('gueltig_bis')
                ->first();

            if (!$parzelle) {
                return response()->json(['success' => false, 'message' => 'Liegenschaft im aktiven Bestand nicht lokalisiert.'], 404);
            }

            // 🎯 REINHEITS-GEBOT: Gibt exakt die Spaltenwerte deines Git-Migrationsstands aus!
            return response()->json([
                'success' => true,
                'parzelle' => [
                    'id'                  => $parzelle->id,
                    'parzelle_uuid'       => $parzelle->parzelle_uuid,
                    'version'             => $parzelle->version,
                    'freigabe_status'     => $parzelle->freigabe_status,
                    'gemarkung'           => $parzelle->gemarkung,
                    'flur'                => $parzelle->flur,
                    'flurstueck_zaehler'  => $parzelle->flurstueck_zaehler,
                    'flurstueck_nenner'   => $parzelle->flurstueck_nenner,
                    'flurname_lage'       => $parzelle->flurname_lage,
                    'amtliche_flaeche_m2' => $parzelle->amtliche_flaeche_m2,
                    'besitz_status'       => $parzelle->besitz_status,
                    'anlage_name'         => null, // Wird in Phase 4 mit der echten 'anlagen'-Tabelle verknüpft
                    'schlag_name'         => null  // Wird in Phase 4 mit der echten 'schlaege'-Tabelle verknüpft
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Kernel-Sperre: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 📝 HISTORISIERTE REVISION & ERSTPRÜFUNG (PHASE 2)
     * 🛡️ REVISIONS-SCHUTZ: Blockiert manuelle Änderungen des Besitzverhältnisses ab Version 2!
     */
    public function aktualisiereParzelle(Request $request, $uuid): JsonResponse
    {
        $request->validate([
            'besitz_status'   => 'required|in:eigentum,gepachtet,verpachtet,undefiniert',
            'flurname_lage'   => 'nullable|string|max:255',
            'aenderungsgrund' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();
            $jetzt = now();

            // 1. Holt den aktuellen Zustand der Parzelle aus der Datenbank
            $alteParzelle = DB::table('parzellen')
                ->where('parzelle_uuid', $uuid)
                ->whereNull('gueltig_bis')
                ->first();

            if (!$alteParzelle) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Liegenschaft nicht gefunden.'], 404);
            }

            $aktuelleVersion = intval($alteParzelle->version);

            // 🛡️ INTELLIGENTE REVISIONS-SCHRANKE (MIGRATIONS-WEICHE)
            // Erlaubt das Setzen nur, wenn der Status in der DB aktuell noch 'undefiniert' ist!
            if ($alteParzelle->besitz_status !== 'undefiniert') {
                // Sobald die Parzelle einmal fest zugewiesen ist, blockiert jeder Änderungsversuch!
                if ($alteParzelle->besitz_status !== $request->input('besitz_status')) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false, 
                        'message' => 'ERP-Sperre: Das Besitzverhältnis ist vertragsgesteuert und versiegelt. Manuelle Änderungen sind blockiert!'
                    ], 422);
                }
            }

          // 2. Zeitschloss: Alten Datensatz für den historischen Audit-Trail einfrieren
            DB::table('parzellen')
                ->where('id', $alteParzelle->id)
                ->update(['gueltig_bis' => $jetzt, 'updated_at' => $jetzt]);

            // 3. Neuen Zustand mit Version + 1 und Status 'aktiv' einstanzen
            DB::table('parzellen')->insert([
                'parzelle_uuid'       => $uuid,
                'version'             => $aktuelleVersion + 1,
                'freigabe_status'     => 'aktiv', // 🟢 Schaltet auf Sattes Grün um!
                'polygon_vektoren'    => $alteParzelle->polygon_vektoren,
                'gemeinde'            => $alteParzelle->gemeinde,
                'gemarkung'           => $alteParzelle->gemarkung,
                'gemarkungsschuelser' => $alteParzelle->gemarkungsschuelser,
                'flur'                => $alteParzelle->flur,
                'flurstueck_zaehler'  => $alteParzelle->flurstueck_zaehler,
                'flurstueck_nenner'   => $alteParzelle->flurstueck_nenner,
                'flurname_lage'       => trim($request->input('flurname_lage') ?? $alteParzelle->flurname_lage),
                'amtliche_flaeche_m2' =>$alteParzelle->amtliche_flaeche_m2,
                // Bei V1 greift die automatisierte Vorbelegung, ab V2 ist es starr fixiert!
                'besitz_status'       => ($aktuelleVersion === 1) ? $request->input('besitz_status') : $alteParzelle->besitz_status,
                
                'aenderungsgrund'     => trim($request->input('aenderungsgrund') ?? 'Liegenschafts-Matrix aktualisiert'),
                'gueltig_von'         => $jetzt,
                'gueltig_bis'         => null,
                'user_id'             => Auth::id(),
                'created_at'          => $alteParzelle->created_at,
                'updated_at'          => $jetzt
            ]);

            // 🔓 Record Lock aufheben
            DB::table('parzellen_locks')->where('parzelle_uuid', $uuid)->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Liegenschaft erfolgreich historisiert saniert!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Absturz im Revisions-Kernel: ' . $e->getMessage()], 500);
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
    /**
     * 🛰️ VERTRAGS-STAMMDATEN IM SERVER-RAM PUFFERN
     * Schützt den Entwurf absolut sicher im PHP-Sitzungsspeicher vor dem Verlust! [2]
     */
    public function parkeStammdatenInSession(Request $request):JsonResponse
    {
        // Validierung der Mindestanforderungen [2]
        $request->validate([
            'vertrag_nummer' => 'required',
            'typ'            => 'required'
        ]);

        // Schreibt die Formulardaten sicher in die servereigene PHP-Session [2]
        session(['vinicore_schwebe_vertrag' => $request->only([
            'vertrag_nummer', 'typ', 'vertragspartner_name', 'gesamtwert', 'gueltig_von', 'gueltig_bis'
        ])]);

        return response()->json([
            'success' => true, 
            'message' => 'Stammdaten im Server-Sitzungsspeicher fixiert.'
        ]);
    }

}
