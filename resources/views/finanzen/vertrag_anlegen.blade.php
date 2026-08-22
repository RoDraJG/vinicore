@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto mt-6 p-6 bg-white border border-slate-200 rounded-2xl shadow-xl font-sans text-xs">
    <div class="border-b pb-4 border-slate-100 mb-5 flex justify-between items-center">
        <div>
            <h2 class="text-base font-bold text-slate-900 font-mono tracking-tight uppercase">🏢 Revisionssichere Vertragsanlage</h2>
            <p class="text-slate-500 mt-1">Der Vertrag und seine Flächen verbleiben im flüchtigen Speicher, bis du den Prozess aktiv versiegelst.</p>
        </div>
        <button onclick="loescheVertragsEntwurf()" class="bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 px-3 py-1.5 rounded-xl font-mono font-bold tracking-wider uppercase text-[10px] cursor-pointer">🗑️ Abbrechen</button>
    </div>

    <!-- 1. SCHRITT: DIE STAMMDATEN-EINGABE -->
    <div id="vinicoreStammdatenSektion" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
                <label class="block font-mono uppercase text-[10px] text-slate-400 tracking-wider font-bold">● Vertrags- / Urkunden-Nr.</label>
                <input type="text" id="v_nummer" class="w-full bg-white border border-slate-200 p-2.5 rounded-xl font-medium focus:border-emerald-500 text-xs" placeholder="z. B. Notar-Reg. 2026/A">
            </div>
            <div class="space-y-1">
                <label class="block font-mono uppercase text-[10px] text-slate-400 tracking-wider font-bold">● Vertrags-Typus</label>
                <select id="v_typ" class="w-full bg-white border border-slate-200 p-2.5 rounded-xl font-medium focus:border-emerald-500 text-xs cursor-pointer">
                    <option value="pacht_aufwand">🌿 Pachtvertrag (Ausgabe/Kreditor)</option>
                    <option value="kauf">💰 Notarieller Kaufvertrag (Anlagegut)</option>
                    <option value="pacht_ertrag">🚜 Verpachtungsvertrag (Einnahme/Debitor)</option>
                    <option value="tausch">🔄 Flurbereinigung / Flächentausch</option>
                    <option value="schenkung">🎁 Betriebsübergabe / Schenkung</option>
                </select>
            </div>
        </div>
        <div class="space-y-1">
            <label class="block font-mono uppercase text-[10px] text-slate-400 tracking-wider font-bold">● Name des Vertragspartners</label>
            <input type="text" id="v_partner" class="w-full bg-white border border-slate-200 p-2.5 rounded-xl font-medium focus:border-emerald-500 text-xs" placeholder="z. B. Erbengemeinschaft Müller, Klausen">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
                <label class="block font-mono uppercase text-[10px] text-slate-400 tracking-wider font-bold">● Finanzieller Gesamtwert (€ / Jahr)</label>
                <input type="number" step="0.01" id="v_wert" class="w-full bg-white border border-slate-200 p-2.5 rounded-xl font-medium focus:border-emerald-500 text-xs" placeholder="z. B. 1200.00">
            </div>
            <div class="space-y-1">
                <label class="block font-mono uppercase text-[10px] text-slate-400 tracking-wider font-bold">● Wirtschaftlich gültig ab</label>
                <input type="date" id="v_von" value="{{ date('Y-m-d') }}" class="w-full bg-white border border-slate-200 p-2.5 rounded-xl font-medium focus:border-emerald-500 text-xs">
            </div>
        </div>

        <div class="space-y-1 pb-4">
            <label class="block font-mono uppercase text-[10px] text-slate-400 tracking-wider font-bold">● Befristung bis (Optional)</label>
            <input type="date" id="v_bis" class="w-full bg-white border border-slate-200 p-2.5 rounded-xl font-medium focus:border-emerald-500 text-xs">
        </div>

        <button onclick="parkeVertragUndGeheZurKarte()" class="w-full bg-slate-900 hover:bg-slate-800 text-amber-400 font-mono font-bold py-3.5 px-4 rounded-xl shadow-md transition uppercase tracking-wider cursor-pointer text-center block">
            🗺️ Vertrags-Stammdaten einfrieren & Flächen auf Karte wählen
        </button>
    </div>
    <!-- 2. SCHRITT: DIE GEWÄHLTEN FLÄCHEN UND DIE ALLOKATIONS-VORSCHAU -->
    <div id="vinicoreFlaechenVorschauSektion" class="hidden mt-6 pt-6 border-t border-slate-100 space-y-4">
        <h4 class="font-bold text-slate-900 font-mono tracking-tight uppercase text-xs text-emerald-600">● Zugeordnete Katasterflächen & Allokations-Matrix</h4>
        
        <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 font-mono text-[9px] uppercase tracking-wider text-slate-400 font-bold">
                        <th class="p-3">Gemarkung / Lage</th>
                        <th class="p-3">Flur / Flurstück</th>
                        <th class="p-3 text-right">Amtliche m²</th>
                        <th class="p-3 text-right bg-emerald-50/50 text-emerald-700">Kalkulierter Einzelwert</th>
                    </tr>
                </thead>
                <tbody id="vinicoreParzellenVorschauTableBody" class="font-medium text-slate-700">
                    <!-- Wird dynamisch befüllt! -->
                </tbody>
            </table>
        </div>

        <div class="flex gap-3 pt-2">
            <button onclick="geheZurueckZurKarte()" class="w-1/3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono font-bold py-3 px-4 rounded-xl transition uppercase tracking-wider text-center cursor-pointer">
                🔄 Flächen anpassen
            </button>
            <button onclick="feuereFinalenDatenbankCommit()" class="w-2/3 bg-emerald-600 hover:bg-emerald-700 text-white font-mono font-bold py-3 px-4 rounded-xl shadow-md transition uppercase tracking-wider text-center cursor-pointer">
                🚀 Vertrag & Flächen aktiv in Datenbank speichern
            </button>
        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 💾 Liest bestehende Pufferdaten aus dem LocalStorage [source: 1.1.2]
    if (localStorage.getItem('vinicore_temp_vertrag_stammdaten')) {
        const v = JSON.parse(localStorage.getItem('vinicore_temp_vertrag_stammdaten'));
        document.getElementById('v_nummer').value = v.vertrag_nummer;
        document.getElementById('v_typ').value = v.typ;
        document.getElementById('v_partner').value = v.vertragspartner_name;
        document.getElementById('v_wert').value = v.gesamtwert;
        document.getElementById('v_von').value = v.gueltig_von;
        document.getElementById('v_bis').value = v.gueltig_bis;

        // Stammdatenfelder sperren für den laufenden Karten-Import
        blockiereStammdatenFelder(true);
    }

    // Zeigt die reaktive Allokations-Tabelle, wenn Flächen vorhanden sind [source: 1.1.2]
    if (localStorage.getItem('vinicore_temporaere_parzellen')) {
        berechneUndRendereVorschauMatrix();
    }
});

