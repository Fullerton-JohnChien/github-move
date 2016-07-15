<?php
/**
 * 說明：接機列表列表
 * 作者：Sam <sam.tzeng@fullerton.com.tw>
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

$pickup_type = get_val("pickup_type");
$message = get_val("message");
$car_day = get_val("car_day");
$end_area = get_val("end_area");
$begin_date = get_val("begin_date");
$car_people = get_val("car_people");
$car_adult = get_val('car_adult');
$car_child = get_val('car_child');

// 頁面基本參數
$people_count = 10;
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
$form_url = "/bookingcar/pickup/" . $fr_id . "/";

// 頁面搜尋資料
$store_service = new store_service();
$tripitta_service = new tripitta_service();

$lang = "tw";
$cr_type = $pickup_type;
$fleet_route = $tripitta_service->get_fleet_route($fr_id);
$fleet_route_detail = $tripitta_service->get_fleet_route_detail($cr_type, $fr_id);
$c_id = $fleet_route_detail['c_id'];
$cr_id = $fleet_route['fr_car_route_id'];
$car_array = $tripitta_service->get_car_by_car_id($c_id);
$car_attribute_array = $tripitta_service->find_car_attribute_mapping($c_id);
// printmsg($car_attribute_array);
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
$url = get_config_image_server();
$car = (is_production()) ? "car" : "car_alpha";
$photo = $url."/photos/".$car."/route/".$cr_id."/".$fleet_route_detail['cr_main_photo'].".jpg";
$car_photo = $url."/photos/".$car."/car/".$c_id."/".$car_array['c_main_photo'].".jpg";
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
		alertmsg('您的訊息已經發給車隊業者了!', '/bookingcar/charter/'.$fr_id.'/');
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
	<title><?php ""; ?>交通預定 - Tripitta 旅必達</title>
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
	<main class="pickup-intro-container">
		<div class="popFrame"></div>
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
						<div class="cFrame">
							<div class="cImg">
								<i class="img-chartercar-b"></i>
							</div>
							<div class="ctitle">
								<?php echo $fleet_route_detail['c_name']; ?>
							</div>
							<div class="cstitle">
								( 或同級車款 )
							</div>
						</div>
						<div class="cFrame">
							<div class="cImg">
								<i class="fa fa-usd"></i>
							</div>
							<div class="ctitle mark">
								<span>NTD <?php echo number_format($fleet_route_detail['fr_price']); ?></span>
							</div>
						</div>
					</div>
					<ul class="rul">
						<li class="rCon">
							<!-- <i class="fa fa-chevron-right"></i> -->
							<div class="block">
								<?php echo $fleet_route_detail['cr_route_description']; ?>
							</div>
						</li>
					</ul>
					<label class="res">
						<button class="submit submit3">立即購買</button>
					</label>
				</div>

				<!-- popup -->
				<div id="popupCar" class="popupCar">
					<div class="closeBtn">
						<i class="fa fa-times" aria-hidden="true"></i>
					</div>
					<div class="imgWrap">
						<div class="lbtn"><i class="fa fa-chevron-left"></i></div>
						<div class="rbtn"><i class="fa fa-chevron-right"></i></div>
						<!--// <img src="<?php echo $car_photo; ?>"> //-->
						<?php
							$i = 0;
							foreach ($car_array['photo'] as $value) {
								$car_single_photo = $url."/photos/".$car."/car/".$c_id."/".$value['p_id'].".jpg";
						?>
						<div id="pic<?php echo $i; ?>">
							<img src="<?php echo $car_single_photo; ?>" <?php if ($i == 0) { echo 'class="selected"'; } ?> />
						</div>
						<?php
								$i++;
							}
						?>
					</div>
					<div class="iBlock">
						<?php
							$i = 0;
							foreach ($car_array['photo'] as $value) {
								$car_single_photo = $url."/photos/".$car."/car/".$c_id."/".$value['p_id'].".jpg";
						?>
						<img src="<?php echo $car_single_photo; ?>" id="ipic<?php echo $i; ?>" data-pic="<?php echo $i; ?>" <?php if ($i == 0) { echo 'class="selected"'; } ?> />
						<?php
								$i++;
							}
						?>
					</div>
					<div class="pcDetail">
						<div class="brand">
							<div class="meta">品牌：</div>
							<div class="val"><span><?php echo $car_array['b_name']; ?></span></div>
						</div>
						<div class="type">
							<div class="meta">車種：</div>
							<div class="val">
								<span>
									<?php
										switch ($car_array['c_type']) {
											case 1 :
												echo "轎車";
												break;
											case 2 :
												echo "休旅車";
												break;
											case 3 :
												echo "箱型車";
												break;
										}
									?>
								</span>
							</div>
						</div>
						<div class="vehicle">
							<div class="meta">車款：</div>
							<div class="val"><span><?php echo $car_array['c_name']; ?></span> ( 或同級車款 )</div>
						</div>
						<div class="seat">
							<div class="meta">座位：</div>
							<div class="val"><span><?php echo $car_array['c_seats']; ?></span>人座</div>
						</div>
						<div class="rul">
							<div class="meta">若：</div>
							<div class="val">
								<?php
									foreach ($car_attribute_array as $value) {
								?>
								乘客<span><?php echo $value['ca_capacity_passenger']; ?></span>人，行李28吋<span><?php echo $value['ca_capacity_luggage']; ?></span>件，手提行李<span><?php echo $value['ca_capacity_hand_package']; ?></span>件<br>
								<?php
									}
								?>
							</div>
						</div>
						<div class="note">
							◎以上車型照片顏色、內裝僅供參考，實際車輛依現場為準。
						</div>
					</div>
				</div>
				<div id="popupQuest" class="popupQuest">
					<div class="closeBtn" onclick="close_quest();">
						<i class="fa fa-times" aria-hidden="true"></i>
					</div>
					<div class="fBlock">
						<div class="fImg">
							<div class="default">
								<i class="fa fa-user"></i>
							</div>
						</div>
						<div class="fTitle"><div>發訊息給</div><div>台灣寶島遊車隊</div></div>
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
							<button type="button" class="submit" id="submit2">發送訊息</button>
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
					<button class="bBtn" onclick="show_share();">分享好友 <i class="fa fa-share-alt" aria-hidden="true"></i></button>
					<button class="bBtn online">線上諮詢 <i class="fa fa-commenting" aria-hidden="true"></i></button>
				</div>
			</div>
		</section>

		<section>
			<div class="rule noPdBot-m">
				<div class="rWrap">
					<input type="checkbox" id="ruleOrder" class="rCbx" checked>
					<label for="ruleOrder" class="rToggle">
						訂購須知
						<i class="fa fa-angle-down" aria-hidden="true"></i>
						<i class="fa fa-angle-up" aria-hidden="true"></i>
					</label>
					<div class="rBlock">
						<h2>訂購須知</h2>
						<ol style="line-height: 24px;">
							<li>指定接機時間：預設班機抵達後１小時上車，可視實際需求調整。</li>
							<li>指定送機時間：預設班機起飛前３小時上車，請依航班限定報到時間及車程距離等實際情況調整。</li>
							<li>接送皆最多等候15分鐘，逾時則司機無法繼續等候，且視同已使用該項服務，不予退費。</li>
						</ol>
					</div>
				</div>
				<div class="rWrap">
					<input type="checkbox" id="ruleNotice" class="rCbx" checked>
					<label for="ruleNotice" class="rToggle">
						特別提醒
						<i class="fa fa-angle-down" aria-hidden="true"></i>
						<i class="fa fa-angle-up" aria-hidden="true"></i>
					</label>
					<div class="rBlock">
						<h2>特別提醒</h2>
						<ol style="line-height: 24px;">
							<li>送機時，請於預定時間及地點準時上車。</li>
							<li>接機時，請在班機抵達後立即打開手機以利聯繫；可快速通關或無託運行李者，預約時請先告知，以縮短候車時間。</li>
							<li>若接機航班延誤，在原定接機時間24小時前通知車隊可更改調度，24小時內則須請旅客自理，恕不另補償或退費。</li>
							<li>若航班提前抵達，則請旅客耐心等候。</li>
							<li>如因資料提供不完整而無法完成接送，恕不另補償或退費。</li>
						</ol>
					</div>
				</div>
			</div>
		</section>

		<section>
			<div class="rule">
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
								<div><?php echo $today; ?></div>
								<div><?php echo $one_day ?></div>
								<div><?php echo $two_day ?></div>
								<div><?php echo $three_day ?></div>
								<div><?php echo $four_day ?></div>
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
							 <?if(count($over_time_add_price)>0&&count($airport_midnight)>0){$tmp++;?>
								<li>4.
								超時，每小時加收NTD <span style="color:blue"><?=number_format($over_time_add_price[0]['frd_category_value'])?></span>元；
									<?for($i=0;$i<count($airport_midnight);$i++){?>
										深夜清晨(<span style="color:blue"><?=$airport_midnight[$i]['frd_begin_time']?></span>~<span style="color:blue"><?=$airport_midnight[$i]['frd_end_time']?></span>)，
										每小時加收NTD <span style="color:blue"><?=number_format($airport_midnight[$i]['frd_category_value'])?></span>元
										<?if($i<count($airport_midnight)-1){?>
											；
										<?}else{?>
											，
										<?}?>
									<?}?>
									訂單價格不包含超時、深夜清晨及附加服務的加價費，請於現場付予司機。
								</li>
							 <?}?>
							 <li><?=(3+$tmp)?>.如遇不可抗力之因素，如颱風、地震、交通中斷等導致車行不能發車或您無法如期出發，將全額退款或依您意願延後出發日期。</li>
							 <li><?=(4+$tmp)?>.本公司為代收代付平台(本服務恕無法開立發票)，若需求乘車收據，請務必於乘車時向司機索取，若當日未索取，視為棄權，無法補發重寄。</li>
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
	<script src="../../../js/lib/jquery/jquery.js"></script>
	<script src="../../../js/main-min.js"></script>
	<script src="/web/pages/topic/js/ftc.js"></script>
	<script type="text/javascript">
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
             	// 顯示車款介紹功能視窗
                $('.info .cate .cFrame').click(function () {
            		$('.overlay').show();
                    $('#popupCar').show();
            		$(window).scrollTop("0");
                });
                // 關閉車款介紹按鈕功能視窗
                $('#popupCar .closeBtn').click(function () {
            		$('.overlay').hide();
                    $('#popupCar').hide();
                });
                // 立刻購買按鈕
                $('.submit3').click(function () {
                    var data = '<?php echo $header_is_login; ?>';
                    if(data == 1){
                    	location.href = "/web/pages/bookingcar/check_data.php?pickup_type=<?php echo $pickup_type; ?>&type=<?= $cr_type ?>&fr_id=<?= $fr_id ?>&begin_date=<?= $begin_date ?>&car_adult=<?= $car_adult ?>&car_child=<?= $car_child ?>";
                    }else{
                    	show_popup_login();
                    }
                });
            });

       // 輪播調整控制項目
       var chgSecond = 5000;  //幾秒換圖 (單位：毫秒)
       var slideSpeed = 2000; //圖片滑行速度 (單位：毫秒)

       //全域變數宣告
       var i = 0;
       var isFirstStart = true; //是否載入時第一次執行
       var beforeNum = 0;	//前一張編號
       var totalNum = $("#popupCar div[id^=pic]").length; //取得大圖總數
       // 先將所有圖檔改圖層 z-index:1
       $("#popupCar div[id^=pic]").css("display","none").css("z-index","1");
       $("#popupCar div[id^=pic]").hide();
       $("#popupCar #pic" + i ).css("display","block").css("z-index","100");
       $("#popupCar #pic" + i ).show();

       $(document).ready(function(){
    	   $(".socialmedia_frame").hide();
            //設定3秒換圖
            setInterval(slidePhoto, chgSecond);
            $("#popupCar .lbtn").on("click", function(){
                i--;
                //若編號<0則為最後一張
                if ( i<0 ){ i = totalNum-1; }
                popupCarButton(i);
            });
            $("#popupCar .rbtn").on("click", function(){
            	//若編號>總數則重頭第一張開始
                if ( i >= totalNum ){	i=0;	}
            	popupCarButton(i);
                i++;
                //若編號>總數則重頭第一張開始
                if ( i >= totalNum ){	i=0;	}
            });
            $('#popupCar .iBlock img').on("click", function(){
                var pic = $(this).attr('data-pic');
                popupCarButton(pic);
            });
       });

       popupCarButton = function(pic_i){
    	 	//將所有圖檔隱藏並設定層級最低
           $("#popupCar div[id^=pic]").css("display","none").css("z-index","1");
           $("#popupCar div[id^=pic]").hide();
           $("#popupCar .iBlock img").removeClass("selected");
           //將目前要滑行的這張圖檔打開並設定層級最高
           $("#popupCar #pic" + pic_i ).css("display","block").css("z-index","100");
           $("#popupCar #pic" + pic_i ).show();
           $("#popupCar #ipic" + pic_i).addClass("selected");
       }

       slidePhoto = function (){
           var totalNum = $("#popupCar div[id^=pic]").length; //取得大圖總數
           if(isFirstStart!=true){
           		beforeNum = (i-1);
           }

           if ( isFirstStart==true && i==0 ){
               isFirstStart=false; //載入時第一次執行，第一張不滑行
               i++;
           }
           else{
               if ( i >= totalNum ){ i=0; }           //若編號>總數則重頭第一張開始
               popupCarButton(i);
               //開始滑行
               $("#popupCar #pic" + i ).show('slide', {direction: 'left'}, slideSpeed ,function(){
                   i++;
               });
           }
       }
    </script>
</body>
</html>