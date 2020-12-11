<?php

use App\Events\StreamInfoTsVideoBitrate;
use App\Events\StreamNotification;
use App\Http\Controllers\CcErrorController;
use App\Http\Controllers\DiagnosticController;
use App\Http\Controllers\EmailNotificationController;
use App\Http\Controllers\FfmpegController;
use App\Http\Controllers\FirewallController;
use App\Http\Controllers\FirewallLogController;
use App\Http\Controllers\NodeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\StreamHistoryController;
use App\Http\Controllers\StreamNotificationLimitController;
use App\Http\Controllers\StreamSheduleFromIptvDokuController;
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
use Linfo\Linfo;

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
// StreamInfo -> Výpis historie, posledních 5 zázamů
Route::post('streamInfo/history/5', [StreamHistoryController::class, 'stream_info_history_five'])->middleware('firewall');
// StreamInfo -> Výpis z dokumentace pomocí api
Route::post('streamInfo/doku', [StreamController::class, 'stream_info_doku'])->middleware('firewall');
// StreamInfo -> Výpis CC Erorru do grafu
Route::post('streamInfo/ccError', [CcErrorController::class, 'get_ccErrors_for_current_stream'])->middleware('firewall');
// StreamIfo -> sheduler
Route::post('streamInfo/sheduler', [StreamSheduleFromIptvDokuController::class, 'return_shedule_data'])->middleware('firewall');
// StreamInfo -> TodayEvent (sheduler)
Route::post('streamInfo/todayEvent', [StreamSheduleFromIptvDokuController::class, 'check_if_today_is_shedule'])->middleware('firewall');
// NavigationComponent -> zobrazení notifikace, pokud ke dnesnimu dni jsou plánované nejaké události
Route::get('todayEvents', [StreamSheduleFromIptvDokuController::class, 'return_all_today_events'])->middleware('firewall');


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

// Editace Dark || light modu
Route::post('user/theme', [UserController::class, 'change_theme'])->middleware('firewall');

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
// vypsání uživatelů, dle data vytvoření od nejnovejsiho -> posledních 5
Route::get('users/get/last/ten', [UserController::class, 'get_last_ten_users'])->middleware('firewall');


/**
 * Systém blok
 */

// systémové pozadavky
Route::get('system', [SystemController::class, 'checkSystemUsage'])->middleware('firewall');


Route::get('system/usage/areaChart', [SystemController::class, 'create_data_for_area_chart'])->middleware('firewall');
Route::get('cpu', [SystemController::class, 'cpu'])->middleware('firewall');
Route::get('system/avarage/load', function () {
    foreach (sys_getloadavg() as $load) {
        $output[] = round($load, 2);
    }
    return $output;
})->middleware('firewall');
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
// dashboard -> vykreslení streamu s cc errory
Route::get('streams/cc', [CcErrorController::class, 'take_streams_check_if_exist_cc_and_count']);

/**
 * Nastavení Streamů
 */
Route::get("streams", [StreamController::class, 'get_streams'])->middleware('firewall');
Route::post("stream/edit", [StreamController::class, 'edit_stream'])->middleware('firewall');
Route::post("stream/delete", [StreamController::class, 'delete_stream'])->middleware('firewall');
Route::post("stream/add", [StreamController::class, 'create_stream'])->middleware('firewall');
Route::post("stream/issues", [StreamNotificationLimitController::class, 'get_information_for_editation'])->middleware('firewall');
Route::post('stream/get_name_and_dohled', [StreamController::class, 'get_stream_name_and_dohled'])->middleware('firewall');
Route::post('stream/mozaika/edit/save', [StreamController::class, 'mozaika_stream_small_edit'])->middleware('firewall');

// zobrazeni poslednich 10 stremu jak byli zalozeny od posledniho
Route::get('streams/get/last/ten', [StreamController::class, 'get_last_ten'])->middleware('firewall');

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
// zobrazení emalovych uctu ptrici danemu uzivateli
Route::get('user/notificationAccounts', [EmailNotificationController::class, 'return_emails_by_user'])->middleware('firewall');


/**
 * DATA PRO VYKRESLOVÁNÍ GRAFŮ
 */
// vykreslení funkčních || nefunkčních streamů
Route::get('working_streams/areacharts', [StreamController::class, 'retun_count_of_working_streams']);
// donut chart streamů
Route::get('streams/donutChart', [StreamController::class, 'return_streams_data_for_donutChart']);
// system -> load history for area chart
Route::get('system/load/history', [SystemController::class, 'load_history_system_usage']);
// System -> load history ram
Route::get('system/load/ram', [SystemController::class, 'ram_history_system_usage']);
// System -> load history HDD
Route::get('system/hdd/history', [SystemController::class, 'hdd_history_system_usage']);
// System -> load history swap
Route::get('system/swap/history', [SystemController::class, 'swap_history_system_usage']);

/**
 * TESTING
 */

// Route::get('time', function () {
//     date_default_timezone_set('Europe/Prague');
//     return date('H:i', time());
// });




Route::get('system/info/test', function () {
    $linfo = new Linfo();
    $parser = $linfo->getParser();
    // getWebService
    return $parser->getContains();
    // return $parser->getWebService(); // posible
    // return $parser->getUpTime(); // posible
});



Route::get('tsDuck/arr', function () {
    // ssome code

    $tsDuckData = shell_exec("tsp -I ip 239.250.2.37:1234 -P until -s 1 -P analyze --normalized -O drop");
    return DiagnosticController::convert_tsduck_string_to_array($tsDuckData);
});

// Route::get('run/node', function () {
//     $cpuData = NodeController::runTestScrit();
//     dd(explode("\n", $cpuData));
// });
