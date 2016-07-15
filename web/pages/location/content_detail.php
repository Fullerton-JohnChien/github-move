<?php
	require_once __DIR__ . '/../../config.php';
	$taiwan_content_service = new taiwan_content_service();
	$tripitta_web_service = new tripitta_web_service();
	$tripitta_homestay_service = new tripitta_homestay_service();
	$tripitta_api_client_service = tripitta_api_client_service::__get_instance(tripitta_api_client_service::SITE_TRIPITTA_WEB_TW);
	$geographical_coordinates_dao = Dao_loader::__get_geographical_coordinates_dao();
	$db_reader_travel = Dao_loader::__get_checked_db_reader();
	$area = Dao_loader::__get_area_dao();
	$track_content_view_dao = Dao_loader_tripitta::__get_track_content_view_dao();
	$taiwan_content_suggestion = Dao_loader::__get_taiwan_content_suggestion_dao();

	$tc_id = get_val("tc_id");

	$content_row = $taiwan_content_service -> get_scenic_content_by_id($tc_id);

	// 判斷是否有效
	if($content_row["content"]["tc_status"] != 1) {
		header("Location: https://www.tripitta.com/web/pages/adout/404.php");
		exit();
	}

	$media_row = $taiwan_content_service -> get_media_by_content_id($tc_id);
	//$tag_list = $taiwan_content_service -> get_tag_by_content_id($tc_id);
	$parent_tag_list = $taiwan_content_service->find_parent_tag_by_content_id(get_config_current_lang(), $tc_id);
	$photo_row = $taiwan_content_service->get_content_image_by_id($tc_id);
	$content_list = $taiwan_content_service->find_valid_content_by_content_type($content_row["content"]["tc_type"], 5);
	$currency_id = $tripitta_web_service->get_display_currency();

	$currency_code = NULL;
	// 取得匯率
	if (1 == $currency_id) {
		$currency_code = 'NTD';
	}
	else {
		$exchange = $tripitta_homestay_service->get_exchange_by_currency_id($currency_id);
		$currency_code = $exchange['cr_code'];
	}
	$plan_count = $taiwan_content_service -> count_plan_by_content_id($tc_id);
	$myGc = $geographical_coordinates_dao->getHfGeographicalCoordinatesByCategoryAndReferenceId('taiwan.content', $tc_id);
	$lat = isset($myGc['gc_latitude']) ? $myGc['gc_latitude'] : "";
	$lng = isset($myGc['gc_longitude']) ? $myGc['gc_longitude'] : "";
	// 地圖上要顯示的
	$contentList = $geographical_coordinates_dao->find_content_with_area_by_lat_and_log($lat, $lng, 0.1);
	$hsList = $geographical_coordinates_dao->find_tripitta_homestay_info($lat, $lng, 0.1);

	// 紀錄view count
	if (!empty($tc_id)){
		$track_count_row = $track_content_view_dao -> load($content_row["content"]["tc_type"], $tc_id);
		if(empty($track_count_row)){
			$data = array('type_id'=>$content_row["content"]["tc_type"], 'ref_id'=>$tc_id);
			$tripitta_api_client_service ->add_view_count($data);
			// 紀錄log
			$item = array();
			$item["tcv_folder_id"] = $content_row["content"]["tc_type"];
			$item["tcv_ref_id"] = $tc_id;
			$item["tcv_src_ip"] = get_remote_ip();
			$item["tcv_create_time"] = date('Y-m-d H:i:s');
			$track_content_view_dao -> save($item);
		}else{
			$a = $track_count_row['tcv_create_time'];
			$b = strftime(date("Y-m-d H:i:s")); //現在時間
			$a = date("Y-m-d H:i:s", strtotime($a.'+30 min'));
			$diff =  strtotime($b) - strtotime($a); //單位秒
			if($diff > 0 || get_remote_ip() != $track_count_row['tcv_src_ip']){
				$data = array('type_id'=>$content_row["content"]["tc_type"], 'ref_id'=>$tc_id);
				$tripitta_api_client_service ->add_view_count($data);
				// 紀錄log
				$item = array();
				$item["tcv_folder_id"] = $content_row["content"]["tc_type"];
				$item["tcv_ref_id"] = $tc_id;
				$item["tcv_src_ip"] = get_remote_ip();
				$item["tcv_create_time"] = date('Y-m-d H:i:s');
				$track_content_view_dao -> save($item);
			}
		}
	}

	// 檢查會員是否登入
	$tripitta_web_service = new tripitta_web_service();

	// $display_currency_id = $tripitta_web_service->get_display_currency(0);

	$login_user_data = $tripitta_web_service->check_login();
	if (isset($login_user_data['name']) && $login_user_data['name'] != "") {
		$name = $login_user_data['name'];
	} else {
		$name = (isset($login_user_data['nickname'])) ? $login_user_data['nickname'] : "";
	}
	$email = (isset($login_user_data['email'])) ? $login_user_data['email'] : "";
	$phone = (isset($login_user_data['mobile'])) ? $login_user_data['mobile'] : "";
	$living_country_id = (isset($_POST['living_country_id'])) ? $_POST['living_country_id'] : "";
	if (isset($_SESSION['travel.ezding.captcha.user']) && isset($_POST['captchaCode']) && $_SESSION['travel.ezding.captcha.user'] == $_POST['captchaCode']) {
		$tcs_status = 0;
		$tcs_ref_category = "taiwan.content";
		$gender = ($_POST['gender'] == 1) ? "M" : "F";
		$user_id = (isset($login_user_data['serialId'])) ? $login_user_data['serialId'] : 0;
		$item = array(	 "tcs_ref_category" => $tcs_ref_category
						,"tcs_ref_id" => $tc_id
						,"tcs_user_id" => $user_id
						,"tcs_email" => $_POST['email']
						,"tcs_name" => $_POST['name']
						,"tcs_gender" => $gender
						,"tcs_country_id" => $_POST['living_country_id']
						,"tcs_mobile" => $_POST['phone']
						,"tcs_status" => $tcs_status
						,"tcs_suggestion" => $_POST['textarea']
						,"tcs_create_time" => date("Y-m-d H:i:s")
					);
		$id = $taiwan_content_suggestion->save($item);
	}
