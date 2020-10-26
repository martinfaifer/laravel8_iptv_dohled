<?php

use App\Events\StreamInfoTsVideoBitrate;
use App\Events\StreamNotification;
use App\Http\Controllers\EmailNotificationController;
use App\Http\Controllers\FfmpegController;
use App\Http\Controllers\FirewallController;
use App\Http\Controllers\FirewallLogController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\StreamHistoryController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDetailController;
use App\Http\Controllers\UserHistoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
})->middleware('firewall');


// výpis alertů streamů
Route::get('streamAlerts', [StreamController::class, 'show_problematic_streams_as_alerts'])->middleware('firewall');
// mozaika , pagination
Route::get('pagination', [StreamController::class, 'streams_for_mozaiku'])->middleware('firewall');
// StreamInfo -> image
Route::post('streamInfo/image', [StreamController::class, 'stream_info_image'])->middleware('firewall');
// StreamInfo -> Detailní informace o Streamu, pokud existuji ( u kanálů z ffproby data nejsou )
Route::post('streamInfo/detail', [StreamController::class, 'stream_info_detail'])->middleware('firewall');
// StreamInfo -> Výpis historie, posledních 10 zázamů
Route::post('streamInfo/history/10', [StreamHistoryController::class, 'stream_info_history_ten'])->middleware('firewall');
// StreamInfo -> Výpis z dokumentace pomocí api
Route::post('streamInfo/doku', [StreamController::class, 'stream_info_doku'])->middleware('firewall');

// % streamů, které fungují
Route::get('working/streams', [StreamController::class, 'percent_working_streams'])->middleware('firewall');



/**
 * VYHKEDÁVÁNÍ NAPŘÍČ APP
 */

Route::post('search', [SearchController::class, 'search_in_app'])->middleware('firewall');

/**
 * USER BLOK
 */

Route::post('login', [UserController::class, 'loginUser'])->middleware('firewall');

Route::get('logout', [UserController::class, 'logout'])->middleware('firewall');

// Editace jmena a mailu
Route::post('user/obecne/edit', [UserController::class, 'obecne_edit'])->middleware('firewall');

// Editace hesla uzivatele
Route::post('user/heslo/edit', [UserController::class, 'user_password_edit'])->middleware('firewall');

// Editace detailnich informaci o uzivately  / pripadne zalození novych nebo odebrání
Route::post('user/detail/edit', [UserDetailController::class, 'user_detail_edit'])->middleware('firewall');

// Editace GUI, ktere si user nastavuje, dle sebe ( staticke kanaly, pocet kanalu v mozaice )
Route::post('user/gui/edit', [UserController::class, 'user_gui_edit'])->middleware('firewall');

// získání informací o uživateli ( aktuálně přihlášeném )
Route::get('user', [UserController::class, 'getLoggedUser'])->middleware('firewall');

// informace o userovi, pro userComponent
Route::get('user/detail', [UserController::class, 'userDetail'])->middleware('firewall');

// informace o historii daného uzivatele
Route::post('user/history', [UserHistoryController::class, 'user_history'])->middleware('firewall');

// získání všech streamu pro statické kanály
Route::get('user/streams', [UserController::class, 'user_streams'])->middleware('firewall');

// editace / odebrání statických streamů
Route::post('user/streams/set', [UserController::class, 'user_streams_set'])->middleware('firewall');

// Výpis všech userů v systému
Route::get('users', [UserController::class, 'users'])->middleware('firewall');


/**
 * Systém blok
 */

// systémové pozadavky
Route::get('system', [SystemController::class, 'checkSystemUsage'])->middleware('firewall');

Route::get('cpu', [SystemController::class, 'cpu'])->middleware('firewall');
Route::get('ram', [SystemController::class, 'ram'])->middleware('firewall');
Route::get('swap', [SystemController::class, 'swap'])->middleware('firewall');
Route::get('hdd', [SystemController::class, 'hdd'])->middleware('firewall');
Route::get('uptime', [SystemController::class, 'get_uptime'])->middleware('firewall');
Route::get('server/satatus', [SystemController::class, 'server_status'])->middleware('firewall');
Route::get('firewall/status', [FirewallController::class, 'check_status'])->middleware('firewall');

/**
 * Nastavení Streamů
 */
Route::get("streams", [StreamController::class, 'get_streams'])->middleware('firewall');
Route::post("stream/edit", [StreamController::class, 'edit_stream'])->middleware('firewall');
Route::post("stream/delete", [StreamController::class, 'delete_stream'])->middleware('firewall');


/**
 * FIREWALL
 */
Route::get("firewall/logs", [FirewallLogController::class, 'get_logs'])->middleware('firewall');
// změna stavu firewallu
Route::post("firewall/settings", [FirewallController::class, 'change_status'])->middleware('firewall');
// výpis povolených IP
Route::get('firewall/ips', [FirewallController::class, 'return_allowd_ips'])->middleware('firewall');
// odebrání povolené IP
Route::post('firewall/ip/delete', [FirewallController::class, 'delete_allowed_ip'])->middleware('firewall');



/**
 * ALERTING SETTINGS
 */
Route::get("notifications/mails", [EmailNotificationController::class, 'return_emails'])->middleware('firewall');



/**
 * TESTING
 */
// Route::get("certifikate", [SystemController::class, 'check_web_certificate']);
