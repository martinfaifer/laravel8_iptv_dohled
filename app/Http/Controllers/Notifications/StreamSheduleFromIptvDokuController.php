<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Models\Stream;
use App\Models\StreamHistory;
use App\Models\StreamSheduleFromIptvDoku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StreamSheduleFromIptvDokuController extends Controller
{

    /**
     * funkce na ověření zda exsituje záznam
     *
     * @return array
     */
    public static function check_if_exist(): array
    {
        if (StreamSheduleFromIptvDoku::first()) {
            return [
                'status' => "exist"
            ];
        } else {
            return [
                'status' => "empty"
            ];
        }
    }


    /**
     * funkcne na vypnutí nebo zapnutí notifikací
     *
     * @return void
     */
    public static function check_time_to_schedule()
    {
        $checkIfIsSchedule = self::check_if_exist();

        if ($checkIfIsSchedule['status'] === "exist") {
            $day = date("Y-m-d", time());

            date_default_timezone_set('Europe/Prague');
            $time = date('H:i', time());
            // $time = date("H:m", time());
            // vyhledání kanálů pro vypnutí dohledu
            if (StreamSheduleFromIptvDoku::where('start_day', $day)->where('start_time', $time)->first()) {

                StreamSheduleFromIptvDoku::where('start_day', $day)->where('start_time', $time)->get()->each(function ($streamForDisableNotification) {
                    Stream::where('id', $streamForDisableNotification->streamId)->update(
                        [
                            'sendMailAlert' => 0,
                            'sendSmsAlert' => 0
                        ]
                    );

                    StreamHistory::create([
                        'stream_id' => $streamForDisableNotification->streamId,
                        'status' => "sheduler_disable"
                    ]);
                });
            }

            // pokud má every_day jinou hodnotu nez null, tak se dohleduje pouze cas
            if (StreamSheduleFromIptvDoku::where('every_day', "!=", null)->where('start_time', $time)->first()) {

                StreamSheduleFromIptvDoku::where('every_day', "!=", null)->where('start_time', $time)->get()->each(function ($streamForDisableEveryDayNotification) {
                    Stream::where('id', $streamForDisableEveryDayNotification->streamId)->update(
                        [
                            'sendMailAlert' => 0,
                            'sendSmsAlert' => 0
                        ]
                    );

                    StreamHistory::create([
                        'stream_id' => $streamForDisableEveryDayNotification->streamId,
                        'status' => "sheduler_disable"
                    ]);
                });
            }


            // zapnutí notifikací, pokud nadešel správný čas a odebrání události
            if (StreamSheduleFromIptvDoku::where('end_day', $day)->where('end_time', $time)->first()) {

                StreamSheduleFromIptvDoku::where('end_day', $day)->where('end_time', $time)->get()->each(function ($streamForEnableNotification) {
                    Stream::where('id', $streamForEnableNotification->streamId)->update(
                        [
                            'sendMailAlert' => 1,
                        ]
                    );

                    StreamHistory::create([
                        'stream_id' => $streamForEnableNotification->streamId,
                        'status' => "sheduler_enable"
                    ]);

                    // odebrání události
                    StreamSheduleFromIptvDoku::where('id', $streamForEnableNotification->id)->delete();
                });
            }

            // pokud má every_day jinou hodnotu nez null, tak se dohleduje pouze cas
            if (StreamSheduleFromIptvDoku::where('every_day', "!=", null)->where('end_time', $time)->first()) {

                StreamSheduleFromIptvDoku::where('every_day', "!=", null)->where('end_time', $time)->get()->each(function ($streamForEnableEveryDayNotification) {
                    Stream::where('id', $streamForEnableEveryDayNotification->streamId)->update(
                        [
                            'sendMailAlert' => 1,
                        ]
                    );

                    StreamHistory::create([
                        'stream_id' => $streamForEnableEveryDayNotification->streamId,
                        'status' => "sheduler_enable"
                    ]);
                });
            }
        }
    }

    /**
     * funkcne na vyhledání a navrácení informací o streamu, kdy se má nebo nemá dohledovat
     *
     * @param Request $request->streamId
     * @return array
     */
    public static function return_shedule_data(Request $request): array
    {
        if (StreamSheduleFromIptvDoku::where('streamId', $request->streamId)->first()) {

            foreach (StreamSheduleFromIptvDoku::where('streamId', $request->streamId)->get() as $sheduleData) {

                if (is_null($sheduleData->every_day)) {
                    $data[] = array(
                        'start' => $sheduleData->start_day . " " . $sheduleData->start_time,
                        'end' => $sheduleData->end_day . " " . $sheduleData->end_time
                    );
                } else {
                    $data[] = array(
                        'start' => "Každý den od " . $sheduleData->start_time,
                        'end' => "Každý den do " . $sheduleData->end_time
                    );
                }
            }

            return $data;
        } else {
            return [
                'status' => "empty"
            ];
        }
    }

    /**
     * funkce na notifikace , zda je na dnesek plánovaný výpadek kanálu
     *
     * @param Request $request
     * @return array
     */
    public static function check_if_today_is_shedule(Request $request): array
    {
        $day = date("Y-m-d", time());
        if (StreamSheduleFromIptvDoku::where('streamId', $request->streamId)->where('start_day', $day)->first()) {
            // pro dnesni den je nalezena udalost, ta se bude následně zobrazovat u streamu
            foreach (StreamSheduleFromIptvDoku::where('streamId', $request->streamId)->where('start_day', $day)->get() as $todayEvent) {
                $data[] = array(
                    'start' => $todayEvent->start_time,
                    'end' => $todayEvent->end_time
                );
            }
            return $data;
        } else if (StreamSheduleFromIptvDoku::where('streamId', $request->streamId)->where('every_day', "!=", null)->first()) {
            foreach (StreamSheduleFromIptvDoku::where('streamId', $request->streamId)->where('every_day', "!=", null)->get() as $eventEveryDay) {
                $data[] = array(
                    'start' => $eventEveryDay->start_time,
                    'end' => $eventEveryDay->end_time
                );
            }

            return $data;
        } else {
            return [
                'status' => "empty"
            ];
        }
    }

    /**
     * funkce na vypsání všech dnesních událostí, pokud neexistuje zádná událost, bude vráceno pole "status" => "empty
     *
     * @return array
     */
    public static function return_all_today_events(): array
    {
        $day = date("Y-m-d", time());
        if (StreamSheduleFromIptvDoku::where('start_day', $day)->first()) {
            // pro dnesni den je nalezena udalost, ta se bude následně zobrazovat u streamu
            foreach (StreamSheduleFromIptvDoku::where('start_day', $day)->get() as $todayEvent) {
                $data[] = array(
                    'stream' => Stream::where('id', $todayEvent->streamId)->first()->nazev,
                    'start' => $todayEvent->start_time,
                    'end' => $todayEvent->end_time
                );
            }
        } else if (StreamSheduleFromIptvDoku::where('every_day', "!=", null)->first()) {
            foreach (StreamSheduleFromIptvDoku::where('every_day', "!=", null)->get() as $eventEveryDay) {
                $data[] = array(
                    'stream' => Stream::where('id', $eventEveryDay->streamId)->first()->nazev,
                    'start' => $eventEveryDay->start_time,
                    'end' => $eventEveryDay->end_time
                );
            }
        } else {
            $data = [
                'status' => "empty"
            ];
        }

        return $data;
    }


    /**
     * funkce na vytvoření nové události
     *
     * @param Request $request
     * @return void
     */
    public static function create_new_event(Request $request): void
    {

        // vyhledání uri u dohledovanáho streamu , zasilá se z docu multicast, h264 a h265
        if (Stream::where('stream_url', $request->multicastUri)->first()) {
            // zalození id ID streamu
            $stream = Stream::where('stream_url', $request->multicastUri)->first();
        } else if (Stream::where('stream_url', $request->h264Uri)->first()) {
            // zalození id ID streamu
            $stream = Stream::where('stream_url', $request->h264Uri)->first();
        } else if (Stream::where('stream_url', $request->h265Uri)->first()) {
            // zalození id ID streamu
            $stream = Stream::where('stream_url', $request->h265Uri)->first();
        }

        if ($stream) {
            StreamSheduleFromIptvDoku::create(
                [
                    'streamId' => $stream->id,
                    'start_day' => $request->start_day ?? null,
                    'start_time' => $request->start_time,
                    'end_day' => $request->end_day ?? null,
                    'end_time' => $request->end_time,
                    'every_day' => $request->every_day ?? null
                ]
            );
        }
    }


    /**
     * funkce na odebání události z dohledu
     *
     * @param Request $request
     * @return void
     */
    public static function delete_event(Request $request): void
    {
        Log::info("jsme ve funkci delete_event");
        // vyhledání uri u dohledovanáho streamu , zasilá se z docu multicast, h264 a h265
        if (Stream::where('stream_url', $request->multicastUri)->first()) {
            // zalození id ID streamu
            $stream = Stream::where('stream_url', $request->multicastUri)->first();
        } else if (Stream::where('stream_url', $request->h264Uri)->first()) {
            // zalození id ID streamu
            $stream = Stream::where('stream_url', $request->h264Uri)->first();
        } else if (Stream::where('stream_url', $request->h265Uri)->first()) {
            // zalození id ID streamu
            $stream = Stream::where('stream_url', $request->h265Uri)->first();
        }


        // pokud byl nalezen stream tak se musí vyhledat shoda v start_time a end_time
        if ($stream) {
            Log::info("vyhledán stream" . $stream->id);
            if ($dataToDelete = StreamSheduleFromIptvDoku::where('streamId', $stream->id)->where('start_time', $request->start_time)->where('end_time', $request->end_time)->first()) {
                StreamSheduleFromIptvDoku::where('id', $dataToDelete->id)->delete();
            } else {
                Log::info("nebyla vyhledána shoda");
            }
        }
    }
}
