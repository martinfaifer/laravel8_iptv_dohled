<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public $hello_dokumentace = "d4c3ed93-3768-48c0-98b6-1717108157e9";
    public $hello_dohled = "873134d5-6324-4555-aa6d-fcdb1f7a9f4f";
    public $iptvdokuUriApiConnectionTest = "http://iptvdoku.grapesc.cz/api/connectionTest";
    public $iptvdokuUriApiStreamInfo = "http://iptvdoku.grapesc.cz/api/channel/search";
    public $iptvdokuUriApiStreams = "http://iptvdoku.grapesc.cz/api/channel/return";

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

    /**
     * funkce na získání všech streamů, co se dají dohledovat jak pro H264 tak i pro H265
     *
     * @return void
     */
    public function get_streams_for_monitoring_from_dohled()
    {
        return $response = Http::get($this->iptvdokuUriApiStreams, [
            'hello' => $this->hello_dokumentace,
        ]);
    }

    /**
     * funkce na otestování připojení do tohoto systému
     *
     * @param Request $request
     * @return void
     */
    public function test_connection_from_another_system_to_this(Request $request)
    {
        if ($request->hello == $this->hello_dohled) {
            return "success";
        } else {
            return "error";
        }
    }

    /**
     * funknce na vypsání alertů, co jsou aktuální
     *
     * @param Request $request
     * @return array
     */
    public function send_alerts_information_to_another_system(Request $request)
    {
        if ($this->test_connection_from_another_system_to_this($request) != "success") {
            return [
                'status' => "not_connected"
            ];
        }

        return StreamController::show_problematic_streams_as_alerts();
    }
}
