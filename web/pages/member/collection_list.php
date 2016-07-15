<?
/**
 * 說明：
 * 作者：cheans <cheans.huang@fullerton.com.tw>
 * 日期：2015年11月4日
 * 備註：
 *
 * 參數說明 :
 * $type : 格式 integer
 * 美食 : food[7] 參考 travel.hf_taiwan_content where tc_type = 7
 * 景點 : scenic[8] 參考 travel.hf_taiwan_content where tc_type = 8
 * 住宿 : homestay[10] 參考 travel.hf_home_stay where tc_type = 10
 * 伴手禮 : gift[82] 參考 travel.hf_taiwan_content where tc_type = 82
 * 活動(娛樂、節慶活動) : event[12, 15] 參考 travel.hf_taiwan_content where tc_type in (12, 15)
 * 行程遊記 : travel_plan[5] 參考 travel.hf_travel_plan
 * 主題企劃 : topic_plan[6] 參考 travel.hf_topic_plan
 *
 */
require_once __DIR__ . '/../../config.php';
define('MAX_DESCRIPTION_LENGTH', 35);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<? include __DIR__ . "/../common/head_new.php"; ?>
	<title>tripitta 旅必達 會員中心 - 旅遊收藏</title>
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<link rel="stylesheet" href="/web/css/member.css">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="/web/js/jquery.twbsPagination.js" type="text/javascript"></script>


</head>
<body>
<?
$type = get_val('type');
$pageno = get_val('pageno');
if(empty($type)) {
    $type = 'food';
}
if(empty($pageno)) {
    $pageno = 1;
}
$lang = 'tw';
$pageSize = 5;

$tripitta_web_service = new tripitta_web_service();
$login_user_data = $tripitta_web_service->check_login();
$user_id = 0;
if(!empty($login_user_data)) {
    $user_id = $login_user_data["serialId"];
}
$user_favorite_type_ids = $tripitta_web_service->get_user_favorite_type_ids($type);
$total_items = $tripitta_web_service->count_user_favorite('tw', $user_id, $user_favorite_type_ids);
$total_page = getTotalPage($total_items, $pageSize);
if($pageno > $total_page && $total_page > 0){
    $pageno = $total_page;
}
$limit = $pageSize;
$offset = ($pageno - 1) * $pageSize;
$item_list = $tripitta_web_service->list_user_favorite('tw', $user_id, $user_favorite_type_ids, $limit, $offset);
?>


	<header><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="traCollection-container">
		<h1 class="title">我的收藏</h1>
		<div class="tile">
			<? include __DIR__ . '/member_function_menu.php' ?>
			<article>
				<div class="wrapper">
					<ul class="nav">
						<li><a href="/member/collection/food/">美食</a></li>
						<li><a href="/member/collection/scenic/">景點</a></li>
						<li><a href="/member/collection/homestay/">住宿</a></li>
						<li><a href="/member/collection/gift/">伴手禮</a></li>
						<li><a href="/member/collection/event/">活動</a></li>
						<li><a href="/member/collection/travel_plan/">行程遊記</a></li>
						<!-- <li><a href="/member/collection/topic_plan/">主題企劃</a></li> -->
					</ul>
					<h3 class="delBar">
						<div class="delWrap" title="刪除">
							<span>全選刪除</span>
							<i class="fa fa-trash-o fa-2"></i>
						</div>
						<div class="counterWrap">
							共<span class="counter"><?= $total_items ?></span>筆
						</div>
					</h3>
					<div class="dataList">
<?
    if('homestay' == $type) {
        include "collection_list_homestay.php";
    } else if(in_array($type, array('food', 'scenic', 'gift', 'event'))) {
        include "collection_list_taiwan_content.php";
    } else if('travel_plan' == $type) {
        include "collection_list_travel_plan.php";
    } else if('topic_plan' == $type) {
        include "collection_list_topic_plan.php";
    }
?>
					</div>
					<div class="text-center">
						<ul id="visible-pages-example" class="pagination"></ul>
    				</div>
				</div>
			</article>

		</div>
		<div class="popupCollect">
			<div class="closeBtn">
				<i class="img-member-close"></i>
			</div>
			<h1>刪除收藏</h1>
			<h2>
				確定要刪除此
				<!-- 這邊可以設計無論點全刪或單刪，都用這個popup去秀出訊息，如果單筆刪除訊息，則count內的值都去掉，設計完此說明可刪 -->
				<span class="count">
					頁全部
					<i id="cnt_del">5</i>
				</span>筆收藏嗎?
			</h2>
			<div class="btnWrap">
				<input type="reset" value="取消" class="reset">
				<input type="button" value="確定刪除" class="submit">
			</div>
		</div>

	</div>
	<footer><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
	<input type="hidden" id="user_serial_id" value="<?php echo $login_user_data['serialId']?>">
</body>
</html>
<script>
	var total_page = <?= $total_page ?>;
	var collection_type = '<?= $type ?>';
	var remove_items = [];
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
        			var url = '/member/collection/' + collection_type + '/?pageno=' + page;
        			location.href = url;
        			// console.log(url);
        		    // $('#page-content').text('Page ' + page);
        		}
    	    });
        }
	    $('.dataList .del').each(function(){
		    $(this).click(function(){ remove_from_my_favorite($(this).attr('data-type'), $(this).attr('data-id')); });
	    });

	    $('.traCollection-container .delWrap').click(function(){ remove_page_items(); });
	    $('.popupCollect .reset').click( function() { $('.popupCollect').hide(); $('.overlay').hide(); });
	    $('.popupCollect .img-member-close').click( function() { $('.popupCollect').hide(); $('.overlay').hide(); });
	    $('.popupCollect .submit').click( function() { send_remove_request(); });
    });
    function remove_from_my_favorite(data_type, data_id){
        remove_items = [];
        if(data_id != undefined) {
        	remove_items.push({'type_id':data_type,'ref_id':data_id});
            $('.popupCollect .count').hide();
        } else {
        	remove_id = 0;
        }
        $('.overlay').show();
        $('.popupCollect').show();
    }
    function remove_page_items() {
    	remove_items = [];
    	$('.dataList .del').each(function(){
		    var data_id = $(this).attr('data-id');
		    var data_type = $(this).attr('data-type');
		    remove_items.push({'type_id':data_type,'ref_id':data_id});
	    });
	    if(remove_items.length > 0) {
		    $('#cnt_del').html(remove_items.length);
	    	$('.overlay').show();
	    	$('.popupCollect').show();
	    }
    }
	function send_remove_request() {

		var p = {};
	    p.func = 'remove_user_favorite';
	    p.user_id = $('#user_serial_id').val();
	    p.items = remove_items;
	    console.log(p);
	    $.post("/web/ajax/ajax.php", p, function(data) {
		    console.log(data);
	        if(data.code != '0000'){
	            alert(data.msg);
	        } else {
	            location.reload();
	        }
	    }, 'json').done(function() { }).fail(function() { }).always(function() { });

	}
    </script>