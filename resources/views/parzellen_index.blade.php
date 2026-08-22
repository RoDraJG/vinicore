@extends('layouts.app')

@section('content')
<!-- Clean SaaS Header -->
<header class="flex justify-between items-center bg-bg-surface px-6 py-4 rounded-xl border border-border-main shadow-xs">
    <div>
        <h1 class="text-xl font-bold tracking-tight text-text-main">
            vinicore <span class="text-text-muted font-normal text-sm">| Katasterverwaltung</span>
        </h1>
        <a href="/schlaege" class="text-[10px] text-text-muted hover:text-accent-brand font-mono uppercase tracking-wider block mt-1 transition">&larr; Zurück zum Cockpit</a>
    </div>
    <span class="text-text-muted border border-border-main px-3 py-1 rounded-full text-[10px] font-mono font-bold bg-bg-base tracking-wider">
        MODUL: LIEGENSCHAFTS-KATASTER
    </span>
</header>

<main class="bg-bg-surface p-6 rounded-xl border border-border-main shadow-xs mt-4">
    <form id="parzelleForm" onsubmit="speichereNeueParzelle(event)" class="space-y-6 text-xs">
        <!-- Sektion: Amtliche Schlüsselmerkmale -->
        <div class="space-y-3">
            <h3 class="text-[10px] font-mono uppercase tracking-wider text-text-muted font-bold border-b border-border-main pb-1.5 mb-3">
                Identifikation nach ALKIS (5-fach-Schlüssel)
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-text-muted font-medium block mb-1">Gemeinde (z. B. Burgen):</label>
                    <input type="text" id="gemeinde" required class="w-full bg-bg-input border border-border-main rounded-lg p-2.5 text-text-main font-bold focus:outline-none focus:ring-2 focus:ring-accent-brand transition">
                </div>
                <div>
                    <label class="text-text-muted font-medium block mb-1">Gemarkungsschlüssel (6-stellig, z. B. 072425):</label>
                    <input type="text" id="gemarkung_schluessel" required class="w-full bg-bg-input border border-border-main rounded-lg p-2.5 text-text-main font-bold focus:outline-none focus:ring-2 focus:ring-accent-brand transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 pt-2">
                <div>
                    <label class="text-text-muted font-medium block mb-1">Flur (reine Zahl, z. B. 5):</label>
                    <input type="text" id="flur" required class="w-full bg-bg-input border border-border-main rounded-lg p-2.5 text-text-main font-bold focus:outline-none focus:ring-2 focus:ring-accent-brand transition">
                </div>
                <div>
                    <label class="text-text-muted font-medium block mb-1">Flurstück Zähler (z. B. 115):</label>
                    <input type="text" id="flurstueck_zaehler" required class="w-full bg-bg-input border border-border-main rounded-lg p-2.5 text-text-main font-bold focus:outline-none focus:ring-2 focus:ring-accent-brand transition">
                </div>
                <div>
                    <label class="text-text-muted font-medium block mb-1">Flurstück Nenner (optional):</label>
                    <input type="text" id="flurstueck_nenner" class="w-full bg-bg-input border border-border-main rounded-lg p-2.5 text-text-main font-bold focus:outline-none focus:ring-2 focus:ring-accent-brand transition">
                </div>
                <!-- KORREKTUR: Die rechtlich differenzierte Eigentums- und Nutzungsmatrix -->
                <div>
                    <label class="text-text-muted font-medium block mb-1">Eigentums-Verhältnis:</label>
                    <select id="eigentum_status" onchange="harmonisiereNutzungsStatus()" class="w-full bg-bg-input border border-border-main rounded-lg p-2.5 text-text-main focus:outline-none focus:ring-2 focus:ring-accent-brand transition cursor-pointer font-bold">
                        <option value="eigentum">🏢 Eigenes Eigentum</option>
                        <option value="fremdeigentum">👤 Fremdeigentum</option>
                    </select>
                </div>
                <div>
                    <label class="text-text-muted font-medium block mb-1">Bewirtschaftung / Nutzung:</label>
                    <select id="nutzung_status" class="w-full bg-bg-input border border-border-main rounded-lg p-2.5 text-text-main focus:outline-none focus:ring-2 focus:ring-accent-brand transition cursor-pointer font-bold">
                        <option value="eigenbetrieb">🚜 Eigenbewirtschaftung</option>
                        <option value="verpachtet">📜 Verpachtet (Fremdbetrieb)</option>
                        <option value="zugepachtet" disabled>🍇 Zugepachtet (Pachtland)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Absende-Knopf -->
        <div class="flex justify-end pt-4 border-t border-border-main">
            <button type="submit" class="bg-accent-brand hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-lg transition shadow-sm cursor-pointer text-xs uppercase tracking-wider">
                Flurstück im System registrieren
            </button>
        </div>
    </form>
