<?php
$homeStayRuleDetailDao = Dao_loader::__get_home_stay_rule_detail_dao();
$homeStayExtend = Dao_loader::__get_home_stay_extend_dao();
// 取得民宿相關規定資料
$homeStayRuleDetailList = $homeStayRuleDetailDao->findValidHomeStayRuleDetailListByHomeStayId($homeStayId);

$home_stay_rule_row = $tripitta_homestay_service -> get_home_stay_rule($homeStayId);
$deposit = 0;
$theDayRate = 0;
$checkInTime = '';
$checkOutTime = '';
$checkKeepTime = ''; // 最晚入住時間
$maxCancelDate = '';
$keepHour = '';
$fee = 100;
//printmsg($homeStayRuleDetailList);
// 1:check in, 2:check out, 3:入住當日退房, 4:入住日前n天退房, 5:天災延期, 6:入住當日延期, 7:入住日前n天延期, 8:訂金%, 9:延期次數
foreach ($homeStayRuleDetailList as $hsrd) {
	if ($hsrd['hsrd_type'] == 1) $checkInTime = substr($hsrd['hsrd_check_time'], 0, 5);
	else if ($hsrd['hsrd_type'] == 2) $checkOutTime = substr($hsrd['hsrd_check_time'], 0, 5);
	else if ($hsrd['hsrd_type'] == 3) $theDayRate = $hsrd['hsrd_rate'];
	else if ($hsrd['hsrd_type'] == 4) $maxCancelDate = $hsrd['hsrd_check_date'];
	else if ($hsrd['hsrd_type'] == 8) $deposit = $hsrd['hsrd_rate'];
	else if ($hsrd['hsrd_type'] == 10) {
		$checkKeepTime = substr($hsrd['hsrd_check_time'], 0, 5);
		$keepHour=round(substr($hsrd['hsrd_check_time'],0,2));
	}
}

$homeStayExtendRow = $homeStayExtend->loadHfHomeStayExtend($homeStayId);
?>
<div class="div_booking001">
<table width="700" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="700" height="35" class="introduction_bt20b" style="font-size: 26px">訂房注意事項</td>
  </tr>
</table>
<table width="700" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="700" height="35" class="introduction_bt20b">入住退房須知：</td>
  </tr>
  <tr class="introduction_t12">
    <td valign="top">
    ★入住時間：<?php if (substr($checkInTime, 0, 2) > 11) echo '下午'; else echo '上午';?> <font color="blue"><?php echo $checkInTime?></font> 以後，若當日會延遲入住，務必請先至電通知民宿業主。<br />
    ★退房時間：<?php if (substr($checkOutTime, 0, 2) > 11) echo '下午'; else echo '上午';?> <font color="blue"><?php echo $checkOutTime?></font> 以前。若延後退房，可能會產生相關延遲費用，請自行負責。<br />
    ★最晚入住時間：<?php if (empty($keepHour)){ echo '不限定。'; }else {?>請務必於 <font color="blue"><?php echo $checkKeepTime;?></font> 以前辦理入住手續，若因行程可能延誤，<font color="blue">請務必先電話聯絡業者，並告知業者正確的入住時間</font>，<font color="red">若未告知則會視同當日未入住，並不退還該日之住房費用</font>。<?php }?><br />
    ★住房需提供身份証件(外藉人士需帶護照，大陸遊客請出示入台証)及本網提供之訂房確認單，辦理入住手續。<br />
    ★入住人數，務必同訂房人數，若不符合規定時，業者有權現場要求補收相關差價，或不予超過之人員入住。敬請注意，此責任自負，因此問題造成之損失，本網站恕無法賠償或退還任何款項。
    </td>
  </tr>
</table>
<table width="150" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td height="30">&nbsp;</td>
  </tr>
</table>
<style>
.introduction_bt12blue{	font-family: "微軟正黑體","Microsoft JhengHei","Helvetica Neue","Arial","Trebuchet MS","Helvetica","Verdana","sans-serif";font-size: 12px;
	color: blue;
	}
</style>
<table width="700" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="700" height="35" class="introduction_bt20b">取消訂房須知：</td>
  </tr>
  <tr class="introduction_t12">
    <td valign="top">
			<h3>
				<p>★客戶若欲取消訂房，相關依<span style="color:blue"> <?= $home_stay_row['name'] ?></span>取消規定。如下所示：</p>
			</h3>
			<h3>
				<p>★本民宿之取消訂房之相關扣款費用計算規範如下：</p>
			</h3>
			<h4>
				<p>★<b>旅客於住宿日前 <span style="color:red"><?= $date1 = $home_stay_rule_row['lastCancelDays'] +1 ?></span> 日前不含入住日(<span style="color:blue"><?= date('Y/m/d', strtotime($beginDate . ' -' . $date1 . ' days')) ?></span>)取消訂房，全額退回。</b></p>
			</h4>
			<h4>
				<p>★旅客於住宿日前<span style="color:red"> <?= $date2 = $home_stay_rule_row['lastCancelDays'] ?></span>日內(<span style="color:blue"><?= date('Y/m/d', strtotime($beginDate . ' -' . $date2 . ' days')) ?></span>)取消訂房，扣<span style="color:red">第一晚房價</span>，為取消訂房手續費。</p>
			</h4>
			<h4>
				<p>★旅客於住宿 <span style="color:red">當日</span>(<span style="color:blue"><?= date('Y/m/d', strtotime($beginDate)) ?></span>) 將無法取消訂房，當日未入住恕無法退款。</p>
			</h4>
			<h3>
				<p><i class="fa fa-dot-circle-o"></i>本民宿於連續假期、特殊節慶，大型展會期或民宿所在區之旺季期間訂房，恕不接受取消訂房之要求，請務必特別注意，以確保您的權益。</p>
			</h3>
			<h3>
				<p><i class="fa fa-dot-circle-o"></i>如住宿之所在地 或 旅客出發地，於入住當日因天災(主要機關發佈如颱風地震產生相關重大影響等)或其他重大災害（人力不可抗力因素）影響，或有主要交通中斷情形發生，<span style="color:blue"> 致使無法如期入住時，敬請於發佈訊息後<span style="color:red">3</span>日內，儘速與本網客服人員連繫，更改或取消您的訂房</span>，本網將會依各民宿住房取消、更改規定辦理相關手續。</p>
			</h3>
    </td>
  </tr>
</table>
</div>