$(function(){
	$('#myOrders .ride-qrcode').on("click", function(){
		var server = $(this).data('server');
		var type = $(this).data('type');
		var code = $(this).data('id');
		var url = encodeURIComponent(server + '/web/pages/bookingcar/pay/pay_succ.php?type=' + type + '&order_id=' + code);
		var qrcode_url = "https://chart.googleapis.com/chart?cht=qr&chl=" + url + "&chs=120x120&choe=UTF-8";
		$('.popupQR .wrap img').attr("src", qrcode_url);
		$('.popupQR').show();
		$('.overlay').show();
	});

	// 關閉乘車憑證
	$('.popupQR .closeBtn').on("click", function(){
		$('.popupQR').hide();
		$('.overlay').hide();
	});
	
	$('.order .order-back').on("click", function(){
		var type = $(this).data('type');
		var deviceType = $(this).data('devicetype');
		var id_name = "#myOrders-m";
		if(deviceType=="computer"){
			id_name = "#myOrders";
		}
		var url = '';
		switch(parseInt(type)){
			case 1:
				if(deviceType=='computer'){
					url = "orders-charter.php";
				}else{
					url = "orders-charter-m.php";						
				}
				break;
			case 2:
			case 4:
				if(deviceType=="computer"){
					url = "orders-pickup.php";
				}else{
					url = "orders-pickup-m.php";						
				}
				break;
			case 3:
				if(deviceType=="computer"){
					url = "orders-tourbus.php";
				}else{
					url = "orders-tourbus-m.php";						
				}
				break;
			case 5:
				if(deviceType=="computer"){
					url = "orders-hsr.php";
				}else{
					url = "orders-hsr-m.php";						
				}
				break;	
		}
		var redirect_url = "/web/pages/member/embed/orders/" + url;
		$( id_name ).load( redirect_url );
	});
	
	$('.member-insurance-container .order-back').on("click", function(){
		var type = $(this).data('type');
		var deviceType = $(this).data('devicetype');
		var id_name = "#myOrders-m";
		if (deviceType=="computer") {
			id_name = "#myOrders";
		}
		var url = '';
		switch (parseInt(type)) {
			case 1:
				if (deviceType=='computer') {
					url = "orders-charter.php";
				} else {
					url = "orders-charter-m.php";						
				}
				break;
			case 2:
			case 4:
				if (deviceType=="computer") {
					url = "orders-pickup.php";
				} else {
					url = "orders-pickup-m.php";						
				}
				break;
			case 3:
				if (deviceType=="computer") {
					url = "orders-tourbus.php";
				} else {
					url = "orders-tourbus-m.php";						
				}
				break;
			case 5:
				if (deviceType=="computer") {
					url = "orders-hsr.php";
				} else {
					url = "orders-hsr-m.php";						
				}
				break;	
		}
		var redirect_url = "/web/pages/member/embed/orders/" + url;
		$( id_name ).load( redirect_url );
	});
	
	$('#myOrders .order-contactus').on("click", function() {
		var id = $(this).data('id');
		var name = $(this).data('name');
		var date = $(this).data('date');
		var getoff = $(this).data('getoff');
		var days = $(this).data('days');
		var people = $(this).data('people');
		$('.popupCar #co_id').val(id);
		$('.popupCar #partner').html(name);
		$('.popupCar #begin_date').val(date);
		$('.popupCar #end_area').val(getoff);
		$('.popupCar #car_day').val(days);
		$('.popupCar #car_people').val(people);
		$('.popupCar').show();
		$('.overlay').show();
	});
	
	$('#myOrders-m .order-contactus').on("click", function() {
		var id = $(this).data('id');
		var name = $(this).data('name');
		var date = $(this).data('date');
		var getoff = $(this).data('getoff');
		var days = $(this).data('days');
		var people = $(this).data('people');
		$('.popupCar #co_id').val(id);
		$('.popupCar #partner').html(name);
		$('.popupCar #begin_date').val(date);
		$('.popupCar #end_area').val(getoff);
		$('.popupCar #car_day').val(days);
		$('.popupCar #car_people').val(people);
		$('.popupCar').show();
		$('.overlay').show();
	});
	
	// 關閉聯繫業者
	$('.popupCar .closeBtn').on("click", function() {
		$('.popupCar').hide();
		$('.overlay').hide();
	});
	
	$('#myOrders .modify').on("click", function() {
		var order_id = $(this).data('orderid');
		var deviceType = $(this).data('devicetype');
		var id_name = "#myOrders-m";
		if (deviceType == "computer") {
			id_name = "#myOrders";
		}
		var redirect_url = "/web/pages/member/embed/insurance.php?order_id="+order_id;
		$( id_name ).load( redirect_url );
	});
	
	$('#myOrders-m .modify').on("click", function() {
		var order_id = $(this).data('orderid');
		var deviceType = $(this).data('devicetype');
		var id_name = "#myOrders-m";
		if (deviceType == "computer") {
			id_name = "#myOrders";
		}
		var redirect_url = "/web/pages/member/embed/insurance.php?order_id="+order_id;
		$( id_name ).load( redirect_url );
	});
	
	// 開啟修改訂單
	$('.member-insurance-container .members .member .btn').on("click", function() {
		var id = $(this).data('id');
		var name = $(this).data('name');
		var birthday = $(this).data('birthday');
		var country = $(this).data('country');
		var identity = $(this).data('identity');
		$('.popupConnect #copi_id').val(id);
		$('.popupConnect #copi_name').val(name);
		$('.popupConnect #copi_birthday').val(birthday);
		$('.popupConnect #copi_country_id').val(country);
		$('.popupConnect #copi_identity_number').val(identity);
		$('.popupConnect').show();
		$('.overlay').show();
	});
	
	// 關閉修改訂單
	$('.popupConnect .closeBtn').on("click", function() {
		$('.popupConnect').hide();
		$('.overlay').hide();
	});
	
	// 確定修改訂單
	$('.popupConnect .blk .btn').on("click", function() {
		var deviceType = $("#devicetype").val();
		var order_id = $("#order_id").val();
		var copi_id = $("#copi_id").val();
		var copi_name = $("#copi_name").val();
		var copi_birthday = $("#copi_birthday").val();
		var copi_country_id = $("#copi_country_id").val();
		var copi_identity_number = $("#copi_identity_number").val();
		$.getJSON('/web/ajax/ajax.php',
			{func: 'modify_passenger', 'copi_id': copi_id, 'copi_name': copi_name, 'copi_birthday': copi_birthday, 'copi_country_id': copi_country_id, 'copi_identity_number': copi_identity_number},
			function(data) {
				if (data.code == "0000") {
					alert("修改此筆乘客資訊成功!");
					$('.popupConnect').hide();
					$('.overlay').hide();
					var id_name = "#myOrders-m";
					if (deviceType == "computer") {
						id_name = "#myOrders";
					}
					var redirect_url = "/web/pages/member/embed/insurance.php?order_id="+order_id;
					$( id_name ).load( redirect_url );
				} else if (data.code == "9999") {
					alert(data.msg);
					$('.popupConnect').hide();
					$('.overlay').hide();
				}
			}
		);
	});
	
	$('#myOrders .notification').on("click", function() {
		var order_id = $(this).data('id');
		var deviceType = $(this).data('devicetype');
		var id_name = "#myOrders-m";
		if (deviceType == "computer") {
			id_name = "#myOrders";
		}
		var redirect_url = "/web/pages/member/embed/messenger.php?type=order&order_id="+order_id;
		$( id_name ).load( redirect_url );
	});
});

