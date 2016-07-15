<?php
/**
 * 說明：交通預定 - 觀光巴士詳細
 * 作者：Bobby <bobby.luo@fullerton.com.tw>
 * 日期：2016年06月01日
 * 備註：
 */
require_once __DIR__ . '/../../../config.php';
header("Content-Type:text/html; charset=utf-8");

// 預設清除 pay_step session
if(!empty($_SESSION["pay_step"])){
	unset($_SESSION["pay_step"]);
}

$fr_id = get_val("fr_id");

$message = get_val("message");
$car_day = get_val("car_day");
$end_area = get_val("end_area");
$begin_date = get_val("begin_date");
$car_people = get_val("car_people");
$car_adult = get_val_with_default("car_adult", 0);
$car_child = get_val_with_default("car_child", 0);

// 頁面基本參數
$people_count = 10;
$adult_count = 10;
$child_count = 10;
$category = 'car';
$area_dao = Dao_loader::__get_area_dao();
$area_list = $area_dao->findAreasWithLangByCategoryAndParentId(get_config_current_lang(), $category, 0);

// 包車天數內容(預設間隔0.5)
$day_count = 3;
$day_space = 0.5;
$car_day_list = array();
for ($i = 0.5; $i <= $day_count; $i+=$day_space) {
	$car_day_list[] = $i;
}
$form_url = "/bookingcar/tourbus/" . $fr_id . "/";

// 頁面搜尋資料
$store_service = new store_service();
$tripitta_service = new tripitta_service();

$lang = "tw";
$cr_type = 3;
$fleet_route = $tripitta_service->get_fleet_route($fr_id);
$fleet_route_detail = $tripitta_service->get_fleet_route_detail($cr_type, $fr_id);
if (empty($fleet_route_detail)) {
	alertmsg('查無此頁!', '/transport/');
} else {
	$c_id = $fleet_route_detail['c_id'];
}
$cr_id = $fleet_route['fr_car_route_id'];
$car_array = $tripitta_service->get_car_by_car_id($c_id);
// $car_attribute_array = $tripitta_service->find_car_attribute_mapping($c_id);
$car_route_spot = $tripitta_service->find_car_route_spot_by_car_route_id($cr_id);
$fleet_route_note = $tripitta_service->get_fleet_price_note($lang, $fleet_route_detail['s_id'], $cr_type);
$cancel_rule_array = $store_service->find_cancel_rule_by_store_id($fleet_route_detail['s_id']);
$today = date("m/d");
$one_day = date("m/d",strtotime("+1 day"));
$two_day = date("m/d",strtotime("+2 day"));
$three_day = date("m/d",strtotime("+3 day"));
$four_day = date("m/d",strtotime("+4 day"));
$fcrd_day = 0;
$cancel_rule = array();
$i = 1;
foreach ($cancel_rule_array as $value) {
	if ($value['fcrd_percent'] == 0) {
		$fcrd_day = $value['fcrd_day'];
	} else {
		$i++;
		$value['order'] = $i;
		$cancel_rule[] = $value;
	}
}

$this_day = date("m/d", strtotime($begin_date));
$start_day = date("m/d", strtotime($begin_date)-(86400*($fcrd_day+1)));
$ready_day = date("Y-m-d", strtotime($begin_date)-(86400*($fcrd_day+1)));

$url = get_config_image_server();
$car = (is_production()) ? "car" : "car_alpha";
$photo = $url."/photos/".$car."/route/".$cr_id."/".$fleet_route_detail['cr_main_photo'].".jpg";
// $car_photo = $url."/photos/".$car."/car/".$c_id."/".$car_array['c_main_photo'].".jpg";
$commont = $fleet_route_detail['commont'];
$commont = number_format($commont, 1);
if ($commont > 0 && $commont < 0.3) {
	$commont = 0;
} else if ($commont > 0.2 && $commont < 0.8) {
	$commont = 0.5;
} else if ($commont > 0.7 && $commont < 1.3) {
	$commont = 1.0;
} else if ($commont > 1.2 && $commont < 1.8) {
	$commont = 1.5;
} else if ($commont > 1.7 && $commont < 2.3) {
	$commont = 2.0;
} else if ($commont > 2.2 && $commont < 2.8) {
	$commont = 2.5;
} else if ($commont > 2.7 && $commont < 3.3) {
	$commont = 3.0;
} else if ($commont > 3.2 && $commont < 3.8) {
	$commont = 3.5;
} else if ($commont > 3.7 && $commont < 4.3) {
	$commont = 4.0;
} else if ($commont > 4.2 && $commont < 4.8) {
	$commont = 4.5;
} else if ($commont > 4.7 && $commont <= 5.0) {
	$commont = 5.0;
}

