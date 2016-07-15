<?php
require_once __DIR__ . '/../../../../config.php';

$tripitta_service = new tripitta_service();
$tripitta_web_service = new tripitta_web_service();
$login_user_data = $tripitta_web_service->check_login();
$travel_notification_service = new travel_notification_service();

if (!empty($login_user_data)) {
	$avatar = $login_user_data["avatar"];
}

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

$co_id = get_val("co_id");
$begin_date = get_val("begin_date");
$end_area = get_val("end_area");
$car_day = get_val("car_day");
$car_people = get_val("car_people");
$message = get_val("message");

$form_url = "/web/pages/member/embed/orders/orders.php";
if ($message != "") {
	$order = $tripitta_service->get_car_order_by_order_id($co_id);
	$message_str = "";
	$co_route_type = $order['co_route_type'];
	$co_transaction_id = $order['co_transaction_id'];
	$co_prod_name = $order['co_prod_name'];
	$co_buyer_email = $order['co_buyer_email'];
	$message_str .= "訂單編號 : ".$co_transaction_id."<br>";
	$message_str .= "行程名稱 : ".$co_prod_name."<br>";
	$message_str .= "購買人 E-mail : ".$co_buyer_email."<br>";
	if ($begin_date != "") {
		switch ($co_route_type) {
			case 1 :
			case 3 :
				$type = "出發";
				break;
			case 2 :
				$type = "接機";
				break;
			case 4 :
				$type = "送機";
				break;
		}
		$message_str .= $type."日期 : ".$begin_date."<br>";
	}
	if ($end_area != "") {
		$message_str .= "目的地 : ".$end_area."<br>";
	}
	if ($car_day != "") {
		$message_str .= "天數 : ".$car_day." 天<br>";
	}
	if ($car_people != "") {
		$message_str .= "人數 : ".$car_people." 人<br><br>";
	}
	$message_str .= "詢問問題 : <br>";
	$message_str .= $message;
	$to_ids = array();
	$account_type = 5;
	$s_id = $order['co_store_id'];
	$user_id = $_SESSION['travel.ezding.user.data']['serialId'];
	$partner_account = $tripitta_service->get_number_one_partner_account_by_store_id($s_id);
	$to_ids[] = array("to_id" => $partner_account['pa_id'], "account_type" => 40);
	$notification = $travel_notification_service->add_notification($user_id, $account_type, $to_ids, $message_str);
	if ($notification['ngs_group_id'] > 0) {
		alertmsg('您聯繫業者反映之問題已經傳送至車隊業者!', '/member/');
	}
}
?>
<div class="orders">
	<div class="listBlock">
		<ul class="list">
			<!--
			<li id="all">全部訂單</li>
			-->
			<li id="homestay">旅宿</li>
			<li id="charter" class="selected">包車</li>
			<li id="pickup">接送機</li>
			<li id="tourbus">觀光巴士</li>
			<li id="4g">4G網卡</li>
			<li id="hsr">高鐵</li>
		</ul>
		<!--
		<div class="searchDetail">
			<span class="result">
				SERACH RESULTS(<span>128</span>)
			</span>
			<?php /*
			<div class="selector">
				<select>
					<option>全部明細</option>
					<option>2</option>
					<option>3</option>
				</select>
				<i class="fa fa-angle-down"></i>
			</div>
			*/ ?>
		</div>
		-->
	</div>
	<div class="list-m">
		<div class="selector">
			<select id="change">
				<option value="all">全部訂單</option>
				<option value="homestay">旅宿</option>
				<option value="charter" selected>包車</option>
				<option value="pickup">接送機</option>
				<option value="tourbus">觀光巴士</option>
				<option value="4g">4G網卡</option>
				<option value="hsr">高鐵</option>
			</select>
			<i class="fa fa-angle-down"></i>
		</div>
		<div class="selector">
			<select id="submenu">
				<option value='-1'>全部明細</option>
				<!--  <option>中部</option>
				<option>東部</option>
				<option>西部</option>-->
				<?php 
				foreach (Constants::$TICKET_ORDER_PROCESS_STATUS as $k => $v) {
					echo "<option class='hsr' value='" . $k . "'>".$v."</option>";
				}
				?>
			</select>
			<i class="fa fa-angle-down"></i>
		</div>
	</div>
	<section id="myOrders" class="myOrders">

	</section>
	<section id="myOrders-m" class="myOrders-m">

	</section>
	<!-- pagination -->
	<ul class="pagination" id="pagination"></ul>
	<div id="popupCar" class="popupCar">
		<div class="closeBtn">
			<i class="fa fa-times" aria-hidden="true"></i>
		</div>
		<div class="member">
			<? if(!empty($avatar)){ ?>
				<div class="img">
					<img src="<?php echo $img_server, $avatar, '?', time();?>" alt="" class="img">
				</div>
			<? } else { ?>
				<div class="img"></div>
			<?php } ?>
			<div class="info">
				<div class="text">聯繫業者</div>
				<div class="wrap">
					<div class="text">發訊息給</div>
					<div id="partner"></div>
				</div>
			</div>
		</div>
		<div class="note">
			如果網頁上的建議行程不符合你的需求，或是在訂購前有其他問題，你可在此發訊息詢問。
		</div>
		<form role="form" id="form1" method="post" action="<?php echo $form_url; ?>">
			<div class="opt">
				<input id="co_id" type="hidden" name="co_id" value="">
				<div class="blank1">
					<i class="fa fa-calendar"></i>
					<input id="begin_date" type="date" name="begin_date" maxlength="10">
				</div>
				<div class="blank1" style="border-radius: 0px 0px 0px 0px;">
					<i class="fa fa-map-marker"></i>
					<select id="end_area" name="end_area">
						<option selected disabled>選擇目的地</option>
						<?php
	                        if (!empty($area_list)) {
	                            foreach ($area_list as $a) {
	                    ?>
	                    <option value="<?php echo $a["a_name"]; ?>"><?php echo $a["a_name"]; ?></option>
	                    <?php
	                            }
	                        }
	                    ?>
					</select>
					<i class="fa fa-angle-down" <?php if ($deviceType == "phone") { ?>style="right:5px;"<?php } ?>></i>
				</div>
				<div class="blank2" style="border-radius: 0px 0px 0px 0px;">
					<i class="fa fa-calendar"></i>
					<select id="car_day" name="car_day">
						<option value="" selected disabled>天數</option>
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
					</select>
					<i class="fa fa-angle-down" style="right:10px;"></i>
				</div>
				<div class="blank2">
					<i class="fa fa-user"></i>
					<select id="car_people" name="car_people">
						<option value="" selected disabled>人數</option>
						<?php for ($i = 1; $i <= $people_count; $i++) { ?>
	                        <option value="<?php echo $i; ?>"><?php echo $i; ?>人</option>
	                    <?php } ?>
					</select>
					<i class="fa fa-angle-down" style="right:10px;"></i>
				</div>
			</div>
			<textarea id="message" name="message" rows="6" placeholder="請填上您想詢問的內容或是有什麼其他的需求？"></textarea>
		</form>
		<div class="btnWrap">
			<button class="btn" id="submit2">發送訊息</button>
		</div>
	</div>
	<div class="popupQR">
		<div class="closeBtn">
			<i class="fa fa-times" aria-hidden="true"></i>
		</div>
		<h4>乘車憑證</h4>
		<div class="wrap">
			<img src="https://chart.googleapis.com/chart?cht=qr&chl=1234&chs=120x120&choe=UTF-8" class="QRcode">
		</div>
	</div>
