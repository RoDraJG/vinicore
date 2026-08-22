@extends('layouts.map_base')

<!-- ==========================================================================
     🛰️ REAKTIVES VINICORE SYSTEM-TOAST (PROZESSUAL GESICHERTER SITZ)
     ========================================================================== -->
<div id="vinicoreToast" class="hidden opacity-0 pointer-events-none fixed top-20 left-1/2 -translate-x-1/2 z-50 flex items-center space-x-2 px-4 py-3 rounded-xl border font-sans text-xs font-bold shadow-lg bg-emerald-50 border-emerald-200 text-emerald-800 transition-all duration-300 ease-out" style="display: none !important;">
    <span id="toastIcon" class="text-sm"></span>
    <span id="toastText" class="text-slate-950"></span>
</div>

@section('inspektor_content')
<!-- 🚀 MAX-SPACE: Haupt-Padding auf p-1 reduziert für absolute, randlose Desktop-Brillanz! -->
<div id="vinicoreGlobalInspektor" class="flex flex-col h-full w-full bg-white font-sans">
    <div class="p-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 flex-shrink-0">
        <h4 class="font-bold font-mono text-[11px] uppercase tracking-wider text-slate-500 flex items-center gap-1.5 select-none">
            📊 Liegenschafts-Inspektor
        </h4>
    </div>
    <!-- 🛒 VINICORE VERTRAGS-STEUERUNG (ABSOLUT SICHER PLATZIERT) -->
    @if(request()->has('vertrag_id'))
    <div class="p-4 bg-emerald-50 border-b border-emerald-200 flex justify-between items-center font-sans text-xs w-full shadow-xs">
        <div class="space-y-0.5">
            <span class="inline-block bg-emerald-600 text-white font-mono uppercase text-[9px] tracking-wider px-2 py-0.5 rounded-md font-bold">● Vertrags-Modus aktiv</span>
            <span class="text-slate-600 block sm:inline ml-2">Du sammelst aktuell Flächen für den Vertrag: <strong class="font-mono">#{{ request()->get('vertrag_id') }}</strong></span>
        </div>
        <button onclick="speichereVertragsWarenkorb()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-mono font-bold py-2 px-4 rounded-xl shadow-md transition uppercase tracking-wider text-[11px] cursor-pointer">
            💾 Auswahl im Vertrag speichern
        </button>
    </div>
    @endif
    <div id="globalInspektorBody" class="p-1 flex-1 overflow-y-auto space-y-2 bg-white">
        <div class="text-center py-36 text-slate-400 text-sm tracking-wide leading-relaxed font-sans font-medium">
            <div class="text-3xl mb-3 text-slate-300">🛰️</div>
            Klicke auf <strong class="text-blue-600 font-bold">hellblaue</strong> Flächen zum Importieren,<br><br>
            oder auf <strong class="text-emerald-600 font-bold">grüne</strong> Flächen zur Ansicht.
        </div>
    </div>
</div>
@endsection

@section('modals')
<div id="gemarkungAuswahlModal" class="hidden fixed inset-0 bg-slate-900/80 backdrop-blur-md flex items-center justify-center p-4" style="z-index: 9999 !important;">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 text-sm space-y-4 transform transition-all scale-100 relative z-50">
        <div class="flex justify-between items-center border-b pb-2.5 border-slate-100">
            <h3 class="font-bold text-slate-900 uppercase font-mono tracking-wider text-xs flex items-center gap-1.5">⚖️ Eindeutige Gemarkung wählen</h3>
            <button onclick="schliesseGemarkungModal()" class="text-slate-400 hover:text-slate-600 font-bold text-xl p-1 cursor-pointer transition-colors">&times;</button>
        </div>
        <p class="text-slate-500 leading-relaxed text-xs">Es wurden mehrere Gemarkungen with diesem Namen im Landesregister gefunden. Bitte wähle das zutreffende Flurstück aus:</p>
        <div id="gemarkungAuswahlListe" class="space-y-2 max-h-64 overflow-y-auto pr-1"></div>
    </div>
</div>
<!-- ⚖️ REAKTIVES VINICORE REVISIONS-MODAL (PROZESSUAL GESICHERT) -->
<div id="bearbeitenModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="z-index: 99999 !important;">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-5 text-sm space-y-4 relative z-50">
        <div class="flex justify-between items-center border-b pb-2 border-slate-100">
            <h3 class="font-bold text-slate-900 uppercase font-mono tracking-wider text-xs flex items-center gap-1.5">⚖️ Besitzverhältnis revisionieren</h3>
            <button onclick="schliesseBearbeitenModal()" class="text-slate-400 hover:text-slate-600 font-bold text-xl p-1 cursor-pointer transition-colors">&times;</button>
        </div>
        
        <form id="vinicoreEditForm" onsubmit="sendeBearbeitung(event)" class="space-y-3.5 font-sans text-xs">
            <input type="hidden" id="editUuid">
            <input type="hidden" id="editVersion">
            
            <div class="space-y-1">
                <label class="block font-mono uppercase text-[10px] text-slate-400 tracking-wider font-bold">● Besitz-Status</label>
                <select id="editStatus" class="w-full bg-white border border-slate-200 p-2.5 rounded-xl font-medium focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition shadow-3xs cursor-pointer text-xs">
                    <option value="eigentum">● Eigentum</option>
                    <option value="gepachtet">● Gepachtet</option>
                    <option value="verpachtet">○ Verpachtet</option>
                </select>
            </div>
            
            <div class="space-y-1">
                <label class="block font-mono uppercase text-[10px] text-slate-400 tracking-wider font-bold">● Amtlicher Flurname / Lage</label>
                <input type="text" id="editLage" class="w-full bg-white border border-slate-200 p-2.5 rounded-xl font-medium focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition shadow-3xs text-xs">
            </div>
            
            <div class="space-y-1">
                <label class="block font-mono uppercase text-[10px] text-slate-400 tracking-wider font-bold">● Revisions-Grund / Protokolltext</label>
                <textarea id="editGrund" rows="3" class="w-full bg-white border border-slate-200 p-2.5 rounded-xl font-medium focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition shadow-3xs text-xs leading-relaxed" required></textarea>
            </div>
            
            <button type="submit" class="w-full bg-slate-900 hover:bg-slate-900/90 text-amber-400 font-mono font-bold text-xs py-3.5 px-4 rounded-xl shadow-md transition uppercase tracking-wider cursor-pointer">
                💾 Revision rechtssicher versiegeln
            </button>
        </form>
    </div>
