@extends('layouts.app')

@section('content')
<div class="h-full w-full flex flex-col min-w-0 bg-bg-base overflow-hidden">
    
    <!-- Modul-Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-bg-surface border-b border-border-main flex-shrink-0">
        <div>
            <a href="{{ route('crm.index') }}" class="text-[10px] font-mono font-bold tracking-wider text-text-muted hover:text-accent-brand no-underline transition-colors uppercase">
                ← Zurück zum Register
            </a>
            <h1 class="text-sm font-mono font-bold tracking-wider text-text-main uppercase mt-1 mb-0">
                ➕ Neuen Partner registrieren
            </h1>
        </div>
    </div>

    <!-- Das Formular-Gehäuse -->
    <form action="{{ route('crm.store') }}" method="POST" class="flex-1 overflow-y-auto min-h-0 p-3 md:p-4">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            
            <!-- LINKER HAUPTFLÜGEL: Stammdaten & Multi-Kontakte -->
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-bg-surface border border-border-main rounded-2xl shadow-3xs p-4 space-y-4">
                    
                    <!-- Sektion 1: Partner-Rolle & Typ-Weiche -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">1. Partner-Klassifizierung</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            
                            <!-- 🎯 DAS DYNAMISCHE PARTNER-DROPDOWN -->
                            <div>
                                <label for="partner_typ" class="block text-xs text-text-main font-medium mb-1">Partner-Typ</label>
                                <select id="partner_typ" name="partner_typ" onchange="SteuerePartnerMaske()" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-accent-brand font-bold font-mono">
                                    <option value="privat">👤 Privatkunde</option>
                                    <option value="firma">🏢 Firmenkunde / B2B</option>
                                </select>
                            </div>

                            <label class="block p-2.5 border border-border-main rounded-xl cursor-pointer bg-bg-base/30 hover:bg-bg-input/50 transition relative">
                                <div class="flex items-center justify-between mt-1">
                                    <div>
                                        <div class="text-xs font-bold text-text-main">Kunde</div>
                                    </div>
                                    <input type="checkbox" name="ist_kunde" value="1" checked class="rounded border-border-main text-accent-brand focus:ring-accent-brand scale-110 cursor-pointer">
                                </div>
                            </label>
                            
                            <label class="block p-2.5 border border-border-main rounded-xl cursor-pointer bg-bg-base/30 hover:bg-bg-input/50 transition relative">
                                <div class="flex items-center justify-between mt-1">
                                    <div>
                                        <div class="text-xs font-bold text-text-main">📦 Lieferant / Erzeuger</div>
                                    </div>
                                    <input type="checkbox" name="ist_lieferant" value="1" class="rounded border-border-main text-text-main focus:ring-slate-500 scale-110 cursor-pointer">
                                </div>
                            </label>
                        </div>
                    </div>

                    <hr class="border-border-main/60">

                    <!-- Sektion 2: Identität (Wird per JS reaktiv gesteuert) -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">2. Identität &amp; Kern-Stammdaten</label>
                        
                        <div id="wrapper_firma" class="mb-3 hidden">
                            <label for="firma" class="block text-xs text-text-main font-medium mb-1">Firmenname<span class="text-red-500 font-bold">*</span></label>
                            <input type="text" id="firma" name="firma" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label for="vorname" class="block text-xs text-text-main font-medium mb-1">Vorname</label>
                                <input type="text" id="vorname" name="vorname" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">
                            </div>
                            <div>
                                <label id="label_nachname" for="nachname" class="block text-xs text-text-main font-medium mb-1">Nachname <span class="text-red-500 font-bold">*</span></label>
                                <input type="text" id="nachname" name="nachname" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">
                            </div>
                            <div>
                                <label for="geburtsdatum" class="block text-xs text-text-main font-medium mb-1">Geburtsdatum</label>
                                <input type="date" id="geburtsdatum" name="geburtsdatum" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Sektion 3 & 4: Adress-Gehäuse -->
                <div class="bg-bg-surface border border-border-main rounded-2xl shadow-3xs p-4 space-y-4">
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">3. Haupt- / Rechnungsanschrift</label>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mb-3">
                            <div class="sm:col-span-2">
                                <label for="strasse" class="block text-xs text-text-main font-medium mb-1">Straße</label>
                                <input type="text" id="strasse" name="strasse" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium" placeholder="z.B. Hauptstraße">
                            </div>
                            <div>
                                <label for="hausnummer" class="block text-xs text-text-main font-medium mb-1">Nr.</label>
                                <input type="text" id="hausnummer" name="hausnummer" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium" placeholder="z.B. 12a">
                            </div>
                            <div>
                                <label for="adresszusatz" class="block text-xs text-text-main font-medium mb-1">Zusatz</label>
                                <input type="text" id="adresszusatz" name="adresszusatz" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium" placeholder="Abt., Etage...">
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label for="plz" class="block text-xs text-text-main font-medium mb-1">PLZ</label>
                                <input type="text" id="plz" name="plz" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                            <div class="col-span-2">
                                <label for="ort" class="block text-xs text-text-main font-medium mb-1">Ort</label>
                                <input type="text" id="ort" name="ort" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">
                            </div>
                        </div>
                    </div>

                    <hr class="border-border-main/60">

                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">4. Abweichende Lieferanschrift (Optional)</label>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mb-3">
                            <div class="sm:col-span-2">
                                <label for="liefer_strasse" class="block text-xs text-text-main font-medium mb-1">Liefer-Straße / Packstation</label>
                                <input type="text" id="liefer_strasse" name="liefer_strasse" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium" placeholder="z.B. Weinstraße">
                            </div>
                            <div>
                                <label for="liefer_hausnummer" class="block text-xs text-text-main font-medium mb-1">Nr.</label>
                                <input type="text" id="liefer_hausnummer" name="liefer_hausnummer" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium" placeholder="z.B. 44">
                            </div>
                            <div>
                                <label for="liefer_adresszusatz" class="block text-xs text-text-main font-medium mb-1">Liefer-Zusatz</label>
                                <input type="text" id="liefer_adresszusatz" name="liefer_adresszusatz" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium" placeholder="z.B. Postnummer">
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label for="liefer_plz" class="block text-xs text-text-main font-medium mb-1">Liefer-PLZ</label>
                                <input type="text" id="liefer_plz" name="liefer_plz" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                            <div class="col-span-2">
                                <label for="liefer_ort" class="block text-xs text-text-main font-medium mb-1">Liefer-Ort</label>
                                <input type="text" id="liefer_ort" name="liefer_ort" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 🎯 Sektion 5: Das reaktive Multi-Kontakt-Getriebe -->
                <div class="bg-bg-surface border border-border-main rounded-2xl shadow-3xs p-4 space-y-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted">5. Kommunikationskanäle &amp; Ansprechpartner</label>
                            <p class="text-[11px] text-text-muted m-0 mt-0.5" id="kontakte_beschreibung">Hinterlege zusätzliche E-Mail-Adressen und Telefone für Abteilungen.</p>
                        </div>
                        <button type="button" onclick="FügeKontaktZeileHinzu()" class="bg-bg-input border border-border-main hover:bg-border-main text-text-main text-[11px] font-mono font-bold px-2.5 py-1.5 rounded-xl transition cursor-pointer">
                            ➕ Kontakt anbauen
                        </button>
                    </div>

                    <!-- Der dynamische Injektions-Container für Zeilen -->
                    <div id="kontakt_zeilen_container" class="space-y-2">
                        <!-- Erste Standardzeile läuft direkt mit rein -->
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 items-center bg-bg-base/30 p-2 rounded-xl border border-border-main/50 text-xs contact-row">
                            <div>
                                <input type="text" name="kontakte_details[0][abteilung]" placeholder="Abteilung (z.B. Buchhaltung)" class="w-full bg-bg-surface text-text-main text-xs rounded-lg border border-border-main px-2.5 py-1.5 focus:outline-none">
                            </div>
                            <div>
                                <input type="text" name="kontakte_details[0][ansprechpartner_name]" placeholder="Name Ansprechpartner" class="w-full bg-bg-surface text-text-main text-xs rounded-lg border border-border-main px-2.5 py-1.5 focus:outline-none">
                            </div>
                            <div>
                                <input type="email" name="kontakte_details[0][email]" placeholder="E-Mail-Adresse" class="w-full bg-bg-surface text-text-main text-xs rounded-lg border border-border-main px-2.5 py-1.5 focus:outline-none">
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="text" name="kontakte_details[0][telefon]" placeholder="Durchwahl" class="w-full bg-bg-surface text-text-main text-xs rounded-lg border border-border-main px-2.5 py-1.5 focus:outline-none flex-1">
                                <label class="flex items-center justify-center p-1 text-text-muted hover:text-accent-brand cursor-pointer" title="Haupt-Belegempfänger">
                                    <input type="radio" name="hauptkontakt_index" value="0" checked class="text-accent-brand focus:ring-accent-brand scale-105 cursor-pointer">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- Schließt den linken Flügel -->
            <!-- RECHTE FLANKE: Kaufmännische Weichen, DATEV & Logistik -->
            <div class="space-y-4">
                <div class="bg-bg-surface border border-border-main rounded-2xl shadow-3xs p-4 space-y-4">
                    
                    <!-- Sektion 6: Finanzkonditionen & DATEV -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">6. Finanzkonditionen &amp; DATEV</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="kunden_kategorie" class="block text-xs text-text-main font-medium mb-1">Kunden-Segment</label>
                                <select id="kunden_kategorie" name="kunden_kategorie" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-accent-brand">
                                    @foreach($konfig_segmente as $code => $wert)
                                        <option value="{{ $code }}">{{ $wert }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="buchhaltung_gruppe" class="block text-xs text-text-main font-medium mb-1">DATEV Steuer-Zone</label>
                                <select id="buchhaltung_gruppe" name="buchhaltung_gruppe" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-accent-brand">
                                    @foreach($konfig_steuerzonen as $code => $wert)
                                        <option value="{{ $code }}">{{ $wert }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="debitorennummer" class="block text-xs text-text-main font-medium mb-1">DATEV Debitor</label>
                                <input type="text" id="debitorennummer" name="debitorennummer" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                            <div>
                                <label for="kreditorennummer" class="block text-xs text-text-main font-medium mb-1">DATEV Kreditor</label>
                                <input type="text" id="kreditorennummer" name="kreditorennummer" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="standard_zahlungsziel_tage" class="block text-xs text-text-main font-medium mb-1">Ziel (Tage)</label>
                                <input type="number" id="standard_zahlungsziel_tage" name="standard_zahlungsziel_tage" value="14" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium" required>
                            </div>
                            <div>
                                <label for="individueller_rabatt_prozent" class="block text-xs text-text-main font-medium mb-1">Rabatt (%)</label>
                                <input type="number" id="individueller_rabatt_prozent" name="individueller_rabatt_prozent" value="0" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium" step="0.01">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="skonto_prozent" class="block text-xs text-text-main font-medium mb-1">Skonto (%)</label>
                                <input type="number" id="skonto_prozent" name="skonto_prozent" value="0" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium" step="0.01">
                            </div>
                            <div>
                                <label for="skonto_tage" class="block text-xs text-text-main font-medium mb-1">Skonto (Tage)</label>
                                <input type="number" id="skonto_tage" name="skonto_tage" value="0" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="block p-3 border border-red-200 rounded-xl cursor-pointer bg-red-50/30 hover:bg-red-50/60 transition relative">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs font-bold text-red-800">🛡️ Automatische Liefersperre</div>
                                    </div>
                                    <input type="checkbox" name="ist_gesperrt" value="1" class="rounded border-red-300 text-red-600 focus:ring-red-500 scale-110 cursor-pointer">
                                </div>
                            </label>
                        </div>
                    </div>
                    <hr class="border-border-main/60">

                    <!-- Sektion 7: Logistik-Routing -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">7. Versand- &amp; Zollsteuerung</label>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="lieferbedingungen" class="block text-xs text-text-main font-medium mb-1">Frachtbasis</label>
                                <select id="lieferbedingungen" name="lieferbedingungen" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-accent-brand">
                                    @foreach($konfig_incoterms as $code => $wert)
                                        <option value="{{ $code }}">{{ $wert }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="versanddienstleister" class="block text-xs text-text-main font-medium mb-1">Logistiker</label>
                                <select id="versanddienstleister" name="versanddienstleister" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-accent-brand">
                                    @foreach($konfig_logistiker as $code => $wert)
                                        <option value="{{ $code }}">{{ $wert }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="speditions_hinweis" class="block text-xs text-text-main font-medium mb-1">Avisierungs- / Speditionshinweis</label>
                            <input type="text" id="speditions_hinweis" name="speditions_hinweis" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">
                        </div>
                    </div>

                    <hr class="border-border-main/60">

                    <!-- Sektion 8: Steuern & SEPA-Lastschrift -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">8. Fiskal- &amp; Bankdaten (SEPA)</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="ust_id" class="block text-xs text-text-main font-medium mb-1">USt-IdNr. (EU)</label>
                                <input type="text" id="ust_id" name="ust_id" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                            <div>
                                <label for="steuernummer" class="block text-xs text-text-main font-medium mb-1">Steuernummer (Inland)</label>
                                <input type="text" id="steuernummer" name="steuernummer" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="iban" class="block text-xs text-text-main font-medium mb-1">IBAN</label>
                                <input type="text" id="iban" name="iban" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                            <div>
                                <label for="bic" class="block text-xs text-text-main font-medium mb-1">BIC</label>
                                <input type="text" id="bic" name="bic" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                        </div>
                    </div>
                    <hr class="border-border-main/60">

                    <!-- Sektion 9: Weinbau-Marketing & Notizen -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">9. Weinbau-Marketing &amp; Kontaktkanal</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="bevorzugte_weinstilistik" class="block text-xs text-text-main font-medium mb-1">Präferenz Weinstilistik</label>
                                <select id="bevorzugte_weinstilistik" name="bevorzugte_weinstilistik" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-accent-brand">
                                    <option value="">Keine Angabe</option>
                                    @foreach($konfig_stilistiken as $code => $wert)
                                        <option value="{{ $code }}">{{ $wert }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="herkunft_kontakt" class="block text-xs text-text-main font-medium mb-1">Akquise-Kanal</label>
                                <select id="herkunft_kontakt" name="herkunft_kontakt" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-accent-brand">
                                    <option value="">Keine Angabe</option>
                                    @foreach($konfig_kanaele as $code => $wert)
                                        <option value="{{ $code }}">{{ $wert }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Zentrale Erreichbarkeiten (Zentrale / Hauptkontakt) -->
                        <div class="mb-3">
                            <label for="email" class="block text-xs text-text-main font-medium mb-1">Zentrale E-Mail-Adresse (Hauptstamm)</label>
                            <input type="email" id="email" name="email" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">
                        </div>
                        <div class="mb-3">
                            <label for="telefon" class="block text-xs text-text-main font-medium mb-1">Zentrale Telefonnummer</label>
                            <input type="text" id="telefon" name="telefon" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">
                        </div>
                        <div class="mb-3">
                            <label for="notizen" class="block text-xs text-text-main font-medium mb-1">Interne Winzer-Notizen</label>
                            <textarea id="notizen" name="notizen" rows="2" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium"></textarea>
                        </div>

                        <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold font-mono text-xs py-3 rounded-xl transition shadow-3xs cursor-pointer mt-2 border-0">
                            🔒 Partner permanent im ERP versiegeln
                        </button>
                    </div>

                </div>
            </div> <!-- Schließt die rechte Flanke -->
        </div>
    </form>
</div>

<!-- 🧠 REAKTIVES JAVASCRIPT-TRIEBWERK -->
<script>
    let kontaktZeilenIndex = 1;

    function SteuerePartnerMaske() {
        const typ = document.getElementById('partner_typ').value;
        const wrapperFirma = document.getElementById('wrapper_firma');
        const inputFirma = document.getElementById('firma');
        const labelNachname = document.getElementById('label_nachname');
        const inputNachname = document.getElementById('nachname');
        const kontakteBeschreibung = document.getElementById('kontakte_beschreibung');

        if (typ === 'firma') {
            // 🏢 B2B-FIRMEN-MODUS
            wrapperFirma.classList.remove('hidden');
            inputFirma.setAttribute('required', 'required');
            labelNachname.innerHTML = 'Nachname (Inhaber / Einzelunternehmer)';
            inputNachname.removeAttribute('required');
            if (kontakteBeschreibung) {
                kontakteBeschreibung.innerHTML = 'Hinterlege beliebig viele abteilungsbezogene Ansprechpartner (Einkauf, Gastro-Leitung, Buchhaltung) mitsamt eigenen Kanälen.';
            }
        } else {
            // 👤 PRIVATKUNDEN-MODUS
            wrapperFirma.classList.add('hidden');
            inputFirma.removeAttribute('required');
            inputFirma.value = '';
            labelNachname.innerHTML = 'Nachname <span class="text-red-500 font-bold">*</span>';
            inputNachname.setAttribute('required', 'required');
            if (kontakteBeschreibung) {
                kontakteBeschreibung.innerHTML = 'Hinterlege zusätzliche Erreichbarkeiten (z.B. Zweit-E-Mail, Mobilnummer des Partners, etc.).';
            }
        }
    }

    function FügeKontaktZeileHinzu() {
        const container = document.getElementById('kontakt_zeilen_container');
        const div = document.createElement('div');
        div.className = "grid grid-cols-1 sm:grid-cols-4 gap-2 items-center bg-bg-base/30 p-2 rounded-xl border border-border-main/50 text-xs contact-row";
        
        div.innerHTML = `
            <div>
                <input type="text" name="kontakte_details[\${kontaktZeilenIndex}][abteilung]" placeholder="Abteilung / Kennung" class="w-full bg-bg-surface text-text-main text-xs rounded-lg border border-border-main px-2.5 py-1.5 focus:outline-none">
            </div>
            <div>
                <input type="text" name="kontakte_details[\${kontaktZeilenIndex}][ansprechpartner_name]" placeholder="Name Ansprechpartner" class="w-full bg-bg-surface text-text-main text-xs rounded-lg border border-border-main px-2.5 py-1.5 focus:outline-none">
            </div>
            <div>
                <input type="email" name="kontakte_details[\${kontaktZeilenIndex}][email]" placeholder="E-Mail-Adresse" class="w-full bg-bg-surface text-text-main text-xs rounded-lg border border-border-main px-2.5 py-1.5 focus:outline-none">
            </div>
            <div class="flex items-center gap-2">
                <input type="text" name="kontakte_details[\${kontaktZeilenIndex}][telefon]" placeholder="Durchwahl" class="w-full bg-bg-surface text-text-main text-xs rounded-lg border border-border-main px-2.5 py-1.5 focus:outline-none flex-1">
                <button type="button" onclick="this.closest('.contact-row').remove()" class="text-red-500 hover:text-red-700 bg-transparent border-0 font-bold text-base cursor-pointer px-1 shadow-none transition-colors" title="Zeile entfernen">&times;</button>
            </div>
        `;
        
        container.appendChild(div);
        kontaktZeilenIndex++;
    }

    // Beim Laden der Seite die Initialisierung einmal triggern
    document.addEventListener('DOMContentLoaded', function() {
        SteuerePartnerMaske();
    });
</script>
@endsection
