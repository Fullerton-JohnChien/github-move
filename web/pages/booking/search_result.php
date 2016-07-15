<?
require_once __DIR__ . '/../../config.php';
?><!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<? include __DIR__ . "/../common/head.php"; ?>
	<title>旅宿預訂 - Tripitta 旅必達</title>
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<link rel="stylesheet" href="/web/css/search_pagination.css">
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css">
	<style>
	.gm-style-iw + div { display: none !important; }
	.gm-style-iw {
		width: 300px !important;
		top: 15px !important;
		left: 20px !important;
		background-color: #fff;
		box-shadow: rgba(0, 0, 0, 0.6) 0px 1px 6px;
		z-index: -1;
	}
	i.fa.fa-heart {
	    color: #ff8c7a;
	}
	</style>
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
	<link rel="stylesheet" href="/web/css/idangerous.swiper.css" />
	<script src="/web/js/idangerous.swiper-2.0.min.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
</head>
<body>
<?
$facility_row = array(1 => ' WIFI',2 => '附早餐',3 => '可帶寵物');
$cur_lang = get_config_current_lang();
$pageSize = 10;
// 置頂旅宿間數
//$num_on_top = 1;
$uuid = uniqid();
$pageno = get_val('pageno');
if($pageno < 1) $pageno = 1;
$offset = $pageSize * ($pageno - 1);

/**
 * 第一階段搜尋
 */
$areaCode = get_val('area_code');
$beginDate = get_val('beginDate');
$endDate = get_val('endDate');
$roomType = (get_val('roomType') == null) ? 2 : get_val('roomType');
$roomQuantity = (get_val('roomQuantity') == null) ? 1 : get_val('roomQuantity');

if(date('Y-m-d', strtotime($beginDate)) !== $beginDate) $beginDate = "";
if(date('Y-m-d', strtotime($endDate)) !== $endDate) $endDate = "";
if(empty($beginDate) && empty($endDate)) {
	$beginDate = Date('Y-m-d', strtotime("+ 1 days"));
	$endDate = Date('Y-m-d', strtotime("+ 2 days"));
} else if(!empty($beginDate) && empty($endDate)) {
	$endDate = Date('Y-m-d', strtotime($beginDate . "+ 1 days"));
} else if(empty($beginDate) && !empty($endDate)) {
	$beginDate = Date('Y-m-d', strtotime($endDate."- 1 days"));
}

/**
 * 第二階段搜尋
 */
// 子區 array(1, 2, 3...);
$selectSmallArea = get_val('selectSmallArea');
$smallArea = $selectSmallArea;
// 旅宿類別 array(1 ~ 6);
$selectHomeStayCategory = get_val('selectHomeStayCategory');
// 最低價
$minPrice = (get_val('minPrice')==null) ? 0 : get_val('minPrice');
// 最高價
$maxPrice = (get_val('maxPrice')==null) ? 25000 : get_val('maxPrice');
// 設施
$facility = get_val('facility');
$facilitys = $facility;
// 對象
// $people = get_val('people');
// $peoples = $people;
// 話題
$topic = get_val('topic');
$topics = $topic;
// 優惠
$preferential = get_val('preferential');
$preferentials = $preferential;
// 生態
$ecology = get_val('ecology');
$ecologys = $ecology;
// 休閒
$leisure = get_val('leisure');
$leisures = $leisure;

// 關鍵字
$keyWord = get_val('keyWord');
if(!empty($keyWord)) {
    $keyWord = trim($keyWord);
}

// 價格排序 asc, desc
$selectPriceOrder = get_val('selectPriceOrder');
// 評論排序 asc, desc
$selectCommentOrder = get_val('selectCommentOrder');

// 搜尋摸式 map or list
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'list';

$room_days = (strtotime($endDate) - strtotime($beginDate)) / 86400;

$link_home_stay_link_params = 'beginDate=' . urlencode($beginDate) . '&endDate=' . urlencode($endDate) . '&roomType=' . urlencode($roomType) . '&roomQuantity=' . urlencode($roomQuantity);
$page_params = "?area_code=" . $areaCode. "&keyWord=" . urlencode($keyWord) . "&uuid=" . $uuid . "&beginDate=" . $beginDate . "&endDate=" . $endDate . "&roomType=" . $roomType . "&roomQuantity=" . $roomQuantity;
$page_params2 = "&selectSmallArea=" . $smallArea . "&minPrice=" . $minPrice . "&maxPrice=" . $maxPrice . "&ecology=" . $ecologys . "&topic=" . $topics . "&preferential=" . $preferentials ;
$page_params3 = "&minPrice=" . $minPrice . "&maxPrice=" . $maxPrice . "&ecology=" . $ecologys . "&topic=" . $topics . "&preferential=" . $preferentials ;

// printmsg('<a href="home_stay_search_result.php?' . $page_params . '">下一頁</a>');
// $dayCount = (strtotime($endDate) - strtotime($beginDate)) / 86400;

if (empty($selectSmallArea)) $selectSmallArea = [];
if (empty($selectHomeStayCategory)) $selectHomeStayCategory = 0;
if (empty($selectPriceOrder)) $selectPriceOrder = 0;
if (empty($selectCommentOrder)) $selectCommentOrder = 0;

$db_reader_travel = Dao_loader::__get_checked_db_reader();
$tripitta_web_service = new tripitta_web_service();
$tripitta_homestay_service = new tripitta_homestay_service();
$home_stay_search_service = new Home_stay_search_service();
$promotion_service = new Home_stay_promotion_service();
$area_dao = Dao_loader::__get_area_dao();
// $area_extend_dao = Dao_loader::__get_area_extend_dao();
$room_dao = Dao_loader::__get_room_dao();
$photo_dao = Dao_loader::__get_photo_dao();
$home_stay_advertisment_dao = Dao_loader::__get_home_stay_advertisment_dao();

$login_user_data = $tripitta_web_service->check_login();
$current_channel = getCurrentChannel();

$serialId = 0;
$is_show_add_favorite  = false;
$is_login = false;
if($current_channel == 'ezding' || (is_array($current_channel) && $current_channel['c_add_favorite'] == '1')){
    $is_show_add_favorite = true;
}
if(!empty($login_user_data)) {
    $is_login = true;
    $serialId = $login_user_data['serialId'];
}

$currentChannelCode = 'tripitta';

$area_list = $tripitta_homestay_service->find_valid_area_by_category_and_parent_id('tw', 'homestay', 0);
$area_row = array();
foreach($area_list as $t) {
    if($areaCode == $t["a_code"]) {
        $area_row = $t;
    }
}
$areaId = $area_row["a_id"];
$legal_home_stay_ids = $home_stay_search_service->get_legal_home_stay_ids($currentChannelCode, $areaId);

// 設定搜尋條件
$cond = array();
$cond["area_id"] = $areaId;
$cond["keyword"] = $keyWord;
//$cond["location"] = array("latitude" => 24.692947200000000, "longitude" => 121.719545900000000, "diff" => 0.1);
if(!empty($roomType)) $cond["room_type"] = $roomType;
if(!empty($roomQuantity)) $cond["room_quantity"] = $roomQuantity;
//if(!empty($facility)) {$cond["facility_ids"] = preg_split('/,/', $facility); }
$cond["legal_ids"] = $legal_home_stay_ids;

/**
 * 第一階段搜尋，以此階段搜尋結果呈現前端子區…等相關資料
 */
// 搜尋旅宿
$first_level_home_stay_id_list = $home_stay_search_service->narrow_down_home_stay_id_for_home_stay_search_by_cond($cond, $beginDate, $endDate);
//printmsg($first_level_home_stay_id_list);
// 排除不顯示旅宿
$first_level_home_stay_id_list = $home_stay_search_service->narrow_down_channel_home_stay_id_for_web($first_level_home_stay_id_list, $currentChannelCode);
//printmsg($first_level_home_stay_id_list);
// 排除測試民宿
// 排除1648
foreach ($first_level_home_stay_id_list as $index => $hs){
	if($hs['hs_id'] == 1648) unset($first_level_home_stay_id_list[$index]);
}

$home_stay_ids = array();
foreach($first_level_home_stay_id_list as $home_stay_id_row) {
    $home_stay_ids[] = $home_stay_id_row["hs_id"];
}

$ta_info_list = $tripitta_homestay_service->find_valid_homestay_trip_advisor_info($home_stay_ids);

// 取得預設排序及預設最低價(如果沒有活動時顯示)
$result = $home_stay_search_service->sort_search_result_by_home_stay_ids_and_date_range($cur_lang, $home_stay_ids, $beginDate, $endDate, $roomQuantity);

