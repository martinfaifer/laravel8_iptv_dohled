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
        foreach (Stream::all() as $stream) {
            if (Cache::has("stream" . $stream->id)) {
                $alerts[] =  Cache::get("stream" . $stream->id, 'default');
            }
        }

        if (empty($alerts)) {
            return $alerts;
        }

        foreach ($alerts as $alert) {
            $output_array[] = $this->sort_stream_status_by_data($alert);
        }

        return $output_array;
    }


    protected function sort_stream_status_by_data($stream)
    {
        switch ($stream['status']) {
            case "error":
                // error status
                return [
                    'status' => "error",
                    'msg' => "{$stream["stream"]} nefunguje!"
                ];
                break;
            case "issue":

                switch ($stream['msg']) {
                    case '"Chyba ve streamu':
                        return [
                            'status' => "warning",
                            'msg' => "{$stream["stream"]} zlobí"
                        ];
                        break;

                    default:
                        return [
                            'status' => "warning",
                            'msg' => "{$stream["stream"]} neznámá chyba"
                        ];
                        break;
                }

                break;
            default:
                // neznámí status
                return [
                    'status' => "error",
                    'msg' => "Neznámý status streamu"
                ];
        }
    }
}
