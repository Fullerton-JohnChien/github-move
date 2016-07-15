<?php
session_start();
date_default_timezone_set('Asia/Taipei');
define('WEB_ROOT', dirname(dirname(dirname(__FILE__))));

define('is_develop', false);
if(defined('ajax_mode'))
    define('sql_debug', false);
else
    define('sql_debug', false);


define('CAPTCHA_PAYMENT', 'travel.ezding.captcha.payment');
define('CAPTCHA_USER', 'travel.ezding.captcha.user');
define('CHANNEL', 'travel.channel');
define('COOKIE_CAMPAIGN_CODE', 'cookie_campaign_code');
define('COOKIE_DISPLAY_CURRENCY', '_cookie_display_currency_');
define('COOKIE_LOGIN_FROM', '_login_from_');
define('COOKIE_OTR_CODE', 'cookie_otr_code');
define('COOKIE_OTR_CODE_HS_ID', 'coolie_otr_hs_id');

define('COOKIE_SSO_USER', 'cookie_sso_user');
define('COOKIE_THIRD_PARTY_UID', '_third_party_uid_');
define('COOKIE_THIRD_PARTY_UNAME', '_third_party_uname_');
define('FACEBOOK_APP_ID', '474784012720774');
define('FACEBOOK_SECRET', 'a6ab69109068c051d2869a6d105a3625');
// define('FACEBOOK_APP_ID', '113918565303043');
// define('FACEBOOK_SECRET', '99a40292ad598fc30d3eea81dd088ae4');
define('GOOGLE_CLIENT_ID', '477113239096-m4ditplsog38g5q1hpj7ml7ccihm2u9h');
define('GOOGLE_SECRET', 'tJrLhFatTpQF78t8EIJ-kig_');
define('HOME_STAY_ID', 'travel.ezding.home_stay.id');
define('LANGUAGE', 'travel.ezding.language');
define('OTR_STORE_NAME', 'tripitta.otr.store.name');
define('OTR_URI', 'tripitta.otr.uri');
define('PAYMENT_TYPE', 'travel.ezding.payment.type');
define('RC4_KEY_OTR_CODE', 'Tripitta!@#$');
define('SHOPPING_CART_ID', 'travel.ezding.shopping.cart.id');
define('SSO_DOMAIN', 'tripitta.com');
define('TRIPADVISOR_PARTNER_ID', 'CB56EED944AF4459B7E92BBF9B292AC6');
define('USER_DATA', 'travel.ezding.user.data');


$webSite = null;
if (getenv('SERVER_NAME') == 'local.www.tripitta.com'
		|| getenv('SERVER_NAME') == 'local.tw.tripitta.com'
        || getenv('SERVER_NAME') == 'local.www.tripitta'
        ) {
	$webSite = 'localhost';
}else if (getenv('SERVER_NAME') == 'alpha.www.tripitta.com') {
	$webSite = 'alpha';
}

$cur_lang = $lang = 'tw';

/* 圖檔路徑 */
$cloudFlag = 1; // 是否由雲端載入照片、js、css等檔案, 1:由雲端載入
$cloudPath = 'https://www.tripitta.com';
// $img_server = 'https://www.tripitta.com';
$img_server = 'https://s3-ap-northeast-1.amazonaws.com/tripitta';
$avatar_path = '/photos/user_avatar';
$taiwan_content_path = '/photos/taiwan_content';
$imgPath = $img_server . '/photos/travel/';
$price_guarantee = '/photos/tripitta/price_guarantee'; // 最低價格保證圖檔路徑

/* 系統 log 目錄, 預設為document_root /log */
$log_base_folder = '/webhome/sites/www.tripitta.com/logs';


$siteStr = '';
$transStr = 'EZ';

$serverName = "www.tripitta.com";

$searchResultCacheTime = 600; // cache時間 10分鐘
$shortCacheTime = 60; // cache時間 1分鐘
$longCacheTime = 3600; // cache時間 60分鐘
$cacheTimeHomeSearch = 60; // 民宿搜尋用

