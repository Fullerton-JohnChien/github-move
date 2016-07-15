<?
require_once __DIR__ . '/../../config.php';
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<? include __DIR__ . "/../common/head_new.php"; ?>
	<title>tripitta 旅必達 會員中心 - 我的訂單</title>
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<link rel="stylesheet" href="/web/css/member.css">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="/web/js/jquery.twbsPagination.js" type="text/javascript"></script>
</head>
<body>
<?
if(empty($lang)) {
    $lang = 'tw';
}
$query_type = get_val('qt');
$pageno = get_num('pageno');
$desc_title = "全部明細";
if('all' == $query_type) {
    $desc_title = '全部明細';
} else if ('not_check_in' == $query_type) {
    $desc_title = '未入住明細';
} else if ('check_in' == $query_type) {
    $desc_title = '已入住明細';
} else if ('cancel' == $query_type) {
    $desc_title = '取消明細';
}
if($pageno <= 0) {
    $pageno = 1;
}
$total_page = 0;
$page_size = 5;
$total_items = 0;

$tripitta_service = new tripitta_service();
$tripitta_web_service = new tripitta_web_service();
$homestay_service = new Home_stay_service();
$homestay_promotion_service = new Home_stay_promotion_service();

$login_user_data = $tripitta_web_service->check_login();

$order_list = array();
if(!empty($login_user_data)) {
// $login_user_data["serialId"] = 1094890;
    // $data["user_id"] : 查詢特定會員訂單
    // $data["buyer_data"] : 篩選特定購買人資料, 搜尋購買人姓名、Email、手機
    // $data["begin_date"] : 篩選訂單建立日期大餘特定日期之資料資料
    // $data["end_date"] : 篩選訂單建立日期小餘特定日期之資料資料
    // $data["check_in_status"] : 篩選入住狀態 (0:全部 1:未入住 2:已入住)
    // $data["cancel_status"] : 篩選取消狀態 0:全部(default) 1:未取消 2:已取消
    $data = array();
    $data["user_id"] = $login_user_data["serialId"];
    if('cancel' == $query_type) {
        $data["cancel_status"] = 2;
    } else {
        if('not_check_in' == $query_type) {
            $data["cancel_status"] = 1;
            $data["check_in_status"] = 1;
        } else if('check_in' == $query_type) {
            $data["cancel_status"] = 1;
            $data["check_in_status"] = 2;
        }
    }
//     writeLog('query_order_cond : ' . json_encode($data, JSON_UNESCAPED_UNICODE));

    // 原 odc 訂單，奇怪明就做分頁還全部的訂單都撈出來是怎樣？ john 2016-07-03
    $ret = $tripitta_web_service->query_odc_order($data);
    if($ret["code"] == "0000") {
        $api_ret = $ret["data"];
        if($api_ret["code"] == "0000") {
            $order_list = $api_ret["data"];
            $total_items = count($order_list);
        }
    }

    /**
     * 新tripitta訂單將直接寫入到travel資料庫hf_order
     * john 2016-07-03
     */

    // 直接讀取hf_order的訂單資料
//     $total_items = $tripitta_service->count_home_stay_order_by_user_id($login_user_data["serialId"]);
// printmsg($total_items);
	$page_order_list = $tripitta_service->find_home_stay_order_by_user_id($login_user_data["serialId"], $page_size, (($pageno - 1) * $page_size));

    // 再依o_id比對odc的訂單，取出odc的訂單編號
    foreach ($page_order_list as $idx => $page_item) {
    	foreach ($order_list as $item) {
			if ($item['order.homestay'][0]['oh_partner_trans_id'] == $page_item['o_id']) {
				$page_item['od_id'] = $item['od_id'];
				$page_item['od_transaction_id'] = $item['od_transaction_id'];
				$page_order_list[$idx] = $page_item;
// printmsg($item['od_transaction_id']);
				break;
			}
    	}
    }
// printmsg($page_order_list);

    //writeLog('query_order_cond : ' . json_encode($order_list, JSON_UNESCAPED_UNICODE));
    $total_page = getTotalPage($total_items, $page_size);
    if($total_page <= 0) {
        $total_page = 1;
    }
}

