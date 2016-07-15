<?php
/**
 *  說明：旅宿訂購流程 - 訂購確認 & 資料填寫頁
 *  作者：Steak <steak@fullerton.com.tw>
 *  日期：2015年12月17日
 *  備註：
 *  2015-12-17 John 於輸入優惠代碼欄位下方新增一「查詢我的優惠券」連結，點選後會另開一新視窗
 */

header("Content-Type: text/html; charset=utf-8");
require_once __DIR__ . '/../../config.php';
error_reporting(E_ALL);
$tripitta_web_service = new tripitta_web_service();
$tripitta_homestay_service = new tripitta_homestay_service();
$tripitta_api_client_service = tripitta_api_client_service::__get_instance(tripitta_api_client_service::SITE_TRIPITTA_WEB_TW);

$homeStayId = $_REQUEST['homeStayId'];
$area_code = $_REQUEST["area_code"];
$beginDate = $_REQUEST['beginDate'];
$endDate = $_REQUEST['endDate'];
$roomType = $_REQUEST['roomType'];
$roomQuantity = $_REQUEST['roomQuantity'];
$selectRoom = $_REQUEST['selectRoom'];
$pc_ids = $_REQUEST["pc_ids"];
$shoppingCartId = $_REQUEST['shoppingCartId'];

$user_row = !empty($_SESSION[USER_DATA]) ? $_SESSION[USER_DATA] : '';
$dateDiff = (strtotime($endDate) - strtotime($beginDate)) / (86400);

if (empty($shoppingCartId)) {
	header('Location: /booking/'.$area_code.'/'.$homeStayId.'/');
	exit();
}

// 檢查購物車中的 room 資料是否有效
$data = array('shopping_cart_id'=>$shoppingCartId);
$result = $tripitta_api_client_service->query_shopping_cart($data);
if ('0000' != $result['data']['code']) {
	writeLog('booking_info 購物車 ' . $shoppingCartId . ' ' . $result['data']['msg']);
	alertmsg('保留的房間已被釋出!!', '/booking/'.$area_code.'/'.$homeStayId.'/');
}

// 取得匯率
$currency_id = $tripitta_web_service->get_display_currency();
$currency_code = NULL;
$exchange_rate = 1;
if (1 == $currency_id) {
    $currency_code = 'NTD';
    $exchange_rate = 1;
}
else {
    $exchange = $tripitta_homestay_service->get_exchange_by_currency_id($currency_id);
    $currency_code = $exchange['cr_code'];
    $exchange_rate = $exchange['erd_rate'];
}

$idx = 0;
$selectRoomDataList = array();
$ary = preg_split('/,/', $selectRoom);
foreach ($ary as $a) {
	$t = preg_split('/_/', $a); // $t: rtId, booking room quantity
	if ($t[1] == 0) continue;

	$selectRoomDataList[$idx]['rt_id'] = $t[0];
	$selectRoomDataList[$idx]['room_count'] = $t[1];
	$selectRoomDataList[$idx]['p_type'] = $t[2];
	$selectRoomDataList[$idx]['p_id'] = $t[3];
	$selectRoomDataList[$idx]['c_status'] = $t[4];
	$selectRoomDataList[$idx]['c_day'] = $t[5];
	$selectRoomDataList[$idx]['p_name'] = $t[6];
	$idx++;
}