/* 載入檔案 */
if(is_readable(__DIR__ . '/../inc/common.php')) {
    // Alpha 、 Production
    include_once __DIR__ . '/../inc/common.php';
    include_once __DIR__ . '/../inc/constants.php';
    include_once __DIR__ . '/../inc/constants_user_center.php';
    include_once __DIR__ . '/../inc/db.pdo.v2.class.php';
    include_once __DIR__ . '/../inc/crypt.rc4.class.php'; // RC4 加密
    include_once __DIR__ . '/../inc/crypt.tripledes.class.php'; // TripleDES 加密
    include_once __DIR__ . '/../inc/cache.apc.class.php'; // APC Cache Plugin
    include_once __DIR__ . '/../inc/cache.memcache.class.php'; // APC Cache Plugin
    include_once __DIR__ . '/../inc/order.util.class.php';
    include_once __DIR__ . '/../inc/payment.class.php';
    include_once __DIR__ . '/../inc/sms.class.php';
    include_once __DIR__ . '/../inc/user.util.class.php';
    include_once __DIR__ . '/../inc/dao_loader.php';
    include_once __DIR__ . '/../inc/mobile_detect.class.php';

    //include_once 'phpmailer/class.phpmailer.php'; // Email Plugin
    require 'phpmailer_5.2.13/PHPMailerAutoload.php'; // Email Plugin

    include_once(__DIR__ . '/../inc/ezding_user_center.class.php');
    include_once(__DIR__ . '/../inc/ezding_user_util.class.php');
    include_once(__DIR__ . '/../inc/service/tripitta/tripitta_web_service.php');

    // 設定自動載入根目錄, 視各站台目錄結構自行調整
    define('INC_ROOT', __DIR__ . '/../inc');

} else if(is_readable(__DIR__ . '/inc/common.php')) {
    // Alpha 、 Production
	include_once __DIR__ . '/inc/common.php';
    include_once __DIR__ . '/inc/constants.php';
    include_once __DIR__ . '/inc/constants_user_center.php';
    include_once __DIR__ . '/inc/db.pdo.v2.class.php';
    include_once __DIR__ . '/inc/crypt.rc4.class.php'; // RC4 加密
    include_once __DIR__ . '/inc/crypt.tripledes.class.php'; // TripleDES 加密
    include_once __DIR__ . '/inc/cache.apc.class.php'; // APC Cache Plugin
    include_once __DIR__ . '/inc/cache.memcache.class.php'; // APC Cache Plugin
    include_once __DIR__ . '/inc/order.util.class.php';
    include_once __DIR__ . '/inc/payment.class.php';
    include_once __DIR__ . '/inc/sms.class.php';
    include_once __DIR__ . '/inc/user.util.class.php';
    include_once __DIR__ . '/inc/dao_loader.php';
    include_once __DIR__ . '/inc/mobile_detect.class.php';

    //include_once 'phpmailer/class.phpmailer.php'; // Email Plugin
    require 'phpmailer_5.2.13/PHPMailerAutoload.php'; // Email Plugin

    include_once(__DIR__ . '/inc/ezding_user_center.class.php');
    include_once(__DIR__ . '/inc/ezding_user_util.class.php');
    include_once(__DIR__ . '/inc/service/tripitta/tripitta_web_service.php');

    // 設定自動載入根目錄, 視各站台目錄結構自行調整
    define('INC_ROOT', __DIR__ . '/inc');

} else {
    // 開發環境
    include_once __DIR__ . '/../../../inc/common.php';
    include_once __DIR__ . '/../../../inc/constants.php';
    include_once __DIR__ . '/../../../inc/constants_user_center.php';
    include_once __DIR__ . '/../../../inc/db.pdo.v2.class.php';
    include_once __DIR__ . '/../../../inc/crypt.rc4.class.php'; // RC4 加密
    include_once __DIR__ . '/../../../inc/crypt.tripledes.class.php'; // TripleDES 加密
    include_once __DIR__ . '/../../../inc/cache.apc.class.php'; // APC Cache Plugin
    include_once __DIR__ . '/../../../inc/cache.memcache.class.php'; // APC Cache Plugin
    include_once __DIR__ . '/../../../inc/order.util.class.php';
    include_once __DIR__ . '/../../../inc/payment.class.php';
    include_once __DIR__ . '/../../../inc/sms.class.php';
    include_once __DIR__ . '/../../../inc/user.util.class.php';
    include_once __DIR__ . '/../../../inc/dao_loader.php';
    include_once __DIR__ . '/../../../inc/mobile_detect.class.php';

    //include_once 'phpmailer/class.phpmailer.php'; // Email Plugin
    require 'phpmailer_5.2.13/PHPMailerAutoload.php'; // Email Plugin

    include_once(__DIR__ . '/../../../inc/ezding_user_center.class.php');
    include_once(__DIR__ . '/../../../inc/ezding_user_util.class.php');
    include_once(__DIR__ . '/../../../inc/service/tripitta/tripitta_web_service.php');

    // 設定自動載入根目錄, 視各站台目錄結構自行調整
    define('INC_ROOT', __DIR__ . '/../../../inc');
}

