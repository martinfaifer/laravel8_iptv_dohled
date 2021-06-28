<?php

use App\Console\Commands\SlackNotification;
use App\Events\StreamInfoTs;
use App\Http\Controllers\CcErrorController;
use App\Http\Controllers\DiagnosticController;
use App\Http\Controllers\EmailNotificationController;
use App\Http\Controllers\FirewallController;
use App\Http\Controllers\FirewallLogController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SlackController;
use App\Http\Controllers\StreamAlertController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\StreamHistoryController;
use App\Http\Controllers\StreamNotificationLimitController;
use App\Http\Controllers\StreamSheduleFromIptvDokuController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDetailController;
use App\Http\Controllers\UserHistoryController;
use App\Mail\SendErrorStream;
use App\Models\Stream;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use App\Traits\FfmpegTrait;
use Illuminate\Support\Facades\Mail;

Route::group(['middleware' => 'firewall'], function () {
    Route::get('/', function () {
        return view('welcome');
    });

    // výpis alertů streamů
    Route::get('streamAlerts', [StreamAlertController::class, 'index']);
    // mozaika , pagination
    Route::get('pagination', [StreamController::class, 'streams_for_mozaiku']);
    // mozaika , error streams
    Route::get('error/streams', [StreamController::class, 'error_streams_for_mozaika']);

    Route::group(['prefix' => 'streamInfo'], function () {
        // overení status streamu
        Route::post('status', [StreamController::class, 'stream_info_checkStatus']);
        // StreamInfo -> image
        Route::post('image', [StreamController::class, 'stream_info_image']);
        // StreamInfo -> Detailní informace o Streamu, pokud existuji ( u kanálů z ffproby data nejsou )
        Route::post('detail', [StreamController::class, 'stream_info_detail']);
        // StreamInfo -> Výpis historie
        Route::post('history', [StreamHistoryController::class, 'stream_info_history']);
        // StreamInfo -> Výpis z dokumentace pomocí api
        Route::post('doku', [StreamController::class, 'stream_info_doku']);
        // StreamInfo -> Výpis CC Erorru do grafu
        Route::post('ccError', [CcErrorController::class, 'get_ccErrors_for_current_stream']);
        // StreamIfo -> sheduler
        Route::post('sheduler', [StreamSheduleFromIptvDokuController::class, 'return_shedule_data']);
        // StreamInfo -> TodayEvent (sheduler)
        Route::post('todayEvent', [StreamSheduleFromIptvDokuController::class, 'check_if_today_is_shedule']);
        // vyjresleni audio / video grafu
        Route::post('bitrates', [StreamController::class, 'show_audio_video_bitrate_data']);
    });

    // NavigationComponent -> zobrazení notifikace, pokud ke dnesnimu dni jsou plánované nejaké události
    Route::get('todayEvents', [StreamSheduleFromIptvDokuController::class, 'return_all_today_events']);


    // StreamHistory
    Route::get('streams/history/{records}', [StreamHistoryController::class, 'streams_history']);

    // % streamů, které fungují
    Route::get('working/streams', [StreamController::class, 'percent_working_streams']);



    /**
     * VYHKEDÁVÁNÍ NAPŘÍČ APP
     */

    Route::post('search', [SearchController::class, 'search']);

    /**
     * USER BLOK
     */

    Route::post('login', [UserController::class, 'loginUser']);

    Route::get('logout', [UserController::class, 'logout']);

    Route::group(['prefix' => 'user'], function () {
        // Editace jmena a mailu
        Route::post('obecne/edit', [UserController::class, 'obecne_edit']);
        // Editace Dark || light modu
        Route::post('theme', [UserController::class, 'change_theme']);
        // Editace hesla uzivatele
        Route::post('heslo/edit', [UserController::class, 'user_password_edit']);
        // Editace detailnich informaci o uzivately  / pripadne zalození novych nebo odebrání
        Route::post('detail/edit', [UserDetailController::class, 'user_detail_edit']);
        // Editace GUI, ktere si user nastavuje, dle sebe ( staticke kanaly, pocet kanalu v mozaice )
        Route::post('gui/edit', [UserController::class, 'user_gui_edit']);
        // získání informací o uživateli ( aktuálně přihlášeném )
        Route::get('', [UserController::class, 'getLoggedUser']);
        // informace o userovi, pro userComponent
        Route::get('detail', [UserController::class, 'userDetail']);
        // informace o historii daného uzivatele
        Route::post('history', [UserHistoryController::class, 'user_history']);
        // získání všech streamu pro statické kanály
        Route::get('streams', [UserController::class, 'user_streams']);
        // editace / odebrání statických streamů
        Route::post('streams/set', [UserController::class, 'user_streams_set']);
        // výpis uživatelských oprávnění
        Route::get('roles', [UserController::class, 'roles']);
        // generator hesel
        Route::get('password/generator', [UserController::class, 'generate_password']);
        // vytvoření nového uživatele
        Route::post('create', [UserController::class, 'create']);
        // Editace uživatele, bez editace hesla, tu at si uzivatel edituje sám
        Route::post('edit', [UserController::class, 'update']);
        // Odebrání uživatele
        Route::post('delete', [UserController::class, 'delete']);
        // zobrazení emalovych uctu ptrici danemu uzivateli
        Route::get('notificationAccounts', [EmailNotificationController::class, 'return_emails_by_user']);
    });


    // Výpis všech userů v systému
    Route::get('users', [UserController::class, 'index']);
    // vypsání uživatelů, dle data vytvoření od nejnovejsiho -> posledních 5
    Route::get('users/get/last/ten', [UserController::class, 'get_last_ten_users']);


    /**
     * Systém blok
     */
    Route::group(['prefix' => 'system'], function () {
        Route::get('', [SystemController::class, 'checkSystemUsage']);
        Route::get('usage/areaChart', [SystemController::class, 'create_data_for_area_chart']);
        Route::get('cpu"', [SystemController::class, 'cpu']);
        Route::get('cpu/data', [SystemController::class, 'get_cpu_history_data']);
        Route::get('ram', [SystemController::class, 'ram']);
        Route::get('swap', [SystemController::class, 'swap']);
        Route::get('hdd', [SystemController::class, 'hdd']);
        Route::get('uptime', [SystemController::class, 'get_uptime']);
        Route::get('status', [SystemController::class, 'server_status']);
        Route::get('firewall/status', [FirewallController::class, 'check_status']);
        Route::get("certifikate", [SystemController::class, 'check_web_certificate']);
        Route::get('certifikate/check', [SystemController::class, 'count_expiration_of_ssl']);
        Route::get('working_streams/areacharts', [StreamController::class, 'retun_count_of_working_streams']);
        Route::get('streams/donutChart', [StreamController::class, 'return_streams_data_for_donutChart']);
        Route::get('load/history', [SystemController::class, 'load_history_system_usage']);
        Route::get('load/ram', [SystemController::class, 'ram_history_system_usage']);
        Route::get('hdd/history', [SystemController::class, 'hdd_history_system_usage']);
        Route::get('swap/history', [SystemController::class, 'swap_history_system_usage']);
        Route::get("cron", [SystemSettingController::class, 'cron']);
        Route::get("cron/{id}", [SystemSettingController::class, 'cron_show']);
        Route::patch("cron", [SystemSettingController::class, 'cron_update']);
    });
    // admin zona
    Route::get('admin/system/info', [SystemController::class, 'admin_info_system']);
    // dashboard -> vykreslení streamu s cc errory
    Route::get('streams/cc', [CcErrorController::class, 'take_streams_check_if_exist_cc_and_count']);

    /**
     * Nastavení Streamů
     */
    Route::get("streams", [StreamController::class, 'index']);
    // zobrazeni poslednich 10 stremu jak byli zalozeny od posledniho
    Route::get('streams/get/last/ten', [StreamController::class, 'get_last_ten']);

    Route::group(['prefix' => 'stream'], function () {
        Route::post("edit", [StreamController::class, 'edit_stream']);
        Route::post("delete", [StreamController::class, 'delete_stream']);
        Route::post("create", [StreamController::class, 'create_stream']);
        Route::post("issues", [StreamNotificationLimitController::class, 'get_information_for_editation']);
        Route::post('get_name_and_dohled', [StreamController::class, 'get_stream_name_and_dohled']);
        Route::post('mozaika/edit/save', [StreamController::class, 'mozaika_stream_small_edit']);
    });

    /**
     * FIREWALL
     */
    Route::group(['prefix' => 'firewall'], function () {
        Route::get("logs", [FirewallLogController::class, 'get_logs']);
        // změna stavu firewallu
        Route::post("settings", [FirewallController::class, 'change_status']);
        // výpis povolených IP
        Route::get('ips', [FirewallController::class, 'return_allowd_ips']);
        // odebrání povolené IP
        Route::post('ip/delete', [FirewallController::class, 'delete_allowed_ip']);
        // vytvoření nové IP
        Route::post('ip/create', [FirewallController::class, 'create_allowed_ip']);
    });



    /**
     * ALERTING SETTINGS
     */
    Route::group(['prefix' => 'notifications'], function () {
        Route::get("mails", [EmailNotificationController::class, 'return_emails']);
        // zalození noveho emailu pro zasilani alertu
        Route::post('create', [EmailNotificationController::class, 'create_email']);
        // odebrání emalové adresy pro notifikaci
        Route::post('delete', [EmailNotificationController::class, 'delete_email']);
        // editace emailove adresy
        Route::post('edit', [EmailNotificationController::class, 'edit_email']);

        Route::get('slack', [SlackController::class, 'index']);
        Route::post('slack', [SlackController::class, 'store']);
        Route::delete('slack', [SlackController::class, 'delete']);
    });
});
