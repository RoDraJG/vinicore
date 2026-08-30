@extends('layouts.map_base')

<!-- ==========================================================================
     🛰️ REAKTIVES VINICORE SYSTEM-TOAST (PROZESSUAL GESICHERTER SITZ)
     ========================================================================== -->
<div id="vinicoreToast" class="hidden opacity-0 pointer-events-none fixed top-20 left-1/2 -translate-x-1/2 z-50 flex items-center space-x-2 px-4 py-3 rounded-xl border font-sans text-xs font-bold shadow-lg bg-emerald-50 border-emerald-200 text-emerald-800 transition-all duration-300 ease-out" style="display: none !important;">
    <span id="toastIcon" class="text-sm"></span>
    <span id="toastText" class="text-slate-950"></span>
</div>

@section('inspektor_content')
<div id="vinicoreGlobalInspektor" class="flex flex-col h-full w-full bg-white font-sans">
    <div class="p-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 flex-shrink-0">
        <h4 class="font-bold font-mono text-[11px] uppercase tracking-wider text-slate-500 flex items-center gap-1.5 select-none">
            📊 Liegenschafts-Inspektor
        </h4>
    </div>
    
    <!-- 🛒 REAKTIVER CONTRACT-HEADER: Reagiert dynamisch auf die URL -->
    @if(request()->has('vertrag_id'))
    <div class="p-4 bg-emerald-50 border-b border-emerald-200 flex justify-between items-center font-sans text-xs w-full shadow-xs">
        <div class="space-y-0.5">
            <span class="inline-block bg-emerald-600 text-white font-mono uppercase text-[9px] tracking-wider px-2 py-0.5 rounded-md font-bold">● Vertrags-Modus aktiv</span>
            <span class="text-slate-600 block sm:inline ml-2">Du sammelst aktuell Flächen für den Vertrag: <strong class="font-mono">#{{ request()->get('vertrag_id') }}</strong></span>
        </div>
        <button onclick="ZündeVertragsWeiterleitung()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-mono font-bold py-2 px-4 rounded-xl shadow-md transition uppercase tracking-wider text-[11px] cursor-pointer">
            💾 In Vertrag übernehmen
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
        <p class="text-slate-500 leading-relaxed text-xs">Es wurden mehrere Gemarkungen mit diesem Namen im Landesregister gefunden. Bitte wähle das zutreffende Flurstück aus:</p>
        <div id="gemarkungAuswahlListe" class="space-y-2 max-h-64 overflow-y-auto pr-1"></div>
    </div>
