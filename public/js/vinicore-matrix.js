// ==========================================================================
// vinicore - Matrix-Planer GIS Engine — Version 4.6 (Core-Leitstand)
// ==========================================================================

let aktuelleZeilenNummer = 1;
let istBearbeitungsmodus = false;
let globalerZeilenCache = {};
let neuGenerierteZeilen = [];
let parzellenCacheListe = []; 
let globalerGisKontextMosaik = []; 
let globalerSchlagGassenbreiteCm = 200; 
let aktuellerSchlagId = 1; 

document.addEventListener("DOMContentLoaded", async () => {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        aktuellerSchlagId = urlParams.get('id') || 1;

        await ladeStammdaten();
        await ladeExistierendeZeilen();
        await aktiviereSichtbareZeile(aktuelleZeilenNummer);
        initSynonymSuche();
    } catch (e) { console.error("Fehler beim Starten der vinicore-Engine:", e); }
});

async function ladeExistierendeZeilen() {
    try {
        const responseGis = await fetch(`/api/matrix/${aktuellerSchlagId}/topologie`);
        const resultGis = await responseGis.json();
        
        if (resultGis.success && Array.isArray(resultGis.parzellen_mosaik)) {
            globalerGisKontextMosaik = resultGis.parzellen_mosaik;
        } else {
            globalerGisKontextMosaik = [];
        }

        const dropDown = document.getElementById('zeilenWechsler');
        if (dropDown && resultGis.success) {
            dropDown.innerHTML = '';
            let basisZeilen = resultGis.zeilen || [];
            
            if (basisZeilen.length === 0) {
                for (let z = 1; z <= 40; z++) basisZeilen.push(z);
            }

            let alleZeilen = [...new Set([...basisZeilen, ...neuGenerierteZeilen])];
            alleZeilen.sort((a, b) => a - b);
            
            alleZeilen.forEach(nr => {
                const opt = document.createElement('option');
                opt.value = nr; opt.innerText = `Weinberg-Zeile ${nr}`;
                if (nr === aktuelleZeilenNummer) opt.selected = true;
                dropDown.appendChild(opt);
            });
        }
    } catch (error) { console.error("🛑 Fehler in ladeExistierendeZeilen:", error); }
    finally {
        const loaderText = document.getElementById('gisLoaderText');
        if (loaderText) {
            loaderText.style.display = 'none';
            console.log("📡 vinicore-Loader: Ladebildschirm erfolgreich zwangs-deaktiviert.");
        }
    }
}
async function ladeStammdaten() {
    try {
        const response = await fetch(`/api/schlaege/daten/${aktuellerSchlagId}`);
        const result = await response.json();
        if (result.success && result.data) {
            const headerElement = document.getElementById('schlagNameHeader');
            if (headerElement) headerElement.innerText = `| ${result.data.name}`;
            globalerSchlagGassenbreiteCm = parseFloat(result.data.zeilenbreite_cm) || 200;
        }
    } catch (e) { console.error(e); }
}

