<!-- ==========================================================================
     🛰️ GLOBAL VINICORE LIEGENSCHAFTS-INSPEKTOR (DOM-ROOT INSULATED)
     🚀 CORE-FIX: fixed + max-w-[384px] + !important zwingen das Panel in die Schranken!
     ========================================================================== -->
<div id="vinicoreGlobalInspektor" class="hidden fixed top-0 right-0 h-full w-full bg-white border-l border-slate-200 shadow-2xl flex flex-col pt-16 transform translate-x-full transition-transform duration-300 ease-in-out" style="z-index: 99999 !important; max-w: 384px !important;">
    
    <!-- Modaler Kopfbereich -->
    <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 flex-shrink-0">
        <h4 class="font-bold font-mono text-[11px] uppercase tracking-wider text-slate-500 flex items-center gap-1.5 select-none">
            📊 Liegenschafts-Inspektor
        </h4>
        <button onclick="schliesseGlobalenInspektorWidget()" class="text-slate-500 hover:text-slate-800 hover:bg-slate-100 bg-white border border-slate-200 px-3 py-1.5 rounded-xl font-sans font-bold text-xs shadow-2xs transition cursor-pointer">
            Schließen &times;
        </button>
    </div>
    
    <!-- Dynamischer Inhalts-Körper (Wird via AJAX gefüttert) -->
    <div id="globalInspektorBody" class="p-4 flex-1 overflow-y-auto space-y-4 font-sans bg-white">
        <div class="text-center py-24 text-slate-400 animate-pulse text-xs tracking-wide">
            📡 Synchronisiere Satellitendaten...
        </div>
    </div>
</div>

<script>
    /**
     * 🚀 DOM-ROOT-BREAKOUT: Reißt das Panel aus allen blockierenden Layout-Käfigen heraus
     * und pflanzt es direkt an den obersten HTML-Körper (body) der Seite!
     */
    (function() {
        document.addEventListener("DOMContentLoaded", function() {
            const panel = document.getElementById('vinicoreGlobalInspektor');
            if (panel && panel.parentElement !== document.body) {
                document.body.appendChild(panel);
            }
        });
    })();

    /**
     * Schiebt das globale Panel reaktiv von rechts vor das Kartenwerk!
     */
    function oeffneGlobalenInspektorWidget(uuid) {
        const panel = document.getElementById('vinicoreGlobalInspektor');
        const body = document.getElementById('globalInspektorBody');
        if (!panel || !body) return;

        // Sicherstellen, dass das Panel am body hängt, falls DOM-Events verzögert waren
        if (panel.parentElement !== document.body) {
            document.body.appendChild(panel);
        }

        panel.classList.remove('hidden');
        setTimeout(() => {
            panel.classList.remove('translate-x-full');
            panel.classList.add('translate-x-0');
        }, 20);

        body.innerHTML = `
            <div class="flex flex-col items-center justify-center py-24 text-slate-400 text-xs font-medium space-y-3 font-sans">
                <div class="text-2xl animate-spin">📡</div>
                <div class="animate-pulse tracking-wide">Lese parzellenspezifische Matrix ein...</div>
            </div>`;

        // 🍇 UNIVERSAL-TUNNEL: Holt sich die Daten jeder Parzelle live
        fetch(`/api/kataster/parzelle-details/${uuid}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const p = res.parzelle;
                const vBadge = (parseInt(p.version) === 1) 
                    ? `<div class="bg-amber-50 text-amber-800 border border-amber-200 px-3 py-2 rounded-xl text-[10px] font-bold uppercase tracking-wider animate-pulse text-center font-sans mt-2 shadow-2xs">⚠️ Erstprüfung im Katasterspiegel ausstehend</div>` 
                    : '';

                let statusPille = '<span class="bg-slate-50 text-slate-500 border border-slate-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide">○ Verpachtet</span>';
                if (p.besitz_status === 'eigentum') statusPille = '<span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide">● Eigentum</span>';
                if (p.besitz_status === 'gepachtet') statusPille = '<span class="bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide">● Gepachtet</span>';

                let verknuepfungsHtml = `<span class="text-slate-400 text-[11px] font-mono italic">Katasterfläche besitzt aktuell keine Bestockung</span>`;
                if (p.anlage_name) {
                    verknuepfungsHtml = `
                        <div class="space-y-1.5 font-sans">
                            <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-0.5 rounded-lg text-[11px] font-bold uppercase tracking-wider shadow-2xs">🌿 ${p.anlage_name}</span>
                            <span class="block text-[11px] text-slate-400 font-medium font-sans">🚜 Zugeordneter Großschlag: ${p.schlag_name || 'Unbekannt'}</span>
                        </div>`;
                }

                body.innerHTML = `
                    <div class="space-y-4 font-sans text-xs text-slate-700">
                        <div class="bg-slate-50/50 border border-slate-200 p-4 rounded-2xl space-y-3 shadow-2xs">
                            <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                                <div>
                                    <h5 class="text-slate-900 font-extrabold text-sm tracking-tight">${p.gemarkung}</h5>
                                    <span class="text-slate-400 font-mono text-[10px] block mt-0.5">Flur ${p.flur} | Flurstück ${p.flurstueck_zaehler}${p.flurstueck_nenner ? '/' + p.flurstueck_nenner : ''}</span>
                                </div>
                                <div class="text-right">${statusPille}</div>
                            </div>
                            <div class="text-slate-500 italic text-[11px] font-sans">Amtlicher Flurname: <strong class="text-slate-700 font-semibold not-italic">${p.flurname_lage || 'Keine Angabe'}</strong></div>
                            <div class="font-mono font-bold text-slate-900 text-xs border-t border-slate-100 pt-2.5 flex justify-between items-center">
                                <span class="font-sans text-slate-400 font-medium">📐 Amtliche Fläche:</span>
                                <span class="bg-white border px-2 py-0.5 rounded-md shadow-2xs">${parseInt(p.amtliche_flaeche_m2).toLocaleString('de-DE')} m²</span>
                            </div>
                            ${vBadge}
                        </div>

                        <div class="bg-slate-50/30 border border-slate-150 p-4 rounded-2xl space-y-2.5 shadow-2xs">
                            <h6 class="font-mono font-bold uppercase text-[9px] text-slate-400 tracking-wider border-b border-slate-150 pb-1.5 flex items-center gap-1">🍇 Agronomische Schlagkartei</h6>
                            <div class="py-1">${verknuepfungsHtml}</div>
                        </div>
                    </div>`;
            } else {
                body.innerHTML = `<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center text-red-700 font-mono text-xs shadow-2xs">❌ GDI-Schnittstellenfehler:<br><span class="block mt-1 text-[11px] font-sans text-slate-600">${res.message}</span></div>`;
            }
        })
        .catch(err => {
            body.innerHTML = `<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center text-red-700 font-mono text-xs shadow-3xs">❌ Netzwerk-Abbruch bei der Synchronisation.</div>`;
        });
    }

    function schliesseGlobalenInspektorWidget() {
        const panel = document.getElementById('vinicoreGlobalInspektor');
        if (!panel) return;
        panel.classList.remove('translate-x-0');
        panel.classList.add('translate-x-full');
        setTimeout(() => { panel.classList.add('hidden'); }, 300);
    }
</script>