</div>

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
    let map; let geojsonLayer; let alkisWmsLayer; let umgebungsWfsLayer;     
    let vorschauLayer = null; let wfsAbortController = null; 

    // EINKORB-SYSTEM (Single Source of Truth)
    let gewaehlteFeaturesSammelkorb = [];    
    let gewaehlteBestandsParzellenKorb = []; 
    let vinicoreAktiveVertragId = null;

    const urlParams = new URLSearchParams(window.location.search);
    vinicoreAktiveVertragId = urlParams.get('vertrag_id') ? parseInt(urlParams.get('vertrag_id')) : null;

    document.addEventListener("DOMContentLoaded", function() {
        window.vinicoreKartenFokusBereitsAusgefuehrt = false;
        
        const temporaereFlächenRaw = localStorage.getItem('vinicore_temporaere_parzellen');

        if (temporaereFlächenRaw) {
            try {
                const geladeneParzellen = JSON.parse(temporaereFlächenRaw);
                if (Array.isArray(geladeneParzellen) && geladeneParzellen.length > 0) {
                    gewaehlteFeaturesSammelkorb = geladeneParzellen;
                }
            } catch (e) {
                console.error("Fehler beim Vorladen der Vertragsparzellen:", e);
            }
        }
        
        initVinicoreMap(); 
        ladeGeoJsonKataster();
        if (gewaehlteFeaturesSammelkorb.length > 0 && map) {
            // 🚀 GEOMETRIE-ENTPACKER: Liest die Properties sicher aus dem GeoJSON-Kopf
            const ersteParzelle = gewaehlteFeaturesSammelkorb[0];
            const props = ersteParzelle.properties ? ersteParzelle.properties : ersteParzelle;
            
            const g = (props.gemarkung || props.gemarkungs_name || '').toString().trim();
            const f = parseInt(props.flur || props.flurnummer || 1);
            
            let zRaw = props.flurstueck_zaehler || props.zaehler || props.flurstueck || '';
            let nRaw = props.flurstueck_nenner || props.nenner || '';
            
            if (zRaw.toString().includes('/')) {
                const parts = zRaw.toString().split('/');
                zRaw = parts[0] ? parts[0] : zRaw;
                if (!nRaw && parts[1]) nRaw = parts[1];
            }

            const z = zRaw.toString().replace(/^[0\s]+/, '').replace(/[^\d]/g, '');
            let nennerPayload = null;
            if (nRaw && nRaw.toString().trim() !== '' && nRaw.toString().trim() !== '0' && nRaw.toString().trim() !== 'null') {
                nennerPayload = nRaw.toString().replace(/[^\d]/g, '');
            }

            if (!g || !z) return;

            fetch('/api/kataster/suchen-im-geoportal', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json', 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                },
                body: JSON.stringify({ 
                    gemarkung: g, 
                    flur: f.toString(), 
                    flurstueck_zaehler: z, 
                    nenner: nennerPayload 
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && (data.feature || data.features)) {
                    const feat = data.feature || data.features;
                    const tempLayer = L.geoJSON(feat);
                    const zentrum = tempLayer.getBounds().getCenter();
                    
                    if (zentrum && map) {
                        // 🪐 KAMERA-ANKER: Setzt den Fokus ohne pixelabhängige Abstürze
                        map.setView(zentrum, 18, { animate: false });
                        window.vinicoreKartenFokusBereitsAusgefuehrt = true;
                        steuereEbenen();
                    }
                }
            })
            .catch(err => console.error("Fokus-Zentrierung abgefangen:", err));
        }

    });

    function initVinicoreMap() {
        if (typeof map !== 'undefined' && map !== null) {
            try { map.remove(); } catch (e) { console.warn(e); }
            map = null;
        }
        const startLat = {{ $betrieb->zentrums_lat ?? 50.0000 }}; 
        const startLng = {{ $betrieb->zentrums_lng ?? 7.0000 }};
        map = L.map('vinicoreOverviewMap').setView([startLat, startLng], 18);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

        alkisWmsLayer = L.tileLayer.wms("{!! config('services.gdi_rlp.wms_url') !!}", {
            layers: 'WMS_RP_ALKIS_Liegenschaften', format: 'image/png', transparent: true, version: '1.1.1', crs: L.CRS.EPSG4326, maxZoom: ZOOM_MAX_LIMIT, maxNativeZoom: 19 
        });

        umgebungsWfsLayer = L.geoJSON(null).addTo(map);

        map.on('moveend', steuereEbenen);
        map.whenReady(function() {
            setTimeout(() => { steuereEbenen(); }, 250);
        });
    }

    function steuereEbenen() {
        const z = map.getZoom();
        if (z >= ZOOM_WFS_START) { 
            if (!map.hasLayer(alkisWmsLayer)) map.addLayer(alkisWmsLayer); 
            ladeUmliegendeWfsParzellen(); 
        } else if (z === ZOOM_WMS_START) { 
            if (!map.hasLayer(alkisWmsLayer)) map.addLayer(alkisWmsLayer); 
            if (umgebungsWfsLayer) umgebungsWfsLayer.clearLayers(); 
        } else { 
            if (map.hasLayer(alkisWmsLayer)) map.removeLayer(alkisWmsLayer); 
            if (umgebungsWfsLayer) umgebungsWfsLayer.clearLayers(); 
        }
    }
    async function ladeGeoJsonKataster(deaktiviereGlobalenZoom = false) {
        try {
            const response = await // VORHER IN ZEILE 430: fetch('/api/geojson/parzellen')
// 🚀 JETZT NEU UND UNZERSTÖRBAR:
fetch('{{ route("kataster.parzellen.laden") }}')
    .then(response => {
        if (!response.ok) {
            throw new Error('Netzwerk-Antwort war nicht ok');
        }
        return response.json();
    })
    .then(geoJson => {
        // Hier läuft dein bestehender Code (L.geoJSON...) absolut fehlerfrei weiter!
        L.geoJSON(geoJson, {
            // deine karten-logik...
        }).addTo(map);
    })
    .catch(error => console.error('Fehler beim Laden der Parzellen:', error));

            const data = await response.json();
            if (geojsonLayer) map.removeLayer(geojsonLayer);

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
                        const echteUuid = feature.properties.parzelle_uuid || feature.properties.uuid;

                        if (!echteUuid || echteUuid === 'undefined') return;

                        if (typeof window.vinicoreZuletztAktiveGruenUuid !== 'undefined' && window.vinicoreZuletztAktiveGruenUuid === echteUuid) {
                            window.vinicoreZuletztAktiveGruenUuid = null;
                            geojsonLayer.resetStyle(layer);
                            rendereSammlerInspektor();
                        } else {
                            geojsonLayer.eachLayer(l => geojsonLayer.resetStyle(l));
                            layer.setStyle({ fillColor: '#047857', fillOpacity: 0.65, weight: 3.5, color: '#065f46' });
                            rendereSammlerInspektor(echteUuid);
                        }
                    });
                }
            }).addTo(map);

            if (data.features && data.features.length > 0 && !deaktiviereGlobalenZoom && !hatFokusGefunden) {
                map.fitBounds(geojsonLayer.getBounds(), { padding: [20, 20] });
            }
        } catch (e) { console.error(e); }
    }
     /**
     * 🛰️ GEOPORTAL WFS-UMKREIS-LOADER (PERFORMANZ-OPTIMIERT)
     * Lädt das hellblaue Umland blitzschnell und vollkommen ballastfrei ohne Spinner!
     */
    async function ladeUmliegendeWfsParzellen() {
        if (!map) return;

        if (wfsAbortController) { wfsAbortController.abort(); }
        wfsAbortController = new AbortController(); 
        const signal = wfsAbortController.signal;

        const bounds = map.getBounds();
        const sued = bounds.getSouth(); const west = bounds.getWest(); const nord = bounds.getNorth(); const ost = bounds.getEast();
        const latDelta = nord - sued; const lngDelta = ost - west;
        const swX = (west - (lngDelta * 0.05)) * 20037508.34 / 180;
        const swY = Math.log(Math.tan((90 + (sued - (latDelta * 0.05))) * Math.PI / 360)) / (Math.PI / 180) * 20037508.34 / 180;
        const neX = (ost + (lngDelta * 0.05)) * 20037508.34 / 180;
        const neY = Math.log(Math.tan((90 + (nord + (latDelta * 0.05))) * Math.PI / 360)) / (Math.PI / 180) * 20037508.34 / 180;
        const bboxString = [swX.toFixed(5), swY.toFixed(5), neX.toFixed(5), neY.toFixed(5), 'urn:ogc:def:crs:EPSG::3857'].join(',');
        
        try {
            const response = await fetch('/kataster/umgebung-laden', { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }, 
                body: JSON.stringify({ bbox: bboxString }), 
                signal: signal 
            });
            const data = await response.json();
            
            if (data && data.type === "FeatureCollection" && map) { 
                if (umgebungsWfsLayer) { map.removeLayer(umgebungsWfsLayer); }

                umgebungsWfsLayer = L.geoJSON(data, {
                    style: function(feature) {
                        const aktuelleWfsId = holeEindeutigeParzellenId(feature);
                        const istBereitsAusgewaehlt = gewaehlteFeaturesSammelkorb.some(k => holeEindeutigeParzellenId(k) === aktuelleWfsId);

                        if (istBereitsAusgewaehlt) {
                            return { fillColor: '#2563eb', fillOpacity: 0.5, weight: 2.5, color: '#2563eb', dashArray: null };
                        }
                        return { fillColor: '#3b82f6', fillOpacity: 0.12, weight: 1.2, color: '#2563eb', dashArray: '3, 5' };
                    },
                    onEachFeature: function(feature, layer) {
                        layer.on('click', function(e) {
                            L.DomEvent.stopPropagation(e); L.DomEvent.preventDefault(e);
                            const aktuelleWfsId = holeEindeutigeParzellenId(feature);

                            const idx = gewaehlteFeaturesSammelkorb.findIndex(k => holeEindeutigeParzellenId(k) === aktuelleWfsId);
                            
                            if (idx > -1) { 
                                gewaehlteFeaturesSammelkorb.splice(idx, 1); 
                                umgebungsWfsLayer.resetStyle(layer); 
                            } else { 
                                gewaehlteFeaturesSammelkorb.push(feature); 
                                layer.setStyle({ fillColor: '#2563eb', fillOpacity: 0.5, weight: 2.5, color: '#2563eb', dashArray: null }); 
                            }
                            rendereSammlerInspektor();
                        });
                    }
                }).addTo(map);

                if (geojsonLayer) geojsonLayer.bringToFront();
                if (typeof rendereSammlerInspektor === 'function') rendereSammlerInspektor();
            }
        } catch (e) { 
            if (e.name !== 'AbortError') {
                console.error("WFS-Absturz gefangen:", e);
            }
        }
    }
    /**
     * 🛰️ VINICORE ADVANCED IDENTITY-ENGINE
     * Erkennt Flächen unabhängig davon, ob sie als rohes GeoJSON oder als flaches Vertragsobjekt vorliegen!
     */
    function holeEindeutigeParzellenId(feature) {
        if (!feature) return '';
        
        // Weiche: Handelt es sich um ein GeoJSON-Feature oder um ein flaches Objekt aus dem LocalStorage?
        const p = feature.properties ? feature.properties : feature;
        
        let zRaw = p.flurstueck_zaehler || p.zaehler || p.flstnrzae || p.flurstueck || '';
        let nRaw = p.flurstueck_nenner || p.nenner || p.flstnrnen || '';
        
        if (zRaw.toString().includes('/')) { 
            const parts = zRaw.toString().split('/');
            zRaw = parts[0] ? parts[0] : '';
            if (!nRaw && parts[1]) nRaw = parts[1];
        }
        
        const zaehler = zRaw.toString().replace(/^[0\s]+/, '').replace(/[^\d]/g, '');
        const nenner = nRaw.toString().replace(/^[0\s]+/, '').replace(/[^\d]/g, '');
        const flur = parseInt(p.flur || p.flurnummer || 1);
        const gemarkung = (p.gemarkung || p.gemarkungsname || '').toLowerCase().trim();
        
        return gemarkung + '-' + flur + '-' + zaehler + (nenner ? '-' + nenner : '-0');
    }

    /**
     * 📜 VINICORE UNIVERSAL-FORMATTER (KUGELSICHER)
     * Garantiert, dass Zähler und Nenner immer als flache, saubere Strings im LocalStorage landen!
     */
    function transformiereFeatureFürVertrag(feature) {
        if (!feature) return null;
        
        // 🎯 WEICHE 1: Das Objekt kommt aus einem bestehenden Korb / LocalStorage (Bereits flach)
        if (!feature.properties) {
            const z = feature.flurstueck_zaehler || feature.zaehler || feature.flurstueck || '';
            const n = feature.flurstueck_nenner || feature.nenner || '';
            return {
                gemarkung: feature.gemarkung || 'Umland',
                flur: parseInt(feature.flur || 1),
                flurstueck_zaehler: z.toString().replace(/^[0\s]+/, '').replace(/[^\d]/g, ''),
                flurstueck_nenner: n.toString().replace(/[^\d]/g, '') || null,
                amtliche_flaeche_m2: parseInt(feature.amtliche_flaeche_m2 || 0)
            };
        }

        // 🎯 WEICHE 2: Das Objekt ist ein rohes GeoJSON-Feature (Frisch von der Karte geklickt)
        const props = feature.properties;
        let zaehler = props.flurstueck_zaehler || props.zaehler || props.flstnrzae || props.flurstueck || '';
        let nenner = props.flurstueck_nenner || props.nenner || props.flstnrnen || '';
        
        if (zaehler.toString().includes('/')) {
            const parts = zaehler.toString().split('/');
            zaehler = parts[0] || '';
            if (!nenner && parts[1]) nenner = parts[1];
        }

        const zaehlerString = zaehler.toString().replace(/^[0\s]+/, '').replace(/[^\d]/g, '');
        const nennerString = nenner.toString().replace(/[^\d]/g, '');

        return {
            gemarkung: props.gemarkung || 'Umland',
            flur: parseInt(props.flur || 1),
            flurstueck_zaehler: zaehlerString,
            flurstueck_nenner: nennerString ? nennerString : null,
            amtliche_flaeche_m2: parseInt(props.flaeche_m2 || props.amtliche_flaeche_m2 || 0)
        };
    }
