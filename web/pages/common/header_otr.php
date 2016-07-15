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
$tripitta_service = new tripitta_service();

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
	$living_tel_country_id = $login_user_data["living_tel_country_id"];
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

$total_orders = 0;
if(!empty($login_user_data)) {
	$total_orders = $tripitta_service -> count_user_order($login_user_data["serialId"]);
}
if (!empty($_SESSION[OTR_URI])) $OTR_URI = $_SESSION[OTR_URI];
if (!empty($_SESSION[OTR_STORE_NAME])) $OTR_STORE_NAME = $_SESSION[OTR_STORE_NAME];

$total_msg = 0;
// @todo
?>
<div class="header-container">
	<div class="headerBar">
		<a href="/" class="aLogo">
			<i class="img-logo"></i>
		</a>
		<div class="toolbar">
			<div class="toolbarTop">
				<div class="filter">
					<div class="fType Selector">
						<select>
							<option>類別</option>
							<option>2</option>
						</select>
						<i class="fa fa-angle-down"></i>
					</div>
					<div class="fLocation Selector">
						<select>
							<option>地區</option>
							<option>2</option>
						</select>
						<i class="fa fa-angle-down"></i>
					</div>
					<div class="fKeyword Textor">
						<input type="text" name="" class="fKey" maxlength="30">
						<i class="fa fa-search"></i>
					</div>
				</div>
				<div class="change">
					<div class="cLanguage Selector">
						<select>
							<option disabled="">語言</option>
							<option selected>繁中</option>
							<!--
							<option>簡中</option>
							<option>英文</option>
							-->
						</select>
						<i class="fa fa-angle-down"></i>
					</div>
					<div class="cCurrency Selector">
						<select id="header_currency">
							<option value="0" <? if($display_currency_id == 0) { echo ' selected '; } ?> disabled="">幣別</option>
							<option value="1" <? if($display_currency_id == 1) { echo ' selected '; } ?>>NTD</option>
							<option value="5" <? if($display_currency_id == 5) { echo ' selected '; } ?>>HKD</option>
							<option value="3" <? if($display_currency_id == 3) { echo ' selected '; } ?>>RMB</option>
						</select>
						<i class="fa fa-angle-down"></i>
					</div>
				</div>
				<div class="msgWrap" style="display:none;">
					<div class="iconWrap">
						<a href="javascript:void(0)" class="img-icon-messages">
							<div class="msgNum">123</div>
						</a>
					</div>
					<div class="menu">
						<div class="lists">
							<a href="javascript:void(0)" class="list">
								<div class="img">
								</div>
								<div class="info">
									<h3>
										<div class="name">
											台灣寶島遊車隊台灣寶島遊車隊台灣寶島遊車隊台灣寶島遊車隊
										</div>
										<div class="tag">
											已預訂
										</div>
									</h3>
									<div class="date">
										2016/12/31  21:00
									</div>
									<div class="content">
										詢問內容詢問內容詢問內容詢問內容詢問內容詢問內容詢問內容詢詢問內內容詢詢問內內容詢詢詢詢問內容詢問內容詢問內容詢問內容詢問內容詢問內容詢問內容詢詢問內內容詢詢問內內容詢詢詢詢問內容詢問內容詢問內容詢問內容詢問內容詢問內容詢問內...問內容詢問內容詢問內容詢問內容詢問內容詢問內...問內容詢問內容詢問內容詢問內容詢問內容詢問內...
									</div>
								</div>
							</a>
							<a href="javascript:void(0)" class="list">
								<div class="img"></div>
								<div class="info">
									<h3>
										<div class="name">
											台灣寶島遊車隊
										</div>
										<div class="tag">
											已預訂
										</div>
									</h3>
									<div class="date">
										2016/12/31  21:00
									</div>
									<div class="content">
										詢問內容詢問內容詢問內容詢問內容詢問內容詢問內容詢問內容詢詢問內內容詢詢問內內容詢詢詢詢問內容詢問內容詢問內容詢問內容詢問內容詢問內容詢問內容詢詢問內內容詢詢問內內容詢詢詢詢問內容詢問內容詢問內容詢問內容詢問內容詢問內容詢問內...
									</div>
								</div>
							</a>
							<a href="javascript:void(0)" class="list">
								<div class="img"></div>
								<div class="info">
									<h3>
										<div class="name">
											台灣寶島遊車隊
										</div>
										<div class="tag">
											已預訂
										</div>
									</h3>
									<div class="date">
										2016/12/31  21:00
									</div>
									<div class="content">
										詢問內容詢問內容詢問內容詢問內容詢問內容詢問內容詢問內容詢詢問內內容詢詢問內內容詢詢詢詢問內容詢問內容詢問內容詢問內容詢問內容詢問內容詢問內容詢詢問內內容詢詢問內內容詢詢詢詢問內容詢問內容詢問內容詢問內容詢問內容詢問內容詢問內...
									</div>
								</div>
							</a>
						</div>
						<a href="javascript:void(0)" class="searchAll">
							查看全部
						</a>
					</div>
				</div>
				<div class="member">
					<? if(!$tripitta_web_service->is_login()) { ?>
					<div class="signWrap" >
						<a href="javascript:void(0)" id="header_btn_login">登入</a>
						<a href="javascript:void(0)" id="header_btn_register">註冊</a>
					</div>
					<? } ?>
					<? if($tripitta_web_service->is_login()) { ?>
					<div class="memInfo" style="display: flex;">
						<? if(!empty($avatar)){ ?><img src="<?php echo $img_server, $avatar, '?', time();?>" alt="" class="img"><? }else { ?>
						<img src="https://placehold.it/30x30" class="img">
						<?php } ?>
						<div class="memName"><?= $nickname ?></div>
						<ul class="menu">
							<li id="header_btn_member">
								<span>會員中心</span>
							</li>
							<li id="header_btn_my_order">
								<span>我的訂單</span>
								<span class="message"><?= $total_orders ?></span>
							</li>
							<li id="header_btn_my_message">
								<span>我的訊息</span>
								<span class="message"><?= $total_msg ?></span>
							</li>
							<li id="header_btn_logout">
								<span>登出</span>
							</li>
						</ul>
					</div>
					<?php } ?>
				</div>
			</div>
			<nav class="category">
				<a href="/booking/">
					住宿預訂
				</a>
				<a href="/transport/" class="selected">
					交通預訂
				</a>
				<!--
				<a href="javascript:void(0)">
					4G卡預訂
				</a>
				-->
				<a href="/trip/">
					行程遊記
				</a>
				<a href="/location/">
					觀光指南
				</a>
			</nav>
		</div>
	</div>
