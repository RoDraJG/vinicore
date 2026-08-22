@extends('layouts.app')

@section('content')
<!-- Clean SaaS Header mit Live-Metriken -->
<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white px-6 py-4 rounded-xl border border-slate-200 shadow-sm gap-4">
    <div>
        <h1 class="text-xl font-bold tracking-tight text-slate-900">
            vinicore <span class="text-slate-400 font-normal text-sm" id="matrixSchlagTitel">| Matrix-Planer</span>
        </h1>
        <div class="flex items-center gap-2 mt-1">
            <a href="/schlaege" class="text-[10px] text-slate-500 hover:text-emerald-600 font-mono uppercase tracking-wider transition">&larr; Cockpit</a>
            <span class="text-slate-300 text-[10px] font-mono">|</span>
            <span id="editStatusBadge" class="text-slate-400 border border-slate-200 px-2 py-0.5 rounded text-[9px] font-mono uppercase tracking-wider font-bold bg-slate-50">STATUS: Read-Only</span>
        </div>
    </div>
    
    <!-- Aktive Werkzeugleiste im Header -->
    <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
        <div class="flex items-center gap-1.5 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg">
            <span class="text-[9px] font-mono text-slate-400 uppercase font-bold">Reihe:</span>
            <select id="zeilenWechsler" onchange="aktiviereSichtbareZeile(this.value)" class="bg-transparent text-slate-900 text-xs font-mono font-bold focus:outline-none cursor-pointer">
                <option value="1">Lade Zeilen...</option>
            </select>
        </div>
        <button id="toggleEditBtn" onclick="starteBearbeitungsmodus()" class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-2 px-4 rounded-lg transition text-xs uppercase tracking-wider cursor-pointer shadow-sm">
            🛠️ Planen
        </button>
        <button id="saveEditBtn" onclick="finaleBearbeitungAbschliessen()" class="hidden bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg transition text-xs uppercase tracking-wider cursor-pointer shadow-sm">
            💾 Sichern
        </button>
        <button id="cancelEditBtn" onclick="bearbeitungAbbrechenOhneSpeichern()" class="hidden bg-white hover:bg-slate-50 text-slate-500 border border-slate-200 font-bold py-2 px-4 rounded-lg transition text-xs uppercase tracking-wider cursor-pointer">
            Abbrechen
        </button>
    </div>
</header>

<!-- Zweispaltiges GIS-Raster -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mt-4">
    <!-- Linke Hauptspalte: Das interaktive 2D-SVG-Spielfeld -->
    <div class="lg:col-span-3 bg-slate-900 rounded-xl border border-slate-800 shadow-inner p-4 relative overflow-hidden flex items-center justify-center min-h-[520px]">
        <div id="gisLoaderText" class="absolute inset-0 flex items-center justify-center bg-slate-950/80 text-slate-400 font-mono text-xs tracking-wider z-50">
            <span class="animate-pulse">📡 Initialisiere Vektor-Mosaik...</span>
        </div>
        
        <!-- HIER IST DIE RENDERING-RETTUNG: Die IDs passen haargenau zu deiner vinicore-matrix.js! -->
        <svg id="vinicoreGisCanvas" viewBox="0 0 1400 720" class="w-full h-auto select-none rounded-lg">
            <g id="katasterLayer"></g>
            <g id="zeilenLayer"></g>
        </svg>
    </div>
    <!-- Rechte Spalte: Das dedizierte Inspektions-Panel (Der OP-Tisch) -->
    <aside class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex flex-col justify-between space-y-6">
        <div>
            <div class="border-b border-slate-100 pb-3 mb-4">
                <span id="infoZeilenBadge" class="text-xs font-mono font-bold bg-emerald-50 border border-emerald-200 text-emerald-700 px-2 py-1 rounded">REIHE: 1</span>
                <h2 class="text-base font-bold text-slate-900 tracking-tight mt-2">Rebzeilen-Inspektion</h2>
            </div>

            <!-- Dynamische Kennzahlen (Werden live im RAM berechnet) -->
            <div class="space-y-3.5 text-xs">
                <div class="flex justify-between items-center bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                    <span class="text-slate-500 font-medium">Reihen-Länge:</span>
                    <span id="infoZeilenLaenge" class="font-mono font-bold text-slate-900">0.0 m</span>
                </div>
                <div class="flex justify-between items-center bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                    <span class="text-slate-500 font-medium">Vitale Rebstöcke:</span>
                    <span id="infoAnzahlReben" class="font-mono font-bold text-emerald-600">0</span>
                </div>
                <div class="flex justify-between items-center bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                    <span class="text-slate-500 font-medium">Physische Fehlstellen:</span>
                    <span id="infoAnzahlFehlstellen" class="font-mono font-bold text-rose-500">0</span>
                </div>
                <div class="flex justify-between items-center bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                    <span class="text-slate-500 font-medium">Gesetzte Stickel/Pfähle:</span>
                    <span id="infoAnzahlPfaehle" class="font-mono font-bold text-slate-700">0</span>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 pt-4 space-y-3 hidden">
            <div>
                <label class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold block mb-1">Flurstücks-Kupplung:</label>
                <select id="zeilenParzellenWaehler" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-xs text-slate-900 font-bold focus:outline-none">
                    <option value="">Automatisches GIS-Mapping active</option>
                </select>
            </div>
        </div>
        <p class="text-[10px] text-slate-400 italic leading-relaxed">
            Klicke auf eine Haarlinie im Spielfeld, um eine Rebzeile zu inspizieren oder ihren Zustand im Planungs-Modus zu verändern.
        </p>
    </aside>
</div>

<!-- Verknüpfung deiner runderneuerten JS-Engine -->
<script src="{{ asset('js/vinicore-matrix.js') }}"></script>
