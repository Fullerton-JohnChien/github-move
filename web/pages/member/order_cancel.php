<?
require_once __DIR__ . '/../../config.php';
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<? include __DIR__ . "/../common/head_new.php"; ?>
	<title>tripitta 旅必達 會員中心 - 我的訂單 - 取消訂單</title>
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<link rel="stylesheet" href="/web/css/member.css">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="/web/js/jquery.twbsPagination.js" type="text/javascript"></script>
</head>
<body>
<?
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
$login_user_data = $tripitta_web_service->check_login();
$login_user_id = 0;
if(!empty($login_user_data)) {
    $login_user_id = $login_user_data["serialId"];
}

$lang = $order_row["ode_language"];
$order_user_id = $order_row["om_user_id"];
if(!get_val('is_dev')) {
    if($login_user_id != $order_user_id) {
        gotourl('/', true);
    }
}
$order_id = $order_row["od_id"];
$store_id = $order_row["od_store_id"];
$order_homestay_row = $order_row["order.homestay"][0];
$order_line_homestay_list = $order_homestay_row["order.lines"];
$days = (strtotime($order_homestay_row["oh_check_out_date"]) - strtotime($order_homestay_row["oh_check_in_date"])) / 86400;
// 取得旅宿、房型資料
$homestay_row = $homestay_service->get_hotel_by_id($lang, $store_id);
$image_server_url = get_config_image_server();
$homestay_image_url = '/web/img/no-pic.jpg';
if(!empty($homestay_row["hs_main_photo"])) {
    $homestay_image_url = $image_server_url . "/photos/travel/home_stay/" . $store_id . '/' . $homestay_row["hs_main_photo"] . '_middle.jpg';
}

$room_type_list = $homestay_service->get_room_types_by_hotel_id($lang, array($store_id));

$home_stay_rule_row = $tripitta_homestay_service -> get_home_stay_rule($store_id);

$over_cancel_date = 0;
if(strtotime($order_homestay_row["oh_last_cancel_date"]) < strtotime(date('Y-m-d'))) {
    $over_cancel_date = 1;
}
?>
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="orderCancel-container">
		<h1 class="title"><?= ($order_row["od_cancel_status"] == 0) ? '取消訂單' : '取消完成' ?></h1>
		<div class="tile">
			<? include 'order_function_menu.php'; ?>
			<article>
				<div class="wrapper">
					<? if($order_row["od_cancel_status"] == 1){ ?><p class="cancelSuccInfo" style="display:block"><span>您已完成取消訂房</span>，您取消訂房的詳細資料如下：</p><? } ?>
					<section>
						<div class="primary">
							<img src="<?= $homestay_image_url ?>" alt="">
							<div class="orderWrap">
								<h2>
									訂單編號
									<span class="orderNo"><?= $order_row["od_transaction_id"] ?></span>
								</h2>
								<h1 class="orderHotel"><?= $order_homestay_row["oh_store_name"] ?></h1>
							</div>
							<div class="dateWrap">
								<h2>
									訂購日期
									<span class="dateOrder"><?= date('Y.m.d', strtotime($order_homestay_row["oh_create_time"])) ?></span>
								</h2>
								<h2>
									入住日期
									<span class="dateCheckIn"><?= date('Y.m.d', strtotime($order_homestay_row["oh_check_in_date"])) ?></span>
								</h2>
								<h2>
									退房日期
									<span class="dateCheckOut"><?= date('Y.m.d', strtotime($order_homestay_row["oh_check_out_date"])) ?></span>
								</h2>
							</div>
							<div class="days">
								<span class="day"><?= $days ?></span>晚住宿
							</div>
						</div>
						<div class="secondary">
							<div class="border">
