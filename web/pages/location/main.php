<?
/**
 * 說明：觀光指南列表
 * 作者：cheans <cheans.huang@fullerton.com.tw>
 * 日期：2015年12月4日
 * 備註：
 * 頁面接收參數
 * f : Content Folder array('food', 'scenic', 'homestay', 'event', 'gift')
 * sf : Content Sub Folder 目前只有美食有子類別 array('restaurant', 'snack')
 * areas : 區域 array('north', 'east', 'south', 'west', 'islands')
 * tages : tag 已放棄不用
 * pageno : 頁面呈現資料頁數
 *
 * 參考資料表
 * hf_taiwan_content
 * hf_taiwan_content_tag
 * hf_taiwan_content_folder
 * hf_tag
 * hf_folder
 * hf_home_stay
 * hf_home_stay_tag
 * hf_city_town
 *
 * 1. 為了效能關係先捉出來要顯示之Type, ID後再依Type, ID取得Content資料
 *
 * 2015/12/17 : cheans
 * 1. 因Vincent要求每個選擇都要切換所以放棄ajax呼叫，改採直接換頁方式處理
 * 2. 隱藏Tag
 *
 * 2016/01/13 : steak
 * 依照加權排列
 */
require_once __DIR__ . '/../../config.php';
?>
<!Doctype html>
<html lang="zh-Hant">
<head>
	<? include __DIR__ . "/../common/head.php"; ?>
	<link rel="stylesheet" type="text/css" href="/web/pages/location/css/frame.css">
	<link rel="stylesheet" type="text/css" href="/web/pages/location/css/index.css">
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<style>
	.kv{
	width: 100%;
    height: 100%;
    background: url(/web/img/location/location_banner_food.jpg) 50% 50%/cover no-repeat;
	}
	</style>
	<title>觀光指南 - Tripitta 旅必達</title>
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
	<script src="/web/js/jquery.twbsPagination.js" type="text/javascript"></script>
</head>
<?
$folder_list = array('food', 'scenic', 'homestay', 'event', 'gift');
$sf_list = array('restaurant', 'snack');
$area_list = array('north', 'east', 'south', 'west', 'islands');


$f = get_val('f');
$sf = get_val('sf');
$areas = get_val('areas');
$tags = get_val('tags');
$pageno = get_val('pageno');
$tripitta_web_service = new tripitta_web_service();
$login_user_data = $tripitta_web_service->check_login();
$is_login = false;
$serialId = 0;
if(!empty($login_user_data)) {
    $is_login = true;
    $serialId = $login_user_data['serialId'];
}
$tag_list = $tripitta_web_service->find_valid_tag_by_parent_id(get_config_current_lang(), 0);


if(!in_array($f, $folder_list)) {
    $f = '';
}
if(!in_array($sf, $sf_list)) {
    $sf = '';
}
if(!empty($areas) && !in_array($areas, $area_list)) {
    $areas = '';
}
if(!empty($tags) && !empty($tags)) {
    $selected_tags = preg_split('/,/', $tags);
}
$city_ids = array();
if(!empty($areas)) {
    $area_list = preg_split('/,/', $areas);
    foreach($area_list as $t) {
        if('north' == strtolower($t)) {
            $city_ids = array_merge(array(1,2,3,4,5,6,7), $city_ids);
        } else if('east' == strtolower($t)) {
            $city_ids = array_merge(array(17,18,19), $city_ids);
        } else if('south' == strtolower($t)) {
            $city_ids = array_merge(array(10,11,12,14,15,16), $city_ids);
        } else if('west' == strtolower($t)) {
            $city_ids = array_merge(array(8,9,13,21), $city_ids);
        } else if('islands' == strtolower($t)) {
            $city_ids = array_merge(array(22,20), $city_ids);
        }
    }
}
$pageSize = 9;
$pageno = get_val('pageno');
if(empty($pageno)) {
    $pageno = 1;
}

$cond = [];
if(!empty($f)) {
    $cond["folder"] = $f;
}
if(!empty($sf)) {
    $cond["sub_folder"] = $sf;
}
if(!empty($selected_tags)) {
    $cond["tags"] = $selected_tags;
}
if(!empty($city_ids)) {
    $cond["citys"] = $city_ids;
}


