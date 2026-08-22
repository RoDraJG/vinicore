@extends('layouts.app')

@section('content')
<!-- ==========================================================================
     🏛️ VINICORE ERP - AMTLICHER BETRIEBSSPIEGEL (FLURSTÜCKSLISTE)
     ========================================================================== -->
<div class="space-y-4 font-sans text-slate-700 h-full overflow-y-auto pb-8 pr-1">
    
    <!-- 1. MODUL-KOPFZEILE -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs flex flex-wrap justify-between items-center gap-3">
        <div>
            <h1 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-1.5">
                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                Flurstücksliste & Katasterspiegel
            </h1>
            <p class="text-[11px] text-slate-500 font-medium">Rechtssichere Verwaltung, Pachtverhältnisse und lückenlose ALKIS-Historisierung.</p>
        </div>
        <a href="/kataster/parzellen-karte" class="bg-blue-600 hover:bg-blue-700 text-white font-mono font-bold text-[10px] uppercase tracking-wider px-3 py-2 rounded-lg transition shadow-xs no-underline">
            🛰️ Neue Flächen sammeln
        </a>
    </div>

    <!-- 2. SEARCH- & FILTERBAR (NATIVE LIVE-MÜNDUNG) -->
    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-2xs flex items-center justify-between gap-4">
        <div class="w-full max-w-md flex items-center relative">
            <!-- 🚀 CORE-FIX: oninput fängt jeden Tastendruck sofort live ab! -->
            <input type="text" id="liveSucheInput" value="{{ $suche ?? '' }}" oninput="ZündeLiveSuche()" placeholder="Live filtern (Gemarkung, Lage, Status...)" class="w-full text-xs border border-slate-200 rounded-xl pl-8 pr-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 font-medium bg-slate-50/50">
            <div class="absolute left-2.5 top-2.5 text-slate-400">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
            @if(!empty($suche))
                <a href="/parzellen/uebersicht" class="absolute right-2 text-slate-400 hover:text-slate-600 font-bold text-xs p-1 no-underline">&times;</a>
            @endif
        </div>
        <!-- Füge dem Zähler die ID 'trefferZaehlerPille' hinzu: -->
        <div id="trefferZaehlerPille" class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400 bg-slate-50 border px-2 py-1 rounded-md">
            Treffer: {{ count($aktive) }} Parzellen
        </div>
    </div>

    <!-- 3. DIE REAKTIVE KATASTER-TABELLE -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs font-sans">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 font-mono text-[10px] uppercase text-slate-400 tracking-wider">
                        <!-- Gemarkung ist wieder der unumstößliche Startanker -->
                        <th class="p-3 font-bold">
                            <a href="?suche={{ $suche }}&sort=gemarkung&direction={{ $sortSpalte === 'gemarkung' && $sortRichtung === 'asc' ? 'desc' : 'asc' }}" class="flex items-center gap-1 hover:text-slate-700 no-underline">
                                Gemarkung {!! $sortSpalte === 'gemarkung' ? ($sortRichtung === 'asc' ? '▲' : '▼') : '↕' !!}
                            </a>
                        </th>
                        <th class="p-3 font-bold">
                            <a href="?suche={{ $suche }}&sort=flur&direction={{ $sortSpalte === 'flur' && $sortRichtung === 'asc' ? 'desc' : 'asc' }}" class="flex items-center gap-1 hover:text-slate-700 no-underline">
                                Flur {!! $sortSpalte === 'flur' ? ($sortRichtung === 'asc' ? '▲' : '▼') : '↕' !!}
                            </a>
                        </th>
                        <th class="p-3 font-bold">Flurstück</th>
                        <th class="p-3 font-bold">Lagebezeichnung</th>
                        <!-- 🚀 NEU: Die beiden operativen ERP-Verknüpfungsspalten -->
                        <th class="p-3 font-bold text-center">Verknüpfte Anlage</th>
                        <th class="p-3 font-bold text-center">Karte</th>
                        <th class="p-3 font-bold text-right">
                            <a href="?suche={{ $suche }}&sort=amtliche_flaeche_m2&direction={{ $sortSpalte === 'amtliche_flaeche_m2' && $sortRichtung === 'asc' ? 'desc' : 'asc' }}" class="flex items-center justify-end gap-1 hover:text-slate-700 no-underline">
                                Amtliche Fläche {!! $sortSpalte === 'amtliche_flaeche_m2' ? ($sortRichtung === 'asc' ? '▲' : '▼') : '↕' !!}
                            </a>
                        </th>
                        <th class="p-3 font-bold text-center">
                            <a href="?suche={{ $suche }}&sort=besitz_status&direction={{ $sortSpalte === 'besitz_status' && $sortRichtung === 'asc' ? 'desc' : 'asc' }}" class="flex items-center justify-center gap-1 hover:text-slate-700 no-underline">
                                Verhältnis {!! $sortSpalte === 'besitz_status' ? ($sortRichtung === 'asc' ? '▲' : '▼') : '↕' !!}
                            </a>
                        </th>
                        <th class="p-3 font-bold text-center">Aktionen</th>
                    </tr>
                </thead>
<tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
    @forelse($aktive as $p)
        <tr class="hover:bg-slate-50/50 transition-colors">
        <!-- 🏛️ 1. AMTLICHE GEMARKUNG MITSAMT REINEM GEMEINDENAMEN & GEMARKUNGSSCHLÜSSEL -->
        <td class="p-3 leading-tight">
            <span class="text-slate-900 font-bold text-xs block">{{ $p->gemarkung }}</span>
            <span class="text-[10px] font-mono text-slate-400 block mt-0.5">
                @php
                    // 🚀 VISUELLER FILTER: Schneidet den Gemeindeschlüssel (falls vorhanden) für den Bildschirm ab
                    $reinerGemeindeName = explode(' (', $p->gemeinde ?? 'Weinbaugemeinde')[0];
                @endphp
                {{ $reinerGemeindeName }} 
                @if(!empty($p->gemarkungsschuelser))
                    ({{ $p->gemarkungsschuelser }})
                @endif
            </span>
        </td>
        <!-- 🏛️ 2. FLURNUMMER -->
        <td class="p-3 font-mono font-bold text-slate-500">Flur {{ $p->flur }}</td>
        
        <!-- 🏛️ 3. FLURSTÜCK-NUMMER (Zähler / Nenner) -->
        <td class="p-3 font-mono font-bold text-blue-600 bg-blue-50/20 px-2 py-0.5 rounded-md inline-block mt-2">
            {{ $p->flurstueck_zaehler }}{{ $p->flurstueck_nenner ? '/' . $p->flurstueck_nenner : '' }}
        </td>
        
        <!-- 🏛️ 4. LAGEBEZEICHNUNG / AMTLICHER FLURNAME -->
        <td class="p-3 text-slate-500 text-xs italic">{{ $p->flurname_lage ?? 'Keine Angabe' }}</td>
        
        <!-- 🚜 5. OPERATIVE VERKNÜPFUNG: Sperrt die Nutzung, falls Version = 1 -->
        <td class="p-3 text-left">
            @if(intval($p->version) === 1)
                <!-- 🚀 CORE-FIX: Optische Warnung im Betriebsspiegel -->
                <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-800 border border-amber-200 px-2 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider animate-pulse" title="Dieses Flurstück muss einmalig revisioniert werden, bevor es genutzt werden kann!">
                    ⚠️ Erstprüfung ausstehend
                </span>
            @else
                @if(!empty($p->schlag_name))
                    <div class="space-y-1">
                        <a href="/schlaege/schlag-karte?fokus_anlage={{ $p->anlage_id }}" class="inline-flex items-center gap-1 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold border border-blue-200 px-2 py-0.5 rounded-lg text-[10px] uppercase tracking-wider no-underline transition shadow-3xs">
                            🌿 {{ $p->anlage_name }}
                        </a>
                        @if(!empty($p->schlag_name))
                            <span class="block text-[10px] text-slate-400 font-medium font-sans">🚜 Schlag: {{ $p->schlag_name }}</span>
                        @endif
                    </div>
                @else
                    <span class="text-slate-400 text-[10px] font-mono italic">Nicht verknüpft</span>
                @endif
            @endif
        </td>
        <!-- 🛰️ 6. KARTEN-LINK: Übergibt die eindeutige interne UUID an die Katasterkarte [INDEX: 3] -->
        <td class="p-3 text-center">
            <a href="/kataster/parzellen-karte?fokus_parzelle={{ $p->parzelle_uuid }}" class="inline-block p-1.5 border border-blue-200 rounded-lg bg-blue-50/30 hover:bg-blue-100/60 text-blue-600 transition shadow-3xs" title="Auf der Katasterkarte fokussieren">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2v1a2 2 0 002 2h2.1l.6 2.4c.1.4.5.6.9.6h1.5a1 1 0 001.8-.6l.3-1.2A9 9 0 103.055 11z" />
                </svg>
            </a>
        </td>
        <!-- 📐 7. AMTLICHE FLÄCHEN-BERECHNUNG -->
        <td class="p-3 text-right font-mono font-bold text-slate-900">
            {{ number_format($p->amtliche_flaeche_m2, 0, ',', '.') }} m²
            <span class="text-[10px] text-slate-400 block font-normal">({{ number_format($p->amtliche_flaeche_m2 / 10000, 4, ',', '.') }} ha)</span>
        </td>
        <!-- ⚖️ 8. BESITZVERHÄLTNIS (Eigentum vs. Pacht) -->
        <td class="p-3 text-center">
            @if($p->besitz_status === 'eigentum')
                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">● Eigentum</span>
            @elseif($p->besitz_status === 'gepachtet')
                <span class="bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">● Gepachtet</span>
            @else
                <span class="bg-slate-100 text-slate-600 border border-slate-300 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">○ Verpachtet</span>
            @endif
        </td>

        <!-- 🛠️ 9. AKTIONEN: Historisierte Bearbeitung & Ausbuchung -->
        <td class="p-3 text-center">
            <div class="flex items-center justify-center space-x-1.5">
                <button onclick="oeffneBearbeitenModal('{{ $p->parzelle_uuid }}', '{{ $p->besitz_status }}', '{{ $p->flurname_lage }}','{{ $p->version }}')" class="p-1.5 border border-slate-200 rounded-lg hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition cursor-pointer shadow-3xs" title="Bearbeiten & Historisieren">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
                <button onclick="oeffneAusbuchenModal('{{ $p->parzelle_uuid }}')" class="p-1.5 border border-red-200 rounded-lg hover:bg-red-50 text-red-500 hover:text-red-700 transition cursor-pointer shadow-3xs" title="Historisch ausbuchen / Löschen">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </td>
    </tr>
    @empty
    <!-- LEERZUSTAND DER FILTRATION -->
    <tr>
        <td colspan="9" class="p-12 text-center text-slate-400 font-medium">
            <div class="text-2xl mb-2">🔍</div> Keine aktiven Flurstücke für diese Suchanfrage lokalisiert.
        </td>
    </tr>
    @endforelse

