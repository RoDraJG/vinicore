@extends('layouts.app')

@section('content')
<!-- 🚀 REPARATUR: flex-col und w-full zwingen den Header, oben über die volle Breite zu laufen, nicht links! -->
<div class="h-full w-full flex flex-col min-w-0 bg-bg-base overflow-hidden">
    
    <!-- 🎛️ Modul-Header (Volle Breite am oberen Rand des ERP-Fensters) -->
    <div class="w-full flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-bg-surface border-b border-border-main flex-shrink-0">
        <div>
            <!-- 🎯 REAKTIVE HERKUNFTS-WEICHE: Springt punktgenau dorthin zurück, wo du hergekommen bist -->
            @if(request('ref') === 'index')
                <a href="{{ route('crm.index') }}" class="text-[10px] font-mono font-bold tracking-wider text-text-muted hover:text-accent-brand no-underline transition-colors uppercase">
                    ← Zurück zum Register
                </a>
            @else
                <a href="{{ route('crm.show', $kontakt->id) }}" class="text-[10px] font-mono font-bold tracking-wider text-text-muted hover:text-accent-brand no-underline transition-colors uppercase">
                    ← Zurück zur Partner-Akte
                </a>
            @endif
            <h1 class="text-sm font-mono font-bold tracking-wider text-text-main uppercase mt-1 mb-0">
                ✏️ Partner-Stammdaten modifizieren
            </h1>
        </div>
    </div>

    <!-- 📝 Das Formular-Gehäuse (Nimmt den restlichen Raum ein und scrollt bündig) -->
    <form action="{{ route('crm.update', $kontakt->id) }}?ref={{ request('ref', 'show') }}" method="POST" class="flex-1 overflow-y-auto min-h-0 p-3 md:p-4">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            
            <!-- Linker Hauptflügel: Stammdaten & Adressblöcke -->
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-bg-surface border border-border-main rounded-2xl shadow-3xs p-4 space-y-4">
                    
                    <!-- Sektion 1: Partner-Rolle festlegen -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">1. Partner-Rolle festlegen</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                            <label class="block p-3 border border-border-main rounded-xl cursor-pointer bg-bg-base/30 hover:bg-bg-input/50 transition relative">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs font-bold text-text-main">🍷 Weinkunde / Abnehmer</div>
                                        <div class="text-[10px] text-text-muted mt-0.5">Endverbraucher, Gastro, Handel</div>
                                    </div>
                                    <input type="checkbox" name="ist_kunde" value="1" {{ $kontakt->ist_kunde ? 'checked' : '' }} class="rounded border-border-main text-accent-brand focus:ring-accent-brand scale-110 cursor-pointer">
                                </div>
                            </label>
                            
                            <label class="block p-3 border border-border-main rounded-xl cursor-pointer bg-bg-base/30 hover:bg-bg-input/50 transition relative">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs font-bold text-text-main">📦 Lieferant / Erzeuger</div>
                                        <div class="text-[10px] text-text-muted mt-0.5">Flaschen, Hefe, Dienstleistungen</div>
                                    </div>
                                    <input type="checkbox" name="ist_lieferant" value="1" {{ $kontakt->ist_lieferant ? 'checked' : '' }} class="rounded border-border-main text-text-main focus:ring-slate-500 scale-110 cursor-pointer">
                                </div>
                            </label>
                        </div>
                    </div>
                    <hr class="border-border-main/60">

                    <!-- Sektion 2: Identität & Unternehmen -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">2. Identität & Unternehmen</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="firma" class="block text-xs text-text-main font-medium mb-1">Firmenname / Weingut (B2B)</label>
                                <input type="text" id="firma" name="firma" value="{{ $kontakt->firma }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">
                            </div>
                            <div>
                                <label for="ansprechpartner_name" class="block text-xs text-text-main font-medium mb-1">Ansprechpartner (Sommelier / Einkauf)</label>
                                <input type="text" id="ansprechpartner_name" name="ansprechpartner_name" value="{{ $kontakt->ansprechpartner_name }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium" placeholder="z.B. Herr Sommelier Müller">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label for="vorname" class="block text-xs text-text-main font-medium mb-1">Vorname</label>
                                <input type="text" id="vorname" name="vorname" value="{{ $kontakt->vorname }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">
                            </div>
                            <div>
                                <label for="nachname" class="block text-xs text-text-main font-medium mb-1">Nachname</label>
                                <input type="text" id="nachname" name="nachname" value="{{ $kontakt->nachname }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium" required>
                            </div>
                            <div>
                                <label for="geburtsdatum" class="block text-xs text-text-main font-medium mb-1">Geburtsdatum</label>
                                <input type="date" id="geburtsdatum" name="geburtsdatum" value="{{ $kontakt->geburtsdatum ? \Carbon\Carbon::parse($kontakt->geburtsdatum)->format('Y-m-d') : '' }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sektion 3: Rechnungsanschrift (Straße und Hausnummer physikalisch getrennt) -->
                <div class="bg-bg-surface border border-border-main rounded-2xl shadow-3xs p-4 space-y-4">
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">3. Haupt- / Rechnungsanschrift</label>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mb-3">
                            <div class="sm:col-span-2">
                                <label for="strasse" class="block text-xs text-text-main font-medium mb-1">Straße</label>
                                <input type="text" id="strasse" name="strasse" value="{{ $kontakt->strasse }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium" placeholder="z.B. Hauptstraße">
                            </div>
                            <div>
                                <label for="hausnummer" class="block text-xs text-text-main font-medium mb-1">Nr.</label>
                                <input type="text" id="hausnummer" name="hausnummer" value="{{ $kontakt->hausnummer }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium" placeholder="z.B. 12a">
                            </div>
                            <div>
                                <label for="adresszusatz" class="block text-xs text-text-main font-medium mb-1">Zusatz</label>
                                <input type="text" id="adresszusatz" name="adresszusatz" value="{{ $kontakt->adresszusatz }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium" placeholder="Abt., Etage...">
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label for="plz" class="block text-xs text-text-main font-medium mb-1">PLZ</label>
                                <input type="text" id="plz" name="plz" value="{{ $kontakt->plz }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                            <div class="col-span-2">
                                <label for="ort" class="block text-xs text-text-main font-medium mb-1">Ort</label>
                                <input type="text" id="ort" name="ort" value="{{ $kontakt->ort }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">
                            </div>
                        </div>
                    </div>

                    <hr class="border-border-main/60">

                    <!-- Sektion 4: Abweichende Lieferanschrift (Ebenfalls getrennt) -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">4. Abweichende Lieferanschrift (Optional)</label>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mb-3">
                            <div class="sm:col-span-2">
                                <label for="liefer_strasse" class="block text-xs text-text-main font-medium mb-1">Liefer-Straße / Packstation</label>
                                <input type="text" id="liefer_strasse" name="liefer_strasse" value="{{ $kontakt->liefer_strasse }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium" placeholder="z.B. Weinstraße">
                            </div>
                            <div>
                                <label for="liefer_hausnummer" class="block text-xs text-text-main font-medium mb-1">Nr.</label>
                                <input type="text" id="liefer_hausnummer" name="liefer_hausnummer" value="{{ $kontakt->liefer_hausnummer }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium" placeholder="z.B. 44">
                            </div>
                            <div>
                                <label for="liefer_adresszusatz" class="block text-xs text-text-main font-medium mb-1">Liefer-Zusatz</label>
                                <input type="text" id="liefer_adresszusatz" name="liefer_adresszusatz" value="{{ $kontakt->liefer_adresszusatz }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium" placeholder="z.B. Postnummer">
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label for="liefer_plz" class="block text-xs text-text-main font-medium mb-1">Liefer-PLZ</label>
                                <input type="text" id="liefer_plz" name="liefer_plz" value="{{ $kontakt->liefer_plz }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                            <div class="col-span-2">
                                <label for="liefer_ort" class="block text-xs text-text-main font-medium mb-1">Liefer-Ort</label>
                                <input type="text" id="liefer_ort" name="liefer_ort" value="{{ $kontakt->liefer_ort }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- Schließt den linken Flügel -->
            <!-- Rechte Flanke: Kaufmännische Weichen, DATEV & Logistik (Edit-Modus) -->
            <div class="space-y-4">
                <div class="bg-bg-surface border border-border-main rounded-2xl shadow-3xs p-4 space-y-4">
                    
                    <!-- Sektion 5: Finanzkonditionen & DATEV -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">5. Finanzkonditionen & DATEV</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="kunden_kategorie" class="block text-xs text-text-main font-medium mb-1">Kunden-Segment</label>
                                <select id="kunden_kategorie" name="kunden_kategorie" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-accent-brand">
                                    @foreach($konfig_segmente as $code => $wert)
                                        <option value="{{ $code }}" {{ $kontakt->kunden_kategorie === $code ? 'selected' : '' }}>{{ $wert }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="buchhaltung_gruppe" class="block text-xs text-text-main font-medium mb-1">DATEV Steuer-Zone</label>
                                <select id="buchhaltung_gruppe" name="buchhaltung_gruppe" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-accent-brand">
                                    @foreach($konfig_steuerzonen as $code => $wert)
                                        <option value="{{ $code }}" {{ $kontakt->buchhaltung_gruppe === $code ? 'selected' : '' }}>{{ $wert }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="debitorennummer" class="block text-xs text-text-main font-medium mb-1">DATEV Debitor</label>
                                <input type="text" id="debitorennummer" name="debitorennummer" value="{{ $kontakt->debitorennummer }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                            <div>
                                <label for="kreditorennummer" class="block text-xs text-text-main font-medium mb-1">DATEV Kreditor</label>
                                <input type="text" id="kreditorennummer" name="kreditorennummer" value="{{ $kontakt->kreditorennummer }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="standard_zahlungsziel_tage" class="block text-xs text-text-main font-medium mb-1">Ziel (Tage)</label>
                                <input type="number" id="standard_zahlungsziel_tage" name="standard_zahlungsziel_tage" value="{{ $kontakt->standard_zahlungsziel_tage }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium" required>
                            </div>
                            <div>
                                <label for="individueller_rabatt_prozent" class="block text-xs text-text-main font-medium mb-1">Rabatt (%)</label>
                                <input type="number" id="individueller_rabatt_prozent" name="individueller_rabatt_prozent" value="{{ $kontakt->individueller_rabatt_prozent }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium" step="0.01">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="skonto_prozent" class="block text-xs text-text-main font-medium mb-1">Skonto (%)</label>
                                <input type="number" id="skonto_prozent" name="skonto_prozent" value="{{ $kontakt->skonto_prozent }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium" step="0.01">
                            </div>
                            <div>
                                <label for="skonto_tage" class="block text-xs text-text-main font-medium mb-1">Skonto (Tage)</label>
                                <input type="number" id="skonto_tage" name="skonto_tage" value="{{ $kontakt->skonto_tage }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="block p-3 border border-red-200 rounded-xl cursor-pointer bg-red-50/30 hover:bg-red-50/60 transition relative">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs font-bold text-red-800">🛡️ Automatische Liefersperre</div>
                                    </div>
                                    <input type="checkbox" name="ist_gesperrt" value="1" {{ $kontakt->ist_gesperrt ? 'checked' : '' }} class="rounded border-red-300 text-red-600 focus:ring-red-500 scale-110 cursor-pointer">
                                </div>
                            </label>
                        </div>
                    </div>

                    <hr class="border-border-main/60">

                    <!-- Sektion 6: Logistik-Routing -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">6. Versand- & Zollsteuerung</label>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="lieferbedingungen" class="block text-xs text-text-main font-medium mb-1">Frachtbasis</label>
                                <select id="lieferbedingungen" name="lieferbedingungen" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-accent-brand">
                                    @foreach($konfig_incoterms as $code => $wert)
                                        <option value="{{ $code }}" {{ $kontakt->lieferbedingungen === $code ? 'selected' : '' }}>{{ $wert }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="versanddienstleister" class="block text-xs text-text-main font-medium mb-1">Logistiker</label>
                                <select id="versanddienstleister" name="versanddienstleister" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-accent-brand">
                                    @foreach($konfig_logistiker as $code => $wert)
                                        <option value="{{ $code }}" {{ $kontakt->versanddienstleister === $code ? 'selected' : '' }}>{{ $wert }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="speditions_hinweis" class="block text-xs text-text-main font-medium mb-1">Avisierungs- / Speditionshinweis</label>
                            <input type="text" id="speditions_hinweis" name="speditions_hinweis" value="{{ $kontakt->speditions_hinweis }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">
                        </div>
                    </div>

                    <hr class="border-border-main/60">

                    <hr class="border-border-main/60">

                    <!-- Sektion 7: Steuern & SEPA-Lastschrift (Edit-Modus) -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">7. Fiskal- & Bankdaten (SEPA)</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="ust_id" class="block text-xs text-text-main font-medium mb-1">USt-IdNr. (EU)</label>
                                <input type="text" id="ust_id" name="ust_id" value="{{ $kontakt->ust_id }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                            <div>
                                <label for="steuernummer" class="block text-xs text-text-main font-medium mb-1">Steuernummer (Inland)</label>
                                <input type="text" id="steuernummer" name="steuernummer" value="{{ $kontakt->steuernummer }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="iban" class="block text-xs text-text-main font-medium mb-1">IBAN</label>
                                <input type="text" id="iban" name="iban" value="{{ $kontakt->iban }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                            <div>
                                <label for="bic" class="block text-xs text-text-main font-medium mb-1">BIC</label>
                                <input type="text" id="bic" name="bic" value="{{ $kontakt->bic }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                        </div>
                    </div>

                    <hr class="border-border-main/60">

                    <!-- Sektion 8: Weinbau-Marketing & Notizen (Edit-Modus) -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">8. Weinbau-Marketing &amp; Kontaktkanal</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <!-- 🎯 REAL-DYNAMISCH: Weinstilistik aus Datenbank -->
                            <div>
                                <label for="bevorzugte_weinstilistik" class="block text-xs text-text-main font-medium mb-1">Präferenz Weinstilistik</label>
                                <select id="bevorzugte_weinstilistik" name="bevorzugte_weinstilistik" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-accent-brand">
                                    <option value="" {{ $kontakt->bevorzugte_weinstilistik === '' ? 'selected' : '' }}>Keine Angabe</option>
                                    @foreach($konfig_stilistiken as $code => $wert)
                                        <option value="{{ $code }}" {{ $kontakt->bevorzugte_weinstilistik === $code ? 'selected' : '' }}>{{ $wert }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- 🎯 REAL-DYNAMISCH: Akquise-Kanal aus Datenbank -->
                            <div>
                                <label for="herkunft_kontakt" class="block text-xs text-text-main font-medium mb-1">Akquise-Kanal</label>
                                <select id="herkunft_kontakt" name="herkunft_kontakt" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-accent-brand">
                                    <option value="" {{ $kontakt->herkunft_kontakt === '' ? 'selected' : '' }}>Keine Angabe</option>
                                    @foreach($konfig_kanaele as $code => $wert)
                                        <option value="{{ $code }}" {{ $kontakt->herkunft_kontakt === $code ? 'selected' : '' }}>{{ $wert }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="block text-xs text-text-main font-medium mb-1">E-Mail-Adresse</label>
                            <input type="email" id="email" name="email" value="{{ $kontakt->email }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">
                        </div>
                        <div class="mb-3">
                            <label for="telefon" class="block text-xs text-text-main font-medium mb-1">Telefon / Mobil</label>
                            <input type="text" id="telefon" name="telefon" value="{{ $kontakt->telefon }}" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">
                        </div>
                        <div class="mb-3">
                            <label for="notizen" class="block text-xs text-text-main font-medium mb-1">Interne Winzer-Notizen</label>
                            <textarea id="notizen" name="notizen" rows="2" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">{{ $kontakt->notizen }}</textarea>
                        </div>

                        <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold font-mono text-xs py-3 rounded-xl transition shadow-3xs cursor-pointer mt-2 border-0">
                            🔒 Modifizierte Daten versiegeln
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>
@endsection
