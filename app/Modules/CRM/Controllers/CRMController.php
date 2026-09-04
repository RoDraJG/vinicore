<?php

namespace App\Modules\CRM\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Models\CRMKontakt;
use App\Modules\CRM\Models\CRMEinstellung; // 🎯 WICHTIG: Bindet das Einstellungs-Model ein
use Illuminate\Http\Request;

class CRMController extends Controller
{
    /**
     * 📊 ZENTRALE LISTENANSICHT (Mit serverseitiger Suche & Seitenselektor)
     */
    public function index(Request $request)
    {
        // 1. Parameter aus der URL abgreifen (Standard: kunde / Suchbegriff leer)
        $typ = $request->get('typ', 'kunde');
        $search = $request->get('suche', '');

        // 2. Eloquent-Query über das Modul-Model initialisieren
        $query = CRMKontakt::query();

        // 3. Typ-Filter anwenden (Kunde, Lieferant oder Alle)
        if ($typ === 'kunde') {
            $query->kunden();
        } elseif ($typ === 'lieferant') {
            $query->lieferanten();
        }

        // 4. 🔍 SERVERSEITIGE LIVESUCHE-ERKENNUNG (Sucht flexibel über alle Spalten)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $term = '%' . $search . '%';
                $q->where('nachname', 'LIKE', $term)
                  ->orWhere('vorname', 'LIKE', $term)
                  ->orWhere('firma', 'LIKE', $term)
                  ->orWhere('kundennummer', 'LIKE', $term)
                  ->orWhere('lieferantennummer', 'LIKE', $term)
                  ->orWhere('email', 'LIKE', $term);
            });
        }

        // 5. Sortieren und mit Seitenteilung (10 Einträge pro Seite) ausgeben
        $kontakte = $query->orderBy('nachname')
            ->paginate(10)
            ->appends(['typ' => $typ, 'suche' => $search]);

        return view('CRM::index', compact('kontakte', 'typ', 'search'));
    }

    /**
     * 🎯 REPARATUR: DIE NEUE CREATE-METHODE FÜR DATENBANK-DROPDOWNS
     * Lädt alle Dropdown-Optionen vollautomatisch und dynamisch aus den crm_einstellungen
     */
    public function create()
    {
        $search = request('suche', '');
        $typ = request('typ', 'kunde');

        // Holt alle Optionen sortiert aus der neuen Einstellungstabelle und gruppiert sie nach Typ
        $einstellungen = CRMEinstellung::orderBy('sortierung')->get()->groupBy('typ');

        // Aufteilen in die spezifischen Variablen für das Formular-Looping
        $konfig_segmente    = $einstellungen->get('segment', collect())->pluck('wert', 'code')->toArray();
        $konfig_steuerzonen = $einstellungen->get('steuerzone', collect())->pluck('wert', 'code')->toArray();
        $konfig_incoterms   = $einstellungen->get('incoterm', collect())->pluck('wert', 'code')->toArray();
        $konfig_logistiker  = $einstellungen->get('logistiker', collect())->pluck('wert', 'code')->toArray();
        $konfig_stilistiken = $einstellungen->get('stilistik', collect())->pluck('wert', 'code')->toArray();
        $konfig_kanaele     = $einstellungen->get('kanal', collect())->pluck('wert', 'code')->toArray();

        return view('CRM::create', compact(
            'search', 
            'typ', 
            'konfig_segmente', 
            'konfig_steuerzonen', 
            'konfig_incoterms', 
            'konfig_logistiker', 
            'konfig_stilistiken', 
            'konfig_kanaele'
        ));
    }
    /**
     * Versiegelt neue Partner – vollautomatisch gesplittet nach Privat- oder B2B-Firmenkunde
     */
    public function store(Request $request)
    {
        // 1. Vor-Validierung des Typen, um die Pflichtfelder mathematisch zu splitten
        $partnerTyp = $request->input('partner_typ', 'privat');

        $regeln = [
            'partner_typ' => 'required|string|in:privat,firma',
            'strasse' => 'nullable|string|max:150',
            'hausnummer' => 'nullable|string|max:20',
            'adresszusatz' => 'nullable|string|max:255',
            'plz' => 'nullable|string|max:10',
            'ort' => 'nullable|string|max:255',
            'standard_zahlungsziel_tage' => 'required|integer|min:0',
            'individueller_rabatt_prozent' => 'required|numeric|min:0|max:100',
            'skonto_prozent' => 'required|numeric|min:0|max:100',
            'skonto_tage' => 'required|integer|min:0',
            'email' => 'nullable|email|max:255',
            'telefon' => 'nullable|string|max:255',
            'notizen' => 'nullable|string',
            // Weitere Fiskal- und Logistikfelder deiner Migration hier einfügen...
        ];

        // 🎯 DYNAMISCHE PFLICHTFELD-WEICHE (Perfekt ausgereift für Einzelunternehmen!)
        if ($partnerTyp === 'firma') {
            // Bei Firmen ist der Firmenname Pflicht. Vor-/Nachname sind OPTIONAL für Einzelunternehmer!
            $regeln['firma'] = 'required|string|max:255';
            $regeln['nachname'] = 'nullable|string|max:255';
            $regeln['vorname'] = 'nullable|string|max:255';
        } else {
            // Bei Privatpersonen gibt es keine Firma. Nachname ist zwingend Pflicht!
            $regeln['firma'] = 'nullable|string|max:255';
            $regeln['nachname'] = 'required|string|max:255';
            $regeln['vorname'] = 'nullable|string|max:255';
        }


        $validated = $request->validate($regeln);

        // 2. Checkboxen auswerten
        $validated['ist_kunde'] = $request->has('ist_kunde') ? 1 : 0;
        $validated['ist_lieferant'] = $request->has('ist_lieferant') ? 1 : 0;
        $validated['ist_gesperrt'] = $request->has('ist_gesperrt') ? 1 : 0;

        if (!$validated['ist_kunde'] && !$validated['ist_lieferant']) {
            $validated['ist_kunde'] = 1;
        }

        // 3. Nummernkreise über das neue, zentrale Configuration-Modul ziehen
        if ($validated['ist_kunde']) {
            $validated['kundennummer'] = \App\Modules\Configuration\Models\GlobalNummernkreis::generiereNaechsteNummer('kunde');
        }
        if ($validated['ist_lieferant']) {
            $validated['lieferantennummer'] = \App\Modules\Configuration\Models\GlobalNummernkreis::generiereNaechsteNummer('lieferant');
        }

        // 4. Haupt-Partner in der Datenbank anlegen
        $kontakt = \App\Modules\CRM\Models\CRMKontakt::create($validated);

        // 5. 🎯 MULTI-KONTAKT-SCHLEIFE: Speichert unbegrenzt viele Personen/Kanäle
        $details = $request->input('kontakte_details', []);
        if (is_array($details)) {
            foreach ($details as $d) {
                if (!empty($d['email']) || !empty($d['telefon']) || !empty($d['ansprechpartner_name'])) {
                    $kontakt->ansprechpartner()->create([
                        'abteilung' => $d['abteilung'] ?? null,
                        'ansprechpartner_name' => $d['ansprechpartner_name'] ?? null,
                        'email' => $d['email'] ?? null,
                        'telefon' => $d['telefon'] ?? null,
                        'ist_hauptkontakt' => isset($d['ist_hauptkontakt']) ? 1 : 0,
                        'notiz' => $d['notiz'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('crm.index')->with('success', 'Partner erfolgreich im Register versiegelt!');
    }



    /**
     * Öffnet die detaillierte B2B Enterprise-Partnerakte
     */
    public function show($id)
    {
        $kontakt = CRMKontakt::findOrFail($id);
        return view('CRM::show', compact('kontakt'));
    }
        
    /**
     * Zeigt das Bearbeitungsformular für einen bestehenden Partner
     */
    public function edit($id)
    {
        $kontakt = CRMKontakt::findOrFail($id);
        
        // Lädt die dynamischen Dropdown-Listen aus der DB für den Edit-Modus
        $einstellungen = CRMEinstellung::orderBy('sortierung')->get()->groupBy('typ');
        $konfig_segmente    = $einstellungen->get('segment', collect())->pluck('wert', 'code')->toArray();
        $konfig_steuerzonen = $einstellungen->get('steuerzone', collect())->pluck('wert', 'code')->toArray();
        $konfig_incoterms   = $einstellungen->get('incoterm', collect())->pluck('wert', 'code')->toArray();
        $konfig_logistiker  = $einstellungen->get('logistiker', collect())->pluck('wert', 'code')->toArray();
        $konfig_stilistiken = $einstellungen->get('stilistik', collect())->pluck('wert', 'code')->toArray();
        $konfig_kanaele     = $einstellungen->get('kanal', collect())->pluck('wert', 'code')->toArray();

        return view('CRM::edit', compact(
            'kontakt',
            'konfig_segmente', 
            'konfig_steuerzonen', 
            'konfig_incoterms', 
            'konfig_logistiker', 
            'konfig_stilistiken', 
            'konfig_kanaele'
        ));
    }

    /**
     * Aktualisiert die geänderten Partnerdaten in der Datenbank
     */
    public function update(Request $request, $id)
    {
        $kontakt = CRMKontakt::findOrFail($id);

        // 1. Vollständige Validierung für alle neuen Adress- und Fiskalspalten beim Update
        // 1. B2B-Weiche: Nachname ist nur Pflicht, wenn KEIN Firmenname eingetragen wurde!
        $validated = $request->validate([
            'firma' => 'nullable|string|max:255',
            'nachname' => 'required_without:firma|nullable|string|max:255', // 🎯 DYNAMISCH
            'vorname' => 'nullable|string|max:255',
            // ... alle restlichen Felder bleiben exakt so bestehen wie sie sind ...

            'ansprechpartner_name' => 'nullable|string|max:255',
            'strasse' => 'nullable|string|max:150',
            'hausnummer' => 'nullable|string|max:20',
            'adresszusatz' => 'nullable|string|max:255',
            'plz' => 'nullable|string|max:10',
            'ort' => 'nullable|string|max:255',
            'liefer_strasse' => 'nullable|string|max:150',
            'liefer_hausnummer' => 'nullable|string|max:20',
            'liefer_adresszusatz' => 'nullable|string|max:255',
            'liefer_plz' => 'nullable|string|max:10',
            'liefer_ort' => 'nullable|string|max:255',
            'kunden_kategorie' => 'nullable|string|max:50',
            'buchhaltung_gruppe' => 'nullable|string|max:50',
            'debitorennummer' => 'nullable|string|max:50',
            'kreditorennummer' => 'nullable|string|max:50',
            'standard_zahlungsziel_tage' => 'required|integer|min:0',
            'individueller_rabatt_prozent' => 'required|numeric|min:0|max:100',
            'skonto_prozent' => 'required|numeric|min:0|max:100',
            'skonto_tage' => 'required|integer|min:0',
            'lieferbedingungen' => 'nullable|string|max:50',
            'versanddienstleister' => 'nullable|string|max:50',
            'speditions_hinweis' => 'nullable|string|max:255',
            'ust_id' => 'nullable|string|max:50',
            'steuernummer' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:50',
            'bic' => 'nullable|string|max:50',
            'bevorzugte_weinstilistik' => 'nullable|string|max:50',
            'herkunft_kontakt' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'telefon' => 'nullable|string|max:255',
            'notizen' => 'nullable|string',
        ]);

        // 2. Checkboxen reaktiv auswerten
        $validated['ist_kunde'] = $request->has('ist_kunde') ? 1 : 0;
        $validated['ist_lieferant'] = $request->has('ist_lieferant') ? 1 : 0;
        $validated['ist_gesperrt'] = $request->has('ist_gesperrt') ? 1 : 0;

        // 3. Daten aktualisieren
        $kontakt->update($validated);

        // 4. Zirkuläre Rückleitung auswerten
        if ($request->query('ref') === 'index') {
            return redirect()->route('crm.index')->with('success', 'Partnerdaten erfolgreich im Register aktualisiert!');
        }

        return redirect()->route('crm.show', $id)->with('success', 'Partnerdaten erfolgreich in der Akte aktualisiert!');
    }
} // 🎯 SCHLIESSUNG DER CONTROLLER-KLASSE

