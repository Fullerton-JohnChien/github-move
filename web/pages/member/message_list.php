<?
/**
 * 說明：個人/站台訊息
 * 作者：cheans <cheans.huang@fullerton.com.tw>
 * 日期：2015年11月7日
 * 備註：
 * 頁面訊息實作部份請參考message_list_page.php
 */
require_once __DIR__ . '/../../config.php';
// printmsg(sendmail($mail_from, 'cheans.huang@fullerton.com.tw', 'tripitta test mail', date('Y-m-d H:i:s')));
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<? include __DIR__ . "/../common/head_new.php"; ?>
	<title>Tripitta 旅必達 - 會員中心 - 優惠券查詢</title>
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<link rel="stylesheet" href="/web/css/member.css">
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
	<script src="/web/js/jquery.twbsPagination.js" type="text/javascript"></script>
</head>
<body>
<?
$message_list = $cache->get('cache_key_message_list');
if(empty($message_list)) {
    $ezding_user_service = EzdingUserUtil::get_ezding_user_util();
    $user_data = $ezding_user_service->checkLogin(false);;
    $user_serial_id = $user_data["serialId"];
    $nickname = $user_data["nickname"];
    //$ret = $ezding_user_service->add_persoanl_message($user_serial_id, $nickname, 2, 1, $user_serial_id, "這是試Title", "這是測試message 內容" . time());
    $ret = $ezding_user_service->get_my_message_list($user_serial_id);
    $message_list = array();
    if($ret["status"] == 1) {
        $message_list = array_merge($ret["msg"]["site"], $ret["msg"]["personal"]);
    }
}
if(empty($message_list)) {
    $message_list = array();
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
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="msgRemind-container">
		<h1 class="title">訊息通知</h1>
		<div class="tile">
			<? include __DIR__ . '/member_function_menu.php' ?>
			<article>
				<!-- 資料透過ajax捉取 -->
				<div class="wrapper">
				</div>
			</article>
		</div>
	</div>
	<footer class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
</body>
</html>
<script>
$(function() {
	toPage(1, true);
});
function toPage(pageno, initial) {
	var p = {};
    p.pageno = pageno;
    console.log(p);
    $.post("/web/pages/member/message_list_page.php", p, function(data) {
    	$('.msgRemind-container .wrapper').html(data);
    	if(initial != true) {
    		scrollTo('.msgRemind-container .wrapper');
    	}
    }, 'html').done(function() { }).fail(function() { }).always(function() { });
}
</script>