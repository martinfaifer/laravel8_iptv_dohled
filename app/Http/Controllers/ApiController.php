<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public $hello_dokumentace = ""; // api klíč doku 
    public $hello_dohled = ""; // api klíč dohledu
    public $iptvdokuUriApiConnectionTest = ""; // url pro auth pripojení
    public $iptvdokuUriApiStreamInfo = ""; // url pro vyhledání informací o streamu v docu
    public $iptvdokuUriApiStreams = ""; // url pro výpis všech streamů pro založení zde do dohledu 

    /**
     * funkce na testování pripojení do dokumentace
     *
     * @return void
     */
    public function test_connection_to_dokumentace()
    {

        try {
            return $response = Http::get($this->iptvdokuUriApiConnectionTest, [
                'hello' => $this->hello_dokumentace,
            ]);
        } catch (\Throwable $th) {
            return "error";
        }
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


    /**
     * funknce na pridaní nové událsti do dohledu z extrerního systemu
     *
     * @param Request $request
     * @return void
     */
    public function create_new_event(Request $request)
    {
        // nutné vyhledat k jakému streamu se má založit událost

        if ($this->test_connection_from_another_system_to_this($request) != "success") {
            return [
                'status' => "not_connected"
            ];
        }

        StreamSheduleFromIptvDokuController::create_new_event($request);
    }



    /**
     * funkce na odebrání události z sheduleru
     *
     * @param Request $request
     * @return void
     */
    public function delete_event(Request $request)
    {
        if ($this->test_connection_from_another_system_to_this($request) != "success") {
            return [
                'status' => "not_connected"
            ];
        }
        StreamSheduleFromIptvDokuController::delete_event($request);
    }


    public function get_information_about_stream(Request $request)
    {
        if ($this->test_connection_from_another_system_to_this($request) != "success") {
            return [
                'status' => "not_connected"
            ];
        }

        return StreamController::get_information_about_streams($request);
    }
}