<?
    $room_type_ids = [];
    $room_type_promotions = [];
    foreach($order_line_homestay_list as $order_line_homestay_row) {
        $room_type_id = $order_line_homestay_row["olh_product_id"];
        $promotion_type = $order_line_homestay_row["olh_promotion_type"];
        $promotion_config_id = $order_line_homestay_row["olh_promotion_config_id"];
        if(!in_array($room_type_id, $room_type_ids)) {
            $room_type_ids[] = $room_type_id;
        }
        $t_key = $room_type_id . '_' . $promotion_type . '_' . $promotion_config_id;
        if(!in_array($t_key, $room_type_promotions)) {
            $room_type_promotions[] = $t_key;
        }
    }

    foreach($room_type_promotions as $room_type_promotion) {
        $t = preg_split('/_/', $room_type_promotion);
        $room_type_id = $t[0];
        $promotion_type = $t[1];
        $promotion_config_id = $t[2];
        $room_type_name = "";
        $have_breakfast = 0;
        foreach($order_line_homestay_list as $order_line_homestay_row) {
            if($order_line_homestay_row["olh_product_id"] == $room_type_id && empty($room_type_name)) {
                $room_type_name = $order_line_homestay_row["olh_product_name"];
            }
            if($order_line_homestay_row["olh_breakfast"] == 1 && $have_breakfast == 0) {
                $have_breakfast = 1;
            }
        }

        $room_type_row = null;
        foreach($room_type_list as $t) {
            if($t["rt_id"] == $room_type_id) {
                $room_type_row = $t;
                break;
            }
        }

        $room_type_image_url = '/web/img/no-pic.jpg';
        if(!empty($room_type_row["rt_main_photo"])) {
            $room_type_image_url = $image_server_url . "/photos/travel/room_type/" . $room_type_id . '/' . $room_type_row["rt_main_photo"] . '_middle.jpg';
        }
        $breakfast_info = ($have_breakfast == 1) ? "含早餐" : "不含早餐";
        $cancel_info = "免費取消期限<span>" . $order_homestay_row["oh_last_cancel_date"] . "</span>	前";
        $is_allow_cancel = 1;
        if($order_homestay_row["oh_allow_cancel"] == 2){
            $cancel_info = "不可取消";
            $is_allow_cancel = 0;
        }

        $promotion_info = "";
        if($promotion_config_id > 0) {
            if($promotion_type == 1) {
                $promotion_row = $homestay_promotion_service->get_promotion_by_promotion_config_id($promotion_config_id);
                $promotion_info = $promotion_row["p_name"];
            }
        }
        if($order_row["od_cancel_status"] == 0) {
            $cancel_fee = 0;
            $total_refund = 0;
            if($is_allow_cancel) {
                if($over_cancel_date == 0) {
                    $cancel_fee = 0;
                    foreach($order_line_homestay_list as $order_line_homestay_row) {
                        $total_refund += $order_line_homestay_row["olh_bank_trans_money"];
                    }
                } else {
                    foreach($order_line_homestay_list as $order_line_homestay_row) {
                        if($order_homestay_row["oh_check_in_date"] != $order_line_homestay_row["olh_date"]) {
                            $total_refund += $order_line_homestay_row["olh_bank_trans_money"] ;
                        } else {
                            $cancel_fee += $order_line_homestay_row["olh_bank_trans_money"];
                        }
                    }
                }
                if($order_row["od_coupon_discount"] > 0) {
                    $total_refund -= $order_row["od_coupon_discount"];
                    if($cancel_fee > 0) {
                        $is_allow_cancel = 0;
                    }
                }
                if($total_refund < 0) {
                    $total_refund = 0;
                }
            }
        } else {
            $cancel_fee = $order_row["od_cancel_fee"];
            $total_refund = $order_row["od_bank_refund_money"];

        }
?>
								<section>
									<img src="<?= $room_type_image_url ?>" alt="">
									<div class="roomWrap">
										<h1><?= $room_type_name ?></h1>
										<div class="group">
											<div class="deadline">
												<h2><?= $promotion_info ?></h2>
												<p>
													<span class="breakfast"><?= $breakfast_info ?></span>
													<span class="freeDeadline"><?= $cancel_info ?></span>
												</p>
											</div>
											<div class="room">
<?
        foreach($order_line_homestay_list as $order_line_homestay_row) {
            if($room_type_id == $order_line_homestay_row["olh_product_id"]
                    && $promotion_type == $order_line_homestay_row["olh_promotion_type"]
                    && $promotion_config_id == $order_line_homestay_row["olh_promotion_config_id"]) {

                $room_date = date('Y.m.d', strtotime($order_line_homestay_row["olh_date"]));
                $sell_price = $order_line_homestay_row["olh_sell_price"];
                $unit_info = $order_line_homestay_row["olh_product_unit"] == 1 ? "間" : "床";
?>

												<h3>
													<p>
														<span class="date"><?= $room_date ?></span>
													</p>
													<p>
														<span class="count">1</span><?= $unit_info ?>
													</p>
													<p>
														NTD<span class="cost"><?= number_format($sell_price) ?></span>
													</p>
												</h3>
<?
            }
        }
?>

											</div>
										</div>
									</div>
								</section>
<?
    }
