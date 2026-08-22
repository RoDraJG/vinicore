@extends('layouts.app')

@section('content')
<!-- SaaS Header mit Live-Status -->
<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white px-6 py-4 rounded-xl border border-slate-200 shadow-sm gap-4">
    <div>
        <h1 class="text-xl font-bold tracking-tight text-slate-900">
            vinicore <span class="text-slate-400 font-normal text-sm">| Schlag-Stammblatt</span>
        </h1>
        <a href="/schlaege" class="text-[10px] text-slate-500 hover:text-emerald-600 font-mono uppercase tracking-wider block mt-1 transition">&larr; Zurück zum Cockpit</a>
    </div>
    <div id="lockStatusBadge" class="text-slate-500 border border-slate-200 px-3 py-1 rounded-full text-[10px] font-mono font-bold bg-slate-50 tracking-wider uppercase animate-pulse">
        PRÜFE BEARBEITUNGS-LOCK...
    </div>
</header>

<!-- Haupt-Eingabemaske -->
<main class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm mt-4 text-xs">
    <form id="schlagStammForm" onsubmit="speichereSchlagStammdaten(event)" class="space-y-6">
        
        <!-- Sektion 1: Administrative Parameter -->
        <div class="space-y-3">
            <h3 class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold border-b border-slate-100 pb-1.5">
                1. Organisatorische Basisdaten
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-slate-600 font-medium block mb-1">Weinberg-Name (Lage):</label>
                    <input type="text" id="schlagName" required class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                </div>
                <div>
                    <label class="text-slate-600 font-medium block mb-1">Gesamtfläche (Hektar):</label>
                    <input type="number" id="schlagFlaeche" step="0.0001" required class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-slate-900 font-mono focus:outline-none">
                </div>
                <div>
                    <label class="text-slate-600 font-medium block mb-1">Bodenart (Haupt-Terroir):</label>
                    <input type="text" id="schlagBodenart" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-slate-900 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Sektion 2: Analytik & Zustand -->
        <div class="space-y-3">
            <h3 class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold border-b border-slate-100 pb-1.5">
                2. Terroir-Analytik
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-slate-600 font-medium block mb-1">Letzte offizielle Bodenprobe:</label>
                    <input type="date" id="schlagBodenprobe" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-slate-900 focus:outline-none">
                </div>
                <div class="bg-slate-50/50 border border-slate-100 p-3 rounded-lg flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-700 block">Zugeordnete Teilstücke</span>
                        <span class="text-[10px] text-slate-400 font-mono">Biologische Einheiten (Anlagen)</span>
                    </div>
                    <button type="button" onclick="navigiereZuAnlagen()" class="bg-slate-900 hover:bg-slate-800 text-white font-mono font-bold text-[10px] px-3 py-2 rounded-lg transition shadow-sm uppercase tracking-wider">
                        Anlagen-Struktur öffnen
                    </button>
                </div>
            </div>
        </div>

        <!-- Sichern-Riegel -->
        <div class="flex justify-end pt-4 border-t border-slate-100 gap-2">
            <a href="/schlaege" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 px-6 rounded-lg transition text-center uppercase tracking-wider">
                Abbrechen
            </a>
            <button type="submit" id="saveButton" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-lg transition shadow-sm cursor-pointer uppercase tracking-wider">
                Stammdaten versiegeln
            </button>
        </div>
    </form>
</main>
<script>
    let aktuellerSchlagId = 1;

    document.addEventListener("DOMContentLoaded", async () => {
        const urlParams = new URLSearchParams(window.location.search);
        aktuellerSchlagId = parseInt(urlParams.get('id')) || 1;
        
        await ladeSchlagDetails();
    });

    /**
     * Lädt die administrativen Stammdaten direkt aus der JSON-Schnittstelle
     */
    async function ladeSchlagDetails() {
        try {
            const response = await fetch(`/api/schlaege/${aktuellerSchlagId}`);
            const result = await response.json();
            
            if (result.success && result.data) {
                const s = result.data;
                document.getElementById('schlagName').value = s.name || '';
                document.getElementById('schlagFlaeche').value = s.flaeche_ha || '';
                document.getElementById('schlagBodenart').value = s.bodenart || '';
                document.getElementById('schlagBodenprobe').value = s.letzte_bodenprobe || '';
                
                // Setzt das Status-Badge auf aktiv
                const badge = document.getElementById('lockStatusBadge');
                badge.className = 'text-emerald-600 border border-emerald-200 px-3 py-1 rounded-full text-[10px] font-mono font-bold bg-emerald-50 tracking-wider uppercase';
                badge.innerText = 'Bereit zur Bearbeitung';
            } else {
                alert("🚨 Fehler: " + result.message);
            }
        }   
        catch (e) { 
            console.error("Kollaps beim Laden der Schlagdatei:", e); 
        }
    }

    /**
     * Sendet die überarbeiteten Stammdaten per PUT an die MariaDB zurück
     */
    async function speichereSchlagStammdaten(event) {
        if (event) event.preventDefault();
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const paket = {
            name: document.getElementById('schlagName').value,
            flaeche_ha: parseFloat(document.getElementById('schlagFlaeche').value) || 0,
            bodenart: document.getElementById('schlagBodenart').value,
            letzte_bodenprobe: document.getElementById('schlagBodenprobe').value || null
        };

        try {
            const response = await fetch(`/api/schlaege/${aktuellerSchlagId}`, {
                method: 'PUT',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json', 
                    'X-CSRF-TOKEN': token 
                },
                body: JSON.stringify(paket)
            });
            const result = await response.json();
            
            if (response.ok && result.success) {
                alert("System-Meldung: " + result.message);
                window.location.href = '/schlaege';
            } else {
                alert("🚨 Speichern abgelehnt: " + result.message);
            }
        } catch (e) { console.error("Netzwerkfehler beim Sichern:", e); }
    }

    function navigiereZuAnlagen() {
        window.location.href = `/anlagen-verwaltung?schlag_id=${aktuellerSchlagId}`;
    }
</script>
@endsection
