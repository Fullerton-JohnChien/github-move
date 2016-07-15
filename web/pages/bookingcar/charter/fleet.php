<?php
/**
 * 說明：交通預定 - 車隊介紹
 * 作者：Casper <casper.lee@fullerton.com.tw>
 * 日期：2016年5月10日
 * 備註：
 */
require_once __DIR__ . '/../../../config.php';
header("Content-Type:text/html; charset=utf-8");

// 頁面基本參數
$people_count = 10;
$category = 'car';
$area_dao = Dao_loader::__get_area_dao();
$area_list = $area_dao->findAreasWithLangByCategoryAndParentId(get_config_current_lang(), $category, 0);
$user_reviews_count = 0;

$message = get_val("message");
$car_day = get_val("car_day");
$end_area = get_val("end_area");
$begin_date = get_val("begin_date");
$car_people = get_val("car_people");

// 包車天數內容(預設間隔0.5)
$day_count = 3;
$day_space = 0.5;
$car_day_list = array();
for ($i = 0.5; $i <= $day_count; $i+=$day_space) {
    $car_day_list[] = $i;
}

// 頁面傳送資料
$s_id = get_val('s_id');
$star = 5;
$image_server_url = get_config_image_server();
$img = '/web/img/no-pic.jpg';
$main_url = "/bookingcar/";
$form_url = "/bookingcar/" . $s_id . "/";

// 偵測頁面參數
if (empty($s_id)) {
    header('Location: ' . $main_url);
    exit;
}

// 取得車隊基本資料
$partner_service = new partner_service();
$partner_row = $partner_service->find_partner_owner_by_store_id($s_id);

