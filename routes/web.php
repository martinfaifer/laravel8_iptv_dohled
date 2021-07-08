<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Logo\CompanyLogoController;
use App\Http\Controllers\Notifications\EmailNotificationController;
use App\Http\Controllers\Search\SearchController;
use App\Http\Controllers\Notifications\SlackController;
use App\Http\Controllers\Notifications\StreamAlertController;
use App\Http\Controllers\Streams\StreamController;
use App\Http\Controllers\Streams\StreamHistoryController;
use App\Http\Controllers\Notifications\StreamNotificationLimitController;
use App\Http\Controllers\Streams\StreamInfoController;
use App\Http\Controllers\Notifications\StreamSheduleFromIptvDokuController;
use App\Http\Controllers\System\SystemController;
use App\Http\Controllers\System\SystemCpuController;
use App\Http\Controllers\System\SystemLogController;
use App\Http\Controllers\System\SystemSettingController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\UserDetailController;
use App\Http\Controllers\User\UserHistoryController;
use App\Models\Stream;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


/**
 * AUTH
 */
Route::post('login', [AuthController::class, 'login']);
Route::get('logout', [AuthController::class, 'logout']);


// výpis alertů streamů
Route::get('streamAlerts', [StreamAlertController::class, 'index']);
// mozaika , pagination
Route::get('pagination', [StreamController::class, 'streams_for_mozaiku']);

Route::group(['prefix' => 'streamInfo'], function () {
    // overení status streamu
    Route::post('status', [StreamInfoController::class, 'stream_info_checkStatus']);
    // StreamInfo -> image
    Route::post('image', [StreamInfoController::class, 'stream_info_image']);
    // StreamInfo -> Detailní informace o Streamu, pokud existuji ( u kanálů z ffproby data nejsou )
    Route::post('detail', [StreamInfoController::class, 'stream_info_detail']);
    // StreamInfo -> Výpis historie
    Route::post('history', [StreamInfoController::class, 'get_history_data']);
    // StreamInfo -> Výpis z dokumentace pomocí api
    Route::post('doku', [StreamInfoController::class, 'stream_info_doku']);
    // StreamIfo -> sheduler
    Route::post('sheduler', [StreamInfoController::class, 'get_shedule_data']);
    // StreamInfo -> TodayEvent (sheduler)
    Route::post('todayEvent', [StreamInfoController::class, 'get_today_shedule_data']);
    // vyjresleni audio / video grafu
    Route::post('bitrates', [StreamInfoController::class, 'show_audio_video_bitrate_data']);
    Route::post('scrambled', [StreamInfoController::class, 'show_stream_pids_scrambled_data']);
});

// NavigationComponent -> zobrazení notifikace, pokud ke dnesnimu dni jsou plánované nejaké události
Route::get('todayEvents', [StreamSheduleFromIptvDokuController::class, 'return_all_today_events']);


// StreamHistory
Route::get('streams/history/{records}', [StreamHistoryController::class, 'streams_history']);

// % streamů, které fungují
// Route::get('working/streams', [StreamController::class, 'percent_working_streams']);

Route::post('search', [SearchController::class, 'search']);

/**
 * USER BLOK
 */
Route::group(['prefix' => 'user'], function () {
    // získání informací o uživateli ( aktuálně přihlášeném )
    Route::get('', [UserController::class, 'getLoggedUser']);
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
    // informace o userovi, pro userComponent
    Route::get('detail', [UserController::class, 'userDetail']);
    // informace o historii daného uzivatele
    Route::post('history', [UserHistoryController::class, 'user_history']);
    // získání všech streamu pro statické kanály
    Route::get('streams', [UserController::class, 'user_streams']);
    // editace / odebrání statických streamů
    Route::post('streams/set', [UserController::class, 'user_streams_set']);

    Route::group(['middleware' => 'isAdmin'], function () {
        // Výpis všech userů v systému
        Route::get('users', [UserController::class, 'index']);
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
    });
    // zobrazení emalovych uctu ptrici danemu uzivateli
    Route::get('notificationAccounts', [EmailNotificationController::class, 'return_emails_by_user']);
});

