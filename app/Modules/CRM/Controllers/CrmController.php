<?php

namespace App\Modules\CRM\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Models\CrmKontakt;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    /**
     * 📊 ZENTRALE LISTENANSICHT (Mit serverseitiger Suche & Seitenselektor)
     */
    public function index(Request $request)
    {
        // 1. Parameter aus der URL abgreifen (Standard: kunde / Suchbegriff leer)
        $typ = $request->get('typ', 'kunde');
        $search = $request->get('suche', '');

        // 2. Eloquent-Query initialisieren [source: 1.2.4]
        $query = CrmKontakt::query();

        // 3. Typ-Filter anwenden (Kunde, Lieferant oder NEU: Alle)
        if ($typ === 'kunde') {
            $query->kunden();
        } elseif ($typ === 'lieferant') {
            $query->lieferanten();
        }

        // 4. 🔍 SERVERSEITIGE GANZ-DATENBANK-SUCHE (Sucht über alle Seiten hinweg!) [source: 1.2.4]
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

        // 5. Sortieren und mit Seitenteilung (10 Einträge pro Seite) ausgeben [source: 1.2.2]
        // appends() sorgt dafür, dass Filter & Suche beim Umblättern nicht verloren gehen! [source: 1.1.3]
        $kontakte = $query->orderBy('nachname')
            ->paginate(10)
            ->appends(['typ' => $typ, 'suche' => $search]);

        return view('CRM::index', compact('kontakte', 'typ', 'search'));
    }

    /**
     * 📝 ERFASSUNGS-FENSTER
     */
    public function create()
    {
        return view('CRM::create');
    }

    /**
     * 💾 UNIVERSAL-SPEICHERUNG
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nachname' => 'required|string|max:255',
            'vorname' => 'nullable|string|max:255',
            'firma' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefon' => 'nullable|string|max:255',
            'strasse_nr' => 'nullable|string|max:255',
            'plz' => 'nullable|string|max:20',
            'ort' => 'nullable|string|max:255',
            'kunden_kategorie' => 'required|string|in:privat,gastro,handel',
            'standard_zahlungsziel_tage' => 'required|integer|min:0',
            'ust_id' => 'nullable|string|max:20',
            'notizen' => 'nullable|string',
        ]);

        $validated['ist_kunde'] = $request->has('ist_kunde');
        $validated['ist_lieferant'] = $request->has('ist_lieferant');

        if (!$validated['ist_kunde'] && !$validated['ist_lieferant']) {
            $validated['ist_kunde'] = true;
        }

        if ($validated['ist_kunde']) {
            $maxKl = CrmKontakt::max('kundennummer');
            $validated['kundennummer'] = $maxKl ? intval($maxKl) + 1 : 10000;
        }

        if ($validated['ist_lieferant']) {
            $maxLf = CrmKontakt::max('lieferantennummer');
            $validated['lieferantennummer'] = $maxLf ? intval($maxLf) + 1 : 50000;
        }

        CrmKontakt::create($validated);

        return redirect()->route('crm.index', ['typ' => $validated['ist_lieferant'] ? 'lieferant' : 'kunde'])
            ->with('success', 'Geschäftspartner erfolgreich erfasst!');
    }
        /**
     * Öffnet die detaillierte B2B Enterprise-Partnerakte
     */
    public function show($id)
    {
        // Den Partner aus der Datenbank laden oder 404 auswerfen
        $kontakt = CRMKontakt::findOrFail($id);


        return view('CRM::show', compact('kontakt'));
    }
}