</tbody>

            </table>
        </div>
    </div>
    <!-- 4. DIE HISTORISCHEN ARCHIV-KARTEN (REIN INFORMATIV AM FUSS) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 text-xs font-mono">
        <div class="bg-white p-3 rounded-xl border border-slate-200 text-slate-500">
            <span>📉 Ausgebuchte Flächen im Archiv: {{ count($geloeschte) + count($verkaufte) }}</span>
        </div>
    </div>
</div>

<!-- ==========================================================================
     🏛️ MODAL 1: RECHTSSICHERE BEARBEITUNG (HISTORISIERUNG)
     ========================================================================== -->
<div id="editParzelleModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 text-sm space-y-4 relative z-50">
        <div class="flex justify-between items-center border-b pb-2.5 border-slate-100">
            <h3 class="font-bold text-slate-900 uppercase font-mono tracking-wider text-xs">📝 Flurstück historisch anpassen</h3>
            <button onclick="schliesseBearbeitenModal()" class="text-slate-400 hover:text-slate-600 font-bold text-xl p-1 cursor-pointer">&times;</button>
        </div>
        <form onsubmit="sendeBearbeitung(event)" class="space-y-4">
            <input type="hidden" id="editUuid">
            <div class="space-y-1">
                <label class="text ?? [10px] uppercase font-mono tracking-wider font-bold text-slate-400">Pacht- & Besitzverhältnis:</label>
                <select id="editStatus" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 font-bold bg-slate-50/50">
                    <option value="eigentum">Eigentum</option>
                    <option value="gepachtet">Gepachtet</option>
                    <option value="verpachtet">Verpachtet</option>
                </select>
            </div>
            <div class="space-y-1">
                <label class="text-[10px] uppercase font-mono tracking-wider font-bold text-slate-400">Lagebezeichnung / Flurname:</label>
                <input type="text" id="editLage" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 font-medium bg-slate-50/50">
            </div>
            <div class="space-y-1">
                <label id="labelEditGrund" class="text-[10px] uppercase font-mono tracking-wider font-bold text-red-500">Änderungsgrund (RECHTSPFLICHT):</label>
                <input type="text" id="editGrund" required placeholder="z.B. Pachtvertrag verlängert" class="w-full border border-red-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-red-500 font-medium bg-red-50/30">
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="schliesseBearbeitenModal()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono font-bold text-xs py-2 px-4 rounded-xl transition cursor-pointer">Abbrechen</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-mono font-bold text-xs py-2 px-4 rounded-xl transition shadow-sm cursor-pointer">Neue Version sichern</button>
            </div>
        </form>
    </div>
