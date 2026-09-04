<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Modules\Kataster\Controllers\GisLiegenschaftenController;
use App\Modules\CRM\Controllers\CRMController;
use App\Modules\Configuration\Controllers\GlobalConfigController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }
    return redirect()->route('login');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/kataster/parzellen-karte', [GisLiegenschaftenController::class, 'index'])->name('kataster.karte')->middleware('can:check-permission,kataster.view');
    Route::get('/api/geojson/parzellen', [GisLiegenschaftenController::class, 'index']);
    
    Route::group(['prefix' => 'api/kataster'], function () {
        Route::get('/get-parzellen', [GisLiegenschaftenController::class, 'holeGespeicherteParzellenAusDatenbank'])->name('kataster.parzellen.laden');
        Route::get('/parzelle-details/{uuid}', [GisLiegenschaftenController::class, 'holeParzelleDetails'])->name('kataster.parzelle.details');
        Route::post('/umgebung-laden', [GisLiegenschaftenController::class, 'ladeUmgebungVomGeoportal'])->name('kataster.umgebung.laden');
        Route::post('/suchen-im-geoportal', [GisLiegenschaftenController::class, 'suchenImGeoportal'])->name('kataster.geoportal.suche');
        Route::post('/parzelle-speichern', [GisLiegenschaftenController::class, 'spechereParzelleImSammelKorb'])->name('kataster.parzelle.speichern');
        Route::delete('/parzelle-loeschen/{uuid}', [GisLiegenschaftenController::class, 'loescheParzelleAusSammelKorb'])->name('kataster.parzelle.loeschen');
    });
    // ======================================================================
    // 🏪 2. DOMÄNE: CENTRAL CRM (Schnittstellen-Altlasten entfernt)
    // ======================================================================
    Route::group(['prefix' => 'crm', 'middleware' => ['web', 'auth']], function () {
        // 1. Übersichten und Formulare (Spezifische Pfade immer nach oben!)
        Route::get('/', [CRMController::class, 'index'])->name('crm.index');
        Route::get('/create', [CRMController::class, 'create'])->name('crm.create');
        Route::post('/store', [CRMController::class, 'store'])->name('crm.store');
        
        // 2. Das Bearbeitungsformular (Spezifischer als der nackte ID-Platzhalter)
        Route::get('/{id}/edit', [CRMController::class, 'edit'])->name('crm.edit');
        
        // 3. ID-Platzhalter (Immer ganz nach unten, damit sie nichts abfangen!)
        Route::get('/{id}', [CRMController::class, 'show'])->name('crm.show');
        Route::put('/{id}', [CRMController::class, 'update'])->name('crm.update');
    });
    // ======================================================================
    // ⚙️ 3. DOMÄNE: CENTRAL CONFIGURATION & ADMIN-HUB
    // ======================================================================
    Route::group(['prefix' => 'admin', 'middleware' => ['web', 'auth', 'can:check-permission,admin.view']], function () {
        
        // Zentrales ERP-Konfigurationszentrum (Tabs über URL-Parameter ?tab=...)
        Route::get('/konfiguration/einstellungen', [GlobalConfigController::class, 'index'])->name('admin.einstellungen');
        Route::post('/konfiguration/nummernkreise', [GlobalConfigController::class, 'speichereNummernkreise'])->name('admin.nummernkreise.store');
        
    });


    Route::group(['prefix' => 'finanzen', 'middleware' => 'can:check-permission,finanzen.view'], function () {
        Route::get('/vertrag-anlegen', function () {
            return view('finanzen.vertrag_anlegen');
            })->name('finanzen.vertrag_anlegen');
        Route::post('/vertrag/entwurf-initialisieren', [GisLiegenschaftenController::class, 'initialisiereEntwurf'])->name('finanzen.vertrag.entwurf.init');
        Route::post('/vertrag/entwurf-parzellen-sync', [GisLiegenschaftenController::class, 'synchronisiereEntwurfsParzellen'])->name('finanzen.vertrag.entwurf.sync');
        Route::post('/vertrag/final-versiegeln', [GisLiegenschaftenController::class, 'finalVersiegeln'])->name('finanzen.vertrag.versiegeln');});

});