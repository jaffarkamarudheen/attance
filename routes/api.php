<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ApiController;

Route::post('/login', [ApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/punch-in', [ApiController::class, 'punchIn']);
    Route::post('/punch-out', [ApiController::class, 'punchOut']);
    Route::post('/network-log', [ApiController::class, 'networkLog']);
});
