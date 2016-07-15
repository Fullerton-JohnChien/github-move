<?php
/**
 * 說明：交通 - 填寫信用卡資訊頁
 * 作者：Steak
 * 日期：2016年6月8日}
 * 備註：這頁宗達還要改，先實作傳値到下一頁
 */
require_once __DIR__ . '/../../../config.php';
header("Content-Type:text/html; charset=utf-8");
$tripitta_service = new tripitta_service();
$error_url = "/transport/";

// 接收前一頁
$pay_steps = get_val("pay_step");
if(!empty($pay_steps)) {
	$pay_step = json_decode($pay_steps, true);
	$_SESSION["pay_step"] = $pay_step;
}else {
	$pay_step = $_SESSION["pay_step"];
	$pay_steps = json_encode($pay_step);
}

$type = $pay_step["type"];
$coupon = $pay_step["coupon"];

// 取得訂購內容
if($type == 1 || $type == 2 || $type == 3 || $type == 4) {
	$action_url = "/web/pages/bookingcar/shopping_cart_to_order.php";
	$fleet_route_detail_row = $tripitta_service -> get_fleet_route_detail($pay_step["type"], $pay_step["fr_id"]);
	if($type == 3) {
		$pay_price = $fleet_route_detail_row["fr_adult_price"] * $pay_step["adult"] + $fleet_route_detail_row["fr_child_price"] * $pay_step["child"];
	}else {
		$pay_price = $fleet_route_detail_row["fr_price"];
	}

	if (empty($fleet_route_detail_row)) {
		alertmsg('取得訂購內容失敗!!', $error_url);
	}
}else if($type == 5){
	// 檢查票券及票價
	// 取得高鐵 - 票券類型資料
	$action_url = "/web/pages/bookingcar/hsr_shopping_cart_to_order.php";
	$t_id = $pay_step["ticket_id"];
// 	$tc_parent_id = $pay_step["tree_config_id"];
// 	$tree_type = $pay_step["tree_type"];
	$start_area = $pay_step["start_area"];
	$end_area = $pay_step["end_area"];
	$ticket_type_price_list = $tripitta_service->find_ticket_type_price_by_ticket_id($t_id, $start_area, $end_area);

	if(empty($ticket_type_price_list)) {
		alertmsg('取得票種票價失敗!!', $error_url);
	}

	$pay_price = 0;
	$t_count = 1;
	foreach ($ticket_type_price_list as $t){
		if($t_count==1){
			$pay_price += $t["ttp_sell_price"] * $pay_step["adult"];
		}elseif($t_count==2){
			$pay_price += $t["ttp_sell_price"] * $pay_step["child"];
		}
		$t_count++;
	}
}

// 是否有折扣
$bonus = 0;
if(empty($coupon) || $coupon == "undefined") {
	$market_list = $tripitta_service -> find_campaign_campaign_must_possessed_by_user_and_type($_SESSION['travel.ezding.user.data']["serialId"], 1);
	if(!empty($market_list)) {
		foreach ($market_list as $ml) {
			$mc_id = $ml["mc_id"];
			$bo_array = $tripitta_service-> cal_marking_campain_discount($pay_price, $mc_id, $_SESSION['travel.ezding.user.data']["serialId"]);
			$bonus = $bo_array["data"]["discount"];
			if($bonus > 0) break;
		}
	}
}

$pay_price = $pay_price - $bonus;
?>
<!DOCTYPE html>
<html lang="zh-Hant" prefix="og: http://ogp.me/ns#">
<head>
	<?php include __DIR__ . "/../../common/head_new.php"; ?>
    <link rel="stylesheet" href="/web/css/main.css">
    <link rel="stylesheet" href="/web/css/main2.css">
	<script src="/web/js/lib/jquery/jquery.js"></script>
	<script src="/web/js/main-min.js"></script>
	<script src="/web/js/lib/autogrow/autogrow.min.js"></script>
