<?php
	require_once __DIR__ . '/../../config.php';
	$travel_plan_service = new travel_plan_service();
	$author_service = new author_service();
	$tripitta_web_service = new tripitta_web_service();
	$gc_dao = Dao_loader::__get_geographical_coordinates_dao();
	$tripitta_api_client_service = tripitta_api_client_service::__get_instance(tripitta_api_client_service::SITE_TRIPITTA_WEB_TW);
	$track_content_view_dao = Dao_loader_tripitta::__get_track_content_view_dao();
	$area = Dao_loader::__get_area_dao();
	$home_stay_dao = Dao_loader::__get_home_stay_dao();
	$favorite_dao = Dao_loader::__get_user_favorite_dao();
	$count_dao = Dao_loader::__get_home_stay_counter_dao();
	$taiwan_content_service = new taiwan_content_service();
	$db_reader_travel = Dao_loader::__get_checked_db_reader();
	$tripitta_homestay_service = new tripitta_homestay_service();
	$home_stay_room_price_dao = Dao_loader::__get_home_stay_room_price_dao();
	$geographical_coordinates_dao = Dao_loader::__get_geographical_coordinates_dao();
	$tp_id = get_val('tp_id');
	$day = !empty($_REQUEST['day']) ? $_REQUEST['day'] : 1;
	$url = 'http://img.ezding.com.tw';
	$login_user_data = $tripitta_web_service->check_login();
	$user_id = !empty($login_user_data) ? $login_user_data['serialId'] : 0;
	$service_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	// 行程遊記主擋
	$result = $travel_plan_service -> get_plan_by_plan_id($tp_id);
	$plan_list = $result["plan"];
	$title = $plan_list['tp_title']." - 行程遊記 - Tripitta 旅必達";
	if(empty($plan_list) || $plan_list['tp_days'] < $day) {
		alertmsg("查無行程資訊!!", '/');
		exit();
	}
	// tag
	$tag_list = $travel_plan_service->get_plan_tag_by_plan_id($tp_id);
	$tag_row = explode(',', $tag_list['content_tag_names']);
	// 行前準備 + 交通安排 + 消費預算
	$plan_detail_list = $travel_plan_service->get_plan_detail_by_plan_id($tp_id, 1);
	// 作者
	$author_list = array();
	if(!empty($plan_list)){
		$author_list = $author_service->get_author_by_author_id($plan_list['tp_author_id']);
		$article_list = $author_service->get_article_by_author_id($plan_list['tp_author_id']);
		$pre_row = array();
		$next_row = array();
		if(count($article_list) > 1) {
			foreach ($article_list as $k => $v){
				if($tp_id == $v['tp_id']) {
					if($k > 0) array_push($pre_row, $article_list[$k-1]);
					if($k < count($article_list)-1) array_push($next_row, $article_list[$k+1]);
				}
			}
		}
	}
	// D!、D2、D3...
	$plan_detail_list2 = $travel_plan_service -> get_plan_detail_by_plan_id($tp_id, 2);
	// 組行程
	$plan_row = array();
	for ($i=0;$i< $plan_list['tp_days'];$i++){
		if($i+1 == $day) {
			$plan_row[$i] = array();
			foreach ($plan_detail_list2 as $pdl){
				if($i+1 == $pdl['tpd_day']) array_push($plan_row[$i], $pdl);
			}
		}
	}
	// 總共幾站
	$total_count = 0;
	foreach ($plan_row as $p) {
		foreach ($p as $k => $pr) {
			if(!empty($pr["tpd_title"])) {
				$total_count++;
			}
		}
	}
	$idex = 0;
	$lat = null;
	$lng = null;
	$keyWord = "";
	$other_plan = array();
	foreach ($plan_row as $p) {
		foreach ($p as $k => $pr) {
			if(!empty($pr['tpd_title'])){
				$idex ++;
			}
			if ($idex == 1) {
				// 第一站的經緯度
				$keyWord = $pr['tpd_title'];
				$ref_category = $pr["tpd_ref_category"];
				$ref_id = $pr["tpd_ref_id"];
				if($ref_category == 10) {
					$gc_row = $gc_dao ->findByCategoryAndRefIds('home_stay', $ref_id);
				}else {
					$gc_row = $gc_dao ->findByCategoryAndRefIds('taiwan.content', $ref_id);
				}
				if(!empty($gc_row)) {
					$lat = $gc_row[0]['gc_latitude'];
					$lng = $gc_row[0]['gc_longitude'];
				}
				break;
			}
		}
	}
	$idex = 0;
	foreach ($plan_row as $p) {
		foreach ($p as $k => $pr) {
			if(!empty($pr['tpd_title'])){
				$idex ++;
			}
			if ($idex == 1) {

			} else {
				if (!empty($pr['tpd_title'])){
					$other_plan[] = $pr;
				}
			}
		}
	}
	$content_row = $travel_plan_service -> find_city_by_tp_id($tp_id);
	$favorite_list = array();
	if(!empty($login_user_data)) {
		$user_favorite_type_ids = $tripitta_web_service->get_user_favorite_type_ids('travel_plan');
		$favorite_list = $tripitta_web_service->find_user_favorite_by_user_id_and_ref_type_ids($login_user_data["serialId"], $user_favorite_type_ids);
	}
	$favorite_class = "fa-heart-o";
	foreach($favorite_list as $favorite_row) {
		if($favorite_row["uf_type"] == 5 && $favorite_row["uf_home_stay_id"] == $tp_id) {
			$favorite_class = "fa-heart";
			break;
		}
	}
	$city_ids = array();
		if(!empty($content_row)){
		foreach ($content_row as $r){
			array_push($city_ids, $r['tc_city_id']);
		}
		$city_ids = implode(",", $city_ids);
	}
	$content_info_row = array();
	if(!empty($city_ids)){
		$content_info_row = $travel_plan_service->find_content_by_city_ids($city_ids);
	}
	// 紀錄view count
	if (!empty($tp_id)){
		$track_count_row = $track_content_view_dao -> load(5, $tp_id);
		if (empty($track_count_row)) {
			$data = array('type_id'=>5, 'ref_id'=>$tp_id);
			$tripitta_api_client_service ->add_view_count($data);
			// 紀錄log
			$item = array();
			$item["tcv_folder_id"] = 5;
			$item["tcv_ref_id"] = $tp_id;
			$item["tcv_src_ip"] = get_remote_ip();
			$item["tcv_create_time"] = date('Y-m-d H:i:s');
			$track_content_view_dao -> save($item);
		} else {
			$a = $track_count_row['tcv_create_time'];
			$b = strftime(date("Y-m-d H:i:s")); //現在時間
			$a = date("Y-m-d H:i:s", strtotime($a.'+30 min'));
			$diff =  strtotime($b) - strtotime($a); //單位秒
			if($diff > 0 || get_remote_ip() != $track_count_row['tcv_src_ip']){
				$data = array('type_id'=>5, 'ref_id'=>$tp_id);
				$tripitta_api_client_service ->add_view_count($data);
				// 紀錄log
				$item = array();
				$item["tcv_folder_id"] = 5;
				$item["tcv_ref_id"] = $tp_id;
				$item["tcv_src_ip"] = get_remote_ip();
				$item["tcv_create_time"] = date('Y-m-d H:i:s');
				$track_content_view_dao -> save($item);
			}
		}
	}
	$type = 8;
	$taiwan_content = $taiwan_content_service->find_scenic_content($type, "", "", $keyWord);
	if (empty($taiwan_content)) {
		$type = 7;
		$taiwan_content = $taiwan_content_service->find_scenic_content($type, "", "", $keyWord);
	}
	if (isset($taiwan_content[0])) {
		$tc_id = $taiwan_content[0]['tc_id'];
	} elseif ($ref_category == 10) {
		$home_stay_row = $home_stay_dao->loadHfHomeStay($ref_id);
		if (isset($home_stay_row['hs_area_id'])) {
			$areaRow = $area->loadHfArea($home_stay_row['hs_area_id']);
			$content_list[] = array("folder" => 10, "id" => $ref_id);
			$content_detail_list = $tripitta_web_service->find_valid_taiwan_content_for_location_home($content_list);
		}
	}
	if (!empty($tc_id)) {
		$content_row = $taiwan_content_service->get_scenic_content_by_id($tc_id);
		// 地圖上要顯示的
		$contentList = $geographical_coordinates_dao->find_content_by_lat_and_log($lat, $lng, 0.1);
		$hsList = $geographical_coordinates_dao->findAllowSellByLatitudeAndLongitude($lat, $lng, 0.1);
		$currency_code = NULL;
		$currency_id = $tripitta_web_service->get_display_currency();
		// 取得匯率
		if (1 == $currency_id) {
			$currency_code = 'NTD';
		}
		else {
			$exchange = $tripitta_homestay_service->get_exchange_by_currency_id($currency_id);
			$currency_code = $exchange['cr_code'];
		}
	} else {
		$contentList = $geographical_coordinates_dao->find_content_by_lat_and_log($lat, $lng, 0.1);
		$hsList = $geographical_coordinates_dao->findAllowSellByLatitudeAndLongitude($lat, $lng, 0.1);
	}
