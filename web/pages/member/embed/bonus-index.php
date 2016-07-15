<?php
/**
 * 說明：
 * 作者：Bobby
 * 日期：2016年07月04日
 * 備註：
 */
require_once __DIR__ . '/../../../config.php';
header("Content-Type:text/html; charset=utf-8");

$user_id = $_SESSION['travel.ezding.user.data']['serialId'];

$tripitta_service = new tripitta_service();
$marketing_campaign_list = $tripitta_service->find_marketing_campaign_must_possessed_by_user($user_id);

$bonus = 0;
foreach ($marketing_campaign_list as $mc) {
	$marking_campaign_times = $tripitta_service->find_valid_marking_campaign_times($user_id, $mc['mc_id']);
	if ($mc["mc_type"] == 1) $bonus += $marking_campaign_times * 100;
}
$bonus = number_format($bonus);
?>
<div class="member-bonus-index-container">
	<div class="top">
		<h2>查詢旅遊金</h2>
	</div>
	<hr>
	<div class="myWallet">
		<div class="img-wallet"></div>
		<div class="wrap">
			<div class="text">
				<div>剩餘</div>
				<div class="currency">NTD</div>
			</div>
			<div class="price"><?php echo $bonus; ?></div>
		</div>
	</div>
	<div class="bonusList">
		<?php
		foreach ($marketing_campaign_list as $mc) {
			$bonus = 0;
			// 目前只有1，之後有別的在加
			if ($mc["mc_type"] == 1) $bonus = $mc["mc_use_times"] * 100;
			$mc_name = $mc["mc_name"];
			$d=strtotime($mc["mcmp_create_time"]);
			$mcmp_create_time = date("Y-m-d", $d);
			$mc_sell_end_date = $mc["mc_sell_end_date"];
		?>
		<div class="bonus">
			<div class="expiry">
				<div class="">發放日<span><?php echo $mcmp_create_time ?></span></div>
				<div class="">到期日<span><?php echo $mc_sell_end_date ?></span></div>
			</div>
			<div class="campaign">
				<div class="name"><?php echo $mc_name ?></div>
				<div class="text">
					<div>獲得</div>
					<div class="currency">NTD</div>
				</div>
				<div class="price"><?= $bonus ?></div>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
<script type="text/javascript">
	$(function(){
		$("#prexPage2").click(function(){
	        window.location.href = '/member/';
		});
	})
</script>