$home_stay_row = $tripitta_homestay_service -> get_home_stay_info($homeStayId);
$room_type_row = $tripitta_homestay_service -> get_booking_room_type_info($selectRoomDataList , $beginDate, $endDate, $exchange_rate);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<? include __DIR__ . "/../common/head.php"; ?>
<title>旅宿預訂 - Tripitta 旅必達</title>
<style type="text/css">
#noticeDiv {z-index:115;display:none}
div.notice {z-index:115;display:none}
A.T01_btn01 {
    font-size: 15px;
    color: #000000;
    border: 1px solid #CCCCCC;
    background-color: #83C705;
    padding-top: 8px;
    padding-right: 30px;
    padding-bottom: 9px;
    padding-left: 30px;
    text-decoration: none;
}
.search_T16gary {
    font-family: "arial","Hiragino Sans GB","Microsoft JhengHei","Microsoft Yahei","sans-serif";
    font-size: 16px;
    color: #666666;
}
.search_T20red {
    font-family: "arial","Hiragino Sans GB","Microsoft JhengHei","Microsoft Yahei","sans-serif";
    font-size: 20px;
    color: #FF0000;
}
</style>
<link rel="stylesheet" href="/web/css/main.css?01121536">
<script src="/web/js/common.js"></script>
<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
<script type="text/javascript">
$(function(){
	$(".errMsg").hide();
	refreshCaptcha();
	// 會員登入 -> 登入
	$('#btnLogin2').click( function () {
		$('#login2_is_click').val(1);
		var auto_login = $('#btnLogin2').prop('checked') ? 1:0;
		login_user_center('<?= constants_user_center::USER_CATEGORY_TRIPITTA ?>', $('#login_account').val(), $('#login_password').val(), auto_login);
	});
	// $('.secretData').hide();
	$('input[name=cardType]').click( function(){
		if($(this).val() == 'tripitta.hitrust') {
			$('.secretData').show();
		}else {
			$('.secretData').hide();
		}
	});

});
function refreshCaptcha() {
	var timestamp = Number(new Date());
	$('#capImg').attr('src', '/web/ajax/authimg.php?authType=user&act=refresh&' + timestamp);
}
window.onbeforeunload = function() {
	if ($('#register_is_click').val() == 1
		|| $('#login_is_click').val() == 1
		|| $('#submit_is_click').val() == 1
		|| $('#login2_is_click').val() == 1
		|| $('#currency_is_click').val() == 1
		|| $('#coupon_is_click').val() == 1
		) {

	} else {
		// 釋出房間
		var p = {};
	    p.func = 'release_room';
	    p.cart_id = '<?= $shoppingCartId ?>';
	    // p.pc_ids = '<?= $pc_ids ?>';
	    console.log(p);
	    $.post("/web/ajax/ajax.php", p, function(data) {
	        if(data.code == '9999'){
	            alert(data.msg);
	        }
	    }, 'json').done(function() { }).fail(function() { }).always(function() { });

		var msg = '注意!!! 已將保留的房間釋出，請重新選擇!!\n\n';
		msg += '重新整理頁面或關閉頁面，系統將釋出保留的房間。';
		return msg;
	}
}
function showLoginDiv() {
	$('#loginForm').show();
	$('#btnLogin').hide();
	$('#btnLogin2').show();
}
function checkdata(){
	var msg = '';
	if ($('#userName').val() == '') {
		$('#userName').focus();
		alert('請輸入訂購人姓名!!');
		return;
	}
	if ($('#userEmail').val() == '') {
		$('#userEmail').focus();
		alert('請輸入訂購人E-mail!!');
		return;
	}
	if (!verifyEmailAddress($('#userEmail').val())) {
		$('#userEmail').focus();
		alert('請檢查訂購人E-mail格式是否正確!!');
		return;
	}
	if ($('#userMobilePhone').val() == '') {
		$('#userMobilePhone').focus();
		alert('請輸入訂購人電話!!');
		return;
	}
	<?php if(empty($user_row)) { ?>
	if ($('#userPassword').val() == '') {
		$('#userPassword').focus();
		alert('請輸入密碼!!');
		return;
	}else{
		var ret = verify_password($('#userPassword').val());
		if(ret != ''){
			alert(ret);
			return;
		}
	}
	if ($('#userPassword2').val() == '') {
		$('#userPassword2').focus();
		alert('請輸入確認密碼!!');
		return;
	}else{
		var ret = verify_password($('#userPassword2').val());
		if(ret != ''){
			alert(ret);
			return;
		}
	}
	if ($('#userPassword').val() != $('#userPassword2').val()) {
		alert('密碼與再次輸入的密碼不符合!!');
		$('#userPassword').val('');
		$('#userPassword2').val('');
		return;
	}
	if($("input[name='policyChk']:checked").length == 0) {
		$('#policyChk').focus();
		alert('請先閱讀，並同意Tripitta會員條款相關規範!!');
		return;
	}
	<?php } ?>
	if ($('#roomerName').val() == '') {
		$('#roomerName').focus();
		alert('請輸入住房旅客姓名!!');
		return;
	}
	if ($('#roomerMobilePhone').val() == '') {
		$('#roomerMobilePhone').focus();
		alert('請輸入住房旅客手機門號!!');
		return;
	}
	if ($('#roomerEmail').val() == '') {
		$('#roomerEmail').focus();
		alert('請輸入住房旅客電子郵件!!');
		return;
	}
	if($("input[name='roomPolicyChk']:checked").length == 0) {
		$('#roomPolicyChk').focus();
		alert('請先閱讀，並同意訂房條款和取消規定之內容!!');
		return;
	}
	if($("input[name='cardType']:checked").length == 0) {
		$('#cardType').focus();
		alert('請選擇付款方式!!');
		return;
	}

	if($('input[name=cardType]:checked').val() == "tripitta.hitrust" && $('#totals').val() != 0) {
		var d = new Date();
		var thisYear = d.getFullYear();
		var thisMonth = d.getMonth() + 1;
// 		if (thisYear == $('#ccExpYear').val() && thisMonth > $('#ccExpMonth').val()) {
// 			$('#ccExpMonth').focus();
// 			alert('有效月/年錯誤!!');
// 			return;
// 		}
		if($('#ccNo1').val() == '' || $('#ccNo2').val() == '' ||　$('#ccNo3').val() == '' || $('#ccNo4').val() == ''){
			$('#ccNo1').focus();
			alert('請輸入信用卡卡號!');
			return;
		}

		if($('#ccNo1').val().length != 4){
			$('#ccNo1').focus();
			alert('刷卡資料錯誤!!');
			return;
		}

		if($('#ccNo2').val().length != 4){
			$('#ccNo2').focus();
			alert('刷卡資料錯誤!!');
			return;
		}

		if($('#ccNo3').val().length != 4){
			$('#ccNo3').focus();
			alert('刷卡資料錯誤!!');
			return;
		}

		if($('#ccNo4').val().length != 4){
			$('#ccNo4').focus();
			alert('刷卡資料錯誤!!');
			return;
		}

		if ($('#cvc2').val() == '') {
			$('#cvc2').focus();
			alert('請輸入信用卡安全碼!!');
			return;
		}
		$('#ccNo').val($('#ccNo1').val() + $('#ccNo2').val() + $('#ccNo3').val() + $('#ccNo4').val());

		// 驗證認證碼是否正確
		var data = {'captchaCode': $('#captchaCode').val(), 'type': 'user'};
		$.getJSON('/web/ajax/ajax.php',
			{func: 'checkCaptchaCode', data: data},
			function(jsonData) {cbCheckCaptcha(jsonData);}
	    );
	}

	$('#submit_is_click').val(1);
	if($('input[name=cardType]:checked').val() == "tripitta.hitrust") {
		$('#form1').submit();
	}else if($('input[name=cardType]:checked').val() == "tripitta.china.union")  {
		msg = "提醒您，當您按下「銀聯卡付款」，即進入銀聯卡扣款系統，當您輸入卡號及相關資料完成認証後，訂單即成立，且同時直接於您的銀聯卡帳戶中扣款完成，若您想要更改或取消訂單，相關扣款規定，請務必詳閱「訂購須知」。";
		if (confirm(msg)) {
			showNotice('paymentChannelNotice_11');
		}
	}
}