// 將九重盯移到最前面(理查說的)
$temp = array();
foreach ($result as $index => $hs){
	if($hs['hs_id'] == 366) $temp[0] = $result[$index];
}
if(!empty($temp)) {
	foreach ($result as $index => $hs){
		if($hs['hs_id'] == 366) unset($result[$index]);
	}
	// var_dump($first_level_home_stay_id_list);
	foreach ($result as $index => $hs){
		array_push($temp, $result[$index]);
	}
	$result = $temp;
}

$home_stay_ids = array();
foreach($result as $result_row) {
    $home_stay_ids[] = $result_row["hs_id"];
}
$promotion_result = $home_stay_search_service->get_promotion_price_by_home_stay_ids_and_room_date_range($home_stay_ids, $beginDate, $endDate);

// 取得其他相關資訊(wifi、寵物、早餐、免費取消、定價)
$info_result = $home_stay_search_service->get_other_info_by_home_stay_ids_and_room_date_range($home_stay_ids, $beginDate, $endDate);

//取得活動清單
$promotion_list = $promotion_service->find_valid_promotions_by_home_stay_ids_and_room_date_range($home_stay_ids, $beginDate, $endDate);

// 排出不再設定日期裡的活動(一般優惠、24小時優惠可以設星期幾)
$room_days = (strtotime($endDate) - strtotime($beginDate)) / 86400;
foreach ($promotion_list as $k=>$promotion){
    $tmpHlidayAry = explode(',', $promotion['p_weekday']);
    for($i=0;$i<$room_days;$i++){
        $w = getWeek(strtotime($beginDate."+".$i." day"));
        if(!in_array($w, $tmpHlidayAry)){
            unset($promotion_list[$k]);
        }
    }
}

$promotion_ids = array();
foreach($promotion_list as $promotion_row) {
    $promotion_ids[$promotion_row['p_home_stay_id']][] = $promotion_row["p_type"];
}

// 設定最低價
foreach($promotion_result as $promotion_result_row) {
    foreach($result as $result_row) {
        if($promotion_result_row["hs_id"] == $result_row["hs_id"]) {
            $result_row["min_price"] = $promotion_result_row["min_price"];
        }
    }
}
/**
 * 第二階段程式排除
 * 排除像子區、…等相關資料
 */
$cond = array();
if(!empty($selectSmallArea)) {
    $selectSmallArea = preg_split('/,/', $selectSmallArea);
    $cond["small_area_ids"] = is_array($selectSmallArea) ? $selectSmallArea : array($selectSmallArea);
}
if(!empty($minPrice)) {
    $cond["min_price"] = $minPrice;
}
if(!empty($maxPrice)) {
    $cond["max_price"] = $maxPrice;
}
if(!empty($facility)) {
    $facility = preg_split('/,/', $facility);
    $cond["facilitys"] = is_array($facility) ? $facility : array($facility);
}
if(!empty($ecology)) {
    $ecology = preg_split('/,/', $ecology);
    $cond["ecologys"] = is_array($ecology) ? $ecology : array($ecology);
}
if(!empty($leisure)) {
    $leisure = preg_split('/,/', $leisure);
    $cond["leisures"] = is_array($leisure) ? $leisure : array($leisure);
}
if(!empty($topic)) {
    $topic = preg_split('/,/', $topic);
    $cond["topics"] = is_array($topic) ? $topic : array($topic);
}
if(!empty($preferential)) {
    $preferential = preg_split('/,/', $preferential);
    $cond["preferentials"] = is_array($preferential) ? $preferential : array($preferential);
}

$result = $home_stay_search_service->narrow_down_result_by_cond($result, $promotion_result, $info_result, $promotion_ids, $cond);
// printmsg($result);
//exit();
// 排序價格
if(!empty($selectPriceOrder)) {
    $result = $home_stay_search_service->sort_result_data_by_price_order($result, $promotion_result, $selectPriceOrder);
}

// 排序評論
if(!empty($selectCommentOrder)) {
    $result = $home_stay_search_service->sort_result_data_by_comment_order($result, $selectCommentOrder);
}
// 2015-08-17 置頂不要 steak
// if(empty($keyWord)) {
// 	// 取得 alway on top 旅宿 hf_home_stay_display_order.hsdo_top_flag = 1, 目前只取1筆
// 	$on_top_list = $home_stay_search_service->find_always_ontop_homestay($cur_lang, $num_on_top, $home_stay_ids, $beginDate, $endDate);
// 	if(!empty($on_top_list)) {
// 		$result = array_merge($on_top_list, $result);
// 	}
// }

$totalRow = count($result);
$totalPage = getTotalPage($totalRow, $pageSize);
// 存入Session 分頁用
// if($totalPage > 1) {
// 	$_SESSION[$uuid . 'data'] = $result;
// 	$_SESSION[$uuid . 'promotion'] = $promotion_result;
// 	$_SESSION[$uuid . 'first_level'] = $first_level_home_stay_id_list;
// 	$_SESSION[$uuid . 'promotion_list'] = $promotion_list;
// 	$_SESSION[$uuid . 'info'] = $info_result;
// }
// 取得頁面顯示所須資料
$smallAreaList = array();
//$eventList = array();
$photoList = array();
$roomList = array();
$homeStayAdvertisementList = array();

if(!empty($first_level_home_stay_id_list)){
    // 進階篩選 - 地區(小區)
    $smallAreaList = $area_dao->findCountingSmallAreaListByHomeStayIdList($first_level_home_stay_id_list);

    // 找出有空房日期
    $roomList = $room_dao->findValidRoomListByHomeStayIdListAndDate($result, $beginDate, $endDate, $pageSize, $offset);
    // 找出圖片
    $photoList = $photo_dao->findValidPhotoListByHomeStayIdList($result, $pageSize, $offset);

    // 找出媒體推薦、影視場景 (目前不處理名人足跡)
    $homeStayAdvertisementList = $home_stay_advertisment_dao->findValidHomeStayAdvertisementListByHomeStayIdList($result, $pageSize, $offset);

    // 找出活動
    //$eventList = $event_data_config_dao->find_valid_by_home_stay_id_and_date_range($home_stay_ids, $beginDate, $endDate);
}

// 取得主區資料
$bigAreaRow = array();
$bigAreaName = "";
if(!empty($areaId)){
    $bigAreaRow = $area_dao->loadAreaWithLang($areaId, $cur_lang);
}
if(!empty($bigAreaRow)) {
    $bigAreaName = empty($bigAreaRow["aml_name"]) ? $bigAreaRow["a_name"] : $bigAreaRow["aml_name"];
}

// 搜尋主區 Top 5 旅宿
// $areaExtendList = $area_extend_dao->findHfAreaExtendListByAreaId($areaId, true);

$meta_keyword = 'EZ訂,#areaName#,#areaName#民宿,#areaName#訂房,#areaName#推薦民宿,#areaName#特色民宿,#areaName#不錯民宿,#areaName#2天1夜,#areaName#過夜,信用卡,買一送一';
$meta_keyword = preg_replace('/#areaName#/', $bigAreaName, $meta_keyword);

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

$favorite_list = array();
if(!empty($login_user_data)) {
	$user_favorite_type_ids = $tripitta_web_service->get_user_favorite_type_ids('homestay');
	$favorite_list = $tripitta_web_service->find_user_favorite_by_user_id_and_ref_type_ids($login_user_data["serialId"], $user_favorite_type_ids);
}

