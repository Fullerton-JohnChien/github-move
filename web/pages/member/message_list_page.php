<?
require_once __DIR__ . '/../../config.php';
$ezding_user_service = EzdingUserUtil::get_ezding_user_util();
$user_data = $ezding_user_service->checkLogin(false);;
$user_serial_id = $user_data["serialId"];
$nickname = $user_data["nickname"];
$message_list = $cache->get('cache_key_message_list_' . $user_serial_id);
if(empty($message_list)) {

    //$ret = $ezding_user_service->add_persoanl_message($user_serial_id, $nickname, 2, 1, $user_serial_id, "這是試Title", "這是測試message 內容" . time());
    $ret = $ezding_user_service->get_my_message_list($user_serial_id);
    $message_list = array();
    if($ret["status"] == 1) {
        $message_list = array_merge($ret["msg"]["site"], $ret["msg"]["personal"]);
    }
    $cache->set('cache_key_message_list_' . $user_serial_id, $message_list);
}
$pageno = get_val('pageno');
if(empty($pageno)) {
    $pageno = 1;
}
$total_items = count($message_list);
$pageSize = 5;
$total_page = getTotalPage($total_items, $pageSize);
if($pageno > $total_page && $total_page > 0){
    $pageno = $total_page;
}
$offset = ($pageno - 1) * $pageSize;
$now = time();
?>
					<h3 class="sortBar">
						<div class="sortWrap">
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
    $row = $message_list[$i];
    if($i == $offset) {
        //printmsg($row);
    }
    if($row["mm_message_type"] == 1) {
        // 站台訊息
        $title = $row["sm_title"];
        $message = $row["sm_message"];
        $create_time = $row["sm_create_time"];
        if($row["mm_status"] == 0){
            $site_ids[] = $row["mm_message_id"];
        }
    } else {
        // 個人訊息
        $title = $row["pm_title"];
        $message = $row["pm_message"];
        $create_time = $row["pm_create_time"];
        if($row["mm_status"] == 0){
            $personal_ids[] = $row["mm_message_id"];
        }
    }
    $sec_diff = $now - strtotime($create_time);
    if($sec_diff > 86400) {
        $desc_time_before = floor($sec_diff / 86400) . "天";
    } else if($sec_diff < 3600) {
        $desc_time_before = floor($sec_diff / 60) . "分";
    } else if($sec_diff < 60){
        $desc_time_before = $sec_diff . "秒";
    } else {
        $desc_time_before = floor($sec_diff) . "小時";
    }
?>

						<section>
							<h1><?= $title ?></h1>
							<div class="subContent"><?= $message ?></div>
							<div class="ago">
								<i class="img-member-clock"></i>
								<span class="time"><?= $desc_time_before ?>前</span>
							</div>
						</section>
<?
}

if(!empty($site_ids)) {
    $ezding_user_service->update_site_message_status($user_serial_id, $site_ids, 1);
}
if(!empty($personal_ids)) {
    $ezding_user_service->update_my_message_status($user_serial_id, $personal_ids, 1);
}
?>
					</div>
					<div class="text-center">
						<ul id="visible-pages-example" class="pagination"></ul>
    				</div>

<script>
    $(function() {
	    $('#visible-pages-example').twbsPagination({
    		totalPages: <?= $total_page ?>,
    		startPage: <?= $pageno ?>,
    	    first: "第一頁",
    	    prev: "上一頁",
    	    next: "下一頁",
    	    last: "最後一頁",
    		initiateStartPageClick:false,
    		onPageClick: function (event, page) {
    			var url = '/member/message/?pageno=' + page;
    			//location.href = url;
    			// console.log(url);
    		    // $('#page-content').text('Page ' + page);
    		    toPage(page);
    		}
	    });
    });
 </script>