</div>

<!-- 滑鼠scroll後改為這個區塊顯示 -->
<div class="header-container-fixed">
	<div class="headerBarWrap">
		<div class="headerBar">
			<a href="/" class="aLogo" id="aLogo">
				<i class="img-logo-2"></i>
			</a>
			<input type="checkbox" name="" id="switcher" class="switcher">
			<div class="Wrap">
				<div class="frame">
					<div class="filter">
						<div class="fType Selector">
							<select>
								<option>類別</option>
								<option>2</option>
							</select>
							<i class="fa fa-angle-down"></i>
						</div>
						<div class="fLocation Selector">
							<select>
								<option>地區</option>
								<option>2</option>
							</select>
							<i class="fa fa-angle-down"></i>
						</div>
						<div class="fKeyword Textor">
							<input type="text" name="" class="fKey">
							<i class="fa fa-search"></i>
						</div>
					</div>
					<nav class="category">
						<a href="/booking/">
							住宿預訂
						</a>
						<a href="/transport/">
							交通預訂
						</a>
						<!--
						<a href="javascript:void(0)">
							4G卡預訂
						</a>
						-->
						<a href="/trip/">
							行程遊記
						</a>
						<a href="/location/">
							觀光指南
						</a>
					</nav>
				</div>
			</div>
			<label for="switcher" class="switchBtn">
				<div class="searchBtn">
					<i class="fa fa-search"></i>
				</div>
				<div class="btn">
					取消
				</div>
			</label>
			<? if($tripitta_web_service->is_login()) { ?>
			<div class="member">
				<? if(!empty($avatar)){ ?><img src="<?php echo $img_server, $avatar, '?', time();?>" alt="" class="img"><? }else { ?>
				<img src="https://placehold.it/30x30" class="img">
				<?php } ?>
				<ul class="menu">
					<li id="header_btn_member2">
						<span>會員中心</span>
					</li>
					<li id="header_btn_my_order2">
						<span>我的訂單</span>
						<span class="message">10</span>
					</li>
					<li id="header_btn_my_message2">
						<span>我的訊息</span>
						<span class="message">1</span>
					</li>
					<li id="header_btn_logout2">
						<span>登出</span>
					</li>
				</ul>
			</div>
			<?php } ?>
		</div>
	</div>
</div>