set_utf8_header();

// 設定自動載入function
spl_autoload_register('auto_load_daos');
/**
 * 只依子目錄進行 autoload class
 *
 * Class name 須和檔名一樣才可自動載入
 *
 * @param string $class_name
 */
function auto_load_daos($class_name) {
    // $class =  $class_name . '.php';
    $class = strtolower(str_replace('\\', '/', $class_name)) . '.php';
    if(defined('INC_ROOT')){
        $path = INC_ROOT . "/" . $class;
        // echo __FUNCTION__ . ':' . $path . '<br>';
        if(is_readable($path)){
            // 預設 LIB 主目錄
            require_once($path);
        }else {
            // 搜尋 LIB_ROOT 下子目錄
            foreach(Constants::$CONFIG_AUTOLOAD_INC_FOLDERS as $folder) {
                $path = INC_ROOT . "/" . $folder . "/" . $class;
                // echo __FUNCTION__ . ':' . $path . '<br>';
                if(is_readable($path)) {
                    require_once($path);
                    break;
                }
            }
        }
    }
}


/* initial apc cache */
$cache = null;


/* 會員中心 */
$urlUserCenter = 'http://api.ezding.com.tw/user';
$clientUserAccount = 'tripitta.tw';
$clientUserPassword = '((tripitta.tw))';
$clinetUserCategory = 'tripitta';


/* SMTP設定 */
//192.168.80.82 61.216.80.82
$smtp_server = '61.216.80.82';
if(is_dev() || is_alpha()) {
    //$smtp_server = '192.168.80.82';
}
$smtp_auth = true;
//$smtp_uid = 'test';
//$smtp_pwd = 'test11!';
$smtp_uid = 'service';
$smtp_pwd = 'Service12#$';
$mail_default_character = 'utf-8';
$mail_from = 'service@mail.tripitta.com';


/* 授權交易路徑 */
$transPath = 'https://secure.ezding.com.tw/servlet/payment/';
$transKey = 'ZnRjVHJpcGxlREVDODkxMiQjKSk=';
$transSum = '';
$transAccount = 'tripitta.ezding';
$transPassword = 'fullerton';

$enableSynPicToClouds = true;  ////啟用雲端同步圖片
if (getenv('SERVER_ADDR') == '210.242.196.84') {
	error_reporting(E_ALL);
	$serverName = 'local.travel';
	$webSite = 'localhost';
	$siteStr = '測試站台 ';
	$mngSite = 'local.homestay.ezding.com.tw';
	$enableSynPicToClouds = false;  ////啟用雲端同步圖片
}
else if (getenv('SERVER_NAME') == 'alpha.homestay.ezding.com.tw' || (!empty($argv[1]) && $argv[1] == 'alpha')) {
	$serverName = 'alpha.travel.ezding.com.tw';
	$webSite = 'alpha';
	$siteStr = '測試站台 ';
	$mngSite = 'alpha.homestay.ezding.com.tw';
	$enableSynPicToClouds = false;  ////啟用雲端同步圖片
}

/**
 * 正式 & 測試 & 本機 環境的參數設定!
 */
if (is_production()) {
//     $cache = new MemoryCache('210.242.196.84');
//     $cache = new ApcCache();
	$cache = new MemoryCache('192.168.195.111');

    // 目前只有正式站台才使用雲端 (會算流量)
    if (1 == $cloudFlag) $imgPath = $cloudPath . '/photos/travel/';
}
else if(is_alpha()) {
    $cache = new MemoryCache('192.168.195.87');

//     $log_base_folder = '/webhome/sites/tw.tripitta.com/logs';

    $img_server = 'http://alpha.www.tripitta.com';
    $avatar_path = '/photos/alpha_user_avatar';
    $taiwan_content_path .= '_alpha';
    $imgPath = $img_server . '/photos/alpha_travel/';

    $urlUserCenter = 'http://alpha.api.ezding.com.tw/user';

    $siteStr = '測試站台-';
    $transStr = 'EZTEST';

    $searchResultCacheTime = 60;
    $shortCacheTime = 60;
    $longCacheTime = 60;
    $cacheTimeHomeSearch = 60;

    $serverName = "alpha.www.tripitta.com";

    $transPath = 'http://221.222.222.215:9080/servlet/payment/';
    $transAccount = 'local.travel';
}
else if(is_dev()) {
    error_reporting(E_ALL);
    $cache = new ApcCache();
    $log_base_folder = '';

    $img_server = 'http://alpha.www.tripitta.com';
    $avatar_path = '/photos/alpha_user_avatar';
    $taiwan_content_path .= '_alpha';
    $imgPath = $img_server . '/photos/alpha_travel/';

    $urlUserCenter = 'http://alpha.api.ezding.com.tw/user';

    $siteStr = '測試站台-';
    $transStr = 'EZTEST';

    $searchResultCacheTime = 60;
    $shortCacheTime = 60;
    $longCacheTime = 60;
    $cacheTimeHomeSearch = 60;
    $serverName = "local.tw.tripitta.com";

    $transPath = 'http://221.222.222.215:9080/servlet/payment/';
    $transAccount = 'local.travel';
}

