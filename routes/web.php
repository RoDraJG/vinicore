<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Modules\Kataster\Controllers\GisLiegenschaftenController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }
    return redirect()->route('login');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    
    // 🗺️ HTML-WEICHE: Die Kartenwebseite lädt hier direkt das HTML-Skelett
    Route::get('/kataster/parzellen-karte', function () {
        return view('kataster.parzellen_karte');
    })->name('kataster.karte');
    
    // 🛰️ API-WEICHE: Die API-Route zapft direkt die funktionierende JSON-Methode an!
    // Das verhindert ein für alle Mal, dass die HTML-Seite (map.base.blade) als JSON ausgegeben wird.
    Route::get('/api/geojson/parzellen', [GisLiegenschaftenController::class, 'holeGespeicherteParzellenAusDatenbank']);

    // Die saubere API-Präfix-Gruppe für deine Kataster-Schnittstellen
    Route::group(['prefix' => 'api/kataster'], function () {
        Route::get('/get-parzellen', [GisLiegenschaftenController::class, 'holeGespeicherteParzellenAusDatenbank'])->name('kataster.parzellen.laden');
        
        // 🚀 DIE VERMISSTE DETAIL-ROUTE (Fängt den UUID-Aufruf deines Widgets perfekt ab!):
        Route::get('/parzelle-details/{uuid}', [GisLiegenschaftenController::class, 'holeParzelleDetails'])->name('kataster.parzelle.details');
        
        Route::post('/umgebung-laden', [GisLiegenschaftenController::class, 'ladeUmgebungVomGeoportal'])->name('kataster.umgebung.laden');
        Route::post('/suchen-im-geoportal', [GisLiegenschaftenController::class, 'suchenImGeoportal'])->name('kataster.geoportal.suche');
        Route::post('/parzelle-speichern', [GisLiegenschaftenController::class, 'spechereParzelleImSammelKorb'])->name('kataster.parzelle.speichern');
        Route::delete('/parzelle-loeschen/{uuid}', [GisLiegenschaftenController::class, 'loescheParzelleAusSammelKorb'])->name('kataster.parzelle.loeschen');
    });


    // Die Gruppe für deine Finanz- und Vertragsdomäne
    Route::group(['prefix' => 'finanzen'], function () {
        Route::get('/vertrag-anlegen', function () {
            return view('finanzen.vertrag_anlegen');
        })->name('finanzen.vertrag_anlegen');
        
        Route::post('/vertrag/entwurf-initialisieren', [GisLiegenschaftenController::class, 'initialisiereEntwurf'])->name('finanzen.vertrag.entwurf.init');
        Route::post('/vertrag/entwurf-parzellen-sync', [GisLiegenschaftenController::class, 'synchronisiereEntwurfsParzellen'])->name('finanzen.vertrag.entwurf.sync');
        Route::post('/vertrag/final-versiegeln', [GisLiegenschaftenController::class, 'finalVersiegeln'])->name('finanzen.vertrag.versiegeln');
    });

    Route::group(['prefix' => 'admin'], function () {
        // Zukünftiges ACL-Schaltpult
    });
});