function order_info(deviceType, type, id) {
	var url = '';
	var id_name = "#myOrders-m";
	//var id_name = "#dataBlock";
	switch(type){
		case '1':
			url = "orderinfo-charter.php";
			break;
		case '2':
		case '4':
			url = "orderinfo-pickup.php";
			break;
		case '3':
			url = "orderinfo-tourbus.php";
			break;
		case '5':
			url = "orderinfo-hsr.php";
			break;	
			
	}	
	if (deviceType == "computer") {
		id_name = "#myOrders";
	}
	$( id_name ).load( "/web/pages/member/embed/orderinfo/" + url + "?order_id=" +id );
	$(".listBlock").hide();
}

function cancel(deviceType, type, id) {
	var url = '';
	var id_name = "#myOrders-m";
	switch(type){
		case '1':
			url = "cancel-charter.php";
			break;
		case '2':
		case '4':
			url = "cancel-pickup.php";
			break;
		case '3':
			url = "cancel-tourbus.php";
			break;
		case '5':
			url = "cancel-hsr.php";
			break;	
			
	}	
	if (deviceType == "computer") {
		id_name = "#myOrders";
	}
	var redirect_url = "/web/pages/member/embed/cancel/" + url + "?order_id=" +id;
	$( id_name ).load( redirect_url );
}

function show_title() {
	$(".title").show();
}

var order_search_results = function(num){
	if(typeof(num) == "undefined" || num==''){
		num = 0;
	}
	$('.orders .searchDetail .result span').html(num);
}

function sendOrderNotify(proof_photo,to_id){
	if (proof_photo=='0'){
		alert("尚未產生憑證檔!");
	} else {
		//ajax------------------------------------------------------------------------
		$.get('/web/ajax/ajax.php', {func: "send_proof_email", to_id: to_id},
            function (data) {
    			var obj = jQuery.parseJSON(data);
    			if(obj.code=='0000'){
	    			alert("請稍候，已重寄憑證Email!");
    			}
            }, "text"
		);
		//ajax--------------------------------------------------------------------------
	}
}