$tripitta_web_service = new tripitta_web_service();
$tripitta_homestay_service = new tripitta_homestay_service();
$login_user_data = $tripitta_web_service->check_login();
$favorite_list = array();
if(!empty($login_user_data)) {
    $user_favorite_type_ids = $tripitta_web_service->get_user_favorite_type_ids($f);
    $favorite_list = $tripitta_web_service->find_user_favorite_by_user_id_and_ref_type_ids($login_user_data["serialId"], $user_favorite_type_ids);
}
$total_items = $tripitta_web_service->count_valid_taiwan_content_for_location_home($cond);
$total_page = getTotalPage($total_items, $pageSize);
if($pageno > $total_page && $total_page > 0){
    $pageno = $total_page;
}
if($total_page <= 0) {
    $total_page = 1;
}
$cond["limit"] = $pageSize;
$cond["offset"] = ($pageno - 1) * $pageSize;
$content_list = $tripitta_web_service->find_valid_type_and_ids_for_location_home($cond);
$content_detail_list = $tripitta_web_service->find_valid_taiwan_content_for_location_home($content_list);
//printmsg($content_detail_list);
$image_server_url = get_config_image_server();


// 取得顯示幣別、匯率
$display_currency_id = $tripitta_web_service->get_display_currency();
$currency_code = NULL;
$exchange_rate = 1;
$point_length = 1;
if (1 == $display_currency_id) {
    $currency_code = 'NTD';
    $exchange_rate = 1;
}
else {
    $exchange = $tripitta_homestay_service->get_exchange_by_currency_id($display_currency_id);
    $currency_code = $exchange['cr_code'];
    $exchange_rate = $exchange['erd_rate'];
    $point_length = $exchange["cr_point_length"];
}

$homestay_ids = [];
foreach($content_list as $content_row) {
    if($content_row["folder"] == 10) {
        $homestay_ids[] = $content_row["id"];
    }
}
$homestay_min_price_list = [];
if(!empty($homestay_ids)) {
    $homestay_min_price_list = $tripitta_web_service->find_homestay_min_price_by_homestay_ids($homestay_ids);
}


