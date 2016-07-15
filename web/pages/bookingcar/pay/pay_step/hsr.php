<?php
/**
 * 說明：交通預定 - 高鐵 - 付款流程
 * 作者：Casper <casper.lee@fullerton.com.tw>
 * 日期：2016年6月16日
 * 備註：
 */
require_once __DIR__ . '/../../../../config.php';
header("Content-Type:text/html; charset=utf-8");

$tripitta_service = new tripitta_service();

if(!empty($_SESSION["pay_step"])){
	$checked = array("policy_chk");
	$selected = array("gender", "living_country_id");
	$p_step = $_SESSION["pay_step"];
	$ticket_id = $p_step["ticket_id"];
	$tree_type = $p_step["tree_type"];
	$start_area = $p_step["start_area"];
	$end_area = $p_step["end_area"];
	$take_date = $p_step["take_date"];
	$wechat_id = $p_step["wechat_id"];
	$line_id = $p_step["line_id"];
	$whatsapp_id = $p_step["whatsapp_id"];
	$ticket_adult = !empty($p_step["adult"]) ? $p_step["adult"] : 0;
	$ticket_child = !empty($p_step["child"]) ? $p_step["child"] : 0;
	$type = $p_step["type"];
	$coupon = $p_step["coupon"];
	$memo = $p_step["memo"];

	$passenger_last_name = array();
	$passenger_first_name = array();
	$passenger_birthday = array();
	$passenger_gender = array();
	$passenger_country = array();
	$passenger_number = array();
	for($i=0;$i<$ticket_adult+$ticket_child;$i++) {
		$passenger_last_name[$i] = $p_step["passenger_last_name[".$i."]"];
		$passenger_first_name[$i] = $p_step["passenger_first_name[".$i."]"];
		$passenger_birthday[$i] = $p_step["passenger_birthday[".$i."]"];
		$passenger_gender[$i] = $p_step["passenger_gender[".$i."]"];
		$passenger_country[$i] = $p_step["passenger_country[".$i."]"];
		$passenger_number[$i] = $p_step["passenger_number[".$i."]"];
	}

	$p_content = $p_step;
	for($i=0;$i<$ticket_adult+$ticket_child;$i++) {
		unset($p_content["passenger_last_name[".$i."]"]);
		unset($p_content["passenger_first_name[".$i."]"]);
		unset($p_content["passenger_birthday[".$i."]"]);
		unset($p_content["passenger_gender[".$i."]"]);
		unset($p_content["passenger_country[".$i."]"]);
		unset($p_content["passenger_number[".$i."]"]);
	}
	unset($p_content["take_date"]);
	unset($p_content["ticket_id"]);
	unset($p_content["tree_type"]);
	unset($p_content["start_area"]);
	unset($p_content["end_area"]);
	unset($p_content["adult"]);
	unset($p_content["child"]);
	unset($p_content["wechat_id"]);
	unset($p_content["line_id"]);
	unset($p_content["wechat_id"]);
	unset($p_content["type"]);
	unset($p_content["coupon"]);
	unset($_SESSION["pay_step"]);
}else{
	$ticket_id = get_val("ticket_id");
	// $tc_parent_id = get_val("tree_config_id");
	$tree_type = get_val("tree_type");
	$start_area = get_val("start_area");
	$end_area = get_val("end_area");
	$take_date = get_val("take_date");
	$ticket_adult = get_val_with_default("ticket_adult", 0);
	$ticket_child = get_val_with_default("ticket_child", 0);
	$type = get_val("type");
	$coupon = get_val("coupon");
	$memo = '';
	$wechat_id = '';
	$line_id = '';
	$whatsapp_id = '';

	$passenger_last_name = array();
	$passenger_first_name = array();
	$passenger_birthday = array();
	$passenger_gender = array();
	$passenger_country = array();
	$passenger_number = array();
	for($i=0;$i<$ticket_adult+$ticket_child;$i++) {
		$passenger_last_name[$i] = '';
		$passenger_first_name[$i] = '';
		$passenger_birthday[$i] = '';
		$passenger_gender[$i] = '';
		$passenger_country[$i] = '';
		$passenger_number[$i] = '';
	}
}

