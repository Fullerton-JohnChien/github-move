<?
/**
 *  說明：
 *  作者：Bobby <bobby.luo@fullerton.com.tw>
 *  日期：2016年01月14日
 *  備註：
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

$db = new pdo_reader($db_dsn_ezding, $db_uid_ezding, $db_pwd_ezding);
$sourceMappingDao = new travel_source_mapping($db);
$category = "tripadvisor.homestay";
$source_id = $homestay_row['hs_id'];
//$source_id = "1167";
$SourceMappings = $sourceMappingDao->findSourceMappingsByCategoryAndSourceId($category, $source_id);
if (!empty($SourceMappings)) {
	$opts = ['user_id' => $order_row['om_user_id']];
	$trip_advisor_service = new trip_advisor_service();
	$url = $trip_advisor_service->get_rcp_widget_link($SourceMappings[0]['sm_ref_id'], $order_row['om_buyer_email'], $order_row['om_buyer_name'], $order_row['om_buyer_name'], '', $opts);
?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<style>
	*{
		font-size: 16px;
		font-family: arial, "Microsoft JhengHei";
	}
	</style>	
</head>
<body>
	<table width="650" align="center" border="0" cellpadding="15" cellspacing="0">
		<tr bgcolor="#FFE900">
			<td height="100" valign="bottom">
				<h1 style="font-size:25px;">您寶貴的意見</h1>
			</td>
			<td valign="bottom">
				<img src="<?= $server_url . $logo ?>"/>
			</td>
		</tr>
		<tr bgcolor="#f2f2f2">
			<td colspan="2">
				<p>
					<span class="username"><?= $order_row["om_buyer_name"] ?></span>先生/小姐 您好：<br><br>
					感謝您的入住，希望您在旅行過程中對於我們的服務感<br><br>
					到滿意。 在此耽誤您幾分鐘寶貴的時間，到全球最大旅遊網站<br><br>
					TripAdvisor為旅宿寫評論，幫助全球旅行者規劃旅程，<br><br>
					讓我們做的更好！
				</p>
			</td>
		</tr>
		<tr>
			<td colspan="2" bgcolor="#f2f2f2">
				<br>
				<table width="100%" align="center" border="0" cellpadding="15" cellspacing="0">
					<tr bgcolor="#f2f2f2">
						<tdm width="20%">
							<img src="<?= $homestay_image_url ?>" width="200">
						</td>
						<td>
							<h3><?= $order_homesty_row["oh_store_name"] ?></h3>
							<p style="margin:10px 0"><?= empty($city_town_row["ctml_city_name"]) ? $city_town_row["ct_city_name"] : $city_town_row["ctml_city_name"] ?></p>
							<p style="margin:10px 0"><?= $homestay_row["hs_email"] ?></p>
							<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td>
										<p style="margin:10px 0">
											<span style="color:grey">市話：</span>
											<span><?= $homestay_row["hs_phone"] ?></span>
										</p>
										<p style="margin:10px 0">
											<span style="color:grey">手機：</span>
											<span><?= $homestay_row["hs_mobile"] ?></span>
										</p>
									</td>
									<td>
										<p style="margin:10px 0">
											<span style="color:grey">Line：</span>
											<span><?= $homestay_row["hs_line"] ?></span>
										</p>
										<p style="margin:10px 0">
											<span style="color:grey">WeChat：</span>
											<span><?= $homestay_row["hs_wechat"] ?></span>
										</p>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>				
			</td>
		</tr>
		<tr>
			<td>
				<a href="<?php echo $url; ?>" style="margin-right: 25px;width:250px;padding:20px 60px;background-color: #ffe500; text-decoration: none; color:black;text-align: center" target="_blank">撰寫評論</a>
				<img src="<?= $server_url . $logo2 ?>"/>
			</td>
		</tr>
	</table>	
</body>
</html>
<?php 
	}
?>