function blockiereStammdatenFelder(status) {
    const felder = ['v_nummer', 'v_typ', 'v_partner', 'v_wert', 'v_von', 'v_bis'];
    felder.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.disabled = status;
    });
}

function parkeVertragUndGeheZurKarte() {
    const num = document.getElementById('v_nummer').value.trim();
    if (!num) { alert("Bitte gib eine Vertrags- oder Urkundennummer ein!"); return; }

    const vertragObj = {
        vertrag_nummer: num,
        typ: document.getElementById('v_typ').value,
        vertragspartner_name: document.getElementById('v_partner').value.trim(),
        gesamtwert: parseFloat(document.getElementById('v_wert').value || 0),
        gueltig_von: document.getElementById('v_von').value,
        gueltig_bis: document.getElementById('v_bis').value
    };

    // Im LocalStorage einfrieren und mit ID 999999 zur Karte springen [source: 1.1.2]
    localStorage.setItem('vinicore_temp_vertrag_stammdaten', JSON.stringify(vertragObj));
    window.location.href = '/kataster/parzellen-karte?vertrag_id=999999';
}

function geheZurueckZurKarte() {
    window.location.href = '/kataster/parzellen-karte?vertrag_id=999999';
}

function loescheVertragsEntwurf() {
    if (confirm("Möchtest du den gesamten ungespeicherten Entwurf wirklich löschen?")) {
        localStorage.removeItem('vinicore_temp_vertrag_stammdaten');
        localStorage.removeItem('vinicore_temporaere_parzellen');
        window.location.reload();
    }
}

function berechneUndRendereVorschauMatrix() {
    const v = JSON.parse(localStorage.getItem('vinicore_temp_vertrag_stammdaten'));
    const parzellen = JSON.parse(localStorage.getItem('vinicore_temporaere_parzellen'));
    const tBody = document.getElementById('vinicoreParzellenVorschauTableBody');

    if (!v || !parzellen || !tBody) return;

    document.getElementById('vinicoreFlaechenVorschauSektion').classList.remove('hidden');

    let gesamtM2 = 0;
    parzellen.forEach(p => { gesamtM2 += parseInt(p.properties.amtliche_flaeche_m2 || p.properties.flaeche_m2 || 0); });

    let html = '';
    parzellen.forEach(p => {
        const props = p.properties;
        const m2 = parseInt(props.amtliche_flaeche_m2 || props.flaeche_m2 || 0);
        
        // 📈 KASKADEN-ALLOKATION: Rechnet flächenproportional (Modell A) direkt im RAM
        let einzelWert = 0;
        if (gesamtM2 > 0 && v.typ !== 'schenkung') {
            einzelWert = (v.gesamtwert * (m2 / gesamtM2));
        }

        html += `<tr class="border-b border-slate-100 hover:bg-slate-50/50 transition">
            <td class="p-3 font-bold text-slate-900">${props.gemarkung || 'Umland'}</td>
            <td class="p-3 font-mono text-slate-500">Flur ${props.flur} | Nr. ${props.flurstueck}</td>
            <td class="p-3 text-right font-mono font-bold">${m2.toLocaleString('de-DE')} m²</td>
            <td class="p-3 text-right text-emerald-600 font-mono font-extrabold bg-emerald-50/30">${einzelWert.toFixed(2)} €</td>
        </tr>`;
    });

    tBody.innerHTML = html;
}

async function feuereFinalenDatenbankCommit() {
    const v = localStorage.getItem('vinicore_temp_vertrag_stammdaten');
    const p = localStorage.getItem('vinicore_temporaere_parzellen');

    if (!v || !p) { alert("Fehler: Keine Daten im Speicher vorhanden."); return; }

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    try {
        const response = await fetch('/api/kataster/vertrag/final-versiegeln', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify({ vertrag: JSON.parse(v), parzellen: JSON.parse(p) })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            alert("Sensationell: " + data.message);
            localStorage.removeItem('vinicore_temp_vertrag_stammdaten');
            localStorage.removeItem('vinicore_temporaere_parzellen');
            
            // 🗺️ AUTOMATISCHER KARTEN-SPRUNG: Schickt den Winzer direkt zur fertigen Bestandskarte!
            window.location.href = '/kataster/parzellen-karte'; 
        }
 else {
            alert("Datenbank-Sperre: " + data.message);
        }
    } catch (e) {
        console.error(e);
        alert("Schnittstellen-Absturz beim finalen DB-Commit.");
    }
}
</script>
@endsection