</div>
<!-- ==========================================================================
     🏛️ MODAL 2: HISTORISCHE AUSBUCHUNG (LÖSCHUNG)
     ========================================================================== -->
<div id="deleteParzelleModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 text-sm space-y-4 relative z-50">
        <div class="flex justify-between items-center border-b pb-2.5 border-slate-100">
            <h3 class="font-bold text-red-600 uppercase font-mono tracking-wider text-xs">🗑️ Flurstück ausbuchen (Archivieren)</h3>
            <button onclick="schliesseAusbuchenModal()" class="text-slate-400 hover:text-slate-600 font-bold text-xl p-1 cursor-pointer">&times;</button>
        </div>
        <form onsubmit="sendeAusbuchtung(event)" class="space-y-4">
            <input type="hidden" id="deleteUuid">
            <p class="text-xs text-slate-500 leading-relaxed">
                Das Flurstück wird <strong>niemals physikalisch gelöscht</strong>. Es erhält ein historisches Zeitschloss, wird aus dem aktiven Betriebsspiegel entfernt und wandert ins Archiv.
            </p>
            <div class="space-y-1">
                <label class="text-[10px] uppercase font-mono tracking-wider font-bold text-red-500">Grund für den Flächenabgang:</label>
                <input type="text" id="deleteGrund" required placeholder="z.B. Pachtvertrag ausgelaufen / Fläche verkauf" class="w-full border border-red-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-red-500 font-medium bg-red-50/30">
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="schliesseAusbuchenModal()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono font-bold text-xs py-2 px-4 rounded-xl transition cursor-pointer">Abbrechen</button>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-mono font-bold text-xs py-2 px-4 rounded-xl transition shadow-sm cursor-pointer">Rechtssicher ausbuchen</button>
            </div>
        </form>
    </div>
</div>

