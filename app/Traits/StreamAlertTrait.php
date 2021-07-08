<?php

namespace App\Traits;

use App\Models\Stream;
use Illuminate\Support\Facades\Cache;

trait StreamAlertTrait
{
    public function alerts()
    {
        $alerts = [];

        // výpis z cache , pokud existuje, je problem
        foreach (Stream::where('dohledovano', true)->where('status', 'running')->get() as $stream) {
            if (Cache::has("stream" . $stream->id)) {
                $alerts[][$stream->id] =  Cache::get("stream" . $stream->id, 'default');
            }

            if (Cache::has("stream" . $stream->id . "_resync")) {
                $alerts[][$stream->id] =  Cache::get("stream" . $stream->id . "_resync", 'default');
            }
        }
        if (empty($alerts)) {
            return $alerts;
        }
        foreach ($alerts as $alert) {
            foreach ($alert as $alert_key => $alert_value) {
                if (array_key_exists('status', $alert_value)) {
                    $output_array[] = $this->sort_stream_status_by_data($alert_value, $alert_key);
                }
            }
        }

        if (empty($output_array)) {
            return [];
        }

        return $output_array;
    }


    protected function sort_stream_status_by_data($stream, int $streamId)
    {
        switch ($stream['status']) {
            case "error":
                // error status
                return [
                    'status' => "error",
                    'msg' => "{$stream["stream"]} nefunguje!",
                    'id' => $streamId
                ];
                break;
            case "issue":

                switch ($stream['msg']) {
                    case 'Audio video resync!':
                        return [
                            'status' => "warning",
                            'msg' => "{$stream["stream"]} problém se zvukem",
                            'id' => $streamId
                        ];
                        break;

                    case 'Video má nulový datový tok!':
                        return [
                            'status' => "warning",
                            'msg' => "{$stream["stream"]} video má nulový datový tok!",
                            'id' => $streamId
                        ];
                        break;

                    case 'Audio má nulový datový tok!':
                        return [
                            'status' => "warning",
                            'msg' => "{$stream["stream"]} audio má nulový datový tok!",
                            'id' => $streamId
                        ];
                        break;

                    case 'Audio se nedekryptuje':
                        return [
                            'status' => "warning",
                            'msg' => "{$stream["stream"]} audio se nedekryptuje!",
                            'id' => $streamId
                        ];
                        break;

                    case 'video se nedekryptuje':
                        return [
                            'status' => "warning",
                            'msg' => "{$stream["stream"]} video se nedekryptuje!",
                            'id' => $streamId
                        ];
                        break;

                    case 'Audio nebo video se nedekryptuje!':
                        return [
                            'status' => "warning",
                            'msg' => "{$stream["stream"]} audio nebo video se nedekryptuje!",
                            'id' => $streamId
                        ];
                        break;

                    case '"Chyba ve streamu':
                        return [
                            'status' => "warning",
                            'msg' => "{$stream["stream"]} problém se streamem",
                            'id' => $streamId
                        ];
                        break;

                    default:
                        return [
                            'status' => "warning",
                            'msg' => "{$stream["stream"]} neznámá chyba!",
                            'id' => $streamId
                        ];
                        break;
                }

                break;
            default:
                // neznámí status
                return [
                    'status' => "error",
                    'msg' => "Neznámý status streamu",
                    'id' => $streamId
                ];
        }
    }
}
