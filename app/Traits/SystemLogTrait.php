<?php

namespace App\Traits;

use App\Models\SystemLog;
use Illuminate\Support\Str;

trait SystemLogTrait
{
    public static function create(string $component,  string $payload): void
    {
        SystemLog::create([
            'component' => $component,
            'payload' => $payload
        ]);
    }
}
