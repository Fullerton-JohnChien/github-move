<?php
/**
 * 說明：
 * 作者：Bobby
 * 日期：2016年07月01日
 * 備註：
 */
require_once __DIR__ . '/../../../../config.php';
header("Content-Type:text/html; charset=utf-8");

$tripitta_service = new tripitta_service();
$tripitta_web_service = new tripitta_web_service();

$type = 5;
$order_id = get_val("order_id");
$user_id = $_SESSION['travel.ezding.user.data']['serialId'];
$order = $tripitta_service->get_ticket_order_by_order_id($order_id);
$order_line = $tripitta_service->find_ticket_order_line($order_id);
$begin_area_id = $order["to_begin_area_id"];
$end_area_id = $order["to_end_area_id"];
$to_create_time = substr($order['to_create_time'], 0, 10);
$living_country_id = $order["to_country_id"];
$living_country_row = $tripitta_web_service->load_country($living_country_id);
$living_country = $living_country_row["c_name"];
$living_country_code = $living_country_row["c_tel_code"];
$cancel_order_line = $tripitta_service->find_unprocessed_apply_cancel_order_line_by_category_and_ref_id("hf_ticket_order", $order_id);

// 取得高鐵 - 票券類型資料
$tc_level = "1";
$t_id = 1;
$t_partner_id = "2";
$category = null;
$tree_list = $tripitta_service->find_tree_config($category, $t_partner_id);
$tree_type = null;
$tree_type_name = null;
if(!empty($tree_list)){
	$tree_count = 1;
	foreach ($tree_list as $t){
		if($tree_count == 1){
			$tree_type = $t["tc_id"];
			$tree_type_name = $t["tc_name"];
		}
		$tree_count++;
	}
}
$tc_level = $tc_level . "." . $t_partner_id . "." . $tree_type;
$ticket_price_list = $tripitta_service->find_ticket_price($tc_level, $begin_area_id, $end_area_id, $t_id);
$adult_price = 0;
$child_price = 0;
if(!empty($ticket_price_list)){
	foreach ($ticket_price_list as $tpl){
		if($tpl["tt_name"]=="成人"){
			$adult_price = $tpl["ttp_sell_price"];
		}
		if($tpl["tt_name"]=="小孩"){
			$child_price = $tpl["ttp_sell_price"];
		}
	}
}
//取得票券-年齡編輯設定
$age_edit_list = $tripitta_service->find_ticket_type($t_id);

foreach ($age_edit_list as $age_edit_data){
	if (($age_edit_data['tt_name']=='成人') && (strlen($age_edit_data['tt_desc']) >0)){
		$tt_desc_adult = $age_edit_data['tt_desc'];
	}
	if (($age_edit_data['tt_name']=='小孩') && (strlen($age_edit_data['tt_desc']) >0)){
		$tt_desc_child = $age_edit_data['tt_desc'];
	}
}

//取得票券-取消規則說明
$rule_list = $tripitta_service->get_ticket_cancel_rule($t_id);
$adult_count = 0;
$child_count = 0;
$adult_total = 0;
$child_total = 0;
if(!empty($order_line)){
	foreach ($order_line as $ol){
		if($ol["tol_ticket_type_name"]=="成人"){
			$adult_total += $ol["tol_sell_price"];
			$adult_count++;
		}
		if($ol["tol_ticket_type_name"]=="小孩"){
			$child_total += $ol["tol_sell_price"];
			$child_count++;
		}
	}
}