if ($message != "") {
	$travel_notification_service = new travel_notification_service();
	$message_str = "";
	$message_str .= $fleet_route_detail['cr_name']."<br>";
	$email = $_SESSION['travel.ezding.user.data']['email'];
	$message_str .= "詢問者 E-mail : ".$email."<br>";
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
	$s_id = $fleet_route_detail['s_id'];
	$user_id = $_SESSION['travel.ezding.user.data']['serialId'];
	$partner_account = $tripitta_service->get_number_one_partner_account_by_store_id($s_id);
	$to_ids[] = array("to_id" => $partner_account['pa_id'], "account_type" => 40);
	$notification = $travel_notification_service->add_notification($user_id, $account_type, $to_ids, $message_str);
	if ($notification['ngs_group_id'] > 0) {
		alertmsg('您的訊息已經發給車隊業者了!', '/bookingcar/tourbus/'.$fr_id.'/');
	}
}

$tripitta_web_service = new tripitta_web_service();
$login_user_data = $tripitta_web_service->check_login();
$is_login = false;
$serialId = '';
if(!empty($login_user_data)) {
	$serialId = $login_user_data["serialId"];
	$is_login = true;
	$user_favorite_type_ids = array("11");
	$favorite_list = $tripitta_web_service->find_user_favorite_by_user_id_and_ref_type_ids($serialId, $user_favorite_type_ids);
}

