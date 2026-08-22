@extends('layouts.app')

@section('content')
<!-- Clean SaaS Header -->
<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white px-6 py-4 rounded-xl border border-slate-200 shadow-sm gap-4">
    <div>
        <h1 class="text-xl font-bold tracking-tight text-slate-900">
            vinicore <span class="text-slate-400 font-normal text-sm" id="parentSchlagHeader">| Biologische Anlagen</span>
        </h1>
        <a href="/schlaege" class="text-[10px] text-slate-500 hover:text-emerald-600 font-mono uppercase tracking-wider block mt-1 transition">&larr; Zurück zum Haupt-Leitstand</a>
    </div>
    <span class="text-slate-500 border border-slate-200 px-3 py-1 rounded-full text-[10px] font-mono font-bold bg-slate-50 tracking-wider uppercase">
        Homogene Teilstücke
    </span>
</header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4 text-xs">
    <!-- Linke Spalte: Neue biologische Anlage hinzufügen -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4 h-fit">
        <h3 class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold border-b border-slate-100 pb-1.5">
            ➕ Neue Anlage initialisieren
        </h3>
        <form onsubmit="erstelleAnlage(event)" class="space-y-3">
            <div>
                <label class="text-slate-600 font-medium block mb-1">Name des Teilstücks (z.B. Riesling Steilhang 2014):</label>
                <input type="text" id="anlageName" required class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
            </div>
            
            <div class="grid grid-cols-2 gap-3 border-t border-slate-50 pt-2">
                <div>
                    <label class="text-slate-600 block mb-1">Vorgewende Oben (cm):</label>
                    <input type="number" id="vorgewendeStart" value="400" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-1.5 font-mono">
                </div>
                <div>
                    <label class="text-slate-600 block mb-1">Vorgewende Unten (cm):</label>
                    <input type="number" id="vorgewendeEnde" value="400" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-1.5 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-slate-600 block mb-1">Randabstand Links (cm):</label>
                    <input type="number" id="rand Links" value="120" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-1.5 font-mono">
                </div>
                <div>
                    <label class="text-slate-600 block mb-1">Randabstand Rechts (cm):</label>
                    <input type="number" id="randRechts" value="120" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-1.5 font-mono">
                </div>
            </div>

            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-lg transition uppercase tracking-wider text-[10px]">
                Anlage im Schlag verankern
            </button>
        </form>
    </div>

    <!-- Rechte Spalte: Die Liste aller biologischen Teilstücke dieses Schlags -->
    <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col min-h-[450px]">
        <h3 class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold mb-3 border-b border-slate-100 pb-1.5">
            📋 Vorhandene biologische Blöcke
        </h3>
        <div id="anlagenListeContainer" class="space-y-3 flex-1 overflow-y-auto">
            <div class="text-center text-slate-400 py-12 font-mono">Lade Anlagen-Struktur...</div>
        </div>
    </div>
</div>
<script>
    let aktuellerSchlagId = 1;

    document.addEventListener("DOMContentLoaded", async () => {
        const urlParams = new URLSearchParams(window.location.search);
        aktuellerSchlagId = parseInt(urlParams.get('schlag_id')) || 1;
        
        await ladeSchlagKontextHeader();
        await ladeAnlagenZentrale();
    });

    async function ladeSchlagKontextHeader() {
        try {
            const response = await fetch(`/api/schlaege/${aktuellerSchlagId}`);
            const result = await response.json();
            if (result.success && result.data) {
                document.getElementById('parentSchlagHeader').innerText = `| ${result.data.name} (Anlagen)`;
            }
        } catch (e) { console.error(e); }
    }

    async function ladeAnlagenZentrale() {
        try {
            const response = await fetch(`/api/anlagen/schlag/${aktuellerSchlagId}`);
            const result = await response.json();
            const container = document.getElementById('anlagenListeContainer');
            
            if (result.success && Array.isArray(result.data)) {
                container.innerHTML = '';

                if (result.data.length === 0) {
                    container.innerHTML = `<div class="text-center text-slate-400 py-12 font-mono">🚫 Keine biologischen Anlagen in diesem Schlag gepflegt. Nutze die linke Maske!</div>`;
                    return;
                }

                result.data.forEach(a => {
                    const div = document.createElement('div');
                    div.className = 'bg-slate-50 p-4 rounded-xl border border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shadow-sm group';
                    
                    let statusColor = 'text-amber-500 border-amber-200 bg-amber-50';
                    if (a.plan_status === 'aktiv_bewirtschaftet') statusColor = 'text-emerald-500 border-emerald-200 bg-emerald-50';

                    div.innerHTML = `
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-slate-900 text-sm tracking-tight">${a.name}</h4>
                                <span class="text-[9px] font-mono px-2 py-0.5 rounded border ${statusColor} uppercase font-bold">${a.plan_status.replace('_', ' ')}</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-1 text-[10px] font-mono text-slate-500">
                                <span>📐 Vw: O: ${a.vorgewende_start_cm} / U: ${a.vorgewende_end_cm} cm</span>
                                <span>🗺️ Rand: L: ${a.randabstand_links_cm} / R: ${a.randabstand_rechts_cm} cm</span>
                                <span>🪵 Zug: P: ${a.abstand_anker_endpfahl_cm} / R: ${a.abstand_endpfahl_rebe_cm} cm</span>
                                <span>🍇 Raster: G: ${a.ziel_gassenbreite_cm} / S: ${a.stockabstand_cm} cm</span>
                            </div>
                        </div>
                        <div class="flex gap-2 w-full sm:w-auto justify-end">
                            <button onclick="window.location.href='/matrix-planer?anlage_id=${a.id}'" class="bg-slate-900 hover:bg-slate-800 text-white font-mono font-bold text-[10px] px-3 py-2 rounded-lg transition shadow-sm cursor-pointer uppercase tracking-wider">
                                🎨 Matrix-Designer
                            </button>
                        </div>
                    `;
                    container.appendChild(div);
                });
            }
        } catch (e) { console.error(e); }
    }

    async function erstelleAnlage(event) {
        if (event) event.preventDefault();
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const paket = {
            schlag_id: aktuellerSchlagId,
            name: document.getElementById('anlageName').value,
            vorgewende_start_cm: parseInt(document.getElementById('vorgewendeStart').value) || 400,
            vorgewende_ende_cm: parseInt(document.getElementById('vorgewendeEnde').value) || 400,
            randabstand_links_cm: parseInt(document.getElementById('randLinks').value) || 120,
            randabstand_rechts_cm: parseInt(document.getElementById('randRechts').value) || 120
        };

        try {
            const response = await fetch('/api/anlagen', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify(paket)
            });
            
            if (response.ok) {
                document.getElementById('anlageName').value = '';
                await ladeAnlagenZentrale();
            }
        } catch (e) { console.error(e); }
    }
</script>
