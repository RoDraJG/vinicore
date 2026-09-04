<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Modules\CRM\Models\CRMEinstellung; // Bindet deine Modul-Tabelle ein
use Carbon\Carbon;

class GlobalNummernkreis extends Model
{
    /**
     * Generiert die nächste formatierte Nummer – vollautomatisch zeitgesteuert und historisiert.
     * Erhöht zeitgleich atomar den Zählerstand im sicheren SQL-Lock-Verfahren.
     */
    public static function generiereNaechsteNummer(string $kreisKey, ?Carbon $belegDatum = null): string
    {
        return DB::transaction(function () use ($kreisKey, $belegDatum) {
            $zielZeit = $belegDatum ?? Carbon::now();

            // 🎯 ZEITFENSTER-FILTER: Holt den für den Belegzeitpunkt exakt gültigen Zeitraum
            $kreis = CRMEinstellung::where('typ', 'nummernkreis')
                ->where('kreis_key', $kreisKey)
                ->where(function ($query) use ($zielZeit) {
                    $query->whereNull('gueltig_von')
                          ->orWhere('gueltig_von', '<=', $zielZeit);
                })
                ->where(function ($query) use ($zielZeit) {
                    $query->whereNull('gueltig_bis')
                          ->orWhere('gueltig_bis', '>=', $zielZeit);
                })
                ->lockForUpdate()
                ->first();

            // Fallback auf unbegrenzten Standardeintrag, falls kein spezifischer Zeitraum greift
            if (!$kreis) {
                $kreis = CRMEinstellung::where('typ', 'nummernkreis')->where('kreis_key', $kreisKey)->whereNull('gueltig_von')->whereNull('gueltig_bis')->lockForUpdate()->first();
            }

            if (!$kreis) {
                throw new \Exception("Kein gültiger Nummernkreis für '{$kreisKey}' im Belegzeitraum definiert.");
            }

            // 1. Zähler hochzählen und sichern
            $neuerZaehler = $kreis->zaehlerstand + 1;
            $kreis->update(['zaehlerstand' => $neuerZaehler]);

            // 2. Zähler mit führenden Nullen präparieren
            $zaehlerFormatiert = strval($neuerZaehler);
            if ($kreis->fuehrende_nullen > 0) {
                $zaehlerFormatiert = str_pad($zaehlerFormatiert, $kreis->fuehrende_nullen, '0', STR_PAD_LEFT);
            }

            // 3. Zeitparameter über Carbon extrahieren
            $jahrLang  = $zielZeit->format('Y'); // z.B. 2026
            $jahrKurz  = $zielZeit->format('y'); // z.B. 26
            $monat     = $zielZeit->format('m'); // z.B. 09
            $kw        = $zielZeit->format('W'); // Kalenderwoche (01-53)
            $tagWoche  = $zielZeit->format('N'); // Wochentag (1 = Montag, 7 = Sonntag)
            $tagJahr   = str_pad($zielZeit->dayOfYear, 3, '0', STR_PAD_LEFT); // Tag des Jahres (001-366)

            // 4. 🎯 EXKLUSIVER KLAMMER-PARSER: Ersetzt ausschließlich geschützte Ausdrücke
            $ergebnis = $kreis->muster;
            $ergebnis = str_replace('{ZAEHLER}', $zaehlerFormatiert, $ergebnis);
            $ergebnis = str_replace('{JJJJ}', $jahrLang, $ergebnis);
            $ergebnis = str_replace('{JJ}', $jahrKurz, $ergebnis);
            $ergebnis = str_replace('{MM}', $monat, $ergebnis);
            $ergebnis = str_replace('{KW}', $kw, $ergebnis);
            $ergebnis = str_replace('{TAG_WOCHE}', $tagWoche, $ergebnis);
            $ergebnis = str_replace('{TAG_JAHR}', $tagJahr, $ergebnis);

            return $ergebnis;
        });
    }
}