<!-- mobile -->
<div class="header-container-m">
	<div class="headBarWrap">
		<div class="left">
			<i class="fa fa-bars" id="openMenu2"></i>
			<i class="fa fa-angle-left" id="prexPage"></i>
			<i class="fa fa-angle-left" id="prexPage3"></i>
		</div>
		<a href="/" class="aLogo">
			<img src="/web/img/sec/common/logo.png" class="logo">
		</a>
		<div class="right">
			<i class="img-icon-score" style="display:none;"></i>
			<i class="img-icon-edit" id="mobile_edit" style="display:none;"></i>
			<i class="img-icon-share" style="display:none;"></i>
			<i class="img-icon-menucopy" id="menucopy" style="display: inline-block;"></i>
			<i class="fa fa-list-ul" id="chgngeList"></i> <!-- 列表 -->
			<i class="img-icon-menulocal" style="display: inline-block;" id="chgngeMap"></i> <!-- 地圖 -->
			<i class="fa fa-search" id="mobile_search" style="display:none;"></i>
			<a href="javascript:void(0)" class="img-icon-messages" style="displaynone;">
			<div class="msgNum">
				<?= $total_msg ?>
			</div>
			</a>
			<i class="img-icon-back-copy" style="display:none;"></i>
		</div>
	</div>
	<!-- popup for OTR -->
	<div class="popupList">
		<a href="javascript:void(0)" id="header_sort_far" data-sort="1">依距離由遠到近排序</a>
		<a href="javascript:void(0)" id="header_sort_near" data-sort="2" class="selected">依距離由近到遠排序</a>
	</div>
</div>

<!-- 左側滑出選單 -->
<div class="menuList-m">
	<div class="wrapForScroll">
		<div class="content">
		<? if(!$tripitta_web_service->is_login()) { ?>
			<div class="btnWrap">
				<button class="btn" id="header_btn_login2">登入</button>
				<button class="btn" id="header_btn_register2">註冊</button>
			</div>
		<?php } ?>
		<? if($tripitta_web_service->is_login()) { ?>
			<!-- 登入後的畫面 -->
			<div class="member" style="display: flex;">
				<? if(!empty($avatar)){ ?><img src="<?php echo $img_server, $avatar, '?', time();?>" alt="" class="img"><? }else { ?>
				<img src="https://placehold.it/30x30" class="img">
				<?php } ?>
				<div class="memName"><?= $nickname ?></div>
			</div>
        <?php } ?>
		</div>
		<div class="title">
			設定
		</div>
		<div class="content">
			<label>
				<span>選擇語言</span>
				<select>
					<option>繁中</option>
					<option>簡體</option>
					<option>英文</option>
				</select>
			</label>
			<label>
				<span>選擇幣別</span>
				<select id="header_currency2">
					<option value="0" <? if($display_currency_id == 0) { echo ' selected '; } ?> disabled="">幣別</option>
					<option value="1" <? if($display_currency_id == 1) { echo ' selected '; } ?>>NTD</option>
					<option value="5" <? if($display_currency_id == 5) { echo ' selected '; } ?>>HKD</option>
					<option value="3" <? if($display_currency_id == 3) { echo ' selected '; } ?>>RMB</option>
				</select>
			</label>
		</div>
		<div class="title">
			選單
		</div>
		<div class="content">
			<a href="/booking/">
				住宿預訂
			</a>
			<a href="/transport/">
				交通預訂
			</a>
			<!--
			<a href="javascript:void(0)">
				4G卡預定
			</a>

			<a href="javascript:void(0)">
				主題企劃
			</a>
			-->
			<a href="/trip/">
				行程遊記
			</a>
			<a href="/location/">
				觀光指南
			</a>
		</div>
		<? if($tripitta_web_service->is_login()) { ?>
		<div class="content lgout">
			<a href="javascript:void(0)" id="header_btn_logout3">
				會員登出
			</a>
		</div>
		<?php } ?>
	</div>
</div>

