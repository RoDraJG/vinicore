<?php

namespace App\Modules\Configuration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Modules\CRM\Models\CRMEinstellung;
use Carbon\Carbon;

class GlobalNummernkreis extends Model
{
    /**
     * Generiert die nächste formatierte Nummer – vollautomatisch historisiert.
     * Unterstützt dynamische Nullen-Definitionen wie {ZAEHLER;3} direkt im Muster.
     */
    public static function generiereNaechsteNummer(string $kreisKey, ?Carbon $belegDatum = null): string
    {
        return DB::transaction(function () use ($kreisKey, $belegDatum) {
            $zielZeit = $belegDatum ?? Carbon::now();

            // 🎯 ZEITFENSTER-FILTER: Holt den exakt gültigen Zeitraum aus den Einstellungen
            $kreis = CRMEinstellung::where('typ', 'nummernkreis')
                ->where('kreis_key', $kreisKey)
                ->where(function ($query) use ($zielZeit) {
                    $query->whereNull('gueltig_von')->orWhere('gueltig_von', '<=', $zielZeit);
                })
                ->where(function ($query) use ($zielZeit) {
                    $query->whereNull('gueltig_bis')->orWhere('gueltig_bis', '>=', $zielZeit);
                })
                ->lockForUpdate()
                ->first();

            if (!$kreis) {
                $kreis = CRMEinstellung::where('typ', 'nummernkreis')->where('kreis_key', $kreisKey)->whereNull('gueltig_von')->whereNull('gueltig_bis')->lockForUpdate()->first();
            }

            if (!$kreis) {
                throw new \Exception("Kein gültiger Nummernkreis für '{$kreisKey}' im Belegzeitraum definiert.");
            }

            // 1. Zähler atomar hochzählen und sichern
            $neuerZaehler = $kreis->zaehlerstand + 1;
            $kreis->update(['zaehlerstand' => $neuerZaehler]);

            // 2. Zeitparameter über Carbon extrahieren
            $jahrLang  = $zielZeit->format('Y'); 
            $jahrKurz  = $zielZeit->format('y'); 
            $monat     = $zielZeit->format('m'); 
            $kw        = $zielZeit->format('W'); 
            $tagWoche  = $zielZeit->format('N'); 
            $tagJahr   = str_pad($zielZeit->dayOfYear, 3, '0', STR_PAD_LEFT); 

            // 3. Erst Basis-Datums-Ersetzungen durchführen
            $ergebnis = $kreis->muster;
            $ergebnis = str_replace('{JJJJ}', $jahrLang, $ergebnis);
            $ergebnis = str_replace('{JJ}', $jahrKurz, $ergebnis);
            $ergebnis = str_replace('{MM}', $monat, $ergebnis);
            $ergebnis = str_replace('{KW}', $kw, $ergebnis);
            $ergebnis = str_replace('{TAG_WOCHE}', $tagWoche, $ergebnis);
            $ergebnis = str_replace('{TAG_JAHR}', $tagJahr, $ergebnis);

            // 4. 🎯 DER ADVANCED REGEX-PARSER FÜR {ZAEHLER;X}
            // Sucht nach '{ZAEHLER;3}', '{ZAEHLER;10}' etc.
            $ergebnis = preg_replace_callback('/\{ZAEHLER;(\d+)\}/', function ($matches) use ($neuerZaehler) {
                $laenge = intval($matches[1]); // Extrahiert die Zahl hinter dem Semikolon
                return str_pad(strval($neuerZaehler), $laenge, '0', STR_PAD_LEFT);
            }, $ergebnis);

            // Fallback: Falls der Benutzer nur das einfache {ZAEHLER} ohne Semikolon getippt hat
            $ergebnis = str_replace('{ZAEHLER}', strval($neuerZaehler), $ergebnis);

            return $ergebnis;
        });
    }
}
