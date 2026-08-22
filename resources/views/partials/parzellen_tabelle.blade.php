<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse font-sans text-xs">
        <thead>
            <tr class="bg-slate-50 text-slate-400 uppercase font-mono tracking-wider text-[9px] border-b border-slate-200">
                <th class="p-3">Gemarkung</th>
                <th class="p-3">Flur</th>
                <th class="p-3">Flurstück-Nr.</th>
                <th class="p-3 text-right">Fläche (m²)</th>
            </tr>
        </thead>
        <tbody class="divide-y border-slate-100">
            @forelse($parzellen as $p)
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="p-3 font-semibold text-slate-800">{{ $p->gemarkung ?? 'Kataster' }}</td>
                    <td class="p-3 font-mono">Flur {{ $p->flur ?? '1' }}</td>
 <td class="p-3">
                        <span class="text-blue-600 font-mono font-bold bg-blue-50 px-1.5 py-0.5 rounded">
                            <!-- 🚀 KATASTER-ZUSAMMENBAU: Formatiert Zähler und Nenner unzerstörbar nach Weinbau-ERP-Standard -->
                            @if(!empty($p->flurstueck_nenner) && $p->flurstueck_nenner !== '0')
                                {{ $p->flurstueck_zaehler }}/{{ $p->flurstueck_nenner }}
                            @else
                                {{ $p->flurstueck_zaehler ?? '?' }}
                            @endif
                        </span>
                    </td>


                    <td class="p-3 text-right font-mono font-bold">
                        {{ isset($p->flaeche_m2) ? number_format($p->flaeche_m2, 0, ',', '.') : (isset($p->amtliche_flaeche_m2) ? number_format($p->amtliche_flaeche_m2, 0, ',', '.') : '0') }} m²
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="p-8 text-center text-slate-400 italic">💡 In dieser Kategorie sind aktuell keine registrierten Stammparzellen hinterlegt.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