</div>

@endsection

@section('map_js')
<script>
    // 🚀 CORE-FIX: Doppelte const-Deklarationen entfernt.
    // Das System nutzt nun die globalen Zoom-Wächter aus deinem map_base Layout.

    let map; let geojsonLayer; let alkisWmsLayer; let umgebungsWfsLayer;     
    let vorschauLayer = null; let wfsAbortController = null; 

    let gewaehlteFeaturesSammelkorb = [];    
    let gewaehlteBestandsParzellenKorb = []; 
        // 🛰️ VINICORE CONTRACT-CART STORAGE
    let vinicoreVertragsWarenkorb = []; // Puffert die angeklickten Geometrien im RAM
    let vinicoreAktiveVertragId = null;  // Hält die ID des aktuell bearbeiteten Vertrags

    // Liest die vertrag_id automatisch aus der URL (z.B. ?vertrag_id=45)
    const urlParams = new URLSearchParams(window.location.search);
    vinicoreAktiveVertragId = urlParams.get('vertrag_id') ? parseInt(urlParams.get('vertrag_id')) : null;


    document.addEventListener("DOMContentLoaded", function() {
        initVinicoreMap(); ladeGeoJsonKataster();
    });

    function initVinicoreMap() {
        if (typeof map !== 'undefined' && map !== null) {
            try { map.remove(); } catch (e) { console.warn(e); }
            map = null;
        }
        const startLat = {{ $betrieb->zentrums_lat ?? 50.0000 }}; 
        const startLng = {{ $betrieb->zentrums_lng ?? 7.0000 }};
        map = L.map('vinicoreOverviewMap').setView([startLat, startLng], 19);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

        alkisWmsLayer = L.tileLayer.wms("{!! config('services.gdi_rlp.wms_url') !!}", {
            layers: 'WMS_RP_ALKIS_Liegenschaften', format: 'image/png', transparent: true, version: '1.1.1', crs: L.CRS.EPSG4326, maxZoom: ZOOM_MAX_LIMIT, maxNativeZoom: 19 
        });

        umgebungsWfsLayer = L.geoJSON(null, {
            style: { fillColor: '#3b82f6', fillOpacity: 0.12, weight: 1.2, color: '#2563eb', dashArray: '3, 5' },
            onEachFeature: function(feature, layer) {
                layer.on('click', function(e) {
                    L.DomEvent.stopPropagation(e); L.DomEvent.preventDefault(e);
                    
                    // 🚀 FUNKTIONALER FRONTEND-WÄCHTER: Gleicht im Dreier-Verbund ab!
                    const idx = gewaehlteFeaturesSammelkorb.findIndex(f => 
                        f.properties.flurstueck === feature.properties.flurstueck &&
                        parseInt(f.properties.flur) === parseInt(feature.properties.flur) &&
                        f.properties.gemarkung.toLowerCase().trim() === feature.properties.gemarkung.toLowerCase().trim()
                    );
                    
                    if (idx > -1) { 
                        gewaehlteFeaturesSammelkorb.splice(idx, 1); 
                        umgebungsWfsLayer.resetStyle(layer); 
                    } else { 
                        gewaehlteFeaturesSammelkorb.push(feature); 
                        layer.setStyle({ fillColor: '#3b82f6', fillOpacity: 0.5, weight: 2.5, color: '#2563eb', dashArray: null }); 
                    }
                    rendereSammlerInspektor();
                });
            }
        }).addTo(map);

        // 🚀 CORE-FIX FRONTEND: Der WFS-Umland-Loader zündet jetzt absolut fehlerfrei direkt beim Laden,
        // ohne dass du die Karte auch nur einen Millimeter bewegen musst!
        map.on('moveend', steuereEbenen);
        
        map.whenReady(function() {
            setTimeout(() => { 
                steuereEbenen(); 
            }, 250);
        });
    }

    async function ladeGeoJsonKataster(deaktiviereGlobalenZoom = false) {
        try {
            const response = await fetch('/api/geojson/parzellen');
            const data = await response.json();
            if (geojsonLayer) map.removeLayer(geojsonLayer);

            const urlParams = new URLSearchParams(window.location.search);
            const fokusUuid = urlParams.get('fokus_parzelle');
            let hatFokusGefunden = false;

            geojsonLayer = L.geoJSON(data, {
                style: { fillColor: '#10b981', fillOpacity: 0.35, weight: 2, color: '#059669' },
                onEachFeature: function(feature, layer) {
                    if (fokusUuid && (feature.properties.uuid === fokusUuid || feature.properties.parzelle_uuid === fokusUuid)) {
                        hatFokusGefunden = true;
                        layer.setStyle({ fillColor: '#f59e0b', fillOpacity: 0.6, weight: 3.5, color: '#d97706' });
                        setTimeout(() => {
                            map.setView(layer.getBounds().getCenter(), 19, { animate: true, duration: 1.2 });
                            rendereSammlerInspektor(feature.properties.uuid);
                            window.history.replaceState({}, document.title, window.location.pathname);
                        }, 200);
                    }
                    layer.on('click', function(e) {
                        L.DomEvent.stopPropagation(e); L.DomEvent.preventDefault(e);
                        
                        // 🎯 REVISIONS-SCHILD: Zieht die echte UUID aus den Properties, egal wie der Schlüssel heißt!
                        const echteUuid = feature.properties.parzelle_uuid || feature.properties.uuid;

                        if (!echteUuid || echteUuid === 'undefined') {
                            console.error("Kritisch: Keine gültige UUID im GeoJSON gefunden!");
                            return;
                        }

                        // 💥 DIAGNOSE-ALARM ENTFERNT – Wir übergeben die verifizierte ID!
                        if (typeof window.vinicoreZuletztAktiveGruenUuid !== 'undefined' && window.vinicoreZuletztAktiveGruenUuid === echteUuid) {
                            window.vinicoreZuletztAktiveGruenUuid = null;
                            geojsonLayer.resetStyle(layer);
                            rendereSammlerInspektor();
                        } else {
                            geojsonLayer.eachLayer(l => geojsonLayer.resetStyle(l));
                            layer.setStyle({ fillColor: '#047857', fillOpacity: 0.65, weight: 3.5, color: '#065f46' });
                            
                            // Zündet den Detail-Tunnel mit der realen ID
                            rendereSammlerInspektor(echteUuid);
                        }
                    });


                }
            }).addTo(map);

            if (data.features && data.features.length > 0 && !deaktiviereGlobalenZoom && !hatFokusGefunden) {
                map.fitBounds(geojsonLayer.getBounds(), { padding: [20,20]});
            }
        } catch (e) { console.error(e); }
    }
    function steuereEbenen() {
        const z = map.getZoom();
        if (z >= ZOOM_WFS_START) { if (!map.hasLayer(alkisWmsLayer)) map.addLayer(alkisWmsLayer); ladeUmliegendeWfsParzellen(); } 
        else if (z === ZOOM_WMS_START) { if (!map.hasLayer(alkisWmsLayer)) map.addLayer(alkisWmsLayer); if (umgebungsWfsLayer) umgebungsWfsLayer.clearLayers(); } 
        else { if (map.hasLayer(alkisWmsLayer)) map.removeLayer(alkisWmsLayer); if (umgebungsWfsLayer) umgebungsWfsLayer.clearLayers(); }
    }

    /**
     * Lädt alle umliegenden Parzellen via WFS-BBOX vom Geoportal RLP.
     * 🚀 FINAL-FIX: Eliminiert Phantomflächen durch echten Layer-Reset und sichert Gemarkungen im Verbund ab!
     */
    async function ladeUmliegendeWfsParzellen() {
        if (!map) return;
        if (wfsAbortController) { wfsAbortController.abort(); }
        wfsAbortController = new AbortController(); const signal = wfsAbortController.signal;
        const spinner = document.getElementById('vinicoreMapSpinner'); if (spinner) spinner.classList.remove('hidden');

        const bounds = map.getBounds();
        const sued = bounds.getSouth(); const west = bounds.getWest(); const nord = bounds.getNorth(); const ost = bounds.getEast();
        const latDelta = nord - sued; const lngDelta = ost - west;
        const swX = (west - (lngDelta * 0.05)) * 20037508.34 / 180;
        const swY = Math.log(Math.tan((90 + (sued - (latDelta * 0.05))) * Math.PI / 360)) / (Math.PI / 180) * 20037508.34 / 180;
        const neX = (ost + (lngDelta * 0.05)) * 20037508.34 / 180;
        const neY = Math.log(Math.tan((90 + (nord + (latDelta * 0.05))) * Math.PI / 360)) / (Math.PI / 180) * 20037508.34 / 180;
        const bboxString = [swX.toFixed(5), swY.toFixed(5), neX.toFixed(5), neY.toFixed(5), 'urn:ogc:def:crs:EPSG::3857'].join(',');
        
        try {
            const response = await fetch('/api/kataster/umgebung-laden', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }, body: JSON.stringify({ bbox: bboxString }), signal: signal });
            const data = await response.json();
            
            if (data && data.type === "FeatureCollection" && map) { 
                if (umgebungsWfsLayer) {
                    map.removeLayer(umgebungsWfsLayer);
                }

                umgebungsWfsLayer = L.geoJSON(data, {
                    style: function(feature) {
                        const props = feature.properties;
                        // Baut die eindeutige ID des WFS-Flurstücks
                        const flurstueckId = props.gemarkung + '-' + props.flur + '-' + props.flurstueck;
                        
                        // 🚀 CORE-FIX: Prüft live, ob diese Fläche bereits als GRÜNER Bestand geladen ist
                        let bereitsImBestand = false;
                        if (typeof geojsonLayer !== 'undefined' && geojsonLayer) {
                            geojsonLayer.eachLayer(function(bl) {
                                if (bl.feature && bl.feature.properties) {
                                    const bp = bl.feature.properties;
                                    const bId = bp.gemarkung + '-' + bp.flur + '-' + (bp.flurstueck || bp.flurstueck_zaehler);
                                    if (bId.toLowerCase().trim() === flurstueckId.toLowerCase().trim()) {
                                        bereitsImBestand = true;
                                    }
                                }
                            });
                        }

                        // ❌ ÜBERLAGERUNGS-SCHUTZ: Wenn im Bestand, machen wir das blaue Overlay unsichtbar und blockieren Klicks!
                        if (bereitsImBestand) {
                            return {
                                fillColor: '#000000',
                                fillOpacity: 0,
                                color: '#000000',
                                weight: 0,
                                opacity: 0,
                                interactive: false // 🎯 Verhindert, dass Blau den Klick auf Grün abfängt!
                            };
                        }

                        // Normaler Style für echtes, unberührtes Umland
                        const schonImKorb = gewaehlteFeaturesSammelkorb.some(k => 
                            k.properties.flurstueck === feature.properties.flurstueck && 
                            parseInt(k.properties.flur) === parseInt(feature.properties.flur) &&
                            k.properties.gemarkung.toLowerCase().trim() === feature.properties.gemarkung.toLowerCase().trim()
                        );
                        
                        if (schonImKorb) {
                            return { fillColor: '#2563eb', fillOpacity: 0.5, weight: 2.5, color: '#2563eb', dashArray: null };
                        }

                        return { fillColor: '#3b82f6', fillOpacity: 0.12, weight: 1.2, color: '#2563eb', dashArray: '3, 5' };
                    },
                    onEachFeature: function(feature, layer) {
                        // (Der restliche onEachFeature-Klickblock bleibt exakt so wie er ist!)

                        layer.on('click', function(e) {

                            L.DomEvent.stopPropagation(e); L.DomEvent.preventDefault(e);
                      
                            // 🚀 WENN EIN VERTRAG AKTIV IST (Warenkorb-Modus):
                            if (typeof vinicoreAktiveVertragId !== 'undefined' && vinicoreAktiveVertragId) {
                                const idx = gewaehlteFeaturesSammelkorb.findIndex(f => 
                                    f.properties.flurstueck === feature.properties.flurstueck &&
                                    parseInt(f.properties.flur) === parseInt(feature.properties.flur) &&
                                    f.properties.gemarkung.toLowerCase().trim() === feature.properties.gemarkung.toLowerCase().trim()
                                );
                                
                                if (idx > -1) { 
                                    gewaehlteFeaturesSammelkorb.splice(idx, 1); 
                                    umgebungsWfsLayer.resetStyle(layer); 
                                } else { 
                                    gewaehlteFeaturesSammelkorb.push(feature); 
                                    layer.setStyle({ fillColor: '#2563eb', fillOpacity: 0.5, weight: 2.5, color: '#2563eb', dashArray: null }); 
                                }
                                rendereSammlerInspektor();
                                return;
                            }

                            // 🔷 REINER ANSICHTSMODUS (Kein Vertrag aktiv): Zeigt die Daten kollisionsfrei!
                            if (umgebungsWfsLayer) {
                                umgebungsWfsLayer.eachLayer(function(l) { umgebungsWfsLayer.resetStyle(l); });
                            }
                            layer.setStyle({ fillColor: '#06b6d4', fillOpacity: 0.6, color: '#0891b2', weight: 2 });

                            const props = feature.properties;
                            const body = document.getElementById('globalInspektorBody'); // Deine echte ID!
                            if (body) {
                                body.innerHTML = `
                                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl space-y-1.5 font-sans text-xs animate-fadeIn m-1">
                                        <h5 class="font-bold text-blue-900 uppercase text-[10px] tracking-wider">● Umland-Information</h5>
                                        <div class="font-bold text-slate-800 text-sm">${props.gemarkung || 'Umland'}</div>
                                        <span class="text-slate-500 font-mono block">Flur ${props.flur || '0'} | Nr. ${props.flurstueck || '0'}</span>
                                        <div class="text-red-700 font-medium text-[11px] mt-1 pt-1 border-t border-red-100">⚠️ Diese Fläche ist gesperrt. Kataster-Erfassungen sind aus Sicherheitsgründen streng vertragsgesteuert.</div>
                                    </div>`;
                            }
                        });

}
            }).addTo(map);


            }

        } catch (e) { if (e.name !== 'AbortError') console.error(e); }
        finally { if (spinner) spinner.classList.add('hidden'); }
    }

    async function ZündeSammelSpeicherung() {
        if (gewaehlteFeaturesSammelkorb.length === 0) return;
        try {
            const response = await fetch('/api/kataster/parzellen/speichern-sammelkorb', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }, body: JSON.stringify({ parzellen: gewaehlteFeaturesSammelkorb }) });
            const res = await response.json();
            if (res.success) {
                zeigeVinicoreToast('success', `Erfolg: Parzellen historiensicher importiert!`);
                gewaehlteFeaturesSammelkorb = []; await ladeGeoJsonKataster(true); 
                if (umgebungsWfsLayer) umgebungsWfsLayer.clearLayers(); await ladeUmliegendeWfsParzellen();
            }
        } catch (e) { console.error(e); }
    }

    function zeigeVinicoreToast(typ, text) {
        let toast = document.getElementById('vinicoreToast'); if (toast && toast.parentElement !== document.body) { document.body.appendChild(toast); }
        const icon = document.getElementById('toastIcon'); const inhalt = document.getElementById('toastText'); if (!toast || !inhalt) return;
        inhalt.innerText = text; icon.innerText = (typ === 'success') ? '✅' : '⚠️';
        toast.className = (typ === 'success') ? "fixed top-20 left-1/2 -translate-x-1/2 flex items-center space-x-2 px-4 py-3 rounded-xl border font-sans text-xs font-bold shadow-xl bg-emerald-50 border-emerald-200 text-emerald-800 transition-all duration-300 ease-out" : "fixed top-20 left-1/2 -translate-x-1/2 flex items-center space-x-2 px-4 py-3 rounded-xl border font-sans text-xs font-bold shadow-lg bg-red-50 border-red-200 text-red-800 transition-all duration-300 ease-out";
        toast.style.zIndex = "99999"; toast.style.display = 'flex'; toast.style.opacity = '1';
        setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => { toast.style.display = 'none'; }, 300); }, 4000);
    }

    function EntferneEinzelneAuswahl(typ, identifier) {
        const idx = parseInt(identifier); 
        if (gewaehlteFeaturesSammelkorb[idx]) { 
            const f = gewaehlteFeaturesSammelkorb[idx]; gewaehlteFeaturesSammelkorb.splice(idx, 1); 
            if (umgebungsWfsLayer) umgebungsWfsLayer.eachLayer(l => { if (l.feature.properties.flurstueck === f.properties.flurstueck) umgebungsWfsLayer.resetStyle(l); }); 
        }
        rendereSammlerInspektor();
    }

    function rendereSammlerInspektor(aktiveUuid = null) {
        const body = document.getElementById('globalInspektorBody'); if (!body) return;
        const anzahlBlau = gewaehlteFeaturesSammelkorb.length;

        // 🚀 CORE-FIX: Wenn kein Vertrag aktiv ist und der Korb leer ist, brechen wir das Neuzeichnen ab,
        // damit der eben geöffnete Umland-Infotext im globalInspektorBody felsenfest stehen bleibt!
        if (anzahlBlau === 0 && !aktiveUuid && (typeof vinicoreAktiveVertragId === 'undefined' || !vinicoreAktiveVertragId)) {
            return;
        }

        if (typeof window.vinicoreZuletztAktiveGruenUuid === 'undefined') { window.vinicoreZuletztAktiveGruenUuid = null; }
        if (aktiveUuid !== null) { window.vinicoreZuletztAktiveGruenUuid = aktiveUuid; }

        let html = `<div class="space-y-2 font-sans text-xs flex flex-col h-full text-slate-700">`;
        // ... (Der restliche Code der Funktion bleibt unberührt!)

        html += `<div id="liveInspektorDetailSlot" class="space-y-2"></div>`;

        // 🛒 INTEGRATION: Wenn eine vertrag_id aktiv ist, schaltet die Sidebar auf den neuen LocalStorage-Warenkorb!
        if (vinicoreAktiveVertragId) {
            if (vinicoreVertragsWarenkorb.length > 0) {
                let listHtml = '';
                vinicoreVertragsWarenkorb.forEach((f, idx) => {
                    const props = f.properties;
                    listHtml += `<div class="bg-amber-50/40 border border-amber-200 p-2 rounded-xl text-[11px] flex justify-between items-center shadow-3xs">
                        <div>
                            <strong>${props.gemarkung || 'Umland'}</strong><br>
                            <span class="text-slate-500 font-mono text-[9px]">Flur ${props.flur || '1'} | Nr. ${props.flurstueck || '0'}</span>
                        </div>
                        <button onclick="entferneAusKorbDirekt(${idx})" class="text-red-500 hover:text-red-700 font-bold px-2 text-base cursor-pointer">×</button>
                    </div>`;
                });

                html += `
                    <div class="bg-amber-50/10 border border-amber-100 p-2 rounded-xl flex flex-col h-auto shadow-3xs">
                        <div class="flex justify-between items-center border-b border-amber-100 pb-1 mb-1.5">
                            <h5 class="font-bold text-amber-600 font-mono tracking-wider text-[10px] uppercase flex items-center gap-1 select-none">
                                <span>🛒 Gewählte Parzellen (${vinicoreVertragsWarenkorb.length})</span>
                            </h5>
                            <button onclick="vinicoreVertragsWarenkorb=[];if(umgebungsWfsLayer)umgebungsWfsLayer.resetStyle();rendereSammlerInspektor();" class="text-[9px] font-mono font-bold text-red-500 bg-red-50 hover:bg-red-100 border border-red-200 px-1.5 py-0.5 rounded transition cursor-pointer">🗑️ Leeren</button>
                        </div>
                        <div class="space-y-1 overflow-y-auto pr-0.5 mb-1.5 max-h-40 md:max-h-[35vh]">${listHtml}</div>
                    </div>`;
            } else if (!window.vinicoreZuletztAktiveGruenUuid && !window.vinicoreZuletztGeklicktesUmland) {
                // Zeigt den Platzhalter NUR an, wenn weder eine grüne Parzelle NOCH eine blaue Umlandfläche aktiv angewählt ist!
                html += `<div class="text-center py-12 text-slate-400 text-xs font-medium"><div class="text-2xl mb-1 text-slate-300">🛰️</div>Klicke auf blaue Umlandflächen, um sie in den Vertrag zu legen.</div>`;
            }

        } else {
            // 🔹 ALTER ORIGINALER MODUS (Ohne Vertrag): Nutzt wie gewohnt das blaue Array
            const anzahlBlau = gewaehlteFeaturesSammelkorb.length;
            if (anzahlBlau > 0) {
                let summeBlauM2 = 0; let blauListHtml = '';
                gewaehlteFeaturesSammelkorb.forEach((f, idx) => {
                    const props = f.properties; const onyxM2 = parseInt(props.flaeche_m2 || 0); summeBlauM2 += onyxM2;
                    blauListHtml += `<div class="bg-blue-50/30 border border-blue-200 p-2 rounded-xl text-[11px] flex justify-between items-center shadow-3xs"><div><strong>${props.gemarkung}</strong><br><span class="text-slate-500 font-mono text-[9px]">Flur ${props.flur || '1'} | Nr. ${props.flurstueck}</span><br><div class="text-blue-700 font-mono font-bold text-[10px] mt-0.5 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100/60 inline-block">${onyxM2.toLocaleString('de-DE')} m²</div></div><button onclick="EntferneEinzelneAuswahl('blau', ${idx})" class="text-red-500 hover:text-red-700 font-bold px-2 text-base cursor-pointer">×</button></div>`;
                });
                if (typeof inspektorBlauGeoeffnet === 'undefined') window.inspektorBlauGeoeffnet = true;
                const pfeilB = window.inspektorBlauGeoeffnet ? '▲' : '▼'; const verstecktB = window.inspektorBlauGeoeffnet ? '' : 'hidden';
                html += `<div class="bg-blue-50/10 border border-blue-100 p-2 rounded-xl flex flex-col h-auto shadow-3xs"><div class="flex justify-between items-center border-b border-blue-100 pb-1 mb-1.5"><h5 onclick="window.inspektorBlauGeoeffnet=!window.inspektorBlauGeoeffnet;rendereSammlerInspektor();" class="font-bold text-blue-600 font-mono tracking-wider text-[10px] uppercase flex items-center gap-1 cursor-pointer select-none"><span>🛒 Neue Auswahl (${anzahlBlau})</span> <span class="text-[9px] bg-blue-100/80 px-1.5 py-0.5 rounded text-blue-700 font-mono">${pfeilB}</span></h5><button onclick="gewaehlteFeaturesSammelkorb=[];if(umgebungsWfsLayer)umgebungsWfsLayer.resetStyle();rendereSammlerInspektor();" class="text-[9px] font-mono font-bold text-red-500 bg-red-50 hover:bg-red-100 border border-red-200 px-1.5 py-0.5 rounded transition cursor-pointer">🗑️ Leeren</button></div><div class="${verstecktB} space-y-1 overflow-y-auto pr-0.5 mb-1.5 h-auto max-h-40 md:max-h-[35vh]">${blauListHtml}</div><div class="text-right text-[11px] font-mono font-bold text-blue-600 bg-blue-50/60 px-2 py-0.5 rounded border border-blue-200/50">Σ Neufl.: ${summeBlauM2.toLocaleString('de-DE')} m²</div></div><button onclick="ZündeSammelSpeicherung()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-mono font-bold text-[11px] py-2 px-3 rounded-xl shadow-md transition uppercase tracking-wider cursor-pointer">📥 Auswahl importieren</button>`;
            } else if (!window.vinicoreZuletztAktiveGruenUuid) {
                html += `<div class="text-center py-12 text-slate-400 text-xs font-medium"><div class="text-2xl mb-1 text-slate-300">🛰️</div>Hellblaue Flächen anklicken, um den Importkorb zu füllen.</div>`;
            }
        }

        html += `</div>`;
        body.innerHTML = html;

        if (window.vinicoreZuletztAktiveGruenUuid) { 
            oeffneGlobalenInspektorWidget(window.vinicoreZuletztAktiveGruenUuid); 
        }
    }

     /**
     * 🛰️ DEINE PRIMÄRE GEOPORTAL-ABFRAGE
     * 🚀 SYNTAX-FIX: Absolut saubere Klammerung, damit nachfolgende Funktionen (sendeBearbeitung) geladen werden!
     */
    function ZündeGeoportalAbfrage() {
        const g = document.getElementById('searchOrtName')?.value || '';
        const f = document.getElementById('searchFlur')?.value || '';
        const z = document.getElementById('searchFlurstueck')?.value || '';
        const n = document.getElementById('searchNenner')?.value || '';

        const spinner = document.getElementById('vinicoreMapSpinner');
        if (spinner) spinner.classList.remove('hidden');

        const payload = { 
            gemarkung: g, 
            flur: f, 
            flurstueck_zaehler: z, 
            nenner: n 
        };

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        fetch('/api/kataster/suchen-im-geoportal', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && (data.feature || data.features)) {
                const zielFeature = data.feature || data.features;
                const props = zielFeature.properties;
                let istBestand = false;

                if (geojsonLayer) {
                    geojsonLayer.eachLayer(function(gLayer) {
                        if (gLayer.feature && gLayer.feature.properties) {
                            const bp = gLayer.feature.properties;
                            if (bp.gemarkung.toLowerCase().trim() === props.gemarkung.toLowerCase().trim() && 
                                String(bp.flur) === String(props.flur) && 
                                String(bp.flurstueck) === String(props.flurstueck)) {
                                
                                istBestand = true;
                                setTimeout(() => {
                                    geojsonLayer.eachLayer(l => geojsonLayer.resetStyle(l));
                                    gLayer.setStyle({ fillColor: '#047857', fillOpacity: 0.65, weight: 3.5, color: '#065f46' });
                                    const grenzen = gLayer.getBounds();
                                    if (grenzen && grenzen.isValid()) {
                                        map.fitBounds(grenzen, { padding:[20,20], maxZoom: 19 });
                                    }
                                    rendereSammlerInspektor(bp.uuid);
                                }, 50);
                            }
                        }
                    });
                }

                if (!istBestand) {
                    setTimeout(() => { zündeVorschauFuerFeature(zielFeature); }, 50);
                }

            } else if (data.auswahl && data.auswahl.length > 0) {
                let listHtml = ''; 
                data.auswahl.forEach(item => { 
                    listHtml += `<button onclick="ZündeManuelleSuchInjektion('${item.gemarkung}','${f}','${z}','${item.nenner || ''}')" class="w-full text-left bg-slate-50 hover:bg-blue-50 border border-slate-200 p-3 rounded-xl mb-1.5 transition flex justify-between items-center cursor-pointer text-sm font-sans">
                        <div>
                            <strong>${item.gemarkung}</strong><br>
                            <span class="text-slate-500 text-xs">Flur ${f} | Nr. ${z}${item.nenner ? '/' + item.nenner : ''}</span>
                        </div>
                        <div class="text-mono text-blue-600 font-bold text-xs">${parseInt(item.flaeche_m2 || 0).toLocaleString('de-DE')} m² ➔</div>
                    </button>`; 
                });
                document.getElementById('gemarkungAuswahlListe').innerHTML = listHtml;
                document.getElementById('gemarkungAuswahlModal').classList.remove('hidden');
            }
        }) // 🎯 KORREKTER ABSCHLUSS DER PROMTS KORRIGIERT!
        .catch(err => console.error(err))
        .finally(() => { if (spinner) spinner.classList.add('hidden'); });
    }

    function ZündeManuelleSuchInjektion(g, f, z, n) { document.getElementById('gemarkungAuswahlModal').classList.add('hidden'); document.getElementById('searchOrtName').value = g; document.getElementById('searchFlur').value = f; document.getElementById('searchFlurstueck').value = z; document.getElementById('searchNenner').value = n; ZündeGeoportalAbfrage(); }
    /**
     * 🛰️ AMTUCHES VORSCHAU-POLYGON (SUCH-ERGEBNIS)
     * 🚀 CORE-FIX: Zeichnet die Fläche rein visuell und zoomt dorthin, legt sie aber NICHT direkt in den Korb!
     */
    function zündeVorschauFuerFeature(feat) {
        if (vorschauLayer) { map.removeLayer(vorschauLayer); }
        const props = feat.properties;
        
        vorschauLayer = L.geoJSON(feat, { 
            style: { fillColor: '#ea580c', fillOpacity: 0.35, weight: 4.0, color: '#c2410c', dashArray: '6,4' }, 
            interactive: true,
            onEachFeature: function(f, layer) {
                layer.on('click', function(e) {
                    L.DomEvent.stopPropagation(e); L.DomEvent.preventDefault(e); 
                    
                    // Entfernt die Vorschau-Farbe, da wir die Fläche jetzt operativ verarbeiten
                    map.removeLayer(vorschauLayer); 
                    vorschauLayer = null; 
                    
                    let istBestand = false;
                    // Checkt, ob es sich um eine bereits grüne Bestandsfläche handelt
                    if (geojsonLayer) { 
                        geojsonLayer.eachLayer(function(gLayer) { 
                            if (gLayer.feature && gLayer.feature.properties) { 
                                const bp = gLayer.feature.properties; 
                                if (bp.gemarkung.toLowerCase().trim() === props.gemarkung.toLowerCase().trim() && String(bp.flur) === String(props.flur) && String(bp.flurstueck) === String(props.flurstueck)) { 
                                    istBestand = true; 
                                    gLayer.fire('click'); // Fokussiert die grüne Bestandsfläche im Inspektor!
                                } 
                            } 
                        }); 
                    }
                    
                    // NUR WENN ES KEIN BESTAND IST, wandert sie ERST JETZT durch deinen Klick aktiv in den blauen Warenkorb!
                    if (!istBestand) { 
                        const schonImKorb = gewaehlteFeaturesSammelkorb.some(k => 
                            String(k.properties.flurstueck) === String(props.flurstueck) && 
                            parseInt(k.properties.flur) === parseInt(props.flur) &&
                            k.properties.gemarkung.toLowerCase().trim() === props.gemarkung.toLowerCase().trim()
                        ); 
                        if (!schonImKorb) { 
                            gewaehlteFeaturesSammelkorb.push(feat); 
                        } 
                        rendereSammlerInspektor(); 
                    }
                });
            }
        }).addTo(map);
        
        // Zieht die Kamera flüssig an die Grenzen des gefundenen Flurstücks heran
        const grenzen = vorschauLayer.getBounds(); 
        if (grenzen && grenzen.isValid()) { 
            map.fitBounds(grenzen, { padding:[20,20], maxZoom: 19 }); 
        }
    }

    function schliesseGemarkungModal() { document.getElementById('gemarkungAuswahlModal').classList.add('hidden'); }
    function oeffneGlobalenInspektorWidget(uuid) {
        const body = document.getElementById('globalInspektorBody'); 
        if (!body) return;
        
        body.innerHTML = `<div class="flex flex-col items-center justify-center py-12 text-slate-400 text-xs font-medium space-y-2 font-sans"><div class="text-xl animate-spin">📡</div><div class="animate-pulse">Synchronisiere Detail-Matrix...</div></div>`;

        // 🚀 HIGH-SECURITY FETCH: Schickt Token und Header mit, um Routing-Sperren zu brechen!
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        fetch(`/api/kataster/parzelle-details/${uuid}`, { 
            method: 'GET',
            headers: { 
                'X-Requested-With': 'XMLHttpRequest', 
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            } 
        })
        .then(r => {
            if (!r.ok) {
                throw new Error("Server-Antwort war nicht OK: Status " + r.status);
            }
            return r.json();
        })
        .then(res => {
            if (res.success) {
                const p = res.parzelle; 
                const vZahl = parseInt(p.version || p.v || 1);
                const vBadge = (vZahl === 1) ? `<div class="bg-amber-50 text-amber-800 border border-amber-200 px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider animate-pulse text-center mt-1.5 shadow-3xs">⚠️ Erstprüfung im Katasterspiegel ausstehend</div>` : '';
                
                let buttonHtml = '';
                if (vZahl === 1) {
                    buttonHtml = `
                        <div class="flex items-center gap-1 mt-1">
                            <button onclick="oeffneBearbeitenModal('${p.parzelle_uuid}', '${p.besitz_status || 'eigentum'}', '${p.flurname_lage || ''}', '${vZahl}')" class="text-amber-700 hover:text-amber-900 font-bold px-2 py-1 bg-amber-50 hover:bg-amber-100 border border-amber-300 rounded-lg shadow-3xs transition cursor-pointer text-[11px] animate-pulse">⚖️ Erstprüfung</button>
                            <button onclick="vinicoreExpressVernichtung('${p.parzelle_uuid}')" class="text-red-600 hover:text-red-800 font-bold p-1 px-2 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg shadow-3xs transition cursor-pointer text-[11px]">🗑️</button>
                        </div>`;
                } else {
                    buttonHtml = `
                        <button onclick="oeffneBearbeitenModal('${p.parzelle_uuid}', '${p.besitz_status || 'eigentum'}', '${p.flurname_lage || ''}', '${vZahl}')" class="text-blue-600 hover:text-blue-800 font-bold px-2 py-1 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg shadow-3xs transition cursor-pointer text-[11px] mt-1">📝 Bearbeiten</button>`;
                }

                let statusPille = (p.besitz_status === 'eigentum') ? '<span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide">● Eigentum</span>' : ((p.besitz_status === 'gepachtet') ? '<span class="bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide">● Gepachtet</span>' : '<span class="bg-slate-50 text-slate-500 border border-slate-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide">○ Verpachtet</span>');
                let verknuepfungsHtml = p.anlage_name ? `<div class="space-y-1 font-sans"><span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-0.5 rounded-lg text-xs font-bold uppercase tracking-wider shadow-3xs">🌿 ${p.anlage_name}</span><span class="block text-[11px] text-slate-400 font-medium">🚜 Großschlag: ${p.schlag_name || 'Unbekannt'}</span></div>` : `<span class="text-slate-400 text-[10px] font-mono italic">Katasterfläche besitzt aktuell keine Bestockung</span>`;

                body.innerHTML = `
                    <div class="space-y-2 font-sans text-xs text-slate-700 p-1.5 animate-fadeIn">
                        <div class="bg-slate-50/70 border border-slate-200 p-2.5 rounded-xl space-y-2 shadow-3xs">
                            <div class="flex justify-between items-start border-b border-slate-100 pb-1.5">
                                <div>
                                    <h5 class="text-slate-900 font-extrabold text-sm tracking-tight leading-none mb-1">${p.gemarkung}</h5>
                                    <span class="text-slate-400 font-mono text-[10px] block">Flur ${p.flur} | Nr. ${p.flurstueck_zaehler}${p.flurstueck_nenner ? '/' + p.flurstueck_nenner : ''}</span>
                                </div>
                                <div class="flex flex-col items-end space-y-1">${statusPille}${buttonHtml}</div>
                            </div>
                            <div class="text-slate-500 italic text-[11px]">Amtlicher Flurname: <strong class="text-slate-700 font-semibold not-italic text-xs">${p.flurname_lage || 'Keine Angabe'}</strong></div>
                            <div class="font-mono font-bold text-slate-900 text-sm border-t border-slate-100 pt-2 flex justify-between items-center">
                                <span class="font-sans text-slate-400 font-medium text-xs">📐 Fläche:</span>
                                <span class="bg-white border border-slate-200 px-2 py-0.5 rounded shadow-3xs text-slate-950 font-bold">${parseInt(p.amtliche_flaeche_m2 || 0).toLocaleString('de-DE')} m²</span>
                            </div>
                            ${vBadge}
                        </div>
                        <div class="bg-slate-50/30 border border-slate-150 p-2.5 rounded-xl space-y-1.5 shadow-3xs"><h6 class="font-mono font-bold uppercase text-[9px] text-slate-400 tracking-wider border-b border-slate-150 pb-1 flex items-center gap-1">🍇 Agronomische Rebanlage</h6><div class="py-0.5">${verknuepfungsHtml}</div></div>
                    </div>`;
            }
        })
        .catch(err => { 
            console.error("Detaillierter Fehler im Gruen-Tunnel:", err); 
            body.innerHTML = `<div class="text-center py-12 text-red-500 font-sans text-xs">⚠️ Fehler beim Laden der Parzellendaten. Prüfe die Browser-Konsole (F12)!</div>`;
        });
    }



