<!DOCTYPE html>
<html lang="zh-Hant" prefix="og: http://ogp.me/ns#">
<head>
<?php
$t1 = microtime(true);
/**
 *  說明：Tripitta 首頁
 *  作者：John <john.chien@fullerton.com.tw>
 *  日期：2015年12月17日
 *  備註：
 *  2015-12-17 John 檢查為裝置手機且寬度小於640，將logo移至kv圖的一半高度
 *  2015-12-18 John js加上檢查瀏覽器版本
 *  2015-12-21 Cheans 調整 KV 搜尋Bar以符合Vincent想要之版本,
 *      美食、景點之地區為縣市分區(北、中、南、東、離島)
 *      旅記、旅宿之地區為觀光區(hf_area.a_category = 'homestay' and a_parent_id = 0)
 */
require_once 'config.php';
//include __DIR__ . '/../inc/mobile_detect.class.php';

const KEY_VISION = 'homepage.key.vision';
const SELL_BANNER = 'homepage.sell.banner';
const EXCELLENT_FOOD = 'homepage.excellent.food';
const EXCELLENT_PLAN = 'homepage.excellent.plan';
const RECOMMEND_HOMESTAY = 'homepage.recommend.homestay';
const FLATTERING = 'homepage.flattering';
const MAGICAL_EGG = 'homepage.magical.egg';


$str = 'this is john test';


// $key = '(^ezding$)\0\0\0\0\0\0';
$key = '(^ezding$)';
$data = 'hopO363Qqxl35tZqyNlioQ==';
$rc4 = new CryptRC4();
$result = $rc4->decrypt($key, $data);
printmsg($result);

$en_str = $rc4->encrypt($key, $str);
printmsg($en_str);

$de_str = $rc4->decrypt($key, $en_str);
printmsg($de_str);








$tripitta_web_service = new tripitta_web_service();
$travel_ez_content_service = new travel_ez_content_service();
$content_service = new content_service();
$detect = new Mobile_Detect;

$deviceType = 'computer';
if($detect->isMobile()) $deviceType = 'phone';
// $taiwan_content_service = new taiwan_content_service();

// 首頁資料暫存，預設為5分鐘
$cache = get_cache();

// 取得地區資料
$area_list = $tripitta_web_service->find_valid_area_for_search_by_category_and_parent_id(get_config_current_lang(), 'homestay', 0);
$location_list = array(array('key' => 'north', 'val' => '基隆 台北 桃園 新竹 苗栗')
        , array('key' => 'east', 'val' => '宜蘭 花蓮 台東')
        , array('key' => 'south', 'val' => '雲林 嘉義 台南 高雄 屏東')
        , array('key' => 'west', 'val' => '臺中 彰化 南投 澎湖')
        , array('key' => 'islands', 'val' => '金門 馬祖 綠島 蘭嶼 小琉球'));

// key vision
$kv_list = $cache->get(KEY_VISION);
if (empty($kv_list)) {
    $kv_list = $travel_ez_content_service->find_key_vision(1);
    $cache->set(KEY_VISION, $kv_list);
}
// printmsg($kv_list);

// 行銷banner
$sell_banner_list = $cache->get(SELL_BANNER);
if (empty($sell_banner_list)) {
    $sell_banner_list = $travel_ez_content_service->find_homepage_banner(1);
    $cache->set(SELL_BANNER, $sell_banner_list);
}
// printmsg($sell_banner_list);

// 精彩美食
$excellent_food_list = $cache->get(EXCELLENT_FOOD);
if (empty($excellent_food_list)) {
    $excellent_food_list = $content_service->find_valid_excellent_food();
    $cache->set(EXCELLENT_FOOD, $excellent_food_list);
}

// 精彩遊記
$excellent_plan_list = $cache->get(EXCELLENT_PLAN);
if (empty($excellent_plan_list)) {
    $excellent_plan_list = $content_service->find_valid_excellent_plan();
    $cache->set(EXCELLENT_PLAN, $excellent_plan_list);
}

// 推薦旅宿
$recommend_homestay_list = $cache->get(RECOMMEND_HOMESTAY);
if (empty($recommend_homestay_list)) {
    $recommend_homestay_list = $content_service->find_valid_recommend_homestay();
    $cache->set(RECOMMEND_HOMESTAY, $recommend_homestay_list);
}

// smart guide
$flattering_list = $cache->get(FLATTERING);
if (empty($flattering_list)) {
    $flattering_list = $content_service->find_valid_flattering();
    $cache->set(FLATTERING, $flattering_list);
}

// 神奇蛋
$magical_egg_list = $cache->get(MAGICAL_EGG);
if (empty($magical_egg_list)) {
    $magical_egg_list = $content_service->find_valid_magical_egg();
    $cache->set(MAGICAL_EGG, $magical_egg_list);
}

// 取得各分類的 kv 資料
$food = array();
$homestay = array();
$scenic = array();
$topic = array();
$travel_note = array();
foreach ($kv_list as $kv) {
    if ('kv.food' == $kv['cc_content_code']) {
        array_push($food, $kv);
    }
    else if ('kv.homestay' == $kv['cc_content_code']) {
        array_push($homestay, $kv);
    }
    else if ('kv.scenic' == $kv['cc_content_code']) {
        array_push($scenic, $kv);
        $default_kv = $kv;
    }
    else if ('kv.topic' == $kv['cc_content_code']) {
        array_push($topic, $kv);
    }
    else if ('kv.travel.note' == $kv['cc_content_code']) {
        array_push($travel_note, $kv);
    }
}

// 取得最新一筆當成預設 kv
//$default_kv = $kv_list[0];

