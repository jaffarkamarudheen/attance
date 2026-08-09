<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::post('/punch-in', [App\Http\Controllers\ApiController::class, 'punchIn'])->middleware('auth');
Route::post('/punch-out', [App\Http\Controllers\ApiController::class, 'punchOut'])->middleware('auth');
Route::post('/web-network-log', [App\Http\Controllers\ApiController::class, 'webNetworkLog'])->middleware('auth');
Route::post('/admin/employees', [App\Http\Controllers\HomeController::class, 'storeEmployee'])->middleware('auth');
Route::get('/admin/reports/pdf', [App\Http\Controllers\HomeController::class, 'exportPdf'])->middleware('auth');
Route::get('/admin/reports/excel', [App\Http\Controllers\HomeController::class, 'exportExcel'])->middleware('auth');
Route::get('/admin/reports/daily-excel', [App\Http\Controllers\HomeController::class, 'exportDailyExcel'])->middleware('auth');
