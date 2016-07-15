<?
/**
 *  說明：
 *  作者：Cheans <cheans.huang@fullerton.com.tw>
 *  日期：2015年12月17日
 *  備註：
 *  2015-12-17 John 調整顯示金額，依header幣別顯示金額
 */
require_once __DIR__ . '/../../config.php';
error_reporting(E_ALL);
?><!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<? include __DIR__ . "/../common/head_new.php"; ?>
	<title>tripitta 旅必達 訂單完成</title>
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
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

$currency_id = $tripitta_web_service->get_display_currency();
$currency_code = NULL;
$exchange_rate = 1;

// 取得匯率
if (1 == $currency_id) {
    $currency_code = 'NTD';
    $exchange_rate = 1;
}
else {
    $exchange = $tripitta_homestay_service->get_exchange_by_currency_id($currency_id);
    $currency_code = $exchange['cr_code'];
    $exchange_rate = $exchange['erd_rate'];
}
// printmsg($currency_id);
// printmsg($currency_code);
// printmsg($exchange_rate);
$api_ret = $tripitta_web_service->get_odc_order($order_id, null);
// printmsg($api_ret);
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
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="paySucc-container">
		<h1 class="title">成功完成訂房</h1>
		<div class="tile">
			<aside>
				<div class="wrapper">
					<hgroup class="orderSuccNo">
						<h2>
							<span class="peopleName"><?= $order_row["om_buyer_name"] ?></span>
							<span class="gender">先生/小姐</span>
							您好：
						</h2>
						<h1>您的訂房已成功！</h1>
						<h3>
							訂單編號為
							<span class="orderNo"><?= $order_row["od_transaction_id"] ?></span>
						</h3>
						<h4>
							本頁為您完整的訂房資料，入住時，請列印本「入住憑証」，您也可以從「我的訂單」查詢您的訂房的相關紀錄。
						</h4>
						<h5>
							提醒您，本服務只接受Tripitta線上付款及退款，本公司絕對不會通知您改變付款方式及前往ATM做任何操作。
						</h5>
						<button class="print" id="btn_print_certificate">列印入住憑證</button>
					</hgroup>

					<div class="notice">
						<h1>入住退房須知：</h1>
						<div class="checkInContent">
							<p>
								<span>★</span>
								<span>入住時間：<?= $home_stay_rule_row['checkInTime'] ?> 以後，若當日會延遲入住，務必請先至電通知民宿業主。</span>
							</p>
							<p>
								<span>★</span>
								<span>入住人數，務必同訂房人數，若不符合規定時，業者有權現場要求補收相關差價，或不予超過之人員入住。敬請注意，此責任自負，因此問題造成之損失，本網站恕無法賠償或退還任何款項。</span>
							</p>
						</div>

						<h1>取消訂房須知：</h1>
						<div class="checkOutContent">
                            <p><span>客戶若欲取消訂房，相關依<span style="color:blue"> <?= $order_homesty_row["oh_store_name"] ?></span>取消規定。如下所示：</span></p>
                            <p><span>本民宿之取消訂房之相關扣款費用計算規範如下：</span></p>
                            <p><span><b>旅客於住宿日前 <span style="color:red"><?= $date1 = $home_stay_rule_row['lastCancelDays'] +1 ?></span> 日前不含入住日(<span style="color:blue"><?= date('Y/m/d', strtotime($order_homesty_row["oh_check_in_date"] . ' -' . $date1 . ' days')) ?></span>)取消訂房，全額退回。</b></span></p>
                            <p><span>旅客於住宿日前<span style="color:red"> <?= $date2 = $home_stay_rule_row['lastCancelDays'] ?></span>日內(<span style="color:blue"><?= date('Y/m/d', strtotime($order_homesty_row["oh_check_in_date"] . ' -' . $date2 . ' days')) ?></span>)取消訂房，扣<span style="color:red">第一晚房價</span>，為取消訂房手續費。</span></p>
                            <p><span>旅客於住宿 <span style="color:red">當日</span>(<span style="color:blue"><?= date('Y/m/d', strtotime($order_homesty_row["oh_check_in_date"])) ?></span>) 將無法取消訂房，當日未入住恕無法退款。</span></p>
                            <p><span>本民宿於連續假期、特殊節慶，大型展會期或民宿所在區之旺季期間訂房，恕不接受取消訂房之要求，請務必特別注意，以確保您的權益。</span></p>
                       	 	<p><span>如住宿之所在地 或 旅客出發地，於入住當日因天災(主要機關發佈如颱風地震產生相關重大影響等)或其他重大災害（人力不可抗力因素）影響，或有主要交通中斷情形發生，<span style="color:blue"> 致使無法如期入住時，敬請於發佈訊息後<span style="color:red">3</span>日內，儘速與本網客服人員連繫，更改或取消您的訂房</span>，本網將會依各民宿住房取消、更改規定辦理相關手續。</span></p>
						</div>
					</div>
				</div>
			</aside>
			<article>
				<img src="<?= $homestay_image_url ?>" class="storeImg">
				<h1 class="storeName"><?= $order_homesty_row["oh_store_name"] ?></h1>
				<div class="checkDate">
					<div class="checkIn">
						<h5>入住日期</h5>
						<p class="checkInDate"><?= date('Y.m.d', strtotime($order_homesty_row["oh_check_in_date"])) ?></p>
					</div>
					<div class="checkOut">
						<h5>退房日期</h5>
						<p class="checkOutDate"><?= date('Y.m.d', strtotime($order_homesty_row["oh_check_out_date"])) ?></p>
					</div>
					<div class="manyDays">
						<span class="days"><?= $days ?></span>
						<span>晚住宿</span>
					</div>
				</div>

				<!-- room list -->
				<div class="roomList">
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
    $cancel_info = "免費取消期限<span>" . $order_homesty_row["oh_last_cancel_date"] . "</span>	前";
    $is_allow_cancel = 1;
    if($order_homesty_row["oh_allow_cancel"] == 2){
        $cancel_info = "不可取消";
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
					<section>
						<h1><?= $room_type_name ?></h1>
						<h2><?= $promotion_info ?></h2>
						<div class="roomWrap">
							<p class="deadlineWrap">
								<span class="breakfast"><?= $breakfast_info ?></span>
								<span class="freeDeadline"><?= $cancel_info ?></span>
							</p>
							<div class="room">
<?
        foreach($order_line_homestay_list as $order_line_homestay_row) {
            if($room_type_id == $order_line_homestay_row["olh_product_id"]
                    && $promotion_type == $order_line_homestay_row["olh_promotion_type"]
                    && $promotion_config_id == $order_line_homestay_row["olh_promotion_config_id"]) {

                $room_date = date('Y.m.d', strtotime($order_line_homestay_row["olh_date"]));
                $sell_price = $order_line_homestay_row["olh_sell_price"];
                $unit_info = ($order_line_homestay_row["olh_product_unit"] == 1 ? "間" : "床");
?>
								<h3>
									<p>
										<span class="date"><?= $room_date ?></span>
									</p>
									<p>
										<span class="count">1</span><?= $unit_info ?>
									</p>
									<p>
										<?php echo $currency_code?><span class="cost"><?= number_format(floor($sell_price / $exchange_rate)) ?></span>
									</p>
								</h3>
<?
            }
        }
?>

							</div>
						</div>
					</section>
<?
}
?>
				</div>

				<!-- payment -->
				<div class="favorableWrap">
					<? if($order_row["od_coupon_discount"] > 0 ) { ?>
					<div class="detail">
						<section>
							<p>優惠折扣</p>
							<p><?php echo $currency_code?></p>
							<p>
								<span>-</span>
								<span class="discountVal"><?= number_format(floor($order_row["od_coupon_discount"] / $exchange_rate)) ?></span>
							</p>
						</section>
					</div>
					<? } ?>
					<div class="totalWrap">
						<section>
							<p>總價</p>
							<p><?php echo $currency_code?></p>
							<p class="total"><?= number_format(floor($order_row["od_bank_trans_money"] / $exchange_rate)) ?></p>
						</section>
					</div>
					<div class="exchangeWrap">
						<p>
							<span>NTD</span>
							<span class="exchange"><?= number_format($order_row["od_bank_trans_money"]) ?></span>
						</p>
						<h3>本金額僅供參考，實際消費金額以當日結帳匯率為主</h3>
					</div>
				</div>
			</article>
		</div>
	</div>
	<footer class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
	<input type="hidden" id="user_serial_id" value="<?php echo $login_user_data['serialId']?>">
</body>
</html>
<script>
var order_id = '<?= $order_id ?>';
$(function() {
	$('.paySucc-container #btn_print_certificate').click(function() {
		window.open('/web/pages/booking/check_in_certificate.php?order_id=' + order_id, '_blank');
	});
});
</script>