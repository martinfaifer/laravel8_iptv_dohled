<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;

class SystemLogController extends Controller
{
    public function index(): array
    {
        return SystemLog::all()->toArray();
    }
}