// 組成精彩美食需要的內容
$food_1_title = $excellent_food_list[0]['cm_reference_title'];
$food_2_title = $excellent_food_list[1]['cm_reference_title'];
$food_3_title = $excellent_food_list[2]['cm_reference_title'];
$food_4_title = $excellent_food_list[3]['cm_reference_title'];
$food_5_title = $excellent_food_list[4]['cm_reference_title'];
$food_6_title = $excellent_food_list[5]['cm_reference_title'];
$food_1_photo = get_config_image_server() . get_config_taiwan_content_image_path() . '/' . $excellent_food_list[0]['tc_id'] . '/' . $excellent_food_list[0]['tc_main_photo'] . '.jpg';
$food_2_photo = get_config_image_server() . get_config_taiwan_content_image_path() . '/' . $excellent_food_list[1]['tc_id'] . '/' . $excellent_food_list[1]['tc_main_photo'] . '.jpg';
$food_3_photo = get_config_image_server() . get_config_taiwan_content_image_path() . '/' . $excellent_food_list[2]['tc_id'] . '/' . $excellent_food_list[2]['tc_main_photo'] . '.jpg';
$food_4_photo = get_config_image_server() . get_config_taiwan_content_image_path() . '/' . $excellent_food_list[3]['tc_id'] . '/' . $excellent_food_list[3]['tc_main_photo'] . '.jpg';
$food_5_photo = get_config_image_server() . get_config_taiwan_content_image_path() . '/' . $excellent_food_list[4]['tc_id'] . '/' . $excellent_food_list[4]['tc_main_photo'] . '.jpg';
$food_6_photo = get_config_image_server() . get_config_taiwan_content_image_path() . '/' . $excellent_food_list[5]['tc_id'] . '/' . $excellent_food_list[5]['tc_main_photo'] . '.jpg';
$food_1_link = '/location/food/' . $excellent_food_list[0]['tc_id'] . '/';
$food_2_link = '/location/food/' . $excellent_food_list[1]['tc_id'] . '/';
$food_3_link = '/location/food/' . $excellent_food_list[2]['tc_id'] . '/';
$food_4_link = '/location/food/' . $excellent_food_list[3]['tc_id'] . '/';
$food_5_link = '/location/food/' . $excellent_food_list[4]['tc_id'] . '/';
$food_6_link = '/location/food/' . $excellent_food_list[5]['tc_id'] . '/';

// 組成精彩遊記需要的內容
mb_internal_encoding("UTF-8");
$plan_1_title_count = mb_strlen($excellent_plan_list[0]['cm_reference_title'], 'UTF-8');
$excellent_plan_list[0]['cm_reference_title'] = ($plan_1_title_count > 14) ? mb_substr($excellent_plan_list[0]['cm_reference_title'], 0, 14)."..." : $excellent_plan_list[0]['cm_reference_title'];
$plan_1_title = $excellent_plan_list[0]['cm_reference_title'];
$plan_2_title_count = mb_strlen($excellent_plan_list[1]['cm_reference_title'], 'UTF-8');
$excellent_plan_list[1]['cm_reference_title'] = ($plan_2_title_count > 14) ? mb_substr($excellent_plan_list[1]['cm_reference_title'], 0, 14)."..." : $excellent_plan_list[1]['cm_reference_title'];
$plan_2_title = $excellent_plan_list[1]['cm_reference_title'];
$plan_3_title_count = mb_strlen($excellent_plan_list[2]['cm_reference_title'], 'UTF-8');
$excellent_plan_list[2]['cm_reference_title'] = ($plan_3_title_count > 14) ? mb_substr($excellent_plan_list[2]['cm_reference_title'], 0, 14)."..." : $excellent_plan_list[2]['cm_reference_title'];
$plan_3_title = $excellent_plan_list[2]['cm_reference_title'];
$plan_4_title_count = mb_strlen($excellent_plan_list[3]['cm_reference_title'], 'UTF-8');
$excellent_plan_list[3]['cm_reference_title'] = ($plan_4_title_count > 14) ? mb_substr($excellent_plan_list[3]['cm_reference_title'], 0, 14)."..." : $excellent_plan_list[3]['cm_reference_title'];
$plan_4_title = $excellent_plan_list[3]['cm_reference_title'];
$plan_5_title_count = mb_strlen($excellent_plan_list[4]['cm_reference_title'], 'UTF-8');
$excellent_plan_list[4]['cm_reference_title'] = ($plan_5_title_count > 14) ? mb_substr($excellent_plan_list[4]['cm_reference_title'], 0, 14)."..." : $excellent_plan_list[4]['cm_reference_title'];
$plan_5_title = $excellent_plan_list[4]['cm_reference_title'];
$plan_6_title_count = mb_strlen($excellent_plan_list[5]['cm_reference_title'], 'UTF-8');
$excellent_plan_list[5]['cm_reference_title'] = ($plan_6_title_count > 14) ? mb_substr($excellent_plan_list[5]['cm_reference_title'], 0, 14)."..." : $excellent_plan_list[5]['cm_reference_title'];
$plan_6_title = $excellent_plan_list[5]['cm_reference_title'];
$plan_1_photo = get_config_image_server() . '/photos/' . (is_production() ? 'travel_plan' : 'travel_plan_alpha')  . '/' . $excellent_plan_list[0]['tpe_id'] . '/' . $excellent_plan_list[0]['tp_cover_photo'] . '.jpg';
$plan_2_photo = get_config_image_server() . '/photos/' . (is_production() ? 'travel_plan' : 'travel_plan_alpha')  . '/' . $excellent_plan_list[1]['tpe_id'] . '/' . $excellent_plan_list[1]['tp_cover_photo'] . '.jpg';
$plan_3_photo = get_config_image_server() . '/photos/' . (is_production() ? 'travel_plan' : 'travel_plan_alpha')  . '/' . $excellent_plan_list[2]['tpe_id'] . '/' . $excellent_plan_list[2]['tp_cover_photo'] . '.jpg';
$plan_4_photo = get_config_image_server() . '/photos/' . (is_production() ? 'travel_plan' : 'travel_plan_alpha')  . '/' . $excellent_plan_list[3]['tpe_id'] . '/' . $excellent_plan_list[3]['tp_cover_photo'] . '.jpg';
$plan_5_photo = get_config_image_server() . '/photos/' . (is_production() ? 'travel_plan' : 'travel_plan_alpha')  . '/' . $excellent_plan_list[4]['tpe_id'] . '/' . $excellent_plan_list[4]['tp_cover_photo'] . '.jpg';
$plan_6_photo = get_config_image_server() . '/photos/' . (is_production() ? 'travel_plan' : 'travel_plan_alpha')  . '/' . $excellent_plan_list[5]['tpe_id'] . '/' . $excellent_plan_list[5]['tp_cover_photo'] . '.jpg';
$plan_1_link = '/trip/' . $excellent_plan_list[0]['tpe_id'] . '/';
$plan_2_link = '/trip/' . $excellent_plan_list[1]['tpe_id'] . '/';
$plan_3_link = '/trip/' . $excellent_plan_list[2]['tpe_id'] . '/';
$plan_4_link = '/trip/' . $excellent_plan_list[3]['tpe_id'] . '/';
$plan_5_link = '/trip/' . $excellent_plan_list[4]['tpe_id'] . '/';
$plan_6_link = '/trip/' . $excellent_plan_list[5]['tpe_id'] . '/';
$plan_1_favorite = number_format($excellent_plan_list[0]['tpe_collect_total']);
$plan_2_favorite = number_format($excellent_plan_list[1]['tpe_collect_total']);
$plan_3_favorite = number_format($excellent_plan_list[2]['tpe_collect_total']);
$plan_4_favorite = number_format($excellent_plan_list[3]['tpe_collect_total']);
$plan_5_favorite = number_format($excellent_plan_list[4]['tpe_collect_total']);
$plan_6_favorite = number_format($excellent_plan_list[5]['tpe_collect_total']);
$plan_1_view_count = number_format($excellent_plan_list[0]['tpe_click_total']);
$plan_2_view_count = number_format($excellent_plan_list[1]['tpe_click_total']);
$plan_3_view_count = number_format($excellent_plan_list[2]['tpe_click_total']);
$plan_4_view_count = number_format($excellent_plan_list[3]['tpe_click_total']);
$plan_5_view_count = number_format($excellent_plan_list[4]['tpe_click_total']);
$plan_6_view_count = number_format($excellent_plan_list[5]['tpe_click_total']);
$plan_1_location = '';
$plan_2_location = '';
$plan_3_location = '';
$plan_4_location = '';
$plan_5_location = '';
$plan_6_location = '';
if (!empty($excellent_plan_list[0]['area_name'])) $plan_1_location = $excellent_plan_list[0]['area_name'] . '-' . $excellent_plan_list[0]['small_area_name'];
if (!empty($excellent_plan_list[1]['area_name'])) $plan_2_location = $excellent_plan_list[1]['area_name'] . '-' . $excellent_plan_list[1]['small_area_name'];
if (!empty($excellent_plan_list[2]['area_name'])) $plan_3_location = $excellent_plan_list[2]['area_name'] . '-' . $excellent_plan_list[2]['small_area_name'];
if (!empty($excellent_plan_list[3]['area_name'])) $plan_4_location = $excellent_plan_list[3]['area_name'] . '-' . $excellent_plan_list[3]['small_area_name'];
if (!empty($excellent_plan_list[4]['area_name'])) $plan_5_location = $excellent_plan_list[4]['area_name'] . '-' . $excellent_plan_list[4]['small_area_name'];
if (!empty($excellent_plan_list[5]['area_name'])) $plan_6_location = $excellent_plan_list[5]['area_name'] . '-' . $excellent_plan_list[5]['small_area_name'];

