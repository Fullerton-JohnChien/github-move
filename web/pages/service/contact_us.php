<?
/**
 * 說明：聯絡我們
 * 作者：Steak
 * 日期：2015年11月3日
 * 備註：
 */
require_once __DIR__ . '/../../config.php';

// 檢查會員是否登入
$tripitta_web_service = new tripitta_web_service();

$display_currency_id = $tripitta_web_service->get_display_currency(0);

$login_user_data = $tripitta_web_service->check_login();

$email = (isset($login_user_data['email'])) ? $login_user_data['email'] : "";

$qt_id = get_num('qt_id');
$parent_id = get_num('qt_parent_id');
$act = get_val('act');
$mail_from = get_config_mail_from();
//$mail_from = 'service@mail.ezding.com.tw';

$qa_type_dao =  Dao_loader::__get_qa_type_dao();
$qaTypeList = $qa_type_dao ->findQaTypeListByCondition('tripitta', 'tw', 0);
if (empty($qt_id)) $qt_id = $qaTypeList[0]['qt_id'];
if (empty($parent_id)){
	$sub_List = $qa_type_dao ->findQaTypeListByCondition('tripitta', 'tw', $qt_id);
	$qt_id = $sub_List[0]['qt_id'];
	$parent_id = $sub_List[0]['qt_parent_id'];
}