</main>
<!-- Sektion: Das neue vinicore Tab-System (Historisierter Liegenschafts-Katalog) -->
<section class="bg-bg-surface p-6 rounded-xl border border-border-main shadow-xs mt-4">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-border-main pb-3 mb-4 gap-3">
        <!-- Die interaktiven Umschalt-Tabs -->
        <div class="flex gap-2 text-[10px] font-mono font-bold uppercase tracking-wider">
            <button id="tabAktivBtn" onclick="wechsleKatasterTab('aktiv')" class="px-4 py-2 rounded-lg bg-accent-brand text-white shadow-xs transition">
                🟢 Aktiver Bestand
            </button>
            <button id="tabHistorischBtn" onclick="wechsleKatasterTab('historisch')" class="px-4 py-2 rounded-lg bg-bg-base border border-border-main text-text-muted hover:text-text-main transition">
                📜 Historischer Bestand
            </button>
        </div>
        <span class="text-[10px] font-mono bg-bg-base border border-border-main px-2 py-0.5 rounded text-text-muted font-bold" id="parzellenZaehlerBadge">
            Bestand: 0 Flurstücke
        </span>
    </div>

    <!-- Die universelle Kataster-Tabelle -->
    <div class="overflow-x-auto text-[11px]">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="text-text-muted border-b border-border-main font-mono text-[9px] uppercase tracking-wider">
                    <th class="pb-2.5 font-bold">Gemeinde / Ort</th>
                    <th class="pb-2.5 font-bold">ALKIS-Schlüssel</th>
                    <th class="pb-2.5 font-bold">Flur</th>
                    <th class="pb-2.5 font-bold">Flurstück</th>
                    <th class="pb-2.5 font-bold">Rechte-Matrix</th>
                    <th class="pb-2.5 font-bold text-right">Amtliche Fläche</th>
                    <th class="pb-2.5 font-bold text-right pr-2" id="tabelleAktionenHeader">Aktionen</th>
                </tr>
            </thead>
            <tbody id="parzellenTabelleBody" class="divide-y divide-border-main text-text-main font-medium">
                <tr>
                    <td colspan="7" class="py-4 text-center text-text-muted font-mono animate-pulse">Lade ALKIS-Bestand aus MariaDB...</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>