async function aktiviereSichtbareZeile(zeileNr) {
    aktuelleZeilenNummer = parseInt(zeileNr);
    const katasterLayer = document.getElementById('katasterLayer');
    const zeilenLayer = document.getElementById('zeilenLayer');
    if (!katasterLayer || !zeilenLayer) return;

    try {
        if (!globalerZeilenCache[zeileNr]) {
            const response = await fetch(`/api/matrix/${aktuellerSchlagId}/${zeileNr}`);
            const result = await response.json();
            
            if (result.success && result.data && result.data.length > 0) {
                globalerZeilenCache[zeileNr] = result.data;
            } else {
                globalerZeilenCache[zeileNr] = [
                    { objekt_typ: 'anker', abstand_cm: 150, status: 'gesund' },
                    { objekt_typ: 'endpfahl', abstand_cm: 200, status: 'gesund' },
                    { objekt_typ: 'rebe', abstand_cm: 100, status: 'gesund' },
                    { objekt_typ: 'reihenpfahl', abstand_cm: 400, status: 'gesund' },
                    { objekt_typ: 'endpfahl', abstand_cm: 400, status: 'gesund' },
                    { objekt_typ: 'anker', abstand_cm: 150, status: 'gesund' }
                ];
            }
        }
        
        zeichneDrahtrahmenKette(globalerZeilenCache[zeileNr]);

        const aktuelleElemente = globalerZeilenCache[zeileNr] || [];
        let rebenZaehler = 0; let lückenZaehler = 0; let pfahlZaehler = 0; let gesamtLängeCm = 0;

        aktuelleElemente.forEach(el => {
            gesamtLängeCm += (parseInt(el.abstand_cm) || 0);
            const typ = String(el.objekt_typ || 'rebe').toLowerCase().trim();
            const status = String(el.status || 'gesund').toLowerCase().trim();

            if (typ === 'rebe') {
                if (status === 'fehlstelle' || status === 'tot') lückenZaehler++;
                else rebenZaehler++;
            } else if (typ === 'reihenpfahl' || typ === 'endpfahl') {
                pfahlZaehler++;
            }
        });

        if (document.getElementById('infoZeilenBadge')) document.getElementById('infoZeilenBadge').innerText = `REIHE: ${zeileNr}`;
        if (document.getElementById('infoZeilenLaenge')) document.getElementById('infoZeilenLaenge').innerText = `${(gesamtLängeCm / 100).toFixed(1)} m`;
        if (document.getElementById('infoAnzahlReben')) document.getElementById('infoAnzahlReben').innerText = rebenZaehler;
        if (document.getElementById('infoAnzahlFehlstellen')) document.getElementById('infoAnzahlFehlstellen').innerText = lückenZaehler;
        if (document.getElementById('infoAnzahlPfaehle')) document.getElementById('infoAnzahlPfaehle').innerText = pfahlZaehler;

    } catch (e) { console.error("🛑 Fehler bei Zeilenaktivierung:", e); }
}
function zeichneDrahtrahmenKette(elemente) {
    const canvas = document.getElementById('vinicoreGisCanvas');
    const katasterLayer = document.getElementById('katasterLayer');
    const zeilenLayer = document.getElementById('zeilenLayer');
    
    if (!canvas || !katasterLayer) return;
    katasterLayer.innerHTML = ''; if (zeilenLayer) zeilenLayer.innerHTML = '';

    const SF = 8; const xOffset = 100; const yOffset = 80; 
    const mosaik = globalerGisKontextMosaik || [];

    console.log(`📡 vinicore Matrix-Zünder: Parzellen im RAM = ${mosaik.length}`);
    if (mosaik.length === 0) {
        katasterLayer.innerHTML = `<text x="500" y="320" fill="#f43f5e" font-family="mono" font-size="12">⚠️ ERP-Daten-Vakuum: Keine Parzellen gekoppelt!</text>`;
        return; 
    }

    let alleXMeter = []; let alleYMeter = [];
    mosaik.forEach(p => {
        if (p.polygon_vektoren) {
            try {
                const punkte = typeof p.polygon_vektoren === 'string' ? JSON.parse(p.polygon_vektoren) : p.polygon_vektoren;
                if (Array.isArray(punkte)) punkte.forEach(pt => { alleXMeter.push(parseFloat(pt.x)); alleYMeter.push(parseFloat(pt.y)); });
            } catch (e) {}
        } else {
            const startX = parseFloat(p.x_start_meter) || 0; const startY = parseFloat(p.y_start_meter) || 0;
            alleXMeter.push(startX, startX + (parseFloat(p.breite_meter) || 50));
            alleYMeter.push(startY, startY + (parseFloat(p.hoehe_meter) || 80));
        }
    });

    const minGisX = Math.min(...alleXMeter); const minGisY = Math.min(...alleYMeter);
    const maxGisX = Math.max(...alleXMeter); const maxGisY = Math.max(...alleYMeter);
    canvas.setAttribute("viewBox", `0 0 ${Math.max(1400, (maxGisX - minGisX) * SF + 400)} 720`);

    let katasterHtmlString = "";
    mosaik.forEach(p => {
        let fColor = 'rgba(16, 185, 129, 0.06)'; let sColor = 'rgba(16, 185, 129, 0.35)';
        if (p.eigentum_status === 'eigentum' && p.nutzung_status === 'verpachtet') {
            fColor = 'rgba(239, 68, 68, 0.06)'; sColor = 'rgba(239, 68, 68, 0.35)';
        } else if (p.eigentum_status === 'fremdeigentum') {
            fColor = 'rgba(245, 158, 11, 0.05)'; sColor = 'rgba(245, 158, 11, 0.3)';
        }

        if (p.polygon_vektoren) {
            try {
                const punkteArray = typeof p.polygon_vektoren === 'string' ? JSON.parse(p.polygon_vektoren) : p.polygon_vektoren;
                if (Array.isArray(punkteArray)) {
                    let pts = punkteArray.map(pt => `${(pt.x - minGisX) * SF + xOffset},${(pt.y - minGisY) * SF + yOffset}`).join(' ');
                    katasterHtmlString += `<polygon points="${pts}" fill="${fColor}" stroke="${sColor}" stroke-width="1.2"></polygon>`;
                    return; 
                }
            } catch (jsonErr) { console.error(jsonErr); }
        }
        const xVal = ((parseFloat(p.x_start_meter) || 0) - minGisX) * SF + xOffset;
        const yVal = ((parseFloat(p.y_start_meter) || 0) - minGisY) * SF + yOffset;
        katasterHtmlString += `<rect x="${xVal}" y="${yVal}" width="${parseFloat(p.breite_meter)*SF}" height="${parseFloat(p.hoehe_meter)*SF}" fill="${fColor}" stroke="${sColor}" stroke-width="1" rx="4"></rect>`;
    });
    katasterLayer.innerHTML = katasterHtmlString;

    if (zeilenLayer) {
        let zeilenHtmlString = ""; let zeilenZaehlerGlobal = 1;
        mosaik.forEach(p => {
            const richtung = String(p.pflanz_richtung || 'horizontal').toLowerCase().trim();
            const gassenBreiteCm = globalerSchlagGassenbreiteCm || 200;
            const pxStart = ((parseFloat(p.x_start_meter) || 0) - minGisX) * SF + xOffset;
            const pyStart = ((parseFloat(p.y_start_meter) || 0) - minGisY) * SF + yOffset;
            const pBreite = parseFloat(p.breite_meter) * SF; const pHoehe = parseFloat(p.hoehe_meter) * SF;

            if (richtung === 'horizontal') {
                const reihenAnzahl = Math.floor((pHoehe / SF) / (gassenBreiteCm / 100)) || 10;
                const abstandPx = pHoehe / (reihenAnzahl + 1);
                for (let r = 1; r <= reihenAnzahl; r++) {
                    zeilenHtmlString += renderGisLinePro(zeilenZaehlerGlobal, pxStart, pyStart + (r * abstandPx), pxStart + pBreite, pyStart + (r * abstandPx), elemente);
                    zeilenZaehlerGlobal++;
                }
            } else {
                const reihenAnzahl = Math.floor((pBreite / SF) / (gassenBreiteCm / 100)) || 10;
                const abstandPx = pBreite / (reihenAnzahl + 1);
                for (let r = 1; r <= reihenAnzahl; r++) {
                    zeilenHtmlString += renderGisLinePro(zeilenZaehlerGlobal, pxStart + (r * abstandPx), pyStart, pxStart + (r * abstandPx), pyStart + pHoehe, elemente);
                    zeilenZaehlerGlobal++;
                }
            }
        });
        zeilenLayer.innerHTML = zeilenHtmlString;
    }
}
function renderGisLinePro(index, x1, y1, x2, y2, elemente) {
    const isAktiv = (index === aktuelleZeilenNummer);
    const strokeColor = isAktiv ? '#f8fafc' : 'rgba(71, 85, 105, 0.4)'; 
    let html = `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="${strokeColor}" stroke-width="${isAktiv ? '2' : '0.75'}" stroke-dasharray="${isAktiv ? '0' : '4,4'}" style="cursor: pointer;" onclick="aktiviereSichtbareZeile(${index})"></line>`;
    
    if (isAktiv && Array.isArray(elemente)) {
        const pixelSchritt = 32; 
        elemente.forEach((el, idx) => {
            const isVertikal = (x1 === x2);
            const stockX = isVertikal ? x1 : x1 + (idx * pixelSchritt) + 20;
            const stockY = isVertikal ? y1 + (idx * pixelSchritt) + 20 : y1;
            const typ = el.objekt_typ || 'rebe';
            
            if (typ === 'rebe') {
                let rColor = '#10b981'; const statusClean = String(el.status || 'gesund').toLowerCase().trim();
                if (statusClean === 'fehlstelle' || statusClean === 'tot') rColor = '#f43f5e'; 
                if (statusClean === 'nachgepflanzt') rColor = '#3b82f6'; 
                html += `<circle cx="${stockX}" cy="${stockY}" r="4" fill="${rColor}" stroke="#0f172a" stroke-width="1"></circle>`;
            } else if (typ === 'reihenpfahl' || typ === 'endpfahl') {
                const size = typ === 'endpfahl' ? 6 : 4;
                html += `<rect x="${stockX - size/2}" y="${stockY - size/2}" width="${size}" height="${size}" fill="#94a3b8" stroke="#334155" stroke-width="0.5"></rect>`;
            } else if (typ === 'anker') {
                html += `<circle cx="${stockX}" cy="${stockY}" r="2.5" fill="#e2e8f0" stroke="#475569" stroke-width="0.5"></circle>`;
            }
            if (idx % 2 === 0) {
                const tx = isVertikal ? stockX + 10 : stockX; const ty = isVertikal ? stockY + 3 : stockY + 14;
                html += `<text x="${tx}" y="${ty}" fill="#64748b" font-family="sans-serif" font-size="7" text-anchor="${isVertikal ? 'start' : 'middle'}">${el.abstand_cm || 100}</text>`;
            }
        });
    }
    return html;
}