?>
<!Doctype html>
<html lang="zh-Hant">
<head>
<? include __DIR__ . "/../common/head.php"; ?>
<link rel="stylesheet" type="text/css" href="/web/pages/location/css/frame.css">
<link rel="stylesheet" type="text/css" href="/web/pages/location/css/page.css">
<link rel="stylesheet" href="/web/css/main.css?01121536">
<style>
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
.errMsg {
    color: red;
    left: 0;
    line-height: 1.5;
    margin-left: 40px;
    position: absolute;
    top: 120px;
}
</style>
<title><?= $content_row["content_tw"]["tc_name"] ?> - 觀光指南 - Tripitta 旅必達</title>
<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
<!-- Add fancyBox main JS and CSS files -->
<script type="text/javascript" src="/web/pages/location/js/fancybox/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="/web/pages/location/js/fancybox/jquery.fancybox.css" media="screen" />
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
<script src="/web/pages/location/js/jquery.easing.1.3.js"></script>
<script src="/web/pages/location/js/actions.js"></script>
<script>
	$(function() {
		$(".swiper-slide").fancybox();
		// map
		<?php if ($lat != "" && $lng != "") { ?>
		map_init();
		<?php } ?>
	})

	function pageInit() {
		$('.errMsg').hide();
		$('#email').val('<?php echo $email; ?>');
		$('#name').val('<?php echo $name; ?>');
		$('#phone').val('<?php echo $phone; ?>');
		refreshCaptcha();
	}
	function refreshCaptcha() {
		var timestamp = Number(new Date());
		$('#capImg').attr('src', '/web/ajax/authimg.php?authType=user&act=refresh&' + timestamp);
	}

	function chkData()
	{
		var msg = 0;
		var email_empty = false;
		if ($("#email").val() == '') {
			$('#email_err').show();
			$('#email').focus();
			msg++;
			email_empty = true;
		} else {
			$('#email_err').hide();
			email_empty = false;
		}
		if (!email_empty) {
			var email = $("#email").val();
		    /* email 正規語法 */
		    var filter = /^[a-zA-Z0-9]+[a-zA-Z0-9_.-]+[a-zA-Z0-9_-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$/;
		    /* 簡易驗證 email */
		    if (filter.test(email)) {
		    	$('#email_valid_err').hide();
		    } else {
		    	$('#email_valid_err').show();
				$('#email').focus();
				msg++;
		    }
		}
		if ($("#name").val() == '') {
			$('#name_err').show();
			$('#email_err').css("top", 192);
			$('#name').focus();
			msg++;
		} else {
			$('#name_err').hide();
		}
		if ($("#phone").val() == '') {
			$('#phone_err').show();
			$('#phone_err').css("top", 265);
			$('#phone').focus();
			msg++;
		} else {
			$('#phone_err').hide();
		}
		if ($("#textarea").val() == '') {
			$('#textarea_err').show();
			$('#textarea_err').css("top", 435);
			$('#textarea').focus();
			msg++;
		} else {
			$('#textarea_err').hide();
		}
		if ($("#captchaCode").val() == '') {
			$('#captchaCode_err').show();
			$('#captchaCode_err').css("top", 514);
			$('#captchaCode').focus();
			msg++;
		} else {
			$('#captchaCode_err').hide();
		}
		if(msg != 0){
			return false;
		}
		// 驗證認證碼是否正確
		var data = {'captchaCode': $('#captchaCode').val(), 'type': 'user'};
		$.getJSON('/web/ajax/ajax.php',
			{func: 'checkCaptchaCode', data: data},
			function(jsonData) {cbCheckCaptcha(jsonData);}
	    );
	}

	function cbCheckCaptcha(jsonData)
	{
		if (jsonData) {
			if (9999 == jsonData['code']) {
				$('#captchaCode').focus();
				alert('認證碼輸入錯誤!!');
				return;
			}
			else if (0000 == jsonData['code']) {
				alert("已收到您熱心的意見回饋,我們將會派專人處理您的需求,請稍待,謝謝!");
				$('#mainpage').submit();
			}
		}
	}

	function add_google_map_listener(marker, infowindow) {
		google.maps.event.addListener(marker, 'click', function() {
			infowindow.open(map, marker);
	 		if (infowindow !== window.openInfoWindow) window.openInfoWindow.close();
	 		window.openInfoWindow = infowindow;
	 	});
		google.maps.event.addListener(infowindow, 'domready', function() {
			var iwOuter = $('.gm-style-iw');
			var iwBackground = iwOuter.prev();
			iwBackground.children(':nth-child(2)').css({'display' : 'none'});
			iwBackground.children(':nth-child(4)').css({'display' : 'none'});
		});
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
	var activityAry = [];
	var viewpointAry = [];
	var foodAry = [];
	var gitAry = [];
	<?php if ($lat != "" && $lng != "") { ?>
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
			$areaRow = $area->loadHfArea($content_row["content"]["tc_area_id"]);
			if(!empty($areaRow)) $a_name = $areaRow["a_name"];

			// 取得tripadvisor資訊
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

			if(!empty($photo_row['list'])) {
				foreach($photo_row['list'] as $k=>$pr) {
					if($pr['p_id'] == $content_row["content"]['tc_main_photo']) {
						$photoId=$pr['p_id'];
						$photoType=$pr['p_content_type'];
						$url = get_config_image_server() . $photo_row['path'] . $tc_id.'/'.$photoId.'.'.$photoType;
					}
				}
				if (($url=="") || (strpos($url, "no_pic") == false)) {				
					$url = get_config_image_server() . $photo_row['path'] . $tc_id.'/'. $photo_row['list'][0]['p_id'] .'.'. $photo_row['list'][0]['p_content_type'];
				}
			}

		?>
			var contentString =
				'<div class="popupMapPrev" >'
				+ '<a href="<?= $linkurl ?>"><img src="<?php echo $url ?>" alt="" onerror="javascript:this.src=\'/../../web/img/no-pic.jpg\'"></a>'
				+ '<section class="pointInfo">'
				+ '<h1 class="pointName"><?php echo preg_replace("/'/", "\\'", $content_row["content_tw"]['tc_name'])?></h1>'
				+ '<h2 >'
				+ '<p class="location"><i class="fa fa-map-marker" ></i><span ><?php echo $a_name; ?></span></p>'
				+ '<p class="favorite"><i class="fa fa-heart" ></i><span ><?= $content_row["content"]['tc_collect_total']?></span></p>'
				+ '<p class="viewCount"><i class="fa fa-eye" ></i><span ><?= $content_row["content"]['tc_cnt_click']?></span></p>'
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
	 	var marker = new google.maps.Marker({
	 		position: new google.maps.LatLng(<?php echo $lat?>,<?php echo $lng?>),
	 		icon: <?= $icon ?>,
	 		map: map
	 	});
	 	infowindow.open(map,marker);
	 	window.openInfoWindow = infowindow;
	 	google.maps.event.addListener(marker, 'click', function() {
	 		infowindow.open(map,marker);
	 		if (infowindow !== window.openInfoWindow) window.openInfoWindow.close();
	 		window.openInfoWindow = infowindow;
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
		if (!empty($contentList)) {
			foreach ($contentList as $key => $ss) {
				$idx++;

				// 取得地區資訊
				$a_name = '';
				$a_name = $ss["main_area_name"].'-'.$ss["small_area_name"];

				// 取得tripadvisor資訊
// 				$sql = "SELECT * FROM hf_source_mapping ";
// 				$sql .= "INNER JOIN hf_trip_advisor_review_info ON tari_id = sm_ref_id ";
// 				$sql .= "WHERE sm_source_id IN (" . $ss['tc_id'] . ") and sm_category = 'tripadvisor.taiwan.content' ";
// 				$tripadvisor_row = $db_reader_travel->executeReader($sql);

				$ta_conut = 0;
				$ta_img = '';
				$tari_id = 0;

				if(!empty($ss['tari_average_rating'])) {
					$ta_conut = $ss['tari_review_count'];
					$ta_img = '<img src="https://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/'.$ss['tari_average_rating'].'-33123-4.gif" style="width:118px;height:20px;"/>';
					$tari_id = $ss['tari_id'];
				}


				$linkurl = '';
				$type_str = '';
				if(7 == $ss['tc_type']) {
					$type_str = 'food';
				} else if(8 == $ss['tc_type']) {
					$type_str = 'spot';
				} else if(82 == $ss['tc_type']) {
					$type_str = 'gift';
				} else if(12 == $ss['tc_type'] || 15 == $ss['tc_type']) {
					$type_str = 'event';
				}
				$linkurl = '/location/' . $type_str . '/' . $ss['tc_id'] . '/';

				if ($ss['tc_type'] == 7) $icon = 'foodIcon';
				if ($ss['tc_type'] == 8) $icon = 'viewpointIon';
				if ($ss['tc_type'] == 12 || $ss['tc_type'] == 15) $icon = 'activityIcon';
				if ($ss['tc_type'] == 82) $icon = 'gitIcon';
		?>
			var contentString<?php echo $idx?> =
				'<div class="popupMapPrev" >'
				+ '<a href="<?= $linkurl ?>"><img src="<?php echo get_config_image_server() . '/photos/' . (is_production() ? 'taiwan_content' : 'taiwan_content_alpha') . '/' . $ss['tc_id']. '/'. $ss['tc_main_photo']. '.jpg'?>" alt="" onerror="javascript:this.src=\'/../../web/img/no-pic.jpg\';"></a>'
				+ '<section class="pointInfo">'
				+ '<h1 class="pointName"><?php echo preg_replace("/'/", "\\'", $ss['tc_name'])?></h1>'
				+ '<h2 >'
				+ '<p class="location"><i class="fa fa-map-marker" ></i><span ><?php echo $a_name; ?></span></p>'
				+ '<p class="favorite"><i class="fa fa-heart" ></i><span ><?= $ss['tc_collect_total']?></span></p>'
				+ '<p class="viewCount"><i class="fa fa-eye" ></i><span ><?= $ss['tc_cnt_click']?></span></p>'
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
			window.add_google_map_listener(marker<?php echo $idx?>, infowindow<?php echo $idx?>);
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
			//marker<?php echo $idx?>.setMap(map);
		<?php }}?>

		<?
		 // 設定民宿marker
		 if (!empty($hsList)) {
		 	foreach ($hsList as $hs) {

		 		// 取得地區資訊
		 		$a_name = '';
		 		$a_code = '';
		 		$areaRow = $area->loadHfArea($hs["hs_area_id"]);
		 		if(!empty($areaRow)) $a_name = $areaRow["a_name"];
		 		if(!empty($areaRow)) $a_code = $areaRow["a_code"];

				// 關注數
				$favorite_count = $hs['hs_favorite'];

				// 點擊數
				//$click_count = 0;
				$click_count = $hs['hsc_cnt_view_total'];

				// 最低價
				$min_price = $hs['hsrp_price'];

		 		// 取得tripadvisor資訊
				$sql = "SELECT hs_id, hf_trip_advisor_review_info.* FROM hf_home_stay ";
				$sql .= "INNER JOIN hf_source_mapping ON sm_category = 'tripadvisor.homestay' AND sm_source_id = hs_id ";
				$sql .= "INNER JOIN hf_trip_advisor_review_info ON tari_id = sm_ref_id ";
				$sql .= "WHERE hs_id IN (" . $hs["hs_id"] . ") and hs_status = 1 ";
		 		$tripadvisor_row = $db_reader_travel->executeReader($sql);

		 		$ta_conut = 0;
		 		$ta_img = '';
		 		$tari_id = 0;
		 		if(!empty($tripadvisor_row)){
			 		foreach ($tripadvisor_row as $tr){
			 			if ($tr['hs_id'] == $hs['hs_id']) {
			 				$ta_conut = $tr['tari_review_count'];
			 				$ta_img = '<img src="http://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/'.$tr['tari_average_rating'].'-33123-4.gif" style="width:118px;height:20px;"/>';
			 				$tari_id = $tr['tari_id'];
			 			}
			 		}
		 		}

		 		$idx++;
		 		$icon = 'haveRoomIcon';
		 		$img = get_config_image_server() . '/photos/' . (is_production() ? 'travel' : 'alpha_travel') . '/home_stay/' . $hs["hs_id"] . '/' . $hs['hs_main_photo'] . "_middle.jpg";

		 ?>
			var contentString<?php echo $idx?> =
				'<div class="popupMapPrev" >'
				+ '<a href="/booking/<?= $a_code ?>/<?= $hs['hs_id']?>/"><img src="<?php echo $img ?>" alt="" onerror="javascript:this.src=\'/../../web/img/no-pic.jpg\'"></a>'
				+ '<section class="pointInfo">'
				+ '<h1 class="pointName"><?php echo preg_replace("/'/", "\\'", $hs['hs_name'])?></h1>'
				+ '<h2 >'
				+ '<p class="location"><i class="fa fa-map-marker" ></i><span ><?= $a_name ?></span></p>'
				+ '<p class="favorite"><i class="fa fa-heart" ></i><span ><?= $favorite_count ?></span></p>'
				+ '<p class="viewCount"><i class="fa fa-eye" ></i><span ><?= $click_count ?></span></p>'
				+ '</h2>'
				+ '<h3>'
				+ '<span><?= $currency_code ?></span>'
				+ '<span class="cost"><?= number_format($min_price) ?></span>'
				+ '<span>起</span>'
				+ '</h3>'
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
		 		position: new google.maps.LatLng(<?php echo $hs['gc_latitude']?>,<?php echo $hs['gc_longitude']?>),
		 		title: '<?php echo preg_replace("/'/", "\\'", $hs['hs_name'])?>',
		 		icon: <?php echo $icon?>,
		 	});
		 <?php echo 'homeStayAry.push(marker', $idx, ');'?>
		 	window.add_google_map_listener(marker<?php echo $idx?>, infowindow<?php echo $idx?>);
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
		 	// marker<?php echo $idx?>.setMap(map);
		 <?php }}?>
	}
	<?php } ?>

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
			for (i in gitAry) gitAry[i].setMap(map);
		}
		else if (type == 'food') {
			$('#food').attr('onclick', 'triMarker(1,"food")');
			for (i in foodAry) foodAry[i].setMap(map);
		}
		else if (type == 'homestay') {
			$('#homestay').attr('onclick', 'triMarker(1,"homestay")');
			for (i in homeStayAry) homeStayAry[i].setMap(map);
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
			for (i in gitAry) gitAry[i].setMap(null);
		}
		else if (type == 'food') {
			$('#food').attr('onclick', 'triMarker(0,"food")');
			for (i in foodAry) foodAry[i].setMap(null);
		}
		else if (type == 'homestay') {
			$('#homestay').attr('onclick', 'triMarker(0,"homestay")');
			for (i in homeStayAry) homeStayAry[i].setMap(null);
		}
	}

	function open_trip_advisor_review(ta_id) {
		window.open('http://www.tripadvisor.com/WidgetEmbed-cdspropertydetail?locationId=' + ta_id + '&partnerId=CB56EED944AF4459B7E92BBF9B292AC6&lang=zh_TW&allowMobile&display=true', 'trip_advisor', 'width=600, location=0, menubar=0, resizable=0, scrollbars=1, status=0, titlebar=0, toolbar=0');
	}
