<?php

namespace App\Http\Controllers;

use App\Models\Slack;
use App\Models\Stream;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Traits\NotificationTrait;
use Laravel\Horizon\Listeners\SendNotification;

class SlackController extends Controller
{
    use NotificationTrait;

    public function index()
    {
        return Slack::all();
    }

    public function store(Request $request): array
    {
        if (!Slack::first()) {
            Slack::create([
                'channel' => $request->channel
            ]);
            return $this->frontend_notification("success", "Uloženo!");
        }

        return $this->frontend_notification("error", "Může existovat pouze jeden channel!");
    }

    public function update(Request $request): array
    {
        $channel = Slack::find($request->id);
        if ($channel) {
            $channel->update([
                'channel' => $request->channel
            ]);
            return $this->frontend_notification("success", "Upraveno!");
        }

        return $this->frontend_notification("error", "Nenalezen channel!");
    }

    public function delete(Request $request): array
    {
        $channel = Slack::find($request->id);
        if ($channel) {
            $channel->delete();
            return $this->frontend_notification("success", "Odebráno!");
        }

        return $this->frontend_notification("error", "Nenalezen channel!");
    }

    public static function notify(): void
    {

        if (Slack::first()) {
            Stream::where('sendSlackAlert', true)->chunk(50, function ($streams) {
                foreach ($streams as $stream) {
                    if (Cache::has("stream" . $stream->id) && !Cache::has("stream" . $stream->id . "_sended_slack_notification")) {

                        $stream->notify(new SendNotification($stream->nazev . " má výpadek"));

                        Cache::put("stream" . $stream->id . "_sended_slack_notification", []);
                    }
                }
            });
        }
    }
}
