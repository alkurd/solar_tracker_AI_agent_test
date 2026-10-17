<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\TrackerController;

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::redirect('/', '/tracker');

Route::get('/tracker', [TrackerController::class, 'index'])->name('tracker.dashboard');

Route::middleware('auth')->prefix('tracker')->name('tracker.')->group(function () {
    Route::get('/control', [TrackerController::class, 'control'])->name('control');
    Route::post('/oost', [TrackerController::class, 'oost'])->name('oost');
    Route::post('/west', [TrackerController::class, 'west'])->name('west');
    Route::post('/poot-omhoog', [TrackerController::class, 'poot_omhoog'])->name('poot_omhoog');
    Route::post('/poot-omlaag', [TrackerController::class, 'poot_omlaag'])->name('poot_omlaag');
    Route::post('/mode/{mode}', [TrackerController::class, 'setMode'])->whereIn('mode', ['automatic', 'manual'])->name('mode');
    Route::post('/noodstop', [TrackerController::class, 'noodstop'])->name('noodstop');
    Route::post('/noodstop/reset', [TrackerController::class, 'resetNoodstop'])->name('noodstop.reset');
});
