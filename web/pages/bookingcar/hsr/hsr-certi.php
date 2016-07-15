<?php
/**
 * 說明：交通預定 - 高鐵 - 上傳憑證 Email通知
 * 作者：Sandy 
 * 日期：2016年6月7日
 * 備註：
 */
include_once __DIR__ . '/../../../config.php';

$tripitta_service = new tripitta_service();

$to_id = get_val("order_id");

$server_url = 'https://www.tripitta.com';
if(is_dev()) {
	$server_url= 'http://local.tw.tripitta.com';
} else if(is_alpha()) {
	$server_url = 'http://alpha.www.tripitta.com';
}


if (!empty($to_id)){
	$ticket_row = $tripitta_service->get_ticket_order($to_id,"1");
	//echo var_dump($ticket_row)."<BR>";
}
if (!empty($ticket_row)){
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
								<span style="font-size: 26px; font-weight: bold;vertical-align: super;margin-left: 10px;">高鐵乘車憑證Email通知</span>
								<hr style="margin-top: 15px; color: lightgrey;">
							</div>
							<div style="font-size: 16px;">
								<p style="margin: 0; font-size: 20px;">
									<span>親愛的旅客</span>
								</p>
								<p>
									<span style="display: block; line-height: 20px;">您的高鐵乘車兌換券如附檔，請確認您的旅客與乘坐資料無誤，若有問題請盡快透過客服信箱聯繫我們 <a href="https://www.tripitta.com/contact/" style="color:black;" target="_blank">https://www.tripitta.com/contact/</a>。
									</span>
									<span style="display: block; line-height: 20px;">
										再次提醒您，收到兌換憑證後的90天有效期限內，只要您還沒至高鐵櫃檯劃位換票，都可以針對訂單內容做部分/全部的取消或更改。更改視同取消，申請取消訂單作業完成後，我們會扣除10%手續費後退款給您，需請您重新成立新訂單。乘車券兌換成正式票券後後恕不受理任何形式之變更或退票，如不慎遺失、污損或遭竊時，亦不受理補發、掛失或退款，請務必小心保存。
									</span>
								</p>
							</div>
							<div style="font-size: 16px;">
								<p style="margin: 30px 0 0; font-size: 20px;">
									<span>兌換步驟</span>
								</p>
								<ol style="padding-left: 25px;">
									<span>Step 1</span>
									<li style="list-style-type:none";>
										取得電子乘車券後，<font color="red">隔天</font>即可至高鐵人工櫃檯兌換成正式紙本票券。 
									</li>
									<BR>
									<span>Step 2</span>
									<li style="list-style-type:none";>
										出行前，可先上台灣高鐵網站查詢您預計搭乘的班次 <a href="https://www.thsrc.com.tw/tw/TimeTable/SearchResult" style="color:black;" target="_blank">https://www.thsrc.com.tw/tw/TimeTable/SearchResult</a> 。 <BR>
										再憑電子乘車券（電子版和打印版均可）和證件至『高鐵人工售票窗口』兌換正式紙本車票（請留意：您出示的證件需與購票時­相同）­，兌換時需向高鐵站售票窗口工作人員指定有效搭乘日期（有效搭乘日，為兌換日起算30日內之任一天），方可使用。 
									</li>
									<BR>
									<span>Step 3</span>
									<li style="list-style-type:none";>
										兌換後的正式車票可任意搭乘自由座車廂，如需使用標準對號服務時請至售票窗口預先訂位，不加收費用。 
									</li>
									<BR>
									<span>Step 4</span>
									<li style="list-style-type:none";>
										進站時，請從『人工驗票閘門』進站，請攜帶證件以備查驗。 快樂出遊！
									</li>
								</ol>
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