// 取得推薦旅宿大圖 /photos/travel/recommend_homestay/ /photos/alpha_travel/recommend_homestay/

$recommend_homestay = $recommend_homestay_list[0];
$recommend_homestay_first_pic = get_config_image_url() . 'recommend_homestay/' . $recommend_homestay['cc_pk'] . '/' . $recommend_homestay['cc_content'] . '.jpg';
$recommend_homestay_first_title = $recommend_homestay['cc_title'];
if(empty($recommend_homestay_first_title) && !empty($recommend_homestay['hs_name'])) {
    $recommend_homestay_first_title = $recommend_homestay['hs_name'];
}
$recommend_homestay_first_area = $recommend_homestay['area_name'] . '-' . $recommend_homestay['small_area_name'];
$recommend_homestay_first_view_count = 0;
$recommend_homestay_first_favorite = 0;
if (!empty($recommend_homestay['hsc_cnt_view_total'])) $recommend_homestay_first_view_count = number_format($recommend_homestay['hsc_cnt_view_total']);
if (!empty($recommend_homestay['hs_favorite'])) $recommend_homestay_first_favorite = number_format($recommend_homestay['hs_favorite']);

// 隨機取出一筆神奇蛋
$goMagic = rand(0, (count($magical_egg_list) - 1));
$magical_egg = $magical_egg_list[$goMagic];
?>
	<title>Tripitta 台灣自由行旅遊網</title>
	<meta name="keywords" content="台灣自由行,旅遊攻略,民宿訂房,行程規劃,觀光指南">
	<? include __DIR__ . "/pages/common/head.php"; ?>
	<meta property="og:type" content="website">
	<meta property="og:site_name" content="Tripitta 台灣自由行旅遊網">
	<meta property="og:locale" content="zh_TW">
	<meta property="og:url" content="https://www.tripitta.com/">
	<meta property="og:image" content="https://www.tripitta.com/photos/content/key_vision/5460.jpg">
	<meta property="og:title" content="Tripitta 台灣自由行旅遊網">
	<meta property="og:description" content="Tripitta旅遊網，提供中國、香港、澳門等亞洲地區旅行者到台灣自由行旅遊攻略，民宿訂房及行程規劃。Tripitta旅遊網，您最佳的行程規劃助手！">
	<meta property="og:app_id" content="474784012720774">
	<meta name="google-site-verification" content="HTVGwlmAfdHwjizl0l0XZ4ceMoEoOq02y0K3QaiGgzg" />
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<link rel="stylesheet" href="/web/css/swiper.min.css">
	<script type="application/ld+json">
    {
        "@context": "http://schema.org",
        "@type": "Organization",
        "name": "Tripitta 台灣自由行旅遊網",
        "url": "https://www.tripitta.com/",
        "description": "Tripitta旅遊網，提供中國、香港、澳門等亞洲地區旅行者到台灣自由行旅遊攻略，民宿訂房及行程規劃。Tripitta旅遊網，您最佳的行程規劃助手！",
        "image": "https://www.tripitta.com//web/img/logo.jpg"
    }
    </script>
    <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script src="/web/js/swiper.jquery.min.js"></script>
	<script>
	/** 檢查瀏覽器版本 start **/
	var ua = navigator.userAgent.toLowerCase();
        msie = ["msie 6", "msie 7", "msie 8", "msie 9", "msie 10"];
    for (var i = 0; i < msie.length; i++) {
        if (ua.search(msie[i]) != -1) {
            alert("您的瀏覽器支援度不足，建議您下載新版 Firefox 或 Chrome瀏覽網頁，它會是您最佳的選擇！\n\n" + "按下確定後將自動轉到下載頁。");
            location.href = "http://mozilla.com.tw/";
        }
    }
    /** 檢查瀏覽器版本 end **/


	/** key vision start **/
	function show_kv_img(src) {
		src = '<?php echo get_config_image_server()?>' + src;
        $('#vdo').html('');
        $('#vdo').css('background-image','url("' + src + '")');
	}
	function show_kv_vdo(src) {
		src = '<?php echo get_config_image_server()?>' + src;
		content = '<video width="100%" height="100%" src="' + src + '" autoplay></video>';
		$('#vdo').css('background-image','');
		$('#vdo').html(content);
	}
	function show_kv_vdo_yt(src) {
		src2 = 'https://www.youtube.com/embed/' + src;
		src2 += '?autoplay=1&showinfo=0&controls=0&loop=1&playlist='+ src;
		content = '<iframe width="100%" height="100%" src="' + src2 + '" frameborder="0"></iframe>';
		$('#vdo').css('background-image','');
		$('#vdo').html(content);
	}
	function change_kv_type(type) {
	    kv_obj = eval('kv_' + type + '[kv_' + type + '_idx]');
	    reset_kv_search_bar(type);
	    if ('1' == kv_obj.cc_content_type) show_kv_img(kv_obj.cc_content);
	    else if ('2' == kv_obj.cc_content_type) show_kv_vdo(kv_obj.cc_content);
	    else if ('3' == kv_obj.cc_content_type) show_kv_vdo_yt(kv_obj.cc_content);

	    $('#kv_title').html(kv_obj.cc_title);
	    kv_title_link = kv_obj.cc_link_url;

	    eval('kv_' + type + '_idx += 1');
	    eval('if (kv_' + type + '_idx >= kv_' + type + '.length) kv_' + type + '_idx = 0');

	    $("#"+type).css("color","yellow").siblings().css("color","white");

	}
	var last_kv_type = '';
	function reset_kv_search_bar(type) {
		var item_list = [];
		var option_str = '';
		if('food' == type || 'scenic' == type) {
			for(var i=0 ; i<kv_location_list.length ; i++) {
				option_str += '<option value="' + kv_location_list[i].key + '">' + kv_location_list[i].val + '</option>';
			}
		} else {
			for(var i=0 ; i<kv_area_list.length ; i++) {
				item_list.push({'key':kv_area_list[i].a_id, 'val':kv_area_list[i].a_name});
				option_str += '<option value="' + kv_area_list[i].a_code + '">' + kv_area_list[i].a_name + '</option>';
			}
		}
		if(last_kv_type == '') {
			$('#kv_area').html(option_str);
		} else if((('scenic' == last_kv_type || 'food' == last_kv_type) && ('scenic' != type && 'food' != type))
				|| (('travel_note' == last_kv_type || 'homestay' == last_kv_type) && ('travel_note' != type && 'homestay' != type))) {
			$('#kv_area').html(option_str);
		}
		last_kv_type = type;
	}

	function goTitleLink() {
		if ('' != kv_title_link) {
			window.open(kv_title_link);
		}
	}

	var kv_food_idx = 0;
	var kv_homestay_idx = 0;
	var kv_scenic_idx = 0;
	var kv_topic_idx = 0;
	var kv_travel_note_idx = 0;

	var kv_title_link = '';

	var default_kv_bg = '/web/img/visual.jpg';

	var kv_food = <?php echo json_encode($food, JSON_UNESCAPED_UNICODE)?>;
	var kv_homestay = <?php echo json_encode($homestay, JSON_UNESCAPED_UNICODE)?>;
	var kv_scenic = <?php echo json_encode($scenic, JSON_UNESCAPED_UNICODE)?>;
	var kv_topic = <?php echo json_encode($topic, JSON_UNESCAPED_UNICODE)?>;
	var kv_travel_note = <?php echo json_encode($travel_note, JSON_UNESCAPED_UNICODE)?>;
	var kv_area_list = <?= json_encode($area_list, JSON_UNESCAPED_UNICODE) ?>;
	var kv_location_list = <?= json_encode($location_list, JSON_UNESCAPED_UNICODE) ?>;

	$(function(){
		/*	所有動作運行原則，先上圖、再上影，避免user等待時間過長	*/
		var content = '<?php echo $default_kv['cc_content']?>';
		if ('' == content) {
			show_kv_img(default_kv_bg);
		}
		else {
			<?php if ('1' == $default_kv['cc_content_type']) { // img?>
			show_kv_img('<?php echo $default_kv['cc_content']?>');
			<?php } else if ('2' == $default_kv['cc_content_type']) { // mp4?>
			show_kv_vdo('<?php echo $default_kv['cc_content']?>');
			<?php } else if ('3' == $default_kv['cc_content_type']) { // youtube?>
			show_kv_vdo_yt('<?php echo $default_kv['cc_content']?>');
			<?php }?>
			$('#kv_title').html('<?php echo $default_kv['cc_title']?>');
		}
		change_kv_type('<?= substr($default_kv["cc_content_code"], 3) ?>');
		$('#btn_kv_search').click(function() {
			var sel_val = $('#kv_area').val();
			if(sel_val == '') {
				alertmsg('請選擇要查詢之地區');
			} else {
				var url = '';
				sel_val = encodeURIComponent(sel_val);
				//console.log(last_kv_type);
				if('scenic' == last_kv_type || 'food' == last_kv_type) {
					url = '/location/?f=' + last_kv_type + '&areas=' + sel_val;
				} else if('homestay' == last_kv_type) {
					url = '/booking/' + sel_val + '/';
				} else if('travel_note' == last_kv_type) {
					url = '/trip/?area_id=' + sel_val;
				}
				window.open(url);
				console.log(url);
			}
		});
	});
	/** key vision end **/


	/** 行銷banner start **/
	$(function(){
		var mySwiper1 = new Swiper ('.slider1.swiper-container', {
			loop: true,
		    nextButton: '.slider1.swiper-container .swiper-button-next',
		    prevButton: '.slider1.swiper-container .swiper-button-prev',
		    autoplay: 5000,
		    autoplayDisableOnInteraction: true
		});
	});
	/** 行銷banner end **/

	// 公告
	$(function(){
		<?php
			if(!isset($_SESSION['tripitta_home_notice'])) $_SESSION['tripitta_home_notice'] = null;
			if ('2016-02-15 00:00:00' > date('Y-m-d H:i:s')) {
				if ($_SESSION['tripitta_home_notice'] != '1') $_SESSION['tripitta_home_notice'] = null;
				if (empty($_SESSION['tripitta_home_notice'])) {
					$_SESSION['tripitta_home_notice'] = '1';
			?>
					var noticeStr = '「農曆春節假期2016/02/06 ~ 2016/02/14 ，共9天，客服時間修改為每日的 10:00~18:00 ， 02/15恢復正常上班時間，造成不便敬請見諒!」';
					alert(noticeStr);
			<?php
				}
			}
		?>
	});

	function goMoreFood() {
		window.open('/location/food/');
	}
	function goMorePlan() {
		window.open('/trip/');
	}
	function goSuprise() {
		window.open('<?php echo $magical_egg['cc_link_url']?>');
	}


	/** 推薦旅宿 **/
	var homestay_location = '<?php echo $recommend_homestay_list[0]['area_code']?>';
	var homestay_id = '<?php echo $recommend_homestay_list[0]['hs_id']?>';
	function showRecommendHomestay(ccPk, ccContent, hsId, areaCode, title, area, viewCount, favorite) {
		homestay_id = hsId;
		homestay_location = areaCode;
		$('#recommendHomestayTitle').text(title);
		$('#recommendHomestayArea').text(area);
		$('#recommendHomestayViewCount').text(numberFormat(viewCount));
		$('#recommendHomestayFavorite').text(numberFormat(favorite));
//		$('#recommendHomestayBigPic').prop('src', '<?php echo get_config_image_url()?>recommend_homestay/' + ccPk + '/' + ccContent +  '.jpg');
        bg_img = '/web/img/no-pic.jpg';
        if (ccContent) bg_img = '<?php echo get_config_image_url()?>recommend_homestay/' + ccPk + '/' + ccContent +  '.jpg';
		$('#recommendHomestayBigPic').css('background-image','url(' + bg_img + '), url("/web/img/no-pic.jpg")');
	}
	function goRecommendHomestay() {
		window.open('/booking/' + homestay_location + '/' + homestay_id + '/');
	}
	var mySwiper2 = null;
	function showSlider2() {
		// 200為圖片寬度
	    var count_recommend_homestay = <?php echo count($recommend_homestay_list)?>;
	    var slidesSpace = 15;
	    var calWidth = count_recommend_homestay * (200 + slidesSpace);

	    var slider2Width = $('div.slider2').width(); // 大圖寬度
	    var slidesWidth = slider2Width;
	    if (slidesWidth > calWidth) slidesWidth = calWidth;
	    var slidesCount = (slidesWidth / (200 + slidesSpace));

	    $('.thumbMenu.swiper-container').css('width', slidesWidth);

	    // 如果頁面上的圖片不超過總寬度，則不顯示左右按鈕
	    mySwiper2 = null;
	    if (slidesCount == count_recommend_homestay) {
		    $('.thumbMenu.swiper-container .img-arrow-right').hide();
		    $('.thumbMenu.swiper-container .img-arrow-left').hide();

			mySwiper2 = new Swiper ('.thumbMenu.swiper-container', {
				slidesPerView: slidesCount,
				spaceBetween: slidesSpace,
				width : slidesWidth
			});
	    }
	    else {
	    	$('.thumbMenu.swiper-container .img-arrow-right').show();
		    $('.thumbMenu.swiper-container .img-arrow-left').show();

		    mySwiper2 = new Swiper ('.thumbMenu.swiper-container', {
				loop: true,
				slidesPerView: slidesCount,
				spaceBetween: slidesSpace,
				width : slidesWidth,
			    nextButton: '.thumbMenu.swiper-container .img-arrow-right',
			    prevButton: '.thumbMenu.swiper-container .img-arrow-left'
			});
	    }
	}
	$(function(){
		showSlider2();
	});
	/** 推薦旅宿 end **/


	/** 圖片預載 **/
	function preload(arrayOfImages) {
        $(arrayOfImages).each(function(){
            $('<img/>')[0].src = this;
        });
    }
	/** 圖片預載 end **/


	function numberFormat(number, c, d, t) {
    	var n = number, c = isNaN(c = Math.abs(c)) ? 0 : c, d = d == undefined ? "," : d, t = t == undefined ? "." : t, s = n < 0 ? "-" : "", i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
    	return s + (j ? i.substr(0, j) + d : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? t + Math.abs(n - i).toFixed(c).slice(2) : "");
    };


    /** 地區 **/
    function showAreaPic(areaId) {
//         $('#areaBigPic').prop('src', 'img/area/area_info_kv_' + areaId + '.jpg');
        $('#areaBigPic').css('background-image','url("/web/img/area/area_info_kv_' + areaId + '.jpg"), url("/web/img/no-pic.jpg")');
    }
    function goArea(areaCode) {
        window.open('/area/' + areaCode + '/');
    }
    $(function(){
        // 預載地區大圖
        preload(['/web/img/area/area_info_kv_1.jpg'
                 , '/web/img/area/area_info_kv_7.jpg'
                 , '/web/img/area/area_info_kv_9.jpg'
                 , '/web/img/area/area_info_kv_11.jpg'
                 , '/web/img/area/area_info_kv_13.jpg'
                 , '/web/img/area/area_info_kv_16.jpg'
                 ]);

//         alert($('div.food').scrollTop());
        $(window).scroll(function(){
        	showNavPoints();
//         	console.log($(window).scrollTop());
        });
    });
    /** 地區 end **/


    /** nav_point **/
    function showNavPoints() {
    	if ($('div.slider2').width() > 1200) {
    		if ($(window).scrollTop() >= $('div.food').position().top) $('#nav_points').show();
    		else $('#nav_points').hide();
        }
        else {
        	$('#nav_points').hide();
        }
    }
    /** nav_point end **/


    $(window).resize(function() {
		setTimeout(showSlider2, 100);
		showNavPoints();
	});


	$(function(){
		// 檢查為裝置手機且寬度小於640，將logo移至kv圖的一半高度
//		if ('phone' == '<?php echo $deviceType?>' && $('#vdo').width() < 640) {
// 			var initTop = $('#vdo').height() / 2;
//             $(document).scrollTop(initTop);
// 		    $('#logo').css('top', (initTop + 50));
// 		}
	});
	</script>