// 取得高鐵 - 票券類型資料
$ticket_price_list = $tripitta_service->find_ticket_type_price_by_ticket_id($ticket_id, $start_area, $end_area);

$adult_price = 0;
$children_price = 0;
$total = 0;
foreach ($ticket_price_list as $tpr) {
	if($tpr["tt_type"] == 1) {
		if($tpr["tt_value"] == "6") {
			$children_price = $tpr["ttp_sell_price"];
		}
		else if($tpr["tt_value"] == "12") {
			$adult_price = $tpr["ttp_sell_price"];
		}
	}
}
$total = $adult_price * $ticket_adult + $children_price * $ticket_child;

// 檢查 12歲以下小孩 (用年月日判斷)
$child_years = 12;
$child_date = date("Y") - $child_years . date("-m-d");

// $type_name = "高鐵";
$passenger_count = $ticket_adult+$ticket_child;
$min_birthday = "1/1/1920";
$max_birthday = date("m/d/Y", strtotime("-365 days"));
$take_time = strtotime($take_date);
$tripitta_web_service = new tripitta_web_service();
$tripitta_service = new tripitta_service();
$start_area_list = $tripitta_service->get_area_by_id($start_area);
$end_area_list = $tripitta_service->get_area_by_id($end_area);

//  echo var_dump($_SESSION['travel.ezding.user.data']);
// 2016-7-3 Howard 不知道為什麼session中姓名資料都放在nickname中，先改放這個
$name = (isset($_SESSION['travel.ezding.user.data']['name']) && $_SESSION['travel.ezding.user.data']['name'] != "") ? $_SESSION['travel.ezding.user.data']['name'] : $_SESSION['travel.ezding.user.data']['nickname'];
$gender = (isset($_SESSION['travel.ezding.user.data']['gender']) && $_SESSION['travel.ezding.user.data']['gender'] != "") ? $_SESSION['travel.ezding.user.data']['gender'] : "";
$living_country_id = (isset($_SESSION['travel.ezding.user.data']['living_country_id']) && $_SESSION['travel.ezding.user.data']['living_country_id'] != "") ? $_SESSION['travel.ezding.user.data']['living_country_id'] : "";
$mobile = (isset($_SESSION['travel.ezding.user.data']['mobile']) && $_SESSION['travel.ezding.user.data']['mobile'] != "") ? $_SESSION['travel.ezding.user.data']['mobile'] : "";
$email = (isset($_SESSION['travel.ezding.user.data']['email']) && $_SESSION['travel.ezding.user.data']['email'] != "") ? $_SESSION['travel.ezding.user.data']['email'] : "";

// 是否有折扣
$bonus = 0;
if(empty($coupon) || $coupon == "undefined") {
	$market_list = $tripitta_service -> find_campaign_campaign_must_possessed_by_user_and_type($_SESSION['travel.ezding.user.data']["serialId"], 1);
	if(!empty($market_list)) {
		foreach ($market_list as $ml) {
			$mc_id = $ml["mc_id"];
			$bo_array = $tripitta_service-> cal_marking_campain_discount($total, $mc_id, $_SESSION['travel.ezding.user.data']["serialId"]);
			$bonus = $bo_array["data"]["discount"];
			if($bonus > 0) break;
		}
	}
}