</head>
<body>
	<header><?php include __DIR__ . "/../../common/header_new.php"; ?></header>
	<?php
	if($header_is_login == 0) {
		alertmsg("需登入才能訂購!", "/transport/");
	}
	?>
	<form id="form1" name="form1" method="post" action="<?php echo $action_url; ?>">
	<input type="hidden" id="pay_step" name="pay_step" value='<?php echo $pay_steps ?>' />
	<main class="transport-payStepCredit-container">
		<h1 class="title">付款步驟</h1>
		<div class="step-m">
			<div class="circle done">1</div>
			<i class="fa fa-arrow-right" aria-hidden="true"></i>
			<div class="circle done">2</div>
			<i class="fa fa-arrow-right" aria-hidden="true"></i>
			<div class="circle done">3</div>
			<i class="fa fa-arrow-right" aria-hidden="true"></i>
			<div class="circle selected">4</div>
		</div>
		<div class="tile">
			<div class="tileContainer">
				<h2>填寫信用卡資訊</h2>
				<input type="checkbox" id="switchHid">
				<div class="flipper">
					<div class="front">
					<!--
						<img src="http://placehold.it/60x40" class="cardType">
						<div class="expText">MONTH / YEAR</div>-->
						<!-- 底下的需要套入資料 -->
						<!--<div class="cNum">1234-4567-7894-1234</div>
						<div class="cName">徐達夫</div>
						<div class="expWrap">
							<span>03</span>
							<span>/</span>
							<span>2015</span>
						</div>-->
						<div class="cardLogo">
							<div class="img-chip"></div>
							<div id="logoImg"></div>
						</div>
						<div class="cardNum"></div>
						<div class="inner">
							<div class="owner"></div>
							<div class="expireWrap">
								<em class="expText">
									MONTH / YEAR
								</em>
								<div class="vaildDate">
									<em class="text">
										VAILD<br>THRU
									</em>
									<div class="date">
										<span id="expMonth"></span>
										<span>/</span>
										<span id="expYear"></span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="back">
						<div class="magneticStripe"></div>
						<div class="sign">
							<div class="signature"></div>
							<div class="last3Num"></div>
						</div>
						<div class="null"></div>

					</div>
				</div>
				<div class="row">
					<div class="priceText">本次應付刷卡總額</div>
					<div class="price">
						<span class="currency">NTD</span>
						<span class="pNum"><?php echo $pay_price; ?></span>
					</div>
				</div>
				<div class="row">
					<div class="block">
						<input type="text" name="ccNo" id="ccNo" placeholder="卡號" maxlength="16">
					</div>
				</div>

				<div class="row">
					<div class="block">
						<input type="text" name="ccName" id="ccName" placeholder="姓名" maxlength="20">
					</div>

				</div>
