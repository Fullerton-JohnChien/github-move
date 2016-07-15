<?
/**
 *  說明：
 *  作者：Cheans <cheans.huang@fullerton.com.tw>
 *  日期：2015年12月17日
 *  備註：
 *  2015-12-17 John 將EZ訂字樣，修改為Tripitta
 *  2016-01-14 Bobby 加 E-Mail 圖片
 */
require_once __DIR__ . '/../../config.php';
error_reporting(E_ALL);

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

$lang = $order_row["ode_language"];
$order_id = $order_row["od_id"];
$store_id = $order_row["od_store_id"];
$order_homesty_row = $order_row["order.homestay"][0];
$order_line_homestay_list = $order_homesty_row["order.lines"];
$days = (strtotime($order_homesty_row["oh_check_out_date"]) - strtotime($order_homesty_row["oh_check_in_date"])) / 86400;

// 取得旅宿、房型資料
$homestay_row = $homestay_service->get_hotel_by_id($lang, $store_id);
$city_town_row = $tripitta_web_service->get_city_town_by_id($lang, $homestay_row["hs_city_town_id"]);

$ta_city_id = $tripitta_web_service->get_trip_advisor_mapping_city_id($city_town_row["ct_parent_id"]);

$home_stay_rule_row = $tripitta_homestay_service -> get_home_stay_rule($store_id);

$server_url = 'https://www.tripitta.com';
$logo = '/web/img/emailLogo.png';
$logo2 = '/web/img/tripadvisor.png';
$url_order_list = '/member/invoice/';
if(is_dev()) {
    $server_url= 'http://local.tw.tripitta.com';
} else if(is_alpha()) {
    $server_url = 'http://alpha.www.tripitta.com';
}


