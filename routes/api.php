<?php

use App\Http\Controllers\Api\V1\ErnteController;
use App\Http\Controllers\Api\V1\KellerwirtschaftController;
use App\Http\Controllers\Api\V1\PersonalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('ernte')->group(function () {
        Route::get('/kampagnen', [ErnteController::class, 'kampagnen']);
        Route::get('/lesegaenge', [ErnteController::class, 'lesegaenge']);
    });

    Route::prefix('kellerwirtschaft')->group(function () {
        Route::get('/gaarfaesser', [KellerwirtschaftController::class, 'gaarfaesser']);
        Route::get('/gaarprozesse', [KellerwirtschaftController::class, 'gaarprozesse']);
    });

    Route::prefix('personal')->group(function () {
        Route::get('/arbeitskraefte', [PersonalController::class, 'arbeitskraefte']);
        Route::get('/zeiterfassungen', [PersonalController::class, 'zeiterfassungen']);
    });

    Route::get('/health', function (Request $request) {
        return response()->json([
            'status' => 'ok',
            'app' => config('app.name', 'vinicore'),
            'timestamp' => now()->toISOString(),
        ]);
    });
});