?>
							<!-- total payment -->
							<div class="cancelWrap">
								<div class="cancelDetail">
									<h4>
										<p>已付金額</p>
										<p>NTD</p>
										<p class="hasPaid"><?= number_format($order_row["od_bank_trans_money"]) ?></p>
									</h4>
									<h4>
										<p>Coupon折抵</p>
										<p>NTD</p>
										<p class="cancelFee"><?= number_format($order_row["od_coupon_discount"]) ?></p>
									</h4>
									<h4>
										<p>作業處理費</p>
										<p>NTD</p>
										<p class="fee">0</p>
									</h4>
									<h4>
										<p>取消扣款金額</p>
										<p>NTD</p>
										<p class="cancelFee"><?= number_format($cancel_fee) ?></p>
									</h4>
								</div>
								<div class="cancelTotal">
									<h4>
										<p>應刷退金額</p>
										<p>NTD</p>
										<p class="total"><?= number_format($total_refund) ?></p>
									</h4>
								</div>
							</div>
						</div>
					</section>
					<div class="btnWrap">
						<? if($order_row["od_cancel_status"] == 0) { ?>
							<? if($is_allow_cancel) { ?>
						<a href="javascript:cancel_order(<?= $order_id ?>)" class="submit">確定取消</a>
						<h3>確定取消後，將刷退至原付款之信用卡帳戶</h3>
							<? } else { ?>
						<h3>此訂單不充許取消</h3>
							<? } ?>
						<? } else { ?>
						<a href="/member/invoice/" class="submit">回訂單管理</a>
						<? } ?>
					</div>
					<div class="note">
						<h1>取消訂房須知：</h1>
						<div class="content">
							<h3>
								<i class="fa fa-star"></i><p>客戶若欲取消訂房，相關依<span style="color:blue"> <?= $order_homestay_row["oh_store_name"] ?></span>取消規定。如下所示：</p>
							</h3>
							<h3>
								<i class="fa fa-star"></i><p>本民宿之取消訂房之相關扣款費用計算規範如下：</p>
							</h3>
							<h4>
								<i class="fa fa-dot-circle-o"></i><p><b>旅客於住宿日前 <span style="color:red"><?= $date1 = $home_stay_rule_row['lastCancelDays'] +1 ?></span> 日前不含入住日(<span style="color:blue"><?= date('Y/m/d', strtotime($order_homestay_row["oh_check_in_date"] . ' -' . $date1 . ' days')) ?></span>)取消訂房，全額退回。</b></p>
							</h4>
							<h4>
								<i class="fa fa-dot-circle-o"></i><p> 旅客於住宿日前<span style="color:red"> <?= $date2 = $home_stay_rule_row['lastCancelDays'] ?></span>日內(<span style="color:blue"><?= date('Y/m/d', strtotime($order_homestay_row["oh_check_in_date"] . ' -' . $date2 . ' days')) ?></span>)取消訂房，扣<span style="color:red">第一晚房價</span>，為取消訂房手續費。</p>
							</h4>
							<h4>
								<i class="fa fa-dot-circle-o"></i><p>旅客於住宿 <span style="color:red">當日</span>(<span style="color:blue"><?= date('Y/m/d', strtotime($order_homestay_row["oh_check_in_date"])) ?></span>) 將無法取消訂房，當日未入住恕無法退款。</p>
							</h4>
							<h3>
								<i class="fa fa-star"></i><p>本民宿於連續假期、特殊節慶，大型展會期或民宿所在區之旺季期間訂房，恕不接受取消訂房之要求，請務必特別注意，以確保您的權益。</p>
							</h3>
							<h3>
								<i class="fa fa-star"></i><p>如住宿之所在地 或 旅客出發地，於入住當日因天災(主要機關發佈如颱風地震產生相關重大影響等)或其他重大災害（人力不可抗力因素）影響，或有主要交通中斷情形發生，<span style="color:blue"> 致使無法如期入住時，敬請於發佈訊息後<span style="color:red">3</span>日內，儘速與本網客服人員連繫，更改或取消您的訂房</span>，本網將會依各民宿住房取消、更改規定辦理相關手續。</p>
							</h3>
						</div>
					</div>
				</div>
			</article>
		</div>
	</div>
	<footer class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
	<input type="hidden" id="user_serial_id" value="<?php echo $login_user_data['serialId']?>">
</body>
</html>
<script>
function cancel_order (order_id) {
	var p = {};
    p.func = 'cancel_order';
    p.order_id = order_id;
    p.user_id = $('#user_serial_id').val();
    console.log(p);
	$.post("/web/ajax/ajax.php", p, function(data) {
		console.log(data);
        if(data.code != '0000'){
            location.href = 'order_cancel_fail.php?msg=' + encodeURIComponent(data.msg);
        } else {
            // 先暫時做頁面reload，視狀況自行調整
            // location.href = '/web/';
            location.reload();
        }
    }, 'json').done(function() { }).fail(function() { }).always(function() { });
}
</script>