</head>
<body>

	<div class="index-topHeader">
		<img id="logo" src="/web/img/logo.jpg" class="tripittaLogo">
		<div class="volume">
			<i class="fa fa-volume-down"></i>
		</div>
		<?php if ('1' == $default_kv['cc_content_type']) { // img?>
        <img src="<?php echo get_config_image_server(), $default_kv['cc_content']?>" style="display: none;">
        <?php }?>

		<!-- key vision 圖片或影片切換 -->
		<div id="vdo" class="top-bg">
		</div>


		<div class="middleWrap">
			<hgroup class="slogan">
				<h3>WELCOME TO</h3>
				<h1>TAIWAN</h1>
				<h2 id="kv_title" onclick="goTitleLink()" style="cursor: crosshair;">你不快看看 現在有什麼好玩的嗎?</h2>
			</hgroup>
			<nav class="menuBar">
				<ul>
					<li id="food" onclick="change_kv_type('food')" style="color:yellow;">美食</li>
					<li id="scenic" onclick="change_kv_type('scenic')">景點</li>
					<li id="travel_note" onclick="change_kv_type('travel_note')">遊記</li>
					<li id="homestay" onclick="change_kv_type('homestay')">住宿</li>
					<!--<li id="topic" onclick="change_kv_type('topic')">主題</li> -->
				</ul>
				<div class="searchGroup">
					<div class="txt">請選擇地區</div>
					<div class="selectWrap">
						<select class="sel" id="kv_area">

						</select>
						<i class="fa fa-angle-down"></i>
					</div>
					<button id="btn_kv_search" type="button">搜尋</button>
				</div>

				<p>Leading you all ways</p>
			</nav>
			<div class="angleDownBtn" onclick="javascript:scrollToConvas('.head');">
				<i class="fa fa-angle-down"></i>
			</div>
		</div>
	</div>
	<header><?php include __DIR__ . '/pages/common/header.php';?></header>
	<div class="index-container">
		<nav id="nav_points" class="points" style="display: none;">
			<ul>
				<li onclick="javascript:scrollToConvas('.food');">
					<span class="exFood">EXPLORE FOOD</span>
					<span class="point"></span>
				</li>
				<li onclick="javascript:scrollToConvas('.slider2');">
					<span class="lovebb">LOVELY B&B</span>
					<span class="point"></span>
				</li>
				<li onclick="javascript:scrollToConvas('.findTrip');">
					<span class="find">FIND TRIPS</span>
					<span class="point"></span>
				</li>
				<!--
				<li onclick="javascript:scrollToConvas('.smartGuide');">
					<span class="flat">FLATTERING</span>
					<span class="point"></span>
				</li>
				-->
				<li onclick="javascript:scrollToConvas('.suprise');">
					<span class="supe">SURPRISE</span>
					<span class="point"></span>
				</li>
				<li onclick="javascript:scrollToConvas('.takeLook');">
					<span class="taLook">TAKE A LOOK</span>
					<span class="point"></span>
				</li>
			</ul>
		</nav>

		<!-- 行銷banner -->
		<div class="slider1 swiper-container">
			<div class="sliderBtn">
				<div class="left swiper-button-prev" style="background-image:none;">
					<i class="fa fa-angle-left"></i>
				</div>
				<div class="right swiper-button-next" style="background-image:none;">
					<i class="fa fa-angle-right"></i>
				</div>
			</div>
			<div class="swiper-wrapper">
