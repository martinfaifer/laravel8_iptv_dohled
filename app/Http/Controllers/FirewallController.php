<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use Illuminate\Http\Request;

class FirewallController extends Controller
{

    /**
     * funkce, která overuje, zda IP klienta je povolená
     * paklize není povolená redirect na 404
     *
     * pokud neexistuje zadna IP v tabulce => vsechny adresy jsou povolené
     *
     * @return string
     */
    public static function check_if_is_ip_allowed(string $clientAddress): string
    {

        // overeni, zda existuje jakákoliv adresa v db
        if (!Firewall::first()) {
            return "ok";
        }

        if (Firewall::where('allowed_ip', $clientAddress)) {

            return "ok";
        } else {

            return "ko";
        }
    }
}