$kv_background = '/web/img/location/location_banner_food.jpg';
if(!empty($f)) {
    $kv_background = '/web/img/location/location_banner_' . $f . '.jpg';
}
?>
<body class="main">
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<article class="top-container">
		<div class="container">
			<div class="banner">
				<div id="kv_img" class="kv" style="background-image: url(<?= $kv_background ?>);"></div>
			</div>
			<!-- //banner -->
			<div class="menu">
				<ul class="main-list">
					<li id="m01" data-folder="food" class="<? if($f == 'food') { echo "selected"; } ?>">
						<a href="javascript:void(0)">
							<span>FOOD</span>
							<span class="ch pl-20">美食
								<i class="fa fa-angle-down"></i>
							</span>
						</a>
						<ul class="sub" style="display:none">
							<li id="sf_restaurant" data-code="restaurant"class="<? if($sf == 'restaurant') { echo "selected"; } ?>">餐廳</li>
							<li id="sf_snack" data-code="snack"class="<? if($sf == 'snack') { echo "selected"; } ?>">小吃</li>
						</ul>
					</li>
					<li id="m02" data-folder="scenic" class="<? if($f == 'scenic') { echo "selected"; } ?>">
						<a href="javascript:void(0)">
							<span>LOCATION</span>
							<span class="ch">景點</span>
						</a>
					</li>
					<li id="m03" data-folder="homestay" class="<? if($f == 'homestay') { echo "selected"; } ?>">
						<a href="javascript:void(0)">
							<span>BOOKING</span>
							<span class="ch">住宿</span>
						</a>
					</li>
					<li id="m04" data-folder="event" class="<? if($f == 'event') { echo "selected"; } ?>">
						<a href="javascript:void(0)">
							<span>ACTIVITY</span>
							<span class="ch">活動
								<i class="fa fa-angle-down" style="display:none"></i>
							</span>
						</a>
						<ul class="sub" style="display:none">
							<li>節慶</li>
							<li>體驗</li>
						</ul>
					</li>
					<li id="m05" data-folder="gift" class="<? if($f == 'gift') { echo "selected"; } ?>">
						<a href="javascript:void(0)">
							<span>GIFT</span>
							<span class="ch">伴手禮</span>
						</a>
					</li>
				</ul>
				<!-- //main-list -->
				<div class="area"<? if(!empty($f)) { echo 'style="display:block"'; } ?>>
					<ul class="area-list">
						<li class="a01 <? if($areas == 'north') { echo "selected"; } ?>" data-area="north">
							<a href="javascript:void(0)">基隆 台北 桃園 新竹 苗栗</a>
						</li>
						<li class="a02 <? if($areas == 'east') { echo "selected"; } ?>" data-area="east">
							<a href="javascript:void(0)">宜蘭 花蓮 台東</a>
						</li>
						<li class="a03 <? if($areas == 'south') { echo "selected"; } ?>" data-area="south">
							<a href="javascript:void(0)">雲林 嘉義 台南 高雄 屏東</a>
						</li>
						<li class="a04 <? if($areas == 'west') { echo "selected"; } ?>" data-area="west">
							<a href="javascript:void(0)">台中 彰化 南投 澎湖</a>
						</li>
						<li class="a05 <? if($areas == 'islands') { echo "selected"; } ?>" data-area="islands">
							<a href="javascript:void(0)">金門 馬祖 綠島 蘭嶼 小琉球</a>
						</li>
					</ul>
				</div>
				<!-- //area -->
				<div class="tag" style="display:none">
					<ul class="tag-list">
						<? foreach ($tag_list as $tag_row) { ?>
						<li data-tag-id="<?= $tag_row["t_id"] ?>"><?= $tag_row["t_tag"] ?></li>
						<? } ?>
					</ul>
				</div>
				<!-- //tag -->
			</div>
			<!-- //menu -->
		</div>
		<!-- //container -->
	</article>
	<!-- //top-container -->

	<article class="contents" id="content_info">
	<!-- //top-container -->
		<div class="container">
			<div class="sort-bar">
				<div class="select">
					<select name="" id="">
						<option value="人氣指數">人氣指數</option>
					</select>
				</div>
				<span class="sort-result">共 <?= $total_items ?> 筆</span>
			</div>
			<!-- //sort-bar -->
			<ol class="item-list">