function cbCheckCaptcha(jsonData) {
	if (jsonData) {
		if (1 == jsonData['error']) {
			$('#captchaCode').focus();
			//$('#confirmBtn').attr('href','javascript:chkData()');
			$('#confirmBtn').attr('click','javascript:chkData()');
			alert('驗證碼資料錯誤!!');
			return;
		}
		else if (1 == jsonData['success']) {
			checkCreditCardNumber(); // 驗證信用卡號是否正確
		}
	}
}

//登入會員中心
function login_user_center(category, account, password, auto_login) {
	var p = {};
    p.func = 'login';
    p.category= category;
    p.account = account;
    p.password = password;
    p.auto_login = auto_login;
    console.log(p);
    $.post("/web/ajax/ajax.php", p, function(data) {
        if(data.code == '9999'){
            alert(data.msg);
        } else {
            location.reload();
        }
    }, 'json').done(function() { }).fail(function() { }).always(function() { });
}

function copyUserInfo() {
	$('#roomerName').val($('#userName').val());
	$('#roomerMobilePhone').val($('#userMobilePhone').val());
	$('#roomerCountryCode').val($('#userCountryCode').val());
	$('#roomerEmail').val($('#userEmail').val());
	$('#roomerGender').val($('#userGender').val());
}

function findUserByEmail() {
	var p = {};
    p.func = 'find_user_by_email';
    p.email = $('#userEmail').val();
    console.log(p);
    $.post("/web/ajax/ajax.php", p, function(data) {
        if(data.code == '9999'){
            alert(data.msg);
            $('#userEmail').val('');
        }
    }, 'json').done(function() { }).fail(function() { }).always(function() { });
}

