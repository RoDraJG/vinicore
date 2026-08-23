<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GisLiegenschaftenController;

/*
|--------------------------------------------------------------------------
| 🛡️ GESCHÜTZTER WINZER-LEITSTAND (AUTH-ZONE // INKLUSIVE GIS-PIPELINES)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    
    // 1. Das modulare ERP-Dashboard (Startseite)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // 2. Die tabellarische Flurstücksliste (Katasterspiegel)
    Route::get('/parzellen/uebersicht', [GisLiegenschaftenController::class, 'parzellenUebersichtView']);

    // 3. Unterfunktion der Liste: Die interaktive ALKIS-Sammelkarte
    Route::get('/kataster/parzellen-karte', function () {
        $betrieb = auth()->user()->aktuellerBetrieb;
        return view('kataster.parzellen_karte', compact('betrieb'));
    });

    // 4. Das operative Außenbetriebs-Cockpit (Schläge & Weinbergs-Anlagen)
    Route::get('/schlaege', function () {
        $betrieb = auth()->user()->aktuellerBetrieb;
        return view('schlaege.schlag_karte', compact('betrieb'));
    });

    // Dummy-Routen für deine ERP-Schnellzugriffsknöpfe
    Route::get('/betrieb/daten', function () { return "🏢 Hier entstehen deine Betriebsdaten und Hof-Stammdaten."; });
    Route::get('/admin/dashboard', function () { return "⚙️ Hier entsteht dein administratives Winzer-Panel."; });
    Route::get('/user/profile', function () { return "👤 Hier entsteht dein persönliches Winzer-Profil."; });

    // ==========================================================================
    // 🛰️ INTEGRALE ASYNCHRONIE-SCHNITTSTELLEN (NATIV ÜBER WEB-SESSION)
    // ==========================================================================
    
    // Speichert den reaktiven Kollaps-Status (w-0) live im Winzerprofil
    Route::post('/api/user/sidebar-status', function (\Illuminate\Http\Request $request) {
        session(['sidebar_collapsed' => (bool) $request->input('collapsed')]);
        return response()->json(['success' => true]);
    });

    // Zieht deine gebuchten Weinbergsparzellen als GeoJSON für Leaflet
    Route::get('/api/geojson/parzellen', [GisLiegenschaftenController::class, 'index']);

    // Lädt das hellblaue ALKIS-Umland-Vektornetz live vom Landesamt RLP
    Route::post('/api/kataster/umgebung-laden', [GisLiegenschaftenController::class, 'ladeUmgebungVomGeoportal']);

    // Startet die amtliche Flurstücks-Suche über das Gemarkungsmodal
    Route::post('/api/kataster/suchen-im-geoportal', [GisLiegenschaftenController::class, 'ladeVomGeoportalRlp']);

    // 📥 NEU: Speichert die gesammelten Warenkorb-Parzellen direkt zu einer vertrag_id
    Route::post('/api/kataster/parzellen/speichern-sammelkorb', [GisLiegenschaftenController::class, 'speichereInDatenbank']);

    // 📝 Schnittstellen für die historische Flurstücksverwaltung und das Zeitschloss
    Route::post('/api/kataster/parzellen/aktualisieren/{uuid}', [GisLiegenschaftenController::class, 'aktualisiereParzelle']);
    
    // 🗑️ Die prozessuale Vernichtungs-Mündung (NUR vor der Erstprüfung gültig!)
    Route::post('/api/kataster/parzellen/ausbuchen/{uuid}', [GisLiegenschaftenController::class, 'loescheParzelle']);

    // 🤝 NEU: Die erweiterte Abgangs-Mündung (Verkauf / Pachtrückgabe mit Restpacht-Weichen)
    Route::post('/api/kataster/parzellen/verkaufen/{uuid}', [GisLiegenschaftenController::class, 'verkaufeParzelle']);

    // 📡 Satelliten-Tunnel: Lädt die parzellenspezifischen Matrixdetails in Echtzeit
    Route::get('/api/kataster/parzelle-details/{uuid}', [GisLiegenschaftenController::class, 'holeParzelleDetails']);

    // ==========================================================================
    // 🏢 ENTERPRISE SECURITY PLATFORM: RECORD LOCKING & 4-EYES APPROVALS
    // ==========================================================================
    
    // 🔒 Sperr-Mechanismus (Record Locking): Blockiert kollidierende Bearbeitungen
    Route::post('/api/kataster/parzellen/lock/{uuid}', [GisLiegenschaftenController::class, 'lockParzelle']);
    
    // 🔓 Entsperr-Mechanismus: Gibt das Flurstück sofort nach dem Schließen des Modals wieder frei
    Route::post('/api/kataster/parzellen/unlock/{uuid}', [GisLiegenschaftenController::class, 'unlockParzelle']);

    // ⚖️ Admin-Mündung für das Vier-Augen-Prinzip: Gibt eine eingereichte Revision final frei
    Route::post('/api/kataster/parzellen/freigeben/{uuid}', [GisLiegenschaftenController::class, 'freigebeParzelleAudit']);

        // 🏢 NEU: Das kaufmännische Vertragswesen (Die Quelle für deine Katasterparzellen)
    Route::get('/finanzen/vertrag-anlegen', [\App\Http\Controllers\VertragController::class, 'erstelleView'])->name('vertrag.anlegen');
    Route::post('/finanzen/vertrag-speichern', [\App\Http\Controllers\VertragController::class, 'speichereVertrag'])->name('vertrag.speichern');
    Route::post('/api/kataster/vertrag/final-versiegeln', [\App\Http\Controllers\VertragController::class, 'finaleVersiegeln']);

    Route::post('/api/kataster/vertrag/session-parken', [\App\Http\Controllers\GisLiegenschaftenController::class, 'parkeStammdatenInSession']);



    // 🛰️ VINICORE UUID-DRAFTING PIPELINE
    Route::middleware(['web'])->group(function () {
    Route::post('/api/kataster/vertrag/entwurf-initialisieren', [GisLiegenschaftenController::class, 'initialisiereEntwurf']);
    Route::post('/api/kataster/vertrag/entwurf-parzellen-sync', [GisLiegenschaftenController::class, 'synchronisiereEntwurfsParzellen']);
    Route::post('/api/kataster/vertrag/final-versiegeln', [GisLiegenschaftenController::class, 'finalVersiegeln']);
});

});

// BREEZE INTEGRATION: Lädt die namensgesicherte auth.php unzerbrechlich hinzu!
require __DIR__.'/auth.php';
