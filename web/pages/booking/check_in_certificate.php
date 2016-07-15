<?
/**
 * 說明：入住憑證
 * 作者：
 * 日期：2016年4月13日
 * 備註：
 *
 * 2016-04-13 Lewis
 * 修改列印時不出現天地
 */
require_once __DIR__ . '/../../config.php';
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<? include __DIR__ . "/../common/head_new.php"; ?>
	<title>tripitta 旅必達 會員中心 - 入住憑証</title>
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<style type="text/css">
    @media print
    {
    	#non-printable1 { display: none; }
    	#non-printable2 { display: none; }
    	#non-printable3 { display: none; }
    	#non-printable4 { margin-top: -70px; }
    }
    </style>
</head>
<body>
<?
$order_id = get_val('order_id');
if(empty($order_id)) {
    die('參數錯誤');
}
$tripitta_web_service = new tripitta_web_service();
$tripitta_homestay_service = new tripitta_homestay_service();
$homestay_service = new Home_stay_service();
$homestay_promotion_service = new Home_stay_promotion_service();
$api_ret = $tripitta_web_service->get_odc_order($order_id, null);
if($api_ret["code"] != "0000") {
    die($api_ret["msg"]);
}
$get_order_ret = $api_ret["data"];
if($get_order_ret["code"] != "0000") {
    die($get_order_ret["msg"]);
}
$order_row = $get_order_ret["data"];
// printmsg($order_row);

$lang = $order_row["ode_language"];
$order_id = $order_row["od_id"];
$store_id = $order_row["od_store_id"];
$order_homesty_row = $order_row["order.homestay"][0];
$order_line_homestay_list = $order_homesty_row["order.lines"];
$days = (strtotime($order_homesty_row["oh_check_out_date"]) - strtotime($order_homesty_row["oh_check_in_date"])) / 86400;

// 取得旅宿、房型資料
$homestay_row = $homestay_service->get_hotel_by_id($lang, $store_id);
$image_server_url = get_config_image_server();
$homestay_image_url = '/web/img/no-pic.jpg';
if(!empty($homestay_row["hs_main_photo"])) {
    $homestay_image_url = $image_server_url . "/photos/travel/home_stay/" . $store_id . '/' . $homestay_row["hs_main_photo"] . '_middle.jpg';
}

// $homestay_concat_dao = Dao_loader::__get_home_stay_contact_dao();
// $homestay_contact_row = $homestay_concat_dao->getHfHomeStayContactByHomeStayIdAndIndex($store_id, 1);
// printmsg($homestay_contact_row);