// vypsání uživatelů, dle data vytvoření od nejnovejsiho -> posledních 5
Route::get('users/get/last/ten', [UserController::class, 'get_last_ten_users']);


/**
 * Systém blok
 */
Route::group(['prefix' => 'system'], function () {
    Route::get('', [SystemController::class, 'checkSystemUsage']);
    Route::get('usage/areaChart', [SystemController::class, 'create_data_for_area_chart']);
    Route::get('cpu"', [SystemCpuController::class, 'cpu']);
    Route::get('cpu/data', [SystemCpuController::class, 'get_cpu_history_data']);
    Route::get('ram', [SystemController::class, 'ram']);
    Route::get('swap', [SystemController::class, 'swap']);
    Route::get('hdd', [SystemController::class, 'hdd']);
    Route::get('uptime', [SystemController::class, 'get_uptime']);
    Route::get('status', [SystemController::class, 'server_status']);
    Route::get("certifikate", [SystemController::class, 'check_web_certificate']);
    Route::get('certifikate/check', [SystemController::class, 'count_expiration_of_ssl']);
    Route::get('working_streams/areacharts', [StreamController::class, 'retun_count_of_working_streams']);
    Route::get('streams/donutChart', [StreamController::class, 'return_streams_data_for_donutChart']);
    Route::get('load/history', [SystemController::class, 'load_history_system_usage']);
    Route::get('load/ram', [SystemController::class, 'ram_history_system_usage']);
    Route::get('hdd/history', [SystemController::class, 'hdd_history_system_usage']);
    Route::get('swap/history', [SystemController::class, 'swap_history_system_usage']);

    Route::group(['middleware' => 'isAdmin'], function () {
        Route::group(['prefix' => 'cron'], function () {
            Route::get("", [SystemSettingController::class, 'cron']);
            Route::get("{id}", [SystemSettingController::class, 'cron_show']);
            Route::patch("", [SystemSettingController::class, 'cron_update']);
        });

        Route::get('logs', [SystemLogController::class, 'index']);

        Route::group(['prefix' => 'logo'], function () {
            Route::get('', [CompanyLogoController::class, 'index']);
            Route::post('', [CompanyLogoController::class, 'store']);
            Route::delete('', [CompanyLogoController::class, 'delete']);
        });
    });
});

/**
 * Nastavení Streamů
 */
Route::group(['middleware' => ['isEditor']], function () {
    Route::get("streams", [StreamController::class, 'index']);
    // zobrazeni poslednich 10 stremu jak byli zalozeny od posledniho
    // Route::get('streams/get/last/ten', [StreamController::class, 'get_last_ten']);

    Route::group(['prefix' => 'stream'], function () {
        Route::post("edit", [StreamController::class, 'edit_stream']);
        Route::post("delete", [StreamController::class, 'delete_stream']);
        Route::post("create", [StreamController::class, 'create_stream']);
        Route::post("issues", [StreamNotificationLimitController::class, 'get_information_for_editation']);
        Route::post('get_name_and_dohled', [StreamController::class, 'get_stream_name_and_dohled']);
        Route::post('mozaika/edit/save', [StreamController::class, 'mozaika_stream_small_edit']);
    });
});


/**
 * ALERTING SETTINGS
 */
Route::group(['middleware' => 'isAdmin'], function () {
    Route::group(['prefix' => 'notifications'], function () {
        Route::get("mails", [EmailNotificationController::class, 'return_emails']);
        // zalození noveho emailu pro zasilani alertu
        Route::post('create', [EmailNotificationController::class, 'create_email']);
        // odebrání emalové adresy pro notifikaci
        Route::post('delete', [EmailNotificationController::class, 'delete_email']);
        // editace emailove adresy
        Route::post('edit', [EmailNotificationController::class, 'edit_email']);

        Route::group(['prefix' => 'slack'], function () {
            Route::get('', [SlackController::class, 'index']);
            Route::post('', [SlackController::class, 'store']);
            Route::delete('', [SlackController::class, 'delete']);
        });
    });
});
