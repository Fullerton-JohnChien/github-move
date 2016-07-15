<?
require_once __DIR__ . '/../../config.php';
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
	var orig_password_verify = false;
	$(function(){
		$('#btn_update_password').click(function() {
			update_user_password();
		});
		$('#btn_reset').click(function() {
			$('#error_msg_orig_password').html('');
			$('#error_msg_password').html('');
			$('#error_msg_password_confirm').html('');

		});

		$('#orig_password').blur(function() {
			check_orig_password();
		});
		//$('#password').blur(function() { check_register_password('update_password'); });
		//$('#password_confirm').blur(function() { check_register_password('update_password'); });
	});
	function update_user_password() {
		check_orig_password();
		check_register_password('update_password');
		console.log('popup_register_password_verify:' + popup_register_password_verify);
		console.log('orig_password_verify:' + orig_password_verify);
		if(!popup_register_password_verify || !orig_password_verify){
			return;
		}

		var p = {};
	    p.func = 'update_user_password';
	    p.user_id = $('#user_id').val();
	    p.password = $('#password').val();
	    p.orig_password = $('#orig_password').val();
	    console.log(p);
		$.post("/web/ajax/ajax.php", p, function(data) {
			console.log(data);
	        if(data.code == '9999'){
	            alert(data.msg);
	        } else {
	        	alert('密碼已更新');
	            // 先暫時做頁面reload，視狀況自行調整
	            // location.href = '/web/';
	            location.reload();
	        }
	    }, 'json').done(function() { }).fail(function() { }).always(function() { });
	}
	function check_orig_password() {
		if($('#orig_password').val() == '') {
			$('#error_msg_orig_password').html('請輸入您的舊密碼');
			orig_password_verify = false;
		} else {
			$('#error_msg_orig_password').html('');
			orig_password_verify = true;
		}
	}
	</script>
</head>
<body>
	<header><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="updatePwd-container">
		<h1 class="title">更改密碼</h1>
		<form>
		<div class="tile">
			<? include __DIR__ . '/member_function_menu.php' ?>
			<article>
				<div class="wrapper">
					<div class="label">
						<div class="icon">
							<i class="img-member-lock"></i>
						</div>
						<div class="input">
							<input type="password" id="orig_password" autocomplete="off" placeholder="舊密碼" maxlength="20">
							<div class="errMsg" id="error_msg_orig_password"></div>
						</div>
					</div>
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
						<input type="reset" id="btn_reset" value="取消" class="reset">
						<input type="button" id="btn_update_password" value="確認修改" class="submit">
					</div>
				</div>
			</article>
		</div>
		</form>
	</div>
	<footer><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
	<input type="hidden" id="user_id" value="<?php echo $login_user_data['id']?>">
</body>
</html>