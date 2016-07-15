<?php
/**
 * 說明：交通預定 - 高鐵 - 付款流程1
 * 作者：Casper <casper.lee@fullerton.com.tw>
 * 日期：2016年5月6日
 * 備註：
 */
include_once __DIR__ . '/../../../config.php';
header("Content-Type:text/html; charset=utf-8");

// 預設清除 pay_step session
if(!empty($_SESSION["pay_step"])){
	unset($_SESSION["pay_step"]);
}

// 頁面基本參數
// $favourite_type = 11;
$adult_count = 15;
$child_count = 15;
$pageSize = 9;
$limit = $pageSize;
$pageno = get_val('pageno');
$user_serial_id = (isset($_SESSION['travel.ezding.user.data']['serialId'])) ? $_SESSION['travel.ezding.user.data']['serialId'] : 0;
if (empty($pageno) || $pageno <= 0) {
    $pageno = 1;
}
$offset = 0;
if ($pageno == 1) {
    $offset = $pageno - 1;
} else {
    $offset = $pageSize * ($pageno - 1);
}
$tripitta_web_service = new tripitta_web_service();
$login_user_data = $tripitta_web_service->check_login();
$is_login = false;


// 頁面傳送資料
$start_area = get_val('start_area');
$end_area = get_val('end_area');
$coupon = get_val('coupon');
if(empty($start_area)&&empty($end_area))
	$end_area = 278;
/*
2016-7-3
＊＊乘坐日期規則
1. 上班時間定義： 一～六 09:00~19:00； 日09:00~16:00，其餘為下班時間 (server time is TW time)
2. Case：
如果今天是7/2 (六) 上班時間，乘坐日期可以選到的即為7/3開始 （＋1）
如果現在是7/2(六) 下班時間，乘坐日期可以選到的為7/4開始 （+2）
3. 乘坐日期預設為最近可以購買的日期
*/
$take_date = get_val('take_date');
if (empty($take_date)) {
	$weekday = intval(date("w"));
	$hour= intval(date("H"));
	// 星期日>=16點 或 星期一～六>=19點，則可訂日期加2日
	if ((($weekday == 0) && ($hour >= 16)) ||
		(($weekday >= 1) && ($weekday <= 6) && ($hour >= 19)))
		$take_date_default = date('Y-m-d', strtotime(date('Y-m-d') . " +2 day"));
	else
		$take_date_default = date('Y-m-d', strtotime(date('Y-m-d') . " +1 day"));
} else {
	$take_date_default = $take_date;
}
$ticket_adult = get_val('ticket_adult');
$ticket_child = get_val('ticket_child');

$tripitta_service = new tripitta_service();
// 取得高鐵 - 票券類型資料
$ticket_id = get_val('ticket_id');
$tc_parent_id = get_val('tree_config_id');
if (empty($ticket_id)) $ticket_id = 1;
if (empty($tc_parent_id)) $tc_parent_id = "2";
$t_id = $ticket_id;
$category = 'tree.category';
$tree_list = $tripitta_service->find_tree_config($category, $tc_parent_id);

// 取得該票券的票種
// $ticket_type_list = $tripitta_service->find_ticket_type_by_ticket_id($ticket_id);

// 取得出發地/目的地資料
$area_list = $tripitta_service->find_ticket_area();

//取得票券-商品介紹
$product_desciption_list = $tripitta_service->get_ticket_product_desciption($t_id);

//取得票券-注意事項
$notices_list = $tripitta_service->get_ticket_notices($t_id);

//取得票券-取消規則說明
$rule_list = $tripitta_service->get_ticket_cancel_rule($t_id);

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
?>
<!DOCTYPE html>
<html lang="zh-Hant" prefix="og: http://ogp.me/ns#">
	<head>
		<?php include __DIR__ . "/../../common/head_new.php"; ?>
        <link rel="stylesheet" href="/web/css/main.css?01121536">
		<link rel="stylesheet" href="/web/css/main2.css">
        <style>
            .kv{
                width: 100%;
                height: 100%;
                background: url(/web/img/location/location_banner_food.jpg) 50% 50%/cover no-repeat;
            }
            .selectWrap input{
                border: 0px;
                margin-top: -0.2em;
            }
        </style>
        <title>交通預定 - Tripitta 旅必達</title>
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css">
        <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
        <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
        <script src="/web/js/jquery.twbsPagination.js" type="text/javascript"></script>
	</head>