<script>
    // ==========================================================================
    // 🛰️ REAKTIVE JAVASCRIPT-STEUERUNG FÜR DEN BETRIEBSSPIEGEL
    // ==========================================================================
    // 🚀 CORE-FIX: Erkennt beim Öffnen die Version und sperrt das Textfeld bei der Erstaufnahme automatisch!
    function oeffneBearbeitenModal(uuid, status, lage, version) {
        document.getElementById('editUuid').value = uuid;
        document.getElementById('editStatus').value = status;
        document.getElementById('editLage').value = (lage === 'null' || !lage) ? '' : lage;
        
        const grundInput = document.getElementById('editGrund');
        const grundLabel = document.getElementById('labelEditGrund');

        if (parseInt(version) === 1) {
            grundInput.value = 'Automatische Erstaufnahme-Zertifizierung';
            grundInput.disabled = true; // Winzer muss nichts tippen!
            grundInput.className = "w-full border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono bg-slate-150 text-slate-400 cursor-not-allowed shadow-none";
            grundLabel.innerText = "🔒 Änderungsgrund (System-Eintrag läuft)";
            grundLabel.className = "text-[10px] uppercase font-mono tracking-wider font-bold text-slate-400";
        } else {
            grundInput.value = '';
            grundInput.disabled = false;
            grundInput.className = "w-full border border-red-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-red-500 font-medium bg-red-50/30";
            grundLabel.innerText = "⚠️ Änderungsgrund (RECHTSPFLICHT)";
            grundLabel.className = "text-[10px] uppercase font-mono tracking-wider font-bold text-red-500";
        }
        document.getElementById('editParzelleModal').classList.remove('hidden');
    }

    function schliesseBearbeitenModal() { document.getElementById('editParzelleModal').classList.add('hidden'); }

    function oeffneAusbuchenModal(uuid) {
        document.getElementById('deleteUuid').value = uuid;
        document.getElementById('deleteGrund').value = '';
        document.getElementById('deleteParzelleModal').classList.remove('hidden');
    }
    function schliesseAusbuchenModal() { document.getElementById('deleteParzelleModal').classList.add('hidden'); }


    /**
     * Setzt das Zeitschloss und lagert die Parzelle ins Archiv aus!
     */
    async function sendeAusbuchtung(e) {
        e.preventDefault();
        const uuid = document.getElementById('deleteUuid').value;
        const payload = { aenderungsgrund: document.getElementById('deleteGrund').value };

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        try {
            const r = await fetch(`/api/kataster/parzellen/ausbuchen/${uuid}`, {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify(payload)
            });
            if (r.ok) { window.location.reload(); } else { alert("🚨 Ausbuchungs-Sperre im Kernel."); }
        } catch (err) { console.error(err); }
    }
        // ==========================================================================
    // 🛰️ REAKTIVE INTERFACE-MATRIX: LIVE-SUCHE MIT DEBOUNCE-SCHUTZ
    // ==========================================================================
    let liveSucheTimer = null;

    function ZündeLiveSuche() {
        // Löscht den alten Timer bei jedem neuen Tastendruck sofort
        clearTimeout(liveSucheTimer);

        // Wartet exakt 300 Millisekunden ab, ob der Winzer weitertippt
        liveSucheTimer = setTimeout(async () => {
            const suchWert = document.getElementById('liveSucheInput').value.trim();
            
            // Greift die aktuellen Sortierparameter sicher ab, ohne die URL umzubiegen
            const urlParams = new URLSearchParams(window.location.search);
            const sort = urlParams.get('sort') || 'gemarkung';
            const direction = urlParams.get('direction') || 'asc';

            try {
                // 📡 ASYNCHRONER TUNNEL: Schießt den Request im Hintergrund an den Controller
                const url = `/parzellen/uebersicht?suche=${encodeURIComponent(suchWert)}&sort=${sort}&direction=${direction}`;
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        // 🔥 REPARATUR-RIEGEL: Zwingend erforderlich für Laravels $request->ajax() Wächter!
                        'X-Requested-With': 'XMLHttpRequest' 
                    }
                });
                
                const res = await response.json();
                
                // Tauscht reaktiv ausschließlich den HTML-Tabellenkörper aus!
                if (res.success) {
                    const tbody = document.querySelector('table tbody');
                    if (tbody) {
                        tbody.innerHTML = res.table_html;
                    }
                    
                    // Aktualisiert die Trefferzahl live oben rechts in der Bar
                    const zaehler = document.getElementById('trefferZaehlerPille');
                    if (zaehler) {
                        zaehler.innerText = `Treffer: ${res.anzahl} Parzellen`;
                    }
                }
            } catch (e) {
                console.error("vinicore AJAX-Fehler: Filter-Verbindung unterbrochen.", e);
            }
        }, 300); 
    }
    /**
     * 🛰️ RECHTSPFEILER: REVISION & ERSTPRÜFUNG SPEICHERN (UNIVERSAL-KERN)
     * 🚀 ARCHITEKTUR-FIX: Synchronisiert Karte und Übersicht auf dieselbe krisenfeste API-Mündung!
     */
    async function sendeBearbeitung(event) {
        if (event) { event.preventDefault(); }
        
        const uuid = document.getElementById('editUuid')?.value || '';
        const status = document.getElementById('editStatus')?.value || 'eigentum';
        const flurname = document.getElementById('editLage')?.value || '';
        const grund = document.getElementById('editGrund')?.value || '';

        if (!uuid) {
            zeigeVinicoreToast('error', 'Fehler: Keine Parzellen-ID lokalisiert.');
            return;
        }

        const spinner = document.getElementById('vinicoreMapSpinner');
        if (spinner) spinner.classList.remove('hidden');

        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // 🎯 HARMONISIERUNG: Zielt direkt auf deine bereinigte Controller-Schnittstelle
            const response = await fetch(`/api/kataster/parzellen/aktualisieren/${uuid}`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json', 
                    'X-CSRF-TOKEN': token 
                },
                body: JSON.stringify({
                    uuid: uuid,
                    besitz_status: status,
                    flurname_lage: flurname,
                    aenderungsgrund: grund
                })
            });

            const data = await response.json();

            if (data.success || response.ok) {
                // Schließt das Bearbeiten-Modal lautlos
                schliesseBearbeitenModal();
                
                // Feuert das reaktive vinicore System-Toast ab
                zeigeVinicoreToast('success', 'Erstprüfung erfolgreich rechtssicher versiegelt!');
                
                // Aktualisiert die Karte und den Inspektor direkt im RAM
                await ladeGeoJsonKataster(true);
                rendereSammlerInspektor(uuid);
            } else {
                zeigeVinicoreToast('error', data.message || 'Laufzeit-Sperre im Datenbank-Kernel.');
            }
        } catch (err) {
            console.error("Absturz beim Senden der Erstprüfung:", err);
            zeigeVinicoreToast('error', 'Kritischer Fehler in der Netzwerk-Leitung.');
        } finally {
            if (spinner) spinner.classList.add('hidden');
        }
    }


    /**
     * 🛰️ SAMMELKORB-SPEICHERUNG: Synchronisiert das Frontend mit deiner echten Controller-Route!
     */
    async function ZündeSammelSpeicherung() {
        if (gewaehlteFeaturesSammelkorb.length === 0) return;
        const spinner = document.getElementById('vinicoreMapSpinner');
        if (spinner) spinner.className = spinner.className.replace('hidden', '').trim();

        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch('/api/kataster/parzellen/speichern-sammelkorb', { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token }, 
                body: JSON.stringify({ parzellen: gewaehlteFeaturesSammelkorb }) 
            });
            
            const res = await response.json();
            // 🚀 COUPLING-FIX: Akzeptiert den Erfolg und bereinigt den Korb im selben Moment!
            if (res.success || response.status === 200) {
                if (typeof zeigeVinicoreToast === 'function') {
                    zeigeVinicoreToast('success', 'Erfolg: Parzellen historiensicher importiert!');
                } else {
                    alert('Erfolg: Parzellen historiensicher importiert!');
                }
                // Löscht den Korb physikalisch im RAM
                gewaehlteFeaturesSammelkorb = []; 
                
                await ladeGeoJsonKataster(true); 
                if (umgebungsWfsLayer) {
                    try { umgebungsWfsLayer.clearLayers(); } catch(err) { map.removeLayer(umgebungsWfsLayer); }
                }
                rendereSammlerInspektor();
                await ladeUmliegendeWfsParzellen();
            } else {
                alert(res.message || 'Import-Sperre im Kernel.');
            }
        } catch (e) { 
            console.error(e); 
            alert('Schnittstellen-Verbindung unterbrochen.');
        } finally {
            if (spinner) spinner.classList.add('hidden');
        }
    }
    /**
     * 🛰️ RECHTSPFEILER ÜBERSICHT: REVISION & ERSTPRÜFUNG SPEICHERN
     * 🚀 CORE-FIX: Schließt die fehlende JS-Lücke in der Flurstücksliste!
     */
    async function sendeBearbeitung(event) {
        if (event) { event.preventDefault(); }
        
        const uuid = document.getElementById('editUuid')?.value || '';
        const status = document.getElementById('editStatus')?.value || 'eigentum';
        const flurname = document.getElementById('editLage')?.value || '';
        const grund = document.getElementById('editGrund')?.value || '';

        if (!uuid) {
            alert('Fehler: Keine Parzellen-ID lokalisiert.');
            return;
        }

        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // 🎯 ROUTEN-SYNCHRONISATION: Feuert direkt auf deine Controller-Methode
            const response = await fetch(`/api/kataster/parzellen/aktualisieren/${uuid}`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json', 
                    'X-CSRF-TOKEN': token 
                },
                body: JSON.stringify({
                    uuid: uuid,
                    besitz_status: status,
                    flurname_lage: flurname,
                    aenderungsgrund: grund
                })
            });

            const data = await response.json();

            if (data.success || response.ok) {
                // Schließt das Bearbeiten-Modal der Übersicht
                schliesseBearbeitenModal();
                
                // Aktualisiert die Seite, damit die Erstprüfungs-Warnung reaktiv verschwindet
                window.location.reload();
            } else {
                alert(data.message || 'Laufzeit-Sperre im Datenbank-Kernel.');
            }
        } catch (err) {
            console.error("Absturz beim Senden der Erstprüfung:", err);
            alert('Kritischer Fehler in der Netzwerk-Leitung.');
        }
    }


</script>
    <!-- 🚀 Modulare Einbindung des globalen ERP-Inspektors -->
    @include('layouts.inspektor_panel')

@endsection
