<?php

use App\Events\StreamNotification;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\StreamHistoryController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
})->middleware('firewall');


// výpis alertů streamů
Route::get('streamAlerts', [StreamController::class, 'show_problematic_streams_as_alerts'])->middleware('firewall');
// mozaika , pagination
Route::get('pagination', [StreamController::class, 'streams_for_mozaiku'])->middleware('firewall');
// StreamInfo -> image
Route::post('streamInfo/image', [StreamController::class, 'stream_info_image'])->middleware('firewall');
// StreamInfo -> Detailní informace o Streamu, pokud existuji ( u kanálů z ffproby data nejsou )
Route::post('streamInfo/detail', [StreamController::class, 'stream_info_detail'])->middleware('firewall');
// StreamInfo -> Výpis historie, posledních 10 zázamů
Route::post('streamInfo/history/10', [StreamHistoryController::class, 'stream_info_history_ten'])->middleware('firewall');
// StreamInfo -> Výpis z dokumentace pomocí api
Route::post('streamInfo/doku', [StreamController::class, 'stream_info_doku'])->middleware('firewall');



/**
 * USER BLOK
 */

Route::post('login', [UserController::class, 'loginUser'])->middleware('firewall');

// systémové pozadavky
Route::get('system', [SystemController::class, 'checkSystemUsage'])->middleware('firewall');

// Route::get('event', function () {
//     event(new StreamNotification());
//     // StreamNotification::dispatch();
//     return "odeslano";
// });