<?php
foreach ($sell_banner_list as $sell_banner) {
    $img = get_config_image_server() . $sell_banner['cc_content'];
?>
                <div class="swiper-slide">
                    <a href="<?php echo $sell_banner['cc_link_url']?>" class="slider1-bg"  target="_blank" style="width: 100%;height: 640px;display: block;background-image: url(<?php echo $img?>);background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;"></a>
                </div>
<?php
}
?>
            </div>
		</div>
		<!-- 行銷banner end -->

		<!-- explore food -->
		<div class="food">
			<div class="titleGroup">
				<h4 class="title">
					<p>EXPLORE FOOD</p>
					<i class="img-more-black" onclick="goMoreFood()"></i>
				</h4>
				<h5>來台灣，怎能錯過這麼多好吃的美食呢！</h5>
			</div>
			<div class="foodWrap">
				<div class="left">
					<section class="leftUp">
						<a href="<?php echo $food_1_link?>" class="item1" target="_blank">
							<div class="effect fs" onmouseover="$(this).removeClass('fs')">
								<i class="img-food-media"></i>
								<p><?php echo $excellent_food_list[0]['cm_type']?></p>
							</div>
							<img src="<?php echo $food_1_photo?>" alt="" onerror="javascript:this.src='/web/img/no-pic.jpg';">
							<div class="detail">
								<h4><?php echo $food_1_title?></h4>
							</div>
						</a>
					</section>
					<section class="leftDown">
						<a href="<?php echo $food_2_link?>" class="item2" target="_blank">
							<div class="effect">
								<i class="img-food-media"></i>
								<p><?php echo $excellent_food_list[1]['cm_type']?></p>
							</div>
							<img src="<?php echo $food_2_photo?>" alt="" onerror="javascript:this.src='/web/img/no-pic.jpg';">
							<div class="detail">
								<h4><?php echo $food_2_title?></h4>
							</div>
						</a>
						<a href="<?php echo $food_3_link?>" class="item3" target="_blank">
							<div class="effect">
								<i class="img-food-media"></i>
								<p><?php echo $excellent_food_list[2]['cm_type']?></p>
							</div>
							<img src="<?php echo $food_3_photo?>" alt="" onerror="javascript:this.src='/web/img/no-pic.jpg';">
							<div class="detail">
								<h4><?php echo $food_3_title?></h4>
							</div>
						</a>
					</section>
				</div>
				<div class="right">
					<a href="<?php echo $food_4_link?>" class="item4" target="_blank">
						<div class="effect">
							<i class="img-food-media"></i>
							<p><?php echo $excellent_food_list[3]['cm_type']?></p>
						</div>
						<img src="<?php echo $food_4_photo?>" alt="" onerror="javascript:this.src='/web/img/no-pic.jpg';">
						<div class="detail">
							<h4><?php echo $food_4_title?></h4>
						</div>
					</a>
					<a href="<?php echo $food_5_link?>" class="item5" target="_blank">
						<div class="effect">
							<i class="img-food-media"></i>
							<p><?php echo $excellent_food_list[4]['cm_type']?></p>
						</div>
						<img src="<?php echo $food_5_photo?>" alt="" onerror="javascript:this.src='/web/img/no-pic.jpg';">
						<div class="detail">
							<h4><?php echo $food_5_title?></h4>
						</div>
					</a>
					<a href="<?php echo $food_6_link?>" class="item6" target="_blank">
						<div class="effect">
							<i class="img-food-media"></i>
							<p><?php echo $excellent_food_list[5]['cm_type']?></p>
						</div>
						<img src="<?php echo $food_6_photo?>" alt="" onerror="javascript:this.src='/web/img/no-pic.jpg';">
						<div class="detail">
							<h4><?php echo $food_6_title?></h4>
						</div>
					</a>
				</div>
			</div>
		</div>

		<!-- 推薦旅宿 -->
		<div class="slider2">
			<div class="titleGroup">
				<h4 class="title">
					<p>LOVELY B&B</p>
					<i class="img-more-white" onclick="goRecommendHomestay()"></i>
				</h4>
				<h5 id="recommendHomestayTitle"><?php echo $recommend_homestay_first_title?></h5>
				<div class="indicator">
					<p class="location">
						<i class="fa fa-map-marker"></i>
						<span id="recommendHomestayArea"><?php echo $recommend_homestay_first_area?></span>
					</p>
					<p class="favorite">
						<i class="fa fa-heart"></i>
						<span id="recommendHomestayFavorite"><?php echo $recommend_homestay_first_favorite?></span>
					</p>
					<p class="viewCount">
						<i class="fa fa-eye"></i>
						<span id="recommendHomestayViewCount"><?php echo $recommend_homestay_first_view_count?></span>
					</p>
				</div>
			</div>
			<div class="thumbMenu swiper-container">
				<i class="img-arrow-left"></i>
				<i class="img-arrow-right"></i>

				<div class="swiper-wrapper">