function oeffneBearbeitenModal(uuid, status, flurname, version) {
    document.getElementById('editUuid').value = uuid;
    document.getElementById('editLage').value = flurname;
    document.getElementById('editVersion').value = version;
    
    const statusDropdown = document.getElementById('editStatus');
    const grundFeld = document.getElementById('editGrund');
    if (!statusDropdown || !grundFeld) return;

    // 🚀 INTELLIGENTE FINANZ-AUTOMATISIERUNG (NUR FÜR DIE ERSTPRÜFUNG V1)
    if (parseInt(version) === 1) {
        // A. Liest den Typus des Vertrags aus dem LocalStorage-Puffer [source: 1.1.2]
        const tempVertrag = JSON.parse(localStorage.getItem('vinicore_temp_vertrag_stammdaten'));
        
        if (tempVertrag && tempVertrag.typ) {
            if (tempVertrag.typ === 'pacht_aufwand') {
                statusDropdown.value = 'gepachtet'; // 🌿 Automatisch gepachtet setzen
            } else if (tempVertrag.typ === 'kauf') {
                statusDropdown.value = 'eigentum';   // 💰 Automatisch Eigentum setzen
            } else if (tempVertrag.typ === 'pacht_ertrag') {
                statusDropdown.value = 'verpachtet'; // 🚜 Automatisch verpachtet setzen
            }
            
            // B. Aktiviert den harten Schreibschutz für das Dropdown-Feld
            statusDropdown.disabled = true;
            statusDropdown.className = "w-full bg-slate-100 text-slate-500 border border-slate-200 p-2.5 rounded-xl font-medium cursor-not-allowed text-xs";
        }

        // Amtlichen Standardtext für die Erstaufnahme einbrennen
        grundFeld.value = 'Erstaufnahme und erstmalige rechtliche Erfassung des Besitzverhältnisses';
        grundFeld.readOnly = true;
        grundFeld.className = "w-full bg-slate-100 text-slate-500 border border-slate-200 p-2.5 rounded-xl font-medium cursor-not-allowed text-xs";
    
    } else {
        // 🔒 FOLGE-REVISIONEN (VERSION 2+): Der Besitz-Status ist unveränderbar versiegelt!
        statusDropdown.value = status;
        statusDropdown.disabled = true; // 🎯 HIER WIRD AB VERSION 2 STARR GESPERRT!
        statusDropdown.className = "w-full bg-slate-100 text-slate-500 border border-slate-200 p-2.5 rounded-xl font-medium cursor-not-allowed text-xs";
        
        // Die agronomische Lagebezeichnung und der Grund bleiben für Updates natürlich offen
        grundFeld.value = '';
        grundFeld.readOnly = false;
        grundFeld.className = "w-full bg-white border border-slate-200 p-2.5 rounded-xl font-medium focus:border-blue-500 text-xs";
    }


    // Modal auf dem Bildschirm einblenden
    document.getElementById('bearbeitenModal').classList.remove('hidden');
}

        /**
     * 🛰️ RECHTSPFEILER KARTE: REVISION & ERSTPRÜFUNG SPEICHERN
     * 🚀 ORDENTLICHE RESTAURIERUNG: Steht frei im globalen Skript-Scope, damit das HTML-Formular 
     * die Funktion beim Absenden (onsubmit) fehlerfrei und direkt erreicht!
     */
    async function sendeBearbeitung(event) {
        if (event) { event.preventDefault(); }
        
        const uuid = document.getElementById('editUuid')?.value || '';
        // Liest den Wert unzerstörbar aus, völlig egal ob das Feld gesperrt ist oder nicht
        const status = document.getElementById('editStatus').value;

        const flurname = document.getElementById('editLage')?.value || '';
        const grund = document.getElementById('editGrund')?.value || '';

        if (!uuid) {
            if (typeof zeigeVinicoreToast === 'function') {
                zeigeVinicoreToast('error', 'Fehler: Keine Parzellen-ID lokalisiert.');
            } else {
                alert('Fehler: Keine Parzellen-ID lokalisiert.');
            }
            return;
        }

        const spinner = document.getElementById('vinicoreMapSpinner');
        if (spinner) spinner.classList.remove('hidden');

        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // 🎯 ROUTEN-SYNCHRONISATION: Zielt direkt auf deine echte Controller-Mündung
            const response = await fetch(`/api/kataster/parzellen/aktualisieren/${uuid}`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json', 
                    'X-CSRF-TOKEN': token 
                },
                body: JSON.stringify({
                    uuid: uuid,
                    besitz_status: status,
                    flurname_lage: flurname,
                    aenderungsgrund: grund
                })
            });

            const data = await response.json();

            if (data.success || response.ok) {
                // Schließt das Bearbeiten-Modal ordnungsgemäß
                schliesseBearbeitenModal();
                
                // Feuert das reaktive System-Toast ab, falls vorhanden, andernfalls Alert
                if (typeof zeigeVinicoreToast === 'function') {
                    zeigeVinicoreToast('success', 'Erstprüfung erfolgreich rechtssicher versiegelt!');
                } else {
                    alert('Erstprüfung erfolgreich rechtssicher versiegelt!');
                }
                
                // Aktualisiert die Karte und den Inspektor direkt im RAM
                await ladeGeoJsonKataster(true);
                rendereSammlerInspektor(uuid);
            } else {
                if (typeof zeigeVinicoreToast === 'function') {
                    zeigeVinicoreToast('error', data.message || 'Laufzeit-Sperre im Datenbank-Kernel.');
                } else {
                    alert(data.message || 'Laufzeit-Sperre im Datenbank-Kernel.');
                }
            }
        } catch (err) {
            console.error("Absturz beim Senden der Erstprüfung:", err);
            if (typeof zeigeVinicoreToast === 'function') {
                zeigeVinicoreToast('error', 'Kritischer Fehler in der Netzwerk-Leitung.');
            } else {
                alert('Kritischer Fehler in der Netzwerk-Leitung.');
            }
        } finally {
            if (spinner) spinner.classList.add('hidden');
        }
    }
