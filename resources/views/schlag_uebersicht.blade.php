<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>vinicore - Schlagübersicht</title>
    <script src="{{ asset('tailwind.js') }}"></script>
    <style>
        .map-scroll::-webkit-scrollbar { height: 6px; }
        .map-scroll::-webkit-scrollbar-track { background: #090d16; }
        .map-scroll::-webkit-scrollbar-thumb { background: #1e293b; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 font-sans antialiased p-6">

    <div class="w-full max-w-full mx-auto space-y-4">
        
        <!-- Header -->
        <header class="flex justify-between items-center bg-slate-900/60 px-5 py-3 rounded-lg border border-slate-800 shadow-sm">
            <div>
                <h1 class="text-base font-bold tracking-tight text-white">vinicore <span class="text-slate-400 font-normal">| Weinberg-Vogelperspektive (Schlag 1)</span></h1>
            </div>
            <a href="/" class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-xs py-1 px-3 rounded transition">
                Haupt-Dashboard
            </a>
        </header>

        <!-- Die digitale Weinberg-Karte -->
        <main class="bg-slate-900/40 p-5 rounded-lg border border-slate-800/80 shadow-sm space-y-4">
            
            <div class="text-[10px] uppercase font-mono tracking-wider text-slate-500 mb-2">
                Echtzeit-Strukturkarte (Maßstabsgetreuer Zeilenversatz)
            </div>

            <!-- Scrollbarer Gitterraum für den gesamten Hang -->
            <div class="w-full overflow-x-auto map-scroll pb-4 pt-2">
                <div id="weinbergKarte" class="flex flex-col gap-2 p-4 bg-slate-950/80 rounded-lg border border-slate-900 min-h-[300px]">
                    <div class="text-slate-600 text-xs italic m-auto animate-pulse">Generiere digitale Flurkarte aus Rebanlagen-Topologie...</div>
                </div>
            </div>

            <!-- Technische Legende (Kompakt) -->
            <div class="flex flex-wrap gap-x-6 gap-y-2 pt-2 border-t border-slate-800/60 text-[10px] uppercase font-mono tracking-wider text-slate-500">
                <div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 bg-emerald-600"></div> Rebe Vital</div>
                <div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 bg-red-600"></div> Fehlstelle</div>
                <div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 bg-blue-600"></div> Jungrebe</div>
                <div class="flex items-center gap-1.5"><div class="w-0.5 h-4 bg-slate-500"></div> Pfahl</div>
                <div class="flex items-center gap-1.5"><div class="w-2 h-4 border border-dashed border-slate-800 bg-slate-900/40"></div> Querweg / Fahrgasse</div>
            </div>
        </main>
    </div>

    <!-- Die leichtgewichtige Karten-Engine -->
    <script>
        document.addEventListener("DOMContentLoaded", async () => {
            try {
                const response = await fetch('/api/matrix/1/topologie');
                const result = await response.json();
                
                if (result.success) {
                    zeichneWeinbergKarte(result.topologie);
                }
            } catch (error) {
                console.error("Fehler beim Laden der Weinberg-Topologie:", error);
            }
        });

        function zeichneWeinbergKarte(topologie) {
            const karte = document.getElementById('weinbergKarte');
            if (!karte) return;
            karte.innerHTML = '';

            topologie.forEach(zeile => {
                // Erzeugt die Zeilen-Zeile im Hang
                const rowLine = document.createElement('div');
                rowLine.className = 'flex items-center gap-0 h-6 min-w-max hover:bg-slate-900/40 rounded transition-colors pr-4';

                // Kleiner Zeilen-Indikator ganz links
                const label = document.createElement('div');
                label.className = 'w-10 text-[10px] font-mono text-slate-600 font-bold';
                label.innerText = `Z: ${zeile.zeile_nummer}`;
                rowLine.appendChild(label);

                // Schleife durch alle vermessenen Teilstücke
                zeile.verlauf.forEach(el => {
                    const block = document.createElement('div');
                    block.className = 'relative flex items-center justify-center';
                    
                    // Skalierungsfaktor für die Gesamtübersicht (etwas enger als im Planer: 1cm = 0.5px)
                    const cmAbstand = el.abstand_cm || 100;
                    block.style.width = `${cmAbstand * 0.5}px`;

                    const node = document.createElement('div');

                    if (el.typ === 'rebe') {
                        // Rebe als extrem minimalistischer, kompakter Datenpunkt
                        node.className = 'w-2 h-2 rounded-xs border shadow-xs';
                        if (el.status === 'fehlstelle') {
                            node.className += ' bg-red-600 border-red-500';
                            block.title = `Zeile ${zeile.zeile_nummer}: Fehlstelle`;
                        } else if (el.status === 'nachgepflanzt') {
                            node.className += ' bg-blue-600 border-blue-500';
                            block.title = `Zeile ${zeile.zeile_nummer}: Jungrebe [${el.sorte}]`;
                        } else {
                            node.className += ' bg-emerald-600 border-emerald-500';
                            block.title = `Zeile ${zeile.zeile_nummer}: Vitaler Stock [${el.sorte}]`;
                        }
                    } 
                    else if (el.typ === 'reihenpfahl' || el.typ === 'endpfahl') {
                        node.className = el.typ === 'endpfahl' ? 'w-0.5 h-4 bg-slate-400' : 'w-px h-3 bg-slate-600';
                    } 
                    else if (el.typ === 'anker') {
                        node.className = 'w-1 h-1 bg-amber-600 rounded-full';
                    }
                    else if (el.typ === 'querweg') {
                        // Der Querweg schneidet eine echte, befahrbare Lücke in das visuelle Muster
                        node.className = 'h-4 w-full border-y border-dashed border-slate-800 bg-slate-900/30 text-[8px] font-mono text-slate-600 flex items-center justify-center';
                        node.innerText = '||';
                    }
                    else {
                        // Wendeplätze
                        node.className = 'h-3 w-full border-b border-dashed border-slate-900/40 bg-slate-950/20';
                    }

                    block.appendChild(node);
                    rowLine.appendChild(block);
                });

                // Klick auf die Zeilenzeile springt zurück in den Planer
                rowLine.style.cursor = 'pointer';
                // KORREKTUR in schlag_uebersicht.blade.php: Leitet jetzt zum neuen Unterpfad weiter
                rowLine.onclick = () => {
                    window.location.href = `/matrix-planer?zeile=${zeile.zeile_nummer}`;
                };
                karte.appendChild(rowLine);
            });
        }
    </script>
</body>
</html>
