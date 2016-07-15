<?
require_once __DIR__ . '/../../config.php';
$ezding_user_service = EzdingUserUtil::get_ezding_user_util();
$login_user_data = $ezding_user_service->checkLogin(false);;
$user_serial_id = $login_user_data["serialId"];
$nickname = $login_user_data["nickname"];
// $coupon_list = $cache->get('cache_key_coupon_list_' . $user_serial_id);
// if(empty($coupon_list) && !empty($login_user_data)) {
    $coupon_dao = Dao_loader::__get_coupon_dao();
    $coupon_list = $coupon_dao->getCouponByUser($user_serial_id);
//     $cache->set('cache_key_coupon_list_' . $user_serial_id, $coupon_list);
// }
$pageno = get_val('pageno');
if(empty($pageno)) {
    $pageno = 1;
}
$total_items = count($coupon_list);
$pageSize = 5;
$total_page = getTotalPage($total_items, $pageSize);
if($pageno > $total_page && $total_page > 0){
    $pageno = $total_page;
}
$offset = ($pageno - 1) * $pageSize;
$now = time();
?>
					<h3 class="sortBar">
						<div class="sortWrap" style="visibility: hidden;">
							<span>時間排序</span>
							<i class="fa fa-angle-down fa-2"></i>
						</div>

						<div class="counterWrap">
							共<span class="counter"><?= $total_items ?></span>筆
						</div>
					</h3>
					<div class="content">
<?
$site_ids = array();
$personal_ids = array();
for($i =$offset ; $i < $offset + $pageSize ; $i++) {
    if($i >= $total_items) {
        break;
    }
    $row = $coupon_list[$i];
    $batch_name = $row["cb_batch_name"];
    $coupon_number = $row["c_number"];
    $launched_time = $row["c_launched_time"];
    $expited_time = $row["c_expired_time"];
    $terms_of_use = $row["cb_terms_of_use"];
?>

						<section>
							<h1>Coupon NO : <?= $coupon_number ?></h1>
							<div class="subContent"><?= nl2br($terms_of_use) ?></div>
							<div class="ago">
								<i class="img-member-clock"></i>
								<span class="time">
									<?= substr(date('F', strtotime($launched_time)), 0, 3) . date(' d Y', strtotime($launched_time)) ?> ~
									<?= substr(date('F', strtotime($expited_time)), 0, 3) . date(' d Y', strtotime($expited_time)) ?>
								</span>
							</div>
						</section>
<?
}
?>
					</div>
					<div class="text-center">
						<ul id="visible-pages-example" class="pagination"></ul>
    				</div>

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
        			//location.href = url;
        			// console.log(url);
        		    // $('#page-content').text('Page ' + page);
        		    toPage(page);
        		}
    	    });
    	}
    });
 </script>
