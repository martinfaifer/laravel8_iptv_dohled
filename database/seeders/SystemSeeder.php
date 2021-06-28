<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SystemSetting::create([
            'modul' => "firewall",
            'stav' => "neaktivni"
        ]);

        SystemSetting::create([
            'modul' => "CRON",
            'stav' => "off"
        ]);

        SystemSetting::create([
            'modul' => "ffprobe",
            'stav' => "on"
        ]);

        SystemSetting::create([
            'modul' => "stream_diagnostic",
            'stav' => "on"
        ]);

        SystemSetting::create([
            'modul' => "create_thumbnails",
            'stav' => "on"
        ]);

        SystemSetting::create([
            'modul' => "email_sending",
            'stav' => "on"
        ]);

        SystemSetting::create([
            'modul' => "slack_sending",
            'stav' => "off"
        ]);
    }
}