<div style="color: red;font-size:13px">請輸入印於卡片正面上之英文姓名。若卡面上沒有您的個人英文姓名時，請輸入您護照上之英文姓名。</div>
				<div class="row">
					<div class="block w65">
						<span class="expiryText">到期日</span>
						<div class="sWrap">
							<select id="ccExpMonth" name="ccExpMonth">
								<option selected disabled="">mm</option>
			                    <option value="01">01</option>
			                    <option value="02">02</option>
			                    <option value="03">03</option>
			                    <option value="04">04</option>
			                    <option value="05">05</option>
			                    <option value="06">06</option>
			                    <option value="07">07</option>
			                    <option value="08">08</option>
			                    <option value="09">09</option>
			                    <option value="10">10</option>
			                    <option value="11">11</option>
			                    <option value="12">12</option>
							</select>
							<i class="fa fa-angle-down"></i>
						</div>
						<div class="sWrap">
							<select id="ccExpYear" name="ccExpYear">
								<option selected disabled="">yyyy</option>
								<?php
								$thisYear = date('Y');
								for ($i = $thisYear; $i < $thisYear + 12; $i++) echo '<option value=', $i, '>', $i, '</option>';
								?>
							</select>
							<i class="fa fa-angle-down"></i>
						</div>
					</div>
					<div class="block w30">
						<input type="text" name="cvc2" id="cvc2" placeholder="後三碼" maxlength="3">
					</div>
				</div>
				<div class="row">
					<img src="../../../img/sec/common/hitrust.png" class="hitrust">
					<div class="secuText">
						本交易傳送資訊將藉由HiTRUST 128bits SSL 伺服器憑證進行資料安全保護
					</div>
				</div>
				<div class="rowBtn">
					<button type="button" class="pre" onclick="pre()" >上一步</button>
					<button type="button" class="next" onclick="checkdata()">確定付款</button>
				</div>
			</div>
		</div>
	</main>
	</form>
	<footer><? include __DIR__ . "/../../common/footer_new.php"; ?></footer>
	<script type="text/javascript">
		function checkdata() {
			msg = "";
			if($('#ccNo').val() == ''){
				msg += "請輸入信用卡卡號!\n";

			}
			if ($("#ccNo").val().length != 16) {
				msg += "信用卡號長度不足!!\n";
			}
			if ($('#cvc2').val() == '') {
				msg += "請輸入信用卡安全碼!!\n";
			}

			if ($('#ccExpMonth').val() == null) {
				msg += "請輸入到期月!!\n";
			}

			if ($('#ccExpYear').val() == null) {
				msg += "請輸入到期年!!\n";
			}

			if(msg != '') {
				alert(msg);
				return;
			}else {
				$('#form1').submit();
			}
		}

		function pre() {
			location.href = "/web/pages/bookingcar/pay/pay_step.php";
		}
		$(function () {
		    $("#ccNo, #cvc2").keydown(function (e) {
		        // Allow: backspace, delete, tab, escape, enter and .
		        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
		             // Allow: Ctrl+A, Command+A
		            (e.keyCode == 65 && ( e.ctrlKey === true || e.metaKey === true ) ) ||
		             // Allow: home, end, left, right, down, up
		            (e.keyCode >= 35 && e.keyCode <= 40)) {
		                 // let it happen, don't do anything
		                 return;
		        }
		        // Ensure that it is a number and stop the keypress
		        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
		            e.preventDefault();
		        }

		    });
			$('#ccNo').on("change paste keyup", function() {
		        // visa
		        var re = new RegExp("^4");
		        if ($(this).val().match(re) != null) {
		            $(".flipper").addClass("visa").removeClass("master").removeClass("jcb");
	            	$("#logoImg").addClass("img-visa2").removeClass("img-master").removeClass("img-jcb");
		        }
		        // Mastercard
		        re = new RegExp("^5[1-5]");
		        if ($(this).val().match(re) != null) {
		        	$(".flipper").addClass("master").removeClass("visa").removeClass("jcb");
		        	$("#logoImg").addClass("img-master").removeClass("img-visa2").removeClass("img-jcb");
		        }
		        // JCB
		        re = new RegExp("^35(2[89]|[3-8][0-9])|^1800|^2131");
		        if ($(this).val().match(re) != null) {
		        	$(".flipper").addClass("jcb").removeClass("master").removeClass("visa");
		        	$("#logoImg").addClass("img-jcb").removeClass("img-master").removeClass("img-visa2");
			    }
				var foo = $(this).val().split(" ").join(""); // remove hyphens
				if (foo.length > 0) {
					foo = foo.match(new RegExp('.{1,4}', 'g')).join(" ");
				}
				//$(this).val(foo);
				$('.cardNum').text(foo);
				if ($(this).val() == "") {
					$(".flipper").removeClass("jcb").removeClass("master").removeClass("visa");
					$("#logoImg").removeClass("img-jcb").removeClass("img-master").removeClass("img-visa2");
				}
			});
			$('#ccExpMonth').on('change', function (e) {
				$("#expMonth").text($(this).val());
			});
			$('#ccExpYear').on('change', function (e) {
				$("#expYear").text($(this).val().substr(2,2));
			});
			$("#cvc2").focus(function() {
				$("#switchHid").prop("checked", true);
			});
			$("#ccNo, #ccExpMonth, #ccExpYear, #ccName").focus(function() {
				$("#switchHid").prop("checked", false);
			});
			$('#cvc2').on("change paste keyup", function() {
				$('.last3Num').text($('#cvc2').val());
			});
			$('#ccName').on("change paste keyup", function() {
				$('.signature').text($('#ccName').val());
				$('.owner').text($('#ccName').val());
			});
		});
	</script>
</body>
</html>