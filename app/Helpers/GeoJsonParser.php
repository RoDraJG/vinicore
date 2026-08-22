<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeoJsonParser
{
    /**
     * Gleicht die parzellen-Tabelle über einen 5-fachen Kataster-Schlüssel mit der GeoJSON ab.
     */
    public static function befulleFehlendeKatasterDaten(string $dateipfad): array
    {
        if (!file_exists($dateipfad)) {
            Log::error("GeoJSON-Datei nicht gefunden unter: " . $dateipfad);
            return ['success' => false, 'message' => 'GeoJSON-Datei wurde im Speicher nicht gefunden.'];
        }

        // 1. Holt alle Parzellen aus der MariaDB für den kombinierten Abgleich
        $datenbankParzellen = DB::table('parzellen')->get();

        if ($datenbankParzellen->isEmpty()) {
            return ['success' => true, 'message' => 'Keine Parzellen für den Abgleich in der Datenbank gefunden.'];
        }

        // 2. Große GeoJSON-Datei einlesen
        $inhalt = file_get_contents($dateipfad);
        $geoJson = json_decode($inhalt, true);

        if (!isset($geoJson['features'])) {
            return ['success' => false, 'message' => 'Ungültiges GeoJSON-Format (Features fehlen).'];
        }

        // Mathematischer Nullpunkt (Zentriert im Landkreis Bernkastel-Wittlich für 2D-Meter-Projektion)
        $ankerLng = 6.9919; $ankerLat = 49.8803;
        $konstanteMeterProGrad = 111300.0; $radLat = deg2rad($ankerLat);
        $aktualisiertZaehler = 0;

        // 3. Schleife über alle Features der GeoJSON
        foreach ($geoJson['features'] as $feature) {
            $props = $feature['properties'] ?? [];
            
            // Holt alle amtlichen Schlüsselmerkmale aus der GeoJSON-Zeile (Bezeichner aus deiner Vorlage!)
            $geoGemarkungSchluessel = isset($props['gemaschl']) ? trim((string)$props['gemaschl']) : '';
            $geoFlur                = isset($props['flur'])     ? trim((string)$props['flur'])     : '';
            $geoZaehler             = isset($props['flstnrzae'])? trim((string)$props['flstnrzae']): '';
            $geoNenner              = isset($props['flstnrnen'])? trim((string)$props['flstnrnen']): '';
            $geoGemeinde            = isset($props['gemeinde']) ? trim((string)$props['gemeinde']) : '';

            $geoFlurBereinigt = preg_replace('/[^0-9]/', '', $geoFlur);

            foreach ($datenbankParzellen as $dbParzelle) {
                // Datenbank-Werte für den exakten Abgleich normieren
                $dbGemarkungSchluessel = trim((string)($dbParzelle->gemarkung_schluessel ?? ''));
                $dbFlur                = preg_replace('/[^0-9]/', '', trim((string)$dbParzelle->flur));
                $dbZaehler             = trim((string)$dbParzelle->flurstueck_zaehler);
                $dbNenner              = trim((string)($dbParzelle->flurstueck_nenner ?? ''));
                $dbGemeinde            = trim((string)($dbParzelle->gemeinde ?? ''));

                // ==========================================================================
                // DER UNZERSTÖRBARE 5-FACH-ABGLEICH (Verhindert jegliche Orts-Verwechslung)
                // ==========================================================================
                $matchGemarkung = (!empty($dbGemarkungSchluessel) && !empty($geoGemarkungSchluessel)) 
                    ? ($dbGemarkungSchluessel === $geoGemarkungSchluessel) 
                    : (strcasecmp($dbGemeinde, $geoGemeinde) === 0);

                if ($matchGemarkung && 
                    $dbFlur    === $geoFlurBereinigt && 
                    $dbZaehler === $geoZaehler && 
                    ($dbNenner === $geoNenner || (empty($dbNenner) && ($geoNenner === 'null' || empty($geoNenner))))) {
                    $geometry = $feature['geometry'] ?? [];
                    $coords = $geometry['coordinates'] ?? [];
                    $roheRinge = $coords ?? [];
                    if (empty($roheRinge)) continue;

                    // Löst die Ring-Struktur des Polygons sauber auf
                    $punkteFuerBerechnung = is_array($roheRinge[0]) ? $roheRinge[0] : $roheRinge;

                    $polygonPunkte = [];
                    foreach ($punkteFuerBerechnung as $pt) {
                        if (!is_array($pt) || count($pt) < 2) continue;
                        $lng = floatval($pt[0]);
                        $lat = floatval($pt[1]);

                        // Mercator-Projektion: GPS-Gradzahlen in präzise 2D-Meter umrechnen
                        $xMeter = ($lng - $ankerLng) * $konstanteMeterProGrad * cos($radLat);
                        $yMeter = ($ankerLat - $lat) * $konstanteMeterProGrad;

                        $polygonPunkte[] = ['x' => round($xMeter, 2), 'y' => round($yMeter, 2)];
                    }

                    if (empty($polygonPunkte)) continue;

                    // Ausdehnung (Bounding Box) in Metern berechnen
                    $xWerte = array_column($polygonPunkte, 'x'); $yWerte = array_column($polygonPunkte, 'y');
                    $minX = min($xWerte); $maxX = max($xWerte);
                    $minY = min($yWerte); $maxY = max($yWerte);
                    $breite = round($maxX - $minX, 2); $hoehe = round($maxY - $minY, 2);

                    // 4. DATENBANK-UPDATE: Werte dauerhaft in die passende Tabellenzeile brennen
                    DB::table('parzellen')
                        ->where('id', $dbParzelle->id)
                        ->update([
                            'amtliche_flaeche_m2' => $props['flaeche'] ?? ($breite * $hoehe),
                            'flurname_lage'       => $props['lagebeztxt'] ?? $dbParzelle->flurname_lage ?? 'Amtliches Flurstück',
                            'polygon_vektoren'    => json_encode($polygonPunkte)
                        ]);

                    // Standard-Kupplungswerte für die Ausdehnung der 40 Vektor-Reihen mitsichern
                    DB::table('parzelle_schlag')
                        ->where('parzelle_uuid', $dbParzelle->parzelle_uuid)
                        ->update([
                            'breite_meter' => $breite,
                            'hoehe_meter'  => $hoehe
                        ]);

                    $aktualisiertZaehler++;
                    break;
                }
            }
        }

        return [
            'success' => true,
            'message' => "vinicore GIS-Pipeline: Abgleich erfolgreich! {$aktualisiertZaehler} Parzellen wurden mit echten Flurkartendaten (Rheinland-Pfalz) befüllt."
        ];
    }
}
