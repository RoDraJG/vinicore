@extends('layouts.app')

@section('content')
<!-- 🚀 ENTERPRISE AKTE: h-full bindet sich fehlerfrei an die globale Flex-Struktur an -->
<div class="w-full flex-1 flex flex-col min-w-0 bg-bg-surface border border-border-main rounded-2xl shadow-3xs overflow-hidden h-full">
    
    <!-- 🎛️ Akten-Header mit reaktivem Inspektor-Trigger -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-bg-surface border-b border-border-main flex-shrink-0">
        <div>
            <a href="{{ route('crm.index') }}" class="text-[10px] font-mono font-bold tracking-wider text-text-muted hover:text-accent-brand no-underline transition-colors uppercase">
                ← Zurück zum Register
            </a>
            <h1 class="text-sm font-mono font-bold tracking-wider text-text-main uppercase mt-1 mb-0 flex items-center gap-2">
                @if($kontakt->ist_kunde) <span class="text-emerald-600">🍷 K-{{ $kontakt->kundennummer }}</span> @else <span class="text-text-muted">📦 L-{{ $kontakt->lieferantennummer }}</span> @endif
                <span>{{ $kontakt->nachname }}{{ $kontakt->vorname ? ', ' . $kontakt->vorname : '' }}</span>
            </h1>
        </div>

        <!-- Rechte Flanke: Administrative Schnell-Aktionen -->
        <div class="flex items-center gap-2">
            <button type="button"
                    onclick="LadeAushilfsInspektor()"
                    class="bg-bg-input hover:bg-border-main text-text-main font-mono text-xs font-semibold px-4 py-2 rounded-xl transition shadow-3xs cursor-pointer border border-border-main whitespace-nowrap">
                📊 Blitz-Analyse
            </button>
                <!-- 🎯 WORKFLOW-UPGRADE: Signalisiert der Bearbeitungsmaske, dass wir aus den Details kommen -->
                <a href="{{ route('crm.edit', $kontakt->id) }}?ref=show" class="bg-slate-900 hover:bg-slate-800 text-white font-mono text-xs font-semibold px-4 py-2 rounded-xl transition shadow-3xs text-center whitespace-nowrap no-underline border-0">
                    ✏️ Bearbeiten
                </a>
        </div>
    </div>

    <!-- 🏛️ Der Akten-Korpus (Scrollbarer Inhalts-Kanal) -->
    <div class="flex-1 overflow-y-auto min-h-0 p-3 md:p-4 bg-bg-base/20">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            
                <!-- Karte 1: Anschriften & Kontakt (Repariert für getrennte Felder) -->
                <div class="bg-bg-surface border border-border-main rounded-2xl shadow-3xs p-4 space-y-4">
                    
                    <!-- 🛡️ HIER INTEGRIERT: Rote Notbremsen-Warnung bei aktiver Liefersperre -->
                    @if($kontakt->ist_gesperrt)
                        <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-800 font-mono font-bold uppercase tracking-wider animate-pulse flex items-center gap-2">
                            <span>🚫 LIEFERSperre AKTIV: Dieser Partner ist für jeglichen Warenversand gesperrt!</span>
                        </div>
                    @endif

                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">🏠 Rechnungsanschrift</label>
                        <div class="text-xs font-semibold text-text-main">
                            @if($kontakt->firma) <div class="text-xs font-bold mb-0.5 text-accent-brand">🏢 {{ $kontakt->firma }}</div> @endif
                            @if($kontakt->ansprechpartner_name) <div class="text-[11px] text-text-muted mb-1 font-mono">👤 Ansprechpartner: {{ $kontakt->ansprechpartner_name }}</div> @endif
                            <div>{{ $kontakt->strasse }} {{ $kontakt->hausnummer }}{{ $kontakt->adresszusatz ? ' (' . $kontakt->adresszusatz . ')' : '' }}</div>
                            <div class="font-mono text-[11px] text-text-muted mt-0.5">{{ $kontakt->plz }} {{ $kontakt->ort }}</div>
                        </div>
                    </div>

                    @if($kontakt->liefer_strasse)
                        <hr class="border-border-main/60">
                        <div>
                            <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">📦 Abweichende Lieferanschrift</label>
                            <div class="text-xs font-semibold text-text-main">
                                <div>{{ $kontakt->liefer_strasse }} {{ $kontakt->liefer_hausnummer }}{{ $kontakt->liefer_adresszusatz ? ' (' . $kontakt->liefer_adresszusatz . ')' : '' }}</div>
                                <div class="font-mono text-[11px] text-text-muted mt-0.5">{{ $kontakt->liefer_plz }} {{ $kontakt->liefer_ort }}</div>
                            </div>
                        </div>
                    @endif

                    <hr class="border-border-main/60">
                    
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">✉️ Digitale Erreichbarkeit</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 font-mono text-[11px]">
                            <div class="bg-bg-input p-2 rounded-xl border border-border-main/50 truncate">
                                <span class="text-text-muted">E-Mail:</span> <span class="text-text-main font-medium">{{ $kontakt->email ?? '-' }}</span>
                            </div>
                            <div class="bg-bg-input p-2 rounded-xl border border-border-main/50 truncate">
                                <span class="text-text-muted">Telefon:</span> <span class="text-text-main font-medium">{{ $kontakt->telefon ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Karte 2: Versandsteuerung & Logistik-Routing -->
                <div class="bg-bg-surface border border-border-main rounded-2xl shadow-3xs p-4 space-y-4">
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">🚛 Versand- & Zollkonditionen</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div class="border border-border-main/60 p-3 rounded-xl bg-bg-base/30">
                                <div class="text-[9px] font-mono uppercase text-text-muted tracking-wider font-semibold">Frachtbasis</div>
                                <div class="font-bold text-text-main mt-0.5">
                                    @if(($kontakt->lieferbedingungen ?? '') === 'frei_haus') 🚚 Frei Haus @elseif(($kontakt->lieferbedingungen ?? '') === 'dhl') 📦 Paketdienst @else 🏡 Ab Hof @endif
                                </div>
                            </div>
                            <div class="border border-border-main/60 p-3 rounded-xl bg-bg-base/30">
                                <div class="text-[9px] font-mono uppercase text-text-muted tracking-wider font-semibold">Standard-Logistiker</div>
                                <div class="font-bold text-text-main mt-0.5 uppercase font-mono text-[11px]">
                                    {{ $kontakt->versanddienstleister ?? 'DHL Paket' }}
                                </div>
                            </div>
                        </div>
                        @if($kontakt->speditions_hinweis)
                            <div class="mt-3 p-2.5 bg-purple-50/60 border border-purple-100 rounded-xl text-[11px] text-purple-800 font-medium font-mono">
                                ⚠️ Speditionshinweis: {{ $kontakt->speditions_hinweis }}
                            </div>
                        @endif
                    </div>
                </div>
            </div> <!-- Schließt den linken Hauptflügel -->
            <!-- Rechte Flanke: Finanzkonditionen, DATEV & Bankdaten (Ein Drittel Breite) -->
            <div class="space-y-4">
                <div class="bg-bg-surface border border-border-main rounded-2xl shadow-3xs p-4 space-y-4">
                    
                    <!-- Sektion: DATEV & Konditionen -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">📊 Buchhaltung & DATEV</label>
                        <div class="bg-bg-input p-3 rounded-xl border border-border-main/50 space-y-1.5 font-mono text-[11px] mb-3">
                            <div class="flex justify-between">
                                <span class="text-text-muted">Kategorie:</span>
                                <span class="font-bold text-text-main uppercase text-[10px]">
                                    @if(($kontakt->kunden_kategorie ?? '') === 'gastro') 🍽️ Gastro @elseif(($kontakt->kunden_kategorie ?? '') === 'handel') 🛒 Handel @else 🍷 Privat @endif
                                </span>
                            </div>
                            <div class="flex justify-between border-t border-border-main/40 pt-1.5">
                                <span class="text-text-muted">DATEV Debitor:</span>
                                <span class="font-bold text-emerald-600">{{ $kontakt->debitorennummer ?? 'Nicht hinterlegt' }}</span>
                            </div>
                            <div class="flex justify-between border-t border-border-main/40 pt-1.5">
                                <span class="text-text-muted">DATEV Kreditor:</span>
                                <span class="font-bold text-text-main">{{ $kontakt->kreditorennummer ?? 'Nicht hinterlegt' }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-center font-mono text-[11px]">
                            <div class="border border-border-main/60 p-2 rounded-xl bg-bg-base/30">
                                <div class="text-[9px] uppercase text-text-muted tracking-wider">Zahlungsziel</div>
                                <div class="font-bold text-text-main mt-0.5">{{ $kontakt->standard_zahlungsziel_tage }} Tage</div>
                            </div>
                            <div class="border border-border-main/60 p-2 rounded-xl bg-bg-base/30">
                                <div class="text-[9px] uppercase text-text-muted tracking-wider">Festrabatt</div>
                                <div class="font-bold text-emerald-600 mt-0.5">{{ number_format($kontakt->individueller_rabatt_prozent ?? 0, 2, ',', '') }}%</div>
                            </div>
                        </div>

                        @if(($kontakt->skonto_prozent ?? 0) > 0)
                            <div class="mt-2.5 p-2 bg-purple-50 border border-purple-100 rounded-xl text-center font-mono text-[10px] text-purple-700 font-semibold">
                                💸 Skonto gewährt: {{ number_format($kontakt->skonto_prozent, 2, ',', '') }}% innerhalb von {{ $kontakt->skonto_tage }} Tagen
                            </div>
                        @endif
                    </div>

                    <hr class="border-border-main/60">

                    <!-- Sektion: Fiskal- & Bankdaten (SEPA) -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">🏦 Fiskal- & Bankdaten (SEPA)</label>
                        <div class="space-y-2 font-mono text-[11px]">
                            @if($kontakt->ust_id)
                                <div class="flex flex-col bg-bg-base p-2 rounded-xl border border-border-main/40">
                                    <span class="text-[9px] uppercase text-text-muted tracking-wider">USt-IdNr. (EU)</span>
                                    <span class="text-text-main font-bold mt-0.5">{{ $kontakt->ust_id }}</span>
                                </div>
                            @endif
                            @if($kontakt->steuernummer)
                                <div class="flex flex-col bg-bg-base p-2 rounded-xl border border-border-main/40">
                                    <span class="text-[9px] uppercase text-text-muted tracking-wider">Steuernummer (Inland)</span>
                                    <span class="text-text-main font-bold mt-0.5">{{ $kontakt->steuernummer }}</span>
                                </div>
                            @endif
                            
                            <div class="bg-bg-input/60 p-2.5 rounded-xl border border-border-main/40 space-y-1 text-[10px]">
                                <div><span class="text-text-muted">IBAN:</span> <span class="text-text-main font-medium font-mono tracking-tight">{{ $kontakt->iban ? substr($kontakt->iban, 0, 6) . '...' . substr($kontakt->iban, -4) : '-' }}</span></div>
                                <div class="pt-1 border-t border-border-main/20"><span class="text-text-muted">BIC:</span> <span class="text-text-main font-medium font-mono">{{ $kontakt->bic ?? '-' }}</span></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div> <!-- Schließt die rechte Flanke -->

        </div> <!-- Schließt das Grid -->
    </div> <!-- Schließt den Akten-Korpus -->
    <!-- 🍇 Sektion 4: Winzer-Notizen & Wein-Präferenzen (Über die gesamte Breite am Fuß der Akte) -->
    <div class="p-3 md:p-4 bg-bg-surface border-t border-border-main flex-shrink-0">
        <div class="bg-bg-input/40 border border-border-main rounded-2xl p-4">
            <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">🍇 Weinbau-Marketing & Interne Notizen</label>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div class="md:col-span-1 space-y-2">
                    <div class="flex justify-between items-center bg-bg-surface p-2.5 rounded-xl border border-border-main/50">
                        <span class="text-text-muted font-medium">Bevorzugte Weinstilistik:</span>
                        <span class="font-bold text-purple-700 font-mono">{{ $kontakt->bevorzugte_weinstilistik ? ucfirst($kontakt->bevorzugte_weinstilistik) : 'Keine Angabe' }}</span>
                    </div>
                    <div class="flex justify-between items-center bg-bg-surface p-2.5 rounded-xl border border-border-main/50">
                        <span class="text-text-muted font-medium">Akquise-Kanal:</span>
                        <span class="font-semibold text-text-main font-mono">{{ $kontakt->herkunft_kontakt ? ucfirst($kontakt->herkunft_kontakt) : 'Direkt / Vinothek' }}</span>
                    </div>
                </div>
                <div class="md:col-span-2 bg-bg-surface p-3 rounded-xl border border-border-main/50 font-mono text-[11px] text-text-muted leading-relaxed">
                    <span class="text-text-main font-sans font-bold block mb-1 text-xs">✍️ Winzer-Notiz:</span>
                    {{ $kontakt->notizen ?? 'Keine internen Besonderheiten oder logistischen Vermerke zu diesem Partner hinterlegt.' }}
                </div>
            </div>
        </div>
    </div>

</div>

<!-- 🧠 REAKTIVER ASSISTENT: Steuert die Management-Summary im System-Inspektor -->
<script>
    /**
     * Schiebt den Inspektor herein und rendert die Management-Zusammenfassung des Partners
     */
    function LadeAushilfsInspektor() {
        // Wir berechnen hier fiktive, aber kaufmännisch logische Demo-Werte passend zum Partner
        const istKunde = {{ $kontakt->ist_kunde ? 'true' : 'false' }};
        const name = '{{ addslashes($kontakt->nachname) }}{{ $kontakt->vorname ? ", " . addslashes($kontakt->vorname) : "" }}';
        
        const htmlInhalt = `
            <div class="space-y-4 font-sans text-xs text-text-main animate-fade-in">
                
                <!-- Sektion 1: OPOS & Bonitätswarnung -->
                <div class="space-y-1.5">
                    <div class="text-[9px] font-mono font-bold text-text-muted uppercase tracking-wider px-1">⚠️ Debitoren-Risiko &amp; Offene Posten</div>
                    <div class="p-3 ${istKunde ? 'bg-amber-50/60 border-amber-200 text-amber-900' : 'bg-bg-input border-border-main text-text-main'} border rounded-xl shadow-3xs font-mono text-[11px]">
                        <div class="flex justify-between">
                            <span>Offene Posten (OPOS):</span>
                            <span class="font-bold">${istKunde ? '248,50 €' : '0,00 €'}</span>
                        </div>
                        <div class="flex justify-between mt-1 pt-1 border-t border-border-main/20">
                            <span>Mahnstufe:</span>
                            <span class="font-bold ${istKunde ? 'text-amber-600' : ''}">${istKunde ? 'Stufe 1 (Erinnerung)' : 'Keine'}</span>
                        </div>
                    </div>
                </div>

                <!-- Sektion 2: Umsatz-Barometer -->
                <div class="space-y-1.5">
                    <div class="text-[9px] font-mono font-bold text-text-muted uppercase tracking-wider px-1">📈 Jahresumsatz &amp; Absatz</div>
                    <div class="bg-bg-surface border border-border-main rounded-xl p-3 space-y-2">
                        <div class="flex justify-between text-[11px] font-mono">
                            <span class="text-text-muted">Umsatz lfd. Jahr:</span>
                            <span class="font-bold text-emerald-600">${istKunde ? '1.420,00 €' : '-'}</span>
                        </div>
                        <!-- Visueller Fortschrittsbalken -->
                        ${istKunde ? `
                        <div class="w-full bg-bg-input rounded-full h-2 overflow-hidden border border-border-main/40">
                            <div class="bg-emerald-500 h-full rounded-full" style="width: 65%;"></div>
                        </div>
                        <div class="text-[9px] text-text-muted font-mono text-right">65% des Vorjahres-Volumens erreicht</div>
                        ` : '<div class="text-text-muted text-[10px] font-mono italic">Lieferanten-Volumen wird im Einkaufsmodul erfasst.</div>'}
                    </div>
                </div>

                <!-- Sektion 3: DATEV-Schnittstellen-Protokoll -->
                <div class="space-y-1.5">
                    <div class="text-[9px] font-mono font-bold text-text-muted uppercase tracking-wider px-1">🤖 DATEV Sync-Log</div>
                    <div class="bg-bg-surface border border-border-main rounded-xl p-2.5 font-mono text-[10px] text-text-muted space-y-1 bg-bg-base/10">
                        <div class="flex justify-between"><span>Status:</span><span class="text-emerald-600 font-bold">✔ BEREIT</span></div>
                        <div class="flex justify-between pt-1 border-t border-border-main/10"><span>Letzter Sync:</span><span>Heute, 06:12 Uhr</span></div>
                        <div class="flex justify-between pt-1 border-t border-border-main/10"><span>Übertragungs-ID:</span><span>TX_2026_92831</span></div>
                    </div>
                </div>

                <!-- Sektion 4: Logistik-Routing-Kennzahl -->
                <div class="space-y-1.5">
                    <div class="text-[9px] font-mono font-bold text-text-muted uppercase tracking-wider px-1">🗺️ Logistisches Routing</div>
                    <div class="bg-bg-surface border border-border-main rounded-xl p-2.5 text-[10px] text-text-muted leading-relaxed">
                        Dieser Partner ist der Standard-Liefertour <span class="text-text-main font-bold">„Mosel-Rhein-Main“</span> (Fahrzeug Eigenbetrieb) zugeordnet. Nächste geplante Auslieferung: Kommender Donnerstag.
                    </div>
                </div>

            </div>
        `;

        // Wir rufen die von dir vorhin umlautfrei reparierte Core-Funktion der app.blade.php auf!
        window.OeffneGlobalenInspektor('📊 Blitz-Analyse: ' + name, htmlInhalt, 'w-80');
    }
</script>

<style>
    @keyframes fadeIn { from { opacity: 0; transform: translateY(2px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fadeIn 0.15s ease-out forwards; }
</style>
@endsection
