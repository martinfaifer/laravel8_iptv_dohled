<?php

namespace App\Http\Controllers\Firewall;

use App\Http\Controllers\Controller;
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

        if (Firewall::where('allowed_ip', $clientAddress)->first()) {

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


    /**
     * funkce na změnu stavu firewallu ( aktivní / neaktivní)
     *
     * @param Request $request
     * @return void
     */
    public function change_status(Request $request)
    {

        if ($request->firewallStatus == false) {
            SystemSetting::where('modul', "firewall")->update(['stav' => "neaktivni"]);

            return [

                'status' => "success",
                'msg' => "Změnněno"

            ];
        }

        SystemSetting::where('modul', "firewall")->update(['stav' => "aktivni"]);

        return [

            'status' => "success",
            'msg' => "Změnněno"

        ];
    }

    /**
     * funkce na vrácení povolených IP nebo prázdého pole
     *
     * @return void
     */
    public function return_allowd_ips()
    {
        if (!Firewall::first()) {
            return [
                'status' => "empty"
            ];
        }

        return Firewall::get();
    }


    /**
     * odebrání povoleé IP ze systému
     *
     * @param Request $request->allowedIPid
     * @return void
     */
    public function delete_allowed_ip(Request $request)
    {
        Firewall::where('id', $request->allowedIPid)->delete();

        return [
            'status' => "success",
            'msg' => "Adresa byla odebrána"
        ];
    }

    /**
     * funkce na zalození nove IP pro povolení do systemu, pri aktivnim FW
     *
     * @param Request $request
     * @return array
     */
    public function create_allowed_ip(Request $request): array
    {

        if (Firewall::where('allowed_ip', $request->ip)->first()) {
            return [
                'status' => "error",
                'msg' => "Adresa již existuje"
            ];
        }

        // validace IP
        if (!filter_var($request->ip, FILTER_VALIDATE_IP)) {
            return [
                'status' => "error",
                'msg' => "Neplatný formát IPv4"
            ];
        }

        try {
            Firewall::create([
                'allowed_ip' => $request->ip
            ]);
            return [
                'status' => "success",
                'msg' => "Adresa byla přidána"
            ];
        } catch (\Throwable $th) {
            return [
                'status' => "error",
                'msg' => "Nepodařilo se přidat adresu"
            ];
        }
    }
}