?>
<!Doctype html>
<html lang="zh-Hant">

<head>
	<? include __DIR__ . "/../common/head.php"; ?>
	<meta property="og:title" content="<?= $plan_list['tp_title']?>" />
	<meta name="description" content="<?= $plan_list['tp_foreword'] ?>" />
	<meta property="og:image" content="<?= $result['cover_image']?>" />
	<link rel="stylesheet" type="text/css" href="/web/pages/trip/css/frame.css">
	<link rel="stylesheet" type="text/css" href="/web/pages/trip/css/page.css">
	<link rel="stylesheet" type="text/css" href="/web/css/main.css?01121536">
	<link rel="stylesheet" href="/web/pages/trip/css/home_stay.css"  type="text/css"/>
	<style>
	i.fa.fa-heart {
	    color: #ff8c7a;
	}
	.popupMapPrev {
	    width: 300px;
	    background-color: white;
	    box-shadow: 0 0 10px gray;
	    position: relative;
	}
	.popupMapPrev img {
	    width: 300px;
	    height: 206px;
	}
	.popupMapPrev .pointInfo {
	    padding: 20px;
	    box-sizing: border-box;
	    display: flex;
	    flex-direction: column;
	    align-items: center;
	}
	.pointInfo h2 {
	    width: 100%;
	    margin-bottom: 12px;
	    display: flex;
	    font-size: 0.875rem;
	}
	.pointInfo h2 * {
	    font-size: 0.875rem;
	    color: #bababa;
	}

	.pointInfo h2 p:nth-of-type(1), .pointInfo h2 p:nth-of-type(2) {
		margin-right: 15px;
	}

	.tripadvisorLogoMap {
	    width: 118px;
	    height: 40px;
	    margin: 0 auto;
	    text-decoration: none;
	    display: block;
	}
	.tripadvisorLogoMap h2 {
	    margin-top: 2px;
	    text-align: center;
	    display: flex;
	    justify-content: center;
	}
	.pointName {
	    align-self: flex-start;
	    font-size: 1.125rem;
	    margin-bottom: 6px;
	}
	.gm-style-iw +div {
	    width: 24px !important;
	    height: 24px !important;
	    left: 320px !important;
	    top: 15px !important;
	    background: url("/web/img/member-close.jpg") #ffe500;
	    background-position: -1038px -257px;
	    opacity: 1 !important;
	    z-index: 999;
	}
	.gm-style-iw +div > img{
	    display:none;
	}
	.gm-style-iw {
		width: 300px !important;
		top: 15px !important;
		left: 20px !important;
		background-color: #fff;
		box-shadow: rgba(0, 0, 0, 0.6) 0px 1px 6px;
		z-index: -1;
	}
	</style>
	<title><?= $title ?></title>
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<!--  <script src="../../js/embed.js"></script> -->
	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
	<!-- Add fancyBox main JS and CSS files -->
	<script src="/web/pages/trip/js/jquery.easing.1.3.js"></script>
	<script src="/web/pages/trip/js/jquery.scrollTo.min.js"></script>
	<!-- //author_area -->
	<script src="/web/pages/trip/js/actions.js"></script>
	<script>
	$(function() {
		$('.plan-point li a').on('click', function(e) {
			e.preventDefault();
			var href = $(this).attr('href');
			$.scrollTo(href, 500);
			return false;
		});
		//get #Fluid position: top value
		var value = $('#footer').offset().top - $('#Fluid').height() - 120;
		$(window).on('scroll', function() {
			if($(this).scrollTop() > 1111) {
				if ($(this).scrollTop() < value)
					$('#Fluid').css({
						position: 'fixed',
						top: '20px'
					});
				else
					$('#Fluid').removeAttr('style');
			}else{
				$('#Fluid').removeAttr('style');
			}
		}).scroll();

        $("a[href=#Prepare]").click(function() {
            $("html,body").animate({
              scrollTop:$("#Prepare").offset().top
            }, "show");
            return false;
        });

        $("a[href=#Traffic]").click(function() {
            $("html,body").animate({
              scrollTop:$("#Traffic").offset().top
            }, "show");
            return false;
        });

        $("a[href=#Budget]").click(function() {
            $("html,body").animate({
              scrollTop:$("#Budget").offset().top
            }, "show");
            return false;
        });

        <?php for($i = 1;$i <= $total_count;$i++){ ?>
	        $("a[href=#step<?= $i ?>]").click(function() {
	            $("html,body").animate({
	              scrollTop:$("#step<?= $i ?>").offset().top
	            }, "show");
	            return false;
	        });
        <?php } ?>

        var desc = $("meta[name=description]").attr("content");
		$("#fbShare").click(function() {
			var p = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(location.href);
			window.open(p, "FB share", "height=450, width=640, toolbar=no, menubar=no, scrollbars=no, resizable=yes,location=no, status=no")
		})

		$("#twitterShare").click(function() {
			var p = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(desc) + '&url=' + encodeURIComponent(location.href);
			window.open(p, "twitter share", "height=450, width=640, toolbar=no, menubar=no, scrollbars=no, resizable=yes,location=no, status=no")
		})

		$("#weiboShare").click(function() {
			var p = 'http://v.t.sina.com.cn/share/share.php?title=' + encodeURIComponent(desc) + '&url=' + encodeURIComponent(location.href);
			window.open(p, "weibo share", "height=450, width=640, toolbar=no, menubar=no, scrollbars=no, resizable=yes,location=no, status=no")
		})

		$("#weixinShare").click(function() {
			var Qcode = "https://chart.googleapis.com/chart?cht=qr&chl=" + encodeURIComponent(location.href) + "&chs=180x180&choe=UTF-8&chld=L|2"
			$(".weixinQcode img").attr({ src: Qcode});
			$(".weixinQcode").fadeIn(300);
			$(".overlay").css({ display: "block" });
		})

		//closed
		$(".weixinQcode .fa").click(function() {
			$(".weixinQcode").hide();
			$(".overlay").css({ display: "none" });
		});

		$('#btn_add_remove_favorite').click( function() { add_or_remove_favorite(); });

		<?php if ($lat != "" && $lng != "") { ?>
		map_init();
		<?php } ?>

		$(".mockupMap").hide();

		var width = screen.width/1.03;
		var height = screen.height/1.35;
		$("#map").css('width', width);
		$("#map").css('height', height);
		<?php
			if ($lat != "" && $lng != "") {
		?>
		$(".small-map").click(function() {
			$(".mockupMap").show('1000');
			map_init();
		});
		<?php } ?>

		$(".closeButton").click(function() {
			$(".mockupMap").hide();
		});
	});

	function changeDate(){
		location.href = '/trip/<?= $tp_id ?>/?day='+$('#daylist').val();
	}

	function changeDate_2(){
		location.href = '/trip/<?= $tp_id ?>/?day='+$('#daylist_2').val();
	}

	var curIndex = 0,  //当前index
	imgLen = <?= ceil($plan_list['tp_days']/3) ?>;  //图片总数
	//左箭头点击处理
	function clickPrev() {
	    //根据curIndex进行上一个图片处理
	    curIndex = (curIndex > 0) ? (--curIndex) : (imgLen - 1);
	    changeTo(curIndex);
	}

	//右箭头点击处理
	function clicknext(){
	     curIndex = (curIndex < imgLen - 1) ? (++curIndex) : 0;
	     changeTo(curIndex);
	}

	function changeTo(num){
	    var goLeft =  num * 100;
	    $(".imgList").animate({left: "-" + goLeft + "px"},500);
	}

	function add_or_remove_favorite() {
		<?php if(empty($login_user_data)){ ?>
		show_popup_login();
		return;
		<?php } ?>
		var ref_id = $('#btn_add_remove_favorite').attr('data-id');
		var add = $('#favorite').hasClass('fa-heart-o') ? 0 : 1;
	    if (add == 1) {
	    	add_favorite('#favorite', 5, ref_id);
	    } else {
	    	remove_favorite('#favorite', 5, ref_id);
	    }

	}

	function add_favorite(convas, ref_type, ref_id) {
		<?php if(empty($login_user_data)){ ?>
		show_popup_login();
		return;
		<?php } ?>
		var p = {};
	    p.func = 'add_favorite';
	    p.user_id = <?= $user_id ?>;
	    p.ref_type = ref_type;
	    p.ref_id = ref_id;
	    $.post("/web/ajax/ajax.php", p, function(data) {
	        if(data.code == '9999'){
	            alert(data.msg);
	        } else {
	            // 顯示註冊完成並顯示註冊完成popup window
				alert('已加至我的收藏');
				$(convas).removeClass('fa-heart-o').addClass('fa-heart');
				//location.reload();
	        }
	    }, 'json').done(function() { }).fail(function() { }).always(function() { });
	}

	function remove_favorite(convas, ref_type, ref_id) {
		<?php if(empty($login_user_data)){ ?>
		show_popup_login();
		return;
		<?php } ?>
		remove_items = [];
	    remove_items.push({'type_id':ref_type,'ref_id':ref_id});

		var p = {};
	    p.func = 'remove_user_favorite';
	    p.user_id = <?= $user_id ?>;
	    p.items = remove_items;
	    $.post("/web/ajax/ajax.php", p, function(data) {
	        if(data.code == '9999'){
	            alert(data.msg);
	        } else {
	            // 顯示註冊完成並顯示註冊完成popup window
				alert('已從我的收藏移除');
				$(convas).removeClass('fa-heart').addClass('fa-heart-o');
				//location.reload();
	        }
	    }, 'json').done(function() { }).fail(function() { }).always(function() { });
	}

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
	var mainAry = [];
	var mainInfoAry = [];
	var foodAry = [];
	var foodInfoAry = [];
	var viewpointAry = [];
	var viewpointInfoAry = [];
	var activityAry = [];
	var activityInfoAry = [];
	var giftAry = [];
	var giftInfoAry = [];
	<?php
		if ($lat != "" && $lng != "") {
	?>
	function map_init() {
		var latlng = new google.maps.LatLng(<?= $lat ?>+0.03, <?= $lng ?>);
		var myOptions = {
	    	zoom: 13,
	    	center: latlng,
	    	mapTypeId: google.maps.MapTypeId.ROADMAP,
	    	scrollwheel: false
		};
	    map = new google.maps.Map(document.getElementById("map"), myOptions);
	    <?php
			// 主content
			// 取得地區資訊
	    	$a_name = '';
			if (isset($content_row["content"]["tc_area_id"])) {
				$areaRow = $area->loadHfArea($content_row["content"]["tc_area_id"]);
			} else {
				if (!empty($home_stay_row)) {
					$areaRow = $area->loadHfArea($home_stay_row['hs_area_id']);
				}
			}
			if(!empty($areaRow)) $a_name = $areaRow["a_name"];
			// 取得tripadvisor資訊
			if (isset($tc_id)) {
				$sql = "SELECT * FROM hf_source_mapping ";
				$sql .= "INNER JOIN hf_trip_advisor_review_info ON tari_id = sm_ref_id ";
				$sql .= "WHERE sm_source_id IN (" . $tc_id . ") and sm_category = 'tripadvisor.taiwan.content' ";
				$tripadvisor_row = $db_reader_travel->executeReader($sql);
				$ta_conut = 0;
				$ta_img = '';
				$tari_id = 0;
				if(!empty($tripadvisor_row)){
					foreach ($tripadvisor_row as $tr){
						if ($tr['sm_source_id'] == $tc_id) {
							$ta_conut = $tr['tari_review_count'];
							$ta_img = '<img src="http://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/'.$tr['tari_average_rating'].'-33123-4.gif" style="width:118px;height:20px;"/>';
							$tari_id = $tr['tari_id'];
						}
					}
				}
				$linkurl = '';
				$type_str = '';
				if(7 == $content_row["content"]["tc_type"]) {
					$type_str = 'food';
				} else if(8 == $content_row["content"]['tc_type']) {
					$type_str = 'spot';
				} else if(82 == $content_row["content"]['tc_type']) {
					$type_str = 'gift';
				} else if(12 == $content_row["content"]['tc_type'] || 15 == $content_row["content"]['tc_type']) {
					$type_str = 'event';
				}
				$linkurl = '/location/' . $type_str . '/' . $content_row["content"]['tc_id'] . '/';
				$icon = 'foodIcon';
				if ($content_row["content"]['tc_type'] == 8) $icon = 'viewpointIon';
				if ($content_row["content"]['tc_type'] == 12 || $content_row["content"]['tc_type'] == 15) $icon = 'activityIcon';
				if ($content_row["content"]['tc_type'] == 82) $icon = 'gitIcon';
				$tc_name = $content_row["content_tw"]['tc_name'];
				$map_img = get_config_image_server() . '/photos/' . (is_production() ? 'taiwan_content' : 'taiwan_content_alpha') . '/' . $content_row["content"]['tc_id']. '/'. $content_row["content"]['tc_main_photo']. '.jpg';
				$tc_collect_total = $content_row["content"]['tc_collect_total'];
				$tc_cnt_click = $content_row["content"]['tc_cnt_click'];
			} else {
				$sql = "SELECT * FROM hf_source_mapping ";
				$sql .= "INNER JOIN hf_trip_advisor_review_info ON tari_id = sm_ref_id ";
				$sql .= "WHERE sm_source_id IN (" . $ref_id . ") and sm_category = 'tripadvisor.homestay' ";
				$tripadvisor_row = $db_reader_travel->executeReader($sql);
				$ta_conut = 0;
				$ta_img = '';
				$tari_id = 0;
				if(!empty($tripadvisor_row)){
					foreach ($tripadvisor_row as $tr){
						if ($tr['sm_source_id'] == $ref_id) {
							$ta_conut = $tr['tari_review_count'];
							$ta_img = '<img src="http://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/'.$tr['tari_average_rating'].'-33123-4.gif" style="width:118px;height:20px;"/>';
							$tari_id = $tr['tari_id'];
						}
					}
				}
				$tc_id = $ref_id;
				$icon = 'haveRoomIcon';
				$tc_name = (!empty($home_stay_row)) ? $home_stay_row['hs_name'] : "";
				if (8 == $ref_category) {
					$type_str = 'spot';
					$linkurl = (!empty($areaRow)) ? '/'.$type_str.'/' . $ref_id . '/' : "";
				} else if (7 == $ref_category) {
					$type_str = 'food';
					$linkurl = (!empty($areaRow)) ? '/'.$type_str.'/' . $ref_id . '/' : "";
				} else {
					$type_str = 'booking';
					$linkurl = (!empty($areaRow)) ? '/'.$type_str.'/' . $areaRow['a_code'] . '/' . $ref_id . '/' : "";
				}
				$map_img = (!empty($home_stay_row)) ? get_config_image_server() . '/photos/travel/home_stay/' . $ref_id. '/'. $home_stay_row['hs_main_photo']. '_big.jpg' : "";
				$tc_collect_total = (!empty($content_detail_list)) ? $content_detail_list[0]['cnt_collect'] : 0;
				$tc_cnt_click = (!empty($content_detail_list)) ? $content_detail_list[0]['cnt_view'] : 0;
			}
			?>
			var contentString =
				'<div class="popupMapPrev">'
				+ '<a href="<?= $linkurl ?>"><img style="height:210px;" src="<?php echo $map_img; ?>" alt="" onerror="javascript:this.src=\'/../../web/img/no-pic.jpg\'"></a>'
				+ '<section class="pointInfo" style="background-color: #fff;margin-top: -3px;">'
				+ '<h1 class="pointName"><?php echo preg_replace("/'/", "\\'", $tc_name)?></h1>'
				+ '<h2 >'
				+ '<p class="location"><i class="fa fa-map-marker" ></i><span ><?php echo $a_name; ?></span></p>'
				+ '<p class="favorite"><i class="fa fa-heart" ></i><span ><?= $tc_collect_total?></span></p>'
				+ '<p class="viewCount"><i class="fa fa-eye" ></i><span ><?= $tc_cnt_click?></span></p>'
				+ '</h2>'
				<?php if($ta_img != ''){ ?>
				+ '<a href="javascript:open_trip_advisor_review(<?= $tari_id ?>)" class="tripadvisorLogoMap">'
				+ '<?= $ta_img ?>'
				+ '<h2>'
				+ '<span class="count"><?= $ta_conut ?></span>'
				+ '<span>則評論</span>'
				+ '</h2>'
				+ '</a>	'
				<? } ?>
				+ '</section>'
				+ '</div>';
		 	var infowindow = new google.maps.InfoWindow({content: contentString});
		 	var lat = '<?php echo $lat?>';
		 	var lng = '<?php echo $lng?>';
		 	var marker = new google.maps.Marker({
		 		position: new google.maps.LatLng(lat, lng),
		 		icon: <?= $icon ?>,
		 	});
		 	<?php
		 		echo 'mainAry.push(marker);';
		 		echo 'mainInfoAry.push(infowindow);';
		 	?>
		 	//infowindow.open(map,marker);
		 	mainInfoAry[0].open(map, mainAry[0]);
		 	google.maps.event.addListener(marker, 'click', function() {
		 		infowindow.open(map,marker);
		 		<?
		 		foreach ($contentList as $k => $hs) {
		 			if($tc_id != $hs['tc_id']) {
		 		?>
		 			infowindow<?php echo $k+1?>.close();
		 		<?
		 			}
		 		?>
		 		<? } ?>
		 		<? foreach ($hsList as $k2 => $hs) {
		 			$key = $k+1+$k2+1;
		 		?>
		 			infowindow<?php echo $key?>.close();
		 		<? } ?>
			});
		 	google.maps.event.addListener(infowindow, 'domready', function() {
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
		 	marker.setMap(map);

	 	<?php
 			// 設定content marker
 			$idx = 0;
 			if (!empty($other_plan)) {
 				foreach ($other_plan as $key => $op) {
 					$idx++;
 					// 取得地區資訊
 					$a_name = '';
 					$areaRow = $area->loadHfArea(16);
 					if(!empty($areaRow)) $a_name = $areaRow["a_name"];

 					// 取得tripadvisor資訊
 					$sql = "SELECT * FROM hf_source_mapping ";
 					$sql .= "INNER JOIN hf_trip_advisor_review_info ON tari_id = sm_ref_id ";
 					$sql .= "WHERE sm_source_id IN (" . $op["tpd_ref_id"] . ") and sm_category = 'tripadvisor.taiwan.content' ";
 					$tripadvisor_row = $db_reader_travel->executeReader($sql);

 					$ta_conut = 0;
 					$ta_img = '';
 					$tari_id = 0;
 					$latitude = '';
 					$longitude = '';
 					if(!empty($tripadvisor_row)){
 						foreach ($tripadvisor_row as $tr){
 							if ($tr['sm_source_id'] == $op['tpd_ref_id']) {
 								$ta_conut = $tr['tari_review_count'];
 								$ta_img = '<img src="https://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/'.$tr['tari_average_rating'].'-33123-4.gif" style="width:118px;height:20px;"/>';
 								$tari_id = $tr['tari_id'];
 								$myGc = $geographical_coordinates_dao->getHfGeographicalCoordinatesByCategoryAndReferenceId('taiwan.content', $op["tpd_ref_id"]);
 								$latitude = $myGc['gc_latitude'];
 								$longitude = $myGc['gc_longitude'];
 							}
 						}
 					}
 					$linkurl = '';
 					$type_str = '';
 					$tc_id = $op['tpd_ref_id'];
 					$ss = $taiwan_content_service->get_scenic_content_by_id($tc_id);
 					if (8 == $ss['content']['tc_type']) {
 						$type_str = 'spot';
 					} else if (7 == $ss['content']["tc_type"]) {
						$type_str = 'food';
					}
 					$linkurl = '/location/' . $type_str . '/' . $tc_id . '/';
 					if ($ss['content']['tc_type'] == 8) $icon = 'viewpointIon';
 					if ($ss['content']['tc_type'] == 7) $icon = 'foodIcon';
 			?>
 				var contentString<?php echo $idx?> =
 					'<div class="popupMapPrev">'
 					+ '<a href="<?= $linkurl ?>"><img style="height:210px;" src="<?php echo get_config_image_server() . '/photos/' . (is_production() ? 'taiwan_content' : 'taiwan_content_alpha') . '/' . $tc_id. '/'. $ss['content']['tc_main_photo']. '.jpg'?>" alt="" onerror="javascript:this.src=\'/../../web/img/no-pic.jpg\';"></a>'
 					+ '<section class="pointInfo" style="background-color: #fff;margin-top: -3px;">'
 					+ '<h1 class="pointName"><?php echo preg_replace("/'/", "\\'", $ss['content_tw']['tc_name'])?></h1>'
 					+ '<h2 >'
 					+ '<p class="location"><i class="fa fa-map-marker" ></i><span ><?php echo $a_name; ?></span></p>'
 					+ '<p class="favorite"><i class="fa fa-heart" ></i><span ><?= $ss['content']['tc_collect_total']?></span></p>'
 					+ '<p class="viewCount"><i class="fa fa-eye" ></i><span ><?= $ss['content']['tc_cnt_click']?></span></p>'
 					+ '</h2>'
 					<?php if($ta_img != ''){ ?>
 					+ '<a href="javascript:open_trip_advisor_review(<?= $tari_id ?>)" class="tripadvisorLogoMap">'
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
 	 				<?php if ($latitude != "" && $longitude != "") { ?>
 					position: new google.maps.LatLng(<?php echo $latitude; ?>,<?php echo $longitude; ?>),
 					<?php } ?>
 					title: '<?php echo preg_replace("/'/", "\\'", $ss['content_tw']['tc_name'])?>',
 					icon: <?php echo $icon?>
 				});
	 			<?php
	 				if ($ss['content']['tc_type'] == 7) {
	 					echo 'foodAry.push(marker', $idx, ');';
	 					echo 'foodInfoAry.push(infowindow', $idx, ');';
	 				}
	 				if ($ss['content']['tc_type'] == 8) {
	 					echo 'viewpointAry.push(marker', $idx, ');';
	 					echo 'viewpointInfoAry.push(infowindow', $idx, ');';
	 				}
	 				if ($ss['content']['tc_type'] == 12 || $ss['content']['tc_type'] == 15) {
	 					echo 'activityAry.push(marker', $idx, ');';
	 					echo 'activityInfoAry.push(infowindow', $idx, ');';
	 				}
	 				if ($ss['content']['tc_type'] == 82) {
	 					echo 'giftAry.push(marker', $idx, ');';
	 					echo 'giftInfoAry.push(infowindow', $idx, ');';
	 				}
	 			?>
 				google.maps.event.addListener(marker<?php echo $idx?>, 'click', function() {
					infowindow.close();
					mainInfoAry[0].close(map, mainAry[0]);
 			 		infowindow<?php echo $idx?>.open(map,marker<?php echo $idx?>);
 			 		<?
 			 			foreach ($other_plan as $k2 => $hs) {
	 			 			$key = $k2+1;
	 			 			if ($idx != $key) {
 			 		?>
 			 					infowindow<?php echo $key?>.close();
 			 					for (i = 0; i < viewpointInfoAry.length; ++i) {
 	 			 					var key = i+1;
 			 						if (key != (<?php echo $idx?>+1) && key != (<?php echo $idx?>+1)*2) {
 			 							viewpointInfoAry[key].close(map, viewpointAry[key]);
 			 						}
 			 					}
 			 		<?
							}
						}
					?>
 				});
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
 			  	console.log(map);
 				marker<?php echo $idx?>.setMap(map);
 			<?php }} ?>
	}
	<?php } ?>

	function triMarker(idx, tpd_ref_category) {
		if (idx == 1) {
			mainInfoAry[0].open(map, mainAry[0]);
			for (i = 0; i < viewpointInfoAry.length; ++i) {
				viewpointInfoAry[i].close(map, viewpointAry[i]);
			}
			for (i = 0; i < foodInfoAry.length; ++i) {
				foodInfoAry[i].close(map, foodAry[i]);
			}
		} else {
			mainInfoAry[0].close(map, mainAry[0]);
			if (tpd_ref_category == 8) {
				if (idx > 3) {
					var foodInfoAry_length = foodInfoAry.length - 1;
					idx = idx - foodInfoAry_length;
				}
				viewpointInfoAry[idx-2].open(map, viewpointAry[idx-2]);
				for (i = 0; i < viewpointInfoAry.length; ++i) {
					if (i != (idx-2)) {
						viewpointInfoAry[i].close(map, viewpointAry[i]);
					}
				}
				for (i = 0; i < foodInfoAry.length; ++i) {
					foodInfoAry[i].close(map, foodAry[i]);
				}
			} else if (tpd_ref_category == 7) {
				foodInfoAry[idx-3].open(map, foodAry[idx-3]);
				for (i = 0; i < foodInfoAry.length; ++i) {
					if (i != (idx-3)) {
						foodInfoAry[i].close(map, foodAry[i]);
					}
				}
				for (i = 0; i < viewpointInfoAry.length; ++i) {
					viewpointInfoAry[i].close(map, viewpointAry[i]);
				}
			}
		}
	}

	function open_trip_advisor_review(ta_id) {
		window.open('http://www.tripadvisor.com/WidgetEmbed-cdspropertydetail?locationId=' + ta_id + '&partnerId=CB56EED944AF4459B7E92BBF9B292AC6&lang=zh_TW&allowMobile&display=true', 'trip_advisor', 'width=600, location=0, menubar=0, resizable=0, scrollbars=1, status=0, titlebar=0, toolbar=0');
	}
	</script>
</head>
<body class="main article-container">
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="mockupMap">
		<div class="mockupContainer">
			<article class="map-content" id="map" style="position: relative;overflow: hidden;">
			</article>
			<div class="closeButton">X</div>
			<div class="stopListFrame" style="background-color:#FFF">
				<div class="stopListTitle">
					<h1><?php echo $plan_list['tp_title']; ?></h1>
					<h2><?php echo $plan_list['tp_subtitle']; ?></h2>
				</div>
				<div class="stopListDay">
					<select class="stopListDaySelector" id="daylist_2" onchange="changeDate_2()">
						<?php for ($i=0;$i< $plan_list['tp_days'];$i++){ ?>
							<option value="<?= $i+1 ?>" <?php if($day == $i+1){?>selected<?php } ?>>Day <?= $i+1 ?></option>
						<?php } ?>
					</select>
					<i class="fa fa-angle-down"></i>
				</div>
				<div class="stopList">
					<ul class="List">
						<?php
						$idex2 = 0;
						foreach ($plan_row as $p) {
							foreach ($p as $pr) {
								if(!empty($pr['tpd_title'])) {
									$idex2 ++;
						?>
							<li onclick="triMarker('<?php echo $idex2; ?>', '<?php echo $pr['tpd_ref_category']; ?>');" style="cursor:pointer;">
								<?php if ($idex2 == 1) { ?>
								<div class="firstTag">D1</div>
								<?php } else { ?>
								<div> </div>
								<?php } ?>
								<i></i><?= $pr['tpd_title'] ?>
							</li>
						<?php
								}
							}
						}
						?>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<article class="top-container">
		<div class="container">
			<div class="itinerary_kv" style="width: 100%;height: 480px;display: block;background-image: url(<?= $result['cover_image']?>);background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">
				<div class="intro">
					<h2 class="heading">
						<span><?= $plan_list['tp_title']?></span>
					</h2>
				</div>
			</div>
			<!-- //itinerary_kv -->
		</div>
		<!-- //contaner -->
	</article>
	<!-- //top-contaner -->
	<article class="author-area">
		<div class="container">
			<div class="left">
				<div class="article-title">
					<h2><?= $plan_list['tp_subtitle']?></h2>
					<div class="status">
						<span class="category">行程遊記</span>
						<span>
							<i class="fa fa-clock-o"></i><?= date("Y-m-d",strtotime($plan_list['tp_create_time']))?></span>
						<span>
							<i class="fa fa-eye"></i><?= $plan_list['tpe_click_total']*$plan_list['tpe_click_mul']?></span>
					</div>
					<!-- //status -->
					<?php if(!empty($tag_list['content_tag_names'])){ ?>
						<ul class="tag-list">
						<?php foreach($tag_row as $v){ ?>
							<li><?= $v ?></li>
						<?php } ?>
						</ul>
					<?php }else { ?>
					<br />
					<?php } ?>
					<div class="share-wrapper">
						<a id="fbShare" class="fa fa-facebook" style="cursor:pointer"></a>
						<a id="twitterShare" class="fa fa-twitter" style="cursor:pointer"></a>
						<a id="weiboShare" class="fa fa-weibo" style="cursor:pointer"></a>
						<a id="weixinShare" class="fa fa-weixin" style="cursor:pointer"></a>
					</div>
					<!-- //share-wrapper -->
					<div class="weixinQcode">
						<div class="fa fa-times"></div>
						<h4>分享到微信朋友圈</h4>
						<img src='' alt='qr code'>
						<h5>打開微信，點擊底部的“發現”，使用“掃一掃”即可將網頁分享到我的朋友圈。</h5>
					</div>
				</div>
				<!-- //article-title -->
				<div class="main-body">
					<span class="img-collect" id="btn_add_remove_favorite" data-type="10" data-id="<?= $tp_id ?>">
						<?php if($favorite_class == "fa-heart-o"){ ?>
							<i class="fa <?= $favorite_class ?>" id="favorite" ></i>
						<?php }else{ ?>
							<i class="fa <?= $favorite_class ?>" id="favorite" ></i>
						<?php } ?>
					</span>
					<?php foreach ($plan_detail_list as $k => $pdl) {
							if($k == 0) $id = "Prepare";
							if($k == 1) $id = "Traffic";
							if($k == 2) $id = "Budget";
					?>
					<div id="<?= $id ?>" class="part">
						<?php if($k == 0){?>
						<? if(!empty($pdl['tpd_content'])){ ?><h3 class="title"><i class="fa fa-file-text-o"></i>行前準備</h3><?php } ?>
						<?php }else if($k == 1) { ?>
						<? if(!empty($pdl['tpd_content'])){ ?><h3 class="title"><i class="fa fa-plane"></i>交通安排</h3><?php } ?>
						<?php }else if($k == 2) { ?>
						<? if(!empty($pdl['tpd_content'])){ ?><h3 class="title"><i class="fa fa-money"></i>消費預算</h3><?php } ?>
						<?php } ?>
						<div class="con">
							<?= nl2br($pdl['tpd_content']) ?>
						</div>
					</div>
					<?php } ?>
					<?php
					$idex = 0; // 第幾站
					foreach ($plan_row as $p) {
						foreach ($p as $k => $pr) {
						if(!empty($pr['tpd_title'])){
							$idex ++;
						}

						$ap_avatar = null;
						if (is_dev()) {
							$path = '/photos/travel_plan_alpha/';
						} else if(is_alpha()){
							$path = '/photos/travel_plan_alpha/';
						} else {
							$path = '/photos/travel_plan/';
						}
						if(!empty($pr['tpd_image'])){
							$ap_avatar = $url.$path.$pr['tpd_plan_id'].'/data_image/'.$pr['tpd_image'];
						}
					?>
					<div class="part" id="step<?= $idex ?>">
						<?php if(!empty($pr['tpd_title'])){ ?>
						<h3 class="day-title">
							<i>D<?= $day ?></i>
							<div class="date"><?= date("Y-m-d",strtotime($plan_list['tp_begin_date']."+".($day-1)." day")); ?></div>第 <?= $idex ?> 站
							<span><?= $pr['tpd_title'] ?></span>
							<?php if($idex == 1) { ?>
								<?php if($plan_list['tp_days'] > 3) { ?>
								<a class="arrow-r" href="javascript:clicknext();">
									<i class="fa fa-angle-right"></i>
									<span class="tip r">下三天</span>
								</a>
								<?php } ?>
								<div class="days">
									<?php if($plan_list['tp_days'] > 3) { ?>
									<a class="days-arrow-l fa fa-angle-left" href="javascript:clickPrev();"></a>
									<?php } ?>
								    <!-- Swiper -->
									<div id="banner" class="swiper-wrapper"><!-- 轮播部分 -->
							            <ul class="imgList"><!-- 图片部分 -->
							            	<?php
							            	if($plan_list['tp_days'] > 3) {
							            		for ($i=0;$i< 3;$i++){
											?>
												<li><span class="swiper-slide"><a href="/trip/<?= $tp_id ?>/?day=<?= $i+1 ?>">D<?= $i+1 ?></a></span></li>
											<?php
							            		}
							            	}else if($plan_list['tp_days'] == 3) {
							            		for ($i=0;$i< $plan_list['tp_days'];$i++){
							            	?>
							            		<li><span class="swiper-slide"><a href="/trip/<?= $tp_id ?>/?day=<?= $i+1 ?>">D<?= $i+1 ?></a></span></li>
							            	<?php
							            		}
 											}else if($plan_list['tp_days'] == 2) {
							            		for ($i=0;$i< $plan_list['tp_days'];$i++){
							            	?>
							            		<li><span class="swiper-slide"><a href="/trip/<?= $tp_id ?>/?day=<?= $i+1 ?>">D<?= $i+1 ?></a></span></li>
							            	<?php
							            		}
							            	?>
												<li><span class="swiper-slide"></span></li>
							            	<?php
							            	}else if($plan_list['tp_days'] == 1) {
											?>
												<li><span class="swiper-slide"><a href="/trip/<?= $tp_id ?>/?day=1">D1</a></span></li>
												<li><span class="swiper-slide"></span></li>
												<li><span class="swiper-slide"></span></li>
											<?php
							            	}
 											?>
 											<?php for ($i=3;$i< $plan_list['tp_days'];$i++){ ?>
												<li><span class="swiper-slide"><a href="/trip/<?= $tp_id ?>/?day=<?= $i+1 ?>">D<?= $i+1 ?></a></span></li>
 											<?php } ?>
							            </ul>
									</div>
								</div>
							<?php } else { ?>
								<?php if($day < $plan_list['tp_days']) { ?>
								<a class="arrow-r" href="#step<?php echo $idex+1; ?>">
									<i class="fa fa-angle-right"></i>
									<span class="tip r">NEXT</span>
								</a>
								<?php } ?>
							<?php } ?>
						</h3>
						<?php } ?>
						<div class="con day" style="word-wrap: break-word;">
							<?php if(!empty($ap_avatar)){ ?>
							<div class="itinerary_kv" style="width: 580px;height: 328px;display: block;background-image: url(<?= $ap_avatar?>);background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">
							</div>
							<?php } ?>
							<?= nl2br($pr["tpd_content"]) ?>
						</div>
					</div>
					<?php } } ?>
					<div class="relation-info">
						<?php if(count($article_list) > 1) {?>
						<div class="top">
							<div class="cover-photo">
								<?php if ($author_list['author_row']['a_blog_url'] != "") { ?>
								<a href="<?= $author_list['author_row']['a_blog_url']?>" target="_blank">
									<img src="<?= $author_list['avatar_img']?>" alt="">
								</a>
								<?php } else { ?>
								<img src="<?= $author_list['avatar_img']?>" alt="">
								<?php } ?>
							</div>
							<?php if(!empty($pre_row)) {?>
							<a class="arrow-l" href="/trip/<?= $pre_row[0]['tp_id'] ?>/">
								<i class="fa fa-angle-left"></i>
								<span class="title"><?= $pre_row[0]['tp_title']?></span>
								<span class="tip l">上一篇</span>
							</a>
							<?php } ?>
							<?php if(!empty($next_row)) {?>
							<a class="arrow-r" href="/trip/<?= $next_row[0]['tp_id'] ?>/">
								<i class="fa fa-angle-right"></i>
								<span class="title"><?= $next_row[0]['tp_title']?></span>
								<span class="tip r">下一篇</span>
							</a>
							<?php } ?>
						</div>
						<?php } ?>
						<!-- //top -->
						<?php if(!empty($content_info_row)){ ?>
						<h3>相關資訊</h3>
						<ul class="relation-list">
							<?php foreach ($content_info_row as $cir){
								$type_str = '';
								$folder = $cir["tc_type"];
								if(7 == $folder) {
									$type_str = 'food';
								} else if(8 == $folder) {
									$type_str = 'spot';
								} else if(82 == $folder) {
									$type_str = 'gift';
								} else if(12 == $folder || 15 == $folder) {
									$type_str = 'event';
								}
								$linkurl = '/location/' . $type_str . '/' . $cir["tc_id"] . '/';

								$img = '/web/img/no-pic.jpg';
								if(!empty($cir['tc_main_photo'])) {
									$img = get_config_image_server() . '/photos/' . (is_production() ? 'taiwan_content' : 'taiwan_content_alpha') . '/' . $cir['tc_id']. '/'. $cir['tc_main_photo']. '.jpg';
								}

							?>
							<li>
								<a href="<?= $linkurl ?>">
									<img src="<?= $img ?>" alt="" style="width:200px;height:120px;" onerror="javascript:this.src='/../../web/img/no-pic.jpg';">
									<p><?= $cir["tc_name"]?></p>
								</a>
							</li>
							<?php } ?>
						</ul>
						<?php } ?>
					</div>
					<!-- //relation-info -->
				</div>
				<!-- //main-body -->
			</div>
			<!-- //left -->
			<div class="right">
				<div class="main-writer">
					<div class="cover-photo">
						<img src="<?= $author_list['avatar_img']?>" width="180" height="180" alt="">
					</div>
					<span>
						<a href="/author/<?= $author_list['author_row']['a_id']?>/" class="category">
							<?= $author_list['author_row']['a_title']?>
						</a>
					</span>
					<span class="author">
						<a href="/author/<?= $author_list['author_row']['a_id']?>/">
							<?= $author_list['author_row']['a_name']?>
						</a>
					</span>
					<?php if ($author_list['author_row']['a_blog_url'] != "") { ?>
					<span class="category">
						<a href="<?php echo $author_list['author_row']['a_blog_url']; ?>" target="_blank" style="color:blue;">
							作者部落格
						</a>
					</span>
					<?php } ?>
					<p><?= $author_list['author_row']['ap_intro']?></p>
				</div>
				<!-- //main-write -->
				<ul class="plan-point">
					<li>
						<a href="#Prepare">
							<i class="fa fa-file-text-o"></i>行前準備</a>
					</li>
					<li>
						<a href="#Traffic">
							<i class="fa fa-plane"></i>交通安排</a>
					</li>
					<li>
						<a href="#Budget">
							<i class="fa fa-money"></i>消費預算</a>
					</li>
				</ul>
				<div id="Fluid" class="fluid">
					<div class="select">
						<select name="" id="daylist" onchange="changeDate()">
							<?php for ($i=0;$i< $plan_list['tp_days'];$i++){ ?>
							<option value="<?= $i+1 ?>" <?php if($day == $i+1){?>selected<?php } ?>>Day <?= $i+1 ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="small-map" style="cursor:pointer;">
						<span class="meicon"></span>
						<span class="fa fa-expand"></span>
						<img src="http://maps.google.com/maps/api/staticmap?center=<?= $lat .','. $lng ?>&zoom=16&size=180x120&sensor=false'" alt="">
					</div>
					<ol class="stop-list">
					<?php
					$idex2 = 0;
					foreach ($plan_row as $p) {
						foreach ($p as $pr) {
							if(!empty($pr['tpd_title'])) {
								$idex2 ++;
					?>
						<li>
							<a href="#step<?= $idex2 ?>">
								<i></i><?= $pr['tpd_title'] ?>
							</a>
						</li>
					<?php
							}
						}
					}
					?>
					</ol>
					<!-- //stop-list -->
				</div>
				<!-- //fluid -->
			</div>
			<!-- //right -->
		</div>
		<!-- //contaner -->
	</article>

	<footer id="footer" class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
</body>

</html>