$to_user_id = $order['to_user_id'];
$user = $tripitta_service->get_user_by_user_id($to_user_id);
$to_user_name = $order['to_user_name'];
$to_transaction_id = $order['to_transaction_id'];
$gender = ($user['msg']['gender'] == "M") ? "先生" : "小姐";
$begin_area = $tripitta_service->get_area_by_id($begin_area_id);
$end_area = $tripitta_service->get_area_by_id($end_area_id);
$begin_name = $begin_area["a_name"];
$end_name = $end_area["a_name"];
$to_forecast_date = $order["to_forecast_date"];
$take_time = strtotime($to_forecast_date);
$to_wechat_id = $order["to_wechat_id"];
$to_line_id = $order["to_line_id"];
$to_whats_app_id = $order["to_whats_app_id"];
$all_cancel = 0;
if (count($order_line)==count($cancel_order_line)) $all_cancel = 1;
?>
<div class="member-cancel-hsr-container">
	<div class="order" style="padding: 0px 15px;">
		<div class="title" style="display:block;padding: 0px;">
			<span>取消訂單</span>
			<button class="order-back" data-type="<?php echo $type; ?>" data-devicetype="<?php echo $deviceType; ?>">回上頁</button>
		</div>
	</div>
	<hr class="hidden">
	<div class="pBlock-hsr">
		<div class="imgWrap">
			<img src="/web/img/sec/transport/hsr/hsr.png" class="logo">
			<div class="ticketType"><?php echo $tree_type_name; ?></div>
		</div>
		<div class="pbBlock">
			<div class="pbDeparture">
				<div class="pbLocation"><?php echo $begin_name; ?></div>
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
				<div class="pbLocation"><?php echo $end_name; ?></div>
				<div class="pbText">目的地</div>
			</div>
		</div>
	</div>
	<div class="order">
		<ul class="orderInfo">
			<li>
				<span><?php echo $to_user_name; ?></span>
				<span><?php echo $gender; ?></span>
				您好
			</li>
			<li>
				您已完成訂購， 在我們的服務時間內：一到六 09:00～19:00；日 09:00～16:00 訂購，訂購後一小時內我們會再寄出電子乘車券給您，非服務時間則為隔日出票 。以下為您的訂購資料。以下為您的訂購資料。
			</li>
			<li class="mt10">
				訂單編號 <span class="onge ml10"><?php echo $to_transaction_id; ?></span>
			</li>
		</ul>
	</div>
	<div class="twoBlockWrap">
		<div class="listWrap ">
			<div class="lBlock">
				<div class="lBlock">
					<div class="lTitle">訂購日期</div>
					<div class="lData">
						<?php echo $to_create_time; ?>
					</div>
				</div>
			</div>
			<div class="lBlock between">
				<div class="lBlock">
					<div class="lTitle">成人單價</div>
					<div class="lData">
						<span class="currency">NTD</span>
						<span><?php echo number_format($adult_price); ?></span>
					</div>
				</div>
				<div class="lBlock">
					<div class="lTitle">成人人數</div>
					<div class="lData"><?php echo $adult_count; ?> 人</div>
				</div>
			</div>
			<div class="lBlock between">
				<div class="lBlock">
					<div class="lTitle">孩童單價</div>
					<div class="lData">
						<span class="currency">NTD</span>
						<span><?php echo number_format($child_price); ?></span>
					</div>
				</div>
				<div class="lBlock">
					<div class="lTitle">孩童人數</div>
					<div class="lData"><?php echo $child_count; ?> 人</div>
				</div>
			</div>
			<div class="lBlock">
				<hr class="hidden">
			</div>
		</div>
		<div class="listWrap">
			<div class="lBlock hidden">
				<div class="lBlock">
					&nbsp;
				</div>
			</div>
			<div class="lBlock">
				<div class="lTitle">成人小計</div>
				<div class="lData AliRight"><span>NTD</span><span class="priceNum"><?php echo number_format($adult_total); ?></span></div>
			</div>
			<div class="lBlock">
				<div class="lTitle">孩童小計</div>
				<div class="lData AliRight"><span>NTD</span><span class="priceNum"><?php echo number_format($child_total); ?></span></div>
			</div>
			<div class="lBlock">
				<hr>
			</div>
			<div class="lBlock">
				<div class="lTitle">產品總額</div>
				<div class="lData AliRight"><span>NTD</span><span class="priceNum"><?php echo number_format($adult_total + $child_total); ?></span></div>
			</div>
			<!--
			<div class="lBlock">
				<div class="lTitle">銀行紅利折點</div>
				<div class="lData AliRight"><span>NTD</span><span class="priceNum">-100</span></div>
			</div>
			-->
			<div class="lBlock">
				<div class="lTitle">優惠折扣</div>
				<div class="lData AliRight"><span>NTD</span><span class="priceNum">-<?php echo number_format($order["to_coupon_discount"]+$order["to_marking_campaign_discount"]); ?></span></div>
			</div>
			<div class="lBlock">
				<div class="lTitle">應付總額</div>
				<div class="lData AliRight">NTD<span class="priceNum onge"><?php echo number_format($order["to_sell_price"]); ?></span></div>
			</div>
		</div>
	</div>
	<div class="listWrap mt10">
		<div class="onge">
			成人定義(<?php echo $tt_desc_adult; ?>)
			<br>孩童定義(<?php echo $tt_desc_child; ?>)
		</div>
	</div>
	<div class="listWrap">
		<h3>聯絡人資料</h3>
		<div class="lBlock">
			<div class="lTitle">訂購人</div>
			<div class="lData"><?php echo $order["to_user_name"]; ?></div>
		</div>
		<div class="lBlock">
			<div class="lTitle">聯絡電話</div>
			<div class="lData">
				<span><?php echo $living_country; ?></span>
				<span class="ml10"><?php echo $living_country_code; ?></span>
				<span class="ml10"><?php echo $order["to_user_mobile"]; ?></span>
			</div>
		</div>
		<div class="lBlock">
			<div class="lTitle">Email</div>
			<div class="lData"><?php echo $order["to_user_email"]; ?></div>
		</div>
		<div class="lBlock">
			<div class="lTitle">聯絡資訊</div>
			<div class="lData">
				<div class="socialMedia">
					<div class="wrap">
						<div>Wechat : </div>
						<div><?php echo $to_wechat_id; ?></div>
					</div>
					<div class="wrap">
						<div>Line : </div>
						<div><?php echo $to_line_id ?></div>
					</div>
					<div class="wrap">
						<div>Whats App : </div>
						<div><?php echo $to_whats_app_id ?></div>
					</div>
					<div class="wrap">
						<div></div>
						<div></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<hr>
	<div class="cancelBlk">
		<div class="row">
			<div class="title1">
				請選取您要取消的乘客
			</div>
			<div class="btnWrap">
				<button class="notall" id="all">全選</button>
				<button class="notall" id="notall">全不選</button>
			</div>
		</div>
		<div class="blk">
			<input id="return_price" type="hidden" value="0">
			<input id="discount" type="hidden" value="<?php echo $order["to_coupon_discount"]+$order["to_marking_campaign_discount"]; ?>">
			<input id="user_id" type="hidden" value="<?php echo $user_id; ?>">
			<input id="order_id" type="hidden" value="<?php echo $order_id; ?>">
			<input id="all_cancel" type="hidden" value="<?php echo $all_cancel; ?>">
			<?php
				if (!empty($order_line)) {
					foreach ($order_line as $ol) {
						$chkFlag = 0;
						foreach ($cancel_order_line as $col) {
							if($ol['tol_id'] == $col['acol_ref_id']) {
								$chkFlag = 1;
								break;
							}
						}
						if(!empty($ol["tol_user_country_id"])){
							$country_row = $tripitta_web_service->load_country($ol["tol_user_country_id"]);
						}
			?>
			<label class="member">
				<input type="checkbox" name="order_line[<?php echo $ol['tol_id']; ?>]" value="<?php echo $ol['tol_sell_price']; ?>" data-tol_id="<?php echo $ol['tol_id']; ?>" <?php if ($chkFlag == 1 || $ol['tol_status'] == 2) echo "disabled";?>>
				
				<div class="checkSignWrap" >
					<i class="fa fa-check-square-o"></i>
					<?php if ($chkFlag == 0 && $ol['tol_status'] == 1) { ?>
					<i class="fa fa-square-o"></i>
					<?php } ?>
				</div>
				<div class="info" <?php if ($chkFlag == 1 || $ol['tol_status'] == 2) echo "style=background-color:#ededed";?>>
					<div class="row1">
						<div class="meta">英文姓名</div>
						<div class="data"><?php echo $ol["tol_user_name"]; ?></div>
					</div>
					<!--
					<div class="row1">
						<div class="meta">英文名</div>
						<div class="data">Amy</div>
					</div>
					<div class="row1">
						<div class="meta">英文姓</div>
						<div class="data">Liu</div>
					</div>
					-->
					<div class="row1">
						<div class="meta">性別</div>
						<div class="data"><?php echo $ol["tol_user_gender"]=="M" ? "男" : "女"; ?></div>
					</div>
					<div class="row1">
						<div class="meta">出生日期</div>
						<div class="data"><?php echo $ol["tol_user_birthday"]; ?></div>
					</div>
					<div class="row1">
						<div class="meta">國籍</div>
						<div class="data"><?php echo $country_row["c_name"]; ?></div>
					</div>
					<div class="row1">
						<div class="meta">身分證號</div>
						<div class="data"><?php echo $ol["tol_user_passport_number"]; ?></div>
					</div>
				</div>
			</label>
			<?php
					}
				}
			?>
		</div>
	</div>
	<div class="info1">
		<div class="left">
			<div>
				取消訂單明細
			</div>
		</div>
		<?php if ($deviceType == "computer") { ?>
		<div class="right">
		<?php } else { ?>
		<div class="right" style="width:100%;">
		<?php } ?>
			<div class="row">
				<div class="meta">產品售價</div>
				<div class="currency">NTD</div>
				<div class="price"><span id="cancel_price">0</span></div>
			</div>			
			<div class="row">
				<div class="meta">取消費</div>
				<div class="currency">NTD</div>
				<div class="price">-<span id="cancel_fee">0</span></div>
			</div>
			
			<div class="row">
				<div class="meta">優惠折扣</div>
				<div class="currency">NTD</div>
				<div class="price">-<span id="marking_campaign_discount">0</span></div>
			</div>
		</div>
	</div>
	<hr>
	<div class="info2">
		<?php if ($deviceType == "computer") { ?>
		<div class="left">
		<?php } else { ?>
		<div class="left" style="width:100%;">
		<?php } ?>
			<ul class="policy">
				取消須知：
				<?foreach ($rule_list as $rule_data){?>
					<li>
						<?=$rule_data['tc_title']?>
					</li>
				<?}?>
				<!-- li>訂購完成後在90天有效期限內，還沒換票都可以申請取消。</li>
				<li>更改視同取消，我們會扣除10%手續費後退款給您，需請您重新成立訂單。</li>
				<li>若您使用支付寶付款，則將會扣除 本訂單金額之4%，20元為手續費，恕無法全額退款。</li>
				<li>確定取消作業完成後，5日內將刷退至原付款之信用卡帳戶中。</li>
				<li>若您使用銀聯卡或支付寶之則將依中國各銀行之作業時間為準，約7~20個工作天不等。</li -->
			</ul>
		</div>
		<?php if ($deviceType == "computer") { ?>
		<div class="right">
		<?php } else { ?>
		<div class="right" style="width:100%;">
		<?php } ?>
			<div class="row">
				<div class="meta onge">刷退金額</div>
				<div class="currency onge">NTD</div>
				<div class="price onge"><span id="return_price_show">0</span></div>
			</div>
			
			<div class="row">
				<div class="meta">紅利返還</div>
				<div class="payBack">0</div>
			</div>
			
			<div class="row">
				<div class="note">( 約 NTD <span id="return_price_show_1">0</span> )</div>
			</div>
		</div>
	</div>
	<button class="btn" onclick="cancel();">
		申請取消
	</button>
	<!--
	<button class="btn">
		回接送機訂單
	</button>
	 -->
	<div class="note1">
		確定取消後，將刷退至原付款之信用卡帳戶
	</div>
	<div class="popupConfirm">
		<div class="closeBtn">
			<i class="fa fa-times" aria-hidden="true"></i>
		</div>
		<p class="text">
			已為您送出取消訂單申請，
			<br>我們會在一個工作天內
			<br>為您進行審核及取消作業，請稍待。
		</p>
		<div class="btnWrap">
			<button class="btn">回高鐵訂單明細</button>
		</div>
	</div>