if($pageno > $total_page) {
    $pageno = $total_page;
}
// printmsg(count($order_list));
?>
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="recoAll-container">
		<h1 class="title"><?= $desc_title ?></h1>
		<div class="tile">
			<? include 'order_function_menu.php'; ?>
			<article>
				<div class="wrapper">
<?
if($total_items == 0) {
    echo '<div style="text-align:center"><a href="/booking/">您目前還沒有訂過房喔，來逛逛我們的旅宿吧</a></div>';
}
else {
    $seek = ($pageno - 1) * $page_size;
    for($i = $seek ; $i < $seek + $page_size ; $i++) {
        if($i >= count($order_list)) {
            break;
        }
        $order_row = $order_list[$i];
        $order_id = $order_row["od_id"];
        $store_id = $order_row["od_store_id"];
        $order_homesty_row = $order_row["order.homestay"][0];
        $order_line_homestay_list = $order_homesty_row["order.lines"];
        $days = (strtotime($order_homesty_row["oh_check_out_date"]) - strtotime($order_homesty_row["oh_check_in_date"])) / 86400;

        // 取得旅宿、房型資料
        $homestay_row = $homestay_service->get_hotel_by_id($lang, $store_id);
        $room_type_list = $homestay_service->get_room_types_by_hotel_id($lang, array($store_id));
        $image_server_url = get_config_image_server();
        $homestay_image_url = '/web/img/no-pic.jpg';
        if(!empty($homestay_row["hs_main_photo"])) {
            $homestay_image_url = $image_server_url . "/photos/travel/home_stay/" . $store_id . '/' . $homestay_row["hs_main_photo"] . '_middle.jpg';
        }
        $order_cancel_status = $order_row["od_cancel_status"];

        $over_cancel_date = 0;
        $is_allow_cancel = 1;
        if($order_homesty_row["oh_allow_cancel"] == 2){
            $is_allow_cancel = 0;
        }
        if(strtotime($order_homesty_row["oh_check_in_date"]) < strtotime(date('Y-m-d'))) {
            $over_cancel_date = 1;
        }
?>
					<section>
						<div class="overlayCancel" style="display: <?= $order_cancel_status == 1 ? "block" : "none" ?>"><span>此筆訂單已取消！</span></div>
						<div class="primary">
							<img src="<?= $homestay_image_url ?>" alt="">
							<div class="orderWrap">
								<h2>
									訂單編號
									<span class="orderNo"><?= $order_row["od_transaction_id"] ?></span>
								</h2>
								<h1 class="orderHotel"><?= $order_homesty_row["oh_store_name"] ?></h1>
							</div>
							<div class="dateWrap">
								<h2>
									訂購日期
									<span class="dateOrder"><?= date('Y.m.d', strtotime($order_homesty_row["oh_create_time"])) ?></span>
								</h2>
								<h2>
									入住日期
									<span class="dateCheckIn"><?= date('Y.m.d', strtotime($order_homesty_row["oh_check_in_date"])) ?></span>
								</h2>
								<h2>
									退房日期
									<span class="dateCheckOut"><?= date('Y.m.d', strtotime($order_homesty_row["oh_check_out_date"])) ?></span>
								</h2>
							</div>
							<div class="days">
								<span class="day"><?= $days ?></span>晚住宿
							</div>
						</div>
						<div class="secondary" data-order-id="<?= $order_id ?>">
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
            $cancel_info = "免費取消期限<span>" . $order_homesty_row["oh_last_cancel_date"] . "</span>	前";

            $promotion_info = "";
            if($promotion_config_id > 0) {
                if($promotion_type == 1) {
                    $promotion_row = $homestay_promotion_service->get_promotion_by_promotion_config_id($promotion_config_id);
                    $promotion_info = $promotion_row["p_name"];
                }
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
                    $unit_info = ($order_line_homestay_row["olh_product_unit"] == 1 ? "間" : "床");
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
							</div>

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
										<p class="fee"><?= ($order_row["od_coupon_discount"] == 0) ? "0" : "-" . number_format($order_row["od_coupon_discount"]) ?></p>
									</h4>
									<? if($order_cancel_status == 1) { ?>
									<h4>
										<p>作業處理費</p>
										<p>NTD</p>
										<p class="fee"><?= ($order_row["od_fee"] == 0) ? "0" : "-" . number_format($order_row["od_fee"]) ?></p>
									</h4>
									<h4>
										<p>取消扣款金額</p>
										<p>NTD</p>
										<p class="cancelFee"><?= ($order_row["od_cancel_fee"] == 0) ? "0" : "-" . number_format($order_row["od_cancel_fee"]) ?></p>
									</h4>
									<? } ?>
								</div>
								<div class="cancelTotal">
									<? if($order_cancel_status == 1) { ?>
									<h4>
										<p>應刷退金額</p>
										<p>NTD</p>
										<p class="total"><?= number_format($order_row["od_bank_refund_money"]) ?></p>
									</h4>
									<? } ?>
								</div>
							</div>
							<? if($order_row["od_cancel_status"] == 0) { ?>
							<div class="totalWrap">
								<h4>
									<p>總價</p>
									<p>NTD</p>
									<p class="total"><?= number_format($order_row["od_bank_trans_money"] - $order_row["od_bank_refund_money"]) ?></p>
								</h4>
							</div>
							<? } ?>
						</div>

						<div class="btnWrap">
							<? if($is_allow_cancel == 1) { ?>
							<div class="cancelAllow">
								<a href="javascript:void(0)" class="btnCheckIn" data-order-id="<?= $order_id ?>">入住憑證</a>
								<? if($over_cancel_date == 0) { ?>
								<a href="javascript:void(0)" class="btnCancel" data-order-id="<?= $order_id ?>" data-trans-id="<?= $order_row["od_transaction_id"] ?>">取消訂單</a>
								<? } ?>
							</div>
							<? } else { ?>
							<div class="cancelNotAllow" style="display:block">
								<a href="javascript:void(0)" class="btnCheckIn" data-order-id="<?= $order_id ?>">入住憑證</a>
								<a class="btnNotAllow">不可取消</a>
							</div>
							<? } ?>
						</div>
						<div class="angle">
							<i class="fa fa-angle-down" data-order-id="<?= $order_id ?>"></i>
						</div>
					</section>
<?
    }
}
?>
					<ul id="pagination" class="pagination"></ul>
					<div class="text-center">
						<ul id="visible-pages-example" class="pagination"></ul>
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
var query_type = '<?= $query_type ?>';
var total_page = <?= $total_page ?>;
$(function() {
	$('.fa-angle-down').each(function () {
		$(this).click(function() { show_order_detail($(this).attr('data-order-id')); });
	});

	$('.recoAll-container .fa-angle-down').each(function () {
		$(this).click(function() { show_order_detail($(this).attr('data-order-id')); });
	});
	$('.recoAll-container .btnCheckIn').each(function () {
		$(this).click(function() {
			var order_id =  $(this).attr('data-order-id');
			window.open('/web/pages/booking/check_in_certificate.php?order_id=' + order_id, '_blank');
		});
	});
	$('.recoAll-container .btnCancel').each(function () {
		$(this).click(function() {
			var order_id =  $(this).attr('data-order-id');
			location.href = '/web/pages/member/order_cancel.php?order_id=' + order_id;
		});
	});

	<?php if($total_items > 0){ ?>
	    $('#visible-pages-example').twbsPagination({
    		totalPages: <?= $total_page ?>,
    		startPage: <?= $pageno ?>,
    	    first: "第一頁",
    	    prev: "上一頁",
    	    next: "下一頁",
    	    last: "最後一頁",
    		initiateStartPageClick:false,
    		onPageClick: function (event, page) {
    			var url = '/member/invoice/';
    			url += (query_type == 'all') ? '':query_type + '/';
    			url += '?pageno=' + page;
    			location.href = url;
    			// console.log(url);
    		    // $('#page-content').text('Page ' + page);
    		}
	    });
    <?php } ?>
});

function query_order() {
	var p = {};
    p.func = 'logout';
    console.log(p);
	$.post("/web/ajax/ajax.php", p, function(data) {
		console.log(data);
        if(data.code == '9999'){
            alert(data.msg);
        } else {
            // 先暫時做頁面reload，視狀況自行調整
            // location.href = '/web/';
            location.reload();
        }
    }, 'json').done(function() { }).fail(function() { }).always(function() { });
}

function show_order_detail(order_id) {
	console.log(order_id);
	$('.recoAll-container .secondary').each(function() {
		if($(this).attr('data-order-id') == order_id) {
			$(this).show(200);
		} else{
			$(this).hide(200);
		}
	});
}
</script>