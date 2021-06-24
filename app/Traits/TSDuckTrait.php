<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait TSDuckTrait
{
    public static function analyze(string $streamUrl)
    {
        if (Str::contains($streamUrl, "http")) {
            return shell_exec(" tsp -I http {$streamUrl} -P until -s 1 -P analyze --normalized -O drop");
        }
        return shell_exec("timeout -s SIGKILL 4 tsp -I ip {$streamUrl} -P until -s 1 -P analyze --normalized -O drop");
    }
}