<body>
	<header><?php include __DIR__ . "/../../common/header_new.php"; ?></header>
	<?php
	//if($header_is_login == 0) {
	//	alertmsg("需登入才能訂購!", "/transport/");
	//}
	?>
	<main class="hsr-payStep-container">
	    <input type="hidden" id="ticket_id" value="<?php echo $t_id?>">
		<h1 class="title">付款步驟</h1>
		<div class="step-m">
			<!-- selected為該步驟進行中 done為已結束的步驟 -->
			<div class="circle selected ">1</div>
			<i class="fa fa-arrow-right" aria-hidden="true"></i>
			<div class="circle">2</div>
			<i class="fa fa-arrow-right" aria-hidden="true"></i>
			<div class="circle">3</div>
		</div>
		<section class="payInfo">
			<div class="secContainer">
				<div class="row">
					<div class="block">
						<img src="/web/img/sec/transport/hsr/hsr.png" class="logo">
					</div>
					<div class="block text">
						<span style="color:hsl(13, 87%, 56%)">訂購時需提供護照或證照號碼及英文姓名</span>
					</div>
				</div>
				<div class="row">
					<div class="block fillBlank">
						<div class="bTitle">
							票券類型
						</div>
						<label class="blank">
							<select id="tree_type" name="tree_type">
								<?php
									if(!empty($tree_list)){
										foreach ($tree_list as $tl){
											?>
								<option value="<?php echo $tl["tc_id"]; ?>" data-t_id="<?php echo $t_id; ?>"><?php echo $tl["tc_name"]; ?></option>
											<?php
										}
								}?>
							</select>
							<!--
							2016-7-3 - BUG LIST說目前只有一種，不要有向下箭頭
							<i class="fa fa-angle-down" aria-hidden="true"></i>
							-->
						</label>
					</div>
					<div class="block fillBlank">
						<div class="bTitle">
							乘坐日期
						</div>
						<label class="blank">
							<input type="text" id="take_date" name="take_date" placeholder="乘坐日期" maxlength="20" />
						</label>
					</div>
				</div>
				<div class="row">
					<div class="block fillBlank">
						<div class="bTitle">
							出發地
						</div>
						<label class="blank bTicketNum">
							<select id="start_area" name="start_area">
								<?php
									if(!empty($area_list)){
										foreach ($area_list as $al){
											?>
								<option value="<?php echo $al["aml_id"]; ?>"<?php echo $start_area==$al["a_id"] ? " selected='selected'" : ""; ?>><?php echo $al["a_name"]; ?></option>
											<?php
										}
								}?>
							</select>
							<i class="fa fa-angle-down" aria-hidden="true"></i>
						</label>
					</div>
					<div class="block fillBlank">
						<div class="bTitle">
							目的地
						</div>
						<label class="blank">
							<select id="end_area" name="end_area">
								<?php
									if(!empty($area_list)){
										foreach ($area_list as $al){
											?>
								<option value="<?php echo $al["aml_id"]; ?>"<?php echo $end_area==$al["a_id"] ? " selected='selected'" : ""; ?>><?php echo $al["a_name"]; ?></option>
											<?php
										}
								}?>
							</select>
							<i class="fa fa-angle-down" aria-hidden="true"></i>
						</label>
					</div>
				</div>
				<div class="row">
					<div class="block fillBlank">
						<div class="bOrder" id="ticket_adult_price">
							<div class="bTitle">
								成人單價
							</div>
							<div class="bPrice">
								<span class="bCurrency">NTD</span>
								<span class="bpNum">10,000</span>
							</div>
						</div>
						<label class="blank">
							<select id="ticket_adult" name="ticket_adult">
								<?php for ($i = 0; $i <= $adult_count; $i++) { ?>
	                                <option value="<?php echo $i; ?>" <?php echo $ticket_adult == $i ? 'selected="selected"' : ''; ?>><?php echo $i; ?>張</option>
	                            <?php } ?>
							</select>
							<i class="fa fa-angle-down" aria-hidden="true"></i>
						</label>
					</div>
					<div class="block fillBlank">
						<div class="bOrder" id="ticket_child_price">
							<div class="bTitle">
								孩童單價
							</div>
							<div class="bPrice">
								<span class="bCurrency">NTD</span>
								<span class="bpNum">10,000</span>
							</div>
						</div>
						<label class="blank">
							<select id="ticket_child" name="ticket_child">
								<?php for ($i = 0; $i <= $child_count; $i++) { ?>
	                                <option value="<?php echo $i; ?>" <?php echo $ticket_child == $i ? 'selected="selected"' : ''; ?>><?php echo $i; ?>張</option>
	                            <?php } ?>
							</select>
							<i class="fa fa-angle-down" aria-hidden="true"></i>
						</label>
					</div>
				</div>
				<div class="row">
					<div class="block note">
						成人定義(<?=$tt_desc_adult?>)
					</div>
					<div class="block note">
						孩童定義(<?=$tt_desc_child?>)
					</div>
				</div>
				<hr>
				<div class="row">
					<div class="block coupon">
					<?php /*
						<div class="bTitle">
							優惠券代碼
						</div>
						<div class="bCoupon">
							<label class="blank couponBlank">
								<input type="text" name="" maxlength="20" placeholder="" name="coupon" id="coupon" value="<?= $coupon ?>">
							</label>
							<button class="submit">送出</button>
							<a href="/member/coupon/" target="_blank">< 查詢優惠券 ></a>
						</div>
						*/ ?>
					</div>
					<div class="block total">
						<span class="totalText">
							產品總額
						</span>
						<span class="totalCurrency">NTD</span>
						<span class="totalNum" id="ticket_total_price">10,000</span>
					</div>
				</div>
				<div class="btnWrap">
					<button type="button" class="prev">上一步</button>
					<button type="button" class="next">下一步</button>
				</div>
			</div>
		</section>
		<section class="intro">
			<div class="secContainer">
				<input type="checkbox" name="" id="introSwitch" class="checkBox">
				<label for="introSwitch" class="listBlock">
					<span>如何兌換</span>
					<i class="fa fa-angle-down"></i>
				</label>
				<div class="content">
					<div class="wrap">

						<!-- product_desciption -->
						<?foreach ($product_desciption_list as $desciption_data){?>
							<h2><?=$desciption_data['tc_title']?></h2>
						<?if ($desciption_data['tc_photo']!='0'){?>
						<img src="<?=get_config_image_server() . '/photos/' . (is_production() ? 'tickets' : 'tickets_alpha') . '/' . $desciption_data['tc_id']."/".$desciption_data['tc_photo'].".jpg"?>">
						<?}?>
						<div class="text">
							<?=$desciption_data['tc_content']?>
						</div>
						<?}?>
						<!-- product_desciption -->
					</div>
				</div>
			</div>
		</section>
		<section class="rule">
			<div class="secContainer">
				<input type="checkbox" name="" id="noteCBox" class="checkBox">
				<label for="noteCBox" class="listBlock">
					<span>注意事項</span>
					<i class="fa fa-angle-down"></i>
				</label>
				<div class="content">
					<div class="wrap">
						<h2>注意事項</h2>
						<ul class="ruleList">
							<?foreach ($notices_list as $notices_data){?>
							<li>
								<?=$notices_data['tc_title']?>
							</li>
							<?}?>
						</ul>
					</div>
				</div>
			</div>
			<div class="secContainer">
				<input type="checkbox" name="" id="cancelCBox" class="checkBox">
				<label for="cancelCBox" class="listBlock">
					<span>取消規定</span>
					<i class="fa fa-angle-down"></i>
				</label>
				<div class="content">
					<div class="wrap">
						<h2>取消規定</h2>
						<ul class="ruleList">
						<?foreach ($rule_list as $rule_data){?>
							<li>
								<?=$rule_data['tc_title']?>
							</li>
						<?}?>
						</ul>
					</div>
				</div>
				<div class="btnWrap1">
					<button type="button" class="prev">上一步</button>
					<button type="button" class="next">下一步</button>
				</div>
			</div>
		</section>
	</main>
	<footer><? include __DIR__ . "/../../common/footer_new.php"; ?></footer>
	<?php include __DIR__ . '/../../common/ga.php';?>
