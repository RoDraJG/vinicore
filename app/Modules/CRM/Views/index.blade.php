@extends('layouts.app')

@section('content')
<!-- 🚀 UNZERSTÖRBAR: flex-1 und h-full füllen das übergeordnete main-Gehäuse im Vollbild zu 100% aus -->
<div class="flex-1 w-full flex flex-col min-w-0 bg-bg-surface border border-border-main rounded-2xl shadow-3xs overflow-hidden h-full">
    
    <!-- Modul-Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 p-4 bg-bg-surface border-b border-border-main flex-shrink-0">
        <div>
            <h1 class="text-sm font-mono font-bold tracking-wider text-text-main uppercase flex items-center gap-2">
                <span>B2B Enterprise</span> Zentrales Partner-Register
            </h1>
            <p class="text-[11px] text-text-muted mt-0.5">Kaufmännische Verwaltung, DATEV-Schnittstellen und Adresslogistik aller Kontakte.</p>
        </div>
        
        <!-- Rechte Flanke: Dezente Suche & Button nebeneinander -->
        <div class="flex items-center gap-2 w-full lg:w-auto">
            <form action="{{ route('crm.index') }}" method="GET" class="relative w-full sm:w-60 m-0">
                <input type="hidden" name="typ" value="{{ $typ }}">
                <button type="submit" class="absolute inset-y-0 left-0 flex items-center pl-3 bg-transparent border-0 text-text-muted text-xs cursor-pointer hover:text-text-main transition-colors">🔍</button>
                <input type="text" 
                       name="suche" 
                       value="{{ $search }}"
                       placeholder="Partner suchen..." 
                       class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main pl-8 pr-8 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">
                @if(!empty($search))
                    <a href="{{ route('crm.index', ['typ' => $typ]) }}" class="absolute inset-y-0 right-0 flex items-center pr-3 text-text-muted hover:text-red-500 font-mono text-[10px] no-underline">✕</a>
                @endif
            </form>

            <!-- Rechte Flanke: Suche, Inspektor-Schalter & Erfassen-Button -->
            <a href="{{ route('crm.create') }}" 
               class="bg-slate-900 hover:bg-slate-800 text-white font-mono text-xs font-semibold px-4 py-2 rounded-xl transition shadow-3xs cursor-pointer border-0 text-center whitespace-nowrap no-underline">
                ➕ Erfassen
            </a>

            <!-- 🎯 REPARATUR 1: Globaler Inspektor-Toggle für das Cockpit-Feature -->
            <button type="button" 
                    onclick="ToggleZentraleInspektor()" 
                    id="inspektorSystemBtn"
                    class="bg-bg-input hover:bg-border-main text-text-main font-mono text-xs font-semibold px-4 py-2 rounded-xl transition shadow-3xs cursor-pointer border border-border-main text-center whitespace-nowrap">
                📋 Assistent Off
            </button>

        </div>
    </div>

    <!-- Registerkarten -->
    <div class="p-3 bg-bg-surface border-b border-border-main flex-shrink-0">
        <div class="flex bg-bg-input p-1 rounded-xl w-fit min-w-[320px] shadow-3xs">
            <a href="{{ route('crm.index', ['typ' => 'kunde', 'suche' => $search]) }}" 
               class="flex-1 text-center font-mono text-[11px] rounded-lg px-4 py-1.5 transition-all border-0 no-underline {{ $typ === 'kunde' ? 'bg-bg-surface text-emerald-600 font-bold shadow-3xs' : 'text-text-muted hover:text-text-main' }}">
                🍷 Kunden
            </a>
            <a href="{{ route('crm.index', ['typ' => 'lieferant', 'suche' => $search]) }}" 
               class="flex-1 text-center font-mono text-[11px] rounded-lg px-4 py-1.5 transition-all border-0 no-underline {{ $typ === 'lieferant' ? 'bg-bg-surface text-text-main font-bold shadow-3xs' : 'text-text-muted hover:text-text-main' }}">
                📦 Lieferanten
            </a>
            <a href="{{ route('crm.index', ['typ' => 'alle', 'suche' => $search]) }}" 
               class="flex-1 text-center font-mono text-[11px] rounded-lg px-4 py-1.5 transition-all border-0 no-underline {{ $typ === 'alle' ? 'bg-bg-surface text-blue-600 font-bold shadow-3xs' : 'text-text-muted hover:text-text-main' }}">
                👥 Alle
            </a>
        </div>
    </div>
    <!-- 🗂️ Datentabelle (min-h-[500px] zwingt den Browser, die Auto-Höhe im Vollbild zu brechen!) -->
    <div class="flex-1 w-full overflow-y-auto bg-bg-base/30">
        
        <!-- 🖥️ DESKTOP-ANSICHT: min-h erzwingt den Platz auf großen Monitoren -->
        <div class="hidden md:block overflow-x-auto w-full min-h-[500px] lg:min-h-[600px]">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead class="bg-bg-base border-b border-border-main text-[10px] font-mono text-text-muted uppercase tracking-wider sticky top-0 z-10">
                    <tr>
                        <th class="pl-4 py-3 font-semibold">Kürzel / DATEV</th>
                        <th class="py-3 font-semibold">Partner / Unternehmen</th>
                        <th class="py-3 font-semibold">Anschrift & Logistik</th>
                        <th class="py-3 font-semibold">Konditionen</th>
                        <th class="py-3 font-semibold">Präferenz / Kontakt</th>
                        <th class="pr-4 py-3 text-right">Aktion</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-border-main/50 bg-bg-surface">
                        <!-- 🎯 REPARATUR 2: Vollkommen absturzsicher ohne Backslash-Kollisionen im Blade-Compiler -->
                        @forelse($kontakte as $k)
                            <tr class="hover:bg-bg-base/40 transition-colors cursor-crosshair"
                                onmouseenter="FüttereInspektorAufHover({
                                    nachname: '{{ addslashes($k->nachname) }}',
                                    vorname: '{{ addslashes($k->vorname ?? '') }}',
                                    firma: '{{ addslashes($k->firma ?? '-') }}',
                                    rolle: '{{ $k->ist_kunde ? '🍷 Weinkunde' : '📦 Lieferant' }}',
                                    datev: '{{ $k->debitorennummer ?? $k->kreditorennummer ?? 'Keine DATEV-Koppelung' }}',
                                    adresse: '{{ addslashes($k->strasse_nr ?? '') }}, {{ $k->plz }} {{ addslashes($k->ort ?? '') }}',
                                    stil: '{{ $k->bevorzugte_weinstilistik ? ucfirst($k->bevorzugte_weinstilistik) : 'Keine Angabe' }}',
                                    news: '{{ $k->newsletter_erlaubt ? '✉️ Abonniert' : '❌ Blockiert' }}',
                                    ziel: '{{ $k->standard_zahlungsziel_tage }} Tage Ziel'
                                })">


                            <td class="pl-4 py-3 font-mono">
                                @if($k->ist_kunde)
                                    <div class="font-bold text-emerald-600">K-{{ $k->kundennummer }}</div>
                                    @if($k->debitorennummer)<div class="text-[9px] text-text-muted">D: {{ $k->debitorennummer }}</div>@endif
                                @endif
                                @if($k->ist_lieferant)
                                    <div class="font-bold text-text-muted mt-0.5">L-{{ $k->lieferantennummer }}</div>
                                    @if($k->kreditorennummer)<div class="text-[9px] text-text-muted">K: {{ $k->kreditorennummer }}</div>@endif
                                @endif
                            </td>
                            <td class="py-3">
                                <div class="font-semibold text-text-main">{{ $k->nachname }}{{ $k->vorname ? ', ' . $k->vorname : '' }}</div>
                                @if($k->firma) <div class="text-[10px] text-text-muted mt-0.5 fw-medium">{{ $k->firma }}</div> @endif
                                @if($k->geburtsdatum) <div class="text-[9px] text-slate-400 font-mono mt-0.5">🎂 {{ \Carbon\Carbon::parse($k->geburtsdatum)->format('d.m.Y') }}</div> @endif
                            </td>
                            <td class="py-3">
                                <div class="text-text-main">{{ $k->strasse_nr }}{{ $k->adresszusatz ? ' (' . $k->adresszusatz . ')' : '' }}</div>
                                <div class="text-[10px] text-text-muted font-medium">{{ $k->plz }} {{ $k->ort }}</div>
                                @if($k->liefer_strasse_nr) <div class="text-[9px] text-accent-brand font-medium mt-1">📦 Lief-Adresse active</div> @endif
                            </td>
                            <td class="py-3 font-mono text-[11px]">
                                <div class="text-text-main">{{ $k->standard_zahlungsziel_tage }} Tage Ziel</div>
                                @if($k->skonto_prozent > 0) <div class="text-purple-600 text-[10px]">{{ $k->skonto_prozent }}% / {{ $k->skonto_tage }} Tage</div> @endif
                                @if($k->individueller_rabatt_prozent > 0) <div class="text-emerald-600 text-[10px] font-bold">-% {{ $k->individueller_rabatt_prozent }}%</div> @endif
                            </td>
                            <td class="py-3">
                                <div class="text-text-main">{{ $k->email ?? '-' }}</div>
                                @if($k->bevorzugte_weinstilistik) <span class="inline-flex items-center rounded-md bg-purple-50 text-purple-700 border border-purple-100 text-[9px] px-1.5 py-0.5 mt-1 font-medium font-mono">🍇 {{ ucfirst($k->bevorzugte_weinstilistik) }}</span> @endif
                                @if($k->newsletter_erlaubt) <span class="inline-flex items-center rounded-md bg-emerald-50 text-emerald-700 border border-emerald-100 text-[9px] px-1.5 py-0.5 mt-1 font-medium font-mono ml-1">✉️ News</span> @endif
                            </td>
                            <td class="pr-4 py-3 text-right">
                                <!-- 🎯 Echte Routenverknüpfung für die Details -->
                                <a href="{{ route('crm.show', $k->id) }}" class="inline-flex items-center bg-bg-input hover:bg-border-main text-text-main px-2.5 py-1 rounded-lg border border-border-main text-[11px] font-mono no-underline transition-colors shadow-3xs">🔍 Details</a>

                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-8 font-mono text-xs text-text-muted bg-bg-base/10">⚠️ Keine Treffer.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        <!-- 📱 MOBILE LISTENANSICHT (Wird ab md:hidden auf Desktop-Bildschirmen unsichtbar) -->
        <div class="block md:hidden divide-y divide-border-main/50 w-full overflow-hidden bg-bg-surface rounded-2xl border border-border-main shadow-3xs">
            @forelse($kontakte as $k)
                <div class="p-4 flex flex-col gap-2 bg-bg-surface hover:bg-bg-base/20 transition-colors w-full">
                    <div class="flex items-center justify-between w-full">
                        <span class="font-mono text-xs font-bold">
                            @if($k->ist_kunde) <span class="text-emerald-600">K-{{ $k->kundennummer }}</span> @else <span class="text-text-muted">L-{{ $k->lieferantennummer }}</span> @endif
                        </span>
                        <div class="flex gap-1">
                            @if($k->ist_kunde) <span class="text-[9px] bg-emerald-50 text-emerald-700 border border-emerald-100 px-1.5 py-0.5 rounded">🍷 Kunde</span> @endif
                            @if($k->ist_lieferant) <span class="text-[9px] bg-slate-100 text-slate-700 border border-slate-200 px-1.5 py-0.5 rounded">📦 Lief</span> @endif
                        </div>
                    </div>
                    <div class="w-full truncate">
                        <div class="text-xs font-bold text-text-main truncate">{{ $k->nachname }}{{ $k->vorname ? ', ' . $k->vorname : '' }}</div>
                        @if($k->firma) <div class="text-[10px] text-text-muted mt-0.5 truncate">{{ $k->firma }}</div> @endif
                    </div>
                    <div class="mt-2 pt-2 border-t border-border-main/40 flex justify-end w-full">
                        <!-- 🎯 Echte Routenverknüpfung für die Details -->
                        <a href="{{ route('crm.show', $k->id) }}" class="inline-flex items-center bg-bg-input hover:bg-border-main text-text-main px-2.5 py-1 rounded-lg border border-border-main text-[11px] font-mono no-underline transition-colors shadow-3xs">🔍 Details</a>

                    </div>
                </div>
            @empty
                <div class="text-center py-8 font-mono text-xs text-text-muted bg-bg-base/10 w-full">⚠️ Keine Treffer.</div>
            @endforelse
        </div>
        <div class="p-2"></div>
    </div>
    <!-- 📊 DER VINICORE SEITENSELEKTOR -->
    @if($kontakte->hasPages())
        <div class="bg-bg-base border-t border-border-main px-4 py-3 flex items-center justify-between flex-shrink-0 z-10 w-full">
            <div class="flex flex-1 justify-between sm:hidden w-full">
                @if($kontakte->onFirstPage())
                    <span class="inline-flex items-center justify-center bg-bg-input text-text-muted/60 font-mono text-xs px-3 py-1.5 rounded-xl border border-border-main cursor-not-allowed">← Zurück</span>
                @else
                    <a href="{{ $kontakte->previousPageUrl() }}" class="inline-flex items-center justify-center bg-bg-surface hover:bg-bg-input text-text-main font-mono text-xs px-3 py-1.5 rounded-xl border border-border-main transition-colors no-underline shadow-3xs">← Zurück</a>
                @endif

                @if($kontakte->hasMorePages())
                    <a href="{{ $kontakte->nextPageUrl() }}" class="inline-flex items-center justify-center bg-bg-surface hover:bg-bg-input text-text-main font-mono text-xs px-3 py-1.5 rounded-xl border border-border-main transition-colors no-underline shadow-3xs">Weiter →</a>
                @else
                    <span class="inline-flex items-center justify-center bg-bg-input text-text-muted/60 font-mono text-xs px-3 py-1.5 rounded-xl border border-border-main cursor-not-allowed">Weiter →</span>
                @endif
            </div>

            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between w-full">
                <div>
                    <p class="text-[11px] font-mono text-text-muted m-0">
                        Zeige <span class="font-bold text-text-main">{{ $kontakte->firstItem() ?? 0 }}</span> bis <span class="font-bold text-text-main">{{ $kontakte->lastItem() ?? 0 }}</span> von <span class="font-bold text-text-main">{{ $kontakte->total() }}</span> Partnern
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-xl bg-bg-input p-0.5" aria-label="Pagination">
                        @if($kontakte->onFirstPage())
                            <span class="relative inline-flex items-center px-2 py-1 text-text-muted/40 cursor-not-allowed text-xs">◀</span>
                        @else
                            <a href="{{ $kontakte->previousPageUrl() }}" class="relative inline-flex items-center px-2 py-1 text-text-muted hover:text-text-main text-xs no-underline">◀</a>
                        @endif

                        @foreach($kontakte->getUrlRange(1, $kontakte->lastPage()) as $page => $url)
                            <a href="{{ $url }}" class="relative inline-flex items-center px-2.5 py-1 font-mono text-xs rounded-lg border-0 no-underline transition-all {{ $page == $kontakte->currentPage() ? 'bg-bg-surface text-accent-brand font-bold shadow-3xs' : 'text-text-muted hover:text-text-main' }}">
                                {{ $page }}
                            </a>
                        @endforeach

                        @if($kontakte->hasMorePages())
                            <a href="{{ $kontakte->nextPageUrl() }}" class="relative inline-flex items-center px-2 py-1 text-text-muted hover:text-text-main text-xs no-underline">▶</a>
                        @else
                            <span class="relative inline-flex items-center px-2 py-1 text-text-muted/40 cursor-not-allowed text-xs">▶</span>
                        @endif
                    </nav>
                </div>
            </div>
        </div>
    @endif