$pay_total = $total - $bonus;
?>
<form id="form1">
<section>
	<!-- 購買明細 -->
	<div class="productList">
		<div class="pBlock-hsr">
			<input type="hidden" name="ticket_id" value="<?php echo $ticket_id?>">
			<input type="hidden" name="tree_type" value="<?php echo $tree_type; ?>" />
			<input type="hidden" id="type" name="type" value="<?php echo $type; ?>">
			<input type="hidden" id="type" name="coupon" value="<?php echo $coupon; ?>">
			<input type="hidden" name="start_area" value="<?php echo $start_area; ?>" />
			<input type="hidden" name="end_area" value="<?php echo $end_area; ?>" />
			<input type="hidden" name="take_date" value="<?php echo $take_date; ?>" />
			<input type="hidden" name="adult" value="<?php echo $ticket_adult; ?>" />
			<input type="hidden" name="child" value="<?php echo $ticket_child; ?>" />
			<div class="imgWrap">
				<img src="../../../img/sec/transport/hsr/hsr.png" class="logo">
				<div class="ticketType">單程票</div>
			</div>
			<div class="pbBlock">
				<div class="pbDeparture">
					<div class="pbLocation"><?php echo $start_area_list["a_name"]; ?></div>
					<div class="pbText">出發地</div>
				</div>
				<div class="pbCalendar">
					<div class="pbYear"><?php echo date("Y", $take_time); ?></div>
					<div class="pbMD">
						<span class="pbMonth"><?php echo date("m", $take_time); ?></span>
						<span>.</span>
						<span class="pbDate"><?php echo date("d", $take_time); ?></span>
					</div>
					<div class="arrowWrap">
						<div class="arrowSign"></div>
					</div>
					<div class="pbText">乘坐日期</div>
				</div>
				<div class="pbDestination">
					<div class="pbLocation"><?php echo $end_area_list["a_name"]; ?></div>
					<div class="pbText">目的地</div>
				</div>
			</div>
		</div>

		<?php // if($ticket_adult>0 && $ticket_child > 0){ ?>
		<div class="pBlock-hsr2">
			<div class="pbLeft">
				<div class="listBlock">
					<div class="ptitle">成人單價</div>
					<div class="pdata"><span class="black">NTD</span><span class="black" id="adult_price"><?= number_format($adult_price) ?></span></div>
					<div class="ptitle">成人人數</div>
					<div class="pdata"><span class="black" id="adult_num" data-num="<?php echo $ticket_adult; ?>"><?php echo $ticket_adult; ?>人</span></div>
				</div>
				<div class="listBlock">
					<div class="ptitle">孩童單價</div>
					<div class="pdata"><span class="black">NTD</span><span class="black" id="child_price"><?= number_format($children_price) ?></span></div>
					<div class="ptitle">孩童人數</div>
					<div class="pdata"><span class="black" id="child_num" data-num="<?php echo $ticket_child; ?>"><?php echo $ticket_child; ?>人</span></div>
				</div>
			</div>
			<div class="pbRight">
				<!-- 最上面三塊做為空白與左方的值對齊 -->
				<div class="listBlock">
					<div class="ptitle">成人小計</div>
					<div class="pCurrency">NTD</div>
					<div class="pNum" id="adult_subtotal"><?= number_format($adult_price * $ticket_adult) ?></div>
				</div>
				<div class="listBlock">
					<div class="ptitle">孩童小計</div>
					<div class="pCurrency">NTD</div>
					<div class="pNum" id="child_subtotal"><?= number_format($children_price * $ticket_child) ?></div></div>
				<div class="listBlock">
					<hr>
				</div>
				<div class="listBlock">
					<div class="ptitle">產品總額</div>
					<div class="pCurrency">NTD</div>
					<div class="pNum" id="price_total"><?= number_format($total) ?></div>
				</div>
				<!--
				<div class="listBlock">
					<div class="ptitle">銀行紅利折點</div>
					<div class="pCurrency">NTD</div>
					<div class="pNum">-100</div>
					<a href="javascript:void(0)" class="fa fa-trash-o" aria-hidden="true"></a>
				</div>
				<div class="listBlock">
					<div class="ptitle">優惠折扣</div>
					<div class="pCurrency">NTD</div>
					<div class="pNum">-100</div>
				</div>
				-->
				<div class="listBlock">
					<div class="ptitle">優惠折扣</div>
					<div class="pCurrency">NTD</div>
					<div class="pNum" id="bonus_discount">-<?= $bonus ?></div>
					<?php if($bonus > 0) { ?>
					<!--
						<a href="javascript:void(0)" class="fa fa-trash-o" aria-hidden="true" id="del_bonus"></a>
					-->
					<?php } ?>
				</div>

				<div class="listBlock">
					<div class="ptitle">應付總額</div>
					<div class="pCurrency">NTD</div>
					<div class="pNum" id="pay_price_total"><?= number_format($pay_total) ?></div>
				</div>
			</div>
			<div class="pNote">
				成人定義(12歲以上) 孩童定義(6~11歲，未滿6歲或115cm以下免票)
			</div>
		</div>
		<?php //  } ?>
	</div>


	<!-- 填寫聯絡人資料 -->
	<div class="fillblankBlock sec">
		<h3>聯絡人資料</h3>
		<div class="colBlock">
			<label class="blank fName">
				<input type="text" id="name" name="name" value="<?php echo $name; ?>" maxlength="20" placeholder="姓名">
			</label>
			<label class="blank gender">
				<select name="gender">
					<option value="" disabled="true" selected>性別</option>
					<option value="M" <?php echo $gender == "M" ? 'selected="selected"' : ""; ?>>男</option>
					<option value="F" <?php echo $gender == "F" ? 'selected="selected"' : ""; ?>>女</option>
				</select>
				<i class="fa fa-angle-down" aria-hidden="true"></i>
			</label>
		</div>
		<div class="colBlock">
			<label class="blank pCounty">
				<select id="living_country_id" name="living_country_id">
					<option value="" disabled="true" selected>電話區號</option>
					<?php
						foreach(constants_user_center::$LIVING_COUNTRY_TEXT as $key => $value) {
						    echo '<option value="', $key, '"';
						    $country_row = $tripitta_web_service->load_country($key);
						    if ($key == $living_country_id) echo ' selected';
						    echo '>', $value . $country_row['c_tel_code'], '</option>';
						}
					?>
				</select>
				<i class="fa fa-angle-down" aria-hidden="true"></i>
			</label>
			<label class="blank pNum">
				<input type="text" id="mobile" name="mobile" value="<?php echo $mobile; ?>" maxlength="15" placeholder="電話號碼">
			</label>
		</div>
		<div class="colBlock">
			<label class="blank email">
				<input type="text" id="email" name="email" value="<?php echo $email; ?>" maxlength="50" placeholder="E-mail">
			</label>
		</div>

		<div class="colBlock social">
			<label class="blank socialMedia">
				<input type="text" id="wechat_id" name="wechat_id" maxlength="25" placeholder="WeChat ID" value="<?= $wechat_id ?>">
			</label>
			<label class="blank socialMedia">
				<input type="text" id="line_id" name="line_id" maxlength="25" placeholder="Line ID" value="<?= $line_id ?>">
			</label>
			<label class="blank socialMedia">
				<input type="text" id="whatsapp_id" name="whatsapp_id" maxlength="25" placeholder="Whatsapp ID" value="<?= $whatsapp_id ?>">
			</label>
		</div>

	</div>


	<!-- 填寫乘客資料 -->
	<div class="passenger-hsr sec">
		<h3>乘客資料</h3>
		<?php
			if($passenger_count > 0){
				for ($i=0; $i<$passenger_count; $i++){
				?>
		<div class="passenger">
			<div class="colBlock">
				<label class="blank pFirstName">
					<input type="text" name="passenger_last_name[<?php echo $i; ?>]" maxlength="20" value="<?= $passenger_last_name[$i]; ?>" placeholder="護照/證件英文姓">
				</label>
				<label class="blank pLastName">
					<input type="text" name="passenger_first_name[<?php echo $i; ?>]" maxlength="20" value="<?= $passenger_first_name[$i]; ?>" placeholder="護照/證件英文名">
				</label>
			</div>
			<div class="colBlock">
				<label class="blank birDate">
					<input type="text" name="passenger_birthday[<?php echo $i; ?>]" maxlength="15" value="<?= $passenger_birthday[$i]; ?>" placeholder="出生年月日 ">
				</label>
				<label class="blank gender">
					<select id="passenger_gender[<?php echo $i; ?>]" name="passenger_gender[<?php echo $i; ?>]">
						<option value="" disabled="true" selected>性別</option>
						<option value="M" <?php echo $passenger_gender[$i] == "M" ? 'selected="selected"' : ""; ?>>男</option>
						<option value="F" <?php echo $passenger_gender[$i] == "F" ? 'selected="selected"' : ""; ?>>女</option>
					</select>
					<i class="fa fa-angle-down" aria-hidden="true"></i>
				</label>
			</div>
			<div class="colBlock">
				<label class="blank country">
					<select id="passenger_country[<?php echo $i; ?>]" name="passenger_country[<?php echo $i; ?>]">
						<option value="" disabled="true" selected>國別</option>
						<?php foreach(constants_user_center::$LIVING_COUNTRY_TEXT as $key => $value) { ?>
						<?php if($key!=228){//不能有台灣?>
						<option value="<?php echo $key; ?>" <?php  if ($key == $passenger_country[$i]) echo ' selected'; ?>><?php echo $value; ?></option>
						<?php }}?>
					</select>
					<i class="fa fa-angle-down" aria-hidden="true"></i>
				</label>
				<label class="blank pIdentity">
					<input type="text" name="passenger_number[<?php echo $i; ?>]" value="<?= $passenger_number[$i]; ?>" placeholder="護照/證件號碼">
				</label>
			</div>
		</div>
		<?php if($i != $passenger_count - 1){?><br /><?php } ?>
				<?php
				}
			}
		?>
	</div>

	<?php /*
	<!-- 萬里通卡號 -->
	<div class="sec">
		<h3>亞洲萬里通卡號</h3>
		<label class="blank">
			<input type="text" name="" maxlength="50" placeholder="輸入卡號">
		</label>
	</div>
	*/ ?>

	<!-- 備註留言 -->
	<div class="sec">
		<h3>備註留言</h3>
		<textarea rows="5" id="memo" name="memo"><?= $memo ?></textarea>
	</div>


	<!-- 信用卡 -->
	<div class="creditBlock sec">
		<h3>選擇付款信用卡</h3>
		<label class="creditLabel">
			<input type="radio" value="tripitta.hitrust.ticket" name="credit" checked="true">
			<div class="creditFrame">
				<i class="fa fa-check-square-o" aria-hidden="true"></i>
				<i class="fa fa-square-o" aria-hidden="true"></i>
				<span class="creditTitle">信用卡/debit卡</span>
				<div class="img-visa"></div>
			</div>
		</label>
		<!--
		<label class="creditLabel ">
			<input type="radio" value="tripitta.china.union.ticket" name="credit">
			<div class="creditFrame">
				<i class="fa fa-check-square-o" aria-hidden="true"></i>
				<i class="fa fa-square-o" aria-hidden="true"></i>
				<span class="creditTitle">銀聯卡</span>
				<div class="img-unionpay"></div>
				<span class="text">銀聯卡(頁面將跳轉至銀聯卡頁面)</span>
			</div>
		</label>
		-->
		<?php /*
		<label class="creditLabel">
			<input type="radio" value="credit" name="credit">
			<div class="creditFrame">
				<i class="fa fa-check-square-o" aria-hidden="true"></i>
				<i class="fa fa-square-o" aria-hidden="true"></i>
				<span class="creditTitle">支付寶</span>
				<div class="img-alipay"></div>
				<span class="text">支付寶(頁面將跳轉至支付寶頁面)</span>
			</div>
		</label>
		*/ ?>
	</div>

	<div class="sec">
		<div class="btnWrap">
			<button type="button" class="pre">上一步</button>
			<button type="button" class="next" id="from_submit">下一步</button>
		</div>
	</div>
