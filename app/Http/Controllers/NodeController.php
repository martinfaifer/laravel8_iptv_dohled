<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NodeController extends Controller
{
    public static function runTestScrit()
    {
        // shell_exec('node Nodejs/checkCpu.js');
        return shell_exec('node Nodejs/checkCpu.js');
    }
}