if (!empty($partner_row[0]["pi_id"])) {
    $owner_mapping_list = $partner_service->find_store_mapping_list_by_partner_account_Id($partner_row[0]["pi_id"], "tw");
    $store_logo = $image_server_url . '/photos/' . (is_production() ? 'store' : 'store_alpha') . '/' . $s_id . '/logo.jpg';
    $check_image = open_url($store_logo);
    if ($check_image["code"] != 200) {
        $store_logo = $img;
    }
    $owner = $owner_mapping_list[0];
    $store_name = $owner["sml_name"];
    $store_info = $owner["sml_info"];

    // 評價 - 暫時先固定假資料
    $store_total_score = 0;
    $store_service_score = 0;
    $store_comment_score = 0;
    $store_drive_safely_score = 0;
    $store_clean_score = 0;

    $tripitta_service = new tripitta_service();
    $user_reviews = $tripitta_service->find_user_reviews_by_store_id($s_id, $category);
    if(!empty($user_reviews)){
    	$user_reviews_count = count($user_reviews);
    	$s_total = 0;
    	$s_service = 0;
    	$s_comment = 0;
    	$s_drive = 0;
    	$s_clean = 0;
    	foreach ($user_reviews as $ur){
    		$s_total += $ur["ur_evaluation"];
    		$s_service += $ur["ur_item1"];
    		$s_comment += $ur["ur_item2"];
    		$s_drive += $ur["ur_item3"];
    		$s_clean += $ur["ur_item4"];
    	}
    	$store_total_score = $s_total / $user_reviews_count;
    	$store_service_score = $s_service / $user_reviews_count;
    	$store_comment_score = $s_comment / $user_reviews_count;
    	$store_drive_safely_score = $s_drive / $user_reviews_count;
    	$store_clean_score = $s_clean / $user_reviews_count;
    }

    // 附加服務(司機, 執照導遊, 嬰兒座椅, 孩童座椅, 乘客責任險) - 後台沒有的項目： 車上WIFI
    $tripitta_car_service = new tripitta_car_service();
    $fleet_facility = $tripitta_car_service->find_fleet_facility_by_store_id($s_id);
    $fleet_facility_detail_list = $tripitta_car_service->find_fleet_facility_detail_by_ff_id($fleet_facility["ff_id"]);

    $drives = array();
    $tour_guide_license = false;
    $baby_seat = false;
    $child_seat = false;
    $accident_insurance = false;
    $accident_insurance_price = 0;
    if (!empty($fleet_facility_detail_list)) {
        foreach ($fleet_facility_detail_list as $ffl) {
            if ($ffl["ffd_category"] == "lang.support.en" && $ffl["ffd_designation"] == "Y") {
                $drives[] = "英文";
            }
            if ($ffl["ffd_category"] == "lang.support.jp" && $ffl["ffd_designation"] == "Y") {
                $drives[] = "日文";
            }
            if ($ffl["ffd_category"] == "lang.support.ct" && $ffl["ffd_designation"] == "Y") {
                $drives[] = "粵語";
            }
            if ($ffl["ffd_category"] == "lang.support.ko" && $ffl["ffd_designation"] == "Y") {
                $drives[] = "韓語";
            }
            if ($ffl["ffd_category"] == "tour.guide.license" && $ffl["ffd_designation"] == "Y") {
                $tour_guide_license = true;
            }
            if ($ffl["ffd_category"] == "baby.seat" && $ffl["ffd_designation"] == "Y") {
                $baby_seat = true;
            }
            if ($ffl["ffd_category"] == "child.seat" && $ffl["ffd_designation"] == "Y") {
                $child_seat = true;
            }
            if ($ffl["ffd_category"] == "accident.insurance" && $ffl["ffd_designation"] == "Y") {
                $accident_insurance = true;
                $accident_insurance_price = $ffl["ffd_value"];
            }
        }
    }

    // 取得店家基本資料
    $fleet_rule = $tripitta_car_service->find_fleet_rule_by_store_id($s_id);
    // 包車超時加價
    $over_time_add_price = 0;
    $over_time_add_price_row = $tripitta_car_service->find_fleet_rule_detail_by_fr_id_category($fleet_rule["fr_id"], "over.time.add_price");
    if (!empty($over_time_add_price_row)) {
        $over_time_add_price = $over_time_add_price_row[0]["frd_category_value"];
    }

    // 深夜加價
    $midnight_add_price = 0;
    $midnight_add_time = null;
    $midnight_add_price_row = $tripitta_car_service->find_fleet_rule_detail_by_fr_id_category($fleet_rule["fr_id"], "midnight.add.price");
    if (!empty($midnight_add_price_row)) {
        // 目前先抓第一筆資料顯示 - 業者後台規則須再確認
        $add_price = $midnight_add_price_row[0];
        $midnight_add_price = $add_price["frd_category_value"];
        $begin_time = date('H A', strtotime($add_price["frd_begin_time"]));
        $end_time = date('H A', strtotime($add_price["frd_end_time"]));
        $midnight_add_time = $begin_time . "~" . $end_time;
    }

    // 機場深夜加價
    $airport_midnight_add_price = 0;
    $airport_midnight_add_time = null;
    $airport_midnight_add_price_row = $tripitta_car_service->find_fleet_rule_detail_by_fr_id_category($fleet_rule["fr_id"], "airport.midnight.add.price");
    if (!empty($airport_midnight_add_price_row)) {
        // 目前先抓第一筆資料顯示 - 業者後台規則須再確認
        $add_price = $airport_midnight_add_price_row[0];
        $airport_midnight_add_price = $add_price["frd_category_value"];
        $begin_time = date('H A', strtotime($add_price["frd_begin_time"]));
        $end_time = date('H A', strtotime($add_price["frd_end_time"]));
        $airport_midnight_add_time = $begin_time . "~" . $end_time;
    }
} else {
    header('Location: ' . $main_url);
    exit;
}

