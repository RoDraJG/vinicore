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
        <div class="flex-1 overflow-y-auto p-4 md:p-6 min-h-0 bg-bg-base/30">

            
            <!-- 🏛️ TAB 1: BETRIEBSDEFINITIONEN & GEODATEN-FARBEN -->
            @if($aktivesTab === 'betrieb')
                <!-- Formular zielt auf deine bestehende Speicher-Route aus dem Kataster-System -->
                <form action="{{ route('einstellungen.speichern') }}" method="POST" class="max-w-2xl space-y-6">
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

            <!-- TAB: NUMMERNKREISE -->
            @if($aktivesTab === 'nummernkreise')
                <form action="{{ route('admin.nummernkreise.store') }}" method="POST" class="max-w-4xl space-y-6">
                    @csrf
                    <div class="bg-bg-surface border border-border-main rounded-2xl p-4 space-y-6 shadow-3xs">
                        
                        <div class="flex justify-between items-start border-b border-border-main/50 pb-3">
                            <div>
                                <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-accent-brand m-0">🔢 Historisierte Zählwerke</h3>
                                <p class="text-[11px] text-text-muted mt-0.5">Nutze einklammerte Definitionen für reaktive Datenübersetzungen.</p>
                            </div>
                            <div class="bg-bg-input/60 border border-border-main/60 p-2 rounded-xl text-[10px] font-mono text-text-muted space-y-0.5 leading-tight">
                                <div><span class="text-text-main font-bold">{ZAEHLER}</span> = Zähler | <span class="text-text-main font-bold">{JJJJ}/{JJ}</span> = Jahr</div>
                                <div><span class="text-text-main font-bold">{MM}</span> = Monat | <span class="text-text-main font-bold">{KW}</span> = Kalenderwoche</div>
                                <div><span class="text-text-main font-bold">{TAG_WOCHE}</span> = Wochentag | <span class="text-text-main font-bold">{TAG_JAHR}</span> = Tag des Jahres</div>
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

                                            <div class="space-y-2">
                                                @foreach($perioden as $k)
                                                    <div class="grid grid-cols-1 md:grid-cols-5 gap-2 items-end bg-bg-surface p-2.5 rounded-xl border border-border-main/50 text-[11px]">
                                                        <div>
                                                            <label class="block text-[10px] text-text-muted mb-0.5">Muster</label>
                                                            <input type="text" name="kreis[{{ $k->id }}][muster]" value="{{ $k->muster }}" class="w-full bg-bg-input text-text-main rounded-lg border border-border-main px-2 py-1 font-mono font-bold">
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] text-text-muted mb-0.5">Zählerstand</label>
                                                            <input type="number" name="kreis[{{ $k->id }}][zaehlerstand]" value="{{ $k->zaehlerstand }}" class="w-full bg-bg-input text-text-main rounded-lg border border-border-main px-2 py-1 font-mono">
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] text-text-muted mb-0.5">Von</label>
                                                            <input type="date" name="kreis[{{ $k->id }}][gueltig_von]" value="{{ $k->gueltig_von ? $k->gueltig_von->format('Y-m-d') : '' }}" class="w-full bg-bg-input text-text-main rounded-lg border border-border-main px-2 py-1 font-mono">
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] text-text-muted mb-0.5">Bis</label>
                                                            <input type="date" name="kreis[{{ $k->id }}][gueltig_bis]" value="{{ $k->gueltig_bis ? $k->gueltig_bis->format('Y-m-d') : '' }}" class="w-full bg-bg-input text-text-main rounded-lg border border-border-main px-2 py-1 font-mono">
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <div class="flex-1">
                                                                <label class="block text-[10px] text-text-muted mb-0.5">Nullen</label>
                                                                <input type="number" name="kreis[{{ $k->id }}][fuehrende_nullen]" value="{{ $k->fuehrende_nullen }}" class="w-full bg-bg-input text-text-main rounded-lg border border-border-main px-2 py-1 font-mono">
                                                            </div>
                                                            @if($loop->count > 1)
                                                                <label class="flex items-center gap-1 text-red-600 font-mono text-[10px] mt-4 cursor-pointer"><input type="checkbox" name="kreis[{{ $k->id }}][loeschen]" value="1" class="rounded text-red-600"> <span>🗑️</span></label>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div id="neu_form_{{ $kreisKey }}" class="hidden p-3 bg-slate-50 border border-dashed border-slate-300 rounded-xl space-y-2 text-[11px]">
                                                <div class="text-[10px] font-mono font-bold text-slate-700 uppercase tracking-wider">🆕 Zeitraum vordefinieren</div>
                                                <input type="hidden" name="neu[modul_key]" value="{{ $modulKey }}">
                                                <input type="hidden" name="neu[kreis_key]" value="{{ $kreisKey }}">
                                                
                                                <!-- 🎯 REPARATUR: Fallback-Schutz für das versteckte Label-Feld -->
                                                <input type="hidden" name="neu[label]" value="{{ isset($modulSteckbriefe[$modulKey]['kreise'][$kreisKey]) ? $modulSteckbriefe[$modulKey]['kreise'][$kreisKey]['label'] : 'Zählwerk ' . $kreisKey }}">
                                                
                                                <div class="grid grid-cols-1 sm:grid-cols-5 gap-2">

                                                    <input type="text" name="neu[muster]" placeholder="z.B. {ZAEHLER}/JJ" class="bg-bg-surface rounded-lg border border-border-main px-2 py-1 font-mono"><input type="number" name="neu[zaehlerstand]" placeholder="Start-Wert" class="bg-bg-surface rounded-lg border border-border-main px-2 py-1"><input type="date" name="neu[gueltig_von]" class="bg-bg-surface rounded-lg border border-border-main px-2 py-1 font-mono"><input type="date" name="neu[gueltig_bis]" class="bg-bg-surface rounded-lg border border-border-main px-2 py-1 font-mono"><input type="number" name="neu[fuehrende_nullen]" placeholder="Nullen" class="bg-bg-surface rounded-lg border border-border-main px-2 py-1">
                                                </div>
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
@endsection