</section>


<!-- popup -->
<div class="popupChartPolicy">
	<div class="closeBtn">
		<i class="fa fa-times" aria-hidden="true"></i>
	</div>
	<h4>訂車條款和取消規定</h4>
	<div class="content">
		<ul class="popupPolicy">
			<li>國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國</li>
			<li>國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國</li>
			<li>國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國</li>
			<li>國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國</li>
			<li>國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國</li>
			<li>國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國</li>
			<li>國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國</li>
			<li>國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國</li>
			<li>國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國</li>
			<li>國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國</li>
			<li>國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國</li>
			<li>國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國</li>
			<li>國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國</li>
			<li>國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國</li>
			<li>1</li>
			<li>國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國</li>
			<li>國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國</li>
			<li>1</li>
			<li>國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國</li>
			<li>1</li>
			<li>國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國</li>
			<li>1</li>
			<li>1</li>
			<li>國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國</li>
			<li>1</li>
			<li>1</li>
			<li>國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國國</li>
			<li>1</li>
			<li>1</li>
			<li>1</li>
		</ul>
	</div>
	<div class="lightbox-close"></div>
</div>
</form>
<form id="form2" method="post" action="/web/pages/bookingcar/pay/pay_step_credit.php">
	<input type="hidden" id="pay_step" name="pay_step" />