function chekCoupon() {
	$('#coupon_is_click').val(1);
	var p = {};
    p.func = 'check_coupon';
	p.hs_id = <?= $homeStayId ?>;
	p.cart_id = '<?= $shoppingCartId ?>';
	p.exchange_rate = <?= $exchange_rate ?>;
	p.payment_code = $("input[name='cardType']:checked").val();
    p.number = $('#coupon').val();
    p.total_amount = $('#total_amount').val();
    console.log(p);
    $.post("/web/ajax/ajax.php", p, function(data) {
        if (data.code == '9999') {
            alert(data.msg);
            $('#coupon').val('');
        } else {
			$('#discountDetail').show();
			$('#discountVal').html(data.msg.discount);
			var total = parseInt($('#total_amount').val()) - parseInt(data.msg.discount);
			var twd_total = parseInt($('#tatal_twd_amount').val()) - parseInt(data.msg.twd_discount);

			if(total <= 0) total = 0;
			if(twd_total <= 0) twd_total = 0;

			$('#total').html(total);
			$('#exchange').html(twd_total);
			$('#totals').val(twd_total);

			if(total == 0) $('.secretData2').hide();
        }
    }, 'json').done(function() { }).fail(function() { }).always(function() { });
}

function change_gender_pic(){
	var gender = $('#userGender').val();
	if(gender == 0) {
		$('#gender_pic').addClass('fa-venus');
		$('#gender_pic').removeClass('fa-mars');
	}else if(gender == 1){
		$('#gender_pic').removeClass('fa-venus');
		$('#gender_pic').addClass('fa-mars');
	}
}

function showNotice(objId) {
	$(".overlay").show();
    //$("#" + objId).css("left", ($(window).width() - $("#" + objId).width()) / 2);
    $("#" + objId).show();
}

