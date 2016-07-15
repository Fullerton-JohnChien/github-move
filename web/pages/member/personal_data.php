<!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<meta charset="UTF-8">
<?php
require_once __DIR__ . '/../../config.php';
?>
	<? include __DIR__ . "/../common/head_new.php"; ?>
	<title>Tripitta 旅必達 會員</title>
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<link rel="stylesheet" href="/web/css/member.css">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type="text/javascript">
	$(function() {
		if($('.personalData-container .wrapper .submit').length > 0) {
			$('.personalData-container .wrapper .submit').click(function() { location.href = '/member/profile_edit/'; });
		}
	});
	</script>
</head>

<body>
<?
$tripitta_web_service = new tripitta_web_service();
$login_user_data = $tripitta_web_service->check_login();
$living_country_tel_code = '';
$living_country_name = '';
if(!empty($login_user_data)) {
//     printmsg($login_user_data);
    $living_country_id = $login_user_data["living_country_id"];
    $country_row = $tripitta_web_service->load_country($living_country_id);
    if(!empty($country_row)) {
        $living_country_tel_code = $country_row["c_tel_code"];
        $living_country_name = $country_row["c_name"];
    }
}
?>
	<header><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="personalData-container">
		<h1 class="title">個人資料</h1>
		<div class="tile">
			<? include __DIR__ . '/member_function_menu.php' ?>
			<article>
				<div class="wrapper">
					<figure>
						<div>
						<?php if (!empty($avatar)) {?>
						    <img src="<?php echo $img_server, $avatar, '?', time();?>" alt="">
						<?php }?>
						</div>
					</figure>
					<!--
					<div class="progress">
						<span>資料填寫進度</span>
						<span class="percent">20</span>
						<span>%</span>
					</div>
					-->
					<div class="progressBar">
						<span class="percentBar"></span>
					</div>
					<label class="s1">
						<div class="icon">
							<span class="img-member-mail"></span>
						</div>
						<div class="op-email"><?= $email ?></div>
						<? if($email_verify_status != 1){ ?>
						<div class="errAuthMsg">
							<i class="img-member-cross-small"></i><a>尚未認證</a>
						</div>
						<? } else { ?>
						<div class="succAuthMsg" style="display: block;">
							<i class="img-member-check-small"></i><span>啟用狀態</span>
						</div>
						<? } ?>
					</label>
					<label class="s2">
						<div class="icon">
							<span class="img-member-user"></span>
						</div>
						<div class="op-name"><?= $name ?></div>
						<div class="gender"><?php echo constants_user_center::$GENDER_TEXT[$gender] ?></div>
					</label>
					<label class="s3">
						<div class="icon">
							<span class="img-member-nickname"></span>
						</div>
						<div class="op-nickname"><?= $nickname ?></div>
					</label>
					<label class="s4">
						<div class="birthWrap">
							<div class="icon">
								<span class="img-member-birthday"></span>
							</div>
							<div class="op-birth"><?= empty($birthday) ? "未設定" : $birthday ?></div>
						</div>
						<div class="marryWrap">
							<div class="icon">
								<span class="img-member-marry"></span>
							</div>
							<div class="op-marry"><?php echo constants_user_center::$MARRIED_TEXT[$married]?></div>
						</div>
					</label>
					<label class="s5">
						<div class="eduWrap">
							<div class="icon">
								<span class="img-member-education"></span>
							</div>
							<div class="op-edu"><?php echo constants_user_center::$EDUCATION_TEXT[$education]?></div>
						</div>
						<div class="familyWrap">
							<div class="icon">
								<span class="img-member-family"></span>
							</div>
							<div>
								家庭成員<span class="op-family"><?php echo $family?></span>人
							</div>
						</div>
					</label>
					<label class="s6">
						<div class="icon">
							<span class="img-member-phone"></span>
						</div>
						<div>
							<? if(empty($mobile)) { ?>
							<span class="op-phone">尚未設定</span>
							<? } else { ?>
							<span class="op-countryCode"><?= $living_country_tel_code ?></span> -
							<span class="op-phone"><?= $mobile ?></span>
							<? } ?>
						</div>
					</label>
					<label class="s7">
						<div class="icon">
							<span class="img-member-location"></span>
						</div>
						<div>
							<span class="op-area"><?php echo (empty($living_country_name) ? '未設定' : $living_country_name)?></span>
							<span class="op-location"><?php echo $living_address?></span>
						</div>
					</label>
					<label class="s8">
						<div class="icon">
							<span class="img-member-newspaper"></span>
						</div>
						<span class="op-subscribe"><?php echo (1 == $epaper_homestay) ? '已' : '未';?></span><div>訂閱電子報</div>
					</label>

					<div class="btnWrap">
						<input type="button" value="編輯修改" class="submit">
					</div>
				</div>
			</article>
		</div>
	</div>
	<footer><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
</body>
</html>