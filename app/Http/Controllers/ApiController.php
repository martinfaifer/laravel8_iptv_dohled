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

        try {
            return $response = Http::get($this->iptvdokuUriApiConnectionTest, [
                'hello' => $this->hello_dokumentace,
            ]);
        } catch (\Throwable $th) {
            return "error";
        }
    }


    public function stream_analyze(Request $request): array
    {
        $tsDuckData = shell_exec("timeout -s SIGKILL 3 tsp -I ip {$request->streamUrl} -P until -s 1 -P analyze --normalized -O drop");

        if (is_null($tsDuckData) || $tsDuckData === "Killed" || empty($tsDuckData)) {
            return [
                'status' => "error",
                'data' => []
            ];
        }

        return [
            'status' => "success",
            'data' => $tsduckArr = DiagnosticController::convert_tsduck_string_to_array($tsDuckData)
        ];
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


    public function get_information_about_stream_by_streamId(Request $request)
    {
        if ($this->test_connection_from_another_system_to_this($request) != "success") {
            return [
                'status' => "not_connected"
            ];
        }

        return StreamController::get_information_about_streams_by_streamId($request);
    }


    public function create_stream(Request $request)
    {
        if ($this->test_connection_from_another_system_to_this($request) != "success") {
            return [
                'status' => "not_connected"
            ];
        }

        // vyhledání, zda již stream neexistuje

        if ($stream = Stream::where('stream_url', $request->stream_url)->first()) {
            return [
                "status" => "success",
                "channelId" => $stream->id
            ];
        } else {
            // zalození streamu
            Stream::create(
                [
                    'nazev' => $request->nazev,
                    "stream_url" => $request->stream_url,
                    "dohledovano" => $request->dohledovano,
                    "vytvaretNahled" => $request->vytvaretNahled,
                    "status" => "waiting"
                ]
            );

            return [
                "status" => "success",
                "channelId" => Stream::where('stream_url', $request->stream_url)->first()->id
            ];
        }
    }


    public static function find_stream_and_return_id(Request $request)
    {
        if (!isset($request->stream_url)) {
            return null;
        }

        if (!$stream = Stream::where('stream_url', $request->stream_url)->first()) {
            return null;
        }

        return $stream->id;
    }


    public static function find_channel_logo(int $streamId): string
    {
        return $response = Http::get('http://iptvdoku.grapesc.cz/api/v2/channel/logo', [
            'is_dohled' => true,
            'streamId' => $streamId
        ]);
    }
}