</div>
<script src="/web/js/orders.js"></script>
<script type="text/javascript">
	var amount_price = 0;
	$(function(){
		$('#all').on("click", function() {
			$('#all').addClass("all");
			$('#all').removeClass("notall");
			$('#notall').addClass("notall");
			$('#notall').removeClass("all");
			var return_price = 0;
			var selected = 0;
			var order_line_array = new Array();
			amount_price = 0;
			$("input[name^='order_line']").each(function() {
				var tol_id = $(this).data('tol_id');
				var checked_name = "order_line["+tol_id+"]";
				var price = $('input[name="'+checked_name+'"]').val();
				if (!$(this).prop("disabled")) {
					$(this).prop("checked", true);
					amount_price+=parseInt($(this).val());
				}
		        return_price = parseInt(return_price) + parseInt(price);

				var tol_checked = $('input[name="'+checked_name+'"]:checked').val();
				if (tol_checked != undefined) {
					selected++;
					order_line_array.push(tol_id);
					order_line = order_line_array.join(",");
				}		        
		    });
		    
			var order_id = $("#order_id").val();
			$.getJSON('/web/ajax/ajax.php',
					{func: 'cal_ticket_order_refund_price', 'order_id': order_id, 'cancel_ids': order_line},
					function(data) {
						if (data.code == "0000") {
							//amount_price+=parseInt(sell_price);		

							$("#cancel_price").text(amount_price);
							$("#return_price_show").text(data.data['refund_amount']);
							$("#return_price_show_1").text(data.data['refund_amount']);
							$("#cancel_fee").text(data.data['cancel_fee']);
							$("#marking_campaign_discount").text(data.data['marking_campaign_discount_refund']);
						} else if (data.code == "9999") {
							alert(data.msg);
						}
					}
				);		    
		    $("#return_price").val(return_price);
			$("#return_price_show").html(return_price);
			$("#return_price_show_1").html(return_price);
		});
		$('#notall').on("click", function() {
			$('#all').addClass("notall");
			$('#all').removeClass("all");
			$('#notall').addClass("all");
			$('#notall').removeClass("notall");
			$("input[name^='order_line']").each(function() {
		         $(this).prop("checked", false);
		    });
		    var return_price = 0;
			$("#return_price").val(return_price);
			$("#return_price_show").html(return_price);
			$("#return_price_show_1").html(return_price);
			$("#cancel_price").text(0);
			$("#cancel_fee").text(0);
			$("#marking_campaign_discount").text(0);	
			amount_price = 0;		
		});
		
		//var return_price = $("#return_price").val();
		//return_price = parseInt(return_price) - parseInt(discount);
		//$("#return_price").val(return_price);
		//var amount_price = 0;
		$('input[name^="order_line"]').on('click',function() {
			/*
			var return_price = $("#return_price").val();
			var discount = $("#discount").val();
			var tol_id = $(this).data('tol_id');
			var checked_name = "order_line["+tol_id+"]";
			var tol_checked = $('input[name="'+checked_name+'"]:checked').val();
			var tol_no_checked = $('input[name="'+checked_name+'"]').val();
			if (tol_checked != undefined) {
				return_price = parseInt(return_price) + parseInt(tol_checked);
			} else {
				return_price = parseInt(return_price) - parseInt(tol_no_checked);
			}
			$("#return_price").val(return_price);
			$("#return_price_show").html(return_price);
			$("#return_price_show_1").html(return_price);
			*/
			$('#all').addClass("notall");
			$('#all').removeClass("all");
			$('#notall').removeClass("all");
			$('#notall').addClass("notall");			
			var sell_price = $(this).val();
			var chk_flag = $(this).prop("checked");
			
			var selected = 0;
			var order_line_array = new Array();
			$("input[name^='order_line']").each(function() {
				var tol_id = $(this).data('tol_id');
				var checked_name = "order_line["+tol_id+"]";
				var tol_checked = $('input[name="'+checked_name+'"]:checked').val();
				if (tol_checked != undefined) {
					selected++;
					order_line_array.push(tol_id);
					order_line = order_line_array.join(",");
				}
			});	
			var order_id = $("#order_id").val();
			if (selected > 0) {			
				$.getJSON('/web/ajax/ajax.php',
						{func: 'cal_ticket_order_refund_price', 'order_id': order_id, 'cancel_ids': order_line},
						function(data) {
							if (data.code == "0000") {
								if (chk_flag) {
									amount_price+=parseInt(sell_price);		
								} else {
									amount_price-=parseInt(sell_price);
								}	

								$("#cancel_price").text(amount_price);
								$("#return_price_show").text(data.data['refund_amount']);
								$("#return_price_show_1").text(data.data['refund_amount']);
								$("#cancel_fee").text(data.data['cancel_fee']);
								$("#marking_campaign_discount").text(data.data['marking_campaign_discount_refund']);
							} else if (data.code == "9999") {
								alert(data.msg);
							}
						}
					);	
			} else {
				amount_price = 0;
				$("#cancel_price").text(0);
				$("#return_price_show").text(0);
				$("#return_price_show_1").text(0);
				$("#cancel_fee").text(0);
				$("#marking_campaign_discount").text(0);	
			}		
		});
		if ($("#all_cancel").val() == 1) {
			$(".popupConfirm").show();
			$('.overlay').show();
		}
		var deviceType = "<?php echo $deviceType; ?>";
		if (deviceType == "computer") {
			url = "orders-hsr.php";
			id_name = "#myOrders";
		} else {
			url = "orders-hsr-m.php";
			id_name = "#myOrders-m";						
		}
		var redirect_url = "/web/pages/member/embed/orders/" + url;
		$(".popupConfirm Button.btn, .closeBtn").click(function() {
			$('.overlay').hide();
			$( id_name ).load( redirect_url );
		});			
	});

	function cancel() {
		var selected = 0;
		var order_line;
		var order_line_array = new Array();
		$("input[name^='order_line']").each(function() {
			var tol_id = $(this).data('tol_id');
			var checked_name = "order_line["+tol_id+"]";
			var tol_checked = $('input[name="'+checked_name+'"]:checked').val();
			if (tol_checked != undefined) {
				selected++;
				order_line_array.push(tol_id);
				order_line = order_line_array.join(",");
			}
		});
		if (selected == 0) {
			alert("請選取您要取消的乘客!");
		} else {
			var order_id = $("#order_id").val();
			var user_id = $("#user_id").val();
			$.getJSON('/web/ajax/ajax.php',
				{func: 'cancel_ticket_order', 'order_id': order_id, 'user_id': user_id, 'cancel_ids': order_line},
				function(data) {
					if (data.code == "0000") {
						$(".popupConfirm").show();
						$('.overlay').show();
					} else if (data.code == "9999") {
						alert(data.msg);
					}
					var url = "";
					var id_name = "";
					var deviceType = "<?php echo $deviceType; ?>";
					if (deviceType == "computer") {
						url = "orders-hsr.php";
						id_name = "#myOrders";
					} else {
						url = "orders-hsr-m.php";
						id_name = "#myOrders-m";						
					}
					var redirect_url = "/web/pages/member/embed/orders/" + url;
					$(".popupConfirm Button.btn").click(function() {
						$('.overlay').hide();
						$( id_name ).load( redirect_url );
					});		
				}
			);
		}
	}
</script>