<?
foreach($content_list as $content_type_id_row) {
    $content_row = [];
    foreach($content_detail_list as $content_detail_row) {
        if($content_detail_row["folder"] == $content_type_id_row["folder"] && $content_detail_row["id"] == $content_type_id_row["id"]) {
            $content_row = $content_detail_row;
            break;
        }
    }
    if(empty($content_row)) {
        continue;
    }
    $img = '/web/img/no-pic.jpg';
    if(!empty($content_row["main_photo"])) {
        if($content_row["folder"] == 10) {
            $img = $image_server_url . '/photos/' . (is_production() ? 'travel' : 'alpha_travel') . '/home_stay/' . $content_row["id"] . '/' . $content_row['main_photo'] . "_middle.jpg";
        } else {
            $img = $image_server_url . '/photos/' . (is_production() ? 'taiwan_content' : 'taiwan_content_alpha') . '/' . $content_row["id"] . '/' . $content_row['main_photo'] . ".jpg";
        }
    }
    $min_price = 0;
    if($content_row["folder"] == 10) {
        foreach($homestay_min_price_list as $homestay_min_price_row) {
            if($homestay_min_price_row["hsrpm_home_stay_id"] == $content_row["id"]) {
                $min_price = $homestay_min_price_row["min_price"];
            }
        }
    }
    $folder = $content_row["folder"];
    $favorite_class = "fa-heart-o";
    foreach($favorite_list as $favorite_row) {
        if($folder == 10 && ($favorite_row["uf_type"] == 0 || $favorite_row["uf_type"] == $folder) && $favorite_row["uf_home_stay_id"] == $content_row["id"]) {
            $favorite_class = "fa-heart";
            break;
        }else if($folder != 10 && $favorite_row["uf_type"] == $folder && $favorite_row["uf_home_stay_id"] == $content_row["id"]) {
            $favorite_class = "fa-heart";
            break;
        }
    }
    $linkurl = '';
    if($folder == 10) {
        $linkurl = '/booking/' . $content_row["a_code"] . '/' . $content_row["id"] . '/';
    } else {
        $type_str = '';
        if(7 == $folder) {
            $type_str = 'food';
        } else if(8 == $folder) {
            $type_str = 'spot';
        } else if(82 == $folder) {
            $type_str = 'gift';
        } else if(12 == $folder || 15 == $folder) {
            $type_str = 'event';
        }
        $linkurl = '/location/' . $type_str . '/' . $content_row["id"] . '/';
    }
    $content_name = $content_row["name"];
    if(mb_strlen($content_name, 'utf-8') > 14) {
        $content_name = mb_substr($content_name, 0, 14, 'utf-8') . '...';
    }
?>
				<li>
					<span class="img-collect" data-type="<?= $content_row["folder"] ?>" data-id="<?= $content_row["id"] ?>">
						<i class="fa <?= $favorite_class ?>"  id="<?= $content_row["folder"] . "_" . $content_row["id"] ?>" ></i>
					</span>
					<a href="<?= $linkurl ?>">
						<img src="<?= $img ?>" alt="" style="width:300px; height:208px" onerror="javascript:this.src='/web/img/no-pic.jpg';">
						<h4 class="hotel-name"><?= $content_name ?></h4>
						<div class="status">
							<span>
								<i class="fa fa-map-marker"></i><?= $content_row["a_name"] ?></span>
							<span>
								<i class="fa fa-heart"></i><?= $content_row["cnt_collect"] ?></span>
							<span>
								<i class="fa fa-eye"></i><?= $content_row["cnt_view"]*$content_row["mul"] ?></span>
						</div>
						<div class="price" style="height: 24px">
							<? if($min_price != 0){ ?><span class="small"><?= $currency_code ?></span><?= number_format(getCeil($min_price/$exchange_rate, $point_length))?><? } ?>
						</div>
					</a>
						<div class="advisor" style="height: 66px">
							<? if(!empty($content_row["tari_id"])) { ?>
							<a href="javascript:open_trip_advisor_review(<?= $content_row["tari_id"] ?>)" class="tripadvisorLogo" target="_blank">
							<img src="https://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/<?= $content_row["tari_average_rating"] ?>-33123-4.gif" style="width:118px;height:20px;"/><br />
							<?= $content_row["tari_review_count"] ?>則評論
							</a>
    						<? } ?>
						</div>


				</li>
<?
}
?>

			</ol>
			<!-- //item-list -->
			<div class="text-center">
				<ul id="visible-pages-example" class="pagination"></ul>
    		</div>
			<!--//Pagination-->
		</div>
		<!-- //container -->
	</article>
	<!-- //contents -->

	<footer class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
	<input type="hidden" id="user_serial_id" value="<?php echo $serialId ?>">
	<input type="hidden" id="area_id" value="<?= get_val('area_id') ?>">
</body>

</html>

