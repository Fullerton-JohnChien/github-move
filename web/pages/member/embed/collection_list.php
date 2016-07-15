<?php
/**
 * 說明：會員中心 - 我的收藏
 * 作者：Casper <casper.lee@fullerton.com.tw>
 * 日期：2016年5月19日
 * 備註：
 */
require_once __DIR__ . '/../../../config.php';

// 頁面基本資料
define('MAX_DESCRIPTION_LENGTH', 42);
$lang = 'tw';
$pageSize = 5;
$type = get_val('type');
$type_name = null;
$is_login = false;
$tripitta_web_service = new tripitta_web_service();
$login_user_data = $tripitta_web_service->check_login();
if(!empty($login_user_data)) {
	$is_login = true;
}else{
	exit;
}
include_once '../../common/member_header.php';

switch ($type) {
	default :
	// 美食
	case 'food' :
		$type = 'food';
		$type_name = '美食';
		break;
	// 景點
	case 'scenic' :
		$type_name = '景點';
		break;
	// 旅宿
	case 'homestay' :
		$type_name = '旅宿';
		break;
	// 伴手禮
	case 'gift' :
		$type_name = '伴手禮';
		break;
	// 活動
	case 'event' :
		$type_name = '活動';
		break;
	// 行程遊記
	case 'travel_plan' :
		$type_name = '行程遊記';
		break;
	// 主題企劃
	case 'topic_plan' :
		$type_name = '主題企劃';
		break;
	// 交通
	case 'transport':
		$type_name = '交通';
		break;
}
$pageno = get_val ( 'pageno' );
if (empty ( $pageno )) {
	$pageno = 1;
}

// 取得分類內容資料
$tripitta_service = new tripitta_service ();
$tripitta_web_service = new tripitta_web_service ();
$login_user_data = $tripitta_web_service->check_login ();
$user_id = 0;
if (! empty ( $login_user_data )) {
	$user_id = $login_user_data ["serialId"];
}
$user_favorite_type_ids = $tripitta_service->get_favorite_type_ids ( $type );
// $user_favorite_type_ids = $tripitta_web_service->get_user_favorite_type_ids ( $type );
$total_items = $tripitta_web_service->count_user_favorite ( 'tw', $user_id, $user_favorite_type_ids );
$total_page = getTotalPage ( $total_items, $pageSize );
if ($pageno > $total_page && $total_page > 0) {
	$pageno = $total_page;
}
$limit = $pageSize;
$offset = ($pageno - 1) * $pageSize;
$item_list = $tripitta_service->find_valid_by_user_id_and_type_ids( 'tw', $user_id, $user_favorite_type_ids, $limit, $offset );
// $item_list = $tripitta_web_service->list_user_favorite ( 'tw', $user_id, $user_favorite_type_ids, $limit, $offset );
?>
<script type="text/javascript">
var is_login = <?= ($is_login) ? 1:0 ?>;
$(function () {
	// computer 項目功能
	$('li[id^="collection_"]').on("click", function(){
		var item = $(this).data('item');
		select_collection(item);
	});
	// mobile 項目功能
	$('.dList-m #select_collection').on("change", function(){
		var item = $('#select_collection :selected').val();
		select_collection(item);
	});
	// 收藏移除
	$('.myLovest .fa-heart').on("click", function(){
		var r = confirm("確定要刪除此 筆收藏嗎?");
		if(r == true){
			var type = $(this).data('type');
			var id = $(this).data('id');
			remove_member_favorite(type, id);
			location.href="/<?php echo $member_path; ?>/collection/<?php echo $type; ?>/";
		}
	});
});