function speichereVertragsWarenkorb() {
    if (vinicoreVertragsWarenkorb.length === 0) {
        alert("Fehler: Dein Warenkorb ist leer. Klicke auf blaue Umlandflächen, um sie hinzuzufügen!");
        return;
    }

    // 💾 SPEICHERT TEMPORÄR IM BROWSER-SPEICHER (LOCALSTORAGE) [source: 1.1.2]
    localStorage.setItem('vinicore_temporaere_parzellen', JSON.stringify(vinicoreVertragsWarenkorb));

    alert("Flächen erfolgreich im Puffer zwischengespeichert! Wir kehren jetzt zum Vertragsformular zurück.");
    
    // Leitet zurück zum Formular im Büro
    window.location.href = '/finanzen/vertrag-anlegen';
}

function entferneAusKorbDirekt(index) {
    const feature = vinicoreVertragsWarenkorb[index];
    if (!feature) return;
    
    const props = feature.properties;
    const targetId = props.gemarkung + '-' + props.flur + '-' + props.flurstueck;

    vinicoreVertragsWarenkorb.splice(index, 1);

    if (umgebungsWfsLayer) {
        umgebungsWfsLayer.eachLayer(function(layer) {
            const lp = layer.feature.properties;
            if ((lp.gemarkung + '-' + lp.flur + '-' + lp.flurstueck) === targetId) {
                umgebungsWfsLayer.resetStyle(layer);
            }
        });
    }
    rendereSammlerInspektor();
}
</script>
@endsection
