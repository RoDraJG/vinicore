@extends('layouts.app')

@section('content')
<div class="h-full w-full flex flex-col min-w-0 bg-bg-base overflow-hidden">
    
    <!-- 🎛️ Modul-Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-bg-surface border-b border-border-main flex-shrink-0">
        <div>
            <a href="{{ route('crm.index') }}" class="text-[10px] font-mono font-bold tracking-wider text-text-muted hover:text-accent-brand no-underline transition-colors uppercase">
                ← Zurück zur Übersicht
            </a>
            <h1 class="text-sm font-mono font-bold tracking-wider text-text-main uppercase mt-1 mb-0">
                📝 Enterprise Partner-Erfassung
            </h1>
        </div>
    </div>

    <!-- 📝 Das Formular-Gehäuse -->
    <form action="{{ route('crm.store') }}" method="POST" class="flex-1 overflow-y-auto min-h-0 p-3 md:p-4">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            
            <!-- Linker Hauptflügel: Stammdaten & Adressblöcke -->
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-bg-surface border border-border-main rounded-2xl shadow-3xs p-4 space-y-4">
                    
                    <!-- Sektion 1: Rollen-Kacheln -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">1. Partner-Rolle festlegen</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="block p-3 border border-border-main rounded-xl cursor-pointer bg-bg-base/30 hover:bg-bg-input/50 transition relative">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs font-bold text-text-main">🍷 Weinkunde / Abnehmer</div>
                                        <div class="text-[10px] text-text-muted mt-0.5">Endverbraucher, Gastro, Handel</div>
                                    </div>
                                    <input type="checkbox" name="ist_kunde" value="1" checked class="rounded border-border-main text-accent-brand focus:ring-accent-brand scale-110 cursor-pointer">
                                </div>
                            </label>
                            
                            <label class="block p-3 border border-border-main rounded-xl cursor-pointer bg-bg-base/30 hover:bg-bg-input/50 transition relative">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs font-bold text-text-main">📦 Lieferant / Erzeuger</div>
                                        <div class="text-[10px] text-text-muted mt-0.5">Flaschen, Hefe, Dienstleistungen</div>
                                    </div>
                                    <input type="checkbox" name="ist_lieferant" value="1" class="rounded border-border-main text-text-main focus:ring-slate-500 scale-110 cursor-pointer">
                                </div>
                            </label>
                        </div>
                    </div>

                    <hr class="border-border-main/60">

                    <!-- Sektion 2: Identität & Unternehmen -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">2. Identität & Unternehmen</label>
                        <div class="mb-3">
                            <label for="firma" class="block text-xs text-text-main font-medium mb-1">Firmenname / Weingut (B2B)</label>
                            <input type="text" id="firma" name="firma" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label for="vorname" class="block text-xs text-text-main font-medium mb-1">Vorname</label>
                                <input type="text" id="vorname" name="vorname" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">
                            </div>
                            <div>
                                <label for="nachname" class="block text-xs text-text-main font-medium mb-1">Nachname</label>
                                <input type="text" id="nachname" name="nachname" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium" required>
                            </div>
                            <div>
                                <label for="geburtsdatum" class="block text-xs text-text-main font-medium mb-1">Geburtsdatum</label>
                                <input type="date" id="geburtsdatum" name="geburtsdatum" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Sektion 3: Rechnungs- & Logistikadressen (Tiefer aufgeteilt) -->
                <div class="bg-bg-surface border border-border-main rounded-2xl shadow-3xs p-4 space-y-4">
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">3. Haupt- / Rechnungsanschrift</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                            <div class="sm:col-span-2">
                                <label for="strasse_nr" class="block text-xs text-text-main font-medium mb-1">Straße und Hausnummer</label>
                                <input type="text" id="strasse_nr" name="strasse_nr" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">
                            </div>
                            <div>
                                <label for="adresszusatz" class="block text-xs text-text-main font-medium mb-1">Adresszusatz</label>
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

                    <!-- 🚚 ABWEICHENDE LIEFERANSCHRIFT (Mit Adresszusatz für Logistik-Schnittstellen) -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">4. Abweichende Lieferanschrift (Optional)</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                            <div class="sm:col-span-2">
                                <label for="liefer_strasse_nr" class="block text-xs text-text-main font-medium mb-1">Liefer-Straße und Hausnummer / Packstation</label>
                                <input type="text" id="liefer_strasse_nr" name="liefer_strasse_nr" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium">
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
            </div>
            <!-- Rechte Flanke: Kaufmännische Weichen, DATEV, SEPA & Marketing -->
            <div class="space-y-4">
                <div class="bg-bg-surface border border-border-main rounded-2xl shadow-3xs p-4 space-y-4">
                    
                    <!-- Sektion 5: Kaufmännische Bedingungen & DATEV -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">5. Finanzkonditionen & DATEV</label>
                        
                        <div class="mb-3">
                            <label for="kunden_kategorie" class="block text-xs text-text-main font-medium mb-1">Kunden-Segment</label>
                            <select id="kunden_kategorie" name="kunden_kategorie" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-accent-brand">
                                <option value="privat" selected>🍷 Privatkunde / Endverbraucher</option>
                                <option value="gastro">🍽️ Gastronomie & Hotellerie</option>
                                <option value="handel">🛒 Groß- & Fachhandel</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label Whitespace for="debitorennummer" class="block text-xs text-text-main font-medium mb-1">DATEV Debitor</label>
                                <input type="text" id="debitorennummer" name="debitorennummer" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium" placeholder="10000">
                            </div>
                            <div>
                                <label for="kreditorennummer" class="block text-xs text-text-main font-medium mb-1">DATEV Kreditor</label>
                                <input type="text" id="kreditorennummer" name="kreditorennummer" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium" placeholder="70000">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="standard_zahlungsziel_tage" class="block text-xs text-text-main font-medium mb-1">Ziel (Tage)</label>
                                <input type="number" id="standard_zahlungsziel_tage" name="standard_zahlungsziel_tage" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium" value="14" min="0" required>
                            </div>
                            <div>
                                <label for="individueller_rabatt_prozent" class="block text-xs text-text-main font-medium mb-1">Rabatt (%)</label>
                                <input type="number" id="individueller_rabatt_prozent" name="individueller_rabatt_prozent" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium" value="0.00" min="0" max="100" step="0.01">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="skonto_prozent" class="block text-xs text-text-main font-medium mb-1">Skonto (%)</label>
                                <input type="number" id="skonto_prozent" name="skonto_prozent" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium" value="0.00" min="0" max="100" step="0.01">
                            </div>
                            <div>
                                <label for="skonto_tage" class="block text-xs text-text-main font-medium mb-1">Skonto (Tage)</label>
                                <input type="number" id="skonto_tage" name="skonto_tage" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium" value="0" min="0">
                            </div>
                        </div>
                    </div>

                    <hr class="border-border-main/60">

                    <!-- Sektion 6: Logistik-Routing -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">6. Versand- & Zollsteuerung</label>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="lieferbedingungen" class="block text-xs text-text-main font-medium mb-1">Incoterms / Fracht</label>
                                <select id="lieferbedingungen" name="lieferbedingungen" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-accent-brand">
                                    <option value="ab_hof" selected>🏡 Ab Hof</option>
                                    <option value="frei_haus">🚚 Frei Haus</option>
                                    <option value="dhl">📦 Paketdienst</option>
                                </select>
                            </div>
                            <div>
                                <label for="versanddienstleister" class="block text-xs text-text-main font-medium mb-1">Logistiker</label>
                                <select id="versanddienstleister" name="versanddienstleister" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-accent-brand">
                                    <option value="dhl" selected>DHL Paket</option>
                                    <option value="ups">UPS Express</option>
                                    <option value="spedition">Haus-Spedition</option>
                                    <option value="eigen">Eigen-Fahrzeug</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="speditions_hinweis" class="block text-xs text-text-main font-medium mb-1">Avisierungs- / Speditionshinweis</label>
                            <input type="text" id="speditions_hinweis" name="speditions_hinweis" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium" placeholder="z.B. Hebebühne zwingend erforderlich">
                        </div>
                    </div>
                    <hr class="border-border-main/60">

                    <!-- Sektion 7: Steuern & SEPA-Lastschrift -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">7. Fiskal- & Bankdaten (SEPA)</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="ust_id" class="block text-xs text-text-main font-medium mb-1">USt-IdNr. (EU-Export)</label>
                                <input type="text" id="ust_id" name="ust_id" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium" placeholder="DE123456789">
                            </div>
                            <div>
                                <label for="steuernummer" class="block text-xs text-text-main font-medium mb-1">Steuernummer (Inland)</label>
                                <input type="text" id="steuernummer" name="steuernummer" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium" placeholder="27/123/45678">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="iban" class="block text-xs text-text-main font-medium mb-1">IBAN (Einzugsermächtigung)</label>
                                <input type="text" id="iban" name="iban" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium" placeholder="DE893704...">
                            </div>
                            <div>
                                <label for="bic" class="block text-xs text-text-main font-medium mb-1">BIC</label>
                                <input type="text" id="bic" name="bic" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-mono font-medium" placeholder="WELADED1...">
                            </div>
                        </div>
                    </div>

                    <hr class="border-border-main/60">

                    <!-- Sektion 8: Weinbau-Marketing & Notizen -->
                    <div>
                        <label class="block text-[10px] uppercase font-mono tracking-wider font-bold text-text-muted mb-2">8. Weinbau-Marketing & Kontaktkanal</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="bevorzugte_weinstilistik" class="block text-xs text-text-main font-medium mb-1">Präferenz Weinstilistik</label>
                                <select id="bevorzugte_weinstilistik" name="bevorzugte_weinstilistik" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-accent-brand">
                                    <option value="" selected>Keine Angabe</option>
                                    <option value="trocken">Weißwein trocken</option>
                                    <option value="feinherb">Feinherb & Halbtrocken</option>
                                    <option value="edelsuess">Frucht- & Edelsüß</option>
                                    <option value="rotwein">Kräftiger Rotwein</option>
                                </select>
                            </div>
                            <div>
                                <label for="herkunft_kontakt" class="block text-xs text-text-main font-medium mb-1">Akquise-Kanal</label>
                                <select id="herkunft_kontakt" name="herkunft_kontakt" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-accent-brand">
                                    <option value="direkt" selected>Hofbesuch / Vinothek</option>
                                    <option value="messe">Weinmesse / Präsentation</option>
                                    <option value="online">Online-Shop</option>
                                    <option value="empfehlung">Mundpropaganda</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="block text-xs text-text-main font-medium mb-1">E-Mail-Adresse</label>
                            <input type="email" id="email" name="email" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium" placeholder="partner@weingut.de">
                        </div>
                        <div class="mb-3">
                            <label for="telefon" class="block text-xs text-text-main font-medium mb-1">Telefon / Mobil</label>
                            <input type="text" id="telefon" name="telefon" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium" placeholder="+49 (0) 651 ...">
                        </div>
                        <div class="mb-3">
                            <label for="notizen" class="block text-xs text-text-main font-medium mb-1">Interne Winzer-Notizen</label>
                            <textarea id="notizen" name="notizen" rows="2" class="w-full bg-bg-input text-text-main text-xs rounded-xl border border-border-main px-3 py-2 focus:outline-none focus:ring-1 focus:ring-accent-brand font-medium" placeholder="Besonderheiten zur Logistik oder Weinpräferenz..."></textarea>
                        </div>

                        <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold font-mono text-xs py-3 rounded-xl transition shadow-3xs cursor-pointer mt-2 border-0">
                            🔒 Partner im System versiegeln
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>
@endsection
