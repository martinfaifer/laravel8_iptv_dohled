<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/iptvdoku/testConnection', [ApiController::class, 'test_connection_to_dokumentace'])->middleware('firewall');
Route::post('/iptvdoku/search/stream', [ApiController::class, 'search_stream_data_v_dokumentaci'])->middleware('firewall');
Route::get('/iptvdoku/get/streams_for_monitoring', [ApiController::class, 'get_streams_for_monitoring_from_dohled'])->middleware('firewall');


// externí API pro pripojeni
Route::post('/connectionTest', [ApiController::class, 'test_connection_from_another_system_to_this']);
Route::post('/streamAlerts', [ApiController::class, 'send_alerts_information_to_another_system']);

// přidání nového eventu k streamu, pro automatické vypnutí dohledu
Route::post('/new_event', [ApiController::class, 'create_new_event']);
// odebrání události z sheduleru
Route::post('/delete_event', [ApiController::class, 'delete_event']);
// výpis informací o streamech , dokumentace zasílá maximálně 3 typy Multicast, H264 a H265
Route::post('/getInformationAboutStream', [ApiController::class, 'get_information_about_stream']);
