<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Modules\Kataster\Controllers\GisLiegenschaftenController;
use App\Modules\CRM\Controllers\CrmController;

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
    // 🏪 2. DOMÄNE: CENTRAL CRM (Fenster-Erfassung aktiviert)
    // ======================================================================
    Route::group(['prefix' => 'crm', 'middleware' => ['web', 'auth']], function () {
        // 🗂️ Übersicht & Register (Kunden/Lieferanten)
        Route::get('/', [CrmController::class, 'index'])->name('crm.index');
        
        // ➕ Partner-Erfassung (Formular & Speichern)
        Route::get('/create', [CrmController::class, 'create'])->name('crm.create');
        Route::post('/store', [CrmController::class, 'store'])->name('crm.store');
        
        // 🔍 Die detaillierte Partner-Akte (Details-Button)
        Route::get('/{id}', [CrmController::class, 'show'])->name('crm.show');
    });


    Route::group(['prefix' => 'finanzen', 'middleware' => 'can:check-permission,finanzen.view'], function () {
        Route::get('/vertrag-anlegen', function () {
            return view('finanzen.vertrag_anlegen');
            })->name('finanzen.vertrag_anlegen');
        Route::post('/vertrag/entwurf-initialisieren', [GisLiegenschaftenController::class, 'initialisiereEntwurf'])->name('finanzen.vertrag.entwurf.init');
        Route::post('/vertrag/entwurf-parzellen-sync', [GisLiegenschaftenController::class, 'synchronisiereEntwurfsParzellen'])->name('finanzen.vertrag.entwurf.sync');
        Route::post('/vertrag/final-versiegeln', [GisLiegenschaftenController::class, 'finalVersiegeln'])->name('finanzen.vertrag.versiegeln');});
        Route::group(['prefix' => 'admin'], function () {// Zukünftiges ACL-Schaltpult
    });
});