<?php

namespace App\Traits;

trait TSDuckTrait
{
    public static function analyze(string $streamUrl)
    {
        return shell_exec("timeout -s SIGKILL 4 tsp -I ip {$streamUrl} -P until -s 1 -P analyze --normalized -O drop");
    }
}
