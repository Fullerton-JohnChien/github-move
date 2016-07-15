<?
require_once __DIR__ . '/../../config.php';
$token_str_encoded = base64_decode(get_val('data'));
$uid = substr($token_str_encoded, 0, 32);
$token = substr($token_str_encoded, -32);
$tripitta_web_service = new tripitta_web_service();
$ret = $tripitta_web_service->verify_reset_password_code($uid, $token);
if($ret["status"] == 0) {
    die($ret["msg"]);
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<? include __DIR__ . "/../common/head_new.php"; ?>
	<title>Tripitta 旅必達 - 會員中心</title>
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<link rel="stylesheet" href="/web/css/member.css">
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
	<script type="text/javascript">
	var token = '<?= $token_str_encoded ?>';
	var popup_register_password_verify = false;
// 	$(function(){
// 		$('#btn_reset_password').click(function() {
// 			reset_user_password();
// 		});
// 		//$('#password').blur(function() { check_register_password('reset_password'); });
// 		//$('#password_confirm').blur(function() { check_register_password('reset_password'); });
// 	});

// 	function reset_user_password() {
// 		//check_register_password('reset_password');
// 		var error_msg_pwd_confirm = check_reset_password();
// 		if(error_msg_pwd_confirm != '') {
// 			alert(error_msg_pwd_confirm);
// 			return;
// 		}
	var popup_register_password_verify = false;
	$(function(){
		$('#btn_reset_password').click(function() {
			reset_user_password();
		});
// 		$('#password').blur(function() { check_register_password('reset_password'); });
// 		$('#password_confirm').blur(function() { check_register_password('reset_password'); });
	});

	function reset_user_password() {
		check_register_password('reset_password');
		console.log('popup_register_password_verify : ' + popup_register_password_verify);
		if(!popup_register_password_verify) {
			return;
		}

		var p = {};
	    p.func = 'reset_user_password';
	    p.token = token;
	    p.password = $('#password').val();
	    console.log(p);
		$.post("/web/ajax/ajax.php", p, function(data) {
			console.log(data);
	        if(data.code == '9999'){
	            alert(data.msg);
	        } else {
	        	alert('密碼已更新');
	            // 先暫時做頁面reload，視狀況自行調整
	            location.href = '/';
// 	            location.reload();
	        }
	    }, 'json').done(function() { }).fail(function() { }).always(function() { });
	}

	function check_reset_password() {
		var pwd = $('#password').val();
		var pwd_confirm = $('#password_confirm').val();
		var error_msg = '';
		//console.log(pwd, pwd_confirm);
		if(pwd != '') {
			error_msg = verify_password(pwd);
			if(pwd_confirm != '' && error_msg == '') {
				error_msg = verify_password(pwd_confirm);
				if(error_msg == '' && pwd != pwd_confirm) {
					error_msg = '密碼確認錯誤';
				}
			}
		} else {
			error_msg = '密碼錯誤';
		}
		if(pwd_confirm != '' && error_msg == '') {
			error_msg = verify_password(pwd_confirm);
			if(error_msg == '' && pwd != pwd_confirm) {
				error_msg = '密碼確認錯誤 - 密碼比對錯誤';
			}
		}else {
			if(error_msg == '') {
				error_msg = '密碼確認錯誤';
			}
		}
		return error_msg;
	}
	</script>
</head>
<body>
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="resetPwd-container">
		<form>
		<h1 class="title">重置密碼</h1>
		<main class="tile">
			<div class="wrapper">
				<div class="label">
					<div class="icon">
						<i class="img-member-lock"></i>
					</div>
					<div class="input">
						<input type="password" id="password" autocomplete="off" placeholder="新密碼" maxlength="20">
						<div class="errMsg" id="error_msg_password"></div>
					</div>
				</div>
				<div class="label">
					<div class="icon">
						<i class="img-member-lock"></i>
					</div>
					<div class="input">
						<input type="password" id="password_confirm" autocomplete="off" placeholder="確認密碼" maxlength="20">
						<div class="errMsg" id="error_msg_password_confirm"></div>
					</div>
				</div>
				<div class="btnWrap">
					<input type="reset" value="取消" class="reset">
					<input type="button" id="btn_reset_password" value="確認修改" class="submit">
				</div>
			</div>
		</main>
		</form>
	</div>
	<footer class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
</body>
</html>