function starteBearbeitungsmodus() {
    istBearbeitungsmodus = true;
    document.getElementById('editStatusBadge').innerText = "STATUS: PLANUNGS-MODUS (RAM)";
    document.getElementById('editStatusBadge').className = "text-emerald-400 border border-emerald-700 px-2 py-1 rounded text-[10px] font-mono uppercase tracking-wider font-bold bg-emerald-950/30";
    document.getElementById('toggleEditBtn').classList.add('hidden');
    document.getElementById('saveEditBtn').classList.remove('hidden');
    document.getElementById('cancelEditBtn').classList.remove('hidden');
    zeichneDrahtrahmenKette(globalerZeilenCache[aktuelleZeilenNummer]);
}

function beendeBearbeitungsmodusUI() {
    istBearbeitungsmodus = false;
    document.getElementById('editStatusBadge').innerText = "STATUS: Read-Only";
    document.getElementById('editStatusBadge').className = "text-slate-400 border border-slate-800 px-2 py-1 rounded text-[10px] font-mono uppercase tracking-wider font-bold bg-slate-900";
    document.getElementById('toggleEditBtn').className = "bg-slate-900 hover:bg-slate-800 text-white font-bold py-2 px-4 rounded-lg transition text-xs uppercase tracking-wider cursor-pointer shadow-sm";
    document.getElementById('saveEditBtn').classList.add('hidden');
    document.getElementById('cancelEditBtn').classList.add('hidden');
}