<!--  左側滑出選單 for OTR -->
<div class="menuListOTR-m">
	<div class="wrapForScroll">
		<div class="content">
			<? if(!$tripitta_web_service->is_login()) { ?>
			<div class="btnWrap">
				<button class="btn" id="header_btn_login3">登入</button>
				<button class="btn" id="header_btn_register3">註冊</button>
			</div>
			<?php } ?>
			<? if($tripitta_web_service->is_login()) { ?>
				<!-- 登入後的畫面 -->
				<a href='/member/'>
				<div class="member" style="display: flex;">
					<? if(!empty($avatar)){ ?><img src="<?php echo $img_server, $avatar, '?', time();?>" alt="" class="img"><? }else { ?>
					<img src="https://placehold.it/30x30" class="img">
					<?php } ?>
					<div class="memName"><?= $nickname ?></div>
				</div>
				</a>
	        <?php } ?>
		</div>
		<?php //if(!empty($hs_name)) { ?>
		<?php if(!empty($OTR_URI) && !empty($OTR_STORE_NAME)) { ?>
		<div class="title">
			正在到訪旅宿
		</div>
		<div class="content">
			<!--  <a href='/vendor/R<? //= $id ?>/'><? //= $hs_name ?></a>-->
			<a href='<?= $OTR_URI ?>'><?= $OTR_STORE_NAME ?></a>
		</div>
		<?php } ?>
		<div class="title">
			設定
		</div>
		<div class="content">
			<!--
			<label>
				<span>選擇語言</span>
				<select>
					<option>繁中</option>
					<option>簡體</option>
					<option>英文</option>
				</select>
			</label>
			-->
			<label>
				<span>選擇幣別</span>
				<select id="header_currency2">
					<option value="0" <? if($display_currency_id == 0) { echo ' selected '; } ?> disabled="">幣別</option>
					<option value="1" <? if($display_currency_id == 1) { echo ' selected '; } ?>>NTD</option>
					<option value="5" <? if($display_currency_id == 5) { echo ' selected '; } ?>>HKD</option>
					<option value="3" <? if($display_currency_id == 3) { echo ' selected '; } ?>>RMB</option>
				</select>
			</label>
		</div>
		<div class="title">
			交通預訂
		</div>
		<div class="content">

			<a class="gotoHsr">
				高鐵票券
			</a>

			<a href="/bookingcar/?begin_area=&end_area=&begin_date=<?= date("Y-m-d",strtotime("+1 day")); ?>&car_day=&car_adult=1&car_child=0">
				包車
			</a>
			<a href="/tourbus/?begin_area=&end_area=&begin_date=<?= date("Y-m-d",strtotime("+1 day")); ?>">
				觀光巴士
			</a>
			<!--
			<a href="/pickup/?pickup_type=2&begin_area=&end_area=&begin_date=<?= date("Y-m-d",strtotime("+1 day")); ?>&car_adult=&car_child=0">
				接機
			</a>
			-->
			<a href="/pickup/?pickup_type=4&begin_area=&end_area=&begin_date=<?= date("Y-m-d",strtotime("+1 day")); ?>&car_adult=&car_child=0">
				送機
			</a>
		</div>
		<div class="title">
			觀光指南
		</div>
		<div class="content">
			<a href="/location/?f=scenic">
				景點資訊
			</a>
			<a href="/location/?f=food">
				美食資訊
			</a>
		</div>
		<?php if((empty($OTR_URI) && empty($OTR_STORE_NAME)) || $tripitta_web_service->is_login()) { ?>
		<div class="title">
			其他
		</div>
		<?php } ?>
		<div class="content">
			<?php //if(empty($hs_name)) { ?>
			<?php if(empty($OTR_URI) && empty($OTR_STORE_NAME)) { ?>
			<a href="/traveling">
				旅行助手
			</a>
			<?php } ?>
			<? if($tripitta_web_service->is_login()) { ?>
			<a href="javascript:void(0)" id="header_btn_logout4">
				會員登出
			</a>
			<?php } ?>
		</div>
	</div>
</div>

<!-- 上側選單 -->
<div class="hSearch-m">
	<div class="hTop">
		<label>
			<input type="radio" name="cate" checked>
			<div class="hText"> 景點</div>
		</label>
		<label>
			<input type="radio" name="cate">
			<div class="hText"> 美食</div>
		</label>
		<!-- <label name="cate">活動</label> 暫拿掉-->
		<label>
			<input type="radio" name="cate">
			<div class="hText"> 伴手禮</div>
		</label>
		<a href="javascript:void(0)" class="hclose" id="close_search">
			<i class="fa fa-times"></i>
		</a>
	</div>
	<div class="hsContent">
		<div class="blankBlock">
			<i class="fa fa-search"></i>
			<input type="text" name="" maxlength="30" placeholder="輸入關鍵字">
		</div>
		<div class="blankBlock">
			<i class="fa fa-map-marker"></i>
			<select>
				<option>1</option>
				<option>2</option>
				<option disabled selected>選擇地區</option>
			</select>
			<i class="fa fa-angle-down"></i>
		</div>
		<button class="submit">搜尋</button>
	</div>
</div>
<?php // include 'bread_crumbs.php'; ?>

<script src="/web/js/common.js"></script>
<?php include 'header_javascript.php'; ?>