// for 地圖用
if($type == "map"){
	$home_stay_ids = array();
	foreach ($result as $r){
		array_push($home_stay_ids, $r['hs_id']);
	}
	$home_stay_ids = implode(",", $home_stay_ids);
	$home_stay_gc = $tripitta_homestay_service -> get_home_stay_geographical_coordinates($home_stay_ids, $beginDate, $endDate, $areaId);
}
?>
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="travSearch-container">
		<div class="filterWrap">
			<nav class="left">
				<div class="filterGroup">
					<div class="locationSelect">
						<select id="areaCode" name="areaCode" class="location">
							<? foreach($area_list as $area_row) { ?>
							<option value="<?= $area_row["a_code"] ?>"<? if($areaCode == $area_row["a_code"]) { echo ' selected '; } ?>><?= $area_row["a_name"] ?></option>
							<? } ?>
						</select>
						<i class="fa fa-angle-down"></i>
					</div>
					<input type="text" id="beginDate" name="beginDate" placeholder="入住日期" class="checkIn" maxlength="20" value="<?= $beginDate ?>">
					<input type="text" id="endDate" name="endDate" placeholder="退房日期" class="checkOut" maxlength="20" value="<?= $endDate ?>">
					<div class="peopleSelect">
						<select class="people" id="roomType" name="roroomType">
							<?php for($i=1;$i<=10;$i++){
								$select = '';
								if($i == $roomType) $select = 'selected';
							?>
							<option value="<?= $i ?>" <?= $select ?>><?= $i ?>人</option>
							<?php } ?>
						</select>
						<i class="fa fa-angle-down"></i>
					</div>
					<div class="roomSelect">
						<select class="room" id="roomQuantity" name="roomQuantity">
							<?php for($i=1;$i<=10;$i++){
								$select2 = '';
								if($i == $roomQuantity) $select2 = 'selected';
							?>
							<option value="<?= $i ?>" <?= $select2 ?>><?= $i ?>間</option>
							<?php } ?>
						</select>
						<i class="fa fa-angle-down"></i>
					</div>
					<input type="text" id="keyword" name="keyword" placeholder="關鍵字搜尋" class="keywords" maxlength="25" value="<?= $keyWord ?>">
					<button type="button" class="goBtn">GO</button>
				</div>
			</nav>
			<nav class="right">
				<?php if($type == "list") { ?>
				<div class="mapMode" onclick="toSearchType('map')">
					<i class="img-viewmap"></i>
					地圖模式
				</div>
				<?php }else if($type == "map"){ ?>
				<div class="listMode" onclick="toSearchType('list')" style="display:block;">
					<i class="fa fa-th-list"></i>
					列表模式
				</div>
				<?php } ?>
			</nav>
		</div>

		<!-- advance filter -->
		<div class="filterAdvanWrap">
			<nav class="leftAdvan">
				<button class="btnAdvan" onclick="showAdvancedFilter()">進階篩選</button>
				<ul>
                    <? if($minPrice != 0 || $maxPrice != 25000) { ?><li class="price" onclick="deleteSelectInfo('price');">價格區間<span><i class="fa fa-times"></i></span></li><?php } ?>
                    <? if(!empty($facility)) { ?><li class="price" onclick="deleteSelectInfo('facility');">民宿設施<span><i class="fa fa-times"></i></span></li><?php } ?>
                    <? if(!empty($selectSmallArea)) { ?><li class="area" onclick="deleteSelectInfo('smallArea');">地區分類<span><i class="fa fa-times"></i></span></li><?php } ?>
                    <? if(!empty($preferential)) { ?><li class="discount" onclick="deleteSelectInfo('preferential');">優惠方式<span><i class="fa fa-times"></i></span></li><?php } ?>
                    <? if(!empty($ecology) || !empty($leisure) || !empty($topic)) { ?><li class="hotel" onclick="deleteSelectInfo('hotel');">民宿分類<span><i class="fa fa-times"></i></span></li><?php } ?>
				</ul>
				<h5>
					<span>約有</span>
					<span class="dataCount"><?= number_format(count($result)) ?></span>
					<span>筆資料</span>
				</h5>
			</nav>
			<nav class="rightAdvan">
				<div class="sortSelect">
					<select class="sort" onchange="selPriceOrder()" id="sortSelect">
						<option value="0" <?php if(empty($selectPriceOrder)){echo 'selected'; }?>>排序選擇</option>
						<option value="desc" <?php if($selectPriceOrder == "desc" && !empty($selectPriceOrder)){ echo 'selected'; }?>>高 > 低</option>
						<option value="asc"  <?php if($selectPriceOrder == "asc" && !empty($selectPriceOrder)){ echo 'selected'; } ?>>低 > 高</option>
					</select>
					<input type="hidden" id="selectPriceOrder" name="selectPriceOrder" value="0" />
					<i class="fa fa-angle-down"></i>
				</div>
			</nav>
		</div>


		<!-- filter menu -->
		<div class="filterMenu" id="filterMenu">
			<section class="priceWrap">
				<div class="iconGroup">
					<div class="icon">
						<i class="fa fa-money"></i>
					</div>
					<h2>價格區間</h2>
				</div>
				<div class="priceGroup">
					<div class="minSelect">
						<select class="min" id="minPrice" name="minPrice">
							<? for($i=0 ; $i<=25000 ; $i+= 500) { echo '<option value="' . $i . '"' . ($minPrice == $i ? ' selected ' : '') . '>' . $currency_code . ' ' . getCeil($i / $exchange_rate, $point_length) . '</option>'; } ?>
						</select>
						<i class="fa fa-angle-down"></i>
					</div>
					<h6>～</h6>
					<div class="maxSelect">
						<select class="max" id="maxPrice" name="maxPrice">
							<? for($i=0 ; $i<=25000 ; $i+= 500) { echo '<option value="' . $i . '"' . ($maxPrice == $i ? ' selected ' : '') . '>' . $currency_code . ' ' . getCeil($i / $exchange_rate, $point_length) . '</option>'; } ?>
						</select>
						<i class="fa fa-angle-down"></i>
					</div>
				</div>
			</section>
			<section class="serviceWrap">
				<div class="iconGroup">
					<div class="icon">
						<i class="fa fa-hospital-o"></i>
					</div>
					<h2>民宿設施</h2>
				</div>
				<div class="serviceGroup">
					<?
					foreach($facility_row as $key => $value){
							$active = '';
							$is_click = 0;
							$checkbox = '';
							if(!empty($facility)){
								if(in_array($key,$facility)) $active = 'style="color:hsl(54, 100%, 50%)"';
								if(in_array($key,$facility)) $is_click = 1;
								if(in_array($key,$facility)) $checkbox = 'checked';
							}
					?>
					<label>
						<input type="checkbox" name="facility" value="<?= $key ?>" <?= $checkbox ?> onclick="showActive('facility', <?= $key?>)">
						<input type="hidden" id="facChick<?= $key ?>" value="<?= $is_click ?>" />
						<span id="facText<?= $key ?>" <?= $active ?>><?= $value ?></span>
					</label>
					<?
					}
					?>
					<input type="hidden" id="facilitys" value="<?= $facilitys ?>" />
				</div>
			</section>
			<section class="areaWrap">
				<div class="iconGroup">
					<div class="icon">
						<i class="fa fa-map-marker"></i>
					</div>
					<h2>地區分類</h2>
				</div>
				<div class="areaGroup">
					<?
					if(!empty($smallAreaList)){
					    foreach ($smallAreaList as $idx => $item) {
					        if(empty($item["a_id"])) continue;
					    	$active = '';
							$is_click = 0;
							$checkbox = '';
							if(!empty($selectSmallArea)){
								if(in_array($item["a_id"],$selectSmallArea)) $active = 'style="color:hsl(54, 100%, 50%)"';
								if(in_array($item["a_id"],$selectSmallArea)) $is_click = 1;
								if(in_array($item["a_id"],$selectSmallArea)) $checkbox = 'checked';
							}
					?>
					<label>
						<input type="checkbox" name="smallAreas[]" value="<?= $item["a_id"] ?>" onclick="showActive('smallArea', <?= $item["a_id"] ?>)" <?= $checkbox ?>>
						<input type="hidden" id="isChick<?= $item["a_id"] ?>" value="<?= $is_click ?>" />
						<span id="areaText<?= $item["a_id"] ?>" <?= $active ?>><?= $item["a_name"] ?>(<?= $item['cnt'] ?>)</span>
					</label>
					<?
					    }
					}
					?>
					<input type="hidden" id="smallAreas" value="<?= $smallArea ?>" />
				</div>
			</section>
			<section class="discountWrap">
				<div class="iconGroup">
					<div class="icon">
						<i class="fa fa-tags"></i>
					</div>
					<h2>優惠方式</h2>
				</div>
				<div class="discountGroup">
				<?
				foreach(Constants::$HOME_STAY_PROMOTION as $key => $value){
						$active = '';
						$is_click = 0;
						$checkbox = '';
						if(!empty($preferential)){
							if(in_array($key,$preferential)) $active = 'style="color:hsl(54, 100%, 50%)"';
							if(in_array($key,$preferential)) $is_click = 1;
							if(in_array($key,$preferential)) $checkbox = 'checked';
						}
				?>
					<label>
						<input type="checkbox" name="preferential" value="<?= $key ?>" <?= $checkbox ?> onclick="showActive('preferential', <?= $key?>)">
						<input type="hidden" id="prisChick<?= $key ?>" value="<?= $is_click ?>" />
						<span id="prefText<?= $key ?>" <?= $active ?>><?= $value ?></span>
					</label>
				<?}
				?>
				<input type="hidden" id="preferentials" value="<?= $preferentials ?>" />
				</div>
			</section>
			<section class="hotelWrap">
				<div class="iconGroup">
					<div class="icon">
						<i class="fa fa-sitemap"></i>
					</div>
					<h2>民宿分類</h2>
				</div>
				<div class="hotelGroup">
					<?
					foreach(Constants::$HOME_STAY_TOPIC as $key => $value){
							$active = '';
							$is_click = 0;
							$checkbox = '';
							if(!empty($topic)){
								if(in_array($key,$topic)) $active = 'style="color:hsl(54, 100%, 50%)"';
								if(in_array($key,$topic)) $is_click = 1;
								if(in_array($key,$topic)) $checkbox = 'checked';
							}
					?>
					<label>
						<input type="checkbox" name="topic[]" value="<?= $key ?>" <?= $checkbox ?> onclick="showActive('topic', <?= $key ?>);">
						<input type="hidden" id="tisChick<?= $key ?>" value="<?= $is_click ?>" />
						<span id="topicText<?= $key ?>" <?= $active ?>><?= $value ?></span>
					</label>
					<?
					}
					?>
					<input type="hidden" id="topics" value="<?= $topics ?>" />
					<?php
					foreach(Constants::$HOME_STAY_ECOLOGY_SENERY as $key => $value){
							if($key == 9 ) continue; // 第9個選項(無)前台不秀
							$active = '';
							$is_click = 0;
							$checkbox = '';
							if(!empty($ecology)){
								if(in_array($key,$ecology)) $active = 'style="color:hsl(54, 100%, 50%)"';
								if(in_array($key,$ecology)) $is_click = 1;
								if(in_array($key,$ecology)) $checkbox = 'checked';
							}
					?>
					<label>
						<input type="checkbox" name="ecology[]" value="<?= $key ?>" <?= $checkbox ?> onclick="showActive('ecology', <?= $key ?>)">
						<input type="hidden" id="eisChick<?= $key ?>" value="<?= $is_click ?>" />
						<span id="ecologtText<?= $key ?>" <?= $active ?>><?= $value ?></span>
					</label>
					<?
					}
					?>
					<input type="hidden" id="ecologys" value="<?= $ecologys ?>" />
					<?php
					foreach(Constants::$HOME_STAY_LEISURE_ACTIVITIES as $key => $value){
							$active = '';
							$is_click = 0;
							$checkbox = '';
							if(!empty($leisure)){
								if(in_array($key,$leisure)) $active = 'style="color:hsl(54, 100%, 50%)"';
								if(in_array($key,$leisure)) $is_click = 1;
								if(in_array($key,$leisure)) $checkbox = 'checked';
							}
					?>
					<label>
						<input type="checkbox" name="leisure[]" value="<?= $key ?>" <?= $checkbox ?> onclick="showActive('leisure', <?= $key ?>)">
						<input type="hidden" id="lisChick<?= $key ?>" value="<?= $is_click ?>" />
						<span id="leiText<?= $key ?>" <?= $active ?>><?= $value ?></span>
					</label>
					<?
					}
					?>
					<input type="hidden" id="leisures" value="<?= $leisures ?>" />
				</div>
			</section>
			<div class="btnWrap">
				<button class="resetBtn">取消</button>
				<button class="filterBtn" onclick="choose_other()">篩選</button>
			</div>
		</div>
		<?php
			if ($type == 'list'){
		?>
		<div class="result" id="list">
		<?
		    if (count($result) > 0) {
		        $tc = $offset + $pageSize;
		        if ($tc > count($result)) $tc = count($result);
		        $swiperIdx = 0;
		        for ($ti = $offset; $ti < $tc; $ti++) {
		            $row = $result[$ti];
		            $home_stay_id = $row["hs_id"];
		            $small_area_id = $row["hs_small_area_id"];
		            $home_stay_name = $row["hs_name"];

		            $home_stay_params = "homeStayId=" . $home_stay_id . "&beginDate=" . $beginDate . "&endDate=" . $endDate . (empty($homeStayCampaignParams) ? "" : "&" . $homeStayCampaignParams);

		            $have_promotion = (!empty($promotion_result[$home_stay_id]['promotions'])) ? true:false;

		            $s = '0';
		            $grade = round($row['hs_grade'], 1);
		            $pos = strrpos($grade, '.');
		            if ($pos) $s = intval(substr($grade, -1));
		            $smallAreaName = '';
		            if (count($smallAreaList) > 0) {
		                foreach ($smallAreaList as $item) {
		                    if ($item['a_id'] == $small_area_id) {
		                        $smallAreaName = $item['a_name'];
		                        break;
		                    }
		                }
		            }
		            $idx = 0;
		            $hotDesc = '';
		            $strLength = 0;
		            $lineNumber = 0;

		            if (count($homeStayAdvertisementList) > 0) {
		                foreach ($homeStayAdvertisementList as $hsa) {
		                    if ($hsa['hsa_home_stay_id'] == $home_stay_id) {
		                        if($idx != 0 && ($strLength + mb_strlen($hsa['title'],'UTF-8')+2) > 20){
		                            $hotDesc .= '<br/>';
		                            $strLength = mb_strlen($hsa['title'],'UTF-8')+2;
		                            $lineNumber++;
		                        }else{
		                            $strLength += mb_strlen($hsa['title'],'UTF-8')+2;
		                        }

		                        if (mb_strlen($row['hs_name'], 'UTF-8') < 9 && $lineNumber > 2)  break;
		                        else if (mb_strlen($row['hs_name'], 'UTF-8') > 8 && $lineNumber > 1)  break;

		                        $hotDesc .= '<span class="search_T11">';
		                        $hotDesc .= $hsa['title'];
		                        if ($hsa['hsa_type'] == 1) $hotDesc .= '報導';
		                        else $hotDesc .= '場景';
		                        $hotDesc .= '</span> ';
		                        $idx++;
		                    }
		                }

		            }

		            $idx = 0;
		            $roomDesc = '';
		            $t = 0;
		            $tCount = 0;
		            foreach ($roomList as $item) {
		                if ($item['hs_id'] == $home_stay_id) {
		                    if (intval($item['cnt']) == 0 || $item['cnt'] < $roomQuantity) {
		                        if ($idx > 0) $roomDesc .= ', ';
		                        $time = strtotime($item['r_date']);
		                        $month = intval(date('m', $time));

		                        if ($t != $month) {
		                            $t = $month;
		                            $roomDesc .= $month . ' / ';
		                        }
		                        $roomDesc .= date('d', $time);
		                        $idx++;
		                    }
		                    $tCount++;
		                    // 搜尋出的民宿可能會重覆顯示 如:置頂、活動，但不管單一民宿重覆出現幾次$roomList都只會有一個，所以不用unset($roomList[$room_idx])
		                    // unset($roomList[$room_idx]);
		                }
		            }

		            $min_sell_price = $promotion_result[$home_stay_id]['min_price'];
		            $wifi = $info_result[$home_stay_id]['wifi'];
		            $pet = $info_result[$home_stay_id]['pet'];
		            $breakfast = $info_result[$home_stay_id]['breakfast'];
		            $fee = $info_result[$home_stay_id]['fee_cancel'];
		            $price = $info_result[$home_stay_id]['price'];

		            $cnt_empty_rooms = 0;
		            foreach ($first_level_home_stay_id_list as $first_row){
		                if($home_stay_id == $first_row['hs_id']){
		                    $cnt_empty_rooms = $first_row['cnt'];
		                    break;
		                }
		            }

		            $promotion_names = array();
		            $promotion_name_str = '';
		            foreach ($promotion_list as $idx => $v){
		                if ($home_stay_id == $v['p_home_stay_id']){
		                    $promotion_names[] = $v['p_name'];
		                }
		            }
		            if(!empty($promotion_names)) {
		                $promotion_name_str = implode(" <br /> ", $promotion_names);
		            }


		            // 一開始顯示的民宿圖片
		            $homeStayPhoto = '/web/img/no-pic.jpg';
		            if (!empty($row['hs_main_photo'])) {
		                $homeStayPhoto = get_config_image_server() . '/photos/travel/home_stay/' . $home_stay_id . '/' . $row['hs_main_photo'] . '_middle.jpg';
		            }

		            // 取得Trip Advisor評論資訊
		            $ta_info_row = [];
		            foreach($ta_info_list as $t) {
		                if($t["sm_source_id"] == $home_stay_id) {
		                    $ta_info_row = $t;
		                    break;
		                }
		            }

		            $folder = 10;
		            $favorite_class = "fa-heart-o";
		            foreach($favorite_list as $favorite_row) {
		            	if($folder == 10 && ($favorite_row["uf_type"] == 0 || $favorite_row["uf_type"] == $folder) && $favorite_row["uf_home_stay_id"] == $home_stay_id) {
		            		$favorite_class = "fa-heart";
		            		break;
		            	}else if($folder != 10 && $favorite_row["uf_type"] == $folder && $favorite_row["uf_home_stay_id"] == $home_stay_id) {
		            		$favorite_class = "fa-heart";
		            		break;
		            	}
		            }
		?>
			<section class="item">
				<div class="imgWrap">
					<div class="img-collect" title="收藏" data-type="10" data-id="<?= $home_stay_id ?>">
						<i class="fa <?= $favorite_class ?>" id="<?= "10_" . $home_stay_id ?>"></i>
					</div>
					<div class="sliderBtn">
						<i class="fa fa-angle-left" idx="<?= $swiperIdx ?>" ></i>
						<i class="fa fa-angle-right" idx="<?= $swiperIdx ?>" ></i>
					</div>
					<div class="swiper-container">
						<div class="swiper-wrapper">
							<div class="swiper-slide">
								<a href="/booking/<?= $areaCode ?>/<?= $home_stay_id ?>/?<?= $link_home_stay_link_params ?>"><img alt="<?= $home_stay_name ?>" src="<?= $homeStayPhoto ?>" style="width:300px;height:206px" onerror="javascript:this.src='/web/img/no-pic.jpg';" /></a></div>
<?
			$idx = 0;
			for ($i = 0; $i < count($photoList); $i++) {
				$p = $photoList[$i];
				if ($p['p_reference_id'] == $home_stay_id) {
					if($p["p_id"] == $row['hs_main_photo']){
						$idx++;
						continue;
					}
					if($idx >= 5){
						break;
					}
					$img = $image_server_url . '/photos/travel/home_stay/' . $home_stay_id . '/' . $p['p_id'] . "_middle.jpg";
?>
						<div class="swiper-slide"><a href="/booking/<?= $areaCode ?>/<?= $home_stay_id ?>/"><img alt="<?= $home_stay_name ?>" src="<?= $img ?>" style="width:300px;height:206px" onerror="javascript:this.src='/web/img/no-pic.jpg';" /></a></div>
<?
					$idx++;
				}
			}

?>
						</div>
					</div>
				</div>
				<div class="itemDetailWrap">
					<div class="itemDetailGroup">
						<div class="nameGroup">
							<h4 class="name" style="cursor:pointer;" onclick="javscript:location.href='/booking/<?= $areaCode ?>/<?= $home_stay_id ?>/?<?= $link_home_stay_link_params ?>';"><?= $row['hs_name'] ?></h4>
							<div class="locationGroup">
								<i class="fa fa-map-marker"></i>
								<p class="wrap">
									<span class="location"><?php echo $smallAreaName?></span>
								</p>
							</div>
						</div>
						<div class="costGroup">
							<h2 class="discountType" style="color:red"><?= !empty($promotion_names) ? '促銷優惠' : ''; ?></h2>
							<h3>
								<p class="currency"><?= $currency_code ?></p>
								<p class="cost" style="color:red"><?= number_format($min_sell_price /$exchange_rate) ?></p>
								<p>起</p>
							</h3>
							<h4>
								<p class="currencyFinal">定價<?= $currency_code ?></p>
								<p class="costFinal" ><?= number_format($price /$exchange_rate) ?></p>
							</h4>
						</div>
					</div>
					<div class="service">
						<? if($wifi == 1) { ?>
						<div class="wifi">
							<p>
								<i class="fa fa-wifi"></i>
							</p>
							<p>提供WIFI</p>
						</div>
						<? } ?>
						<? if($breakfast == 1){ ?>
						<div class="cutlery">
							<p>
								<i class="fa fa-cutlery"></i>
							</p>
							<P>附早餐</P>
						</div>
						<? } ?>
						<? if($pet == 2) { ?>
						<div class="paw">
							<p>
								<i class="fa fa-paw"></i>
							</p>
							<p>可帶寵物</p>
						</div>
						<? } ?>
					</div>
				</div>
				<div class="itemSubmitWrap">
					<? if(!empty($ta_info_row)) { ?>
					<a href="javascript:open_trip_advisor_review(<?= $ta_info_row["tari_id"] ?>)" class="tripadvisorLogo" target="_blank">
						<img src="https://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/<?= $ta_info_row["tari_average_rating"] ?>-33123-4.gif" style="width:118px;height:20px;"/>
						<h2>
							<span class="count"><?= $ta_info_row["tari_review_count"] ?></span>
							<span>則評論</span>
						</h2>
					</a>
					<? } else { ?>
					<a href="javascript:void(0)" class="tripadvisorLogo" target="_blank"></a>
					<? } ?>
					<a href="/booking/<?= $areaCode ?>/<?= $home_stay_id ?>/?<?= $link_home_stay_link_params ?>"><button class="submit">選擇</button></a>
					<h5>
						尚餘<span class="remainRoom"><?= $cnt_empty_rooms ?></span>間房
					</h5>
				</div>
			</section>
<?
            $total = $tc - $offset;
            if (((($ti+1)%5) == 0 && $ti != 0 && $total > 4 && ($ti+1)%10 != 0) || ($total <= 4 && $total == $ti+1) ){
                // 顯示活動資訊
                // 限時
                $sql = "SELECT * FROM hf_home_stay WHERE hs_status = 1 AND hs_area_id =". $areaId;
                $hs_row = $db_reader_travel -> executeReader($sql);
                $home_stay_ids = array();
                foreach($hs_row as $hs){
                    array_push($home_stay_ids, $hs['hs_id']);
                }
                $promotion_rows = $promotion_service -> find_valid_promotions_by_home_stay_ids_and_room_date_range($home_stay_ids, $beginDate, $endDate);
                $result1 = array();
                foreach($hs_row as $hsr){
                    foreach ($promotion_rows as $pr){
                        if($hsr['hs_id'] == $pr['p_home_stay_id']) array_push($result1, $hsr);
                    }
                }
                // $result1 = array_unique($result1);

                // 推廣
                $sql = "SELECT hf_home_stay.* FROM hf_home_stay ";
                $sql .= "INNER JOIN hf_home_stay_display_order ON hsdo_id = hs_id AND hsdo_top_flag = 1 ";
                $sql .= "WHERE hs_area_id = ".$areaId." AND hs_status = 1 ";
                //$sql .= "AND EXISTS (SELECT * FROM hf_room WHERE r_home_stay_id = hs_id AND r_date >= '".$beginDate."' AND r_date < '".$endDate."') ";
                $sql .= "ORDER BY RAND() limit 10";
                $result2 = $db_reader_travel->executeReader($sql);

                // 最新
                $sql = "SELECT hf_home_stay.* FROM hf_home_stay ";
                $sql .= "INNER JOIN hf_home_stay_display_order ON hsdo_id = hs_id ";
                $sql .= "WHERE hs_area_id = ".$areaId." AND hs_status = 1 AND hs_internal_view_status = 2 ";
                //$sql .= "AND EXISTS (SELECT * FROM hf_room WHERE r_home_stay_id = hs_id AND r_date >= '".$beginDate."' AND r_date < '".$endDate."') ";
                $sql .= "ORDER BY RAND() limit 10";
                $result3 = $db_reader_travel->executeReader($sql);



?>
			<!-- AD -->
			<section class="ad" style="display:none;">
				<a href="javascript:void(0)" class="ad1">
					<img src="/web/img/images.jpg" alt="">
					<div class="mask">
						<p class="title">早鳥優惠</p>
						<p>
							<span class="currency">NTD</span>
							<span class="cost">1,690</span>
						</p>
					</div>
				</a>
				<a href="javascript:void(0)" class="ad2">
					<img src="/web/img/images.jpg" alt="">
					<div class="mask">
						<p class="title">早鳥優惠</p>
						<p>
							<span class="currency">NTD</span>
							<span class="cost">1,690</span>
						</p>
					</div>
				</a>
				<a href="javascript:void(0)" class="ad3">
					<img src="/web/img/images.jpg" alt="">
					<div class="mask">
						<p class="title">早鳥優惠</p>
						<p>
							<span class="currency">NTD</span>
							<span class="cost">1,690</span>
						</p>
					</div>
				</a>
			</section>

<?
            }
            $swiperIdx++;
        }
    }
?>
		</div>
		<!-- pagination -->
		<!--換下一頁-->
		<div class="pagination2 manu" id="pagination">
		<?php
		if($totalPage > 1) {
			echo getPageLinkForFront($pageno, $totalPage, 'toPage');
		}
		?>
		</div>
		<!--end 換下一頁-->
		<?php
		}
		?>
	</div>
	<!-- map mode -->
	<?php

		if ($type == 'map'){
	?>
	<div class="travHotelMap" id="map">
		<div id="map2" style="width: 100%; height: 700px;"></div>

		<div class="mapControl">
			<section class="part1">
				<p class="hasRoom">
					<i class="img-bnb-s"></i>
					<span>旅宿 (有房)</span>
				</p>
				<p class="noRoom">
					<i class="img-bnb-offs"></i>
					<span>旅宿 (無房)</span>
				</p>
			</section>
			<section class="part2">
				<label for="activity" class="activity">
					<i class="img-activity-s"></i>
					<span>活動</span>
					<input type="checkbox" id="activity" onclick="triMarker(0,'activity')">
				</label>
				<label for="scenic" class="scenic">
					<i class="img-viewpoint-s"></i>
					<span>景點</span>
					<input type="checkbox" id="scenic" onclick="triMarker(0,'scenic')">
				</label>
				<label for="food" class="food">
					<i class="img-food-s"></i>
					<span>美食</span>
					<input type="checkbox" id="food" onclick="triMarker(0,'food')">
				</label>
				<label for="souvenir" class="souvenir">
					<i class="img-gift-s"></i>
					<span>伴手禮</span>
					<input type="checkbox" id="souvenir" onclick="triMarker(0,'souvenir')">
				</label>
			</section>
		</div>
	</div>
	<?php } ?>
	<footer class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
	<input type="hidden" id="user_serial_id" value="<?php echo $serialId ?>">
