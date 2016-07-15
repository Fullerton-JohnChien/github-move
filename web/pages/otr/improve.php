<?php
/**
 * 說明：OTR - 改善此清單頁面
 * 作者：Casper
 * 日期：2016年6月28日
 * 備註：
 */
require_once __DIR__ . '/../../config.php';
header("Content-Type:text/html; charset=utf-8");

$id = get_val("hs_id");
$tc_id = get_val('id');

// 檢查會員是否登入
$tripitta_web_service = new tripitta_web_service();

$login_user_data = $tripitta_web_service->check_login();
if (isset($login_user_data['name']) && $login_user_data['name'] != "") {
	$name = $login_user_data['name'];
} else {
	$name = (isset($login_user_data['nickname'])) ? $login_user_data['nickname'] : "";
}

$email = (isset($login_user_data['email'])) ? $login_user_data['email'] : "";
$phone = (isset($login_user_data['mobile'])) ? $login_user_data['mobile'] : "";
$living_country_id = (isset($_POST['living_country_id'])) ? $_POST['living_country_id'] : "";
$gender = (isset($_SESSION['travel.ezding.user.data']['gender']) && $_SESSION['travel.ezding.user.data']['gender'] != "") ? $_SESSION['travel.ezding.user.data']['gender'] : "";
$taiwan_content_suggestion = Dao_loader::__get_taiwan_content_suggestion_dao();
if (isset($_SESSION['travel.ezding.captcha.user']) && isset($_POST['captchaCode']) && $_SESSION['travel.ezding.captcha.user'] == $_POST['captchaCode']) {
	$tcs_status = 0;
	$tcs_ref_category = "taiwan.content";
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
<!DOCTYPE html>
<html lang="zh-Hant" prefix="og: http://ogp.me/ns#">
<head>
	<?php include __DIR__ . "/../common/head_new.php"; ?>
	<script src="/web/js/lib/jquery/jquery.js"></script>
	<script src="/web/js/main-min.js"></script>
    <link rel="stylesheet" href="/web/css/main.css">
    <link rel="stylesheet" href="/web/css/main2.css">
    <link rel="stylesheet" href="/web/css/swiper.min.css">
</head>
<body>
	<header><?php include __DIR__ . "/../common/header_new.php"; ?></header>
	<main class="otr-improve-container">
		<section>
			<form id="mainpage" action="" method="post">
			<div class="blank">
				<i class="fa fa-envelope-o"></i>
				<input type="text" id="email" name="email" placeholder="信箱" />
				<div class="errMsg" id="email_err">E-mail 不可空白</div>
				<div class="errMsg" id="email_valid_err">E-mail 格式驗證錯誤</div>
			</div>
			<div class="blank">
				<i class="fa fa-user"></i>
				<input type="text" id="name" name="name" placeholder="姓名" class="name" />
				<div class="errMsg" id="name_err">姓名不可空白</div>
			</div>
			<div class="phone">
				<div class="blank">
					<i class="fa fa-mobile"></i>
					<select id="living_country_id" name="living_country_id">
					<?php
						foreach(constants_user_center::$LIVING_COUNTRY_TEXT as $key => $value) {
						    echo '<option value="', $key, '"';
						    if ($key == $living_country_id) echo ' selected';
						    echo '>', $value, '</option>';
						}
					?>
					</select>
					<i class="fa fa-angle-down"></i>
				</div>
				<div class="blank">
					<input type="text" id="phone" name="phone" maxlength="15" />
					<div class="errMsg" id="phone_err">手機號碼不可空白</div>
				</div>
			</div>
			<div class="wrap">
				<textarea id="textarea" name="textarea" placeholder="評論..." rows="8" class="text-area"></textarea>
				<div class="errMsg" id="textarea_err">您的問題不可空白</div>
			</div>
			<div class="certi">
				<div class="blank certiImg">
<!-- 					<img src="http://placehold.it/960x600"> -->
					<img class="service-pic" id="capImg" src="/web/ajax/authimg.php?authType=user" />
					<i class="fa fa-refresh" onclick="refreshCaptcha();"></i>
				</div>
				<div class="blank">
					<input type="text" name="captchaCode" id="captchaCode" maxlength="5" placeholder="輸入驗證碼" />
					<div class="errMsg" id="captchaCode_err">請輸入問題驗證碼</div>
				</div>
			</div>
			<button type="button" class="btn" id="submit-form" onclick="chkData();">
				送出
			</button>
			</form>
		</section>
	</main>
	<footer></footer>
	<script>
	$(function() {

        $('#prexPage3').show();
        $('#openMenu').hide();
        $('#openMenu2').hide();
		$('#mobile_search').hide();
        $('#menucopy').hide();
        $('#chgngeMap').hide();
        $('.img-logo-m').css("display", "none");
        // 字最多只能放置5個字
        $('.headBarWrap .aLogo').html("改善此清單");
        $('.headBarWrap .aLogo').removeAttr("href");
        pageInit();
	});

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

	function chkData(){
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

	function cbCheckCaptcha(jsonData){
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
	</script>
</body>
</html>