<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use Illuminate\Http\Request;
use React\EventLoop\Factory;


class TestStreamController extends Controller
{

    /**
     * fn pro prihlášení multicástů per stream
     *
     * pokud stream nabyde statusu stop => ukocení
     *
     *
     *
     * @param string $streamId
     * @return void
     */
    public static function test_to_hold_multicast(string $streamId): void
    {
        // event loop per stream
        $eventLoop = Factory::create();

        // loop, kdy se kazdou "s" testuje ctení
        $eventLoop->addPeriodicTimer(1, function () use ($streamId) {

            $stream = Stream::where('id', $streamId)->first();

            // rozlození url na uri a port

            $url = explode(":", $stream->stream_url);

            $socket = socket_create(AF_INET, SOCK_RAW, SOL_UDP);
            $result = socket_connect($socket, $url[0], $url[1]);
            if ($result === false) {

                // odpálí event, který ukončí dohled ???

            } else {
                socket_close($socket);
                $socket = socket_create(AF_INET, SOCK_RAW, SOL_UDP);
                socket_bind($socket, $url[0], $url[1]);

                $buf = "";
                echo (socket_recv($socket, $buf, 2048, MSG_WAITALL) . PHP_EOL);
            }


            // ukončení event loopu

            if ($stream->status == "stop") {

                // odpojení od socketu
                socket_close($socket);
                // ukoncení procesu

                StreamController::stop_diagnostic_stream_from_backend($stream->socket_process_pid);
            }
        });

        $eventLoop->run();
    }
}