function confirmToPay() {
	$('#form1').submit();
}
</script>
</head>
<body>
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<form id="form1" name="form1" method="post" action="shopping_cart_to_order.php">
	<input type="hidden" name="pcCode" id="pcCode" value=""/>
	<input type="hidden" id="homeStayId" name="homeStayId" value="<?php echo $homeStayId?>"/>
	<input type="hidden" name="area_code" value="<?php echo $area_code ?>"/>
	<input type="hidden" id="roomType" name="roomType" value="<?php echo $roomType ?>"/>
	<input type="hidden" id="roomQuantity" name="roomQuantity" value="<?php echo $roomQuantity ?>"/>
	<input type="hidden" id="shoppingCartId" name="shoppingCartId" value="<?php echo $shoppingCartId ?>"/>
	<input type="hidden" id="ccNo" name="ccNo"/>
	<div class="payStep-container">
		<h1 class="title">付款步驟</h1>
		<div class="tile">
			<aside>
				<?php if(!empty($user_row)) { ?>
				<hgroup class="hadLoginGroup">
					<h1>
						您好，
						<span class="peopleName"><?= $user_row['nickname'] ?></span>
					</h1>
					<h2>歡迎繼續訂購您喜歡的旅宿</h2>
					<h3>
						訂購人資料
						<span class="peopleName"><?= $user_row['nickname'] ?></span>
						<?php if(!empty($user_row['gender'])) { ?>
							<?php if($user_row['gender'] == 'F') { ?>
							小姐
							<?php }else if($user_row['gender'] == 'M') { ?>
							先生
							<?php } ?>
						<?php } ?>
					</h3>
				</hgroup>
				<?php }else { ?>
				<div class="loginGroup">
					<section class="loginForm" id="loginForm">
						<h1>會員登入</h1>
						<div class="label">
							<div class="icon">
								<i class="img-member-mail"></i>
							</div>
							<div class="input">
								<input id="login_account" type="text" autocomplete="off" placeholder="請輸入E-mail" maxlength="50">
							</div>
						</div>
						<div class="label">
							<div class="icon">
								<i class="img-member-lock"></i>
							</div>
							<div class="input">
								<input id="login_password" type="password" autocomplete="off" placeholder="請輸入密碼" maxlength="20">
							</div>
						</div>
					</section>
					<button type="button" class="btnLogin" id="btnLogin" onclick="showLoginDiv()">會員登入</button>
					<button type="button" class="btnLogin" id="btnLogin2" style="display: none;">登入</button>
					<input type="hidden" id="login2_is_click" value="0" />
				</div>
				<?php } ?>
				<div class="wrapper">
					<div class="orderForm">
						<h1>訂購人資料</h1>
						<div class="label">
							<div class="icon">
								<i class="img-member-user"></i>
							</div>
							<div class="input">
								<div class="inputSub">
									<input id="userName" name="userName" type="text" autocomplete="off" placeholder="請輸入訂購人姓名" maxlength="20" value="<?= !empty($user_row) ? $user_row['nickname'] : '' ?>">
								</div>
								<div class="genderSelect">
									<i class="fa fa-venus" id="gender_pic"></i>
									<select class="gender" name="userGender" id="userGender" onchange="change_gender_pic()">
										<option value="F" <?php if(!empty($user_row)) if($user_row['gender'] == 'F') { ?>selected<?php } ?>>女士</option>
										<option value="M" <?php if(!empty($user_row)) if($user_row['gender'] == 'M') { ?>selected<?php } ?>>男士</option>
									</select>
									<i class="fa fa-angle-down"></i>
								</div>
							</div>
						</div>
						<div class="label">
							<div class="icon">
								<i class="img-member-mail"></i>
							</div>
							<div class="input">
								<input id="userEmail" name="userEmail" type="text" autocomplete="off" placeholder="請輸入E-mail" maxlength="50" value="<?= !empty($user_row) ? $user_row['account'] : '' ?>" <?php if(empty($user_row)) { ?>onchange="findUserByEmail()"<?php } ?>>
							</div>
						</div>
						<div class="label">
							<div class="icon">
								<i class="img-member-phone"></i>
							</div>
							<div class="input">
								<div class="phoneSelect">
									<select class="phone" name="userCountryCode" id="userCountryCode">
									<?php
									foreach(constants_user_center::$LIVING_COUNTRY_TEXT as $key => $value) {
									    echo '<option value="', $key, '"';
									    if(!empty($user_row)) if($user_row['living_country_id'] == $key) echo ' selected';
									    echo '>', $value, '</option>';
									}
									?>
									</select>
									<i class="fa fa-angle-down"></i>
								</div>
								<div class="inputSub">
									<input name="userMobilePhone" id="userMobilePhone" type="text" autocomplete="off" placeholder="請輸入電話" maxlength="20" value="<?= !empty($user_row) ? $user_row['mobile'] : '' ?>">
								</div>
							</div>
						</div>
						<?php if(empty($user_row)) { ?>
						<div class="label">
							<div class="icon">
								<i class="img-member-lock"></i>
							</div>
							<div class="input">
								<input name="userPassword" id="userPassword" type="password" autocomplete="off" placeholder="請輸入密碼，6~20碼英數字" maxlength="20">
								<div class="errMsg">密碼錯誤</div>
							</div>
						</div>
						<div class="label">
							<div class="icon">
								<i class="img-member-lock"></i>
							</div>
							<div class="input">
								<input name="userPassword2" id="userPassword2" type="password" autocomplete="off" placeholder="請確認密碼，6~20碼英數字" maxlength="20">
								<div class="errMsg">密碼錯誤</div>
							</div>
						</div>
						<label for="policyChk">
							<input type="checkbox" id="policyChk" name="policyChk" class="policyChk">
							 本人已同意成為Tripitta會員，並已同意
							<a href="/terms/" class="policyLink" target="_blank" >Tripitta會員條款相關規範</a>
						</label>
						<?php } ?>
					</div>
					<!-- 入住人資料 -->
					<div class="checkMan">
						<h1>入住人資料</h1>
						<h2>請輸入入住房客的姓名，姓名必須與民宿退房時所示的身分證明文件相同。</h2>
						<label for="copyUser">
							<input type="checkbox"class="theSame" id="copyUser" name="copyUser" onclick="copyUserInfo()"> 同訂購人
						</label>
						<div class="label">
							<div class="icon">
								<i class="img-member-user"></i>
							</div>
							<div class="input">
								<div class="inputSub">
									<input name="roomerName" id="roomerName" type="text" autocomplete="off" placeholder="入住房客姓名" maxlength="20">
								</div>
								<div class="genderSelect">
									<i class="fa fa-venus"></i>
									<select class="gender" name="roomerGender" id="roomerGender">
										<option value="F">女士</option>
										<option value="M">男士</option>
									</select>
									<i class="fa fa-angle-down"></i>
								</div>
							</div>
						</div>
						<div class="label">
							<div class="icon">
								<i class="img-member-mail"></i>
							</div>
							<div class="input">
								<input name="roomerEmail" id="roomerEmail" type="text" autocomplete="off" placeholder="請輸入E-mail" maxlength="50">
							</div>
						</div>
						<div class="label">
							<div class="icon">
								<i class="img-member-phone"></i>
							</div>
							<div class="input">
								<div class="phoneSelect">
									<select class="phone" name="roomerCountryCode" id="roomerCountryCode">
									<?php
									foreach(constants_user_center::$LIVING_COUNTRY_TEXT as $key => $value) {
									    echo '<option value="', $key, '"';
									    echo '>', $value, '</option>';
									}
									?>
									</select>
									<i class="fa fa-angle-down"></i>
								</div>
								<div class="inputSub">
									<input name="roomerMobilePhone" id="roomerMobilePhone" type="text" autocomplete="off" placeholder="請輸入電話" maxlength="20">
								</div>
							</div>
						</div>
						<h1 class="requtTitle">
							特殊需求
						</h1>
						<textarea class="txtArea" id="memo" name="memo" placeholder="請在此輸入注意事項：是否有加人/加床需求，是否有攜帶兒童/寵物，延遲入住，不住一樓等" maxlength="500"></textarea>
						<h5>優惠房間有限，請立即預約！</h5>
					</div>
				</div>
			</aside>
			<article>
				<h1 class="storeName"><?= $home_stay_row['name']?></h1>
				<div class="checkDate">
					<div class="checkIn">
						<h5>入住日期</h5>
						<p class="checkInDate"><?= $beginDate ?></p>
						<input type="hidden" name="beginDate" value="<?php echo $beginDate ?>"/>
					</div>
					<div class="checkOut">
						<h5>退房日期</h5>
						<p class="checkOutDate"><?= $endDate?></p>
						<input type="hidden" name="endDate" value="<?php echo $endDate ?>"/>
					</div>
					<div class="manyDays">
						<span class="days"><?= $dateDiff ?></span>
						<span>晚住宿</span>
					</div>
				</div>

				<!-- room list -->
				<div class="roomList">
					<?php
					$total_amount = 0;
					$tatal_twd_amount = 0;
					foreach ($room_type_row as $rt) { ?>
					<section>
						<h1><?= $rt['name'] ?></h1>
						<h2><?= $rt['p_name'] ?></h2>
						<div class="roomWrap">
							<p class="deadlineWrap">
								<span class="breakfast"><?= $rt['promotion'][0]['have_breakfast'] ?  '含' : '不含'?>早餐</span>
								<span class="freeDeadline">
								<?php if($rt['c_status'] == 2) {?>
									不可取消
								<?php }else{ ?>
									免費取消期限
									<span><?= date("Y-m-d", strtotime($beginDate."-". $rt['c_day'] ."day")); ?></span>
									前
								<?php } ?>
								</span>
							</p>
							<div class="room">
								<?php foreach ($rt['promotion'] as $p){
									$total_amount += $p['amount'];
									$tatal_twd_amount += $p['ntd_amount'];
								?>
								<h3>
									<p><span class="date"><?= $p['date'] ?></span></p>
									<p><span class="count">1</span>間</p>
									<p><?= $currency_code ?><span class="cost"><?= number_format($p['amount']) ?></span></p>
								</h3>
								<?php } ?>
							</div>
						</div>
					</section>
					<?php } ?>
				</div>

				<div class="couponWrap">
					<input type="text" class="coupon" id="coupon" name="coupon" placeholder="輸入優惠代碼" maxlength="20">
					<button type="button" class="submit" onclick="chekCoupon()">確認優惠代碼</button>
					<input type="hidden" id="coupon_is_click" value="0" />
				</div>
				<a href="/member/coupon/" class="howGetCode" target="_blank">< 查詢我的優惠券 ></a>

				<!-- payment -->
				<div class="favorableWrap">
					<input type="hidden" id="total_amount" name="total_amount" value="<?= $total_amount ?>" />
					<input type="hidden" id="tatal_twd_amount" name="tatal_twd_amount" value="<?= $tatal_twd_amount ?>" />
					<div class="detail">
						<section id="discountDetail">
							<p>優惠折扣</p>
							<p><?= $currency_code ?></p>
							<p>
								<span>-</span>
								<span class="discountVal" id="discountVal">300</span>
							</p>
						</section>
					</div>
					<div class="totalWrap">
						<section>
							<p>總價</p>
							<p><?= $currency_code ?></p>
							<p class="total" id="total" ><?= number_format($total_amount) ?></p>
							<input type="hidden" id="totals" value="<?= $total_amount ?>" />
						</section>
					</div>
					<div class="exchangeWrap">
						<p>
							<span>NTD</span>
							<span class="exchange" id="exchange"><?= number_format($tatal_twd_amount) ?> </span>
						</p>
						<h3>本金額僅供參考，實際消費金額以當日結帳匯率為主</h3>
					</div>
				</div>
			</article>
		</div>


		<!-- payment -->
		<div class="payment">
			<section class="method secretData2">
				<h2>付款方式</h2>
				<div class="cards">
					<?php if($selectRoomDataList[0]['p_type'] != 2){?>
					<label for="debit" class="debit">
						<input type="radio" name="cardType" id="debit" value="tripitta.hitrust" checked>
						<div class="inOrder">
							<span>信用卡 / debit 卡</span>
							<div class="visaGroup">
								<div class="img-visa"></div>
							</div>
						</div>
					</label>
					<?php } ?>
					<label for="unionPay" class="unionPay">
						<input type="radio" name="cardType" id="unionPay" value="tripitta.china.union" <?php if($selectRoomDataList[0]['p_type'] == 2){?>checked<?php } ?>>
						<div class="inOrder">
							<span>銀聯卡</span>
							<div class="img-unionpay"></div>
						</div>
					</label>
					<!--
					<label for="alipay" class="alipay">
						<input type="radio" name="cardType" id="alipay" value="esun.alipay">
						<div class="inOrder">
							<span>支付寶(頁面將跳轉至支付寶頁面)</span>
							<div class="img-alipay"></div>
						</div>
					</label>
					-->
				</div>
			</section>
			<?php if($selectRoomDataList[0]['p_type'] != 2){?>
			<section class="secretData secretData2">
				<div class="name" style="display:none;">
					<h2>付款人姓名</h2>
					<div class="label">
						<div class="icon">
							<i class="img-member-user"></i>
						</div>
						<div class="input">
							<div class="inputSub">
								<input type="text" autocomplete="off" placeholder="付款人姓名" maxlength="20">
								<div class="errMsg">密碼錯誤</div>
							</div>
							<div class="genderSelect">
								<i class="fa fa-venus"></i>
								<select class="gender">
									<option value="0">女士</option>
									<option value="1" selected>男士</option>
								</select>
								<i class="fa fa-angle-down"></i>
							</div>
						</div>
					</div>
				</div>
				<div class="cardNo">
					<h2>信用卡卡號</h2>
					<div class="input">
						<div class="inputSub">
							<input type="text" autocomplete="off" maxlength="4" class="no1" name="ccNo1" id="ccNo1" onkeyup="if(this.value.length == 4)$('#ccNo2').focus();">
							<p>-</p>
							<input type="text" autocomplete="off" maxlength="4" class="no2" name="ccNo2" id="ccNo2" onkeyup="if(this.value.length == 4)$('#ccNo3').focus();">
							<p>-</p>
							<input type="text" autocomplete="off" maxlength="4" class="no3" name="ccNo3" id="ccNo3" onkeyup="if(this.value.length == 4)$('#ccNo4').focus();">
							<p>-</p>
							<input type="text" autocomplete="off" maxlength="4" class="no4" name="ccNo4" id="ccNo4" onkeyup="if(this.value.length == 4)$('#ccExpMonth').focus();">
						</div>
					</div>
				</div>
				<div class="creditExpire">
					<h2>有效日期</h2>
					<div class="selectWrap">
						<div class="expireMMSelect">
							<select class="month" id="ccExpMonth" name="ccExpMonth">
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
						<p>|</p>
						<div class="expireYYSelect">
							<select class="year" id="ccExpYear" name="ccExpYear">
								<?php
								$thisYear = date('Y');
								for ($i = $thisYear; $i < $thisYear + 12; $i++) echo '<option value=', $i, '>', $i, '</option>';
								?>
							</select>
							<i class="fa fa-angle-down"></i>
						</div>
					</div>
				</div>
				<div class="secureCode">
					<h2>信用卡安全碼</h2>
					<div class="input">
						<input type="text" autocomplete="off" maxlength="3" class="code" name="cvc2" id="cvc2">
						<h5 class="img-security-code"></h5>
					</div>
				</div>
				<div class="captcha">
					<h2>驗證碼</h2>
					<div class="capWrap">
						<img class="service-pic" id="capImg" src="/web/ajax/authimg.php?authType=user" />
						<div class="renewBtn" onclick="refreshCaptcha();">
							<i class="fa fa-repeat"></i>
							換一張圖片試試
						</div>
					</div>
					<div class="input">
						<input type="text" autocomplete="off" id="captchaCode" name="captchaCode" class="capInput" placeholder="請輸入驗證碼" maxlength="10">
						<div class="errMsg">密碼錯誤</div>
					</div>
				</div>
			</section>
			<?php } ?>
			<!-- policy -->
			<section class="policyWrap">
				<h1>訂房條款和取消規定</h1>
				<div class="policy">
				<?php include_once('booking_notice.php')?>
				</div>
				<label for="roomPolicyChk" class="checkWrap">
					<input type="checkbox" id="roomPolicyChk" name="roomPolicyChk">
					<p>我已閱讀並同意訂房條款和取消規定之內容</p>
				</label>
				<label class="btnWrap">
					<button type="button" class="submit" onclick="checkdata()">送出</button>
					<input type="hidden" id="submit_is_click" value="0" />
				</label>
			</section>
			<div id="paymentChannelNotice_11" class="div_introduction_010 notice" style="display:none;top: -30px;position: absolute;">
			  <table width="800" border="0" align="center" cellpadding="0" cellspacing="0" style="background-color:#ffffff;">
			    <tr>
			      <td height="350" valign="top" style="padding-left: 20px;"><table width="150" border="0" align="center" cellpadding="0" cellspacing="0">
			        <tr>
			          <td height="45"></td>
			        </tr>
			      </table>
			        <table width="740" border="0" align="center" cellpadding="0" cellspacing="0">
			          <tr>
			            <td height="100" class="search_T16gary"><center><span class="search_T20red">~~ 敬請注意以下說明 ~~</span></center>
			              <br/>下一步，即會進入 銀聯卡在線支付中心 之頁面，
			              <br/>請選擇使用【銀聯卡支付】，才可正確完成訂房程序。
			              <br/>勿選擇【網銀支付】，以免造成付款後，但無法成功訂房之問題。 請務必注意…如下圖
			            </td>
			          </tr>
			        </table>
			        <table width="460" border="0" align="center" cellpadding="0" cellspacing="0">
			          <tr>
			            <td height="360" align="left" valign="top"><img src="/web/img/payment_channel/cathybk_china_union.gif" width="763" height="393" /></td>
			          </tr>
			        </table>
			        <table width="763" border="0" align="center" cellpadding="0" cellspacing="0">
			          <tr>
			            <td height="60" align="center" valign="middle" style="text-align: center;-align: me;"><input type="button" onclick="confirmToPay()" class="T01_btn01" value="我已經了解，繼續付款" style="cursor: pointer;"/></td>
			          </tr>
			        </table>
			        <table width="150" border="0" align="center" cellpadding="0" cellspacing="0">
			          <tr>
			            <td height="45"></td>
			          </tr>
			        </table></td>
			    </tr>
			  </table>
			</div>
		</div>
	</div>
	</form>
	<footer class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
</body>
</html>