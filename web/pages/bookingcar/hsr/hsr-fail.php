<?php
/**
 * 說明：交通預定 - 高鐵 - 取消成功Email通知信
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
$btn_url="https://www.tripitta.com/member/?item=order";
if(is_dev()) {
	$server_url= 'http://local.tw.tripitta.com';
	$btn_url="http://local.tw.tripitta.com/member/?item=order";
} else if(is_alpha()) {
	$btn_url="http://alpha.www.tripitta.com/member/?item=order";
	$server_url = 'http://alpha.www.tripitta.com';
}
if (!empty($to_id)){
	$ticket_row = $tripitta_service->get_ticket_order($to_id,"1");
	$ticket_row["begin_name"] = $areaDao->loadAreaWithLang ( $ticket_row["to_begin_area_id"], 'tw', true )['aml_name'];
	$ticket_row["end_name"] = $areaDao->loadAreaWithLang ( $ticket_row["to_end_area_id"], 'tw', true )['aml_name'];
	// echo var_dump($ticket_row)."<BR>";


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
								<span style="font-size: 26px; font-weight: bold;vertical-align: super;margin-left: 10px;">高鐵取消失敗Email通知信</span>
								<hr style="margin-top: 15px; color: lightgrey;">
							</div>
							<div style="font-size: 16px;">
								<p style="margin: 0; font-size: 20px;">
									<span>Dear <span><?=$ticket_row['to_user_name']?></span> <!-- <span>小姐</span> --> 您好： </span>
								</p>
								<p>
									<div style="width: 90px; font-size: 16px; color: #666666; display: inline-block;">訂單編號</div>
									<div style="font-size: 16px;display:inline-block; color: #f05930;"><?=$ticket_row['to_transaction_id']?></div>
								</p>
								<p>
									<div style="width: 90px; font-size: 16px; color: #666666; display:inline-block">商品名稱</div>
									<div style="font-size: 16px;display:inline-block"><?=$ticket_row['to_ticket_name']?></div>
								</p>
								<p style="font-weight: bold; line-height: 24px;">
									因已經過了90天有限期限/已換成正式票券，所以無法取消。詳細訂單內容請至會員中心查看，謝謝。
								</p>
								<p>
									<input type="button" name="" value="高鐵訂單明細" onclick="location.href='<?=$btn_url?>'" style="width: 80%; max-width: 300px; height: 40px; margin: 150px auto; font-size: 16px; display:block; background-color: #fee600; border-radius: 40px; cursor: pointer; ">
								</p>
							</div>
							<div style="margin-top: 60px; font-size: 14px;">
								<p style="margin: 0; text-align: right;">
									<span style="width: 100%; display: inline-block; font-size: 24px; font-weight: bold;">
										Tripitta客服中心<span style="margin-left: 5px; font-size: 14px;">敬上</span>
									</span>
								</p>
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
