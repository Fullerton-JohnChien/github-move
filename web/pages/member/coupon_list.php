<?
/**
 * 說明：優惠券查詢
 * 作者：cheans <cheans.huang@fullerton.com.tw>
 * 日期：2015年11月7日
 * 備註：
 * 使用資料表
 * hf_coupon : Coupon主檔
 * hf_coupon_batch : Coupon批次檔
 *
 */
require_once __DIR__ . '/../../config.php';
// printmsg(sendmail($mail_from, 'cheans.huang@fullerton.com.tw', 'tripitta test mail', date('Y-m-d H:i:s')));
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<? include __DIR__ . "/../common/head_new.php"; ?>
	<title>Tripitta 旅必達 - 會員中心 - 優惠券查詢</title>
	<link rel="stylesheet" href="/web/css/coupon.css">
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<link rel="stylesheet" href="/web/css/member.css">
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
	<script src="/web/js/jquery.twbsPagination.js" type="text/javascript"></script>
</head>
<body>
<?
$tripitta_web_service = new tripitta_web_service();
$login_user_data = $tripitta_web_service->check_login();

$user_serial_id = $login_user_data["serialId"];
$coupon_dao = Dao_loader::__get_coupon_dao();
$coupon_list = $coupon_dao->getCouponByUser($user_serial_id);
$pageno = get_val('pageno');
if(empty($pageno)) {
    $pageno = 1;
}
$total_items = count($coupon_list);
$pageSize = 3;
$total_page = getTotalPage($total_items, $pageSize);
if($pageno > $total_page && $total_page > 0){
    $pageno = $total_page;
}
$offset = ($pageno - 1) * $pageSize;
$now = time();
?>
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
    <div class="coupon-container">
        <h1 class="title">優惠券查詢</h1>
        <div class="tile">
            <? include __DIR__ . '/member_function_menu.php' ?>
            <article>
                <div class="wrapper">
                    <h3 class="sortBar">
                        <div class="sortWrap">
                          <span>全部</span>
                          <i class="fa fa-angle-down fa-2"></i>
                        </div>
                        <div class="counterWrap">
                          共<span class="counter"><?= $total_items ?></span>筆
                        </div>
                    </h3>
                    <div class="content">
                        <h2>票券狀態說明</h2>
                        <ul>
                            <li>「可兌換」：尚未兌換票券、可直接兌換使用</li>
                            <li>「已兌換」：已兌換之票券</li>
                            <li>「已過期」：已超過使用期限之票券</li>
                        </ul>
                        <div class="dataList">
<?
for($i =$offset ; $i < $offset + $pageSize ; $i++) {
    if($i >= $total_items) {
        break;
    }
    $row = $coupon_list[$i];
    $coupon_id = $row["c_id"];
    $batch_name = $row["cb_batch_name"];
    $coupon_number = $row["c_number"];
    $launched_time = $row["c_launched_time"];
    $expited_time = $row["c_expired_time"];
    $discount_type = $row["cb_type"]; // 1:折扣, 2:折價
    $percent = $row["cb_percent"];
    $price = $row["cb_price"];
    $class = "";
    if(strtotime($row["c_expired_time"]) < strtotime(date('Y-m-d'))
            || $row["c_status"] == 2
            ) {
        $class = "coupon--disabled";
    }

    $l_y = date('Y', strtotime($launched_time));
    $l_md = date('m/d', strtotime($launched_time));
    $e_y = date('Y', strtotime($expited_time));
    $e_md = date('m/d', strtotime($expited_time));
?>
                            <section class="<?= $class ?>">
                                <div class="couponHead">
                                    <h1>
<?
    if($discount_type == 1) {
?>
                                        <div class="coin"><?= $percent ?></div>
                                        <div class="info">
                                          <span class="discountType">折</span>
                                        </div>
<?
    } else {
?>
                                        <div class="info">
                                          <span class="discountType">面額</span>
                                          <span class="currency">NTD</span>
                                        </div>
                                        <div class="coin"><?= $price ?></div>
<?
    }
?>
                                    </h1>
                                    <h2>優惠代碼<div class="column"><?= $coupon_number ?></div></h2>
                                </div>
                                <div class="coupon-period">
                                    <div class="period__start">
                                        <div class="period__year">
                                            <?= $l_y ?>
                                        </div>
                                        <div class="start__date">
                                            <?= $l_md ?>
                                        </div>
                                    </div>
                                    <div class="period__divide"> - </div>
                                    <div class="period__end">
                                        <div class="period__year">
                                            <?= $e_y ?>
                                        </div>
                                        <div class="period__date">
                                            <?= $e_md ?>
                                        </div>
                                    </div>
                                </div>
                                <h1 class="couponTitle"><?= $batch_name ?>...</h1>
                                <a href="javascript:show_rule(<?= $coupon_id ?>)" class="btn">活動說明</a>
                            </section>
<?
}
?>
                        </div>
                    </div>
                    <div class="text-center">
						<ul id="visible-pages-example" class="pagination"></ul>
    				</div>
                </div>
            </article>
        </div>
<?
foreach($coupon_list as $row) {
    $coupon_id = $row["c_id"];
    $batch_name = $row["cb_batch_name"];
    $launched_time = $row["c_launched_time"];
    $expited_time = $row["c_expired_time"];
?>
        <div id="rule_<?= $coupon_id ?>" class="popupCollect" >
            <div class="closeBtn">
                <i class="img-member-close" data-id="<?= $coupon_id ?>"></i>
            </div>
            <h1>活動說明</h1>
            <p class="content">活動名稱： <?= $batch_name ?>
                <br> Coupon序號使用期限： <?= date('Y / m / d', strtotime($launched_time)) ?> -  <?= date('Y / m / d', strtotime($expited_time)) ?>
                <br> 使用說明：<br>
                <?= empty($row["cb_terms_of_use"]) ? "" : nl2br($row["cb_terms_of_use"]) ?>
            </p>
        </div>
<?
}
?>
    </div>
	<footer class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
</body>
</html>
<script>
var total_page = '<?= $total_page ?>';
$(function() {
	if(total_page > 0) {
	    $('#visible-pages-example').twbsPagination({
    		totalPages: <?= $total_page ?>,
    		startPage: <?= $pageno ?>,
    	    first: "第一頁",
    	    prev: "上一頁",
    	    next: "下一頁",
    	    last: "最後一頁",
    		initiateStartPageClick:false,
    		onPageClick: function (event, page) {
    			var url = '/member/coupon/?pageno=' + page;
    			location.href = url;
    			// console.log(url);
    		    // $('#page-content').text('Page ' + page);
    		    //toPage(page);
    		}
	    });
	}
	$('.img-member-close').click(function() {
		var coupon_id = $(this).attr('data-id');
		$('#rule_' + coupon_id).hide();
		$('.overlay').hide();
	});
});
function toPage(pageno, initial) {
	var p = {};
    p.pageno = pageno;
    console.log(p);
    $.post("/web/pages/member/coupon_list_page.php", p, function(data) {
    	$('.msgRemind-container .wrapper').html(data);
    	if(initial != true) {
    		scrollTo('.msgRemind-container .wrapper');
    	}
    }, 'html').done(function() { }).fail(function() { }).always(function() { });
}

function show_rule(coupon_id) {
	console.log(coupon_id);
	$('div[id^="rule"]').hide();
	$('.overlay').show();
	$('#rule_' + coupon_id).show();
}
</script>