</form>
<form id="form3" method="post" action="/web/pages/bookingcar/hsr_shopping_cart_to_order.php">
	<input type="hidden" id="pay_step2" name="pay_step2" />
</form>

<script type="text/javascript">
	var caneldar_option = <?php echo json_encode(Constants::$CALENDAR_OPTIONS); ?>;
	$('input[name^="passenger_birthday"]').datepicker(caneldar_option).
	datepicker('option', {maxDate: new Date('<?php echo $max_birthday; ?>'), minDate: new Date('<?php echo $min_birthday; ?>'), defaultDate: new Date('<?php echo $max_birthday; ?>'), yearRange: '-100:+100'});

	var bonus = <?= $bonus ?>;

	var check_passenger = function(input_name, item){
		var result = "OK";
		if(item==""){ item = 1;	}
		if(item==1){
			$("input[name^='"+input_name+"']").each(function(){
				if($(this).val()=="" && result=="OK"){
					$(this).focus();
					result = "FAIL";
				}
			});
		}else if(item == 2){
			$('[id^="'+input_name+'"]').each(function(){
				console.log(input_name+":"+$(this).val());
				if($(this).val()==null && result=="OK"){
					$(this).focus();
					result = "FAIL";
				}
			});
		}
		return result;
	}

	var check_passenger_birthday = function (input_name) {
		var result = "OK";
		var child = 0;
		$("input[name^='"+input_name+"']").each(function(){
			var input_val = $(this).val();
			var birthday = 	new Date(input_val);
			var max_birthday = new Date('<?php echo $child_date; ?>');
			if( birthday.getTime() > max_birthday.getTime()){
				child++;
			}
		});
		if(child == <?php echo $ticket_child; ?>){
		}else{
			result = "FAIL";
		}
		return result;
	}

	var checkdata = function(){
		var msg = '';
		if ($('#name').val() == '') {
			$('#name').focus();
			alert('請輸入聯絡人姓名!!');
			return;
		}
		if ($('#gender :selected').val() == '') {
			$('#gender').focus();
			alert('請輸入聯絡人性別!!');
			return;
		}
		if ($('#living_country_id :selected').val() == '') {
			$('#living_country_id').focus();
			alert('請輸入聯絡人電話區號!!');
			return;
		}
		if ($('#mobile').val() == '') {
			$('#mobile').focus();
			alert('請輸入聯絡人電話號碼!!');
			return;
		}
		if ($('#email').val() == '') {
			$('#email').focus();
			alert('請輸入聯絡人E-mail!!');
			return;
		}
		if (!verifyEmailAddress($('#email').val())) {
			$('#email').focus();
			alert('請檢查聯絡人E-mail格式是否正確!!');
			return;
		}

		// 乘客資料
		if(check_passenger("passenger_first_name", 1)=="FAIL"){
			alert('請輸入乘客護照/證件英文名!!');
			return;
		}
		if(check_passenger("passenger_last_name", 1)=="FAIL"){
			alert('請輸入乘客護照/證件英文姓!!');
			return;
		}
		if(check_passenger("passenger_birthday", 1)=="FAIL"){
			alert('請輸入乘客出生年月日!!');
			return;
		}
		if(check_passenger("passenger_gender", 2)=="FAIL"){
			alert('請輸入乘客性別!!');
			return;
		}
		if(check_passenger("passenger_country", 2)=="FAIL"){
			alert('請輸入乘客國別!!');
			return;
		}
		if(check_passenger("passenger_number", 1)=="FAIL"){
			alert('請輸入乘客身分證號碼 !!');
			return;
		}
		if(check_passenger_birthday("passenger_birthday")=="FAIL"){
			alert('乘客大人小孩數錯誤，請確認生日是否符合 !!');
			return;
		}

		if ($("input[name='credit']:checked").length == 0) {
			$('#credit').focus();
			alert('請選擇付款方式!!');
			return;
		}

		var credit_type = $('input[name="credit"]:checked').val();
		if (credit_type == "tripitta.hitrust.ticket") {
			var serialized = $('#form1').serializeArray();
	        var s = '';
	        var data = {};
	        for(s in serialized){
	            data[serialized[s]['name']] = serialized[s]['value']
	        }
			$("#pay_step").val(JSON.stringify(data));
			$('#form2').submit();
		} else if (credit_type == "tripitta.china.union.ticket")  {
			msg = "提醒您，當您按下「銀聯卡付款」，即進入銀聯卡扣款系統，當您輸入卡號及相關資料完成認証後，訂單即成立，且同時直接於您的銀聯卡帳戶中扣款完成，若您想要更改或取消訂單，相關扣款規定，請務必詳閱「訂購須知」。";
			if (confirm(msg)) {
				var serialized = $('#form1').serializeArray();
		        var s = '';
		        var data = {};
		        for(s in serialized){
		            data[serialized[s]['name']] = serialized[s]['value']
		        }
				$("#pay_step2").val(JSON.stringify(data));
				$('#form3').submit();
			}
		}
	}

	var step_selected = function(step){
		if(step>0){
			var step_count = 1;
			clear_selected();
			$('.step-m div').each(function(){
	            if(step_count==step){
	            	$(this).addClass('selected');
	            }
	            step_count++;
			});
		}else{
			$('#step1').show();
			$('#step2').show();
            $('#step3').show();
		}
		$(window).scrollTop(0);
	}

	var clear_selected = function(){
		$('.step-m div').each(function(){
			var circle_selected = $(this).attr("class");
            if(circle_selected.match('selected')){
            	$(this).removeClass('selected');
            }
		});
	}

	var reset_ticket_price = function (){
		$('#adult_price').html('0');
		$('#child_price').html('0');
		$('#adult_subtotal').html('0');
		$('#child_subtotal').html('0');
		$('#price_total').html('0');
		$('#pay_price_total').html('0');
	}

	var get_ticket_price = function (){
//		var type_id = '<?php //echo $type_id; ?>';
		var t_id = '<?php echo $ticket_id; ?>';
		var start_area = '<?php echo $start_area; ?>';
		var end_area = '<?php echo $end_area; ?>';

		if(t_id!="" && start_area!="" && end_area!=""){
			var ret = {};
			var p = {};
	        p.func = 'find_ticket_price';
// 	        p.type_id = type_id;
	        p.t_id = t_id;
	        p.start_area = start_area;
	        p.end_area = end_area;
	        $.ajax({
	            data: p,
	            url: "/web/ajax/ajax.php",
	            async: false,
	            type: "POST",
	            dataType: 'json',
	            success: function(data){
	            	if(data.code == '9999'){
	    				ret = "FAIL";
// 	                	reset_ticket_price();
// 	                    alert(data.msg);
	                } else {
	    				ret = "OK";
	                	ticket_total_price(data.data);
	                    //alert("取回資料：" + data.data);
	                }
	            }
	        });
	        return ret;
		}
	}

	// 合計票價
	var ticket_total_price = function(price){
		var adult_price = 0;
		var child_price = 0;
		var adult_number = <?php echo $ticket_adult; ?>;
		var child_number = <?php echo $ticket_child; ?>;
		$.each( price, function( key, value ) {
			if(value.tt_type==1){
				adult_price = value.sell_price;
				$('#adult_price').html(formatNumber(adult_price));
			}else if(value.tt_type==2){
				child_price = value.sell_price;
				$('#child_price').html(formatNumber(child_price));
			}
		});

		var adult_subtotal = parseInt(adult_price) * parseInt(adult_number);
		var child_subtotal = parseInt(child_price) * parseInt(child_number);
		$('#adult_subtotal').html(formatNumber(adult_subtotal));
		$('#child_subtotal').html(formatNumber(child_subtotal));
		var ticket_total = (parseInt(adult_price) * parseInt(adult_number)) + (parseInt(child_price) * parseInt(child_number));
		$('#price_total').html(formatNumber(ticket_total));
		$('#pay_price_total').html(formatNumber(ticket_total));
	}

	var showNotice = function (objId) {
		$(".overlay").show();
	    //$("#" + objId).css("left", ($(window).width() - $("#" + objId).width()) / 2);
	    $("#" + objId).show();
	}

	$(function(){
        var nowDate = new Date();
        var nowTime = nowDate.getTime();
        var expTime = nowTime + 30 * 60000;
    	setTimeout(function(){
  		  alert("填寫訂單即將於五分鐘後逾時，屆時將請您重新訂購。");
  		}, 25 * 60000);
        $('#form1 .pre').on("click", function(){
            var url = "/hsr/";
            url += "?tree_type=<?php echo $tree_type; ?>&take_date=<?php echo $take_date; ?>";
            url += "&start_area=<?php echo $start_area; ?>&end_area=<?php echo $end_area; ?>";
            url += "&ticket_adult=<?php echo $ticket_adult; ?>&ticket_child=<?php echo $ticket_child; ?>";
            url += '&ticket_id=<?php echo $ticket_id?>&coupon=<?= $coupon ?>';
            window.location.href = url;
        });

        $('#del_bonus').click(function () {
            $('#bonus_discount').html("-0");
            $('#pay_price_total').html(<?= $pay_total + $bonus ?>);
            $('#del_bonus').hide();
        });

		$('#from_submit').on("click",function(){
			var submitDate = new Date();
			var submitTime = submitDate.getTime();
			if (submitTime > expTime) {
				alert('本頁超過30分未執行,請您重新訂購。');
				location.href='/hsr/';
			} else {
				checkdata();
			}
		});

        var default_data = function(){
            <?php
            if(!empty($p_content)){
            	foreach ($p_content as $k => $v){
            		if(in_array($k, $checked)){
            		?>
            			$('input[name="<?php echo $k; ?>"]').prop("checked", true);
            <?php   }elseif (in_array($k, $selected)){ ?>
            			$('#<?php echo $k; ?>').val('<?php echo $v; ?>');
            		<?php
					}else{
            			if($k=="memo"){
            			?>
            			$('textarea[name="<?php echo $k; ?>"]').html('<?php echo $v; ?>');
            		<?php }else if(preg_match('/passenger\_country(.*)$/', $k) || preg_match('/passenger\_gender(.*)$/', $k)){ ?>
            			$('select[name="<?php echo $k; ?>"]').val('<?php echo $v; ?>');
            		<?php }else{ ?>
            			$('input[name="<?php echo $k; ?>"]').val('<?php echo $v; ?>');
						<?php
						}
					}
            	}
			}
            ?>
        }
      //  default_data();
        $('.step-m #i-step4').hide();
        $('.step-m #div-step4').hide();
        step_selected(2);
        // reset_ticket_price();
        // get_ticket_price();

	});
</script>