</script>
</head>
<body class="tour-page" onload="pageInit();">
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="map-block">
		<article class="map-content" id="map">
		</article>
		<aside class="tour-pane">
			<div class="content">
				<h3 class="name"><?= $content_row["content_tw"]["tc_name"] ?></h3>
				<?php if(!empty($parent_tag_list)) { ?>
				<ul class="tag-block">
					<?php foreach ($parent_tag_list as $parent_tag_row) { ?>
					<li><?= $parent_tag_row["t_tag"] ?></li>
					<?php } ?>
				</ul>
				<?php } ?>
				<p class="detail"><?= nl2br($content_row["content_tw"]["tc_full_desc"]) ?></p>
				<div class="total">
					共有
					<span class="num"><?= $plan_count ?></span> 篇文章提到我
				</div>
				<?php if(!empty($media_row)) { ?>
				<ul class="tag-block black">
					<?php foreach ($media_row as $mr) { ?>
					<li><?= $mr["m_title"] ?></li>
					<?php } ?>
				</ul>
				<?php } ?>
			</div>
			<div class="toggle-button fa fa-angle-left fa-2x"></div>
		</aside>
		<div class="filter">
			<ul>
				<li>
					<i class="iconn icon-activity-s"></i>
					<input type="checkbox" id="activity" onclick="triMarker(0,'activity')" />
					<label for="activity">
						<p class="txt">活動</p>
						<span></span>
					</label>
				</li>
				<li>
					<i class="iconn icon-viewpoint-s"></i>
					<input type="checkbox" id="scenic" onclick="triMarker(0,'scenic')" />
					<label for="scenic">
						<p class="txt">景點</p>
						<span></span>
					</label>
				</li>
				<li>
					<i class="iconn icon-food-s"></i>
					<input type="checkbox" id="food" onclick="triMarker(0,'food')" />
					<label for="food">
						<p class="txt">美食</p>
						<span></span>
					</label>
				</li>
				<li>
					<i class="iconn icon-gift-s"></i>
					<input type="checkbox" id="souvenir" onclick="triMarker(0,'souvenir')" />
					<label for="souvenir">
						<p class="txt">伴手禮</p>
						<span></span>
					</label>
				</li>
				<li>
					<i class="iconn icon-bnb-s"></i>
					<input type="checkbox" id="homestay" onclick="triMarker(0,'homestay')" />
					<label for="homestay">
						<p class="txt">住宿</p>
						<span></span>
					</label>
				</li>
			</ul>
		</div>
	</div>
	<!-- Swiper -->
	<div class="block-swiper">
		<div class="swiper-container">
			<div class="swiper-wrapper">
				<?php
				if(!empty($photo_row['list'])) {
					foreach($photo_row['list'] as $k=>$pr) {
						$photoId=$pr['p_id'];
						$photoType=$pr['p_content_type'];
						$url = get_config_image_server() . $photo_row['path'] . $tc_id.'/'.$photoId.'.'.$photoType;
				?>
				<a href="<?= $url ?>" class="swiper-slide" rel="gallery1">
					<img src="<?= $url ?>" alt="" style="width:200px;height:114px;">
				</a>
				<?php
					}
				}
				?>
			</div>
			<!-- Add Pagination -->
			<a id="aLeft" class="swiper-button-prev arrow-left" href="#"></a>
			<a id="aRight" class="swiper-button-next arrow-right" href="#"></a>
		</div>
	</div>
	<div class="improve-block">
		<div class="toggle-button">
			<i class="fa fa-angle-left fa-2x"></i>
			<p>改善此清單</p>
		</div>
		<div class="contect">
			<h2 class="title">改善此清單</h2>
			<form id="mainpage" action="" method="post">
				<div class="form-group">
					<span class="icon-title">
						<i class="iconn icon-mail"></i>
					</span>
					<input type="text" id="email" name="email" placeholder="請輸入E-mail">
					<div class="errMsg" id="email_err">E-mail 不可空白</div>
					<div class="errMsg" id="email_valid_err">E-mail 格式驗證錯誤</div>
				</div>
				<div class="form-group">
					<span class="icon-title">
						<i class="iconn icon-user"></i>
					</span>
					<input type="text" id="name" name="name" placeholder="姓名" class="name">
					<div class="errMsg" id="name_err">姓名不可空白</div>
					<div class="LiSelect gender">
						<i class="fa fa-venus"></i>
						<select id="gender" name="gender" class="gender" style="width:67px;">
							<option value="0">女士</option>
							<option value="1" selected>男士</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<span class="icon-title">
						<i class="iconn icon-mobile"></i>
					</span>
					<div class="LiSelect mobile">
						<select id="living_country_id" name="living_country_id" class="phone">
						<?php
							foreach(constants_user_center::$LIVING_COUNTRY_TEXT as $key => $value) {
							    echo '<option value="', $key, '"';
							    if ($key == $living_country_id) echo ' selected';
							    echo '>', $value, '</option>';
							}
						?>
						</select>
					</div>
					<span>
						<input type="text" class="phone" id="phone" name="phone">
					</span>
					<div class="errMsg" id="phone_err">手機號碼不可空白</div>
				</div>
				<textarea id="textarea" name="textarea" placeholder="請詳述您的問題" cols="45" rows="5" class="text-area"></textarea>
				<div class="errMsg" id="textarea_err">您的問題不可空白</div>
				<div class="form-group check-block">
					<div class="number-block">
						<img class="service-pic" id="capImg" src="/web/ajax/authimg.php?authType=user" style="width:140;height:42px;" />
					</div>
					<input type="text" name="captchaCode" id="captchaCode">
					<div class="errMsg" id="captchaCode_err">請輸入問題驗證碼</div>
					<div class="return" onclick="refreshCaptcha();">
						<i class="fa fa-repeat" style="cursor:pointer;"></i>換一張圖片試試
					</div>
				</div>
				<div class="btnWrap">
					<input type="button" id="submit-form" value="確認送出" class="button large" onclick="chkData();" style="cursor:pointer;line-height: 4px;">
				</div>
			</form>
			<p class="question">使用遇到問題？請先進入
				<a href="">會員常見問題</a>
			</p>
		</div>
	</div>
	<article class="act-block">
		<div class="container">
			<div class="text-block">
				<p class="title">電&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp話</p>
				<p class="text"><?= !empty($content_row["content"]["tc_tel"]) ? nl2br($content_row["content"]["tc_tel"]) : "無" ?></p>
			</div>
			<div class="text-block">
				<p class="title">地&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp址</p>
				<p class="text"><?= !empty($content_row["content_tw"]["tc_address"]) ? nl2br($content_row["content_tw"]["tc_address"]) : "無" ?></p>
			</div>
			<?php if($content_row["content"]["tc_type"] == 12 || $content_row["content"]["tc_type"] == 15){ ?>
			<div class="text-block">
				<p class="title">參與限制</p>
				<p class="text"><?= !empty($content_row["content_tw"]["tc_limit"]) ? nl2br($content_row["content_tw"]["tc_limit"]) : "無" ?></p>
			</div>
			<div class="text-block">
				<p class="title">解說導覽</p>
				<p class="text"><?= !empty($content_row["content_tw"]["tc_navigate"]) ? nl2br($content_row["content_tw"]["tc_navigate"]) : "無" ?></p>
			</div>
			<?php } ?>
			<?php if (!empty($content_row["content_tw"]["tc_open_time"])) { ?>
			<div class="text-block">
				<p class="title">營業時間</p>
				<p class="text"><?= !empty($content_row["content_tw"]["tc_open_time"]) ? nl2br($content_row["content_tw"]["tc_open_time"]) : "無" ?></p>
			</div>
			<?php } ?>
			<?php if($content_row["content"]["tc_type"] == 8 || $content_row["content"]["tc_type"] == 12 || $content_row["content"]["tc_type"] == 15){ ?>
				<?php if (!empty($content_row["content_tw"]["tc_ticket_info"])) { ?>
			<div class="text-block">
				<p class="title">門票資訊</p>
				<p class="text"><?= !empty($content_row["content_tw"]["tc_ticket_info"]) ? nl2br($content_row["content_tw"]["tc_ticket_info"]) : "無" ?></p>
			</div>
				<?php } ?>
			<?php } ?>
			<?php if($content_row["content"]["tc_type"] == 7 || $content_row["content"]["tc_type"] == 82){ ?>
				<?php if (!empty($content_row["content_tw"]["tc_ticket_info"])) { ?>
			<div class="text-block">
				<p class="title">平均消費</p>
				<p class="text"><?= !empty($content_row["content_tw"]["tc_ticket_info"]) ? nl2br($content_row["content_tw"]["tc_ticket_info"]) : "無" ?></p>
			</div>
				<?php } ?>
				<?php if (!empty($content_row["content_tw"]["tc_payment_info"])) { ?>
			<div class="text-block">
				<p class="title">付款方式</p>
				<p class="text"><?= !empty($content_row["content_tw"]["tc_payment_info"]) ? nl2br($content_row["content_tw"]["tc_payment_info"]) : "無" ?></p>
			</div>
				<?php } ?>
			<?php } ?>
			<div class="text-block">
				<p class="title">交通資訊</p>
				<p class="text"><?= !empty($content_row["content_tw"]["tc_travelling_info"]) ? nl2br($content_row["content_tw"]["tc_travelling_info"]) : "無" ?></p>
			</div>
			<?php if (!empty($content_row["content_tw"]["tc_parking_info"])) { ?>
			<div class="text-block">
				<p class="title">停&nbsp車&nbsp&nbsp場 </p>
				<p class="text"><?= !empty($content_row["content_tw"]["tc_parking_info"]) ? nl2br($content_row["content_tw"]["tc_parking_info"]) : "無" ?></p>
			</div>
			<?php } ?>
			<?php if($content_row["content"]["tc_type"] != 8 && $content_row["content"]["tc_type"] != 12 && $content_row["content"]["tc_type"] != 15){ ?>
				<?php if (!empty($content_row["content_tw"]["tc_branch_info"])) { ?>
			<div class="text-block">
				<p class="title">分店資訊</p>
				<p class="text"><?= !empty($content_row["content_tw"]["tc_branch_info"]) ? nl2br($content_row["content_tw"]["tc_branch_info"]) : "無" ?></p>
			</div>
				<?php } ?>
			<?php } ?>
		</div>
	</article>
	<div class="top-block">
		<div class="container">
			<?php foreach ($content_list as $k => $cl) {
				$linkurls = '';
				$type_str = '';
				if(7 == $cl["tc_type"]) {
					$type_str = 'food';
				} else if(8 == $cl['tc_type']) {
					$type_str = 'spot';
				} else if(82 == $cl['tc_type']) {
					$type_str = 'gift';
				} else if(12 == $cl['tc_type'] || 15 == $cl['tc_type']) {
					$type_str = 'event';
				}
				$linkurls = '/location/' . $type_str . '/' . $cl['tc_id'] . '/';

			?>
			<a href="<?= $linkurls ?>" class="top-group">
				<img src="<?php echo get_config_image_server() . '/photos/' . (is_production() ? 'taiwan_content' : 'taiwan_content_alpha') . '/' . $cl['tc_id']. '/'. $cl['tc_main_photo']. '.jpg'?>" onerror="javascript:this.src='/../../web/img/no-pic.jpg';" class="photo" style="width:220px;height:140px;">
				<span class="rang iconn icon-top<?= $k+1?>"></span>
				<p class="text"><?= $cl["tc_name"] ?></p>
				<ul class="tool-group">
					<li class="map">
						<i class="fa fa-map-marker"></i><?= $cl["a_name"] ?></li>
					<li class="love">
						<i class="fa fa-heart"></i><?= $cl["tc_collect_total"] ?></li>
					<li class="seen">
						<i class="fa fa-eye"></i><?= $cl["tc_cnt_click"] ?></li>
				</ul>
			</a>
			<?php } ?>
		</div>
	</div>

	<footer id="footer" class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
</body>
</html>