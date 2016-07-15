<script>
var uri = '<?= substr($_SERVER["REQUEST_URI"], 0, strrpos($_SERVER["REQUEST_URI"], '/') + 1) ?>';
var query_type = '<?= get_val('query_type') ?>';
$(function() {
	console.log(uri);
	$('#my_order_menu_all').click(function() { location.href = '/member/invoice/'; });
	$('#my_order_menu_not_check_in').click(function() { location.href = '/member/invoice/not_check_in/'; });
	$('#my_order_menu_check_in').click(function() { location.href = '/member/invoice/check_in/'; });
	$('#my_order_menu_cancel').click(function() { location.href = '/member/invoice/cancel/'; });
	if(query_type == 'all') {
		$('#my_order_menu_all').addClass('active');
	} else if(query_type == 'not_check_in') {
		$('#my_order_menu_not_check_in').addClass('active');
	} else if(query_type == 'check_in') {
		$('#my_order_menu_check_in').addClass('active');
	} else if(query_type == 'cancel') {
		$('#my_order_menu_cancel').addClass('active');
	}

});
</script>
			<aside>
				<dl>
					<dt class="active">旅宿訂單明細</dt>
					<dd id="my_order_menu_all">全部明細</dd>
					<dd id="my_order_menu_not_check_in">未入住明細</dd>
					<dd id="my_order_menu_check_in">已入住明細</dd>
					<dd id="my_order_menu_cancel">取消明細</dd>
				</dl>
				<dl>
					<dt >包車訂單明細</dt>
				</dl>
				<dl>
					<dt>拼車訂單明細</dt>
				</dl>
			</aside>
