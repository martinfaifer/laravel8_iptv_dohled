<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use App\Models\SystemSetting;
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

    /**
     * funkce na vrácení statusu firewallu
     * vrací status => running
     *       status => stopped
     *
     * @return array
     */
    public static function check_status(): array
    {
        if (!SystemSetting::where('modul', "firewall")->where('stav', "aktivni")->first()) {
            return [
                'status' => "stopped"
            ];
        }

        return [
            'status' => "running"
        ];
    }
}