<script>
var is_login = <?php echo ($is_login) ? 1:0 ?>;
var reset_ticket_price = function (){
	get_ticket_price();
}

var get_ticket_price = function (){
	var type_id = $('#tree_type :selected').data("type");
	var t_id = $('#tree_type :selected').data("t_id");
	var start_area = $('#start_area :selected').val();
	var end_area = $('#end_area :selected').val();
	if( start_area==end_area ){
		alert("起訖站不能相同，請重新選擇！");
	} else {
		if(type_id!="" && t_id!="" && start_area!="" && end_area!=""){
			var ret = {};
			var p = {};
	        p.func = 'find_ticket_price';
	        p.type_id = type_id;
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
	//             	console.log('return');
	//             	console.log(data);
	            	if(data.code == '9999'){
	    				ret = "FAIL";
	                	reset_ticket_price();
	                    alert(data.msg);
	                } else {
	    				ret = "OK";
	                	ticket_total_price(data.data);
	                    //alert("取回資料：" + data.data);
	                }
	            }
	        });
	//         console.log(ret);
	        return ret;
		}
	}
}


// 合計票價
var ticket_total_price = function(price){
	var adult_price = 0;
	var child_price = 0;
	$.each( price, function( key, value ) {
		if(value.tt_type==1){
			adult_price = value.sell_price;
			$('#ticket_adult_price .bpNum').html(formatNumber(adult_price));
		}else if(value.tt_type==2){
			child_price = value.sell_price;
			$('#ticket_child_price .bpNum').html(formatNumber(child_price));
		}
	});
	var adult_number = $('#ticket_adult :selected').val();
	var child_number = $('#ticket_child :selected').val();
	var ticket_total = (parseInt(adult_price) * parseInt(adult_number)) + (parseInt(child_price) * parseInt(child_number));
	$('#ticket_total_price').html(formatNumber(ticket_total));
}