if ($message != "") {
	$travel_notification_service = new travel_notification_service();
	$message_str = "";
	if ($begin_date != "") {
		$message_str .= "出發日期 : ".$begin_date."<br>";
	}
	if ($end_area != "") {
		$message_str .= "目的地 : ".$end_area."<br>";
	}
	if ($car_day != "") {
		$message_str .= "天數 : ".$car_day." 天<br>";
	}
	if ($car_people != "") {
		$message_str .= "人數 : ".$car_people." 人<br>";
	}
	$message_str .= $message;
	$to_ids = array();
	$account_type = 5;
	$user_id = $_SESSION['travel.ezding.user.data']['serialId'];
	$partner_account = $tripitta_service->get_number_one_partner_account_by_store_id($s_id);
	$to_ids[] = array("to_id" => $partner_account['pa_id'], "account_type" => 40);
	$notification = $travel_notification_service->add_notification($user_id, $account_type, $to_ids, $message_str);
	if ($notification['ngs_group_id'] > 0) {
		alertmsg('您的訊息已經發給車隊業者了!', $form_url);
	}
}
?>
<!DOCTYPE html>
<html lang="zh-Hant" prefix="og: http://ogp.me/ns#">
    <head>
        <?php include __DIR__ . "/../../common/head_new.php"; ?>
        <style>
            .kv{
                width: 100%;
                height: 100%;
                background: url(/web/img/location/location_banner_food.jpg) 50% 50%/cover no-repeat;
            }
        </style>
        <title><?php ""; ?>交通預定 - Tripitta 旅必達</title>
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css">
        <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
        <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
        <script src="/web/js/jquery.twbsPagination.js" type="text/javascript"></script>
        <link rel="stylesheet" href="/web/css/main.css">
        <link rel="stylesheet" href="/web/css/main2.css">
    </head>
    <body>
        <header><?php include __DIR__ . "/../../common/header_new.php"; ?></header>
        <main class="charter-fleet-container">
            <section>
                <div class="info">
                    <figure class="titleImg">
                        <div class="img" style="background: url('<?php echo $store_logo; ?>') center center;"></div>
                        <figcaption class="detail">
                            <div class="wrap">
                                <h1><?php echo $store_name; ?></h1>
                            </div>
                            <div class="subtitle">車隊介紹</div>
                            <div class="content">
                                <?php echo $store_info; ?>
                            </div>
                        </figcaption>
                    </figure>
                    <label class="btnWrap">
                        <button class="submit">
                            線上諮詢 <i class="img-qa"></i>
                        </button>
                    </label>
                    <div class="aveRating">
                        <div class="aveFrame">
                            <div class="text">評價總平均</div>
                            <div class="starWrap">
                                <div class="point">
                                    <span><?php echo $store_total_score; ?></span>
                                    <span>/ 5</span>
                                </div>
                                <div class="stars">
                                    <?php
                                    $cnt = $store_total_score;
                                    for ($i = 1; $i <= $star; $i++) {
                                        $class = "fa-star-o";
                                        if ($i <= intval($cnt)) {
                                            $class = "fa-star";
                                        } else {
                                            if ((1 - ($i - $cnt)) > 0 && is_float($cnt - $i)) {
                                                $class = "fa-star-half-o";
                                            }
                                        }
                                        ?>
                                        <i class="fa <?php echo $class; ?>"></i>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="detailFrame">
                            <div class="starWrap">
                                <div class="meta">服務態度</div>
                                <div class="stars">
                                    <?php
                                    $cnt = $store_service_score;
                                    for ($i = 1; $i <= $star; $i++) {
                                        $class = "fa-star-o";
                                        if ($i <= intval($cnt)) {
                                            $class = "fa-star";
                                        } else {
                                            if ((1 - ($i - $cnt)) > 0 && is_float($cnt - $i)) {
                                                $class = "fa-star-half-o";
                                            }
                                        }
                                        ?>
                                        <i class="fa <?php echo $class; ?>"></i>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="starWrap">
                                <div class="meta">解說導覧</div>
                                <div class="stars">
                                    <?php
                                    $cnt = $store_comment_score;
                                    for ($i = 1; $i <= $star; $i++) {
                                        $class = "fa-star-o";
                                        if ($i <= intval($cnt)) {
                                            $class = "fa-star";
                                        } else {
                                            if ((1 - ($i - $cnt)) > 0 && is_float($cnt - $i)) {
                                                $class = "fa-star-half-o";
                                            }
                                        }
                                        ?>
                                        <i class="fa <?php echo $class; ?>"></i>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="starWrap">
                                <div class="meta">安全駕駛</div>
                                <div class="stars">
                                    <?php
                                    $cnt = $store_drive_safely_score;
                                    for ($i = 1; $i <= $star; $i++) {
                                        $class = "fa-star-o";
                                        if ($i <= intval($cnt)) {
                                            $class = "fa-star";
                                        } else {
                                            if ((1 - ($i - $cnt)) > 0 && is_float($cnt - $i)) {
                                                $class = "fa-star-half-o";
                                            }
                                        }
                                        ?>
                                        <i class="fa <?php echo $class; ?>"></i>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="starWrap">
                                <div class="meta">車況整潔</div>
                                <div class="stars">
                                    <?php
                                    $cnt = $store_clean_score;
                                    for ($i = 1; $i <= $star; $i++) {
                                        $class = "fa-star-o";
                                        if ($i <= intval($cnt)) {
                                            $class = "fa-star";
                                        } else {
                                            if ((1 - ($i - $cnt)) > 0 && is_float($cnt - $i)) {
                                                $class = "fa-star-half-o";
                                            }
                                        }
                                        ?>
                                        <i class="fa <?php echo $class; ?>"></i>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="add">
                        <div class="aTItle">附加服務</div>
                        <ul class="aList">
                            <?php
                            if (!empty($drives)) {
                                foreach ($drives as $d) {
                                    ?>
                                    <li><?php echo $d; ?>司機</li>
                                    <?php
                                }
                            }
                            ?>
                            <?php if ($tour_guide_license) { ?>
                                <li>執照導遊</li>
                            <?php } ?>
                            <?php if ($baby_seat) { ?>
                                <li>嬰兒座椅</li>
                            <?php } ?>
                            <?php if ($child_seat) { ?>
                                <li>孩童座椅</li>
                            <?php } ?>
                            <?php /* 後台沒有先隱藏
                              <li>車上WIFI</li>
                             */ ?>
                            <?php if ($accident_insurance) { ?>
                                <li>乘客責任險</li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="add">
                        <div class="aTItle">加價項目</div>
                        <ul class="aList">
                            <?php if ($midnight_add_price > 0) { ?>
                                <li>深夜時段 <span class="mark"><?php echo $midnight_add_time; ?></span></li>
                                <li>深夜加價 <span class="mark">NTD <?php echo $midnight_add_price; ?></span></li>
                            <?php } ?>
                            <?php if ($airport_midnight_add_price > 0) { ?>
                                <li>機場深夜時段 <span class="mark"><?php echo $airport_midnight_add_time; ?></span></li>
                                <li>機場深夜加價 <span class="mark">NTD <?php echo $airport_midnight_add_price; ?></span></li>
                            <?php } ?>
                            <?php if ($over_time_add_price > 0) { ?>
                                <li>超時 <span class="mark">NTD <?php echo $over_time_add_price; ?></span> /小時</li>
                            <?php } ?>
                            <?php if ($accident_insurance_price > 0) { ?>
                                <li>乘客保險 <span class="mark">NTD <?php echo $accident_insurance_price; ?></span> 萬</li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div id="popupQuest" class="popupQuest">
                        <div class="closeBtn">
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </div>
                        <div class="fBlock">
                            <div class="fImg">
                                <div class="default">
                                    <i class="fa fa-user"></i>
                                </div>
                            </div>
                            <div class="fTitle"><div>發訊息給</div><div><?php echo $store_name; ?></div></div>
                        </div>
                        <div class="fContent">
                            如果網頁上的建議行程不符合你的需求，或是在訂購前有其他問題，你可在此發訊息詢問。
                        </div>
                        <form role="form" id="form1" method="post" action="<?php echo $form_url; ?>">
                        <div class="cBlock">
                            <div class="cbOpBlk">
                                <div class="wrap">
                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                    <input id="begin_date" name="begin_date" type="text" class="sText" maxlength="30" placeholder="出發日期"></input>
                                </div>
                                <div class="wrap">
                                    <i class="fa fa-map-marker" aria-hidden="true"></i>
                                    <div class="selectWrap">
                                        <select id="end_area" name="end_area">
                                            <?php
                                            if (!empty($area_list)) {
                                                foreach ($area_list as $a) {
                                                    ?>
                                                    <option value="<?php echo $a["a_id"]; ?>"><?php echo $a["a_name"]; ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                            <option selected disabled>選擇目的地</option>
                                        </select>
                                        <i class="fa fa-angle-down" aria-hidden="true"></i>
                                    </div>
                                </div>
                                <div class="wrap">
                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                    <div class="selectWrap">
                                        <select id="car_day" name="car_day">
                                            <?php
                                            if (!empty($car_day_list)) {
                                                foreach ($car_day_list as $k => $cdl) {
                                                    $car_day_name = null;
                                                    if ($k == 0) {
                                                        $car_day_name = "半日";
                                                    } else {
                                                        $car_day_name = $cdl . "日";
                                                    }
                                                    ?>
                                                    <option value="<?php echo $cdl; ?>"><?php echo $car_day_name; ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                                    <option value="" selected disabled>天數</option>
                                        </select>
                                        <i class="fa fa-angle-down" aria-hidden="true"></i>
                                    </div>
                                </div>
                                <div class="wrap">
                                    <i class="fa fa-user" aria-hidden="true"></i>
                                    <div class="selectWrap">
                                        <select id="car_people" name="car_people">
                                            <?php for ($i = 1; $i <= $people_count; $i++) { ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?>人</option>
                                            <?php } ?>
                                                <option value="" selected disabled>人數</option>
                                        </select>
                                        <i class="fa fa-angle-down" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                            <textarea id="message" name="message" class="cbText" rows="12" cols="50" placeholder="請填上您想詢問的內容或是有什麼其他的需求？"></textarea>
                            <div class="sBtnWrap">
                                <button type="button" class="submit">發送訊息</button>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
            </section>
            <section>
                <div class="reviews">
                    <h2>旅客評價  /  共來自 <span class="mark"><?php echo $user_reviews_count; ?></span> 位</h2>
                    <?php
                    if(!empty($user_reviews)){
                    	foreach ($user_reviews as $ur){
                    		?>
                    <div class="review">
                        <div class="user">
                            <div class="default">
                                <i class="fa fa-user"></i>
                            </div>
                            <ul class="uInfo">
                                <li><?php echo date("Y-m-d", strtotime($ur["ur_create_time"])); ?></li>
                                <li>來自 <span><?php echo strlen($ur["country_name"]); ?></span> 旅客</li>
                                <li><?php echo substr($ur["user_name"], 0, 3); ?>**  <?php echo $ur["gender"]=="M" ? "女士" : "先生"; ?></li>
                            </ul>
                        </div>
                        <div class="conWrap">
                            <div class="tWrap">
                                <h3><?= $ur["cr_name"] ?></h3>
                                <div class="rInfo">
                                    <div class="rating">
                                        <?php
                                        $cnt = $ur["ur_evaluation"];
                                        for ($i = 1; $i <= $star; $i++) {
                                            $class = "fa-star-o";
                                            if ($i <= intval($cnt)) {
                                                $class = "fa-star";
                                            } else {
                                                if ((1 - ($i - $cnt)) > 0 && is_float($cnt - $i)) {
                                                    $class = "fa-star-half-o";
                                                }
                                            }
                                            ?>
                                            <i class="fa <?php echo $class; ?>"></i>
                                            <?php
                                        }
                                        ?>
                                        <span class="rPoint"><?php echo $ur["ur_evaluation"]; ?></span>
                                        <span class="rRange"> / 5</span>
                                    </div>
                                    <?php if(!empty($ur["ur_start_date"])){ ?>
                                    <div class="time">出發日：<?php echo date("Y-m-d", strtotime($ur["ur_start_date"])); ?></div>
                                    <?php } ?>
                                </div>
                            </div>
                            <hr>
                            <h4><?php echo $ur["ur_title"]; ?></h4>
                            <div class="ratings">
                                <div class="rcontent">
                                    <p class="content">
                                        <?php echo $ur["ur_content"]; ?>
                                    </p>
                                    <div class="starWrapFrame">
                                        <div class="starWrap">
                                            <div class="meta">服務態度</div>
                                            <div class="stars">
                                                <?php
                                                $cnt = $ur["ur_item1"];
                                                for ($i = 1; $i <= $star; $i++) {
                                                    $class = "fa-star-o";
                                                    if ($i <= intval($cnt)) {
                                                        $class = "fa-star";
                                                    } else {
                                                        if ((1 - ($i - $cnt)) > 0 && is_float($cnt - $i)) {
                                                            $class = "fa-star-half-o";
                                                        }
                                                    }
                                                    ?>
                                                    <i class="fa <?php echo $class; ?>"></i>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="starWrap">
                                            <div class="meta">解說導覧</div>
                                            <div class="stars">
                                                <?php
                                                $cnt = $ur["ur_item2"];
                                                for ($i = 1; $i <= $star; $i++) {
                                                    $class = "fa-star-o";
                                                    if ($i <= intval($cnt)) {
                                                        $class = "fa-star";
                                                    } else {
                                                        if ((1 - ($i - $cnt)) > 0 && is_float($cnt - $i)) {
                                                            $class = "fa-star-half-o";
                                                        }
                                                    }
                                                    ?>
                                                    <i class="fa <?php echo $class; ?>"></i>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="starWrap">
                                            <div class="meta">安全駕駛</div>
                                            <div class="stars">
                                                <?php
                                                $cnt = $ur["ur_item3"];
                                                for ($i = 1; $i <= $star; $i++) {
                                                    $class = "fa-star-o";
                                                    if ($i <= intval($cnt)) {
                                                        $class = "fa-star";
                                                    } else {
                                                        if ((1 - ($i - $cnt)) > 0 && is_float($cnt - $i)) {
                                                            $class = "fa-star-half-o";
                                                        }
                                                    }
                                                    ?>
                                                    <i class="fa <?php echo $class; ?>"></i>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="starWrap">
                                            <div class="meta">車況整潔</div>
                                            <div class="stars">
                                                <?php
                                                $cnt = $ur["ur_item4"];
                                                for ($i = 1; $i <= $star; $i++) {
                                                    $class = "fa-star-o";
                                                    if ($i <= intval($cnt)) {
                                                        $class = "fa-star";
                                                    } else {
                                                        if ((1 - ($i - $cnt)) > 0 && is_float($cnt - $i)) {
                                                            $class = "fa-star-half-o";
                                                        }
                                                    }
                                                    ?>
                                                    <i class="fa <?php echo $class; ?>"></i>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="readMore" data-times="0" data-height="0"></div>
                            </div>
                        </div>
                    </div>
                    		<?php
                    	}
                    }
                    ?>
            	</div>
            </section>
        </main>
        <footer><? include __DIR__ . "/../../common/footer_new.php"; ?></footer>
        <?php include __DIR__ . '/../../common/ga.php';?>
        <script type="text/javascript">
            $(function () {
                var caneldar_option = <?= json_encode(Constants::$CALENDAR_OPTIONS) ?>;
                $('#begin_date').datepicker(caneldar_option).datepicker('option', {minDate: new Date()});

                $('.rcontent').each(function () {
                    if ($(this).height() > 36) {
                        var tempHeight = $(this).height();
                        $(this).height(72);
                        $(this).siblings(".readMore").html('<a href="javascript:void(0)"><div class="readBtn">展開</div></a>');
                        $(this).siblings(".readMore").data("height", tempHeight);
                    }
                });
                $(".readMore").click(function () {
                    var times = $(this).data("times");
                    $(this).html('<a href="javascript:void(0)"><div class="readBtn">展開</div></a>');
                    if (times % 2 == 1) {
                        $(this).siblings(".rcontent").animate({
                            height: "72px"
                        }, 500, function () {
                        });
                    } else {
                        $(this).html('<a href="javascript:void(0)"><div class="readBtn">收起</div></a>');
                        $(this).siblings(".rcontent").animate({
                            height: $(this).data("height")
                        }, 500
                                );
                    }
                    times += 1;
                    $(this).data("times", times);
                });

                // 線上諮詢按鈕功能檢查
                $('.info .btnWrap .submit').click(function () {
                    var data = '<?php echo $header_is_login; ?>';
                    if(data == 1){
                		$('.overlay').show();
                    	$('#popupQuest').show();
                		$(window).scrollTop("0");
                    }else{
                    	show_popup_login();
                    }
                });
                // 關閉線上諮詢按鈕功能視窗
                $('#popupQuest .closeBtn').click(function () {
            		$('.overlay').hide();
                    $('#popupQuest').hide();
                });
                // 線上諮詢視窗-內容檢查
                $('#popupQuest .sBtnWrap .submit').click(function () {
                    var msg = '';
//                     var begin_date = $('#popupQuest #begin_date').val();
//                     if (begin_date == '') {
//                         msg += '出發日期必須選擇!\n';
//                     }
//                     var end_area = $('#popupQuest #end_area').val();
//                     if (end_area == '' || end_area==null) {
//                         msg += '目的地必須選擇!\n';
//                     }
//                     var car_day = $('#popupQuest #car_day :selected').val();
//                     if (car_day == '' || car_day == 0) {
//                         msg += '天數必須選擇!\n';
//                     }
//                     var car_people = $('#popupQuest #car_people :selected').val();
//                     if (car_people == '' || car_people == 0) {
//                         msg += '人數必須選擇!\n';
//                     }
                    var message = $('#popupQuest #message').val();
                    if (message == '') {
                        msg += '詢問的內容必須填寫!\n';
                    }
                    if (msg != '') {
                        alert(msg);
                    } else {
                        $('#popupQuest #form1').submit();
                    }
                });
            })
        </script>
    </body>
</html>