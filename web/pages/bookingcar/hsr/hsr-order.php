<?php
/**
 * 說明：交通預定 - 高鐵 - Email通知
 * 作者：Sandy
 * 日期：2016年6月7日
 * 備註：
 */
include_once __DIR__ . '/../../../config.php';

$tripitta_service = new tripitta_service();
$travel_countryDao = Dao_loader::__get_country_dao();
$areaDao = Dao_loader::__get_area_dao();

$to_id = get_val("order_id");

$server_url = 'https://www.tripitta.com';
if(is_dev()) {
	$server_url= 'http://local.tw.tripitta.com';
} else if(is_alpha()) {
	$server_url = 'http://alpha.www.tripitta.com';
}

if (!empty($to_id)){
	$ticket_row = $tripitta_service->get_ticket_order($to_id,"1");
	$ticket_row["begin_name"] = $areaDao->loadAreaWithLang ( $ticket_row["to_begin_area_id"], 'tw', true )['aml_name'];
	$ticket_row["end_name"] = $areaDao->loadAreaWithLang ( $ticket_row["to_end_area_id"], 'tw', true )['aml_name'];
	// echo var_dump($ticket_row)."<BR>";
}
if (!empty($ticket_row)){

	//票券類型
	$tc_id3_tcname = "";
	$to_ticket_id = $ticket_row["to_ticket_id"];

	$ticket_data = $tripitta_service->get_ticket_list($to_ticket_id);
	$tc_id3 = $ticket_data["t_tree_config_id"];

	$category = "tree.category";
	$tc_id3_list = $tripitta_service->find_tree_config($category, "",$tc_id3);
	$tc_id3_tcname = $tc_id3_list[0]["tc_name"];

	//國籍
	$country_row = $travel_countryDao->loadCountry($ticket_row["to_country_id"]);
	$country_cname="";
	if (!empty($country_row)){ $country_cname=$country_row["c_name"]; }

	$to_begin_area_name =""; $to_end_area_name="";
	$to_begin_area = $tripitta_service-> get_area_by_id($ticket_row['to_begin_area_id']);
	$to_end_area = $tripitta_service-> get_area_by_id($ticket_row['to_end_area_id']);
	if (!empty($to_begin_area)){
		$to_begin_area_name = $to_begin_area['a_name'];
	}
	if (!empty($to_end_area)){
		$to_end_area_name = $to_end_area['a_name'];
	}
	//取得票券-注意事項
	$notices_list = $tripitta_service->get_ticket_notices($to_ticket_id);
	//取得票券-取消規則說明
	$rule_list = $tripitta_service->get_ticket_cancel_rule($to_ticket_id);

	//取得票券-年齡編輯設定
	$age_edit_list = $tripitta_service->find_ticket_type($to_ticket_id);

	foreach ($age_edit_list as $age_edit_data){
		if (($age_edit_data['tt_name']=='成人') && (strlen($age_edit_data['tt_desc']) >0)){
			$tt_desc_adult = $age_edit_data['tt_desc'];
		}
		if (($age_edit_data['tt_name']=='小孩') && (strlen($age_edit_data['tt_desc']) >0)){
			$tt_desc_child = $age_edit_data['tt_desc'];
		}
	}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="zh-Hant">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
</head>
<body style="font-family: Microsoft JhengHei;">
	<table width="640" align="center" cellpadding="0" cellspacing="0">
		<tr>
			<td>
				<table width="580" align="center" style="margin: 30px auto 0; color: black;">
					<tr>
						<td>
							<div>
								<img src="<?=$server_url?>/web/img/sec/transport/hsr/logo.png" width="142" height="50">
								<span style="font-size: 26px; font-weight: bold;vertical-align: super;margin-left: 10px;">高鐵訂單Email通知</span>
								<hr style="margin-top: 15px; color: lightgrey;">
							</div>
							<div style="font-size: 14px;">
								<p><span><?=$ticket_row['to_user_name']?></span><span style="margin-left: 10px;"></span> 您好：</p>
								<p>
									您已完成訂購， 在我們的服務時間內：一到六 09:00～19:00；日 09:00～16:00 訂購，訂購後一小時內我們會再寄出電子乘車券給您，非服務時間則為隔日出票 。以下為您的訂購資料。
								</p>
								<p>
									<span style="width: 60px; display: inline-block;">訂單編號</span>
									<span style="margin-left: 10px;color:#eb592c; display: inline-block;"><?=$ticket_row['to_transaction_id']?></span>
								</p>
								<p>
									<span style="width: 60px; display: inline-block;">票券類型</span>
									<span style="margin-left: 10px;"><?=$tc_id3_tcname?></span>
								</p>
								<p>
									<span style="width: 60px; display: inline-block;">出發地</span>
									<span style="margin-left: 10px;"><?=$to_begin_area_name?></span>
								</p>
								<p>
									<span style="width: 60px; display: inline-block;">目的地</span>
									<span style="margin-left: 10px;"><?=$to_end_area_name?></span>
								</p>
								<?

									$ticket_type_list = $ticket_row["order_line"];
									//echo var_dump($ticket_type_list)."xxx<BR>";
									//根據不同票種做加總
									$ticket_price = array();
									$ticket_price[0]['tol_ticket_type_name'] = "成人";
									$ticket_price[0]['tol_sell_price'] = "0";
									$ticket_price[0]['total_sell_price'] = "0";
									$ticket_price[0]['tol_count'] = "0";

									$ticket_price[1]['tol_ticket_type_name'] = "小孩";
									$ticket_price[1]['tol_sell_price'] = "0";
									$ticket_price[1]['total_sell_price'] = "0";
									$ticket_price[1]['tol_count'] = "0";

									$ticket_price[2]['tol_ticket_type_name'] = "嬰兒";
									$ticket_price[2]['tol_sell_price'] = "0";
									$ticket_price[2]['total_sell_price'] = "0";
									$ticket_price[2]['tol_count'] = "0";

									$ticket_price[3]['tol_ticket_type_name'] = "老人";
									$ticket_price[3]['tol_sell_price'] = "0";
									$ticket_price[3]['total_sell_price'] = "0";
									$ticket_price[3]['tol_count'] = "0";


									if (!empty($ticket_type_list)){
										foreach ($ticket_type_list as $ticket_type_data){
											if ($ticket_type_data['tol_ticket_type_name']=="成人"){
												$ticket_price[0]['tol_count'] = abs($ticket_price[0]['tol_count']) + 1;
												$ticket_price[0]['tol_sell_price'] = $ticket_type_data['tol_sell_price'];
												$ticket_price[0]['total_sell_price'] = abs($ticket_price[0]['total_sell_price']) + abs($ticket_type_data['tol_sell_price']);
											}
											if ($ticket_type_data['tol_ticket_type_name']=="小孩"){
												$ticket_price[1]['tol_count'] = abs($ticket_price[1]['tol_count']) + 1;
												$ticket_price[1]['tol_sell_price'] = $ticket_type_data['tol_sell_price'];
												$ticket_price[1]['total_sell_price'] = abs($ticket_price[1]['total_sell_price']) + abs($ticket_type_data['tol_sell_price']);
											}
											if ($ticket_type_data['tol_ticket_type_name']=="嬰兒"){
												$ticket_price[2]['tol_count'] = abs($ticket_price[2]['tol_count']) + 1;
												$ticket_price[2]['tol_sell_price'] = $ticket_type_data[2]['tol_sell_price'];
												$ticket_price[2]['total_sell_price'] = abs($ticket_price[2]['total_sell_price']) + abs($ticket_type_data['tol_sell_price']);
											}
											if ($ticket_type_data['tol_ticket_type_name']=="老人"){
												$ticket_price[3]['tol_count'] = abs($ticket_price[3]['tol_count']) + 1;
												$ticket_price[3]['tol_sell_price'] = $ticket_type_data['tol_sell_price'];
												$ticket_price[3]['total_sell_price'] = abs($ticket_price[3]['total_sell_price']) + abs($ticket_type_data['tol_sell_price']);
											}
										}
									}
									$ticket_price_count = 0;
									foreach ($ticket_price as $ticket_price_data){
									$ticket_price_count++;
										if ($ticket_price_data['tol_count'] > 0) {
								?>
								<!-- 成人 -->
								<p>
									<span style="width: 60px; display: inline-block; color: #888888;">
										<?=$ticket_price_data['tol_ticket_type_name']?>單價
									</span>
									<span style="margin-left: 10px;display: inline-block;vertical-align: top; color: #888888;">
										NTD
									</span>
									<span style="margin-left: 10px;display: inline-block;vertical-align: top; color: #888888;">
										<?=$ticket_price_data['tol_sell_price']?>
									</span>
									<span style="margin-left: 50px;display: inline-block;vertical-align: top; color: #888888;">
										<?=$ticket_price_data['tol_ticket_type_name']?>人數
									</span>
									<span style="margin-left: 10px;display: inline-block;vertical-align: top;">
										<?=$ticket_price_data['tol_count']?>人
									</span>
									<br>
									<span style="width: 60px; display: inline-block;">
										&nbsp;
									</span>
									<span style="margin-left: 10px;display: inline-block;vertical-align: top;">
										NTD
									</span>
									<span style="margin-left: 10px;display: inline-block;vertical-align: top;">
										<?=$ticket_price_data['total_sell_price']?>
									</span>
								</p>
								<!-- 成人 -->
								<?

										} //if ($ticket_price_data['tol_count']>0){
									}
								?>
								<p>
									<span style="color:#eb592c;">
										成人定義(<?=$tt_desc_adult?>)    孩童定義(<?=$tt_desc_child?>)
									</span>
								</p>
								<?
								$total_money="0"; //產品總額
								$total_money = abs($ticket_row['to_bank_trans_amount'])+abs($ticket_row['to_coupon_discount'])+abs($ticket_row['to_marking_campaign_discount'])+$ticket_row['to_bonus_discount'];
								?>
								<p>
									<span style="width: 60px; display: inline-block; color: #888888;">產品總額</span>
									<span style="margin-left: 10px;">NTD </span>
									<span style="margin-left: 10px;"><?=$total_money?></span>
								</p>
								<p>
									<span style="width: 60px; display: inline-block; color: #888888;">紅利折抵</span>
									<span style="margin-left: 10px;">NTD </span>
									<span style="margin-left: 10px;"><?=$ticket_row['to_bonus_discount']?></span>
								</p>
								<p>
									<span style="width: 60px; display: inline-block; color: #888888;">優惠折扣</span>
									<span style="margin-left: 10px;">NTD </span>
									<span style="margin-left: 10px;"><?=abs($ticket_row['to_coupon_discount'])+abs($ticket_row['to_marking_campaign_discount'])?></span>
								</p>
								<p>
									<span style="width: 60px; display: inline-block;color:#eb592c;">應付總額</span>
									<span style="margin-left: 10px;color:#eb592c;">NTD </span>
									<span style="margin-left: 10px;color:#eb592c;"><?=$ticket_row['to_bank_trans_amount']?></span>
								</p>
							</div>
							<div style="font-size: 14px;">
								<hr style="margin-top: 15px; color: lightgrey;">
								<p>
									<span style="width: 100%; display: inline-block; font-size: 18px; font-weight: bold;">聯絡人資料</span>
								</p>
								<p>
									<span style="width: 60px; display: inline-block;">聯絡人</span>
									<span style="margin-left: 10px;"><?=$ticket_row['to_user_name']?></span>
								</p>
								<p>
									<span style="width: 60px; display: inline-block;">聯絡電話</span>
									<span style="margin-left: 10px;"><?=$country_cname?></span>
									<span style="margin-left: 10px;">+886</span>
									<span style="margin-left: 10px;"><?=$ticket_row['to_user_mobile']?></span>
								</p>
								<p>
									<span style="width: 60px; display: inline-block;">Email</span>
									<span style="margin-left: 10px;"><?=$ticket_row['to_user_email']?></span>
								</p>
								<p>
									<span style="width: 60px; display: inline-block;">聯絡資訊</span>
									<span style="margin-left: 10px; display: inline-block;">
										<span>Wechat：</span><span><?=$ticket_row['to_wechat_id']?></span>
									</span>
									<br>
									<span style="width: 60px; display: inline-block;">
										&nbsp;
									</span>
									<span style="margin-left: 10px; display: inline-block;">
										<span>Line：</span><span><?=$ticket_row['to_line_id']?></span>
									</span>
									<br>
									<span style="width: 60px; display: inline-block;">
										&nbsp;
									</span>
									<span style="margin-left: 10px; display: inline-block;">
										<span>Whatsapp：</span><span><?=$ticket_row['to_whats_app_id']?></span>
									</span>
								</p>
							</div>
							<div style="margin-top: 30px; font-size: 14px;">
								<p style="margin: 0;">
									<span style="width: 100%; display: inline-block; font-size: 18px; font-weight: bold;">乘客資料</span>
								</p>
								<?
								$ticket_type_list = $ticket_row["order_line"];
								//echo var_dump($ticket_type_list)."xxx<BR>";
								$ticket_type_count = 0;
								foreach ($ticket_type_list as $ticket_type_data){
								$ticket_type_count++;

								//國籍
								$country_row = $travel_countryDao->loadCountry($ticket_type_data["tol_user_country_id"]);
								$country_cname="";
								if (!empty($country_row)){ $country_cname=$country_row["c_name"]; }
								$tol_user_passport_name = $ticket_type_data["tol_user_passport_name"];
								$explode_passport_name = explode(" ",$tol_user_passport_name);
								$passport_name = "";
								for ($i = 1; $i < count($explode_passport_name); $i++) {
									$passport_name .= $explode_passport_name[$i] . ' ';
								}
								?>

								<!-- 乘客 -->
								<div style="width: 47%; display: inline-block;">
									<p>
										<span style="width: 60px; display: inline-block;">英文姓</span>
										<span style="margin-left: 10px;"><?=$explode_passport_name[0]?></span>
									</p>
									<p>
										<span style="width: 60px; display: inline-block;">英文名</span>
										<span style="margin-left: 10px;"><?=$passport_name?></span>
									</p>
									<p>
										<span style="width: 60px; display: inline-block;">性別</span>
										<span style="margin-left: 10px;"><?=$ticket_type_data['tol_user_gender']?></span>
									</p>
									<p>
										<span style="width: 60px; display: inline-block;">出生日期</span>
										<span style="margin-left: 10px;"><?=$ticket_type_data['tol_user_birthday']?></span>
									</p>
									<p>
										<span style="width: 60px; display: inline-block;">國籍</span>
										<span style="margin-left: 10px;"><?=$country_cname?></span>
									</p>
									<!--
									<p>
										<span style="width: 60px; display: inline-block;">身分證號</span>
										<span style="margin-left: 10px;">AXXXXXXXXX</span>
									</p> -->
								</div>
								<!-- 乘客 -->
								<?}?>
							</div>
							<div style="margin-top: 30px; font-size: 14px;">
								<!--  <p style="margin: 0;">
									<span style="width: 100%; display: inline-block; font-size: 18px; font-weight: bold;">萬里通卡號</span>
								</p>-->
								<p>
									<span style="width: 100%; display: inline-block;"><!--1111-2222-3333-4444  --></span>
								</p>
							</div>
							<div style="margin-top: 30px; font-size: 14px;">
								<p style="margin: 0;">
									<span style="width: 100%; display: inline-block; font-size: 18px; font-weight: bold;">備註留言</span>
								</p>
								<p>
									<span style="width: 100%; display: inline-block;">
										<?=$ticket_row['to_memo']?>
									</span>
								</p>
								<hr style="margin-top: 15px; color: lightgrey;">
							</div>
							<div style="margin-top: 30px; font-size: 14px;">
								<p style="margin: 0;">
									<span style="width: 100%; display: inline-block; font-size: 16px; ">注意事項：</span>
								</p>
								<ul style="padding-left: 25px;">
								<?foreach ($notices_list as $notices_data){?>

									<li>
										<?=$notices_data['tc_title']?>
									</li>
								<?}?>

								</ul>
								<p style="margin: 20px 0 0;">
									<span style="width: 100%; display: inline-block; font-size: 16px; ">取消規定：</span>
								</p>
								<ul style="padding-left: 25px;">
								<?foreach ($rule_list as $rule_data){?>
									<li>
										<?=$rule_data['tc_title']?>
									</li>
								<?}?>
								</ul>
							</div>
						</td>
					</tr>
				</table><br>
			</td>
		</tr>
	</table>
</body>
</html>
<?}?>