$t_server_name = $_SERVER["SERVER_NAME"];
if(is_production() && !empty($t_server_name) && strtolower($t_server_name) != 'www.tripitta.com') {
    header("HTTP/1.1 301 Moved Permanently");
	header("Location: https://www.tripitta.com" . $_SERVER["REQUEST_URI"]);
	exit();
}

// 資料庫設定
// 民宿資料庫(M66)
$db_host_ezding = "travel_db";
$db_name_ezding = "travel";
$db_uid_ezding = "phpfun";
$db_pwd_ezding = "((fullerton))";
$db_charset_ezding = "utf8";
$db_dsn_ezding = "mysql:host=$db_host_ezding;dbname=$db_name_ezding";

// EzDing DB(M45)
$db_host_ezding2 = "ezdingdb";
$db_name_ezding2 = "ezding";
$db_uid_ezding2 = "javafun";
$db_pwd_ezding2 = "fullerton";
$db_charset_ezding2 = "utf8";
$db_dsn_ezding2 = "mysql:host=$db_host_ezding2;dbname=$db_name_ezding2";

// 卡號資料認證
$db_host_vd = "travel_db";
$db_name_vd = "event_vd";
$db_uid_event_vd = $db_uid_vd = "phpfun";
$db_pwd_event_vd = $db_pwd_vd = "((fullerton))";
$db_charset_vd = "utf8";
$db_dsn_event_vd = $db_dsn_vd = "mysql:host=$db_host_vd;dbname=$db_name_vd";

// travel log
$db_host_travel_log = "travel_log_db";
$db_name_travel_log = "travel_log";
$db_uid_travel_log = "phpfun";
$db_pwd_travel_log = "((fullerton))";
$db_charset_travel_log = "utf8";
$db_dsn_travel_log = "mysql:host=$db_host_travel_log;dbname=$db_name_travel_log";

// app_push 資料庫設定
if (empty($webSite)){
	$db_host_app_push = "app_push_db";
	$db_name_app_push = "app_push";
	// 	$db_uid_app_push = "ftcEzding";
	// 	$db_pwd_app_push = "((movie))";
	$db_uid_app_push = "phpfun";
	$db_pwd_app_push = "((fullerton))";
}else{
	$db_host_app_push = "travel_db";
	$db_name_app_push = "app_push";
	$db_uid_app_push = "javafun";
	$db_pwd_app_push = "fullerton";
}
$db_charset_app_push = "utf8";
$db_dsn_app_push = "mysql:host=$db_host_app_push;dbname=$db_name_app_push";

// 訂單中心
$db_host_odc = "order_center_db";
$db_name_odc = "odc";
$db_dsn_odc = "mysql:host=$db_host_odc;dbname=$db_name_odc";
$db_uid_odc = "phpfun";
$db_pwd_odc = "((fullerton))";
$db_charset_odc = "utf8";

// 檢查瀏覽器界面
$detect = new Mobile_Detect;
$deviceType = 'computer';
if($detect->isMobile()) $deviceType = 'phone';

// 我的收藏使用路徑
$member_path = "member";
// 我的收藏分類 (food:美食, scenic:景點, homestay:旅宿, gift:伴手禮, event:活動, travel_plan:行程遊記, topic_plan:主題企劃, transport:交通)
$collection_type = array('food', 'scenic', 'homestay', 'gift', 'event', 'travel_plan', 'topic_plan', 'transport');

// 手機版頁面寬度 736px
$mobile_width = "718";

// ios: ipod/iphone/ipad facebook 驗證回傳
$full_serverName = !is_dev() && !is_alpha() ? "https://" . $serverName . "/" : "http://" . $serverName . "/";
$ios_fb_code = !empty($_REQUEST['code']) ? $_REQUEST['code'] : null;
?>