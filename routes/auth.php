<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------==========================================
| 🔐 GÄSTE-ZONE (BREEZE-AUTH-PIPELINE)
|--------------------------------==========================================
*/
Route::middleware('guest')->group(function () {
    // 🚀 CORE-FIX: Das ->name('login') ist jetzt fest in der auth.php verschraubt!
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

/*
|--------------------------------==========================================
| 🛡️ WORKER-ZONE (LOGOUT-SCHALTKREIS)
|--------------------------------==========================================
*/
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
