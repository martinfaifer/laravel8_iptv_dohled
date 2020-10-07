<?php

use App\Events\StreamNotification;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\SystemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
})->middleware('firewall');


// výpis alertů streamů
Route::get('streamAlerts', [StreamController::class, 'show_problematic_streams_as_alerts'])->middleware('firewall');


// systémové pozadavky
Route::get('system', [SystemController::class, 'checkSystemUsage'])->middleware('firewall');
