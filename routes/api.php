<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\SelfHardwareCheckController;
use App\Http\Controllers\StreamController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/iptvdoku/testConnection', [ApiController::class, 'test_connection_to_dokumentace'])->middleware('firewall');
Route::post('/iptvdoku/search/stream', [ApiController::class, 'search_stream_data_v_dokumentaci']);
Route::get('/iptvdoku/get/streams_for_monitoring', [ApiController::class, 'get_streams_for_monitoring_from_dohled'])->middleware('firewall');


// externí API pro pripojeni
Route::post('/connectionTest', [ApiController::class, 'test_connection_from_another_system_to_this']);
Route::post('/streamAlerts', [ApiController::class, 'send_alerts_information_to_another_system']);
Route::post('/stream/search', [ApiController::class, 'find_stream_and_return_id']);

// přidání nového eventu k streamu, pro automatické vypnutí dohledu
Route::post('/new_event', [ApiController::class, 'create_new_event']);
// odebrání události z sheduleru
Route::post('/delete_event', [ApiController::class, 'delete_event']);
// výpis informací o streamech , dokumentace zasílá maximálně 3 typy Multicast, H264 a H265
Route::post('/getInformationAboutStream', [ApiController::class, 'get_information_about_stream']);


// vypsání informací o stream dle streamId
Route::post('/stream', [ApiController::class, 'get_information_about_stream_by_streamId']);
// remote založení streamu
Route::post('/stream/create', [ApiController::class, 'create_stream']);

Route::post('/stream/delete', [StreamController::class, 'delete']);

Route::post('/stream/analyze',  [ApiController::class, 'stream_analyze']);

// Route::get('system/cpu', [SelfHardwareCheckController::class, 'return_cpu_usage']);
