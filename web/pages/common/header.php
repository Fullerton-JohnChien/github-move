<?php
$request_uri = $_SERVER["REQUEST_URI"];
$do_check_login = false;
$popup_login = false;
if(strpos($request_uri, '/member/') !== false) {
    if(strpos($request_uri, '/member/reset_password/') === false) {
        $do_check_login = true;
    }
}

// 檢查會員是否登入
$tripitta_web_service = new tripitta_web_service();

$display_currency_id = $tripitta_web_service->get_display_currency(0);

$login_user_data = $tripitta_web_service->check_login();

$email_verify_status = 0;

$header_is_login = 0;
if(!empty($login_user_data)) {
    $header_is_login = 1;
    $avatar = $login_user_data["avatar"];
    $email = $login_user_data["email"];
    $email_verify_status = $login_user_data["emailVerifyStatus"];
    $name = $login_user_data["name"];
    $gender = $login_user_data["gender"];
    $nickname = $login_user_data["nickname"];
    $birthday = $login_user_data["birthday"];
    $married = $login_user_data["married"];
    $education = $login_user_data["education"];
    $family = $login_user_data["family"];
	// $living_tel_country_id = $login_user_data["living_tel_country_id"];
    $living_country_id = $login_user_data["living_country_id"];
    $mobile = $login_user_data["mobile"];
    $living_area_id = $login_user_data["living_area_id"];
    $living_address = $login_user_data["living_address"];

    // 目前暫時拿來替代訂閱電子報的值，未來會修改？
    $epaper_homestay = $login_user_data["epaperHomeStay"];
} else {
    if($do_check_login) {
        $popup_login = true;
    }
}
if (empty($gender)) $gender = '';
if (empty($married)) $married = '';
?>
<div id="head">
	<div class="head" id="heads">
		<div class="width">
			<div class="logo img-member-logo " style="cursor: pointer"></div>
			<div class="regiGroup">
				<img src="" alt="" class="flag">
				<div class="currencySelect">
					<select class="currency" id="header_currency" style="width: 85px;">
						<option value="0" <? if($display_currency_id == 0) { echo ' selected '; } ?>>幣別選擇</option>
						<option value="1" <? if($display_currency_id == 1) { echo ' selected '; } ?>>NTD</option>
						<option value="5" <? if($display_currency_id == 5) { echo ' selected '; } ?>>HKD</option>
						<option value="3" <? if($display_currency_id == 3) { echo ' selected '; } ?>>RMB</option>
					</select>
					<input type="hidden" id="currency_is_click" value="0" />
					<i class="fa fa-angle-down"></i>
				</div>
				<? if(!$tripitta_web_service->is_login()) { ?>
				<div class="loginWrap">
					<span class="login" id="header_btn_login">登入</span>
					<input type="hidden" id="login_is_click" value="0" />
					<span class="register" id="header_btn_register">註冊</span>
					<input type="hidden" id="register_is_click" value="0" />
				</div>
				<? } ?>
				<? if($tripitta_web_service->is_login()) { ?>
				<div class="portraitWrap" style="display: flex;display: -webkit-flex" id="header_btn_member_function">
					<? if(!empty($avatar)){ ?><img src="<?php echo $img_server, $avatar, '?', time();?>" alt=""><? }else { ?>
					<img src="https://placehold.it/30x30" class="img">
					<?php } ?>
					<span class="name"><?= $nickname ?></span>
					<i class="fa fa-angle-down" ></i>
					<ul class="memMenu">
						<li><a href="/member/" id="header_btn_member">會員中心</a></li>
						<li><a href="/service/" id="header_btn_service">客服中心</a></li>
						<?php /*?><li><a href="/member/invoice/" id="header_btn_my_order">我的訂單</a></li><?php */?>
						<li><a href="/member/?item=order" id="header_btn_my_order">我的訂單</a></li>
						<li><a href="/member/message/" id="header_btn_my_message">訊息通知</a></li>
						<li><a href="javascript:void(0)" id="header_btn_logout">會員登出</a></li>
					</ul>
					<input type="hidden" id="register_is_click" value="0" />
				</div>
				<? } ?>
			</div>
		</div>
	</div>
	<nav>
		<div class="width">
			<ul class="nav">
				<li><a href="/trip/" id="header_btn_trip">行程遊記</a></li>
				<li><a href="/location/" id="header_btn_location">觀光指南</a></li>
				<li><a href="/booking/" id="header_btn_bookin">旅宿預訂</a></li>
				<?php if(is_production()) { ?>
				<li><a href="/transport/" id="header_btn_bookin">高鐵票卷</a></li>
				<?php }else { ?>
				<li><a href="/transport/" id="header_btn_bookin">交通預訂</a></li>
				<?php } ?>
				<!--
				<li><a href="/bookingcar/" id="header_btn_bookin">交通預訂</a></li>
				-->
				<!-- <li><a href="/wifi/" id="header_btn_idealcard">4G網卡預訂</a></li> -->
			</ul>
			<div class="search">
				<i class="iconSearch img-member-search"></i>
				<!--
				<input type="text" class="searchField" maxlength="30" placeholder="請輸入關鍵字查詢">
				-->
				<gcse:search></gcse:search>
			</div>
		</div>
	</nav>
</div>
<?php include 'bread_crumbs.php'; ?>

<script src="/web/js/common.js"></script>
<?php include 'header_javascript.php'; ?>