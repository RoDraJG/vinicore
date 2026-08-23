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
                <input type="text" id="v_nummer" value="{{ session('vinicore_schwebe_vertrag.vertrag_nummer') }}" class="w-full bg-white border border-slate-200 p-2.5 rounded-xl font-medium focus:border-emerald-500 text-xs" placeholder="z. B. Notar-Reg. 2026/A">
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
            <input type="text" id="v_partner" value="{{ session('vinicore_schwebe_vertrag.vertragspartner_name') }}" class="w-full bg-white border border-slate-200 p-2.5 rounded-xl font-medium focus:border-emerald-500 text-xs" placeholder="z. B. Erbengemeinschaft Müller, Klausen">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
                <label class="block font-mono uppercase text-[10px] text-slate-400 tracking-wider font-bold">● Finanzieller Gesamtwert (€ / Jahr)</label>
                <input type="number" step="0.01" id="v_wert" value="{{ session('vinicore_schwebe_vertrag.gesamtwert') }}" class="w-full bg-white border border-slate-200 p-2.5 rounded-xl font-medium focus:border-emerald-500 text-xs" placeholder="z. B. 1200.00">
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
            🗺️ Vertrags-Stammdaten einfrieren &amp; Flächen auf Karte wählen
        </button>
    </div>
    <!-- 2. SCHRITT: DIE GEWÄHLTEN FLÄCHEN UND DIE ALLOKATIONS-VORSCHAU -->
    <div id="vinicoreFlaechenVorschauSektion" class="hidden mt-6 pt-6 border-t border-slate-100 space-y-4">
        <h4 class="font-bold text-slate-900 font-mono tracking-tight uppercase text-xs text-emerald-600">● Zugeordnete Katasterflächen &amp; Allokations-Matrix</h4>
        
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
                🚀 Vertrag &amp; Flächen aktiv in Database speichern
            </button>
        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 🚀 THE BLADE-SAFE SHIELD: Nutzt Laravels unbestechliche Direktiven statt String-Typisierungen
    @if(session()->has('vinicore_schwebe_vertrag'))
        const hatSession = true;
    @else
        const hatSession = false;
    @endif

    // Sichert ab, dass das Formular bei vorhandenem Sitzungsspeicher die Felder sperrt
    if (hatSession || localStorage.getItem('vinicore_temp_vertrag_stammdaten')) {
        const vNum = document.getElementById('v_nummer');
        const vPartner = document.getElementById('v_partner');
        const vWert = document.getElementById('v_wert');
        
        // Verhindert das Überschreiben, falls Daten in der Server-Session liegen
        if (vNum) vNum.value = "{{ session('vinicore_schwebe_vertrag.vertrag_nummer') }}";
        if (vPartner) vPartner.value = "{{ session('vinicore_schwebe_vertrag.vertragspartner_name') }}";
        if (vWert) vWert.value = "{{ session('vinicore_schwebe_vertrag.gesamtwert') }}";

        blockiereStammdatenFelder(true);
    }

    // Zeigt die reaktive Allokations-Tabelle, wenn Flächen vorhanden sind
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


/**
 * 🗺️ VERTRAGS-STAMMDATEN IN SESSION EINFRIEREN
 * Schiebt die Kopfdaten diebstahlsicher in die PHP-Sitzung und springt zur Karte!
 */