</body>
</html>
<?php
function getWeek($timestamp){
	$weekIndex=date("w",$timestamp);
	$weekarray=array('0','1','2','3','4','5','6');
	return $weekarray[$weekIndex];
}
?>
<script>
var is_login = <?= ($is_login) ? 1:0 ?>;
var type = '<?= $type ?>';
var page_params = '<?= $page_params.$page_params2 ?>';
var page_params2 = '<?= $page_params ?>';
var page_params3 = '<?= $page_params3 ?>';
var containers = [];
var swiperIdx = 0;
function toPage(pageno) {
	location.href='/booking/<?= $areaCode ?>/' + page_params + '&pageno=' + pageno + '&selectPriceOrder=' + $("#sortSelect").val();
}
$(function() {
	initSwiper();
	// 設定加入收藏動作
    $('.imgWrap .img-collect').each(function() {
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

	// 顯示進階搜尋
	//$('.travSearch-container .btnAdvan').click(function () { $('.travSearch-container .filterMenu').show(200); });
	$('.travSearch-container .resetBtn').click(function () { $('.travSearch-container .filterMenu').hide(1000); });
	//$('.travSearch-container .filterBtn').click(function () { query_homestay_advance(); });


	// 設定搜尋Bar
	var caneldar_option = <?= json_encode(Constants::$CALENDAR_OPTIONS) ?>;
	$('#beginDate').datepicker(caneldar_option).datepicker('option', {minDate: new Date()}).change(function(){ check_date(); });
	$('#endDate').datepicker(caneldar_option).datepicker('option', {minDate: 1}).change(function(){ check_date(); });
	$('.travSearch-container .goBtn').click(function() { query_homestay(); });

	// 地圖
	<?php if($type == "map"){ ?> map_init(); <?php } ?>
});
function check_date() {
	var beginDate = $('#beginDate').val();
	var endDate = $('#endDate').val();
	if(beginDate != '' && endDate == '') {
		var bd = new Date(beginDate);
		bd.setDate(bd.getDate()+1);
		$('#endDate').val(bd.getFullYear() + '-' + (bd.getMonth() + 1) + '-' + bd.getDate());
	} else if(beginDate == '' && endDate != '') {
		var ed = new Date(endDate);
		ed.setDate(ed.getDate()-1);
		$('#beginDate').val(ed.getFullYear() + '-' + (ed.getMonth() + 1) + '-' + ed.getDate());
	} else if(beginDate != '' && endDate != '') {
// 		var bd = new Date(beginDate);
// 		var ed = new Date(endDate);
// 		if(bd.getTime() >= ed.getTime()) {
// 			bd.setDate(bd.getDate()+1);
// 			$('#endDate').val(bd.getFullYear() + '-' + (bd.getMonth() + 1) + '-' + bd.getDate());
// 		}
	}
}
function showAdvancedFilter() {
	$('#filterMenu').slideToggle(1000);
}
// function add_favorite(ref_type, ref_id) {
// 	if(!is_login) {
// 		show_popup_login();
// 		return;
// 	}
// 	var p = {};
//     p.func = 'add_favorite';
//     p.user_id = $('#user_serial_id').val();
//     p.ref_type = ref_type;
//     p.ref_id = ref_id;
//     console.log(p);
//     $.post("/web/ajax/ajax.php", p, function(data) {
//         console.log(data);
//         if(data.code == '9999'){
//             alert(data.msg);
//         } else {
//             // 顯示註冊完成並顯示註冊完成popup window
// 			alert('已加至我的收藏');
//         }
//     }, 'json').done(function() { }).fail(function() { }).always(function() { });
// }

function initSwiper(){
	var pages = $('.travSearch-container .swiper-container').each(function(idx ){
		if(idx < swiperIdx){
			return;
		}
		containers[swiperIdx] = $(this).swiper({loop:true});
		swiperIdx++;
		});
	$('.travSearch-container .fa-angle-left').on('click', function(e){
		var idx = $(this).attr('idx');
		e.preventDefault();
		containers[idx].swipePrev();
		//mySwiper.swipePrev()
	})
	$('.travSearch-container .fa-angle-right').on('click', function(e){
		var idx = $(this).attr('idx');
		e.preventDefault();
		containers[idx].swipeNext();
		//$(containers[idx]).swipeNext();
	})
}

function open_trip_advisor_review(ta_id) {
	window.open('http://www.tripadvisor.com/WidgetEmbed-cdspropertydetail?locationId=' + ta_id + '&partnerId=CB56EED944AF4459B7E92BBF9B292AC6&lang=zh_TW&allowMobile&display=true', 'trip_advisor', 'width=600, location=0, menubar=0, resizable=0, scrollbars=1, status=0, titlebar=0, toolbar=0');
}

function choose_other(){
	event.preventDefault();
	var smallArea = $('#smallAreas').val();
	var min_price = $('#minPrice').val();
	var max_price = $('#maxPrice').val();
	var smallArea = $('#smallAreas').val();
	var facility = $('#facilitys').val();
	var ecology = $('#ecologys').val();
	var leisure = $('#leisures').val();
	var topic = $('#topics').val();
	var preferential = $('#preferentials').val();

	location.href = '/booking/<?= $areaCode ?>/' + page_params2 + '&minPrice=' + min_price + '&maxPrice=' + max_price + '&selectSmallArea=' + smallArea + '&ecology='+ ecology + '&leisure='+leisure + '&topic='+topic + '&preferential='+preferential + '&facility='+facility + '&type=' + type;
}

function query_homestay() {
	var areaCode = $('#areaCode').val();
	var beginDate = $('#beginDate').val();
	var endDate = $('#endDate').val();
	var roomType = parseInt($('#roomType').val());
	var roomQuantity = parseInt($('#roomQuantity').val());
	var keyword = $('#keyword').val();
	if(areaCode == '') { alert('請選擇地區'); return; }
	if(beginDate != '' && endDate != '') {
		var bd = new Date(beginDate);
		var ed = new Date(endDate);
		//console.log(bd.getTime(), ed.getTime());
		if(bd.getTime() >= ed.getTime()) { alert('入住區間錯誤 - 入住日不可大於退房日'); return; }
	}
	if(isNaN(roomType) || roomType < 0 || roomType > 10){ roomType = 1; }
	if(isNaN(roomQuantity) || roomQuantity < 0 || roomQuantity > 10){ roomQuantity = 1; }
	var url = '/booking/' + areaCode + '/?beginDate=' + encodeURIComponent(beginDate) + '&endDate=' + encodeURIComponent(endDate)  + '&roomType=' + encodeURIComponent(roomType)  + '&roomQuantity=' + encodeURIComponent(roomQuantity) + '&keyWord=' + keyword;
	//console.log(url);
	location.href = url;
}
function query_homestay_advance() {

}

function showActive(type ,id){
	if(type == 'smallArea'){
		smallArea = $('#smallAreas').val();

		if($('#isChick'+id).val() == 0){
			// 原本沒點變有點
			$('#areaText'+id).css('color','hsl(54, 100%, 50%)');
			$('#isChick'+id).val(1);
			// 增加點選的子區
			if(smallArea == ''){
				$('#smallAreas').val(id);
			}else{
				$('#smallAreas').val(smallArea+','+id);
			}
		}else{
			// 原本有點變沒點
			$('#areaText'+id).css('color','');
			$('#isChick'+id).val(0);
			// 刪除點選的子區
			arrayStr = smallArea.split(",");
			for(var i = 0;i< arrayStr.length;i++){
				if(arrayStr[i] == id) arrayStr.splice(i,1);
			}
			asds = arrayStr.join();
			$('#smallAreas').val(asds);
		}
	}else if(type == 'facility'){
		facilitys = $('#facilitys').val();

		if($('#facChick'+id).val() == 0){
			// 原本沒點變有點
			$('#facText'+id).css('color','hsl(54, 100%, 50%)');
			$('#facChick'+id).val(1);
			// 增加點選的對象
			if(facilitys == ''){
				$('#facilitys').val(id);
			}else{
				$('#facilitys').val(facilitys+','+id);
			}
		}else{
			// 原本有點變沒點
			$('#facText'+id).css('color','');
			$('#facChick'+id).val(0);
			// 刪除點選的對象
			arrayStr = facilitys.split(",");
			for(var i = 0;i< arrayStr.length;i++){
				if(arrayStr[i] == id) arrayStr.splice(i,1);
			}
			asds = arrayStr.join();
			$('#facilitys').val(asds);
		}
	}else if(type == 'ecology'){
		peoples = $('#ecologys').val();

		if($('#eisChick'+id).val() == 0){
			// 原本沒點變有點
			$('#ecologtText'+id).css('color','hsl(54, 100%, 50%)');
			$('#eisChick'+id).val(1);
			// 增加點選的對象
			if(peoples == ''){
				$('#ecologys').val(id);
			}else{
				$('#ecologys').val(peoples+','+id);
			}
		}else{
			// 原本有點變沒點
			$('#ecologtText'+id).css('color','');
			$('#eisChick'+id).val(0);
			// 刪除點選的對象
			arrayStr = peoples.split(",");
			for(var i = 0;i< arrayStr.length;i++){
				if(arrayStr[i] == id) arrayStr.splice(i,1);
			}
			asds = arrayStr.join();
			$('#ecologys').val(asds);
		}
	}else if(type == 'leisure'){
		peoples = $('#leisures').val();

		if($('#lisChick'+id).val() == 0){
			// 原本沒點變有點
			$('#leiText'+id).css('color','hsl(54, 100%, 50%)');
			$('#lisChick'+id).val(1);
			// 增加點選的對象
			if(peoples == ''){
				$('#leisures').val(id);
			}else{
				$('#leisures').val(peoples+','+id);
			}
		}else{
			// 原本有點變沒點
			$('#leiText'+id).css('color','');
			$('#lisChick'+id).val(0);
			// 刪除點選的對象
			arrayStr = peoples.split(",");
			for(var i = 0;i< arrayStr.length;i++){
				if(arrayStr[i] == id) arrayStr.splice(i,1);
			}
			asds = arrayStr.join();
			$('#leisures').val(asds);
		}
	}else if(type == 'topic'){
		peoples = $('#topics').val();

		if($('#tisChick'+id).val() == 0){
			// 原本沒點變有點
			$('#topicText'+id).css('color','hsl(54, 100%, 50%)');
			$('#tisChick'+id).val(1);
			// 增加點選的對象
			if(peoples == ''){
				$('#topics').val(id);
			}else{
				$('#topics').val(peoples+','+id);
			}
		}else{
			// 原本有點變沒點
			$('#topicText'+id).css('color','');
			$('#tisChick'+id).val(0);
			// 刪除點選的對象
			arrayStr = peoples.split(",");
			for(var i = 0;i< arrayStr.length;i++){
				if(arrayStr[i] == id) arrayStr.splice(i,1);
			}
			asds = arrayStr.join();
			$('#topics').val(asds);
		}
	}else if(type == 'preferential'){
		peoples = $('#preferentials').val();

		if($('#prisChick'+id).val() == 0){
			// 原本沒點變有點
			$('#prefText'+id).css('color','hsl(54, 100%, 50%)');
			$('#prisChick'+id).val(1);
			// 增加點選的對象
			if(peoples == ''){
				$('#preferentials').val(id);
			}else{
				$('#preferentials').val(peoples+','+id);
			}
		}else{
			// 原本有點變沒點
			$('#prefText'+id).css('color','');
			$('#prisChick'+id).val(0);
			// 刪除點選的對象
			arrayStr = peoples.split(",");
			for(var i = 0;i< arrayStr.length;i++){
				if(arrayStr[i] == id) arrayStr.splice(i,1);
			}
			asds = arrayStr.join();
			$('#preferentials').val(asds);
		}
	}
}
function deleteSelectInfo(type){
	if(type == 'price'){
		$('#minPrice').val(0);
		$('#maxPrice').val(25000);
		choose_other();
	}else if(type == 'hotel'){
		$('#topics').val('');
		$('#leisures').val('');
		$('#ecologys').val('');
		choose_other();
	}else{
		$('#'+type+'s').val('');
		choose_other();
	}
}
function toSearchType(type) {
	if (type == 'map') location.href = '/booking/<?= $areaCode ?>/' + page_params + '&type=map';
	else location.href = '/booking/<?= $areaCode ?>/' + page_params;
}
function selPriceOrder() {
	$("#selectPriceOrder").val($('#sortSelect').val());
	location.href = '/booking/<?= $areaCode ?>/' + page_params + '&selectPriceOrder=' + $('#sortSelect').val();
}
<?php if($type == "map"){ ?>
var map;
var myIcon = '/../../web/img/meicon.png';
var haveRoomIcon = '/../../web/img/bnb.png';
var noRoomIcon = '/../../web/img/bnb_off.fw.png';
var activityIcon = '/../../web/img/activity.png';
var viewpointIon = '/../../web/img/viewpoint.png';
var foodIcon = '/../../web/img/food.png';
var gitIcon = '/../../web/img/git.png';
var homeStayAry = [];
var fullHomeStayAry = [];
var activityAry = [];
var viewpointAry = [];
var foodAry = [];
var gitAry = [];
function map_init() {
	var latlng = new google.maps.LatLng(<?= $home_stay_gc['lat'] ?>, <?= $home_stay_gc['lng'] ?>);
	var myOptions = {
	    	zoom: 13,
	    	center: latlng,
	    	mapTypeId: google.maps.MapTypeId.ROADMAP,
	    	scrollwheel: false
	};
    map = new google.maps.Map(document.getElementById("map2"), myOptions);
	<?
	    $idx = 0;
	 // 設定民宿marker
	 if (!empty($home_stay_gc['hsList'])) {
	 	foreach ($home_stay_gc['hsList'] as $hs) {
	 		$idxs = 0;
	 		$tCount = 0;
	 		$cnt = 0;
	 		foreach ($home_stay_gc['roomList'] as $item) {
	 			if ($item['hs_id'] == $hs['hs_id']) {
	 				if (intval($item['cnt']) == 0) {
	 					$idxs++;
	 				}
	 				$tCount++;
	 				$cnt = $item['cnt'];
	 			}
	 		}

			$ta_conut = 0;
			$ta_img = '';
			foreach ($home_stay_gc['tripadvisor_row'] as $tr){
				if ($tr['hs_id'] == $hs['hs_id']) {
					$ta_conut = $tr['tari_review_count'];
					$ta_img = '<img src="https://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/'.$tr['tari_average_rating'].'-33123-4.gif" style="width:118px;height:20px;"/>';
				}
			}

	 		$idx++;
	 		$icon = 'haveRoomIcon';
	 		if ($cnt == 0) $icon = 'noRoomIcon';

	 ?>
		var contentString<?php echo $idx?> =
			'<div class="popupMapPrev" >'
			+ '<img src="<?php echo get_config_image_url(), 'home_stay/', $hs['hs_id'], '/', $hs['hs_main_photo'], '_middle.jpg'?>" alt="">'
			+ '<section class="pointInfo">'
			+ '<h1 class="pointName"><?php echo preg_replace("/'/", "\\'", $hs['hs_name'])?></h1>'
			+ '<h2 >'
			+ '<p class="location"><i class="fa fa-map-marker" ></i><span ><?= $hs['location'] ?></span></p>'
			+ '<p class="favorite"><i class="fa fa-heart" ></i><span ><?= $hs['favorite_count']?></span></p>'
			+ '<p class="viewCount"><i class="fa fa-eye" ></i><span ><?= $hs['click_count'] ?></span></p>'
			+ '</h2>'
			+ '<h3>'
			+ '<span><?= $currency_code ?></span>'
			+ '<span class="cost"><?= number_format($hs['min_price']) ?></span>'
			+ '<span>起</span>'
			+ '</h3>'
			<?php if($ta_img != ''){ ?>
			+ '<a href="javascript:void(0)" class="tripadvisorLogoMap">'
			+ '<?= $ta_img ?>'
			+ '<h2>'
			+ '<span class="count"><?= $ta_conut ?></span>'
			+ '<span>則評論</span>'
			+ '</h2>'
			+ '</a>	'
			<? } ?>
			+ '</section>'
			+ '</div>';

	 	var infowindow<?php echo $idx?> = new google.maps.InfoWindow({content: contentString<?php echo $idx?>});
	 	var marker<?php echo $idx?> = new google.maps.Marker({
	 		position: new google.maps.LatLng(<?php echo $hs['gc_latitude']?>,<?php echo $hs['gc_longitude']?>),
	 		title: '<?php echo preg_replace("/'/", "\\'", $hs['hs_name'])?>',
	 		icon: <?php echo $icon?>,
	 	});
	 	google.maps.event.addListener(marker<?php echo $idx?>, 'mouseover', function() {infowindow<?php echo $idx?>.open(map,marker<?php echo $idx?>);});
	 	google.maps.event.addListener(marker<?php echo $idx?>, 'mouseout', function() {infowindow<?php echo $idx?>.close();});
	 	google.maps.event.addListener(marker<?php echo $idx?>, 'click', function() {window.location.href = '/booking/<?= $areaCode ?>/<?= $hs['hs_id']?>/';});
	 	google.maps.event.addListener(infowindow<?php echo $idx?>, 'domready', function() {
	  	   // Reference to the DIV which receives the contents of the infowindow using jQuery
	  	   var iwOuter = $('.gm-style-iw');

	  	   /* The DIV we want to change is above the .gm-style-iw DIV.
	  	    * So, we use jQuery and create a iwBackground variable,
	  	    * and took advantage of the existing reference to .gm-style-iw for the previous DIV with .prev().
	  	    */
	  	   var iwBackground = iwOuter.prev();

	  	   // Remove the background shadow DIV
	  	   iwBackground.children(':nth-child(2)').css({'display' : 'none'});

	  	   // Remove the white background DIV
	  	   iwBackground.children(':nth-child(4)').css({'display' : 'none'});
	  	});
	 	marker<?php echo $idx?>.setMap(map);
	 <?php }}?>

	 <?php
	// 設定美食marker
	if (!empty($home_stay_gc['contentList'])) {
		foreach ($home_stay_gc['contentList'] as $key => $ss) {
			$idx++;
			if ($ss['tc_type'] == 7) $icon = 'foodIcon';
			if ($ss['tc_type'] == 8) $icon = 'viewpointIon';
			if ($ss['tc_type'] == 12 || $ss['tc_type'] == 15) $icon = 'activityIcon';
			if ($ss['tc_type'] == 82) $icon = 'gitIcon';
	?>
		var contentString<?php echo $idx?> =
			'<div class="popupMapPrev" >'
			+ '<img src="<?php echo get_config_image_url(), 'home_stay/', $ss['tc_id'], '/', $ss['tc_main_photo'], '_middle.jpg'?>" alt="" onerror="javascript:this.src=\'/../../web/img/no-pic.jpg\';">'
			+ '<section class="pointInfo">'
			+ '<h1 class="pointName"><?php echo preg_replace("/'/", "\\'", $ss['tc_name'])?></h1>'
			+ '<h2 >'
			+ '<p class="location"><i class="fa fa-map-marker" ></i><span ><?= $home_stay_gc['content_location'][$key] ?></span></p>'
			+ '<p class="favorite"><i class="fa fa-heart" ></i><span ><?= $ss['tc_day_cnt_click']?></span></p>'
			+ '<p class="viewCount"><i class="fa fa-eye" ></i><span ><?= $ss['tc_collect_total']?></span></p>'
			+ '</h2>'
			+ '</section>'
			+ '</div>';
		var infowindow<?php echo $idx?> = new google.maps.InfoWindow({content: contentString<?php echo $idx?>});
		var marker<?php echo $idx?> = new google.maps.Marker({
			position: new google.maps.LatLng(<?php echo $ss['gc_latitude']?>,<?php echo $ss['gc_longitude']?>),
			title: '<?php echo preg_replace("/'/", "\\'", $ss['tc_name'])?>',
			icon: <?php echo $icon?>
		});
	<?php
	if ($ss['tc_type'] == 7) echo 'foodAry.push(marker', $idx, ');';$icon = 'foodIcon';
	if ($ss['tc_type'] == 8) echo 'viewpointAry.push(marker', $idx, ');';
	if ($ss['tc_type'] == 12 || $ss['tc_type'] == 15) echo 'activityAry.push(marker', $idx, ');';
	if ($ss['tc_type'] == 82) echo 'gitAry.push(marker', $idx, ');';

	?>
		google.maps.event.addListener(marker<?php echo $idx?>, 'mouseover', function() {infowindow<?php echo $idx?>.open(map,marker<?php echo $idx?>);});
		google.maps.event.addListener(marker<?php echo $idx?>, 'mouseout', function() {infowindow<?php echo $idx?>.close();});
	 	google.maps.event.addListener(infowindow<?php echo $idx?>, 'domready', function() {
		  	   // Reference to the DIV which receives the contents of the infowindow using jQuery
		  	   var iwOuter = $('.gm-style-iw');

		  	   /* The DIV we want to change is above the .gm-style-iw DIV.
		  	    * So, we use jQuery and create a iwBackground variable,
		  	    * and took advantage of the existing reference to .gm-style-iw for the previous DIV with .prev().
		  	    */
		  	   var iwBackground = iwOuter.prev();

		  	   // Remove the background shadow DIV
		  	   iwBackground.children(':nth-child(2)').css({'display' : 'none'});

		  	   // Remove the white background DIV
		  	   iwBackground.children(':nth-child(4)').css({'display' : 'none'});
		  	});
		//google.maps.event.addListener(marker<?php echo $idx?>, 'click', function() {window.location.href = '';});
		//marker<?php echo $idx?>.setMap(map);
	<?php }}?>

}
function triMarker(obj, type) {
	// 打開
	if (obj == 0){
		addMarkers(type);
	}
	// 關閉
	else{
		clearMarkers(type);
	}
}

function addMarkers(type) {
	if (type == 'activity') {
		$('#activity').attr('onclick', 'triMarker(1,"activity")');
		for (i in activityAry) activityAry[i].setMap(map);
	}
	else if (type == 'scenic') {
		$('#scenic').attr('onclick', 'triMarker(1,"scenic")');
		for (i in viewpointAry) viewpointAry[i].setMap(map);
	}
	else if (type == 'souvenir') {
		$('#souvenir').attr('onclick', 'triMarker(1,"souvenir")');
		for (i in placeAry) placeAry[i].setMap(map);
	}
	else if (type == 'food') {
		$('#food').attr('onclick', 'triMarker(1,"food")');
		for (i in foodAry) foodAry[i].setMap(map);
	}
}

function clearMarkers(type) {
	if (type == 'activity') {
		$('#activity').attr('onclick', 'triMarker(0,"activity")');
		for (i in activityAry) activityAry[i].setMap(null);
	}
	else if (type == 'scenic') {
		$('#scenic').attr('onclick', 'triMarker(0,"scenic")');
		for (i in viewpointAry) viewpointAry[i].setMap(null);
	}
	else if (type == 'souvenir') {
		$('#souvenir').attr('onclick', 'triMarker(0,"souvenir")');
		for (i in placeAry) placeAry[i].setMap(null);
	}
	else if (type == 'food') {
		$('#food').attr('onclick', 'triMarker(0,"food")');
		for (i in foodAry) foodAry[i].setMap(null);
	}
}
<?php } ?>

function add_favorite(convas, ref_type, ref_id) {
	if(!is_login) {
		show_popup_login();
		return;
	}
	var p = {};
    p.func = 'add_favorite';
    p.user_id = '<?= $serialId ?>';
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
    p.user_id = '<?= $serialId ?>';
    p.items = remove_items;
    console.log(p);
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
</script>