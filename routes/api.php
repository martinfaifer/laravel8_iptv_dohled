<?php

use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\Streams\StreamController;
use Illuminate\Support\Facades\Route;

/**
 * API V1
 */
Route::get('/iptvdoku/testConnection', [ApiController::class, 'test_connection_to_dokumentace']);
Route::post('/iptvdoku/search/stream', [ApiController::class, 'search_stream_data_v_dokumentaci']);
Route::get('/iptvdoku/get/streams_for_monitoring', [ApiController::class, 'get_streams_for_monitoring_from_dohled']);


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


/**
 *  API V2
 */
Route::group(['prefix' => 'v2'], function () {
    Route::group(['prefix' => 'stream'], function () {
        Route::post('', [ApiController::class, 'get_information_about_stream_by_streamId']);
        Route::post('create', [ApiController::class, 'create_stream']);
        Route::delete('', [StreamController::class, 'delete']);
        Route::post('byUri', [ApiController::class, 'get_information_about_stream']);
        Route::post('analyze',  [ApiController::class, 'stream_analyze']);
    });

    Route::group(['prefix' => 'event'], function () {
        Route::post('', [ApiController::class, 'create_new_event']);
        Route::delete('', [ApiController::class, 'delete_event']);
    });
});
