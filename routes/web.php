<?php

use App\Events\StreamInfoTsVideoBitrate;
use App\Events\StreamNotification;
use App\Http\Controllers\CcErrorController;
use App\Http\Controllers\EmailNotificationController;
use App\Http\Controllers\FfmpegController;
use App\Http\Controllers\FirewallController;
use App\Http\Controllers\FirewallLogController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\StreamHistoryController;
use App\Http\Controllers\StreamNotificationLimitController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDetailController;
use App\Http\Controllers\UserHistoryController;
use App\Jobs\add_ccError;
use App\Jobs\Diagnostic_Status_Update;
use App\Jobs\StreamNotificationLimit;
use App\Models\Stream;
// use App\Models\StreamNotificationLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
})->middleware('firewall');


// výpis alertů streamů
Route::get('streamAlerts', [StreamController::class, 'show_problematic_streams_as_alerts'])->middleware('firewall');
// mozaika , pagination
Route::get('pagination', [StreamController::class, 'streams_for_mozaiku'])->middleware('firewall');
// overení status streamu
Route::post('streamInfo/status', [StreamController::class, 'stream_info_checkStatus'])->middleware('firewall');
// StreamInfo -> image
Route::post('streamInfo/image', [StreamController::class, 'stream_info_image'])->middleware('firewall');
// StreamInfo -> Detailní informace o Streamu, pokud existuji ( u kanálů z ffproby data nejsou )
Route::post('streamInfo/detail', [StreamController::class, 'stream_info_detail'])->middleware('firewall');
// StreamInfo -> Výpis historie, posledních 10 zázamů
Route::post('streamInfo/history/10', [StreamHistoryController::class, 'stream_info_history_ten'])->middleware('firewall');
// StreamInfo -> Výpis z dokumentace pomocí api
Route::post('streamInfo/doku', [StreamController::class, 'stream_info_doku'])->middleware('firewall');
// StreamInfo -> Výpis CC Erorru do grafu
Route::post('streamInfo/ccError', [CcErrorController::class, 'get_ccErrors_for_current_stream'])->middleware('firewall');

// StreamHistory
Route::get('history', [StreamHistoryController::class, 'return_last_10_history'])->middleware('firewall');

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
// výpis uživatelských oprávnění
Route::get('user/roles', [UserController::class, 'user_roles'])->middleware('firewall');
// generator hesel
Route::get('user/password/generator', [UserController::class, 'generate_password'])->middleware('firewall');
// vytvoření nového uživatele
Route::post('user/create', [UserController::class, 'user_create'])->middleware('firewall');
// Editace uživatele, bez editace hesla, tu at si uzivatel edituje sám
Route::post('user/edit', [UserController::class, 'user_edit'])->middleware('firewall');
// Odebrání uživatele
Route::post('user/delete', [UserController::class, 'user_delete'])->middleware('firewall');


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
Route::get("certifikate", [SystemController::class, 'check_web_certificate'])->middleware('firewall');
Route::get('certifikate/check', [SystemController::class, 'count_expiration_of_ssl'])->middleware('firewall');
// admin zona
Route::get('admin/system/info', [SystemController::class, 'admin_info_system'])->middleware('firewall');

/**
 * Nastavení Streamů
 */
Route::get("streams", [StreamController::class, 'get_streams'])->middleware('firewall');
Route::post("stream/edit", [StreamController::class, 'edit_stream'])->middleware('firewall');
Route::post("stream/delete", [StreamController::class, 'delete_stream'])->middleware('firewall');
Route::post("stream/add", [StreamController::class, 'create_stream'])->middleware('firewall');
Route::post("stream/issues", [StreamNotificationLimitController::class, 'get_information_for_editation'])->middleware('firewall');


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
// vytvoření nové IP
Route::post('firewall/ip/create', [FirewallController::class, 'create_allowed_ip'])->middleware('firewall');



/**
 * ALERTING SETTINGS
 */
Route::get("notifications/mails", [EmailNotificationController::class, 'return_emails'])->middleware('firewall');
// zalození noveho emailu pro zasilani alertu
Route::post('notifications/create', [EmailNotificationController::class, 'create_email'])->middleware('firewall');
// odebrání emalové adresy pro notifikaci
Route::post('notifications/delete', [EmailNotificationController::class, 'delete_email'])->middleware('firewall');
// editace emailove adresy
Route::post('notifications/edit', [EmailNotificationController::class, 'edit_email'])->middleware('firewall');


/**
 * TESTING
 */


// Route::get('prum', [CcErrorController::class, 'prum_every_two_hours']);
// Route::get('unlink', function () {
//     if (file_exists(public_path(Stream::where('id', "1")->first()->image))) {
//         // Náhled existuje => odebrání náhledu z filesystemu
//         dd("existuje");
//         // unlink(public_path($oldImage));

//         // Stream::where('id', $streamId)->update(['image' => 'false']);
//     } else {
//         dd("neexituje");
//     }
// });