var check_data = function(){
	var msg = '';
	var adult_count = $('#ticket_adult :selected').val();
	var child_count = $('#ticket_child :selected').val();

	// 檢查出發地與目的地
	if(get_ticket_price()=="FAIL"){
		$('#start_area').focus();
		return;
	}

	if (parseInt(adult_count)+parseInt(child_count) < 1) {
		$('#ticket_adult').focus();
		alert('請輸入票種張數!!');
		return;
	}
	return "OK";
}

$(function () {
	// 搜尋
    var caneldar_option = <?php echo json_encode(Constants::$CALENDAR_OPTIONS); ?>;
    var take_date = new Date();
    $('#take_date').datepicker(caneldar_option).datepicker('option', {minDate: new Date(),  maxDate: "+90D"});

    <?php if(!empty($take_date)){ ?>
    $('#take_date').val('<?php echo $take_date; ?>');
    <?php }else if(!empty($take_date_default) && empty($take_date)){?>
    $('#take_date').val('<?php echo $take_date_default; ?>');
    <?php } ?>

	$('#take_date').on('change', function() {
		take_date_default = new Date('<?php echo $take_date_default; ?>');
		take_date = new Date($('#take_date').val());
		if (take_date.getTime() < take_date_default.getTime()) {
			alert('乘坐日期必需為 <?php echo $take_date_default; ?> 之後！');
			$('#take_date').val('<?php echo $take_date_default; ?>');
		}
	})

	$('#tree_type').on("change", function(){
		get_ticket_price();
	});
	$('#start_area').on("change", function(){
		get_ticket_price();
	});
	$('#end_area').on("change", function(){
		get_ticket_price();
	});
	$('#ticket_adult').on("change", function(){
		get_ticket_price();
	});
	$('#ticket_child').on("change", function(){
		get_ticket_price();
	});

	// 返回上一頁(交通首頁)
	$('.hsr-payStep-container .prev').on("click", function(){
		location.href = "/transport/";
	});

	$('.hsr-payStep-container .next').on("click", function(){
        var data = '<?php echo $header_is_login; ?>';
	    if(data == 1){
	    if(check_data()=="OK"){
		    		var url = "/web/pages/bookingcar/check_data.php?type=5";
		    		url += "&ticket_id=" + $('#ticket_id').val();
		    		url += "&tree_type=" + $('#tree_type :selected').val();
		    		url += "&take_date=" + $('#take_date').val();
		    		url += "&start_area=" + $('#start_area :selected').val();
		    		url += "&end_area=" + $('#end_area :selected').val();
		    		url += "&ticket_adult=" + $('#ticket_adult :selected').val();
		    		url += "&ticket_child=" + $('#ticket_child :selected').val();
		    		url += "&coupon=" + $('#coupon').val();
		    		location.href = url;
	        }
	    }else{
	        	show_popup_login();
	    }
	});

	// 將頁面資料歸零
	reset_ticket_price();
	<?php if(!empty($start_area) && !empty($end_area) && ($ticket_adult > 0 || $ticket_child > 0)){ ?>
	// 計算初始值
	get_ticket_price();
	<?php } ?>
});
</script>
</body>
</html>