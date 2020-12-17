<?php

namespace App\Http\Controllers;

use App\Models\StreamHistory;
use App\Models\SystemHistory;
use Illuminate\Http\Request;

class SystemHistoryController extends Controller
{
    public static function prum_older_than_twelve_hours(): void
    {
        if (SystemHistory::where('created_at', '<=', now()->subHours(12))->first()) {
            SystemHistory::where('created_at', '<=', now()->subHours(12))->delete();
        }

        if (StreamHistory::where('created_at', '<=', now()->subHours(12))->first()) {
            StreamHistory::where('created_at', '<=', now()->subHours(12))->delete();
        }
    }
}
