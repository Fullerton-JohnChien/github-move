<?
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
// printmsg($order_row);

$lang = $order_row["ode_language"];
$order_id = $order_row["od_id"];
$store_id = $order_row["od_store_id"];
$order_homesty_row = $order_row["order.homestay"][0];
$order_line_homestay_list = $order_homesty_row["order.lines"];
$days = (strtotime($order_homesty_row["oh_check_out_date"]) - strtotime($order_homesty_row["oh_check_in_date"])) / 86400;

// 取得旅宿、房型資料
$homestay_row = $homestay_service->get_hotel_by_id($lang, $store_id);
$city_town_row = $tripitta_web_service->get_city_town_by_id($lang, $homestay_row["hs_city_town_id"]);

$ta_info = $tripitta_web_service->get_trip_advisor_id_by_category_and_source_id('tripadvisor.homestay', $store_id);


// $homestay_concat_dao = Dao_loader::__get_home_stay_contact_dao();
// $homestay_contact_row = $homestay_concat_dao->getHfHomeStayContactByHomeStayIdAndIndex($store_id, 1);
// printmsg($homestay_contact_row);

$home_stay_rule_row = $tripitta_homestay_service -> get_home_stay_rule($store_id);

$server_url = 'https://www.tripitta.com';
$logo = '/web/img/emailLogo.png';
$url_order_list = '/member/invoice/';
if(is_dev()) {
    $server_url= 'http://local.www.tripitta.com';
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
	<div class="survey-container">
		<div class="header">
			<h1>您寶貴的意見</h1>
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
					感謝您的入住，希望您在旅行過程中對於我們的服務感
				</p>
				<p>
					到滿意。 在此耽誤您幾分鐘寶貴的時間，填寫以下問
				</p>
				<p>
					卷，讓我們有更多的進步空間。
				</p>
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
							<p>
								<span class="grey">Line</span>
								<span class="lineID"><?= $homestay_row["hs_line"] ?></span>
							</p>
							<p>
								<span class="grey">WeChat</span>
								<span class="wechatID"><?= $homestay_row["hs_wechat"] ?></span>
							</p>
						</div>
					</div>
				</div>
			</div>

			<!-- button -->
			<? if(!empty($ta_info)) { ?>
			<a href="http://www.tripadvisor.com/WidgetEmbed-cdspropertydetail?locationId=<?= $ta_info["tari_id"] ?>&lang=zh_TW&partnerId=CB56EED944AF4459B7E92BBF9B292AC6&display=true" class="goWeb">填寫問卷</a>
			<? } ?>
		</div>
	</div>
	<style>
	*{font-size:16px;font-family:arial, "Microsoft JhengHei";color:#404040}html,body{height:100%}body{margin:0;background:#ededed}a{outline:none}input,textarea{padding:8px;box-sizing:border-box;border:1px solid #e6e6e6;outline:none}textarea{resize:none}h1,h2,h3,h4,h5,p{margin:0;padding:0;font-weight:lighter}.survey-container{width:650px;margin:0 auto}.survey-container .header{padding:15px 50px;background-color:#fee600}.survey-container .header:after{content:"";display:block;clear:both}.survey-container .header h1{margin:48px 0 0;font-size:28px;float:left}.survey-container .header img{width:74px;height:82px;float:right}.survey-container .wrapper{padding:68px 50px;background-color:white}.survey-container .wrapper .confirmInfo{margin-bottom:50px}.survey-container .wrapper .confirmInfo p{margin:5px 0;font-size:20px}.survey-container .wrapper .confirmInfo p *{font-size:20px}.survey-container .wrapper .hotelInfo{margin-bottom:20px}.survey-container .wrapper .hotelInfo:after{content:"";display:block;clear:both}.survey-container .wrapper .hotelInfo img{width:160px;height:160px;margin-right:20px;float:left}.survey-container .wrapper .hotelInfo .detail{width:65%;float:left}.survey-container .wrapper .hotelInfo .detail h1{font-size:24px;margin-bottom:5px}.survey-container .wrapper .hotelInfo .detail h2{color:#999;margin-bottom:5px}.survey-container .wrapper .hotelInfo .detail .IMGroup{margin-top:10px}.survey-container .wrapper .hotelInfo .detail .IMGroup:after{content:"";display:block;clear:both}.survey-container .wrapper .hotelInfo .detail .IMGroup .grey{color:#999}.survey-container .wrapper .hotelInfo .detail .IMGroup .left{width:54%;float:left}.survey-container .wrapper .hotelInfo .detail .IMGroup .right{width:43%;float:right;position:relative;top:4px}.survey-container .wrapper .hotelInfo .detail .IMGroup .right p .grey{width:65px;text-align:right;display:inline-block}.survey-container .wrapper .goWeb{width:250px;height:60px;margin:50px auto;line-height:2.5;background-color:#fee600;font-size:24px;text-align:center;text-decoration:none;display:block}
	</style>
</body>
</html>