<?php
require_once __DIR__ . '/../../config.php';
include_once __DIR__ . '/../common/member_header.php';

// 預設進入頁面
$default_url = "/".$member_path."/profile/";
$url = get_val('url');
switch ($url){
	default:
	// 編輯會員
	case 'profile':
		$default_url = "/".$member_path."/profile/";
		break;
	// 我的點評
	case 'reviews':
		$default_url = "/".$member_path."/reviews_list/";
		break;
	// 我的收藏
	case 'collection':
		$default_url = "/".$member_path."/collection/";
		break;
}
writeLog(time());
?>
<!DOCTYPE html>
<html lang="zh-Hant" prefix="og: http://ogp.me/ns#">
<head>
	<meta charset="UTF-8">
	<?php include __DIR__ . "/../common/head_new.php"; ?>
        <title>Tripitta 旅必達 會員中心</title>
        <link rel="stylesheet" href="/web/css/main.css?01121536">
        <link rel="stylesheet" href="/web/css/member.css">
        <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
		<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css">
		<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
		<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>

</head>
<body>
	<main class="mem-content-m-container">
		<a href="javascript:void(0)" class="header">
			<i class="fa fa-chevron-left" aria-hidden="true"></i>
			<div class="title"><?php if(!empty($login_user_data)){ echo $name; } ?></div>
		</a>
		<div id="dataBlock" class="dataBlock">
		</div>
	</main>
	<footer><? include __DIR__ . "/../common/footer_new.php"; ?></footer>
	<script type="text/javascript">
		var do_popup_login = <?php echo intval($do_check_login); ?>;
		var orig_password_verify = false;
		$(function(){
			// 修改密碼使用
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

    		// 返回會員中心
    		$('.mem-content-m-container .header').on("click", function(){
    			window.location.href = '/<?php echo $member_path; ?>/';
        	});
		});

        $( document ).ready(function(){
        	$("#dataBlock").load("<?php echo $default_url; ?>");
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
</body>
</html>