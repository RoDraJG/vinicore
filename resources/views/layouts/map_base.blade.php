@extends('layouts.app')

@section('content')
<!-- ==========================================================================
     🛰️ VINICORE GIS-COCKPIT BASE-FRAMEWORK
     🚀 REPARATUR: 'pl-12' schafft links den perfekten Freiraum für den schwebenden Button!
     ========================================================================== -->
<div class="flex-1 min-w-0 flex flex-col h-full w-full overflow-hidden font-sans text-xs text-slate-700 space-y-3">
    
    <!-- 🎛️ Das universelle Menüband -->
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex-shrink-0 pl-12">
        
        <!-- Linke Flanke: Deine saubere Unterseiten-Navigation -->
        <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-xl border border-slate-200 shadow-inner">
            <a href="/kataster/parzellen-karte" 
               class="px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider rounded-lg transition-all no-underline flex items-center {{ Request::is('kataster/*') ? 'bg-white text-slate-800 border border-slate-200 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                🌐 Parzellen sammeln (ALKIS)
            </a>
            <a href="/schlaege/schlag-karte" 
               class="px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider rounded-lg transition-all no-underline flex items-center {{ Request::is('schlaege/*') ? 'bg-white text-slate-800 border border-slate-200 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                🍇 Schläge & Anlagen verwalten
            </a>
        </div>

        <!-- Rechte Flanke: Die bewährte Suchmaske -->
        <div class="flex flex-wrap items-center gap-2">
            <input type="text" id="searchOrtName" placeholder="Gemarkung" class="border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500 font-medium bg-slate-50/50">
            <input type="text" id="searchFlur" placeholder="Flur" class="w-20 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500 font-medium bg-slate-50/50">
            <input type="text" id="searchFlurstueck" placeholder="Zähler" class="w-16 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500 font-medium bg-slate-50/50">
            <input type="text" id="searchNenner" placeholder="Nenner" class="w-16 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500 font-medium bg-slate-50/50">
            <button onclick="ZündeGeoportalAbfrage()" class="bg-slate-800 hover:bg-slate-900 text-white font-semibold font-mono px-3 py-1.5 rounded-lg transition cursor-pointer shadow-sm">🔍 ALKIS-Suche</button>
        </div>

    </div>
    <!-- 🗺️ Der 12er-Split für die Unterseiten -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 flex-1 min-w-0 w-full h-full pb-1">
        
        <!-- Karte (10 Spalten - ca. 83% Breite) -->
        <div class="md:col-span-10 bg-white p-2 rounded-xl border border-slate-200 shadow-sm relative h-full min-h-0">
            <div id="vinicoreOverviewMap" class="w-full h-full relative rounded-xl overflow-hidden border border-slate-200 shadow-sm">
                <!-- 🛰️ Reaktiver Spinner -->
                <div id="vinicoreMapSpinner" class="hidden absolute top-4 left-1/2 -translate-x-1/2 bg-slate-900/90 text-white backdrop-blur-xs font-mono font-bold text-[10px] px-3 py-1.5 rounded-full shadow-lg z-50 flex items-center space-x-2 transition-all">
                    <svg class="animate-spin h-3.5 w-3.5 text-blue-500" xmlns="http://w3.org" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>🛰️ GDI-RLP wird geladen...</span>
                </div>
            </div>
        </div>

        <!-- Dynamischer Inspektor (2 Spalten - ca. 17% Breite) -->
        <div id="inspektorContainer" class="md:col-span-2 bg-white p-4 rounded-xl border border-slate-200 shadow-sm overflow-y-auto h-full min-h-0 max-h-full">
            @yield('inspektor_content')
        </div>

    </div>

</div>

@yield('modals')
<!-- ==========================================================================
     ⚙️ GLOBALE GIS-KONSTANTEN (NATIV IM REINEN BROWSER-WINDOW VERANKERT)
     ========================================================================== -->
<script>
    if (typeof window.ZOOM_MAX_LIMIT === 'undefined') { window.ZOOM_MAX_LIMIT = 21; }
    if (typeof window.ZOOM_WMS_START === 'undefined') { window.ZOOM_WMS_START = 10; }
    if (typeof window.ZOOM_WFS_START === 'undefined') { window.ZOOM_WFS_START = 15; }
    
    var ZOOM_MAX_LIMIT = window.ZOOM_MAX_LIMIT;
    var ZOOM_WMS_START = window.ZOOM_WMS_START;
    var ZOOM_WFS_START = window.ZOOM_WFS_START;
</script>

<!-- Hier wird das JavaScript der jeweiligen Unterseiten sauber reingeladen -->
@yield('map_js')

@endsection