$image_server_url = get_config_image_server();
$homestay_image_url = $server_url . '/web/img/no-pic.jpg';
if(!empty($homestay_row["hs_main_photo"])) {
    $homestay_image_url = $image_server_url . "/photos/travel/home_stay/" . $store_id . '/' . $homestay_row["hs_main_photo"] . '_middle.jpg';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="zh-hant">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
</head>
<body>
	<div class="orderFinish-container">
		<div class="header">
			<h1>完成訂房確認通知</h1>
			<!-- <img src="https://www.tripitta.com/event/email/img/emailLogo.png" alt="" /> -->
			<img src="<?= $server_url . $logo ?>" alt="" />
		</div>
		<div class="wrapper">
			<div class="confirmInfo">
				<p>
					<span class="username"><?= $order_row["om_buyer_name"] ?></span>
					先生/小姐 您好：
				</p>
				<p>
					您的訂房已成功，訂單確認編號為
					<span class="confirmCode"><?= $order_row["od_transaction_id"] ?></span>
				</p>
				<p>以下為您完整的訂房資料，入住時，請列印本住宿憑證，</p>
				<p>
					您也可以從
					「<a href="<?= $server_url . $url_order_list ?>" class="recordChk">訂購紀錄</a>」
					查詢訂房相關紀錄。
				</p>
				<h3>
					提醒您，本服務只接受Tripitta之線上付款及退款，本公司絕對不會通知您改變付款方式及前往ATM做任何操作。
				</h3>
			</div>

			<!-- hotel info -->
			<div class="hotelInfo">
				<img src="<?= $homestay_image_url ?>" alt="" />
				<div class="detail">
					<h1><?= $order_homesty_row["oh_store_name"] ?></h1>
					<h2><?= empty($city_town_row["ctml_city_name"]) ? $city_town_row["ct_city_name"] : $city_town_row["ctml_city_name"] ?></h2>
					<h3 class="email"><?= $homestay_row["hs_email"] ?></h3>
					<div class="IMGroup">
						<div class="left">
							<p>
								<span class="grey">市話</span>
								<span class="landline"><?= $homestay_row["hs_phone"] ?></span>
							</p>
							<p>
								<span class="grey">手機</span>
								<span class="mobile"><?= $homestay_row["hs_mobile"] ?></span>
							</p>
						</div>
						<div class="right">
							<?php if (!empty($homestay_row["hs_line"])){ ?>
							<p>
								<span class="grey">Line</span>
								<span class="lineID"><?= $homestay_row["hs_line"] ?></span>
							</p>
							<?php } ?>
							<?php if (!empty($homestay_row["hs_wechat"])){ ?>
							<p>
								<span class="grey">WeChat</span>
								<span class="wechatID"><?= $homestay_row["hs_wechat"] ?></span>
							</p>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>

			<!-- deal info -->
			<div class="dealInfo">
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
    $cancel_info = "免費取消期限<span class=\"freeCancel\">" . $order_homesty_row["oh_last_cancel_date"] . "</span>	前";
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
				<div class="item">
					<h1 class="roomName"><?= $room_type_name ?></h1>
					<table>
						<tr>
							<td class="discoName"><?= $promotion_info ?></td>
							<td class="breakfast"><?= $breakfast_info ?></td>
							<td class="freeCancel"><?= $cancel_info ?></td>
						</tr>
						<tbody>
						<?
					        foreach($order_line_homestay_list as $order_line_homestay_row) {
					            if($room_type_id == $order_line_homestay_row["olh_product_id"]
				                    && $promotion_type == $order_line_homestay_row["olh_promotion_type"]
				                    && $promotion_config_id == $order_line_homestay_row["olh_promotion_config_id"]) {
					                $room_date = date('Y.m.d', strtotime($order_line_homestay_row["olh_date"]));
					                $sell_price = $order_line_homestay_row["olh_sell_price"];
					                $unit_info = ($order_line_homestay_row["olh_product_unit"] == 1 ? "間" : "床");
						?>
							<tr>
								<td class="t1"><?= $room_date ?></td>
								<td class="t2">
									<span class="roomCount">1</span>
									<span><?= $unit_info ?></span>
								</td>
								<td class="t3">
									<span>NTD</span>
									<span class="price"><?= number_format($sell_price) ?></span>
								</td>
							</tr>
							<?
						            }
						        }
							?>
						</tbody>
					</table>
				</div>
				<?
					}
				?>
				<!-- calculator -->
				<? if($order_row["od_coupon_discount"] > 0 ) { ?>
				<div class="discount">
					<span>優惠折扣</span>
					<span class="currency">NTD</span>
					<span class="price">-<?= number_format($order_row["od_coupon_discount"]) ?></span>
				</div>
				<? } ?>
				<div class="total">
					<span>總價</span>
					<span class="currency">NTD</span>
					<span class="price"><?= number_format($order_row["od_sell_price"]) ?></span>
				</div>
				<div class="reference">
					<p>
						<span>NTD</span>
						<span class="NTD"><?= number_format($order_row["od_bank_trans_money"]) ?></span>
					</p>
					<p class="fyi">本金額僅供參考，實際消費金額已當日結帳匯率為主</p>
				</div>
				<!-- special requirement -->
				<div class="special">
					<h1>特殊需求：</h1>
					<div class="requireInfo"><?= nl2br($order_homesty_row["oh_request_memo"]) ?></div>
				</div>
			</div>
			<!-- button -->
			<? if(!empty($ta_city_id)) { ?>
			<div class="tripadvisorLogo">
				<a href="http://www.tripadvisor.com/PrintGuide?partnerId=CB56EED944AF4459B7E92BBF9B292AC6&locationId=<?= $ta_city_id ?>&lang=zh_TW" class="goWeb">TripAdvisor城市指南</a>
				<img src="<?= $server_url . $logo2 ?>"/>
			</div>
			<? } ?>
			<!-- remind -->
			<div class="remindGroup">
				<div class="remindChkIn">
					<h1>入住退房須知：</h1>
					<p>★入住時間（Check-in）：<?= $home_stay_rule_row['checkInTime'] ?>後，退房時間（Check- out）：<?= $home_stay_rule_row['checkOutTime']?>前</p>
					<p>★最晚入住時間： 請務必於 <?= $home_stay_rule_row['checkInTime'] ?> 以前辦理入住手續，若因行程可能延誤，請務必先電話聯絡業者，並告知業者正確的入住時間，若未告知則會視同當日未入住，並不退還該日之住房費用。</p>
				</div>
				<div class="remindCancel">
					<h1>取消訂房須知：</h1>
                    <p>客戶若欲取消訂房，相關依<span style="color:blue"> <?= $order_homesty_row["oh_store_name"] ?></span>取消規定。如下所示：</p>
                    <p>本民宿之取消訂房之相關扣款費用計算規範如下：</p>
                    <p><b>旅客於住宿日前 <span style="color:red"><?= $date1 = $home_stay_rule_row['lastCancelDays'] +1 ?></span> 日前不含入住日(<span style="color:blue"><?= date('Y/m/d', strtotime($order_homesty_row["oh_check_in_date"] . ' -' . $date1 . ' days')) ?></span>)取消訂房，全額退回。</b></p>
                    <p> 旅客於住宿日前<span style="color:red"> <?= $date2 = $home_stay_rule_row['lastCancelDays'] ?></span>日內(<span style="color:blue"><?= date('Y/m/d', strtotime($order_homesty_row["oh_check_in_date"] . ' -' . $date2 . ' days')) ?></span>)取消訂房，扣<span style="color:red">第一晚房價</span>，為取消訂房手續費。</p>
                    <p>旅客於住宿 <span style="color:red">當日</span>(<span style="color:blue"><?= date('Y/m/d', strtotime($order_homesty_row["oh_check_in_date"])) ?></span>) 將無法取消訂房，當日未入住恕無法退款。</p>
                    <p>本民宿於連續假期、特殊節慶，大型展會期或民宿所在區之旺季期間訂房，恕不接受取消訂房之要求，請務必特別注意，以確保您的權益。</p>
                    <p>如住宿之所在地 或 旅客出發地，於入住當日因天災(主要機關發佈如颱風地震產生相關重大影響等)或其他重大災害（人力不可抗力因素）影響，或有主要交通中斷情形發生，<span style="color:blue"> 致使無法如期入住時，敬請於發佈訊息後<span style="color:red">3</span>日內，儘速與本網客服人員連繫，更改或取消您的訂房</span>，本網將會依各民宿住房取消、更改規定辦理相關手續。</p>
				</div>
			</div>
		</div>
	</div>
	<style>
	*{font-size:16px;font-family:arial, "Microsoft JhengHei";color:#404040}html,body{height:100%}body{margin:0;background:#ededed}a{outline:none}input,textarea{padding:8px;box-sizing:border-box;border:1px solid #e6e6e6;outline:none}textarea{resize:none}h1,h2,h3,h4,h5,h6,p{margin:0;padding:0;font-weight:lighter}.orderFinish-container{width:650px;margin:0 auto}.orderFinish-container .header{padding:15px 50px;background-color:#fee600}.orderFinish-container .header:after{content:"";display:block;clear:both}.orderFinish-container .header h1{margin:48px 0 0;font-size:28px;float:left}.orderFinish-container .header img{width:74px;height:82px;float:right}.orderFinish-container .wrapper{padding:68px 50px;background-color:white}.orderFinish-container .wrapper .confirmInfo{margin-bottom:50px}.orderFinish-container .wrapper .confirmInfo p{margin:5px 0;font-size:20px}.orderFinish-container .wrapper .confirmInfo p *{font-size:20px}.orderFinish-container .wrapper .confirmInfo p .confirmCode{color:#f05a30}.orderFinish-container .wrapper .confirmInfo p .recordChk{text-decoration:none;color:#f05a30}.orderFinish-container .wrapper .confirmInfo h3{margin-top:20px;color:#f05a30;font-size:22px}.orderFinish-container .wrapper .hotelInfo{margin-bottom:20px}.orderFinish-container .wrapper .hotelInfo:after{content:"";display:block;clear:both}.orderFinish-container .wrapper .hotelInfo img{width:160px;height:160px;margin-right:20px;float:left}.orderFinish-container .wrapper .hotelInfo .detail{width:65%;float:left}.orderFinish-container .wrapper .hotelInfo .detail h1{font-size:24px;margin-bottom:5px}.orderFinish-container .wrapper .hotelInfo .detail h2{color:#999;margin-bottom:5px}.orderFinish-container .wrapper .hotelInfo .detail .IMGroup{margin-top:10px}.orderFinish-container .wrapper .hotelInfo .detail .IMGroup:after{content:"";display:block;clear:both}.orderFinish-container .wrapper .hotelInfo .detail .IMGroup .grey{color:#999}.orderFinish-container .wrapper .hotelInfo .detail .IMGroup .left{width:54%;float:left}.orderFinish-container .wrapper .hotelInfo .detail .IMGroup .right{width:43%;float:right;position:relative;top:4px}.orderFinish-container .wrapper .hotelInfo .detail .IMGroup .right p .grey{width:65px;text-align:right;display:inline-block}.orderFinish-container .wrapper .dealInfo{background-color:#f7f7f7;padding:45px 35px 0}.orderFinish-container .wrapper .dealInfo .item{margin-bottom:20px}.orderFinish-container .wrapper .dealInfo .item .roomName{text-align:center;font-size:36px;margin-bottom:20px}.orderFinish-container .wrapper .dealInfo .item table{width:100%;margin:0 auto;padding-bottom:20px;border-bottom:1px solid #e6e6e6}.orderFinish-container .wrapper .dealInfo .item table .discoName{width:35%;color:#f05a30;font-size:20px}.orderFinish-container .wrapper .dealInfo .item table .breakfast{width:16%}.orderFinish-container .wrapper .dealInfo .item table .freeCancel{width:48%}.orderFinish-container .wrapper .dealInfo .item table .breakfast,.orderFinish-container .wrapper .dealInfo .item table .freeCancel{padding:0 5px;background-color:#ededed}.orderFinish-container .wrapper .dealInfo .item table tbody .t1{text-align:left}.orderFinish-container .wrapper .dealInfo .item table tbody .t2{text-align:center}.orderFinish-container .wrapper .dealInfo .item table tbody .t3{text-align:right}.orderFinish-container .wrapper .dealInfo .discount,.orderFinish-container .wrapper .dealInfo .total,.orderFinish-container .wrapper .dealInfo .reference{text-align:right}.orderFinish-container .wrapper .dealInfo .discount *,.orderFinish-container .wrapper .dealInfo .total *,.orderFinish-container .wrapper .dealInfo .reference *{font-size:22px;color:#f05a30}.orderFinish-container .wrapper .dealInfo .discount{margin-bottom:20px;padding-bottom:15px;border-bottom:1px solid #b3b3b3}.orderFinish-container .wrapper .dealInfo .total{margin-bottom:15px}.orderFinish-container .wrapper .dealInfo .reference{padding-bottom:20px;border-bottom:1px solid #e6e6e6}.orderFinish-container .wrapper .dealInfo .reference *{color:black}.orderFinish-container .wrapper .dealInfo .reference .fyi{color:#f05a30;font-size:20px}.orderFinish-container .wrapper .dealInfo .special{padding:20px 0 45px}.orderFinish-container .wrapper .dealInfo .special h1{margin-bottom:20px}.orderFinish-container .wrapper .tripadvisorLogo{margin:50px auto 20px}.orderFinish-container .wrapper .tripadvisorLogo .goWeb{width:240px;height:50px;margin-right:10px;line-height:2.18;background-color:#fee600;font-size:24px;text-align:center;text-decoration:none;display:inline-block}.orderFinish-container .wrapper .tripadvisorLogo img{position:relative;top:15px}.orderFinish-container .wrapper .remindGroup .remindChkIn,.orderFinish-container .wrapper .remindGroup .remindCancel{margin-bottom:40px}.orderFinish-container .wrapper .remindGroup .remindChkIn h1,.orderFinish-container .wrapper .remindGroup .remindCancel h1{font-size:20px;margin-bottom:7px}.orderFinish-container .wrapper .remindGroup .remindChkIn p,.orderFinish-container .wrapper .remindGroup .remindCancel p{margin-bottom:5px}.orderFinish-container .wrapper .remindGroup .remindCancel{margin-bottom:0}
	</style>
</body>
</html>