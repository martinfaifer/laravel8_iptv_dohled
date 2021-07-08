<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Models\Stream;
use App\Models\StreamNotificationLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StreamNotificationLimitController extends Controller
{

    /**
     * funkce na overení zda kanál existuje v tabulce, zjistení, zda má nastavené hodnoty na alerting a vyvolání alertingu
     *
     * @param string $streamId
     * @param string $video_bitrate
     * @param string $video_discontinuities
     * @param string $video_scrambled
     * @param string $audio_discontinuities
     * @param string $audio_scrambled
     * @return void
     */
    public static function check_stream_if_exist_in_table_and_check_values_and_probably_return_eventNotification(
        string $streamId,
        string $video_bitrate,
        string $video_discontinuities,
        string $video_scrambled,
        string $audio_discontinuities,
        string $audio_scrambled
    ) {
        if ($streamLimitData = StreamNotificationLimit::where('stream_id', $streamId)->first()) {
            // existuje , bude provedena analýza
            $streamInfoData = Stream::where('id', $streamId)->first();
            self::find_and_check_data($streamLimitData, $streamInfoData, $video_bitrate, "video_bitrate");
            self::find_and_check_data($streamLimitData, $streamInfoData, $video_discontinuities, "video_discontinuities");
            self::find_and_check_data($streamLimitData, $streamInfoData, $video_scrambled, "video_scrambled");
            self::find_and_check_data($streamLimitData, $streamInfoData, $audio_discontinuities, "audio_discontinuities");
            self::find_and_check_data($streamLimitData, $streamInfoData, $audio_scrambled, "audio_scrambled");
        }
    }

    /**
     * funknce, kdy se posle pole s daty, zasle se hodnota, terá se má kontrolovat a kde se má najít
     *
     * @param [array] $streamLimitData
     * @param string $valueForCheck
     * @param string $whatCanBeCheckt
     * @return void
     */
    public static function find_and_check_data($streamLimitData, $streamInfoData, string $valueForCheck, string $whatCanBeCheckt): void
    {
        // vyhledání co se má hlídat (video_bitrate ... )
        if ($streamLimitData->$whatCanBeCheckt >= $valueForCheck) {
            // notify ...
            self::send_notification_to_user($whatCanBeCheckt, $streamInfoData['id'], $streamInfoData['nazev']);
        }
    }

    /**
     * funkce, která vrátí informace o uložených stavech
     *
     * @param Request $request->streamId
     * @return array
     */
    public static function get_information_for_editation(Request $request): array
    {

        if ($informationAboutStream = StreamNotificationLimit::where('stream_id', $request->streamId)->first()) {

            return [
                'status' => "success",
                'video_discontinuities' => $informationAboutStream->video_discontinuities,
                'video_scrambled' => $informationAboutStream->video_scrambled,
                'audio_discontinuities' => $informationAboutStream->audio_discontinuities,
                'audio_scrambled' => $informationAboutStream->audio_scrambled
            ];
        } else {
            return [
                'status' => "empty"
            ];
        }
    }

    /**
     * funkce na přidání streamu do notifikačního limitu
     *
     * @param string $stremId
     * @return void
     */
    public static function add_stream_to_notification_limit($streamId, $video_discontinuities, $audio_discontinuities, $audio_scrambled): void
    {
        StreamNotificationLimit::create([
            'stream_id' => $streamId,
            'video_discontinuities' => $video_discontinuities,
            'audio_discontinuities' => $audio_discontinuities,
            'audio_scrambled' => $audio_scrambled
        ]);
    }

    /**
     * update záznamu
     *
     * @param string $streamId
     * @param string $video_discontinuities
     * @param string $audio_discontinuities
     * @param string $audio_scrambled
     * @return void
     */
    public static function update_stream_limit_for_notification($streamId, $video_discontinuities, $audio_discontinuities, $audio_scrambled): void
    {
        StreamNotificationLimit::where('stream_id', $streamId)->update(
            [
                'video_discontinuities' => $video_discontinuities,
                'audio_discontinuities' => $audio_discontinuities,
                'audio_scrambled' => $audio_scrambled
            ]
        );
    }

    /**
     * funkce na odebání záznamu
     *
     * @param string $streamId
     * @return void
     */
    public static function delete_stream_limit_for_notification($streamId): void
    {
        StreamNotificationLimit::where('stream_id', $streamId)->delete();
    }

    /**
     * funkce na notifikaci chybovosti ( problémů )
     *
     * whatIsNotify => co se dohleduje v tomto případě se bude generovat dle switch case
     *
     * @param string $whatIsNotify
     * @return void
     */
    public static function send_notification_to_user(string $whatIsNotify, $streamId, $streamName): void
    {
        // parametry co nese $whatIsNotify = video_bitrate || video_discontinuities || video_scrambled  || audio_discontinuities || audio_scrambled
        switch ($whatIsNotify) {
            case 'video_bitrate':
                # video_bitrate...
                // dispatch JOB SendStreamNotificationProblem
                EmailNotificationController::send_information_about_problem_stream("video bitratem", $streamId, $streamName, env("APP_URL") . "/#/stream/" . $streamId);
                break;
            case 'video_discontinuities':
                # video_discontinuities...
                EmailNotificationController::send_information_about_problem_stream("video CCR errory", $streamId, $streamName, env("APP_URL") . "/#/stream/" . $streamId);
                break;
            case 'video_scrambled':
                # video_scrambled...
                EmailNotificationController::send_information_about_problem_stream("scramblingem videa", $streamId, $streamName, env("APP_URL") . "/#/stream/" . $streamId);
                break;
            case 'audio_discontinuities':
                # audio_discontinuities...
                EmailNotificationController::send_information_about_problem_stream("audio CCR errory", $streamId, $streamName, env("APP_URL") . "/#/stream/" . $streamId);
                break;
            case 'audio_scrambled':
                # audio_scrambled...
                EmailNotificationController::send_information_about_problem_stream("scramblingem audia", $streamId, $streamName, env("APP_URL") . "/#/stream/" . $streamId);
                break;
        }
    }
}
