<?
require_once __DIR__ . '/../../config.php';

// $ezding_user_service = EzdingUserUtil::get_ezding_user_util();
// $user_data = $ezding_user_service->checkLogin(false);;
// $user_serial_id = $user_data["serialId"];
// $nickname = $user_data["nickname"];
writeLog(time());
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<? include __DIR__ . "/../common/head_new.php"; ?>
	<title>tripitta 旅必達 會員中心</title>
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<link rel="stylesheet" href="/web/css/member.css">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
</head>
<body>
<?
$tripitta_web_service = new tripitta_web_service();
$login_user_data = $tripitta_web_service->check_login();
$coupon_list = array();
if(!empty($login_user_data)) {
    $user_serial_id = $login_user_data["serialId"];
    $coupon_dao = Dao_loader::__get_coupon_dao();
    $coupon_list = $coupon_dao->getCouponByUser($user_serial_id, 3);
}
?>

	<header><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="memIndex-container">
		<div class="title">會員中心</div>
		<div class="tile">
			<div class="preference">
				<section>
					<dl>
						<dt>我的帳號</dt>
						<dd><a href="/member/profile/"><i class="fa fa-angle-right"></i>個人資料</a></dd>
						<dd><a href="/member/update_password/"><i class="fa fa-angle-right"></i>修改密碼</a></dd>
						<dd><a href="/member/coupon/"><i class="fa fa-angle-right"></i>優惠券查詢</a></dd>
					</dl>
				</section>
				<section>
					<dl>
						<dt>訊息中心</dt>
						<dd><a href="/member/"><i class="fa fa-angle-right"></i>會員中心</a></dd>
						<dd><a href="/service/"><i class="fa fa-angle-right"></i>客服中心</a></dd>
						<dd><a href="/member/invoice/"><i class="fa fa-angle-right"></i>我的訂單˙</a></dd>
					</dl>
				</section>
				<section>
					<dl>
						<dt>我的收藏</dt>
						<dd>
							<a href="/member/collection/food/"><i class="fa fa-angle-right"></i>美食</a>
							<a href="/member/collection/scenic/"><i class="fa fa-angle-right"></i>景點</a>
						</dd>
						<dd>
							<a href="/member/collection/homestay/"><i class="fa fa-angle-right"></i>住宿</a>
							<a href="/member/collection/gift/"><i class="fa fa-angle-right"></i>伴手禮</a>
						</dd>
						<dd>
							<a href="/member/collection/event/"><i class="fa fa-angle-right"></i>活動</a>
							<a href="/member/collection/travel_plan/"><i class="fa fa-angle-right"></i>行程遊記</a>
						</dd>
					</dl>
				</section>
				<section>
					<dl>
						<dt>我的點評</dt>
					</dl>
				</section>
				<section>
					<dl>
						<dt>我的行程</dt>
					</dl>
				</section>
			</div>
<?
/*
?>
			<div class="activity">
				<article class="coupon">
					<h1>
						<span>優惠劵查詢</span>
						<a href="/member/coupon/" class="more img-member-more"></a>
					</h1>
<?
foreach($coupon_list as $idx => $coupon_row) {
    if($idx >= 3) {
        break;
    }
    $batch_name = $coupon_row["cb_batch_name"];
    $launched_time = $coupon_row["c_launched_time"];
    $expited_time = $coupon_row["c_expired_time"];
    $terms_of_use = $coupon_row["cb_terms_of_use"];
?>
					<a href="/member/coupon/">
						<section>
							<div class="date">
								<span class="from"><?= substr(date('F', strtotime($launched_time)), 0, 3) . date(' d Y', strtotime($launched_time)) ?></span>
								<i class="fa fa-angle-down"></i>
								<span class="to"><?= substr(date('F', strtotime($expited_time)), 0, 3) . date(' d Y', strtotime($expited_time)) ?></span>
							</div>
							<div class="info">
								<span class="titleInfo"><?= $batch_name ?></span>
								<span class="content"><?= nl2br($terms_of_use) ?></span>
							</div>
						</section>
					</a>
<?
}
?>
				</article>
				<article class="marketing">
					<h1>
						<span>行銷活動</span>
						<!-- <a href="javascript:void(0)" class="more img-member-more"></a> -->
					</h1>
					<a href="javascript:void(0)">
						<section>
							<div class="date">
								<span class="from">2016-03-13</span>
								<i class="fa fa-angle-down"></i>
								<span class="to">2016-03-19</span>
							</div>
							<div class="info">
								<span class="titleInfo">夏日驚喜優惠券</span>
								<span class="content">夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏</span>
							</div>
						</section>
					</a>
					<a href="javascript:void(0)">
						<section>
							<div class="date">
								<span class="from">2016-03-13</span>
								<i class="fa fa-angle-down"></i>
								<span class="to">2016-03-19</span>
							</div>
							<div class="info">
								<span class="titleInfo">夏日驚喜優惠券</span>
								<span class="content">夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏</span>
							</div>
						</section>
					</a>
					<a href="javascript:void(0)">
						<section>
							<div class="date">
								<span class="from">2016-03-13</span>
								<i class="fa fa-angle-down"></i>
								<span class="to">2016-03-19</span>
							</div>
							<div class="info">
								<span class="titleInfo">夏日驚喜優惠券</span>
								<span class="content">夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏日驚喜優惠券夏</span>
							</div>
						</section>
					</a>
				</article>
			</div>
<?
*/
?>
		</div>
	</div>
	<footer><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
</body>
</html>