<script>
    let aktuellesTab = 'aktiv';
    let alleGeladenenParzellen = [];
    let bearbeitungsUuid = null; 

    document.addEventListener("DOMContentLoaded", () => {
        ladeKatasterBestandstabelle();
    });

    // Schaltet im Frontend blitzschnell zwischen lebendem Bestand und Archiv um
    function wechsleKatasterTab(zielTab) {
        aktuellesTab = zielTab;
        const aktivBtn = document.getElementById('tabAktivBtn');
        const histBtn = document.getElementById('tabHistorischBtn');
        
        if (zielTab === 'aktiv') {
            aktivBtn.className = "px-4 py-2 rounded-lg bg-accent-brand text-white shadow-xs transition";
            histBtn.className = "px-4 py-2 rounded-lg bg-bg-base border border-border-main text-text-muted hover:text-text-main transition";
            document.getElementById('tabelleAktionenHeader').style.display = '';
        } else {
            histBtn.className = "px-4 py-2 rounded-lg bg-accent-brand text-white shadow-xs transition";
            aktivBtn.className = "px-4 py-2 rounded-lg bg-bg-base border border-border-main text-text-muted hover:text-text-main transition";
            document.getElementById('tabelleAktionenHeader').style.display = 'none'; 
        }
        renderTableRows();
    }

    async function ladeKatasterBestandstabelle() {
        try {
            // KORREKTUR: Zieht hier die All-Schnittstelle inklusive Historie für die Tabs
            const response = await fetch('/api/kataster/parzellen/alle');
            const result = await response.json();
            
            if (result.success && Array.isArray(result.data)) {
                alleGeladenenParzellen = result.data;
                renderTableRows();
            }
        } catch (e) { console.error("Kritischer Kataster-Ladefehler:", e); }
    }


    function renderTableRows() {
        const tbody = document.getElementById('parzellenTabelleBody');
        if (!tbody) return;

        // FILTER-ENGINE: Trennt scharf zwischen Aktiv und Historisch (gueltig_bis)
        const gefilterteDaten = alleGeladenenParzellen.filter(p => {
            if (aktuellesTab === 'aktiv') return p.gueltig_bis === null;
            return p.gueltig_bis !== null;
        });

        document.getElementById('parzellenZaehlerBadge').innerText = `Bestand: ${gefilterteDaten.length} Flurstücke`;

        if (gefilterteDaten.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="py-4 text-center text-text-muted font-mono">Keine Einträge in diesem Register vorhanden.</td></tr>`;
            return;
        }

        let html = "";
        gefilterteDaten.forEach(p => {
            const nenner = p.flurstueck_nenner ? `/${p.flurstueck_nenner}` : '';
            const flaeche = (p.amtliche_flaeche_m2 && parseFloat(p.amtliche_flaeche_m2) > 0) 
                ? `${parseFloat(p.amtliche_flaeche_m2).toLocaleString('de-DE')} m²` 
                : '0 m² (GeoJSON offen)';
            
            const ortsName = p.gemeinde ? p.gemeinde : (p.gemarkung || 'Unbekannt');
            const flurName = p.flurname_lage ? p.flurname_lage : 'Wird durch GeoJSON ermittelt';

            const eigText = p.eigentum_status === 'eigentum' ? 'Eigentum' : 'Fremdland';
            let nutzBadge = '🟢 Aktiv';
            if (p.nutzung_status === 'verpachtet') nutzBadge = '🔴 Verpachtet';
            if (p.nutzung_status === 'zugepachtet') nutzBadge = '🟡 Zugepachtet';

            let aktionsHtml = '';
            if (aktuellesTab === 'aktiv') {
                aktionsHtml = `
                    <td class="py-2 text-right space-x-1 whitespace-nowrap">
                        <button type="button" onclick="ladeInEingabeMaske('${p.parzelle_uuid}')" class="bg-bg-base border border-border-main hover:bg-slate-100 font-bold px-2 py-1 rounded transition cursor-pointer text-[10px]">📝 Edit</button>
                        <button type="button" onclick="historischArchivieren('${p.parzelle_uuid}')" class="bg-red-50 hover:bg-red-500 text-red-600 hover:text-white border border-red-200 font-bold px-2 py-1 rounded transition cursor-pointer text-[10px]">🗑️ Verkauf</button>
                    </td>`;
            } else {
                // Konvertiert das ISO-Datum lesbar für den Anwender
                const archivDatum = p.gueltig_bis ? p.gueltig_bis.substring(0, 10) : '--';
                aktionsHtml = `<td class="py-2 text-right text-text-muted font-mono italic text-[9px] pr-2">Archiviert am ${archivDatum}</td>`;
            }

            html += `
                <tr class="hover:bg-bg-base/40 border-b border-border-main transition-colors">
                    <td class="py-3 font-bold text-text-main">${ortsName}</td>
                    <td class="py-3 font-mono text-text-muted">${p.gemarkung_schluessel || '--'}</td>
                    <td class="py-3 font-mono">Flur ${p.flur || '?'}</td>
                    <td class="py-3 font-mono font-bold text-accent-brand">${p.flurstueck_zaehler}${nenner}</td>
                    <td class="py-3 text-text-muted font-medium">${eigText} (${nutzBadge})</td>
                    <td class="py-3 text-right font-mono font-bold text-text-main">${flaeche}</td>
                    ${aktionsHtml}
                </tr>`;
        });
        tbody.innerHTML = html;
    }
    // Lädt die angeklickte Parzelle fehlerfrei zurück nach oben in die Eingabefelder
    function ladeInEingabeMaske(uuid) {
        const p = alleGeladenenParzellen.find(item => item.parzelle_uuid === uuid);
        if (!p) return;

        bearbeitungsUuid = p.parzelle_uuid; 
        
        document.getElementById('gemeinde').value = p.gemeinde || '';
        document.getElementById('gemarkung_schluessel').value = p.gemarkung_schluessel || '';
        document.getElementById('flur').value = p.flur || '';
        document.getElementById('flurstueck_zaehler').value = p.flurstueck_zaehler || '';
        document.getElementById('flurstueck_nenner').value = p.flurstueck_nenner || '';
        document.getElementById('eigentum_status').value = p.eigentum_status || 'eigentum';
        
        harmonisiereNutzungsStatus();
        document.getElementById('nutzung_status').value = p.nutzung_status || 'eigenbetrieb';
        
        // Transformiert das Absende-Layout in das edle Bernstein-Design für Updates
        const btn = document.querySelector('#parzelleForm button[type="submit"]');
        btn.innerText = "💾 Änderungen einfrieren (Update)";
        btn.className = "bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-6 rounded-lg transition shadow-sm cursor-pointer text-xs uppercase tracking-wider";
    }

    async function historischArchivieren(uuid) {
        if (!confirm("Möchtest du dieses Flurstück wirklich historisch archivieren/verkaufen? Alte Bilanzen bleiben versiegelt.")) return;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        try {
            const response = await fetch(`/api/kataster/parzellen/archivieren`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ parzelle_uuid: uuid })
            });

            if (response.ok) {
                alert("Kataster-Meldung: Flurstück erfolgreich archiviert!");
                await ladeKatasterBestandstabelle();
            }
        } catch (e) { console.error("Archivierungsfehler:", e); }
    }

    function harmonisiereNutzungsStatus() {
        const eigentum = document.getElementById('eigentum_status').value;
        const nutzung = document.getElementById('nutzung_status');
        if (eigentum === 'fremdeigemtum' || eigentum === 'fremdeigentum') {
            nutzung.value = 'zugepachtet';
            nutzung.options[0].disabled = true;  // Eigenbetrieb aus
            nutzung.options[1].disabled = true;  // Verpachtet aus
            nutzung.options[2].disabled = false; // Zugepachtet an
        } else {
            if (nutzung.value === 'zugepachtet') nutzung.value = 'eigenbetrieb';
            nutzung.options[0].disabled = false;
            nutzung.options[1].disabled = false;
            nutzung.options[2].disabled = true;
        }
    }

    async function speichereNeueParzelle(event) {
        if (event) event.preventDefault();
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const payload = {
            parzelle_uuid: bearbeitungsUuid, 
            gemeinde: document.getElementById('gemeinde').value.trim(),
            gemarkung_schluessel: document.getElementById('gemarkung_schluessel').value.trim(),
            flur: document.getElementById('flur').value.trim(),
            flurstueck_zaehler: document.getElementById('flurstueck_zaehler').value.trim(),
            flurstueck_nenner: document.getElementById('flurstueck_nenner').value.trim() || null,
            eigentum_status: document.getElementById('eigentum_status').value,
            nutzung_status: document.getElementById('nutzung_status').value
        };
        // ==========================================================================
        // GEGEN-CHECK (FRONTEND): Verhindert das Absenden bei Duplikaten im RAM
        // ==========================================================================
        if (!bearbeitungsUuid) {
            const duplikatGefunden = alleGeladenenParzellen.some(p => 
                p.gueltig_bis === null &&
                p.gemeinde.toLowerCase().trim() === payload.gemeinde.toLowerCase() &&
                p.flur.trim() === payload.flur &&
                p.flurstueck_zaehler.trim() === payload.flurstueck_zaehler &&
                (p.flurstueck_nenner || null) === payload.flurstueck_nenner
            );

            if (duplikatGefunden) {
                alert(`⚠️ Eingabe-Sperre:\nDas Flurstück ${payload.flurstueck_zaehler} ist in dieser Gemeinde/Flur bereits im aktiven Bestand vorhanden!`);
                return; // Bricht das Absenden sofort ab!
            }
        }

        try {
            const response = await fetch('/api/kataster/parzellen', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token 
                },
                body: JSON.stringify(payload)
            });

            if (response.ok) {
                alert(bearbeitungsUuid ? "Änderungen erfolgreich versiegelt!" : "Flurstück erfolgreich registriert!");
                bearbeitungsUuid = null;
                document.getElementById('parzelleForm').reset();
                
                const btn = document.querySelector('#parzelleForm button[type="submit"]');
                btn.innerText = "Flurstück im System registrieren";
                btn.className = "bg-accent-brand hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-lg transition shadow-sm cursor-pointer text-xs uppercase tracking-wider";
                
                await ladeKatasterBestandstabelle();
            } else {
                // ==========================================================================
                // KORREKTUR: Liest die exakte 422-Meldung aus dem Controller aus!
                // ==========================================================================
                const fehlerPaket = await response.json();
                alert(fehlerPaket.message || "🚨 Ein unbekannter Validierungsfehler ist aufgetreten.");
            }
        } catch (e) { 
            console.error("Kritischer Speicherfehler:", e); 
        }

    }
</script>