<script>
var total_items = '<?= $total_items ?>';
var is_login = <?= ($is_login) ? 1:0 ?>;
var sub_folder = '<?= $sf ?>';
$(function() {
	// 顯示美食子選單
 	$('.top-container #m01').mouseover(function(){ $('.top-container #m01 .sub').show(); })
 		.mouseout(function(){ $('.top-container #m01 .sub').hide(); });;

 	// 如果點擊美食子選單(餐廳)則停止傳遞
 	$('#sf_restaurant').click(function(event){
 		//event.stopPropagation();
 		event.stopImmediatePropagation();
 		$('#m01').addClass('selected');
 		sub_folder = $(this).attr('data-code');
 		location.href = '/location/?f=food&sf=' + sub_folder;
	});

	// 如果點擊美食子選單(小吃)則停止傳遞
 	$('#sf_snack').click(function(event){
 		//event.stopPropagation();
 		event.stopImmediatePropagation();
	 	$('#m01').addClass('selected');
 	 	sub_folder = $(this).attr('data-code');
 	 	location.href = '/location/?f=food&sf=' + sub_folder;
	});

 	// 設定選擇區域動作
 	$('.area-list li').click(function() {
 		change_area($(this).attr('data-area'));
 	});

 	// 設定選擇主選單動作
    $('.main-list li').each(function() {
		$(this).click(function (event) {
			event.stopPropagation();
			var code = $(this).attr('data-folder');
			location.href = '/location/?f=' + code;
		});
    });

 	if(total_items > 0) {
        $('#visible-pages-example').twbsPagination({
    		totalPages: <?= $total_page ?>,
    		startPage: <?= $pageno ?>,
    	    first: "第一頁",
    	    prev: "上一頁",
    	    next: "下一頁",
    	    last: "最後一頁",
    		initiateStartPageClick:false,
    		onPageClick: function (event, page) {
    		    location_query(page);
    		}
        });
 	}
    $('.contents .img-collect').each(function() {
        $(this).click(function() {
        	var ref_type = $(this).attr('data-type');
            var ref_id = $(this).attr('data-id');
			var add = $('#' + ref_type + '_' + ref_id).hasClass('fa-heart-o') ? 1 : 0;
            if(add == 1) {
            	add_favorite('#' + ref_type + '_' + ref_id, ref_type, ref_id);
            } else {
            	remove_favorite('#' + ref_type + '_' + ref_id, ref_type, ref_id);
            }
        });
    });

});
function change_area(area){
	$('.area-list li').each(function() {
		var data_area = $(this).attr('data-area');
		if(data_area == area) {
			$(this).addClass('selected')
		} else {
			$(this).removeClass('selected')
		}
	});
	location_query(1);
}

function location_query(pageno) {
	pageno = (pageno == undefined) ? 1 : pageno;
	var f = '';
	$('.main-list li[id^="m"]').each(function(){
		if($(this).hasClass('selected')){
			f = $(this).attr('data-folder');
			return false;
		}
	});
	var areas = '';
	$('.area-list li').each(function() {
		if($(this).hasClass('selected')){
			if(areas != '') areas += ',';
			areas += $(this).attr('data-area');
		}
	});
	var url = '';
	params = 'f=' + encodeURIComponent(f) + '&sf=' + encodeURIComponent(sub_folder) + '&areas=' + encodeURIComponent(areas) + '&pageno=' + parseInt(pageno);
	console.log(url);
	location.href = '/location/?' + params;
}

function add_favorite(convas, ref_type, ref_id) {
	if(!is_login) {
		show_popup_login();
		return;
	}
	var p = {};
    p.func = 'add_favorite';
    p.user_id = $('#user_serial_id').val();
    p.ref_type = ref_type;
    p.ref_id = ref_id;
    // console.log(p);
    $.post("/web/ajax/ajax.php", p, function(data) {
        console.log(data);
        if(data.code == '9999'){
            alert(data.msg);
        } else {
            // 顯示註冊完成並顯示註冊完成popup window
			alert('已加至我的收藏');
			$(convas).removeClass('fa-heart-o').addClass('fa-heart');
        }
    }, 'json').done(function() { }).fail(function() { }).always(function() { });
}

function remove_favorite(convas, ref_type, ref_id) {
	if(!is_login) {
		show_popup_login();
		return;
	}
	remove_items = [];
    remove_items.push({'type_id':ref_type,'ref_id':ref_id});

	var p = {};
    p.func = 'remove_user_favorite';
    p.user_id = $('#user_serial_id').val();
    p.items = remove_items;
    //console.log(p);
    $.post("/web/ajax/ajax.php", p, function(data) {
        console.log(data);
        if(data.code == '9999'){
            alert(data.msg);
        } else {
            // 顯示註冊完成並顯示註冊完成popup window
			alert('已從我的收藏移除');
			$(convas).removeClass('fa-heart').addClass('fa-heart-o');
        }
    }, 'json').done(function() { }).fail(function() { }).always(function() { });
}

function open_trip_advisor_review(ta_id) {
	window.open('http://www.tripadvisor.com/WidgetEmbed-cdspropertydetail?locationId=' + ta_id + '&partnerId=CB56EED944AF4459B7E92BBF9B292AC6&lang=zh_TW&allowMobile&display=true', 'trip_advisor', 'width=600, location=0, menubar=0, resizable=0, scrollbars=1, status=0, titlebar=0, toolbar=0');
}
</script>