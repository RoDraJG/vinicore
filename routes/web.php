<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// 🚀 ZWECKDIENLICHER ENTERPRISE-IMPORT:
use App\Modules\Kataster\Controllers\GisLiegenschaftenController;

// 🚀 DER AUTOMATISCHE ERP-GATEWAY: Ersetzt die Laravel-Startseite komplett!
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home'); // Eingeloggt? Ab ins Zentral-Cockpit!
    }
    return redirect()->route('login'); // Nicht eingeloggt? Direkt zum Login-Schnittpunkt!
});

// ... Hier folgen unverändert deine restlichen Routen-Gruppen (Auth::routes(), kataster, finanzen) ...


Auth::routes();

// --------------------------------------------------------------------------
// 🛡️ CENTRAL VINICORE CORE-PIPELINE (Globaler Login-Schutz)
// --------------------------------------------------------------------------
Route::middleware(['auth'])->group(function () {

    // Haupt-Dashboard (Einstiegspunkt für alle Benutzer)
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

        
    // ======================================================================
    // 🌿 1. DOMÄNE: KATASTER & AUSSENWIRTSCHAFT
    // ======================================================================
    Route::group(['prefix' => 'kataster'], function () {
        
        // Haupt-Ansichten & Schnittstellen
        Route::get('/parzellen-karte', [GisLiegenschaftenController::class, 'index'])->name('kataster.karte');
        Route::get('/get-parzellen', [GisLiegenschaftenController::class, 'holeGespeicherteParzellenAusDatenbank'])->name('kataster.parzellen.laden');
        
        // 🛰️ DYNAMISCHE SYNCHRONISATION MIT DEINEM ORIGINAL-CODE:
        // Verweist nun exakt auf deine bestehende Methode im Controller!
        Route::post('/umgebung-laden', [GisLiegenschaftenController::class, 'ladeUmgebungVomGeoportal'])->name('kataster.umgebung.laden');
        
        // Deine restlichen Schnittstellen
        Route::post('/suchen-im-geoportal', [GisLiegenschaftenController::class, 'suchenImGeoportal'])->name('kataster.geoportal.suche');
        Route::post('/parzelle-speichern', [GisLiegenschaftenController::class, 'spechereParzelleImSammelKorb'])->name('kataster.parzelle.speichern');
        Route::delete('/parzelle-loeschen/{uuid}', [GisLiegenschaftenController::class, 'loescheParzelleAusSammelKorb'])->name('kataster.parzelle.loeschen');
    });


    // ======================================================================
    // 💰 2. DOMÄNE: FINANZEN & VERTRAGSWESEN
    // ======================================================================
    Route::group(['prefix' => 'finanzen'], function () {
        
        // Ansichten / GUIs
        Route::get('/vertrag-anlegen', function () {
            return view('finanzen.vertrag_anlegen');
        })->name('finanzen.vertrag_anlegen');

        // 🛰️ API-Endpunkte für das neue Server-UUID-Entwurfsverfahren (Drafting-Pipeline)
        Route::post('/vertrag/entwurf-initialisieren', [GisLiegenschaftenController::class, 'initialisiereEntwurf'])->name('finanzen.vertrag.entwurf.init');
        Route::post('/vertrag/entwurf-parzellen-sync', [GisLiegenschaftenController::class, 'synchronisiereEntwurfsParzellen'])->name('finanzen.vertrag.entwurf.sync');
        Route::post('/vertrag/final-versiegeln', [GisLiegenschaftenController::class, 'finalVersiegeln'])->name('finanzen.vertrag.versiegeln');
    });

    // ======================================================================
    // ⚙️ 3. DOMÄNE: SYSTEM-ADMINISTRATION (Zukünftiges ACL-Schaltpult)
    // ======================================================================
    Route::group(['prefix' => 'admin'], function () {
        // Hier docken wir im nächsten Schritt die Steuerung für Rollen und Berechtigungen an
    });

});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