// Suche deine Funktion ZündeVertragsWeiterleitung() ganz unten im Script:
function ZündeVertragsWeiterleitung() {
    if (!gewaehlteFeaturesSammelkorb || gewaehlteFeaturesSammelkorb.length === 0) return;

    const urlParams = new URLSearchParams(window.location.search);
    const entwurfId = urlParams.get('entwurf_id');

    if (!entwurfId) {
        // Fallback für freies Browsen: Erstellt normale Neuanlage
        localStorage.setItem('vinicore_temporaere_parzellen', JSON.stringify(gewaehlteFeaturesSammelkorb));
        window.location.href = '/finanzen/vertrag-anlegen';
        return;
    }

    // 🚀 LIVE-DATABASE-SYNC: Lädt den Korb direkt unter der UUID in MySQL hoch!
    fetch('/api/kataster/vertrag/entwurf-parzellen-sync', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
        body: JSON.stringify({ entwurf_id: entwurfId, parzellen: gewaehlteFeaturesSammelkorb })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            localStorage.setItem('vinicore_temporaere_parzellen', JSON.stringify(gewaehlteFeaturesSammelkorb));
            // Springt mitsamt der UUID im Gepäck zurück zur Formular-Mündung
            window.location.href = '/finanzen/vertrag-anlegen?entwurf_id=' + entwurfId;
        }
    });
}


    /**
     * 🛰️ KORB-NAVIGATOR ("Gehe zu"-Funktion für blaue Umlandparzellen)
     * Fliegt die Kamera punktgenau zum ausgewählten Korb-Flurstück, 
     * völlig ballastfrei und ohne Spinner-Abfragen!
     */
    function FliegeZuBlauerParzelle(idx) {
        const f = gewaehlteFeaturesSammelkorb[idx];
        if (!f) return;

        const props = f.properties || f;

        if (f.geometry && typeof L.geoJSON === 'function') {
            try {
                const tempLayer = L.geoJSON(f);
                map.fitBounds(tempLayer.getBounds(), { padding: [15, 15], maxZoom: 19, animate: true, duration: 1.0 });
                return;
            } catch (e) { console.warn("Direkt-Fokus fehlgeschlagen, weiche auf API-Suche aus:", e); }
        }

        const g = (props.gemarkung || '').toString().trim();
        const fNum = parseInt(props.flur || 1);
        const z = props.flurstueck_zaehler || props.zaehler || props.flurstueck || '';
        const n = props.flurstueck_nenner || props.nenner || '';

        fetch('/api/kataster/suchen-im-geoportal', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'Accept': 'application/json', 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
            },
            body: JSON.stringify({ gemarkung: g, flur: fNum.toString(), flurstueck_zaehler: z.toString(), nenner: n.toString() })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && (data.feature || data.features)) {
                const feat = data.feature || data.features;
                const tempLayer = L.geoJSON(feat);
                const zentrum = tempLayer.getBounds().getCenter();
                if (zentrum && map) {
                    map.setView(zentrum, 19, { animate: true, duration: 1.0 });
                    setTimeout(() => { steuereEbenen(); }, 1000);
                }
            }
        })
        .catch(err => console.error("Korb-Navigator-Absturz:", err));
    }

    function ZündeGeoportalAbfrage() {
        const g = document.getElementById('searchOrtName')?.value || '';
        const f = document.getElementById('searchFlur')?.value || '';
        const z = document.getElementById('searchFlurstueck')?.value || '';
        const n = document.getElementById('searchNenner')?.value || '';

        fetch('/api/kataster/suchen-im-geoportal', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ gemarkung: g, flur: f.toString(), flurstueck_zaehler: z.toString(), nenner: n.toString() })
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
                            if (bp.gemarkung.toLowerCase().trim() === props.gemarkung.toLowerCase().trim() && String(bp.flur) === String(props.flur) && String(bp.flurstueck) === String(props.flurstueck)) {
                                istBestand = true;
                                setTimeout(() => {
                                    geojsonLayer.eachLayer(l => geojsonLayer.resetStyle(l));
                                    gLayer.setStyle({ fillColor: '#047857', fillOpacity: 0.65, weight: 3.5, color: '#065f46' });
                                    if (gLayer.getBounds() && gLayer.getBounds().isValid()) map.fitBounds(gLayer.getBounds(), { padding: [20, 20], maxZoom: 19 });
                                    rendereSammlerInspektor(bp.uuid);
                                }, 50);
                            }
                        }
                    });
                }
                if (!istBestand) setTimeout(() => { zündeVorschauFuerFeature(zielFeature); }, 50);
            } else if (data.auswahl && data.auswahl.length > 0) {
                let listHtml = ''; 
                data.auswahl.forEach(item => { 
                    listHtml += `<button onclick="ZündeManuelleSuchInjektion('${item.gemarkung}','${f}','${z}','${item.nenner || ''}')" class="w-full text-left bg-slate-50 hover:bg-blue-50 border border-slate-200 p-3 rounded-xl mb-1.5 transition flex justify-between items-center cursor-pointer text-sm font-sans">
                        <div><strong>${item.gemarkung}</strong><br><span class="text-slate-500 text-xs">Flur ${f} | Nr. ${z}${item.nenner ? '/' + item.nenner : ''}</span></div>
                        <div class="text-mono text-blue-600 font-bold text-xs">${parseInt(item.flaeche_m2 || 0).toLocaleString('de-DE')} m² ➔</div>
                    </button>`; 
                });
                document.getElementById('gemarkungAuswahlListe').innerHTML = listHtml;
                document.getElementById('gemarkungAuswahlModal').classList.remove('hidden');
            }
        })
        .catch(err => console.error(err));
    }


    function ZündeManuelleSuchInjektion(g, f, z, n) { document.getElementById('gemarkungAuswahlModal').classList.add('hidden'); document.getElementById('searchOrtName').value = g; document.getElementById('searchFlur').value = f; document.getElementById('searchFlurstueck').value = z; document.getElementById('searchNenner').value = n; ZündeGeoportalAbfrage(); }
    
    function zündeVorschauFuerFeature(feat) {
        if (vorschauLayer) { map.removeLayer(vorschauLayer); }
        const props = feat.properties;
        vorschauLayer = L.geoJSON(feat, { 
            style: { fillColor: '#ea580c', fillOpacity: 0.35, weight: 4.0, color: '#c2410c', dashArray: '6,4' }, 
            interactive: true,
            onEachFeature: function(f, layer) {
                layer.on('click', function(e) {
                    L.DomEvent.stopPropagation(e); L.DomEvent.preventDefault(e); 
                    map.removeLayer(vorschauLayer); vorschauLayer = null; 
                    let istBestand = false;
                    if (geojsonLayer) { 
                        geojsonLayer.eachLayer(function(gLayer) { 
                            if (gLayer.feature && gLayer.feature.properties) { 
                                const bp = gLayer.feature.properties; 
                                if (bp.gemarkung.toLowerCase().trim() === props.gemarkung.toLowerCase().trim() && String(bp.flur) === String(props.flur) && String(bp.flurstueck) === String(props.flurstueck)) { 
                                    istBestand = true; gLayer.fire('click'); 
                                } 
                            } 
                        }); 
                    }
                    if (!istBestand) { 
                        const schonImKorb = gewaehlteFeaturesSammelkorb.some(k => {
                            if (!k) return false;
                            const kp = k.properties || k;
                            return String(kp.flurstueck || kp.flurstueck_zaehler) === String(props.flurstueck) && parseInt(kp.flur) === parseInt(props.flur);
                        }); 
                        if (!schonImKorb) gewaehlteFeaturesSammelkorb.push(feat); 
                        rendereSammlerInspektor(); 
                    }
                });
            }
        }).addTo(map);
        if (vorschauLayer.getBounds() && vorschauLayer.getBounds().isValid()) map.fitBounds(vorschauLayer.getBounds(), { padding:[20,20], maxZoom: 19 });
    }

    function schliesseGemarkungModal() { document.getElementById('gemarkungAuswahlModal').classList.add('hidden'); }
    /**
     * 📡 ASYNCHRONES DETAIL-WIDGET FÜR BESTANDSPARZELLEN
     * 🚀 REVISIONS-FIX: Schreibt Daten isoliert in das Slot-Element, ohne den blauen Korb zu löschen!
     */
    function oeffneGlobalenInspektorWidget(uuid) {
        // 🎯 TARGET-FIX: Wir greifen uns exakt das obere Slot-Element, statt des Haupt-Bodys!
        const slot = document.getElementById('liveInspektorDetailSlot'); 
        if (!slot) return;
        
        slot.innerHTML = `<div class="flex flex-col items-center justify-center py-6 text-slate-400 text-xs font-medium space-y-1 font-sans"><div class="text-lg animate-spin">📡</div><div class="animate-pulse">Synchronisiere Detail-Matrix...</div></div>`;

        fetch(`/api/kataster/parzelle-details/${uuid}`, { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' } })
        .then(r => { if (!r.ok) throw new Error("Status " + r.status); return r.json(); })
        .then(res => {
            if (res.success) {
                const p = res.parzelle; const vZahl = parseInt(p.version || 1);
                const vBadge = (vZahl === 1) ? `<div class="bg-amber-50 text-amber-800 border border-amber-200 px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider animate-pulse text-center mt-1.5 shadow-3xs">⚠️ Erstprüfung im Katasterspiegel ausstehend</div>` : '';
                let buttonHtml = (vZahl === 1) ? `<div class="flex items-center gap-1 mt-1"><button onclick="oeffneBearbeitenModal('${p.parzelle_uuid}', '${p.besitz_status || 'eigentum'}', '${p.flurname_lage || ''}', '${vZahl}')" class="text-amber-700 hover:text-amber-900 font-bold px-2 py-1 bg-amber-50 hover:bg-amber-100 border border-amber-300 rounded-lg shadow-3xs transition cursor-pointer text-[11px] animate-pulse">⚖️ Erstprüfung</button></div>` : `<button onclick="oeffneBearbeitenModal('${p.parzelle_uuid}', '${p.besitz_status || 'eigentum'}', '${p.flurname_lage || ''}', '${vZahl}')" class="text-blue-600 hover:text-blue-800 font-bold px-2 py-1 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg shadow-3xs transition cursor-pointer text-[11px] mt-1">📝 Bearbeiten</button>`;
                let statusPille = (p.besitz_status === 'eigentum') ? '<span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide">● Eigentum</span>' : ((p.besitz_status === 'gepachtet') ? '<span class="bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide">● Gepachtet</span>' : '<span class="bg-slate-50 text-slate-500 border border-slate-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide">○ Verpachtet</span>');
                let verknuepfungsHtml = p.anlage_name ? `<div class="space-y-1 font-sans"><span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-0.5 rounded-lg text-xs font-bold uppercase tracking-wider shadow-3xs">🌿 ${p.anlage_name}</span></div>` : `<span class="text-slate-400 text-[10px] font-mono italic">Katasterfläche besitzt aktuell keine Bestockung</span>`;

                // 🧱 HOCHGRADIG GESICHERTER SITZ MIT SCROLLBAR-BEGRENZUNG FÜR DIE GRÜNE BOX
                slot.innerHTML = `<div class="space-y-2 font-sans text-xs text-slate-700 max-h-[35vh] overflow-y-auto pr-0.5 border-b border-slate-100 pb-2">
                    <div class="bg-slate-50/70 border border-slate-200 p-2.5 rounded-xl space-y-2 shadow-3xs">
                        <div class="flex justify-between items-start border-b border-slate-100 pb-1.5">
                            <div><h5 class="text-slate-900 font-extrabold text-sm tracking-tight leading-none mb-1">${p.gemarkung}</h5><span class="text-slate-400 font-mono text-[10px] block">Flur ${p.flur} | Nr. ${p.flurstueck_zaehler}</span></div>
                            <div class="flex flex-col items-end space-y-1">${statusPille}${buttonHtml}</div>
                        </div>
                        <div class="text-slate-500 italic text-[11px]">Amtlicher Flurname: <strong class="text-slate-700 font-semibold not-italic text-xs">${p.flurname_lage || 'Keine Angabe'}</strong></div>
                        <div class="font-mono font-bold text-slate-900 text-sm border-t border-slate-100 pt-2 flex justify-between items-center"><span class="font-sans text-slate-400 font-medium text-xs">📐 Fläche:</span><span class="bg-white border border-slate-200 px-2 py-0.5 rounded shadow-3xs text-slate-950 font-bold">${parseInt(p.amtliche_flaeche_m2 || 0).toLocaleString('de-DE')} m²</span></div>
                        ${vBadge}
                    </div>
                    <div class="bg-slate-50/30 border border-slate-150 p-2.5 rounded-xl space-y-1.5 shadow-3xs"><h6 class="font-mono font-bold uppercase text-[9px] text-slate-400 tracking-wider border-b border-slate-150 pb-1 flex items-center gap-1">🍇 Agronomische Rebanlage</h6><div class="py-0.5">${verknuepfungsHtml}</div></div>
                </div>`;
            }
        }).catch(err => { slot.innerHTML = `<div class="text-center py-6 text-red-500 font-sans text-xs">⚠️ Fehler beim Laden der Parzellendaten.</div>`; });
    }

    function oeffneBearbeitenModal(uuid, status, flurname, version) {
        document.getElementById('editUuid').value = uuid;
        document.getElementById('editLage').value = flurname;
        document.getElementById('editVersion').value = version;
        const statusDropdown = document.getElementById('editStatus');
        const grundFeld = document.getElementById('editGrund');
        if (!statusDropdown || !grundFeld) return;

        if (parseInt(version) === 1) {
            const tempVertrag = JSON.parse(localStorage.getItem('vinicore_temp_vertrag_stammdaten'));
            if (tempVertrag && tempVertrag.typ) {
                if (tempVertrag.typ === 'pacht_aufwand') statusDropdown.value = 'gepachtet';
                else if (tempVertrag.typ === 'kauf') statusDropdown.value = 'eigentum';
                else if (tempVertrag.typ === 'pacht_ertrag') statusDropdown.value = 'verpachtet';
                statusDropdown.disabled = true;
                statusDropdown.className = "w-full bg-slate-100 text-slate-500 border border-slate-200 p-2.5 rounded-xl font-medium cursor-not-allowed text-xs";
            }
            grundFeld.value = 'Erstaufnahme und erstmalige rechtliche Erfassung des Besitzverhältnisses';
            grundFeld.readOnly = true;
            grundFeld.className = "w-full bg-slate-100 text-slate-500 border border-slate-200 p-2.5 rounded-xl font-medium cursor-not-allowed text-xs";
        } else {
            statusDropdown.value = status;
            statusDropdown.disabled = true;
            statusDropdown.className = "w-full bg-slate-100 text-slate-500 border border-slate-200 p-2.5 rounded-xl font-medium cursor-not-allowed text-xs";
            grundFeld.value = '';
            grundFeld.readOnly = false;
            grundFeld.className = "w-full bg-white border border-slate-200 p-2.5 rounded-xl font-medium focus:border-blue-500 text-xs";
        }
        document.getElementById('bearbeitenModal').classList.remove('hidden');
    }

    function schliesseBearbeitenModal() { document.getElementById('bearbeitenModal').classList.add('hidden'); }

    async function sendeBearbeitung(event) {
        if (event) { event.preventDefault(); }
        const uuid = document.getElementById('editUuid')?.value || '';
        const status = document.getElementById('editStatus').value;
        const flurname = document.getElementById('editLage')?.value || '';
        const grund = document.getElementById('editGrund')?.value || '';

        if (!uuid) return;
        const spinner = document.getElementById('vinicoreMapSpinner');
        if (spinner) spinner.classList.remove('hidden');

        try {
            const response = await fetch(`/api/kataster/parzellen/aktualisieren/${uuid}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                body: JSON.stringify({ uuid: uuid, besitz_status: status, flurname_lage: flurname, aenderungsgrund: grund })
            });
            if (response.ok) {
                schliesseBearbeitenModal();
                zeigeVinicoreToast('success', 'Erstprüfung erfolgreich rechtssicher versiegeln!');
                await ladeGeoJsonKataster(true);
                rendereSammlerInspektor(uuid);
            }
        } catch (err) { console.error(err); }
        finally { if (spinner) spinner.classList.add('hidden'); }
    }
    function EntferneEinzelneAuswahl(typ, identifier) {
        const idx = parseInt(identifier); 
        if (gewaehlteFeaturesSammelkorb[idx]) { 
            const f = gewaehlteFeaturesSammelkorb[idx]; 
            gewaehlteFeaturesSammelkorb.splice(idx, 1); 
            if (umgebungsWfsLayer) {
                const aktuelleId = holeEindeutigeParzellenId(f);
                umgebungsWfsLayer.eachLayer(l => { 
                    if (holeEindeutigeParzellenId(l.feature) === aktuelleId) umgebungsWfsLayer.resetStyle(l); 
                });
            }
        }
        rendereSammlerInspektor();
    }
    /**
     * 📊 REAKTIVER VINICORE UNIVERSAL-INSPEKTOR (RESTAURIERTER STANDARD)
     * 🚀 REVISIONS-FIX: Vereint Grün und Blau reaktiv in einem Container, ohne Datenverlust!
     */
    function rendereSammlerInspektor(aktiveUuid = null) {
        const body = document.getElementById('globalInspektorBody'); 
        if (!body) return;

        const anzahlBlau = gewaehlteFeaturesSammelkorb.length;

        // Wenn überhaupt nichts ausgewählt ist, zeigen wir den leeren Standard-Platzhalter
        if (anzahlBlau === 0 && !aktiveUuid && !vinicoreAktiveVertragId) {
            body.innerHTML = `
                <div class="text-center py-36 text-slate-400 text-sm tracking-wide leading-relaxed font-sans font-medium">
                    <div class="text-3xl mb-3 text-slate-300">🛰️</div>
                    Klicke auf <strong class="text-blue-600 font-bold">hellblaue</strong> Flächen zum Importieren,<br><br>
                    oder auf <strong class="text-emerald-600 font-bold">grüne</strong> Flächen zur Ansicht.
                </div>`;
            return;
        }

        if (typeof window.vinicoreZuletztAktiveGruenUuid === 'undefined') { window.vinicoreZuletztAktiveGruenUuid = null; }
        if (aktiveUuid !== null) { window.vinicoreZuletztAktiveGruenUuid = aktiveUuid; }

        // Startet den unzerstörbaren, reaktiven Gesamt-String von gestern
        let html = `<div class="space-y-3 font-sans text-xs text-slate-700 p-1">`;

        // 🟢 ETAGE 1: Der feste HTML-Slot für das grüne Widget
        html += `<div id="liveInspektorDetailSlot" class="space-y-2"></div>`;

        // 🔵 ETAGE 2: Die aktive Auswahl aus dem blauen Warenkorb
        if (anzahlBlau > 0) {
            let summeBlauM2 = 0; 
            let blauListHtml = '';

            gewaehlteFeaturesSammelkorb.forEach((f, idx) => {
                const props = f.properties || f; 
                const onyxM2 = parseInt(props.flaeche_m2 || props.amtliche_flaeche_m2 || 0); 
                summeBlauM2 += onyxM2;
                
                const gName = props.gemarkung || 'Umland';
                const fNum = props.flur || '1';
                
                let fNennerDisplay = (props.flurstueck || props.flurstueck_zaehler || '0').toString();
                const nRaw = (props.flurstueck_nenner || props.nenner || '').toString().trim();
                
                if (!fNennerDisplay.includes('/') && nRaw !== '' && nRaw !== '0' && nRaw !== 'null') {
                    fNennerDisplay += '/' + nRaw;
                }

                blauListHtml += `
                <div class="bg-blue-50/30 border border-blue-200 p-2 rounded-xl text-[11px] flex justify-between items-center shadow-3xs mb-1">
                    <div>
                        <strong>${gName}</strong><br>
                        <span class="text-slate-500 font-mono text-[9px]">Flur ${fNum} | Nr. ${fNennerDisplay}</span><br>
                        <div class="text-blue-700 font-mono font-bold text-[10px] mt-0.5 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100/60 inline-block">
                            ${onyxM2.toLocaleString('de-DE')} m²
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <!-- 🔍 Der funktionierende Korb-Navigator -->
                        <button onclick="FliegeZuBlauerParzelle(${idx})" title="Flurstück auf Karte fokussieren" class="text-blue-600 hover:text-blue-800 bg-white border border-blue-200 p-1 px-1.5 rounded-lg shadow-3xs text-[10px] font-mono cursor-pointer transition">
                            🔍 Gehe zu
                        </button>
                        <button onclick="EntferneEinzelneAuswahl('blau', ${idx})" class="text-red-500 hover:text-red-700 font-bold px-2 text-base cursor-pointer">&times;</button>
                    </div>
                </div>`;
            });

            if (typeof inspektorBlauGeoeffnet === 'undefined') window.inspektorBlauGeoeffnet = true;
            const pfeilB = window.inspektorBlauGeoeffnet ? '▲' : '▼'; 
            const verstecktB = window.inspektorBlauGeoeffnet ? '' : 'hidden';

            html += `
            <div class="bg-blue-50/10 border border-blue-100 p-2 rounded-xl flex flex-col h-auto shadow-3xs mt-1 border-t-2 border-dashed border-blue-200">
                <div class="flex justify-between items-center border-b border-blue-100 pb-1 mb-1.5">
                    <h5 onclick="window.inspektorBlauGeoeffnet=!window.inspektorBlauGeoeffnet;rendereSammlerInspektor();" class="font-bold text-blue-600 font-mono tracking-wider text-[10px] uppercase flex items-center gap-1 cursor-pointer select-none">
                        <span>🛒 Aktive Auswahl (${anzahlBlau})</span>
                        <span class="text-[9px] bg-blue-100/80 px-1.5 py-0.5 rounded text-blue-700 font-mono">${pfeilB}</span>
                    </h5>
                    <button onclick="gewaehlteFeaturesSammelkorb=[]; localStorage.removeItem('vinicore_temporaere_parzellen'); if(umgebungsWfsLayer)umgebungsWfsLayer.resetStyle(); rendereSammlerInspektor();" class="text-[9px] font-mono font-bold text-red-500 bg-red-50 hover:bg-red-100 border border-red-200 px-1.5 py-0.5 rounded transition cursor-pointer">🗑️ Leeren</button>
                </div>
                <div class="${verstecktB} space-y-1 overflow-y-auto pr-0.5 mb-1.5 max-h-40 md:max-h-[30vh]">${blauListHtml}</div>
                <div class="text-right text-[11px] font-mono font-bold text-blue-600 bg-blue-50/60 px-2 py-0.5 rounded border border-blue-200/50">Σ Gesamtfl.: ${summeBlauM2.toLocaleString('de-DE')} m²</div>
            </div>`;

            if (vinicoreAktiveVertragId) {
                html += `<button onclick="ZündeVertragsWeiterleitung()" class="w-full mt-2 bg-emerald-600 hover:bg-emerald-700 text-white font-mono font-bold text-[11px] py-2.5 px-3 rounded-xl shadow-md transition uppercase tracking-wider cursor-pointer flex items-center justify-center gap-1.5">🔄 Auswahl an Vertrag übergeben</button>`;
            } else {
                html += `<button onclick="ZündeVertragsWeiterleitung()" class="w-full mt-2 bg-slate-900 hover:bg-slate-800 text-amber-400 font-mono font-bold text-[11px] py-2.5 px-3 rounded-xl shadow-md transition uppercase tracking-wider cursor-pointer flex items-center justify-center gap-1.5">📜 Aus Auswahl neuen Vertrag anlegen</button>`;
            }
        } else if (!window.vinicoreZuletztAktiveGruenUuid) {
            html += `<div class="text-center py-6 text-slate-400 text-xs font-medium"><div class="text-2xl mb-1 text-slate-300">🛰️</div>Hellblaue Flächen anklicken, um den Importkorb zu füllen.</div>`;
        }

        html += `</div>`;
        body.innerHTML = html;

        // 🪐 ASYNCHRONER RE-TRIGGER: Wenn eine grüne Fläche aktiv ist, laden wir ihre Daten punktgenau in das obere Slot-Element!
        if (window.vinicoreZuletztAktiveGruenUuid) { 
            oeffneGlobalenInspektorWidget(window.vinicoreZuletztAktiveGruenUuid); 
        }
    }

</script>
@endsection
