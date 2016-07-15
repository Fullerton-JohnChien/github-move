<script>
var uri = '<?= $_SERVER["REQUEST_URI"] ?>';
$(function() {
	console.log(uri);
    if($('#member_menu_my_account').length > 0) {
    	$('#member_menu_my_account').click(function() { location.href = '/member/profile/'; });
    }
    if($('#member_menu_personal_data').length > 0) {
    	$('#member_menu_personal_data').click(function() { location.href = '/member/profile/'; });
    }
    if($('#member_menu_update_password').length > 0) {
    	$('#member_menu_update_password').click(function() { location.href = '/member/update_password/'; });
    }
    if($('#member_menu_my_message').length > 0) {
    	$('#member_menu_my_message').click(function() { location.href = '/member/message/'; });
    }
    if($('#member_menu_my_coupon').length > 0) {
    	$('#member_menu_my_coupon').click(function() { location.href = '/member/coupon/'; });
    }
    if($('#member_menu_my_collection').length > 0) {
    	$('#member_menu_my_collection').click(function() { location.href = '/member/collection/'; });
    }
    if($('#member_menu_my_travel_plan').length > 0) {
    	$('#member_menu_my_travel_plan').click(function() { location.href = '/member/trip/'; });
    }

    if(uri == '/member/profile/' || uri == '/member/profile_edit/') {
    	$('#member_menu_my_account').addClass('active')
    	$('#member_menu_personal_data').addClass('active').show();
    	$('#member_menu_update_password').show();
    }
    if(uri == '/member/update_password/') {
    	$('#member_menu_personal_data').show();
    	$('#member_menu_update_password').addClass('active').show();
    }
    if(uri == '/member/coupon/') {
    	$('#member_menu_my_coupon').addClass('active');
    }
    if(uri == '/member/message/') {
    	$('#member_menu_my_message').addClass('active');
    }
    if(uri == '/member/collection/' || uri == '/member/collection/food/' || uri == '/member/collection/scenic/' || uri == '/member/collection/homestay/' || uri == '/member/collection/gift/' || uri == '/member/collection/event/' || uri == '/member/collection/travel_plan/') {
    	$('#member_menu_my_collection').addClass('active');
    }
});
</script>
			<aside>
				<dl>
					<!--<dt id="member_menu_my_account" onclick="javascript:location.href='/member/profile/'">帳號管理</dt>-->
					<dd id="member_menu_personal_data" style="display: none">個人資料</dd>
					<dd id="member_menu_update_password" style="display: none">更改密碼</dd>
				<dl>
					<dt id="member_menu_my_message">訊息通知</dt>
				</dl>
				<dl>
					<dt id="member_menu_my_coupon" onclick="javascript:location.href='/member/coupon/'">優惠劵查詢</dt>
				</dl>
				<dl>
					<dt id="member_menu_my_collection" onclick="javascript:location.href='/member/collection/'">我的收藏</dt>
				</dl>
				<!--
				<dl>
					<dt id="member_menu_my_travel_plan">我的行程</dt>
				</dl>
				 -->
			</aside>