if($act == 'edit'){
	// 防止被攻擊
	$authType = $_REQUEST['type'];
	$captchaCode = $_REQUEST['captchaCode'];

	$captchaType = null;
	if ('user' == $authType) $captchaType = CAPTCHA_USER;

	if ($captchaCode != $_SESSION[$captchaType] || empty($captchaCode) || empty($captchaType) || strlen($captchaCode) != 4)
	{
		$_SESSION[$captchaType] = null;
		alertmsg('驗證碼錯誤','/contact/29/22/');
		exit();
	}

	$name = $_REQUEST['name'];
	$gender = $_REQUEST['gender'];
	$email = $_REQUEST['email'];
	$phone = $_REQUEST['phone'];
	$QAAsk = $_REQUEST["QAAsk"];
	$C1ID = $_REQUEST["C1ID"];
	$FaID = $_REQUEST["FaID"];
	$EXT01 = $_REQUEST["EXT01"];
	$BckURL = "";


	$service_url = 'http://www.idealcard.com.tw/service/service_msg.php?';
	$params = "";
	$params .= "QAName=" . urlencode($name);
	$params .= "&QAEmail=" . urlencode($email);
	$params .= "&QAAsk=" . urlencode($QAAsk);
	$params .= "&C1ID=" . urlencode($C1ID);
	$params .= "&BckURL=" . urlencode($BckURL);
	$params .= "&EXT01=" . urlencode($EXT01);
	$params .= "&EXT02=" . urlencode($phone);
	$params .= "&FaID=" . urlencode($FaID);
	$params .= "&C2ID=0";

	$body = "QAName=" . $name;
	$body .= "<br>QAGender=" .$gender;
	$body .= "<br>QAEmail=" .$email;
	$body .= "<br>QAAsk=" .$QAAsk;
	$body .= "<br>C1ID=" .$C1ID;
	$body .= "<br>BckURL=" .$BckURL;
	$body .= "<br>EXT01=" . $EXT01;
	$body .= "<br>EXT02=" . $phone;
	$body .= "<br>FaID=" . $FaID;
	sendmail($mail_from, Constants::$TRAVEL_PM_EMAILS, '客服郵件', $body);
	sendmail($mail_from, array('steak.liu@fullerton.com.tw','john.chien@fullerton.com.tw','cheans.huang@fullerton.com.tw','amy.liu@fullerton.com.tw','alexlin@fullerton.com.tw','lily.lin@fullerton.com.tw'), '客服郵件', $body);
	open_url($service_url . $params);
	alertmsg('謝謝您的反應, 我們將盡快回覆您的問題 !', '/contact/?qt_id='.$qt_id.'&qt_parent_id='.$parent_id);
	exit();
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<? include __DIR__ . "/../common/head.php"; ?>
	<title>聯絡我們 - Tripitta 旅必達</title>
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<style type="text/css">
		a{text-decoration: none;}
		dd.active {
		    border-left: 1px solid #f3bd1c;
		    color: #f3bd1c;
		}
		.errMsg{display:none;}
	</style>
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type="text/javascript">
	function chgnge_sub(id, p_id) {
		 location.href = '/service/?qt_id='+id+'&qt_parent_id='+p_id;
	}
	function pageInit() {
		refreshCaptcha();
	}
	function refreshCaptcha() {
		var timestamp = Number(new Date());
		$('#capImg').attr('src', '/web/ajax/authimg.php?authType=user&act=refresh&' + timestamp);
	}
	function chkData()
	{
		$('.errMsg').hide();
		var msg = 0;
		if($("#captchaCode").val() == '') { $('#captchaCode_err').show(); $('#captchaCode').focus(); msg++}
		if($("#QAAsk").val() == '') { $('#QAAsk_err').show(); $('#QAAsk').focus(); msg++}
		if($("#phone").val() == '') { $('#phone_err').show(); $('#phone').focus(); msg++}
		if($("#email").val() == '') { $('#email_err').show(); $('#email').focus(); msg++}
		if($("#name").val() == '') { $('#name_err').show(); $('#name').focus(); msg++}

		if(msg != 0){
			return false;
		}

		// 驗證認證碼是否正確
		var data = {'captchaCode': $('#captchaCode').val(), 'type': 'user'};
		$.getJSON('/web/ajax/ajax.php',
			{func: 'checkCaptchaCode', data: data},
			function(jsonData) {cbCheckCaptcha(jsonData);}
	    );

		var p = {};
	    p.func = 'checkCaptchaCode';
	    p.captchaCode = $('#captchaCode').val();
	    p.type = user;
	    console.log(p);
	    $.post("/web/ajax/ajax.php", p, function(data) {
	        if(data.code == '0000'){
	        	cbCheckCaptcha(data);
	        }
	    }, 'json');
	}

	function cbCheckCaptcha(jsonData)
	{
		if (jsonData) {
			if (9999 == jsonData['code']) {
				$('#captchaCode').focus();
				alert('認證碼輸入錯誤!!');
				return;
			}
			else if (0000 == jsonData['code']) {
				//alert('認證碼輸入正確!!');
				$('#mainpage').submit();
			}
		}
	}
	</script>
</head>
<body onload="pageInit()">
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="contact-container">
		<form id="mainpage" method="post" action="/contact/?qt_id=<?= $qt_id ?>&qt_parent_id=<?= $parent_id ?>">
			<input name="C1ID" value="65" type="hidden" /><!-- 必要 -->
			<input name="act" value="edit" type="hidden" /><!-- 必要 -->
			<input name="FaID" value="tripitta" type="hidden" /><!-- 必要 -->
			<input name="EXT01" value="" type="hidden" /><!-- 會員帳號 -->
			<input name="type" value="user" type="hidden" />
			<input name="qt_id" value="<?= $qt_id ?>" type="hidden" />
			<input name="qt_parent_id" value="<?= $parent_id ?>" type="hidden" />
			<h1 class="title">聯絡我們</h1>
			<div class="tile">
				<aside>
					<?php foreach ($qaTypeList as $cl){
							$subQaTypeList = $qa_type_dao ->findQaTypeListByCondition('tripitta', 'tw', $cl['qt_id']);
							if (!empty($subQaTypeList)){
					?>
							<dl>
								<dt <?php echo ($qt_id == $cl['qt_id'] || $parent_id == $cl['qt_id']) ? 'class="active"' : ''?> ><a href="/service/<?= $cl['qt_id']?>/<?= $cl['qt_parent_id']?>/"><?= $cl['qt_name'] ?></a></dt>
								<?php foreach($subQaTypeList as $s){
								?>
								<dd <?php echo ($qt_id == $s['qt_id']) ? 'class="active"' : ''?> <?= ($qt_id != $cl['qt_id'] ||  ($parent_id != 0) ? $parent_id != $cl['qt_id'] : $qt_id != $cl['qt_id'] )? 'style="display:none;"':'' ?> onclick="chgnge_sub(<?= $s['qt_id'] ?>, <?= $s['qt_parent_id'] ?>)"><?= $s['qt_name'] ?></dd>
								<?php } ?>
							</dl>
							<?php } ?>
					<?php } ?>
				</aside>
				<article>
					<div class="wrapper">
						<div class="label">
							<div class="icon">
								<i class="img-member-user"></i>
							</div>
							<div class="input">
								<div class="inputSub">
									<input id="name" name="name" type="text" autocomplete="off" placeholder="請輸入姓名" maxlength="20">
									<div class="errMsg" id="name_err">姓名不可空白</div>
								</div>
								<div class="genderSelect">
									<i class="fa fa-venus"></i>
									<select id="gender" name="gender" class="gender">
										<option value="0">女士</option>
										<option value="1" selected>男士</option>
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
								<input id="email" name="email" value="<?php echo $email; ?>" type="text" autocomplete="off" placeholder="請輸入E-mail" maxlength="50">
								<div class="errMsg" id="email_err">E-mail不可空白</div>
							</div>
						</div>
						<div class="label">
							<div class="icon">
								<i class="img-member-phone"></i>
							</div>
							<div class="input">
								<div class="phoneSelect">
									<select class="phone" id="phone_c" name="phone_c">
										<option value="886" selected>台灣</option>
										<option value="86">中國大陸</option>
										<option value="852">香港</option>
									</select>
									<i class="fa fa-angle-down"></i>
								</div>
								<div class="inputSub">
									<input id="phone" name="phone" type="text" autocomplete="off" placeholder="請輸入電話" maxlength="20">
									<div class="errMsg" id="phone_err">電話不可空白</div>
								</div>
							</div>
						</div>
						<div class="label" style="display:none;">
							<div class="icon">
								<i class="img-member-classification"></i>
							</div>
							<div class="classiSelect">
								<select class="classi" id="classi" name="classi">
									<option value="0">選擇問題分類</option>
								</select>
								<i class="fa fa-angle-down"></i>
								<div class="errMsg" id="classi_err">尚未選擇您的問題</div>
							</div>
						</div>
						<div class="label">
							<textarea id="QAAsk" name="QAAsk" placeholder="請詳述您的問題"></textarea>
							<div class="errMsg" id="QAAsk_err">請輸入問題</div>
						</div>
						<div class="captcha">
							<img class="service-pic" id="capImg" src="/web/ajax/authimg.php?authType=user" />
							<input type="text" id="captchaCode" name="captchaCode" class="capInput" placeholder="請輸入驗證碼" maxlength="10">
							<div class="errMsg" id="captchaCode_err">請輸入問題驗證碼</div>
						</div>
						<div class="renewBtn" onclick="refreshCaptcha();"><i class="fa fa-repeat"></i>換一張圖片試試</div>
						<div class="btnWrap">
							<input type="button" value="聯絡我們" class="submit" onclick="chkData();">
						</div>
					</div>
				</article>
			</div>
		</form>
	</div>
	<footer class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
</body>
</html>