<?php
foreach ($recommend_homestay_list as $recommend_homestay) {
    $photo = get_config_image_url() . 'recommend_homestay/' . $recommend_homestay['cc_pk'] . '/' . $recommend_homestay['cc_content'] . '.jpg';
    $cc_pk = $recommend_homestay['cc_pk'];
    $cc_content = $recommend_homestay['cc_content'];
    $hs_id = $recommend_homestay['hs_id'];
    $area_code = $recommend_homestay['area_code'];
    $title = $recommend_homestay['cc_title'];
    if (empty($title)) $title = $recommend_homestay['hs_name'];
    $area = $recommend_homestay['area_name'] . '-' . $recommend_homestay['small_area_name'];
    $view_count = 0;
    $favorite = 0;
    if (!empty($recommend_homestay['hsc_cnt_view_total'])) $view_count = $recommend_homestay['hsc_cnt_view_total'];
    if (!empty($recommend_homestay['hs_favorite'])) $favorite = $recommend_homestay['hs_favorite'];
//     printmsg($photo);
?>
                <div class="swiper-slide">
				<a onclick="showRecommendHomestay('<?php echo $cc_pk?>','<?php echo $cc_content?>','<?php echo $hs_id?>','<?php echo $area_code?>','<?php echo $title?>','<?php echo $area?>','<?php echo $view_count?>','<?php echo $favorite?>')">
					<img src="<?php echo $photo?>" width="185" height="114" onerror="javascript:this.src='/web/img/no-pic.jpg';">
					<div class="tripAdvisor"><?php echo $recommend_homestay['hs_name']?></div>
				</a>
				</div>
<?php
}
?>
                </div>
			</div>
			<a class="slider2-bg" onclick="goRecommendHomestay()">
			    <div id="recommendHomestayBigPic" style="background-image: url('<?php echo $recommend_homestay_first_pic?>'), url('/web/img/no-pic.jpg');"></div>
			</a>
		</div>
		<!-- 推薦旅宿 end -->

		<div class="findTrip">
			<div class="wrapper">
				<div class="titleGroup">
					<h4 class="title">
						<p>FIND TRIPS</p>
						<i class="img-more-black" onclick="goMorePlan()"></i>
					</h4>
					<h5>Tripitta 推薦一些新奇有趣的玩法給你</h5>
				</div>
				<div class="tripGroup">
					<a href="<?php echo $plan_1_link?>" class="item1" target="_blank">
						<div class="picCover" <?php if(!empty($plan_1_photo)) { ?>style="background: url(<?php echo $plan_1_photo?>) 50% 50%/cover no-repeat"<?php } ?>>
						</div>
						<div class="detail">
							<h4><?php echo $plan_1_title?></h4>
							<h5>
								<!--
								<p class="location">
									<i class="fa fa-map-marker"></i>
									<span><?php echo $plan_1_location?></span>
								</p>
								-->
								<p class="favorite">
									<i class="fa fa-heart"></i>
									<span><?php echo $plan_1_favorite?></span>
								</p>
								<p class="viewCount">
									<i class="fa fa-eye"></i>
									<span><?php echo $plan_1_view_count?></span>
								</p>
							</h5>
						</div>
					</a>
					<a href="<?php echo $plan_2_link?>" class="item2" target="_blank">
						<div class="picCover" <?php if(!empty($plan_2_photo)) { ?>style="background: url(<?php echo $plan_2_photo?>) 50% 50%/cover no-repeat"<?php } ?>>
						</div>
						<div class="detail">
							<h4><?php echo $plan_2_title?></h4>
							<h5>
								<!--
								<p class="location">
									<i class="fa fa-map-marker"></i>
									<span><?php echo $plan_2_location?></span>
								</p>
								-->
								<p class="favorite">
									<i class="fa fa-heart"></i>
									<span><?php echo $plan_2_favorite?></span>
								</p>
								<p class="viewCount">
									<i class="fa fa-eye"></i>
									<span><?php echo $plan_2_view_count?></span>
								</p>
							</h5>
						</div>
					</a>
					<a href="<?php echo $plan_3_link?>" class="item3" target="_blank">
						<div class="picCover" <?php if(!empty($plan_3_photo)) { ?>style="background: url(<?php echo $plan_3_photo?>) 50% 50%/cover no-repeat"<?php } ?>>

						</div>
						<div class="detail">
							<h4><?php echo $plan_3_title?></h4>
							<h5>
								<!--
								<p class="location">
									<i class="fa fa-map-marker"></i>
									<span><?php echo $plan_3_location?></span>
								</p>
								-->
								<p class="favorite">
									<i class="fa fa-heart"></i>
									<span><?php echo $plan_3_favorite?></span>
								</p>
								<p class="viewCount">
									<i class="fa fa-eye"></i>
									<span><?php echo $plan_3_view_count?></span>
								</p>
							</h5>
						</div>
					</a>
					<a href="<?php echo $plan_4_link?>" class="item4" target="_blank">
						<div class="picCover" <?php if(!empty($plan_4_photo)) { ?>style="background: url(<?php echo $plan_4_photo?>) 50% 50%/cover no-repeat"<?php } ?>>
						</div>
						<div class="detail">
							<h4><?php echo $plan_4_title?></h4>
							<h5>
								<!--
								<p class="location">
									<i class="fa fa-map-marker"></i>
									<span><?php echo $plan_4_location?></span>
								</p>
								-->
								<p class="favorite">
									<i class="fa fa-heart"></i>
									<span><?php echo $plan_4_favorite?></span>
								</p>
								<p class="viewCount">
									<i class="fa fa-eye"></i>
									<span><?php echo $plan_4_view_count?></span>
								</p>
							</h5>
						</div>
					</a>
					<a href="<?php echo $plan_5_link?>" class="item5" target="_blank">
						<div class="picCover" <?php if(!empty($plan_5_photo)) { ?>style="background: url(<?php echo $plan_5_photo?>) 50% 50%/cover no-repeat"<?php } ?>>
						</div>
						<div class="detail">
							<h4><?php echo $plan_5_title?></h4>
							<h5>
								<!--
								<p class="location">
									<i class="fa fa-map-marker"></i>
									<span><?php echo $plan_5_location?></span>
								</p>
								-->
								<p class="favorite">
									<i class="fa fa-heart"></i>
									<span><?php echo $plan_5_favorite?></span>
								</p>
								<p class="viewCount">
									<i class="fa fa-eye"></i>
									<span><?php echo $plan_5_view_count?></span>
								</p>
							</h5>
						</div>
					</a>
					<a href="<?php echo $plan_6_link?>" class="item6" target="_blank">
						<div class="picCover" <?php if(!empty($plan_6_photo)) { ?>style="background: url(<?php echo $plan_6_photo?>) 50% 50%/cover no-repeat"<?php } ?>>
						</div>
						<div class="detail">
							<h4><?php echo $plan_6_title?></h4>
							<h5>
								<!--
								<p class="location">
									<i class="fa fa-map-marker"></i>
									<span><?php echo $plan_6_location?></span>
								</p>
								-->
								<p class="favorite">
									<i class="fa fa-heart"></i>
									<span><?php echo $plan_6_favorite?></span>
								</p>
								<p class="viewCount">
									<i class="fa fa-eye"></i>
									<span><?php echo $plan_6_view_count?></span>
								</p>
							</h5>
						</div>
					</a>
				</div>
			</div>
		</div>
		<!-- find trip -->
		<!--
		<div class="smartGuide">
			<div class="titleGroup">
				<h4 class="title">
					<p>SMART GUIDE</p>
					<i class="img-more-black"></i>
				</h4>
				<h5>看看其他玩家如何玩？</h5>
			</div>
			<div class="guideGroup">
		-->
