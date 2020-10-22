<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public $hello_dokumentace = "d4c3ed93-3768-48c0-98b6-1717108157e9";
    public $iptvdokuUriApiConnectionTest = "http://iptvdoku.grapesc.cz/api/connectionTest";
    public $iptvdokuUriApiStreamInfo = "http://iptvdoku.grapesc.cz/api/channel/search";

    /**
     * funkce na testování pripojení do dokumentace
     *
     * @return void
     */
    public function test_connection_to_dokumentace()
    {

        return $response = Http::get($this->iptvdokuUriApiConnectionTest, [
            'hello' => $this->hello_dokumentace,
        ]);
    }


    /**
     * funkce na vypsání informací o streamu z dokumenatce
     *
     * @param Request $request
     * @return void
     */
    public function search_stream_data_v_dokumentaci(Request $request)
    {

        return $response = Http::get($this->iptvdokuUriApiStreamInfo, [
            'hello' => $this->hello_dokumentace,
            'stream_url' => Stream::where('id', $request->streamId)->first()->stream_url
        ]);
    }
}