remove_member_favorite = function(ref_type, ref_id) {
	if(!is_login) {
		show_popup_login();
		return;
	}
	remove_items = [];
    remove_items.push({'type_id':ref_type,'ref_id':ref_id});

	var p = {};
    p.func = 'remove_user_favorite';
    p.user_id = $('#user_serial_id').val();
    p.items = remove_items;
    //console.log(p);
    $.post("/web/ajax/ajax.php", p, function(data) {
        console.log(data);
        if(data.code == '9999'){
            alert(data.msg);
        } else {
            // 顯示註冊完成並顯示註冊完成popup window
			alert('已從我的收藏移除');
        }
    }, 'json').done(function() { }).fail(function() { }).always(function() { });
}
// 顯示項目內容
select_collection = function(e){
	var url = "/<?php echo $member_path; ?>/collection_list/"+e+"/";
	$("#dataBlock").load(url);
}
</script>
<div class="collection">
	<ul class="dList">
		<?php 
/*
	       * 不提供全部收藏
	       * <li class="selected">全部收藏</li>
	       */
		?>
		<li id="collection_food" data-item="food"
			<?php if($type=="food"){ echo ' class="selected"'; } ?>>美食</li>
		<li id="collection_homestay" data-item="homestay"
			<?php if($type=="homestay"){ echo ' class="selected"'; } ?>>住宿</li>
		<li id="collection_scenic" data-item="scenic"
			<?php if($type=="scenic"){ echo ' class="selected"'; } ?>>景點</li>
		<li id="collection_bookingcar" data-item="transport"
			<?php if($type=="transport"){ echo ' class="selected"'; } ?>>交通</li>
		<li id="collection_gift" data-item="gift"
			<?php if($type=="gift"){ echo ' class="selected"'; } ?>>伴手禮</li>
		<li id="collection_event" data-item="event"
			<?php if($type=="event"){ echo ' class="selected"'; } ?>>活動</li>
		<li id="collection_travel_plan" data-item="travel_plan"
			<?php if($type=="travel_plan"){ echo ' class="selected"'; } ?>>行程遊記</li>
		<!-- <li id="collection_topic_plan" data-item="topic_plan"<?php if($type=="topic_plan"){ echo ' class="selected"'; } ?>>主題企劃</li> -->
	</ul>
	<div class="dList-m">
		<div class="sWrap">
			<select id="select_collection">
<!-- 				<option>全部收藏</option> -->
				<option value="food"<?php if($type=="food"){ echo " selected='selected'"; } ?>>美食</option>
				<option value="homestay"<?php if($type=="homestay"){ echo " selected='selected'"; } ?>>住宿</option>
				<option value="scenic"<?php if($type=="scenic"){ echo " selected='selected'"; } ?>>景點</option>
				<option value="transport"<?php if($type=="transport"){ echo " selected='selected'"; } ?>>交通</option>
				<option value="gift"<?php if($type=="gift"){ echo " selected='selected'"; } ?>>伴手禮</option>
				<option value="event"<?php if($type=="event"){ echo " selected='selected'"; } ?>>活動</option>
				<option value="travel_plan"<?php if($type=="travel_plan"){ echo " selected='selected'"; } ?>>行程遊記</option>
			</select> <i class="fa fa-angle-down"></i>
		</div>
		<?php /*
		<div class="sWrap">
			<select>
				<option>北部</option>
				<option>中部</option>
				<option>東部</option>
				<option>西部</option>
			</select> <i class="fa fa-angle-down"></i>
		</div>
		*/ ?>
	</div>
	<?php
	    if('homestay' == $type) {
	        include "collection_list_homestay.php";
	    } else if(in_array($type, array('food', 'scenic', 'gift', 'event'))) {
	        include "collection_list_taiwan_content.php";
	    } else if('travel_plan' == $type) {
	        include "collection_list_travel_plan.php";
	    } else if('topic_plan' == $type) {
	        include "collection_list_topic_plan.php";
	    } else if('transport' == $type) {
	        include "collection_list_transport.php";	    	
	    }
	?>
	
	<!-- pagination -->
	<div class="pagination" id="pagination"></div>
	<input type="hidden" id="user_serial_id" value="<?php echo $login_user_data['serialId']?>"> 
</div>