function parkeVertragUndGeheZurKarte() {
    const num = document.getElementById('v_nummer').value.trim();
    if (!num) { alert("Bitte gib eine Vertrags- oder Urkundennummer ein!"); return; }

    const payload = {
        vertrag_nummer: num,
        typ: document.getElementById('v_typ').value,
        vertragspartner_name: document.getElementById('v_partner').value.trim(),
        gesamtwert: parseFloat(document.getElementById('v_wert').value || 0),
        gueltig_von: document.getElementById('v_von').value,
        gueltig_bis: document.getElementById('v_bis').value
    };

    // 🚀 SERVER-SESSION-TRANSIT: Schreibt den Entwurf direkt in das Server-RAM
    fetch('/api/kataster/vertrag/session-parken', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json', 
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            // Erst nach erfolgreicher Server-Sicherung wechseln wir zur Karte
            window.location.href = '/kataster/parzellen-karte';
        } else {
            alert("Sitzungs-Fehler: " + res.message);
        }
    })
    .catch(err => console.error("Session-API-Absturz:", err));
}

    function geheZurueckZurKarte() {
        window.location.href = '/kataster/parzellen-karte';
    }

    function loescheVertragsEntwurf() {
        if (confirm("Möchtest du den gesamten ungespeicherten Entwurf wirklich löschen?")) {
            localStorage.removeItem('vinicore_temp_vertrag_stammdaten');
            localStorage.removeItem('vinicore_temporaere_parzellen');
            window.location.reload();
        }
    }

    /**
     * 📊 REAKTIVE ALLOKATIONS-VORSCHAU (GEOJSON REINHEIT)
     * Liest das standardkonforme GeoJSON-Format von der Karte fehlerfrei aus!
     */
    function berechneUndRendereVorschauMatrix() {
        const parzellen = JSON.parse(localStorage.getItem('vinicore_temporaere_parzellen'));
        const tBody = document.getElementById('vinicoreParzellenVorschauTableBody');
        const gesamtWert = parseFloat(document.getElementById('v_wert').value || 0);
        const vTyp = document.getElementById('v_typ').value;

        if (!parzellen || !tBody) return;

        // Schaltet den umschließenden Container unbarmherzig von hidden auf sichtbar!
        if (parzellen.length > 0) {
            document.getElementById('vinicoreFlaechenVorschauSektion').classList.remove('hidden');
        }

        let gesamtM2 = 0;
        // 🎯 GEOJSON-READ: Liest die m² direkt aus den Properties des Features
        parzellen.forEach(p => {
            if (p && p.properties) {
                gesamtM2 += parseInt(p.properties.amtliche_flaeche_m2 || p.properties.flaeche_m2 || 0);
            }
        });

        if (parzellen.length === 0) {
            tBody.innerHTML = `<tr id="vinicoreEmptyRow"><td colspan="4" class="p-8 text-center text-slate-400 font-medium italic">Noch keine Umlandparzellen auf der Karte ausgewählt.</td></tr>`;
            return;
        }

        let html = '';
        parzellen.forEach(p => {
            if (!p || !p.properties) return;
            const props = p.properties; // Unzerstörbarer Zugriff auf den GeoJSON-Unterbaum!
            
            const m2 = parseInt(props.amtliche_flaeche_m2 || props.flaeche_m2 || 0);
            
            let einzelWert = 0;
            if (gesamtM2 > 0 && vTyp !== 'schenkung') {
                einzelWert = (gesamtWert * (m2 / gesamtM2));
            }

            // Sichert, dass kombinierte ALKIS-Strings oder Einzelfelder fehlerfrei formatiert werden
            let fNennerDisplay = (props.flurstueck || props.flurstueck_zaehler || '0').toString();
            const nRaw = (props.flurstueck_nenner || props.nenner || '').toString().trim();
            
            if (!fNennerDisplay.includes('/') && nRaw !== '' && nRaw !== '0' && nRaw !== 'null') {
                fNennerDisplay += '/' + nRaw;
            }

            html += `<tr class="border-b border-slate-100 hover:bg-slate-50/50 transition">
                <td class="p-3 font-bold text-slate-900">${props.gemarkung || 'Umland'}</td>
                <td class="p-3 font-mono text-slate-500">Flur ${props.flur || 1} | Nr. ${fNennerDisplay}</td>
                <td class="p-3 text-right font-mono font-bold">${m2.toLocaleString('de-DE')} m²</td>
                <td class="p-3 text-right text-blue-600 font-mono font-extrabold bg-blue-50/30">${einzelWert.toFixed(2)} €</td>
            </tr>`;
        });

        tBody.innerHTML = html;
    }
/**
 * 🚀 VALIDIERUNGS-KONFORMER DATENBANK-COMMIT
 * Packt die Stammdaten und die GeoJSON-Flächen fehlerfrei in den POST-Payload,
 * um die 422er-Validierungssperre im Backend unzerstörbar zu passieren!
 */
async function feuereFinalenDatenbankCommit() {
    // 1. Holt die parziellen Vektordaten aus dem Korb
    const pRaw = localStorage.getItem('vinicore_temporaere_parzellen');
    if (!pRaw || JSON.parse(pRaw).length === 0) {
        alert("⚠️ WARENKORB LEER:\nEs wurden keine Parzellen von der Landkarte mitgebracht. Springe noch einmal auf die Karte und wähle Flächen aus!");
        return;
    }

    // 2. 🎯 THE VALIDATOR-KEY: Baut das Stammdaten-Objekt live aus den Formularfeldern auf,
    // damit der Backend-Validator 'vertrag => required' zu 100% bedient wird!
    const vertragObj = {
        vertrag_nummer: document.getElementById('v_nummer').value.trim(),
        typ: document.getElementById('v_typ').value,
        vertragspartner_name: document.getElementById('v_partner').value.trim(),
        gesamtwert: parseFloat(document.getElementById('v_wert').value || 0),
        gueltig_von: document.getElementById('v_von').value,
        gueltig_bis: document.getElementById('v_bis').value
    };

    if (!vertragObj.vertrag_nummer) {
        alert("Bitte gib eine Vertrags- oder Urkundennummer ein!");
        return;
    }

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    try {
        const response = await fetch('/api/kataster/vertrag/final-versiegeln', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'Accept': 'application/json', 
                'X-CSRF-TOKEN': token 
            },
            // Übergibt beide erwarteten Hauptschlüssel fehlerfrei an dein Laravel-Backend!
            body: JSON.stringify({ 
                vertrag: vertragObj, // 🧱 Bedient 'vertrag => required'
                parzellen: JSON.parse(pRaw) // 🧱 Bedient 'parzellen => required'
            })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            alert("💥 Sensationell: " + data.message);
            
            // Löscht den Speicher erst nach dem garantierten Datenbank-Erfolg
            localStorage.removeItem('vinicore_temp_vertrag_stammdaten');
            localStorage.removeItem('vinicore_temporaere_parzellen');
            
            // Schickt den Winzer direkt zur fertigen Bestandskarte
            window.location.href = '/kataster/parzellen-karte'; 
        } else {
            alert("❌ Datenbank-Sperre: " + (data.message || "Unbekannter Fehler im Controller"));
        }
    } catch (e) {
        console.error("Schwerer Commit-Absturz:", e);
        alert("📡 Schnittstellen-Absturz beim finalen DB-Commit.");
    }
}


</script>
@endsection