</div>
<!-- 🧠 DASHBOARD-COCKPIT: Intelligente Mausover- & Sichtbarkeitssteuerung -->
<script>
    // Globaler Status, ob der Inspektor aktiv vom Winzer geöffnet wurde
    let inspektorAktiviert = false;

    /**
     * Schaltet den System-Inspektor reaktiv ein oder aus
     */
    function ToggleZentraleInspektor() {
        const btn = document.getElementById('inspektorSystemBtn');
        
        if (!inspektorAktiviert) {
            inspektorAktiviert = true;
            if (btn) {
                btn.innerHTML = '📋 Assistent On';
                btn.classList.remove('bg-bg-input');
                btn.classList.add('bg-emerald-600', 'text-white', 'border-emerald-600');
            }
            
            // 🎯 REPARATUR 2: Umlautfreier Aufruf
            window.OeffneGlobalenInspektor(
                '📋 CRM Live-Cockpit', 
                `<div class="space-y-3 font-sans text-xs text-text-main">
                    <div class="p-3 bg-emerald-50 text-emerald-800 border border-emerald-100 rounded-xl font-mono text-[10px] font-bold uppercase tracking-wider">
                        ⚡ Modus: Schwebe-Modus aktiv
                    </div>
                    <p class="text-text-muted m-0 leading-relaxed">Bewege deine Maus jetzt über die Tabellenzeilen, um die kaufmännischen Daten und Weinstilistiken des jeweiligen Partners live zu spiegeln.</p>
                </div>`,
                'w-80'
            );
        } else {
            inspektorAktiviert = false;
            if (btn) {
                btn.innerHTML = '📋 Assistent Off';
                btn.classList.remove('bg-emerald-600', 'text-white', 'border-emerald-600');
                btn.classList.add('bg-bg-input');
            }
            // 🎯 REPARATUR 3: Umlautfreier Aufruf
            window.SchliesseGlobalenInspektor();
        }
    }


       /**
     * Füttere den Inspektor im Vorbeifliegen, aber NUR wenn er geöffnet ist
     */
    function FüttereInspektorAufHover(data) {
        // Abbruch, falls der Winzer den Assistenten nicht aktiv ausgefahren hat
        if (!inspektorAktiviert) return;

        // Namen reaktiv und sauber zusammensetzen
        const vollständigerName = data.vorname ? `${data.nachname}, ${data.vorname}` : data.nachname;

        const htmlInhalt = `
            <div class="space-y-4 font-sans text-xs text-text-main animate-fade-in">
                
                <!-- Haupt-Identität -->
                <div class="p-3 bg-bg-input rounded-xl border border-border-main shadow-3xs">
                    <div class="text-[9px] font-mono font-bold text-text-muted uppercase tracking-wider">${data.rolle}</div>
                    <div class="text-xs font-bold text-text-main mt-0.5">${vollständigerName}</div>
                    ${data.firma !== '-' ? `<div class="text-[10px] text-accent-brand font-medium mt-0.5">🏢 ${data.firma}</div>` : ''}
                </div>

                <!-- Kaufmännische Daten & DATEV -->
                <div class="space-y-1.5">
                    <div class="text-[9px] font-mono font-bold text-text-muted uppercase tracking-wider px-1">📊 Buchhaltung & DATEV</div>
                    <div class="bg-bg-surface border border-border-main rounded-xl p-2.5 space-y-1 font-mono text-[11px]">
                        <div class="flex justify-between"><span class="text-text-muted">Konto / Nummer:</span><span class="font-bold text-text-main">${data.datev}</span></div>
                        <div class="flex justify-between"><span class="text-text-muted">Kondition:</span><span class="text-purple-600 font-bold">${data.ziel}</span></div>
                    </div>
                </div>

                <!-- Weinbau-Marketing -->
                <div class="space-y-1.5">
                    <div class="text-[9px] font-mono font-bold text-text-muted uppercase tracking-wider px-1">🍇 Weinbau-Präferenz</div>
                    <div class="bg-bg-surface border border-border-main rounded-xl p-2.5 space-y-1">
                        <div class="flex justify-between text-[11px]"><span class="text-text-muted">Stilistik:</span><span class="font-bold text-purple-700">${data.stil}</span></div>
                        <div class="flex justify-between text-[11px]"><span class="text-text-muted">Newsletter:</span><span class="font-medium">${data.news}</span></div>
                    </div>
                </div>

                <!-- Logistik-Zusammenfassung -->
                <div class="space-y-1.5">
                    <div class="text-[9px] font-mono font-bold text-text-muted uppercase tracking-wider px-1">📦 Anschrift</div>
                    <div class="bg-bg-surface border border-border-main rounded-xl p-2.5 text-[10px] text-text-muted leading-relaxed">
                        ${data.adresse}
                    </div>
                </div>

            </div>
        `;

        const bodyElement = document.getElementById('globalInspektorBody');
        const titelElement = document.getElementById('globalInspektorTitel');
        
        if (bodyElement) bodyElement.innerHTML = htmlInhalt;
        if (titelElement) titelElement.innerHTML = '📋 Live-Partner-Check';
    }

</script>
<style>
    @keyframes fadeIn { from { opacity: 0; transform: translateY(2px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fadeIn 0.15s ease-out forwards; }
</style>

@endsection
