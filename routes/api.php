<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/iptvdoku/testConnection', [ApiController::class, 'test_connection_to_dokumentace'])->middleware('firewall');
Route::post('/iptvdoku/search/stream', [ApiController::class, 'search_stream_data_v_dokumentaci'])->middleware('firewall');