<?php
// foreach ($flattering_list as $flattering) {
//     $flattering_pic = get_config_image_server() . $flattering['cc_content'];
?>
<!--
				<a href="javascript:window.open('/trip/?days=<?php echo $flattering['cc_link_url']?>')" class="item1">
					<section class="imgEffect">
						<div class="imgRotate" style="background-image: url('<?php echo $flattering_pic?>'), url('/web/img/no-pic.jpg');"></div>
						<div class="detail">
							<i class="img-dayCircle">
								<span class="days"><?php echo $flattering['cc_link_url']?></span>
								<span>DAY</span>
							</i>
							<h4><?php echo $flattering['cc_title']?></h4>
							<h5>
								<p class="location">
									<i class="fa fa-map-marker"></i>
									<span>大台北-新北市</span>
								</p>
								<p class="favorite">
									<i class="fa fa-heart"></i>
									<span>1,298</span>
								</p>
								<p class="viewCount">
									<i class="fa fa-eye"></i>
									<span>1,298</span>
								</p>
							</h5>
						</div>
					</section>
					<section class="imgEffect">
						<img src="/web/img/pic7.jpg" alt="" onerror="javascript:this.src='/web/img/no-pic.jpg';">
					</section>
					<section class="imgEffect">
						<img src="/web/img/pic7.jpg" alt="" onerror="javascript:this.src='/web/img/no-pic.jpg';">
					</section>
				</a>
