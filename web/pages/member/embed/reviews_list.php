<?php
/**
 * 說明：會員中心 - 我的點評
 * 作者：Casper <casper.lee@fullerton.com.tw>
 * 日期：2016年5月31日
 * 備註：
 */
require_once __DIR__ . '/../../../config.php';

// 頁面基本資料
define('MAX_DESCRIPTION_LENGTH', 42);
// $lang = 'tw';
$pageSize = 5;
$type = get_val('type');
$type_name = null;
$item_list = array();
$is_login = false;

// 取得分類內容資料
$tripitta_service = new tripitta_service();
$tripitta_web_service = new tripitta_web_service();
$login_user_data = $tripitta_web_service->check_login(); 
if(!empty($login_user_data)) {
	$is_login = true;
}else{
	exit;
}
include_once '../../common/member_header.php';
$user_id = 0;
if (! empty ( $login_user_data )) {
	$user_id = $login_user_data ["serialId"];
}
switch($type){
	default:
	// 旅宿
	case 'homestay':
		$type = 'homestay';
		$item_list = $tripitta_service->find_user_homestay_reviews_by_user_id($user_id, $type);
		$type_name = '旅宿';		
		break;
	// 車行(交通)
	case 'transport':
		$type_name = '車行';	
		$item_list = $tripitta_service->find_user_car_reviews_by_user_id($user_id, 'car');	
		break;
}
$pageno = get_val ( 'pageno' );
if (empty ( $pageno )) {
	$pageno = 1;
}


?>
<script type="text/javascript">
var is_login = <?= ($is_login) ? 1:0 ?>;
$(function () {
	// computer 項目功能
	$('li[id^="reviews_"]').on("click", function(){
		var item = $(this).data('item');
		select_collection(item);
	});
	// mobile 項目功能
	$('.dList-m #select_reviews').on("change", function(){
		var item = $('#select_reviews :selected').val();
		select_collection(item);
	});
});

//顯示項目內容
select_collection = function(e){
	var url = "/<?php echo $member_path; ?>/reviews_list/"+e+"/";
	$("#dataBlock").load(url);
}
</script>
<div class="collection">
	<ul class="dList">
		<?php /* 2016/05/31 目前尚無的規格先隱藏
		<li class="selected">全部點評</li>
		<li>美食</li>
		 */ ?>
		<li id="reviews_homestay" data-item="homestay"<?php if($type=="homestay"){ echo ' class="selected"'; } ?>>旅宿</li>
		<?php /* 2016/05/31 目前尚無的規格先隱藏
		<li>景點</li>
		*/ ?>
		<li id="reviews_transport" data-item="transport"<?php if($type=="transport"){ echo ' class="selected"'; } ?>>車行</li>
	</ul>
	<div class="dList-m">
		<div class="sWrap">
			<select id="select_reviews">
				<?php /* 2016/05/31 目前尚無的規格先隱藏
				<option>全部點評</option>
				<option>美食</option>
				 */ ?>
				<option value="homestay"<?php if($type=="homestay"){ echo " selected='selected'"; } ?>>旅宿</option>
				<?php /* 2016/05/31 目前尚無的規格先隱藏
				<option>景點</option>
				 */ ?>
				<option value="transport"<?php if($type=="transport"){ echo " selected='selected'"; } ?>>車行</option>
			</select>
			<i class="fa fa-angle-down"></i>
		</div>
		<?php /* 2016/05/31 目前尚無的規格先隱藏
		<div class="sWrap">			
			<select>
				<option>北部</option>
				<option>中部</option>
				<option>東部</option>
				<option>西部</option>
			</select>
			<i class="fa fa-angle-down"></i>
		</div>
		*/ ?>
	</div>
	<?php
	    if('homestay' == $type) {
	        include "reviews_list_homestay.php";
	    } else if('transport' == $type) {
	        include "reviews_list_transport.php";	    	
	    }
	?>
		
	<!-- pagination -->
	<div class="pagination" id="pagination"></div>
	<input type="hidden" id="user_serial_id" value="<?php echo $login_user_data['serialId']?>"> 
</div>
<script type="text/javascript">
	$(function(){
		$('.rcontent').each(function() {
			var tempHeight = $( this ).outerHeight( true );
			if( $( this ).height() > 36 ){
				$( this ).height(36);
				$( this ).siblings(".readMore").html('<a href="javascript:void(0)" class="readMoreBtn">展開</a>');
				$( this ).siblings(".readMore").data("height", tempHeight);
			}
		});
		$( ".readMore" ).click(function() {
			var times = $( this ).data("times");
			$( this ).html('<a href="javascript:void(0)" class="readMoreBtn">展開</a>');
			if( times % 2 == 1 ){
				$( this ).siblings(".rcontent").animate({
					height: "36px"
					}, 500);
			}
			else{
				$( this ).html('<a href="javascript:void(0)" class="readMoreBtn">收起</a>');
				// console.log($( this ).data("height"));
				$( this ).siblings(".rcontent").animate({
					height: $( this ).data("height")
					}, 500);
			}
			times += 1;
			$( this ).data("times", times);
		});
	});
</script>