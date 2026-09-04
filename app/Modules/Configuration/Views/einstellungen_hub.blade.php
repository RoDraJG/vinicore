@extends('layouts.app')

@section('content')
<div class="h-full w-full flex flex-col min-w-0 bg-bg-base overflow-hidden">
    
    <!-- Modul-Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-bg-surface border-b border-border-main flex-shrink-0">
        <div>
            <a href="{{ route('crm.index') }}" class="text-[10px] font-mono font-bold tracking-wider text-text-muted hover:text-accent-brand no-underline transition-colors uppercase">
                ← Zurück zum Zentralregister
            </a>
            <h1 class="text-sm font-mono font-bold tracking-wider text-text-main uppercase mt-1 mb-0">
                ⚙️ Zentrales ERP-Konfigurationszentrum
            </h1>
        </div>
    </div>

    <!-- Hauptgitter: Nimmt die volle linke Flanke ein, genau wie das normale Hauptmenü -->
    <div class="flex-1 flex overflow-hidden w-full">
        
        <!-- 🎨 VEREDELTE SETTINGS-SIDEBAR: Ersetzt die normale ERP-Sidebar nahtlos im Einstellungs-Modus -->
        <div class="w-64 bg-bg-surface border-r border-border-main p-4 flex flex-col gap-1 flex-shrink-0 overflow-y-auto h-full shadow-2xs">
            <div class="text-[10px] font-mono font-bold text-text-muted uppercase tracking-wider px-2 mb-3">
                ⚙️ Admin-Zentrale
            </div>
            
            @can('betrieb verwalten')
                <a href="{{ route('admin.einstellungen', ['tab' => 'betrieb']) }}" class="flex items-center gap-2.5 px-3 py-2.5 text-xs rounded-xl no-underline font-medium transition-all {{ $aktivesTab === 'betrieb' ? 'bg-slate-950 text-white font-semibold shadow-xs' : 'text-text-muted hover:text-text-main hover:bg-bg-input/60' }}">
                    <span>🏛️</span> Betriebsdefinitionen
                </a>
            @endcan

            @can('nummernkreise bearbeiten')
                <a href="{{ route('admin.einstellungen', ['tab' => 'nummernkreise']) }}" class="flex items-center gap-2.5 px-3 py-2.5 text-xs rounded-xl no-underline font-medium transition-all {{ $aktivesTab === 'nummernkreise' ? 'bg-slate-950 text-white font-semibold shadow-xs' : 'text-text-muted hover:text-text-main hover:bg-bg-input/60' }}">
                    <span>🔢</span> ERP-Nummernkreise
                </a>
            @endcan

            @can('dropdowns verwalten')
                <a href="{{ route('admin.einstellungen', ['tab' => 'dropdowns']) }}" class="flex items-center gap-2.5 px-3 py-2.5 text-xs rounded-xl no-underline font-medium transition-all {{ $aktivesTab === 'dropdowns' ? 'bg-slate-950 text-white font-semibold shadow-xs' : 'text-text-muted hover:text-text-main hover:bg-bg-input/60' }}">
                    <span>📦</span> Dropdown-Listen
                </a>
            @endcan
            
            <!-- 🔄 SCHNELLE RÜCKKEHR: Trennlinie und Home-Button am Fuß der Sidebar -->
            <div class="mt-auto pt-4 border-t border-border-main/50">
                <a href="{{ route('crm.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 text-xs rounded-xl no-underline font-medium text-text-muted hover:text-accent-brand hover:bg-bg-input/40 transition-all font-mono uppercase tracking-wider text-[10px]">
                    <span>←</span> Zum Register
                </a>
            </div>
        </div>

        <!-- RECHTER INHALT: Bleibt vollkommen unberührt -->
        <div class="flex-1 overflow-y-auto p-4 md:p-6 min-h-0 bg-bg-base/30 relative">

            <!-- 🏛️ TAB 1: BETRIEBSDEFINITIONEN & GEODATEN-FARBEN -->
            @if($aktivesTab === 'betrieb')
                <!-- 🎯 REPARATUR: Zielt nun punktgenau auf die neue, geschützte Admin-Route -->
                <form action="{{ route('admin.betrieb.speichern') }}" method="POST" class="max-w-2xl space-y-6">
                    @csrf            
                    <div class="bg-bg-surface border border-border-main rounded-2xl shadow-3xs p-5 space-y-6">
                        <div>
                            <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-accent-brand m-0">🏛️ Betriebsdefinitionen &amp; GIS-Kartenparameter</h3>
                            <p class="text-[11px] text-text-muted mt-0.5">Definiere hier die visuellen Parameter deines ERP-Systems. Die Farbcodes werden in Echtzeit für alle Endgeräte und die Katasterkarte übernommen.</p>
                        </div>

                        <div class="space-y-4">
                            <!-- 🟢 1. Farbe Eigentum -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 bg-bg-base/20 border border-border-main/60 rounded-xl">
                                <div class="flex-1">
                                    <label for="farbe_eigentum" class="block text-xs text-text-main font-bold mb-0.5">Eigentumsflächen:</label>
                                    <span class="text-[10px] text-text-muted font-mono">Standard für Kern-Zonen (Eigenbesitz)</span>
                                </div>
                                <div class="w-32 flex-shrink-0">
                                    <input type="color" id="farbe_eigentum" name="farbe_eigentum" value="{{ $einstellungen['farbe_eigentum'] ?? '#059669' }}" class="w-full h-8 bg-bg-input rounded-lg border border-border-main p-0.5 cursor-pointer" title="Farbe für Eigentum wählen">
                                </div>
                            </div>

                            <!-- 🔵 2. Farbe Pacht -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 bg-bg-base/20 border border-border-main/60 rounded-xl">
                                <div class="flex-1">
                                    <label for="farbe_gepachtet" class="block text-xs text-text-main font-bold mb-0.5">Pachtflächen:</label>
                                    <span class="text-[10px] text-text-muted font-mono">Für zeitlich befristete Verträge</span>
                                </div>
                                <div class="w-32 flex-shrink-0">
                                    <input type="color" id="farbe_gepachtet" name="farbe_gepachtet" value="{{ $einstellungen['farbe_gepachtet'] ?? '#2563eb' }}" class="w-full h-8 bg-bg-input rounded-lg border border-border-main p-0.5 cursor-pointer" title="Farbe für Pacht wählen">
                                </div>
                            </div>

                            <!-- 🚜 3. Farbe Verpachtet -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 bg-bg-base/20 border border-border-main/60 rounded-xl">
                                <div class="flex-1">
                                    <label for="farbe_verpachtet" class="block text-xs text-text-main font-bold mb-0.5">Verpachtete Eigenflächen:</label>
                                    <span class="text-[10px] text-text-muted font-mono">Aktuell von Fremdbetrieben bewirtschaftet</span>
                                </div>
                                <div class="w-32 flex-shrink-0">
                                    <input type="color" id="farbe_verpachtet" name="farbe_verpachtet" value="{{ $einstellungen['farbe_verpachtet'] ?? '#64748b' }}" class="w-full h-8 bg-bg-input rounded-lg border border-border-main p-0.5 cursor-pointer" title="Farbe für Verpachtung wählen">
                                </div>
                            </div>
                        </div>

                        <hr class="border-border-main/40 m-0 my-2">

                        <!-- 💾 Speichern-Aktor im einheitlichen ERP-Gewand -->
                        <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold font-mono text-xs py-3 rounded-xl transition border-0 cursor-pointer">
                            🔒 Visuelle Kartenfarben permanent versiegeln
                        </button>
                    </div>
                </form>
            @endif

            <!-- TAB: NUMMERNKREISE (Auf volle Widescreen-Breite freigeschaltet) -->
            @if($aktivesTab === 'nummernkreise')
                <form action="{{ route('admin.nummernkreise.store') }}" method="POST" class="w-full space-y-6">
                    @csrf
                    <div class="bg-bg-surface border border-border-main rounded-2xl p-5 space-y-6 shadow-3xs w-full">

                        
                        <!-- 🎯 HIER ERSETZT ER DAS ALTE HEADER-BAND: -->
                        <div class="flex justify-between items-start border-b border-border-main/50 pb-3">
                            <div>
                                <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-accent-brand m-0">🔢 Historisierte Zählwerke</h3>
                                <p class="text-[11px] text-text-muted mt-0.5">Nutze einklammerte Definitionen für reaktive Datenübersetzungen.</p>
                            </div>
                            
                            <!-- 🎯 Der reaktive Historie-Umschalter -->
                            <div class="flex items-center gap-2 bg-bg-input px-3 py-2 rounded-xl border border-border-main/60 shadow-3xs">
                                <label class="flex items-center gap-2 text-[11px] font-mono font-bold text-text-main cursor-pointer">
                                    <input type="checkbox" id="zeige_historie_check" onchange="ToggleHistorieSichtbarkeit()" class="rounded border-border-main text-accent-brand focus:ring-accent-brand">
                                    <span>📜 Abgelaufene Kreise anzeigen</span>
                                </label>
                            </div>
                        </div>

                        @foreach($nummernkreise as $modulKey => $kreise)
                            <div class="space-y-4">
                                <!-- 🎯 REPARATUR: Nutzt einen sicheren Fallback, falls der Modul-Key in der DB leer oder ungültig ist -->
                                <h4 class="text-xs font-mono font-bold uppercase text-text-main m-0">
                                    📦 Sektion: {{ isset($modulSteckbriefe[$modulKey]) ? $modulSteckbriefe[$modulKey]['name'] : 'System-Zählwerke (Fallback: ' . ($modulKey ?: 'Unbekannt') . ')' }}
                                </h4>
                                
                                <div class="space-y-6 border-l-2 border-border-main/40 pl-3">

                                    @foreach($kreise as $kreisKey => $perioden)
                                        <div class="bg-bg-input/30 border border-border-main/60 rounded-2xl p-3 space-y-3">
                                            <div class="flex justify-between items-center bg-bg-surface px-3 py-1.5 rounded-xl border border-border-main/40 shadow-3xs">
                                                <!-- 🎯 REPARATUR: Fallback-Schutz, falls die DB korrupte Fragmente enthält -->
                                                <span class="text-xs font-bold text-text-main">
                                                    📌 {{ isset($modulSteckbriefe[$modulKey]['kreise'][$kreisKey]) ? $modulSteckbriefe[$modulKey]['kreise'][$kreisKey]['label'] : 'Zählwerk: ' . ($kreisKey ?: 'Unbekannt') }}
                                                </span>
                                                <button type="button" onclick="document.getElementById('neu_form_{{ $kreisKey }}').classList.toggle('hidden')" class="text-[10px] font-mono bg-bg-input border border-border-main hover:bg-border-main text-text-main px-2 py-0.5 rounded-md cursor-pointer"><span>➕ Zeitraum anbauen</span></button>
                                            </div>

                                            <!-- 🎨 PREMIUM-UX: Flüssige Ausrichtung mitsamt automatischer Nummerierung -->
                                            <div class="space-y-3">
                                                @foreach($perioden as $k)
                                                    <div class="flex flex-col md:flex-row md:items-center gap-4 bg-bg-surface p-4 rounded-xl border border-border-main/60 shadow-2xs transition-all duration-200 {{ $k->ist_historisch ? 'historisch-row hidden opacity-50 border-amber-500/20 bg-amber-500/[0.02]' : '' }}">
                                                        
                                                        <!-- 🎯 NEU: Die fortlaufende Index-Badge (#1, #2, #3...) -->
                                                        <div class="flex-shrink-0 flex items-center justify-center bg-bg-input border border-border-main/80 h-9 w-10 rounded-xl font-mono text-xs font-bold text-text-muted" title="Intervall-Nummer">
                                                            #{{ $loop->iteration }}
                                                        </div>

                                                        <!-- 1. Bereich: Das Muster (SCHREIBGESCHÜTZT via readonly mit data-preview-id) -->
                                                        <div class="flex-1 min-w-[240px]">
                                                            <label class="block text-[11px] font-mono font-bold uppercase tracking-wider text-text-muted mb-1.5">
                                                                Muster {!! $k->ist_historisch ? '<span class="text-amber-600 font-bold font-sans ml-1">(Abgelaufen)</span>' : '' !!}
                                                            </label>
                                                            <input type="text" 
                                                                   name="kreis[{{ $k->id }}][muster]" 
                                                                   value="{{ $k->muster }}" 
                                                                   readonly
                                                                   data-preview-id="preview_{{ $k->id }}"
                                                                   class="w-full bg-bg-input/60 text-text-muted text-xs font-mono font-bold rounded-xl border border-border-main px-3 py-2 cursor-not-allowed select-none pattern-input-readonly">
                                                            
                                                            <div class="text-[11px] text-text-muted mt-1.5 font-mono flex items-center gap-1">
                                                                <span>🔒 Versiegelt:</span> 
                                                                <span id="preview_{{ $k->id }}" class="text-text-main font-bold font-mono">Berechne...</span>
                                                            </div>
                                                        </div>

                                                        <!-- 2. Bereich: Der aktuelle Zählerstand -->
                                                        <div class="w-full md:w-36 flex-shrink-0">
                                                            <label class="block text-[11px] font-mono font-bold uppercase tracking-wider text-text-muted mb-1.5">Zählerstand</label>
                                                            <input type="number" 
                                                                   name="kreis[{{ $k->id }}][zaehlerstand]" 
                                                                   value="{{ $k->zaehlerstand }}" 
                                                                   readonly
                                                                   class="w-full bg-bg-input/60 text-text-muted text-xs font-mono rounded-xl border border-border-main px-3 py-2 cursor-not-allowed select-none">
                                                        </div>

                                                        <!-- 3. Bereich: Gültig von -->
                                                        <div class="w-full md:w-40 flex-shrink-0">
                                                            <label class="block text-[11px] font-mono font-bold uppercase tracking-wider text-text-muted mb-1.5">Gültig von</label>
                                                            <input type="date" 
                                                                   name="kreis[{{ $k->id }}][gueltig_von]" 
                                                                   value="{{ $k->gueltig_von ? $k->gueltig_von->format('Y-m-d') : '' }}" 
                                                                   readonly
                                                                   class="w-full bg-bg-input/60 text-text-muted text-xs font-mono rounded-xl border border-border-main px-3 py-2 cursor-not-allowed select-none">
                                                        </div>

                                                        <!-- 4. Bereich: Gültig bis & Lösch-Aktor -->
                                                        <div class="w-full md:w-44 flex-shrink-0 flex items-center gap-3">
                                                            <div class="flex-1">
                                                                <label class="block text-[11px] font-mono font-bold uppercase tracking-wider text-text-muted mb-1.5">Gültig bis</label>
                                                                <input type="date" name="kreis[{{ $k->id }}][gueltig_bis]" value="{{ $k->gueltig_bis ? $k->gueltig_bis->format('Y-m-d') : '' }}" class="w-full bg-bg-input text-text-main text-xs font-mono rounded-xl border border-border-main px-3 py-2 shortcut-focus">
                                                            </div>
                                                        </div>

                                                    </div>
                                                @endforeach
                                            </div>


                                                   <!-- 🎯 RENDER-REPARATUR: Umstellung von Flex auf ein absolut stabiles Grid-System -->
                                            <div id="neu_form_{{ $kreisKey }}" class="hidden p-4 bg-slate-50 border border-dashed border-slate-300 rounded-xl space-y-3 text-[11px] w-full animate-fade-in">
                                                <div class="text-[10px] font-mono font-bold text-slate-700 uppercase tracking-wider">🆕 Zeitraum vordefinieren</div>
                                                <input type="hidden" name="neu[{{ $kreisKey }}][modul_key]" value="{{ $modulKey }}">
                                                <input type="hidden" name="neu[{{ $kreisKey }}][kreis_key]" value="{{ $kreisKey }}">
                                                <input type="hidden" name="neu[{{ $kreisKey }}][label]" value="{{ isset($modulSteckbriefe[$modulKey]['kreise'][$kreisKey]) ? $modulSteckbriefe[$modulKey]['kreise'][$kreisKey]['label'] : 'Zählwerk ' . $kreisKey }}">
                                                
                                                <!-- 🎯 Grid zwingt die Felder in die korrekte horizontale Achse -->
                                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 w-full">
                                                    
                                                    <!-- Feld 1: Das reaktive Muster-Feld -->
                                                    <div>
                                                        <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-text-muted mb-1">Muster-Format</label>
                                                        <input type="text" 
                                                               name="neu[{{ $kreisKey }}][muster]" 
                                                               placeholder="Muster z.B. RE-{ZAEHLER;4}/JJ" 
                                                               data-zaehlerstand="0"
                                                               oninput="BerechneLiveNummer(this, 'preview_neu_{{ $kreisKey }}')"
                                                               class="w-full bg-bg-surface text-text-main text-xs rounded-xl border border-border-main px-3 py-2 font-mono font-bold pattern-input">
                                                        
                                                        <div class="text-[10px] text-text-muted mt-1.5 font-mono">
                                                            💡 Vorschau: <span id="preview_neu_{{ $kreisKey }}" class="text-accent-brand font-bold font-mono">Muster eintippen...</span>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Feld 2: Der Start-Zählerstand -->
                                                    <div>
                                                        <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-text-muted mb-1">Start-Wert (Zähler)</label>
                                                        <input type="number" name="neu[{{ $kreisKey }}][zaehlerstand]" placeholder="z.B. 0" class="w-full bg-bg-surface text-text-main text-xs rounded-xl border border-border-main px-3 py-2 font-mono font-medium">
                                                    </div>
                                                    
                                                    <!-- Feld 3: Gültig von -->
                                                    <div>
                                                        <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-text-muted mb-1">Gültig von</label>
                                                        <input type="date" name="neu[{{ $kreisKey }}][gueltig_von]" value="{{ date('Y-m-d') }}" class="w-full bg-bg-surface text-text-main text-xs rounded-xl border border-border-main px-3 py-2 font-mono font-medium" title="Startdatum – Standardmäßig Heute">
                                                    </div>
                                                    
                                                    <!-- Feld 4: Gültig bis -->
                                                    <div>
                                                        <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-text-muted mb-1">Gültig bis</label>
                                                        <input type="date" name="neu[{{ $kreisKey }}][gueltig_bis]" placeholder="Unbegrenzt" class="w-full bg-bg-surface text-text-main text-xs rounded-xl border border-border-main px-3 py-2 font-mono font-medium">
                                                    </div>
                                                    
                                                </div>
                                                <div class="text-[10px] text-text-muted font-mono px-1 mt-1">💡 Info: Wenn das Startdatum leer bleibt, wird automatisch das heutige Datum hinterlegt, um einen lückenlosen Verlauf zu garantieren.</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @if(!$loop->last)<hr class="border-border-main/40">@endif
                        @endforeach

                        <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold font-mono text-xs py-3 rounded-xl border-0 cursor-pointer">💾 Parameter permanent sichern</button>
                    </div>
                </form>
            @endif

            @if($aktivesTab === 'dropdowns')
                <div class="max-w-2xl bg-bg-surface border border-border-main rounded-2xl p-4">📦 Maske für Dropdowns folgt im Listenmodul.</div>
            @endif
        </div>
    </div>
</div>
<!-- 🧠 DYNAMISCHER NUMMERNKREIS-LIVE-PARSER -->
<script>
    function BerechneLiveNummer(inputElement, targetId) {
        const previewSpan = document.getElementById(targetId);
        if (!previewSpan || !inputElement) return;

        let muster = inputElement.value;
        // Zählerstand abgreifen und hypothetisch um +1 für den nächsten echten Beleg erhöhen
        let zaehlerstand = parseInt(inputElement.getAttribute('data-zaehlerstand')) || 0;
        let naechsterZaehler = zaehlerstand + 1;

        // 📅 Aktuelle Carbon-Zeitparameter simulieren
        const jetzt = new Date();
        const jjjj = jetzt.getFullYear().toString(); // z.B. 2026
        const jj = jjjj.substring(2); // z.B. 26
        const mm = String(jetzt.getMonth() + 1).padStart(2, '0'); // z.B. 09

        // Kalenderwoche nach ISO-8601 ermitteln
        const d = new Date(Date.UTC(jetzt.getFullYear(), jetzt.getMonth(), jetzt.getDate()));
        d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay() || 7));
        const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
        const kw = String(Math.ceil((((d - yearStart) / 86400000) + 1) / 7)).padStart(2, '0');

        const tagWoche = jetzt.getDay() === 0 ? '7' : jetzt.getDay().toString(); // 1-7

        // Tag des Jahres (001-366) ermitteln
        const start = new Date(jetzt.getFullYear(), 0, 0);
        const diff = jetzt - start;
        const oneDay = 1000 * 60 * 60 * 24;
        const tagJahr = String(Math.floor(diff / oneDay)).padStart(3, '0');

        // 1. Basis-Datums-Ersetzungen durchführen (g = global, i = case-insensitive für z.B. {mm} oder {mm})
        muster = muster.replace(/{JJJJ}/gi, jjjj);
        muster = muster.replace(/{JJ}/gi, jj);
        muster = muster.replace(/{MM}/gi, mm);
        muster = muster.replace(/{KW}/gi, kw);
        muster = muster.replace(/{TAG_WOCHE}/gi, tagWoche);
        muster = muster.replace(/{TAG_JAHR}/gi, tagJahr);

        // 2. 🎯 UNZERSTÖRBARER REGEX-PARSER FÜR {ZAEHLER;X} ODER {zähler;x}
        // Das 'i' am Ende macht den Matcher immun gegen Groß-/Kleinschreibung!
        // Das '?' hinter \d+ fängt auch leere Semikolons {ZAEHLER;} ab.
        muster = muster.replace(/\{(ZAEHLER|ZÄHLER);(\d*)\}/gi, function(match, word, laenge) {
            let n = parseInt(laenge);
            // Fallback: Wenn kein Semikolon-Wert oder eine 0 eingetragen wurde, gib die nackte Zahl aus
            if (isNaN(n) || n <= 0) {
                return String(naechsterZaehler);
            }
            return String(naechsterZaehler).padStart(n, '0');
        });

        // 3. Fallback für den einfachen nackten Zähler völlig ohne Semikolon ({ZAEHLER} oder {zähler})
        muster = muster.replace(/\{(ZAEHLER|ZÄHLER)\}/gi, naechsterZaehler);

        // Vorschau-Badge im edlen ERP-Gewand beschreiben
        previewSpan.innerText = muster;

    }
    // 🎯 ABSOLUTE PFADSICHERHEIT: Nutzt jetzt die direkt eingebrannten Attribute
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. Weg: Für editierbare Felder (Nutzt das oninput-Attribut)
        document.querySelectorAll('.pattern-input').forEach(function(input) {
            const oninputAttr = input.getAttribute('oninput');
            if (oninputAttr) {
                const match = oninputAttr.match(/'([^']+)'/);
                if (match && match[1]) {
                    BerechneLiveNummer(input, match[1]);
                }
            }
        });
        
        // 2. Weg: Für versiegelte (readonly) Felder – blindes, fehlerfreies Ansteuern via Attribut!
        document.querySelectorAll('.pattern-input-readonly').forEach(function(input) {
            const targetId = input.getAttribute('data-preview-id');
            if (targetId) {
                BerechneLiveNummer(input, targetId);
            }
        });
        
        // Initialisiert die Sichtbarkeit der Historie beim Seitenstart
        ToggleHistorieSichtbarkeit();
    });


        /**
     * 🎯 NEU: Steuert die Sichtbarkeit abgelaufener Nummernkreis-Zeiträume im Dashboard
     */
    function ToggleHistorieSichtbarkeit() {
        const check = document.getElementById('zeige_historie_check');
        const rows = document.querySelectorAll('.historisch-row');
        
        if (!check) return;
        
        rows.forEach(row => {
            if (check.checked) {
                row.classList.remove('hidden');
            } else {
                row.classList.add('hidden');
            }
        });
    }
</script>

@endsection
