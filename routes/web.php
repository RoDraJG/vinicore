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
    Route::get('/schlaege/schlag-karte', function () {
        $betrieb = auth()->user()->aktuellerBetrieb;
        return view('schlaege.schlag_karte', compact('betrieb'));
    });

    // Dummy-Routen für deine ERP-Schnellzugriffsknöpfe
    Route::get('/betrieb/daten', function () { 
        return "🏢 Hier entstehen deine Betriebsdaten und Hof-Stammdaten."; 
    });
    
    Route::get('/admin/dashboard', function () { 
        return "⚙️ Hier entsteht dein administratives Winzer-Panel."; 
    });
    
    Route::get('/user/profile', function () { 
        return "👤 Hier entsteht dein persönliches Winzer-Profil."; 
    });

    // ==========================================================================
    // 🛰️ INTEGRALE ASYNCHRONIE-SCHNITTSTELLEN (NATIV ÜBER DEINE WEB-SESSION ACCESSIBLE)
    // ==========================================================================
    
    // 🚀 ARCHITEKTUR-FIX ZEILE 47: Doppelpunkte durch korrekte Backslashes ersetzt!
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

    // Importiert die Neuflächen aus dem Sammelkorb fest als Betriebsbestand in deine 'parzellen'-Tabelle
    Route::post('/api/kataster/parzellen/speichern-sammelkorb', [GisLiegenschaftenController::class, 'speichereInDatenbank']);

    // 📝 Schnittstellen für die historische Flurstücksverwaltung und das Zeitschloss
    Route::post('/api/kataster/parzellen/aktualisieren/{uuid}', [GisLiegenschaftenController::class, 'aktualisiereParzelle']);
    
    // 🗑️ Die prozessuale Vernichtungs-Mündung für Version 1 & Zeitschloss ab Version 2
    Route::post('/api/kataster/parzellen/ausbuchen/{uuid}', [GisLiegenschaftenController::class, 'loescheParzelle']);

    // 📡 Satelliten-Tunnel: Lädt die parzellenspezifischen Matrixdetails in Echtzeit in deine Sidebar
    Route::get('/api/kataster/parzelle-details/{uuid}', [GisLiegenschaftenController::class, 'holeParzelleDetails']);

    // ==========================================================================
    // 🏢 ENTERPRISE SECURITY PLATFORM: RECORD LOCKING & 4-EYES APPROVALS
    // ==========================================================================
    
    // 🔒 Sperr-Mechanismus (Record Locking): Blockiert kollidierende Bearbeitungen
    Route::post('/api/kataster/parzellen/lock/{uuid}', [GisLiegenschaftenController::class, 'lockParzelle']);
    
    // 🔓 Entsperr-Mechanismus: Gibt das Flurstück sofort nach dem Schließen des Modals wieder frei
    Route::post('/api/kataster/parzellen/unlock/{uuid}', [GisLiegenschaftenController::class, 'unlockParzelle']);

    // ⚖️ Admin-Mündung für das Vier-Augen-Prinzip: Gibt eine eingereichte Revision final für das Register frei
    Route::post('/api/kataster/parzellen/freigeben/{uuid}', [GisLiegenschaftenController::class, 'freigebeParzelleAudit']);

});

// BREEZE INTEGRATION: Lädt die namensgesicherte auth.php unzerbrechlich hinzu!
require __DIR__.'/auth.php';
