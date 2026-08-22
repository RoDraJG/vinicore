@extends('layouts.map_base')

@section('inspektor_content')
<div id="inspektorInhalt" class="text-center text-slate-400 py-44 text-sm tracking-wide">
    💡 Wähle eine deiner Stammparzellen (Grün) aus, um die Weinbergs-Anlage zu bearbeiten oder Schläge zu formieren.
</div>
@endsection

@section('modals')
<!-- Modal zur Definition der Zwischenschicht: ANLAGE -->
<div id="anlageModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl border shadow-xl max-w-md w-full p-5 text-xs space-y-4">
        <div class="flex justify-between items-center border-b pb-2">
            <h3 class="font-bold text-slate-900 uppercase font-mono tracking-wider">🍇 Weinbergs-Anlage definieren</h3>
            <button onclick="schliesseAnlageModal()" class="text-slate-400 font-bold text-base cursor-pointer">&times;</button>
        </div>
        <form onsubmit="speichereAnlage(event)" class="space-y-3">
            <input type="hidden" id="anlageParzelleUuid">
            <div>
                <label class="text-slate-600 font-medium block mb-1">Rebsorte:</label>
                <select id="anlageRebsorte" required class="w-full bg-slate-50 border p-2 rounded-lg font-bold">
                    <option value="Riesling">Riesling</option>
                    <option value="Spätburgunder">Spätburgunder</option>
                    <option value="Weißburgunder">Weißburgunder</option>
                    <option value="Grauburgunder">Weißburgunder / Ruländer</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-slate-600 font-medium block mb-1">Anpflanzjahr:</label>
                    <input type="number" id="anlageJahr" min="1950" max="2026" value="2020" class="w-full bg-slate-50 border p-2 rounded-lg font-mono">
                </div>
                <div>
                    <label class="text-slate-600 font-medium block mb-1">Zeilenbreite (m):</label>
                    <input type="number" id="anlageBreite" step="0.1" value="2.00" class="w-full bg-slate-50 border p-2 rounded-lg font-mono">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t">
                <button type="button" onclick="schliesseAnlageModal()" class="bg-slate-100 py-2 px-4 rounded-lg uppercase tracking-wider font-bold">Abbrechen</button>
                <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-lg uppercase tracking-wider font-bold shadow-sm">Anlage sichern</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('map_js')
<script>
    // 🚀 CORE-FIX: Alle Zoom-Konstanten hier gelöscht, da sie bereits in map_base deklariert sind!
    let map, geojsonLayer;
    let gewaehlteBestandsParzellenKorb = [];

    document.addEventListener("DOMContentLoaded", function() {
        // Hier folgt dein unveränderter initVinicoreMap() Code...

        map = L.map('vinicoreOverviewMap').setView([49.8286, 6.9458], 17);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

        // Lädt ausschließlich deine eigenen Bestands-Stammparzellen
        ladeEigeneParzellen();
    });

    async function ladeEigeneParzellen() {
        const r = await fetch('/api/geojson/parzellen');
        const data = await r.json();

        if (geojsonLayer) map.removeLayer(geojsonLayer);
        geojsonLayer = L.geoJSON(data, {
            style: { fillColor: '#10b981', fillOpacity: 0.35, weight: 2, color: '#059669' },
            onEachFeature: function(feature, layer) {
                layer.on('click', function(e) {
                    L.DomEvent.stopPropagation(e);
                    // Öffnet reaktiv das Anlagen-Modal für die agronomische Zwischenschicht!
                    oeffneAnlageModal(feature.properties.uuid);
                });
            }
        }).addTo(map);
        
        if (data.features.length > 0) map.fitBounds(geojsonLayer.getBounds(), { padding: [20, 20] });
    }

    function oeffneAnlageModal(uuid) {
        document.getElementById('anlageParzelleUuid').value = uuid;
        document.getElementById('anlageModal').classList.remove('hidden');
    }

    function schliesseAnlageModal() { document.getElementById('anlageModal').classList.add('hidden'); }

    async function speichereAnlage(e) {
        e.preventDefault();
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const payload = {
            parzelle_uuid: document.getElementById('anlageParzelleUuid').value,
            rebsorte: document.getElementById('anlageRebsorte').value,
            pflanzjahr: document.getElementById('anlageJahr').value,
            zeilenbreite: document.getElementById('anlageBreite').value
        };

        const r = await fetch('/api/kataster/anlagen/speichern', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify(payload)
        });
        const res = await r.json();
        if(res.success) {
            alert("🍇 Anlage erfolgreich unter der Stammparzelle eingebucht!");
            schliesseAnlageModal();
            // Hier fügst du das Feature dem Korb hinzu, um daraus im Inspektor den InVeKoS-Schlag zu bauen...
            const feat = geojsonLayer.getLayers().find(l => l.feature.properties.uuid === payload.parzelle_uuid).feature;
            gewaehlteBestandsParzellenKorb.push(feat);
            rendereSchlagInspektor();
        }
    }

    function rendereSchlagInspektor() {
        // Hier greift deine bewährte, unzerstörbare InVeKoS-Kantenprüfung aus dem vorherigen Schritt
        const c = document.getElementById('inspektorContainer');
        let html = `<div class="space-y-3 font-sans text-xs"><h4 class="font-bold border-b pb-1">🍇 Schlagformierung</h4>`;
        gewaehlteBestandsParzellenKorb.forEach(p => {
            html += `<div class="bg-emerald-50 p-2 border border-emerald-200 rounded">🟢 ${p.properties.gemarkung} | Nr. ${p.properties.flurstueck}</div>`;
        });
        html += `<input type="text" id="neuerSammelSchlagName" placeholder="Schlag Name" class="w-full border p-1.5 rounded">`;
        html += `<button onclick="ZündeSammelSchlagErstellung()" class="w-full bg-blue-600 text-white font-bold py-2 rounded uppercase tracking-wider">🚀 Großschlag erzeugen</button></div>`;
        c.innerHTML = html;
    }
</script>
@endsection
