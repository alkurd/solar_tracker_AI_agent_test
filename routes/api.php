<?php

use App\Http\Controllers\TrackerDataController;
use Illuminate\Support\Facades\Route;

Route::post('/tracker/logs', [TrackerDataController::class, 'store'])->name('api.tracker.logs.store');