function bearbeitungAbbrechenOhneSpeichern() {
    if (!confirm("Änderungen verwerfen?")) return;
    globalerZeilenCache[aktuelleZeilenNummer] = null; beendeBearbeitungsmodusUI(); aktiviereSichtbareZeile(aktuelleZeilenNummer);
}

function markiereZeileAlsGeaendert() { if (!istBearbeitungsmodus) { starteBearbeitungsmodus(); } }
function initSynonymSuche() { console.log("vinicore Synonym-Engine geladen."); }

async function finaleBearbeitungAbschliessen() {
    if (!confirm("Änderungen final in der MariaDB speichern?")) return;
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const parzellenZuordnungMap = {}; let ermittelteParzelleUuid = null;

    if (globalerGisKontextMosaik.length > 0) {
        ermittelteParzelleUuid = globalerGisKontextMosaik[0].parzelle_uuid;
    }

    parzellenZuordnungMap[aktuelleZeilenNummer] = ermittelteParzelleUuid;
    try {
        const response = await fetch('/api/matrix/massenspeichern', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify({ schlag_id: aktuellerSchlagId, matrix_daten: globalerZeilenCache, parzellen_zuordnung: parzellenZuordnungMap })
        });
        const result = await response.json(); alert("Meldung: " + result.message);
        globalerZeilenCache = {}; beendeBearbeitungsmodusUI(); await ladeExistierendeZeilen(); await aktiviereSichtbareZeile(aktuelleZeilenNummer);
    } catch (error) { console.error("Fehler beim Massenspeichern:", error); }
}