-->
<?php
//}
?>
		<!--
			</div>
		</div>
		-->
		<!-- SMART GUIDE -->

		<div class="suprise">
			<div class="wrapper">
				<div class="titleGroup">
					<h4 class="title">SURPRISE</h4>
					<h5>Tripitta 扭蛋</h5>
				</div>
				<div class="egg" onmouseover="$(this).addClass('shake')" onmouseout="$(this).removeClass('shake')" onclick="goSuprise()">
					<i class="img-egg-defult"></i>
				</div>
				<h5>不知道要去哪？ 轉一下Tripitta扭蛋吧！</h5>
			</div>
		</div>
		<!-- suprise -->

		<?php /** 以下內容目前為寫死在 html 中，以後可能需要由後台上架 **/?>
		<div class="takeLook">
			<div id="areaBigPic" class="look-bg" style="background-image: url('/web/img/area/area_info_kv_1.jpg'), url('/web/img/no-pic.jpg');"></div>
			<div class="wrapper">
				<div class="titleGroup">
					<h4 class="title">TAKE A LOOK</h4>
					<h5>你想去哪裡?</h5>
				</div>
				<div class="areaList">
					<a href="/area/taipei/" class="area" onmouseover="showAreaPic('16')" >
						<p>台北</p>
						<p>TAIPEI</p>
					</a>
					<a href="/area/hualien/" class="area" onmouseover="showAreaPic('7')" >
						<p>花蓮</p>
						<p>HUALIEN</p>
					</a>
					<a href="/area/kenting/" class="area" onmouseover="showAreaPic('1')" >
						<p>墾丁</p>
						<p>KENTING</p>
					</a>
					<a href="/area/kaohsiung/" class="area" onmouseover="showAreaPic('13')" >
						<p>高雄</p>
						<p>KAOHSIUNG</p>
					</a>
					<a href="/area/tainan/" class="area" onmouseover="showAreaPic('9')" >
						<p>台南</p>
						<p>TAINAN</p>
					</a>
					<a href="/area/taichung/" class="area" onmouseover="showAreaPic('11')" >
						<p>台中</p>
						<p>TAICHUNG</p>
					</a>
				</div>
			</div>
		</div>
		<!-- take a look -->

		<div class="joinUs">
			<div class="wrapper">
				<p>我們還有很多驚喜等著您！</p>
				<i class="img-joinus" onclick="$('div.popupRegister').show();$('.overlay').show();"></i>
			</div>
		</div>
		<!-- join us -->
	</div>

	<footer><?php include __DIR__ . '/pages/common/footer.php';?></footer>
	<?php include __DIR__ . '/pages/common/ga.php';?>
</body>
</html>
<?php /*?>
<link href="https://vjs.zencdn.net/5.2.4/video-js.css" rel="stylesheet">
<?php */
printmsg('秏費時間：' . (microtime(true) - $t1));
?>