$favourite_type = 11;
$favorite_class = "fa-heart-o";
if (!empty($favorite_list)) {
	foreach($favorite_list as $favorite_row) {
		if ($favorite_row['uf_home_stay_id'] == $fleet_route_detail["fr_id"]) {
			$favorite_class = "fa-heart";
			break;
		}
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
	<title>交通預定 - Tripitta 旅必達</title>
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
	<script src="/web/js/jquery.twbsPagination.js" type="text/javascript"></script>
	<link rel="stylesheet" href="/web/css/main.css">
	<link rel="stylesheet" href="/web/css/main2.css">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Tripitta 旅必達">
    <meta property="og:locale" content="zh_TW">

    <meta property="og:image" content="<?php echo $photo; ?>">
    <meta property="og:title" content="<?php echo $fleet_route_detail['cr_name']; ?>">
    <meta property="og:description" content="<?php echo str_replace("<br>", "", $fleet_route_detail['cr_route_description']); ?>">
    <meta property="og:app_id" content="474784012720774">
	<meta name="twitter:title" content="<?php echo $fleet_route_detail['cr_name']; ?>" />
	<meta name="twitter:description" content="<?php echo str_replace("<br>", "", $fleet_route_detail['cr_route_description']); ?>" />
	<meta name="twitter:image" content="<?php echo $photo; ?>" />
	<meta name="description" content="<?php echo str_replace("<br>", "", $fleet_route_detail['cr_route_description']); ?>">
	<script>
		function show_share(){
    		$('.overlay').show();
			$(".popupSocial").show();
    		$(window).scrollTop("0");
		}

		function close_share() {
    		$('.overlay').hide();
			$(".popupSocial").hide();
		}

		function close_quest(){
			$(".popupQuest").hide();
		}

		var is_login = <?= ($is_login) ? 1:0 ?>;

		$(function () {
			// 加入收藏
	        $('.pic .myFavourite').each(function() {
	            $(this).click(function() {
	            	var ref_type = $(this).attr('data-type');
	                var ref_id = $(this).attr('data-id');
	    			var add = $('#' + ref_type + '_' + ref_id).hasClass('fa-heart-o') ? 1 : 0;
	                if (add == 1) {
	                	add_favorite('#' + ref_type + '_' + ref_id, ref_type, ref_id);
	                } else {
	                	remove_favorite('#' + ref_type + '_' + ref_id, ref_type, ref_id);
	                }
	            });
	        });
		});

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
	</script>
</head>
<body>
	<header><?php include __DIR__ . "/../../common/header_new.php"; ?></header>
	<main class="tourBus-intro-container">
		<section>
			<div class="info">
				<input type="hidden" id="user_serial_id" value="<?php echo $serialId; ?>">
				<div class="pic">
					<div class="img" style="background-image: url('<?php echo $photo; ?>');"></div>
					<div class="myFavourite" data-type="<?php echo $favourite_type; ?>" data-id="<?php echo $fleet_route_detail["fr_id"]; ?>">
						<i class="fa <?php echo $favorite_class; ?>" id="<?php echo $favourite_type; ?>_<?php echo $fleet_route_detail["fr_id"]; ?>"></i>
					</div>
				</div>
				<div class="detail">
					<div class="fleet">
						<a href="/bookingcar/<?php echo $fleet_route_detail['s_id']; ?>/" class="title" target="_blank"><?php echo $fleet_route_detail['sml_name']; ?> 提供</a>
						<div class="rank">
							<div class="stars">
								<?php
                                    $star = 5;
                                    $cnt = $commont;
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
                                    <i class="fa <?php echo $class; ?>" aria-hidden="true"></i>
                                <?php
	                                }
                                ?>
							</div>
							<a href="/bookingcar/<?php echo $fleet_route_detail['s_id']; ?>/" class="reviews" target="_blank">
								看評價
							</a>
						</div>
					</div>
					<h1><?php echo $fleet_route_detail['cr_name']; ?></h1>
					<div class="cate twoCol">
						<div class="cFrame" style="cursor :default;">
							<div class="cImg">
								<i class="img-sightseeingbus-b"></i>
							</div>
							<div class="ctitle">
								觀光巴士
							</div>
							<div class="cstitle">
								( 中巴或休旅車 )
							</div>
						</div>
						<div class="cFrame">
							<div class="cImg">
								<i class="fa fa-usd"></i>
							</div>
							<div class="ctitle mark">
								<span>NTD <?php echo number_format($fleet_route_detail['fr_child_price']); ?></span>
							</div>
						</div>
					</div>
					<ul class="rul">
						<li class="rCon">
							<?if($fleet_route_detail['cr_route_description']!=''){?>
								<!-- <i class="fa fa-chevron-right"></i>-->
							<?}?>
							<div class="block">
								<?php echo $fleet_route_detail['cr_route_description']; ?>
							</div>
						</li>
					</ul>
				</div>
				<div id="popupCar" class="popupCar">
					<div class="closeBtn">
						<i class="fa fa-times" aria-hidden="true"></i>
					</div>
					<div class="imgWrap">
						<div class="lbtn"><i class="fa fa-chevron-left"></i></div>
						<div class="rbtn"><i class="fa fa-chevron-right"></i></div>
						<img src="http://placehold.it/600x400">
					</div>
					<div class="iBlock">
						<img src="http://placehold.it/60x40" class="selected">
						<img src="http://placehold.it/60x40">
						<img src="http://placehold.it/60x40">
						<img src="http://placehold.it/60x40">
						<img src="http://placehold.it/60x40">
						<img src="http://placehold.it/60x40">
						<img src="http://placehold.it/60x40">
						<img src="http://placehold.it/60x40">
						<img src="http://placehold.it/60x40">
					</div>
					<div class="pcDetail">
						<div class="brand">
							<div class="meta">品牌：</div>
							<div class="val"><span>Toyota</span></div>
						</div>
						<div class="type">
							<div class="meta">車種：</div>
							<div class="val"><span>廂型車</span></div>
						</div>
						<div class="vehicle">
							<div class="meta">車款：</div>
							<div class="val"><span>Wish</span> ( 或同級車款 )</div>
						</div>
						<div class="seat">
							<div class="meta">座位：</div>
							<div class="val"><span>9</span>人座</div>
						</div>
						<div class="rul">
							<div class="meta">若：</div>
							<div class="val">
								乘客<span>4</span>人，行李28吋<span>1</span>件，手提行李<span>2</span>件
								<br>乘客<span>3</span>人，行李28吋<span>2</span>件，手提行李<span>2</span>件
							</div>
						</div>
						<div class="note">
							◎以上車型照片顏色、內裝僅供參考，實際車輛依現場為準。
						</div>
					</div>
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
						<div class="fTitle"><div>發訊息給</div><div> <?php echo $fleet_route_detail['sml_name']; ?></div></div>
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
	                                                    <option value="<?php echo $a["a_name"]; ?>"><?php echo $a["a_name"]; ?></option>
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
								<button class="submit" id="submit2">發送訊息</button>
							</div>
						</div>
					</form>
				</div>
				<div class="popupSocial">
					<div class="closeBtn" onclick="close_share();">
						<i class="fa fa-times" aria-hidden="true"></i>
					</div>
					<div class="sTitle">分享</div>
					<ul class="social-button">
						<li id="fbShare">
							<i class="fa fa-facebook"></i>
							<div class="text">facebook</div>
						</li>
						<li id="twitterShare">
							<i class="fa fa-twitter"></i>
							<div class="text">twitter</div>
						</li>
						<li id="weiboShare">
							<i class="fa fa-weibo"></i>
							<div class="text">微博</div>
						</li>
						<li id="weixinShare">
							<i class="fa fa-wechat"></i>
							<div class="text">微信</div>
						</li>
					</ul>
					<!--
					<div class="copy">
						<button class="copyBtn">複製連結 <i class="fa fa-files-o" aria-hidden="true"></i></button>
					</div>
					-->
					<div class="weixinQcode">
						<i class="fa fa-times"></i>
						<h4>分享到微信朋友圈</h4>
						<img src='' alt='qr code'>
						<h5>打開微信，點擊底部的“發現”，使用“掃一掃”即可將網頁分享到我的朋友圈。</h5>
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<div class="btnWrap">
				<div class="btnGroup">
					<button class="bBtn" onclick="show_share();">
						分享好友 <i class="fa fa-share-alt" aria-hidden="true"></i>
					</button>
					<button class="bBtn online">
						線上諮詢 <i class="fa fa-commenting" aria-hidden="true"></i>
					</button>
				</div>
			</div>
			<div class="orderBlock-tourBus">
				<div class="getOn">
					<div class="title">
						上車地點
					</div>
					<div class="selectWrap">
						<select>
							<option value="<?php echo $fleet_route_detail['up_name']; ?>"><?php echo $fleet_route_detail['up_name']; ?></option>
						</select>
						<i class="fa fa-angle-down" aria-hidden="true"></i>
					</div>
				</div>
				<div class="passenger">
					<div class="title">
						乘車人數
					</div>
					<div class="pBlock">
						<div class="pMember">大人 NTD <span id="adult_price_span"><?php echo number_format($fleet_route_detail['fr_adult_price']); ?></span> 元</div>
						<div class="selectWrap">
							<select id="adult_number" name="adult_number">
								<?php for ($i = 0; $i <= $adult_count; $i++) { ?>
                                	<option value="<?php echo $i; ?>"<?php echo $car_adult==$i ? " selected" : ""; ?>><?php echo $i; ?>人</option>
                            	<?php } ?>
							</select>
							<i class="fa fa-angle-down" aria-hidden="true"></i>
						</div>
					</div>
					<div class="pBlock">
						<div class="pMember">小孩 NTD <span id="child_price_span"><?php echo number_format($fleet_route_detail['fr_child_price']); ?></span> 元</div>
						<div class="selectWrap">
							<select id="child_number" name="child_number">
								<?php for ($i = 0; $i <= $child_count; $i++) { ?>
                                	<option value="<?php echo $i; ?>"<?php echo $car_child==$i ? " selected" : ""; ?>><?php echo $i; ?>人</option>
                            	<?php } ?>
							</select>
							<i class="fa fa-angle-down" aria-hidden="true"></i>
						</div>
					</div>
					<div class="text">
						(5歲以下)
					</div>
				</div>
				<div class="price">
					<div class="amount">
						金額 NTD <span id="price">0</span> 元
					</div>
					<button class="submit submit3">立即購買</button>
				</div>
			</div>
		</section>
		<section>
			<div class="itine">
				<h2><i class="fa fa-bullhorn"></i> 建議行程</h2>
				<ul class="itineRule">
					<li>本行程準時發車，請旅客遵守發車的時間，發車地點請見下方圖示，逾時不候，請參加者互相體諒、理解，共同快樂出行。</li>
					<li>我們以安全第一為考量，因此行程可能如因天候狀況或其他特殊原因必須更改時敬請見諒。</li>
				</ul>
				<?php
					$i = 0;
					$car_route_spot_count = count($car_route_spot);
					foreach ($car_route_spot as $value) {
						if (7 == $value["tc_type"]) {
							$type_str = 'food';
						} else if(8 == $value['tc_type']) {
							$type_str = 'spot';
						} else if(82 == $value['tc_type']) {
							$type_str = 'gift';
						} else if(12 == $value['tc_type'] || 15 == $value['tc_type']) {
							$type_str = 'event';
						}
				?>
				<div class="block <?php if ($value['crs_content_id'] != 0 && $value['tc_status']==1 ) { ?>pointed<?php } ?>">
					<?php if ( $value['crs_content_id'] == 0 || $value['tc_status']!=1 ) { ?>
					<div class="outer">
						<div class="circle">
							<div class="cont">
								<?php echo $value['crs_name']; ?>
							</div>
						</div>
						<div class="triangle"></div>
					</div>
					<?php } else { ?>
					<a href="/location/<?php echo $type_str; ?>/<?php echo $value['crs_content_id']; ?>/" target="_blank" class="outer">
						<div class="circle">
							<div class="cont">
								<?php echo $value['crs_name']; ?>
							</div>
						</div>
						<div class="triangle"></div>
					</a>
					<?php } ?>
					<div class="pointWrap">
						<div class="point">
							<?php if ($value['crs_content_id'] == 0) { ?><i class="fa fa-circle"></i><?php }else{ ?><i class="fa fa-picture-o"></i><?php } ?>
						</div>
					</div>
					<div class="stop">
						<?php if ($i == 0) { ?>
						發車站
						<?php } if (($car_route_spot_count-1) == $i) { ?>
						終點站
						<?php } else if ($i > 0) { ?>
						第 <?php echo $i; ?> 站
						<?php } ?>
					</div>
					<?php if ($i != 0 && ($car_route_spot_count-1) != $i) { ?>
					<div class="stay">
						<i class="fa fa-clock-o"></i> 停留約 <?php echo $value['crs_stay_time']; ?> 分
					</div>
					<?php } ?>
				</div>
				<?php
						$i++;
					}
				?>
			</div>
		</section>
		<section>
			<div class="rule">
				<div class="rWrap">
					<input type="checkbox" id="rulePos" class="rCbx">
					<label for="rulePos" class="rToggle">
						搭車位置
						<i class="fa fa-angle-down" aria-hidden="true"></i>
						<i class="fa fa-angle-up" aria-hidden="true"></i>
					</label>
					<div class="rBlock">
						<h2>搭車位置</h2>
						<ul>
							<li>上車地點：<?php echo $fleet_route_detail['up_address']; ?></li>
							<li>下車地點：<?php echo $fleet_route_detail['down_address']; ?></li>
						</ul>
					</div>
				</div>
				<div class="rWrap">
					<input type="checkbox" id="rulePrice" class="rCbx">
					<label for="rulePrice" class="rToggle">
						費用項目
						<i class="fa fa-angle-down" aria-hidden="true"></i>
						<i class="fa fa-angle-up" aria-hidden="true"></i>
					</label>
					<div class="rBlock">
						<h2>費用項目</h2>
						<ul>
							<li>費用包含項目： <?php echo $fleet_route_note['fpn_fee_include']; ?></li>
							<li>費用不包含項目： <?php echo $fleet_route_note['fpn_fee_exclude']; ?></li>
						</ul>
					</div>
				</div>
				<div class="rWrap">
					<input type="checkbox" id="ruleCancel" class="rCbx">
					<label for="ruleCancel" class="rToggle">
						取消規定
						<i class="fa fa-angle-down" aria-hidden="true"></i>
						<i class="fa fa-angle-up" aria-hidden="true"></i>
					</label>
					<div class="rBlock1">
						<h2>取消規定</h2>
						<ol>
							<li>乘車日<span><?php echo $fcrd_day; ?></span>天前取消，全額退款。</li>
							<?php
								foreach ($cancel_rule as $value) {
									if ($value['fcrd_day'] == 0) {
										$value['fcrd_day'] = "當";
										$zero_percent = $value['fcrd_percent'];
									} else {
										$part_percent = $value['fcrd_percent'];
									}
							?>
							<li>乘車日<span><?php echo $value['fcrd_day']; ?></span>天內取消，收取全額的<span> <?php echo $value['fcrd_percent']; ?> ％</span>取消費。</li>
							<?php
								}
							?>
						</ol>
						<div class="expDate">
							<div class="date">
								<div><?php echo $start_day; ?></div>
								<?php
									for ($i=1;$i<=$fcrd_day;$i++) {
										$day = date("m/d", strtotime($ready_day)+(86400*$i));
								?>
								<div><?php echo $day ?></div>
								<?php } ?>
								<div><?php echo $this_day ?></div>
							</div>
							<div class="rul">
								<div>免費取消</div>
								<div>收<?php echo $part_percent ?>%取消費</div>
								<div>收<?php echo $zero_percent ?>%取消費</div>
							</div>
						</div>
					</div>
				</div>
				<div class="rWrap">
					<input type="checkbox" id="ruleNoted" class="rCbx">
					<label for="ruleNoted" class="rToggle">
						注意事項及相關規定
						<i class="fa fa-angle-down" aria-hidden="true"></i>
						<i class="fa fa-angle-up" aria-hidden="true"></i>
					</label>
					<?php
							$partner_service = new partner_service();
							$tripitta_car_service = new tripitta_car_service();
							$partner_row = $partner_service->get_store($fleet_route_detail['s_id']);
							$half_day_hour = $tripitta_car_service->find_fleet_rule_detail_by_fr_id_category($fr_id, 'half.day.hour');
							$full_day_hour = $tripitta_car_service->find_fleet_rule_detail_by_fr_id_category($fr_id, 'day.hour');
							$over_time_add_price = $tripitta_car_service->find_fleet_rule_detail_by_fr_id_category($fr_id, 'over.time.add_price');
							$airport_midnight = $tripitta_car_service->find_fleet_rule_detail_by_fr_id_category($fr_id, 'airport.midnight.add.price');
							$chinesenewyear = $tripitta_car_service->find_fleet_rule_detail_by_fr_id_category($fr_id, 'chinesenewyear.add.price');
							$num = count($half_day_hour)+count($full_day_hour)+count($over_time_add_price)+count($airport_midnight)+count($chinesenewyear);
					?>
					<div class="rBlock">
						<h2>注意事項及相關規定</h2>
						<?if($num>0){?>
						<div class="notice">
						<ul class="popupPolicy">
							<li>1.若有行程或車輛上調動的問題，我們將於訂單成立後一個工作天內與您聯絡，如未能符合您的需求，我們將全額退費給您。</li>
							<li>2.最晚於出發日前24小時我們將會提供您車輛的車牌號碼、司機全名及手機號碼。</li>
							<li>
							 <?
							 	 $tmp=0;
								 if(count($chinesenewyear)>0){$tmp++;?>3.
								 <?for($i=0;$i<count($chinesenewyear);$i++){?>
										若您訂購春節期間(<span style="color:blue"><?=$chinesenewyear[$i]['frd_begin_date']?></span>~<span style="color:blue"><?=$chinesenewyear[$i]['frd_begin_date']?></span>)，
										一日行程加價NTD<span style="color:blue"><?=number_format($chinesenewyear[$i]['frd_category_value2'])?></span>元，半日行程加價NTD<span style="color:blue"><?=number_format($chinesenewyear[$i]['frd_category_value'])?></span>元
										<?if($i<count($chinesenewyear)-1){?>
											；
										<?}else{?>
											，
										<?}?>
								  <?}?>
								     請於現場付予司機。</li>
								  <?}?>
							 <li><?=(3+$tmp)?>.以上景點或使用時間及順序將有增減或調整之可能，實際以當天氣候狀況為準，以安全為第一考量。</li>
							 <li><?=(4+$tmp)?>.如遇不可抗力之因素，如颱風、地震、交通中斷等導致車行不能發車或您無法如期出發，將全額退款或依您意願延後出發日期。</li>
							 <li><?=(5+$tmp)?>.本公司為代收代付平台(本服務恕無法開立發票)，若需求乘車收據，請務必於乘車時向司機索取，若當日未索取，視為棄權，無法補發重寄。</li>
						</ul>
						</div>
						<?}?>
						<div class="notice">
							<?php echo $fleet_route_note['fpn_extra_note']; ?>
						</div>
					</div>
				</div>
				<label class="btnWrap">
					<button class="submit submit3">立即購買</button>
				</label>
			</div>
		</section>
	</main>
	<footer><?php include __DIR__ . "/../../common/footer_new.php"; ?></footer>
	<?php include __DIR__ . '/../../common/ga.php';?>
	<script src="/web/pages/topic/js/ftc.js"></script>
    <script type="text/javascript">
    	$(function () {
        	 // 處理大人乘車人數計算
             $('.passenger #adult_number').on("change",function(){
            	 bus_price_total();
             });
        	 // 處理小孩乘車人數計算
             $('.passenger #child_number').on("change",function(){
            	 bus_price_total();
             });
    	});

		// 計算價格
    	var bus_price_total = function(){
        	var adult_price = $('.passenger #adult_price_span').html();
        	adult_price = adult_price.replace(",", "");
        	var child_price = $('.passenger #child_price_span').html();
        	child_price = child_price.replace(",", "");
        	var adult_number = $('.passenger #adult_number :selected').val();
        	var child_number = $('.passenger #child_number :selected').val();
        	var bus_total = (parseInt(adult_price) * parseInt(adult_number)) + (parseInt(child_price) * parseInt(child_number));
        	$('.orderBlock-tourBus #price').html(formatNumber(bus_total));
    	}

    	$( document ).ready(function(){
        	// 頁面完成，初始計算
    		bus_price_total();
        });

    	$(function () {
        	var caneldar_option = <?= json_encode(Constants::$CALENDAR_OPTIONS) ?>;
            $('#begin_date').datepicker(caneldar_option).datepicker('option', {minDate: new Date()});
        	// 線上諮詢按鈕功能檢查
            $('.btnWrap .btnGroup .online').click(function () {
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
            $('#popupQuest .sBtnWrap #submit2').click(function () {
                var msg = '';
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
            // 立刻購買按鈕
            $('.submit3').click(function () {
                var data = '<?php echo $header_is_login; ?>';
                if(data == 1){
                    var car_adult = $('#adult_number').val();
                    var car_child = $('#child_number').val();
                    if (car_adult == 0) {
                        alert("乘車大人人數不可為0!");
                    } else {
                		location.href = "/web/pages/bookingcar/check_data.php?type=3&fr_id=<?= $fr_id ?>&begin_date=<?= $begin_date ?>&car_adult="+car_adult+"&car_child="+car_child;
                    }
                }else{
                	show_popup_login();
                }
            });
    	});
    </script>
</body>
</html>