</div>
<script type="text/javascript">
	$(function(){
		var caneldar_option = <?= json_encode(Constants::$CALENDAR_OPTIONS) ?>;
		$('#begin_date').datepicker(caneldar_option).datepicker('option', {minDate: new Date()});
		
		<?php if ($deviceType == "computer") { ?>
		$( "#myOrders" ).load( "/web/pages/member/embed/orders/orders-charter.php" );
		<?php } else { ?>
		$( "#myOrders-m" ).show();
		$( "#myOrders-m" ).load( "/web/pages/member/embed/orders/orders-charter-m.php" );
		<?php } ?>
		$("#homestay").on("click", function(){
			location.href = '/member/invoice/';
		});
		$("#charter").on("click", function(){
			$("#all").removeClass("selected");
			$("#homestay").removeClass("selected");
			$("#charter").addClass("selected");
			$("#pickup").removeClass("selected");
			$("#tourbus").removeClass("selected");
			$("#4g").removeClass("selected");
			$("#hsr").removeClass("selected");
			<?php if ($deviceType == "computer") { ?>
			$( "#myOrders" ).load( "/web/pages/member/embed/orders/orders-charter.php" );
			<?php } else { ?>
			$( "#myOrders-m" ).load( "/web/pages/member/embed/orders/orders-charter-m.php" );
			<?php } ?>
		});
		$("#pickup").on("click", function(){
			$("#all").removeClass("selected");
			$("#homestay").removeClass("selected");
			$("#charter").removeClass("selected");
			$("#pickup").addClass("selected");
			$("#tourbus").removeClass("selected");
			$("#4g").removeClass("selected");
			$("#hsr").removeClass("selected");
			<?php if ($deviceType == "computer") { ?>
			$( "#myOrders" ).load( "/web/pages/member/embed/orders/orders-pickup.php" );
			<?php } else { ?>
			$( "#myOrders-m" ).load( "/web/pages/member/embed/orders/orders-pickup-m.php" );
			<?php } ?>
		});
		$("#tourbus").on("click", function(){
			$("#all").removeClass("selected");
			$("#homestay").removeClass("selected");
			$("#charter").removeClass("selected");
			$("#pickup").removeClass("selected");
			$("#tourbus").addClass("selected");
			$("#4g").removeClass("selected");
			$("#hsr").removeClass("selected");
			<?php if ($deviceType == "computer") { ?>
			$( "#myOrders" ).load( "/web/pages/member/embed/orders/orders-tourbus.php" );
			<?php } else { ?>
			$( "#myOrders-m" ).load( "/web/pages/member/embed/orders/orders-tourbus-m.php" );
			<?php } ?>
		});
		$("#hsr").on("click", function(){
			$("#all").removeClass("selected");
			$("#homestay").removeClass("selected");
			$("#charter").removeClass("selected");
			$("#pickup").removeClass("selected");
			$("#tourbus").removeClass("selected");
			$("#4g").removeClass("selected");
			$("#hsr").addClass("selected");
			<?php if ($deviceType == "computer") { ?>
			$( "#myOrders" ).load( "/web/pages/member/embed/orders/orders-hsr.php" );
			<?php } else { ?>
			$( "#myOrders-m" ).load( "/web/pages/member/embed/orders/orders-hsr-m.php" );
			<?php } ?>
		});
		$("#change").on("change", function(){
			var value = $("#change").val();
			if (value == "charter") {
				$( "#myOrders-m" ).load( "/web/pages/member/embed/orders/orders-charter-m.php" );
			} else if (value == "pickup") {
				$( "#myOrders-m" ).load( "/web/pages/member/embed/orders/orders-pickup-m.php" );
			} else if (value == "tourbus") {
				$( "#myOrders-m" ).load( "/web/pages/member/embed/orders/orders-tourbus-m.php" );
			} else if (value == "hsr") {
				$( "#myOrders-m" ).load( "/web/pages/member/embed/orders/orders-hsr-m.php" );
			}
		});
		$("#prexPage2").click(function(){
        	window.location.href = '/member/';
		});
		// 聯繫業者視窗-內容檢查
        $('#popupCar .btnWrap #submit2').click(function () {
            var msg = '';
            var message = $('#popupCar #message').val();
            if (message == '') {
                msg += '詢問的內容必須填寫!\n';
            }
            if (msg != '') {
                alert(msg);
            } else {
                $('#popupCar #form1').submit();
            }
        });
        $("#submenu").on("change", function(){
			if ($("#change").val() == "hsr") {
				if ($("#submenu").val() == -1) {
					$(".order").show();
				} else {
					$(".order").hide();
					$(".status_" + $("#submenu").val()).show();
				}
			}
        });
	})
</script>