$home_stay_rule_row = $tripitta_homestay_service -> get_home_stay_rule($store_id);
?>
	<header id="non-printable1" class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="payPrint-container">
		<h1 class="title" id="non-printable3">列印入住憑證</h1>
		<div class="tile" id="non-printable4">
			<main>
				<div class="printLogo" title="點我列印" onclick="window.print()">
					<i class="img-print"></i>
					<p>Print</p>
				</div>

				<div class="checkInWrap">
					<div class="titleGroup">
						<h1>入住憑證</h1>
						<i class="img-logo-straight"></i>
					</div>

					<div class="contactWrap">
						<img src="<?= $homestay_image_url ?>" alt="">
						<div class="contactGroup">
							<h1 class="storeName"><?= $order_homesty_row["oh_store_name"] ?></h1>
							<h2 class="address"><?= $homestay_row["hs_address"] ?></h2>
							<hgroup class="contactWall">
								<h3 class="emailGroup">
									<span>E-mail</span>
									<span class="email"><?= $homestay_row["hs_email"] ?></span>
								</h3>
								<h3>
									<span>市話</span>
									<span class="landline"><?= $homestay_row["hs_phone"] ?></span>
								</h3>
								<h3>
									<span>QQ</span>
									<span class="qq"><?= $homestay_row["hs_qq"] ?></span>
								</h3>
								<h3>
									<span>手機</span>
									<span class="mobile"><?= $homestay_row["hs_mobile"] ?></span>
								</h3>
								<h3>
									<span>Wechat</span>
									<span class="wechat"><?= $homestay_row["hs_wechat"] ?></span>
								</h3>
								<h3>
									<span>Line</span>
									<span class="line"><?= $homestay_row["hs_line"] ?></span>
								</h3>
							</hgroup>
						</div>
					</div>

					<div class="roomDetail">
						<aside class="right">
							<h3>
								<span>訂單編號</span>
								<strong class="orderNo"><?= $order_row["od_transaction_id"] ?></strong>
							</h3>
							<h3>
								<span>訂購人</span>
								<span class="orderMan"><?= $order_row["om_buyer_name"] ?></span>
								<span class="gender">女士/先生</span>
							</h3>
							<h3>
								<span>入住房客</span>
								<span class="tenant"><?= $order_homesty_row["oh_user_name"] ?></span>
								<span class="gender">女士/先生</span>
							</h3>
							<h3>
								<span>入住日期</span>
								<span class="checkInDate"><?= date('Y.m.d', strtotime($order_homesty_row["oh_check_in_date"])) ?></span>
							</h3>
							<h3>
								<span>退房日期</span>
								<span class="checkOutDate"><?= date('Y.m.d', strtotime($order_homesty_row["oh_check_out_date"])) ?></span>
								<p>
									<i class="days"><?= $days ?></i>晚住宿
								</p>
							</h3>
						</aside>
						<aside class="left">
<?
$room_type_ids = [];
$room_type_promotions = [];
foreach($order_line_homestay_list as $order_line_homestay_row) {
    $room_type_id = $order_line_homestay_row["olh_product_id"];
    $promotion_type = $order_line_homestay_row["olh_promotion_type"];
    $promotion_config_id = $order_line_homestay_row["olh_promotion_config_id"];
    if(!in_array($room_type_id, $room_type_ids)) {
        $room_type_ids[] = $room_type_id;
    }
    $t_key = $room_type_id . '_' . $promotion_type . '_' . $promotion_config_id;
    if(!in_array($t_key, $room_type_promotions)) {
        $room_type_promotions[] = $t_key;
    }
}

