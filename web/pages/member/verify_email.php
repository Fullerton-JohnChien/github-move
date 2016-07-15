<?php
require_once __DIR__ . '/../../config.php';

$token_str_encoded = base64_decode(get_val('data'));
$uid = substr($token_str_encoded, 0, 32);
$token = substr($token_str_encoded, -32);

$tripitta_service = new tripitta_service();
$tripitta_web_service = new tripitta_web_service();
$ezding_user_service = EzdingUserUtil::get_ezding_user_util();

$ret = $ezding_user_service->checkVerifyToken($uid, null, constants_user_center::VERIFY_TYPE_EMAIL, $token);
if($ret["status"] == 0) {
	// 驗證失敗
}else{
	// find user
	$ret2 = $ezding_user_service -> getUserById($uid, 0);
	// 更新email驗證碼
	if($ret2['msg']['emailVerifyStatus'] == 0 || $ret2['msg']['emailVerifyStatus'] == 2){
		$item = array();
		$item["userId"] = $uid;
		$item["emailVerifyStatus"] = 1;
		$ret = $tripitta_web_service->update_user_data($item);

		// 判斷是否有符合的滿千送百資格
		$marking_campaign_dao = Dao_loader::__get_marking_campaign_dao();
		$marking_campaign_list = $marking_campaign_dao -> find_by_type(1);

		$reg_date = date("Y-m-d");
		foreach ($marking_campaign_list as $mc) {
			if($mc["mc_begin_date"] <= $reg_date && $mc["mc_end_date"] >= $reg_date) {
				// 檢查是否已歸戶(不應發生)
				$must_possessed_row = $tripitta_service -> find_must_possessed_by_user_and_marking_campain_id($ret2["msg"]["serialId"], $mc["mc_id"]);

				// 歸戶
				if(empty($must_possessed_row)) {
					$item = array();
					$item["mcmp_id"] = 0;
					$item["mcmp_user_id"] = $ret2["msg"]["serialId"];
					$item["mcmp_marking_campaign_id"] = $mc["mc_id"];
					$item["mcmp_use_times"] = $mc["mc_use_times"];
					$item["mcmp_status"] = 0;
					$tripitta_service -> save_or_update_marking_campaign_must_possessed($item);
				}
			}
		}
	}else{
		alertmsg('此帳號已認證過!', '/member/');
	}
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
	//var popup_register_password_verify = false;
	$(function(){
		$('#auth_fail_errMsg').hide();

		$('.overlay').show();
		<?php if($ret["status"] == 0){ ?>
			$('.popupAuthFail').show();
		<?php }else if($ret["status"] == 1){?>
			$('.popupAuthSucc').show();
		<?php } ?>

		// 會員驗證完成 -> 回頁面
		$('#popup_auth_succ_back_home_page').click(function() {
			location.href = "/member/";
		});

		// 會員驗證失敗 -> 回頁面
		$('#popup_auth_fail_back_home_page').click(function() {
			location.href = "/member/";
		});

		// 重寄認證信
		$('#resend_email').click(function() {
			if($('#auth_email').val() == '') {
				$('#auth_fail_errMsg').show();
				alert('請輸入EMAIL!');
				return;
			}
			resend_verify_email();
		});
	});

	function resend_verify_email(){
		var p = {};
	    p.func = 'resend_verify_email';
	    p.token = token;
	    p.email = $('#auth_email').val();
	    console.log(p);
		$.post("/web/ajax/ajax.php", p, function(data) {
			console.log(data);
	        if(data.code == '9999'){
	            alert(data.msg);
	        } else {
	        	alert('重寄認證信成功');
	        	location.href = "/member/";
	        }
	    }, 'json').done(function() { }).fail(function() { }).always(function() { });
	}
	</script>
</head>
<body>
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="resetPwd-container">

	</div>
	<footer class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
</body>
</html>