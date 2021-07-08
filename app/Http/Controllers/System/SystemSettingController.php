<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use App\Traits\NotificationTrait;
use Illuminate\Support\Facades\Artisan;

class SystemSettingController extends Controller
{
    use NotificationTrait;

    public function cron(): array
    {
        return SystemSetting::where('modul', "CRON")
            ->orWhere('modul', "ffprobe")
            ->orWhere('modul', "stream_diagnostic")
            ->orWhere('modul', "create_thumbnails")
            ->orWhere('modul', "email_sending")
            ->orWhere('modul', "slack_sending")
            ->get()->toArray();
    }

    public function cron_show($id): array
    {
        $modul = SystemSetting::find($id);

        if ($modul->stav === "off") {
            $modul_stav = false;
        } else {
            $modul_stav = true;
        }

        return [
            'id' => $modul->id,
            'stav' => $modul_stav
        ];
    }

    public function cron_update(Request $request): array
    {
        if ($request->stav == 0) {
            $stav = "off";
        } else {
            $stav = "on";
        }

        $modul = SystemSetting::find($request->id);
        $modul->update([
            'stav' => $stav
        ]);

        if ($modul->modul === "CRON" && $request->stav == 0) {
            Artisan::call('streams:kill_all_running_streams');
        }

        return $this->frontend_notification("success", "Upraveno!");
    }
}
