<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{
    public function index(): array
    {
        return SystemLog::all()->toArray();
    }
}
