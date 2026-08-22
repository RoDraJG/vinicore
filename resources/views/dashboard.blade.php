@extends('layouts.app')

@section('content')
<!-- ==========================================================================
     📊 VINICORE DATA-HUB (ZENTRALES MULTI-MODUL-DASHBOARD)
     🚀 ARCHITEKTUR: Abgestimmt auf dein 'weingut_name'-Feld und bereit für Injektionen!
     ========================================================================== -->
<div class="space-y-6 font-sans text-slate-700 h-full overflow-y-auto pb-8 pr-1">
    
    <!-- 1. BEGRÜSSUNGS-HEADER -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs flex justify-between items-center bg-linear-to-r from-white via-white to-emerald-50/10">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Zentrales Betriebs-Dashboard</h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">Betriebsleiter-Cockpit für das Weingut: <span class="text-emerald-600 font-bold font-mono">{{ $betrieb->weingut_name ?? 'Vinicore Weingut' }}</span></p>
        </div>
        <div class="text-right hidden sm:block">
            <span class="text-[10px] font-mono bg-slate-100 px-2 py-1 rounded-md text-slate-500 font-bold uppercase tracking-wider">Daten-Kern: Online</span>
        </div>
    </div>

    <!-- 2. DATEN-KACHELN (BEREITS FÜR WEITERE BEREICHE VORSTRUKTURIERT) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- BEREICH A: KATASTER (Aus DB) -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs flex items-center space-x-4">
            <div class="p-3 rounded-xl bg-blue-50 text-blue-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2v1a2 2 0 002 2h2.1l.6 2.4c.1.4.5.6.9.6h1.5a1 1 0 001.8-.6l.3-1.2A9 9 0 103.055 11z" /></svg>
            </div>
            <div>
                <div class="text-[11px] font-mono uppercase tracking-wider font-bold text-slate-400">Kataster-Fläche</div>
                <div class="text-lg font-bold text-slate-900 mt-0.5 font-mono">{{ $kataster['summe_ha'] }} ha</div>
                <div class="text-[10px] text-slate-500 font-medium mt-0.5">Aus {{ $kataster['anzahl'] }} Parzellen</div>
            </div>
        </div>

        <!-- BEREICH B: AUSSENBETRIEB / WEINBAU (Vorbereitete Injektion) -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs flex items-center space-x-4">
            <div class="p-3 rounded-xl bg-emerald-50 text-emerald-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 4L9 7" /></svg>
            </div>
            <div>
                <div class="text-[11px] font-mono uppercase tracking-wider font-bold text-slate-400">Anlagen & Schläge</div>
                <div class="text-lg font-bold text-slate-900 mt-0.5 font-mono">{{ $weinbau['anzahl_schlaege'] }} Schläge</div>
                <div class="text-[10px] text-slate-500 font-medium mt-0.5">{{ $weinbau['ertrag_prognose'] }}</div>
            </div>
        </div>

        <!-- BEREICH C: INNENBETRIEB / KELLER (Vorbereitete Injektion) -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs flex items-center space-x-4">
            <div class="p-3 rounded-xl bg-purple-50 text-purple-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
            </div>
            <div>
                <div class="text-[11px] font-mono uppercase tracking-wider font-bold text-slate-400">Keller & Füllungen</div>
                <div class="text-lg font-bold text-slate-900 mt-0.5 font-mono">{{ $keller['liter_im_ausbau'] }} L</div>
                <div class="text-[10px] text-purple-600 font-bold mt-0.5">● {{ $keller['aktive_gaerungen'] }} Tanks in Gärung</div>
            </div>
        </div>

        <!-- BEREICH D: VERTRIEB / FINANZEN (Vorbereitete Injektion) -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs flex items-center space-x-4">
            <div class="p-3 rounded-xl bg-amber-50 text-amber-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
                <div class="text-[11px] font-mono uppercase tracking-wider font-bold text-slate-400">Umsatz & Posten</div>
                <div class="text-lg font-bold text-slate-900 mt-0.5 font-mono">{{ $finanzen['monats_umsatz'] }} €</div>
                <div class="text-[10px] text-red-500 font-bold mt-0.5">⚠️ {{ $finanzen['offene_rechnungen'] }} offene Posten</div>
            </div>
        </div>
    </div>
    <!-- 3. STRATEGISCHE ARBEITS-MODULE (DIREKT-LINKS) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <!-- Katasterwesen -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs flex flex-col justify-between space-y-4 hover:border-blue-300 transition-all">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-md uppercase tracking-wider">Stammdaten</span>
                    <span class="text-sm font-semibold text-slate-400">#kataster</span>
                </div>
                <h3 class="text-sm font-bold text-slate-900 tracking-tight">Flurstücksliste & Katasterspiegel</h3>
                <p class="text-xs text-slate-500 leading-relaxed">Verwalte die unbeweglichen, amtlichen ALKIS-Grundlagendaten deines Betriebs. Rufe die tabellarische Flurstücksliste auf oder schlage die interaktive Karte auf, um neue Flurstücke live zu importieren.</p>
            </div>
            <div class="grid grid-cols-2 gap-2 pt-2">
                <a href="/parzellen/uebersicht" class="text-center bg-slate-50 hover:bg-slate-100 text-slate-800 font-semibold font-mono text-[11px] py-2 px-3 rounded-xl border transition no-underline">📋 Zur Liste</a>
                <a href="/kataster/parzellen-karte" class="text-center bg-blue-600 hover:bg-blue-700 text-white font-bold font-mono text-[11px] py-2 px-3 rounded-xl transition no-underline shadow-xs">🗺️ Karten-Ansicht</a>
            </div>
        </div>

        <!-- Außenbetrieb -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs flex flex-col justify-between space-y-4 hover:border-emerald-300 transition-all">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wider">Außenwirtschaft</span>
                    <span class="text-sm font-semibold text-slate-400">#weinbau</span>
                </div>
                <h3 class="text-sm font-bold text-slate-900 tracking-tight">Schläge & Weinbergs-Anlagen</h3>
                <p class="text-xs text-slate-500 leading-relaxed">Steuere deine lebenden Bewirtschaftungsflächen im Hang. Überwache den Rebsortenspiegel, Zeilenbreiten, Pflanzjahre und pflege deine InVeKoS-Großschläge sowie Pflanzenschutzbelegungen.</p>
            </div>
            <a href="/schlaege/schlag-karte" class="text-center bg-emerald-600 hover:bg-emerald-700 text-white font-bold font-mono text-[11px] py-2.5 px-4 rounded-xl transition no-underline shadow-xs block">🧬 Schläge & Geometrien verwalten</a>
        </div>

        <!-- Innenbetrieb & Keller -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs flex flex-col justify-between space-y-4 hover:border-purple-300 transition-all">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono font-semibold text-purple-600 bg-purple-50 px-2.5 py-1 rounded-md uppercase tracking-wider">Keller & Logistik</span>
                    <span class="text-sm font-semibold text-slate-400">#kellerbuch</span>
                </div>
                <h3 class="text-sm font-bold text-slate-900 tracking-tight">Weinausbau & Kellerbuch</h3>
                <p class="text-xs text-slate-500 leading-relaxed">Das zukünftige Herzstück deiner Kellerwirtschaft. Dokumentiere Gärverläufe, sensorische Weinanalysen, Tank- und Fassbelegungen bis hin zur fertigen Abfüllung im Flaschenlager.</p>
            </div>
            <button disabled class="w-full text-center bg-slate-100 text-slate-400 font-mono text-[11px] py-2.5 px-4 rounded-xl border border-slate-200 cursor-not-allowed font-medium">⏳ Modul im Aufbau</button>
        </div>
    </div>

</div>
@endsection