foreach($room_type_promotions as $room_type_promotion) {
    $t = preg_split('/_/', $room_type_promotion);
    $room_type_id = $t[0];
    $promotion_type = $t[1];
    $promotion_config_id = $t[2];
    $room_type_name = "";
    $have_breakfast = 0;
    foreach($order_line_homestay_list as $order_line_homestay_row) {
        if($order_line_homestay_row["olh_product_id"] == $room_type_id && empty($room_type_name)) {
            $room_type_name = $order_line_homestay_row["olh_product_name"];
        }
        if($order_line_homestay_row["olh_breakfast"] == 1 && $have_breakfast == 0) {
            $have_breakfast = 1;
        }
    }
    $breakfast_info = ($have_breakfast == 1) ? "含早餐" : "不含早餐";
    $is_allow_cancel = 1;
    if($order_homesty_row["oh_allow_cancel"] == 2){
        $is_allow_cancel = 0;
    }

    $promotion_info = "";
    if($promotion_config_id > 0) {
        if($promotion_type == 1) {
            $promotion_row = $homestay_promotion_service->get_promotion_by_promotion_config_id($promotion_config_id);
            $promotion_info = $promotion_row["p_name"];
        }
    }

?>
							<section class="room">
								<h1><?= $room_type_name ?></h1>
								<h2>
									<p><?= $breakfast_info ?></p>
									<p>
										<? if($is_allow_cancel == 1){ ?>
										<span>免費取消期限</span>
										<span class="deadline"><?= date('Y/m/d', strtotime($order_homesty_row["oh_last_cancel_date"])) ?>前</span>
										<? } else { ?>
										<span>不可取消</span>
										<? } ?>
									</p>
								</h2>
								<div class="roomAmount">
<?
    foreach($order_line_homestay_list as $order_line_homestay_row) {
        if($room_type_id == $order_line_homestay_row["olh_product_id"]
                && $promotion_type == $order_line_homestay_row["olh_promotion_type"]
                && $promotion_config_id == $order_line_homestay_row["olh_promotion_config_id"]) {

            $room_date = date('Y.m.d', strtotime($order_line_homestay_row["olh_date"]));
            $unit_info = ($order_line_homestay_row["olh_product_unit"] == 1 ? "間" : "床");
?>
									<p>
										<span class="orderDate"><?= $room_date ?></span>
										<span class="hasRoom">1</span>
										<span><?= $unit_info ?></span>
									</p>
<?
        }
    }
?>
								</div>
							</section>
<?
}
?>
						</aside>
					</div>
				</div>

				<div class="notice">
					<section class="checkIn">
						<h1>入住退房須知：</h1>
						<div>
							<em>★</em>
							<span>入住時間（Check-in）：<?= $home_stay_rule_row['checkInTime'] ?>後，退房時間（Check-out）：<?= $home_stay_rule_row['checkOutTime']?>前</span>
						</div>
					</section>
					<section class="cancel">
						<h1>取消訂房須知：</h1>
						<div>
							<em>★</em>
							<span>
                                <p>客戶若欲取消訂房，相關依<span style="color:blue"> <?= $order_homesty_row["oh_store_name"] ?></span>取消規定。如下所示：</p>
                                <p>本民宿之取消訂房之相關扣款費用計算規範如下：</p>
                                <p><b>旅客於住宿日前 <span style="color:red"><?= $date1 = $home_stay_rule_row['lastCancelDays'] +1 ?></span> 日前不含入住日(<span style="color:blue"><?= date('Y/m/d', strtotime($order_homesty_row["oh_check_in_date"] . ' -' . $date1 . ' days')) ?></span>)取消訂房，全額退回。</b></p>
                                <p> 旅客於住宿日前<span style="color:red"> <?= $date2 = $home_stay_rule_row['lastCancelDays'] ?></span>日內(<span style="color:blue"><?= date('Y/m/d', strtotime($order_homesty_row["oh_check_in_date"] . ' -' . $date2 . ' days')) ?></span>)取消訂房，扣<span style="color:red">第一晚房價</span>，為取消訂房手續費。</p>
                                <p>旅客於住宿 <span style="color:red">當日</span>(<span style="color:blue"><?= date('Y/m/d', strtotime($order_homesty_row["oh_check_in_date"])) ?></span>) 將無法取消訂房，當日未入住恕無法退款。</p>
                                <p>本民宿於連續假期、特殊節慶，大型展會期或民宿所在區之旺季期間訂房，恕不接受取消訂房之要求，請務必特別注意，以確保您的權益。</p>
                                <p>如住宿之所在地 或 旅客出發地，於入住當日因天災(主要機關發佈如颱風地震產生相關重大影響等)或其他重大災害（人力不可抗力因素）影響，或有主要交通中斷情形發生，<span style="color:blue"> 致使無法如期入住時，敬請於發佈訊息後<span style="color:red">3</span>日內，儘速與本網客服人員連繫，更改或取消您的訂房</span>，本網將會依各民宿住房取消、更改規定辦理相關手續。</p>
							</span>
						</div>
					</section>
					<!--
					<section class="change">
						<h1>更改訂房須知：</h1>
						<div>
							<em>★</em>
							<span>入住時間: 下午13:00以後，若當日會延遲入住，務必請先致電通知民宿業主。</span>
						</div>
					</section>
					 -->
					<section class="others">
						<h1>其他特殊規定：</h1>
						<div>
							<em>★</em>
							<span>入住時間: <?= $home_stay_rule_row['checkInTime'] ?>以後，若當日會延遲入住，務必請先致電通知民宿業主。</span>
						</div>
					</section>
				</div>
			</main>
		</div>
	</div>
	<footer id="non-printable2" class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
</body>
</html>