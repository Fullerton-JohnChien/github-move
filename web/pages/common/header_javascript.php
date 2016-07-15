<?php
/**
 * 說明：將 header javascript 區域獨立出來
 * 作者：Casper <casper.lee@fullerton.com.tw>
 * 日期：2016年5月25日
 * 備註：
 */
?>
<?php include_once 'header_javascript_facebook.php'; ?>
<?php include_once 'header_javascript_gplus.php'; ?>
<script type="text/javascript">
var header_is_login = '<?php echo $header_is_login; ?>';
var display_currency_id = '<?php echo $display_currency_id; ?>';
var do_popup_login = <?php echo intval($popup_login); ?>;
var popup_register_account_verify = false;
var popup_register_password_verify = false;

$(function (){

	// 先取得 #cart 及其 top 值
	var $cart = $('#categoryBlk');

	$(window).scroll(function() {
		if($(window).scrollTop() > 100) {
			$(".header-container").hide();
			$(".header-container-fixed").show();
		}else {
			$(".header-container").show();
			$(".header-container-fixed").hide();
		}

		if($(window).scrollTop() >= 234){;
			// 如果 $cart 的座標系統不是 fixed 的話
			if($cart.css('position')!='fixed'){
				// 設定 #cart 的座標系統為 fixed
				$cart.css({
					position: 'fixed',
					top: 44
				});
			}
		}else{
			// 還原 #cart 的座標系統為 absolute
			$cart.css({
				position: 'relative',
				top: 0
			});
		}

		if($(window).scrollTop() >= 44){
			// alert('aa');
			// 如果 $cart 的座標系統不是 fixed 的話
			if($('#categoryBlk2').css('position')!='fixed'){
				// 設定 #cart 的座標系統為 fixed
				$('#categoryBlk2').css({
					position: 'fixed',
					top: 44
				});
			}
		}else{
			// 還原 #cart 的座標系統為 absolute
			$('#categoryBlk2').css({
				position: 'relative',
				top: 0
			});
		}

	});


	// web、mobile換來換去
	$(window).resize(function() {
		if($(window).width() <= <?= $mobile_width ?>) {
			$('#banners').css('height', '');
		}else {
			$('#banners').css('height', '500px');
		}
	});

	// 如果一開始就是手機版...
	if($(window).width() <= <?= $mobile_width ?>) {
		$('#banners').css('height', '');
	} else {
		$('#banners').css('height', '500px');
	}


	// web、mobile換來換去
	$(window).resize(function() {
		if($(window).width() <= <?= $mobile_width ?>) {
			$('.overlay').click(function () {
				$(".menuList-m").removeClass("clicked");
				$(".hSearch-m").hide();
				$('.overlay').hide();
				$('.popupCar').hide();
				$('.popupLogin').hide();
				$('.popupRegister').hide();
				$('.popupSocial').hide();
				$('.popupQuest').hide();
				$(".menuListOTR-m").removeClass("clicked");
				$(".popupList").hide();
				$("#popupSelectList").css('visibility', '');
				$(".popupCampaign").hide();
			});
		}
	});

	// 如果一開始就是手機版...
	if($(window).width() <= <?= $mobile_width ?>) {
		$('.overlay').click(function () {
			$(".menuList-m").removeClass("clicked");
			$(".hSearch-m").hide();
			$('.overlay').hide();
			$('.popupCar').hide();
			$('.popupLogin').hide();
			$('.popupRegister').hide();
			$('.popupSocial').hide();
			$('.popupQuest').hide();
			$(".menuListOTR-m").removeClass("clicked");
			$(".popupList").hide();
			$("#popupSelectList").css('visibility', '');
			$(".popupCampaign").hide();
		});
	}

	$('#close_search').click(function () {
		$(".hSearch-m").hide();
		$('.overlay').hide();
	});

	// Header => Home
	if($('.head .logo').length > 0) {
		$('.head .logo').click(function() { location.href = '/'; });
	}

	if($('#header_btn_member_function').length > 0) {
		$('#header_btn_member_function').click(function() {
			$('#register_is_click').val(1);
			$('.regiGroup .memMenu').toggle();
		});
	}
	// Header -> Login
	if($('#header_btn_login').length > 0) {
		$('#header_btn_login').click(function() { show_popup_login(); });
	}

	if($('#header_btn_login2').length > 0) {
		$('#header_btn_login2').click(function() {
			show_popup_login();
			$(".menuList-m").removeClass("clicked");
		});
	}

	if($('#header_btn_login3').length > 0) {
		$('#header_btn_login3').click(function() {
			show_popup_login();
			$(".menuListOTR-m").removeClass("clicked");
		});
	}

	// Header -> 註冊
	if($('#header_btn_register').length > 0) {
		$('#header_btn_register').click(function () {
			$('#register_is_click').val(1);
    		$('.overlay').show();
    		$('.popupRegister').show();
    		$(window).scrollTop("0");
		});
	}

	// Header -> 註冊
	if($('#header_btn_register2').length > 0) {
		$('#header_btn_register2').click(function () {
			$('#register_is_click').val(1);
    		$('.overlay').show();
    		$('.popupRegister').show();
    		$(window).scrollTop("0");
    		$(".menuList-m").removeClass("clicked");
		});
	}

	// Header -> 註冊
	if($('#header_btn_register3').length > 0) {
		$('#header_btn_register3').click(function () {
			$('#register_is_click').val(1);
    		$('.overlay').show();
    		$('.popupRegister').show();
    		$(window).scrollTop("0");
    		$(".menuListOTR-m").removeClass("clicked");
		});
	}

	// Header -> 登入選單 -> 登出
	if($('#header_btn_logout').length > 0) {
		$('#header_btn_logout').click(function() { logout_user_center(); });
	}

	// Header -> 登入選單 -> 登出
	if($('#header_btn_logout2').length > 0) {
		$('#header_btn_logout2').click(function() { logout_user_center(); });
	}

	// Header -> 登入選單 -> 登出
	if($('#header_btn_logout3').length > 0) {
		$('#header_btn_logout3').click(function() { logout_user_center(); });
	}

	// Header -> 登入選單 -> 登出
	if($('#header_btn_logout4').length > 0) {
		$('#header_btn_logout4').click(function() { logout_user_center(); });
	}

	$('#header_btn_member').click(function () {
		location.href = '/member/';
	});

	$('#header_btn_member2').click(function () {
		location.href = '/member/';
	});

	$('#header_btn_my_order').click(function () {
		location.href = '/member/?item=order';
	});

	$('#header_btn_my_order2').click(function () {
		location.href = '/member/?item=order';
	});

	$('#header_btn_my_message').click(function () {
		location.href = '/member/message/';
	});

	$('#header_btn_my_message2').click(function () {
		location.href = '/member/message/';
	});

	// 依遠到近/近到遠排序
	$('[id^="header_sort"]').on("click", function(){
		var sort = $(this).data("sort");
		chang_header_sort(sort);
		url = location.protocol + "//" + location.host + location.pathname;
		url_search = location.search;
		if(url_search.match(/\?sort\=[0-9]+/)){
			url_search = url_search.replace(/\?sort\=[0-9]+/, '');
		}else if(url_search.match(/\&sort\=[0-9]+/)){
			url_search = url_search.replace(/\&sort\=[0-9]+/, '');
		}
		if(url_search != ""){
			url += url_search + '&sort=' + sort;
		}else{
			url += url_search + '?sort=' + sort;
		}
		location.href = url;
	});

	$('#chgngeMap').click(function () {
		$('#chgngeList').show();
		$('#chgngeMap').hide();
		$('#mapPage').show();
		$('#listPage').hide();
		$('.footer-container').hide();
		map_init();
		var swiper = new Swiper('.swiper-container', {
            pagination: '.swiper-pagination',
            paginationClickable: true
        });
		$(window).scrollTop("0");
	});

	$('#chgngeList').click(function () {
		$('#chgngeMap').show();
		$('#chgngeList').hide();
		$('#mapPage').hide();
		$('#listPage').show();
		$('.footer-container').show();
		$(window).scrollTop("0");
	});

	$('#openMenu').click(function () {
		<?php if(empty($OTR_URI) && empty($traveling_flag)){ ?>
		$(".menuList-m").addClass("clicked");
		<?php }else{ ?>
		$(".menuListOTR-m").addClass("clicked");
		<?php } ?>
		$('.overlay').show();
		$("#popupSelectList").hide();
		$(".hSearch-m").hide();
		$('.popupCar').hide();
		$('.popupLogin').hide();
		$('.popupRegister').hide();
		$('.popupSocial').hide();
		$('.popupQuest').hide();
		$(".popupList").hide();

		$(window).scrollTop("0");
	});

	$('#openMenu2').click(function () {
		$(".menuListOTR-m").addClass("clicked");
		$('.overlay').show();
		$(window).scrollTop("0");
		$("#popupSelectList").hide();
		$(".hSearch-m").hide();
		$('.popupCar').hide();
		$('.popupLogin').hide();
		$('.popupRegister').hide();
		$('.popupSocial').hide();
		$('.popupQuest').hide();
		$(".popupList").hide();
	});

	$('#mobile_search').click(function () {
		$(".hSearch-m").show();
		$('.overlay').show();
		$(window).scrollTop("0");
		$("#popupSelectList").hide();
		$('.popupCar').hide();
		$('.popupLogin').hide();
		$('.popupRegister').hide();
		$('.popupSocial').hide();
		$('.popupQuest').hide();
		$(".popupList").hide();
		$(".menuList-m").removeClass("clicked");
	});

	$('#closeBtn2').click(function () {
		$(".popupLogin1").hide();
		$('.overlay').hide();
		$('.popupRegister').hide();
		$('.popupRegister1').hide();
	});

	$('#closeBtn3').click(function () {
		$(".popupForgetPwd").hide();
		$('.overlay').hide();
		$('.popupRegister').hide();
		$('.popupRegister1').hide();
	});

	// 包車列表
	$('#gotoCharter').click(function () {
		location.href = '/bookingcar/?begin_area=&end_area=&begin_date=<?= date("Y-m-d",strtotime("+1 day")); ?>&car_day=&car_adult=1&car_child=0';
	});

	// 接送機列表
	$('#gotoTickup').click(function () {
		location.href = '/pickup/?begin_area=&end_area=&begin_date=<?= date("Y-m-d",strtotime("+1 day")); ?>&car_adult=0&car_child=0';
	});

	// 觀巴列表
	$('#gotoBus').click(function () {
		location.href = '/tourbus/?begin_area=&end_area=&begin_date=<?= date("Y-m-d",strtotime("+1 day")); ?>';
	});

	// 高鐵列表
	$('#gotoHsr').click(function () {
        $('#go_next_page').val('/hsr/');
        if(header_is_login=='0') {
        	show_popup_login();
        }else{
        	location.href = '/hsr/';
        }
	});
	$('.gotoHsr').click(function () {
        $('#go_next_page').val('/hsr/');
        if(header_is_login=='0') {
        	show_popup_login();
        }else{
        	location.href = '/hsr/';
        }
	});

	$('#menucopy').click(function () {
		$(".popupList").show();
		$(".menuListOTR-m").removeClass("clicked");
		$('.overlay').show();
	});

	// otr 回上一頁
	<?
		$path_array = explode("/", $_SERVER['REQUEST_URI']);
		$path = $path_array[1];
		if(!empty($id)) {
			if ($path == "traveling") {
	?>
	$('#prexPage').click(function () {
		location.href = '/traveling';
	});
	<?
			 } else {
	?>
	$('#prexPage').click(function () {
		location.href = '/vendor/R<?= $id ?>/';
	});
	<?
			 }
		}
	?>

	<? if(!empty($tc_id)) { ?>
	$('#prexPage3').click(function () {
		location.href = '/vendor/otr/<?= $tc_id ?>/?hs_id=<?=$id ?>';
	});
	<? } ?>

	$('#prexPage2').click(function () {
		history.go(-1);
	});

	// Header -> 幣別 -> 異動
	if($('#header_currency').length > 0) {
		$('#header_currency').change(function() {
			if($(this).val() != '0') {
				set_display_currency($(this).val());
			}
		});
		$('#header_currency').val(display_currency_id);
	}

	if($('#header_currency2').length > 0) {
		$('#header_currency2').change(function() {
			if($(this).val() != '0') {
				set_display_currency($(this).val());
			}
		});
		$('#header_currency').val(display_currency_id);
	}

	// 會員登入 -> 註冊
	if($('#popup_login_btn_register').length > 0) {
		$('#popup_login_btn_register').click(function () {
    		$('.popupLogin1').hide();
    		$('.popupRegister').show();
		});
	}

	// 會員登入(登入區域) -> 註冊
	if($('#popup_login_btn_register_login').length > 0) {
		$('#popup_login_btn_register_login').click(function () {
    		$('.popupLogin').hide();
    		$('.popupRegister').show();
		});
	}

	// 會員登入 -> 註冊
	if($('#popup_login_btn_register_1').length > 0) {
		$('#popup_login_btn_register_1').click(function () {
    		$('.popupRegister').hide();
    		$('.popupRegister1').show();
		});
	}

	// 會員登入 -> 關閉
	if($('.popupLogin .img-member-close').length > 0) {
		$('.popupLogin .img-member-close').click(function () {
			if(do_popup_login) {
				alert('本服務須要登入才可使用');
				location.href = '/';
			} else {
    			$('.popupLogin').hide();
    			$('.overlay').hide();
			}
		});
	}
	if($('.popupLogin .closeBtn').length > 0) {
		$('.popupLogin .closeBtn').click(function () {
			if(do_popup_login) {
				alert('本服務須要登入才可使用');
				location.href = '/';
			} else {
    			$('.popupLogin').hide();
    			$('.overlay').hide();
			}
		});
	}
	if($('.popupAuthFail .closeBtn').length > 0) {
		$('.popupAuthFail .closeBtn').click(function () {
			if(do_popup_login) {
				alert('本服務須要登入才可使用');
				location.href = '/';
			} else {
    			$('.popupAuthFail').hide();
    			$('.overlay').hide();
			}
		});
	}
	// 會員登入 -> 忘記密碼
	$('#popup_login_btn_forget_password').click(function() {
		$('.popupLogin1').hide();
		$('.popupForgetPwd').show();
	});
	$('.popupForgetPwd .closeBtn').click(function() {
		if(do_popup_login) {
			alert('本服務須要登入才可使用');
			location.href = '/';
		} else {
			$('.popupForgetPwd').hide();
			$('.overlay').hide();
		}
	});


	// 會員登入 -> 帳號檢查 (不應該存在)
	// $('#popup_login_account').blur(function() { check_user_account_exists('popup_login'); });

	// 會員登入 -> 登入
	$('#popup_login_btn_login').click( function () {
		$('#login_is_click').val(1);
		var auto_login = $('#popup_login_auto_login').prop('checked') ? 1:0;
		var go_next_page = null;
		if ($('#go_next_page') && $('#go_next_page').val()) {
			go_next_page = $('#go_next_page').val();
		}
		login_user_center('<?= constants_user_center::USER_CATEGORY_TRIPITTA ?>', $('#popup_login_account').val(), $('#popup_login_password').val(), auto_login, go_next_page);
	});

	// 會員註冊 -> 關閉
	if($('.popupRegister .img-member-close').length > 0) {
		$('.popupRegister .img-member-close').click(function () {
			if(do_popup_login) {
				alert('本服務須要登入才可使用');
				location.href = '/';
			} else {
    			$('.popupRegister').hide();
    			$('.overlay').hide();
			}
		});
	}
	if($('.popupRegister .closeBtn').length > 0) {
		$('.popupRegister .closeBtn').click(function () {
			if(do_popup_login) {
				alert('本服務須要登入才可使用');
				location.href = '/';
			} else {
    			$('.popupRegister').hide();
    			$('.overlay').hide();
    			$('.popupRegister').hide();
    			$('.popupRegister1').hide();
			}
		});
	}
	if($('.popupRegister1 .closeBtn').length > 0) {
		$('.popupRegister1 .closeBtn').click(function () {
			if(do_popup_login) {
				alert('本服務須要登入才可使用');
				location.href = '/';
			} else {
    			$('.popupRegister1').hide();
    			$('.overlay').hide();
			}
		});
	}
	// 會員註冊 -> 帳號檢查
	$('#popup_register_account').blur(function() { check_user_account_exists('popup_register'); });
	// 會員註冊 -> 密碼檢查
	//$('#popup_register_password').blur(function() { check_register_password('popup_register'); });
	// 會員註冊 -> 密碼確認檢查
	//$('#popup_register_password_confirm').blur(function() { check_register_password('popup_register'); });
	// 會員註冊 -> 註冊
	$('#popup_register_btn_register').click(function() {
		register('popup_register');
	});

	// 會員註冊完成 -> 回頁面
	$('#popup_register_success_btn_back_to_screen').click(function() {
		$('.popupRegiSucc').hide();
		$('.overlay').hide();
	});

	// 忘記密碼 -> 清除資料
	// $('#popup_forget_password_btn_reset_data').click(function () { $('#popup_forget_password_account').val(''); });
	$('#popup_forget_password_btn_reset_data').click(function () {
		$(".popupForgetPwd").hide();
		$('.overlay').hide();
		$('.popupRegister').hide();
		$('.popupRegister1').hide();
	});
	$('#popup_forget_password_btn_contact_us').click(function () { location.href = '/service/mail/'; });
	$('#popup_forget_password_btn_reset_password').click(function () { send_reset_password('popup_forget_password'); });

	// 忘記密碼 -> 重置碼已寄出 popup_reset_pwd
	$('#popup_reset_pwd').click(function() {
		$('.popupResetPwd').hide();
		$('.overlay').hide();
	});


	if(do_popup_login) {
		show_popup_login();
	}

	// 註冊區域-馬上登入
	$('#popup_login_right_now_btn_register').on('click', function(){
		$('.overlay').hide();
		$('.popupRegister').hide();
		show_popup_login();
	});

	// 登入區域-馬上登入
	$('#popup_login_right_now_btn_login').on('click', function(){
		$('.overlay').hide();
		$('.popupLogin').hide();
		show_popup_login_account();
	});

	$(".goBackLogin").on("click", function() {
		$(".popupSended").hide();
		$(".popupLogin").show();
	});
	$(".popupSended .closeBtn").on("click", function() {
		$('.overlay').hide();
		$(".popupSended").hide();
	});
	$("#closeBtn").on("click", function() {
		$('.overlay').hide();
		$(".popupLogin1").hide();
	});
	$(".popupRegister1").find("input[type='text'], input[type='password']").focus(function() {
		$(this).parents(".label").addClass("selected");
		$(this).parents(".label").find(".errMsg").css("background-color", "#fff");
	}).blur(function() {
		$(this).parents(".label").removeClass("selected");
		$(this).parents(".label").find(".errMsg").css("background-color", "#e0e0e0");
	});

});

function check_register_password(from) {
	console.log('check_register_password from:' + from);
	if(from == undefined) {
		return;
	}
	popup_register_password_verify = false;
	var pwd = '', pwd_confirm = '';
	var msg_convas_pwd = '', msg_convas_confirm = '';
	if(from == 'popup_register') {
		pwd = $('#popup_register_password').val();
		pwd_confirm = $('#popup_register_password_confirm').val();
		msg_convas_pwd = 'error_msg_popup_register_password';
		msg_convas_pwd_confirm = 'error_msg_popup_register_password_confirm';
	} else if(from == 'reset_password' || from == 'update_password') {
		pwd = $('#password').val();
		pwd_confirm = $('#password_confirm').val();
		msg_convas_pwd = 'error_msg_password';
		msg_convas_pwd_confirm = 'error_msg_password_confirm';
	}

	var error_msg_pwd = '', error_msg_pwd_confirm = '';

	console.log('pwd:' + pwd);
	if(pwd != '') {
		error_msg_pwd = verify_password(pwd);
		console.log(error_msg_pwd);
		if(pwd_confirm != '') {
			error_msg_pwd_confirm = verify_password(pwd_confirm);
			if(error_msg_pwd_confirm == '' && pwd != pwd_confirm) {
				error_msg_pwd_confirm = '密碼確認錯誤';
			}
		}
	} else {
		error_msg_pwd = '密碼錯誤';
	}
	if(pwd_confirm != '') {
		error_msg_pwd_confirm = verify_password(pwd_confirm);
		if(error_msg_pwd_confirm == '' && pwd != pwd_confirm) {
			error_msg_pwd_confirm = '密碼確認錯誤 - 密碼比對錯誤';
		}
	}else {
		error_msg_pwd_confirm = '密碼確認錯誤';
	}

	if(from == 'popup_register') {
		if(msg_convas_pwd != ''){
			$('#' + msg_convas_pwd).html(error_msg_pwd);
		}
		if(msg_convas_pwd_confirm != '') {
			$('#' + msg_convas_pwd_confirm).html(error_msg_pwd_confirm);
		}
		if(pwd != '' && error_msg_pwd == '' && error_msg_pwd_confirm == '') {
			popup_register_password_verify = true;
		}
	} else {
		if(msg_convas_pwd != ''){
			$('#' + msg_convas_pwd).html(error_msg_pwd);
		}
		if(msg_convas_pwd_confirm != '') {
			$('#' + msg_convas_pwd_confirm).html(error_msg_pwd_confirm);
		}
		if(pwd != '' && error_msg_pwd == '' && error_msg_pwd_confirm == '') {
			popup_register_password_verify = true;
		}
	}
}

// 檢查會員帳號是否存在
function check_user_account_exists(from) {
	if(from == undefined) {
		return;
	}
	var p = {};
	var msg_convas = '';
    p.func = 'check_user_account_exists';
    if(from == 'popup_login') {
    	p.account = $('#popup_login_account').val();
    	msg_convas = 'error_msg_popup_login_account';
    }else if(from == 'popup_register') {
    	p.account = $('#popup_register_account').val();
    	msg_convas = 'error_msg_popup_register_account';
		if (!verifyEmailAddress($('#popup_register_account').val())) {
			//$('#popup_register_account').focus();
			$('#' + msg_convas).html('請檢查E-mail格式是否正確!!');
			return false;
		}
    }
    console.log(p);
    $.post("/web/ajax/ajax.php", p, function(data) {
        // console.log(data);
        if(from == 'popup_login') {
    		if(data.code == '9999'){
    	  		$('#' + msg_convas).html(data.msg);
    		} else {
        	  	if(data.code == '0000' && data.msg == '1') {
        		  	$('#' + msg_convas).html('會員資料已存在');
        	  	}else {
        		  	$('#' + msg_convas).html('');
    			}
        	}
        } else if(from == 'popup_register') {
        	popup_register_account_verify = false;
        	if(data.code == '9999'){
    	  		$('#' + msg_convas).html(data.msg);
    		} else {
        	  	if(data.code == '0000' && data.msg == '1') {
        		  	$('#' + msg_convas).html('會員資料已存在');
        	  	}else {
        		  	$('#' + msg_convas).html('');
        		  	popup_register_account_verify = true;
    			}
        	}
        }

    }, 'json').done(function() { }).fail(function() { }).always(function() { });
};

// 登入會員中心
function login_user_center(category, account, password, auto_login, go_next_page) {
	var p = {};
    p.func = 'login';
    p.category= category;
    p.account = account;
    p.password = password;
    p.auto_login = auto_login;
    console.log(p);
    $.post("/web/ajax/ajax.php", p, function(data) {
        console.log(data);
        if(data.code == '9999'){
            alert(data.msg);
        }
        else if (go_next_page) {
        	location.href = go_next_page;
        }
        else {
        	location.reload();
        }
    }, 'json').done(function() { }).fail(function() { }).always(function() { });
};

// 登出會員中心
function logout_user_center() {
	var p = {};
    p.func = 'logout';
    console.log(p);
	$.post("/web/ajax/ajax.php", p, function(data) {
		console.log(data);
        if(data.code == '9999'){
            alert(data.msg);
        } else {
            // 先暫時做頁面reload，視狀況自行調整
            // location.href = '/web/';
            location.reload();
        }
    }, 'json').done(function() { }).fail(function() { }).always(function() { });
}

function register(from) {
	var account = '', password = '', nickname='';
	if(from == undefined) {
		return;
	}
	if(from == 'popup_register') {
		account = $('#popup_register_account').val();
		password = $('#popup_register_password').val();
		nickname = $.trim($('#popup_register_nickname').val());
		check_user_account_exists('popup_register');
		check_register_password('popup_register');
		console.log('popup_register_account_verify:' + popup_register_account_verify);
		console.log('popup_register_password_verify:' + popup_register_password_verify);
		check_register_password('popup_register');

// 		if(nickname == '') {
// 			$('#error_msg_popup_register_nickname').html('請輸入您的暱稱');
// 			return;
// 		} else {
// 			$('#error_msg_popup_register_nickname').html('');
// 		}
		var agreement = $('#popup_register_agreement').prop('checked') ? 1:0;
		if(agreement != 1) {
			alert ('您須同意會員服務條件');
			return;
		}
	}
	if(!popup_register_account_verify || !popup_register_password_verify) {
		return false;
	}
	var p = {};
    p.func = 'register';
    p.account = account;
    p.password = password;
    p.nickname = nickname;
    console.log(p);
    $.post("/web/ajax/ajax.php", p, function(data) {
        console.log('return');
        console.log(data);
        if(!popup_register_account_verify || !popup_register_password_verify) {
			return
		}
        if(data.code == '9999'){
            alert(data.msg);
        } else {
            // 顯示註冊完成並顯示註冊完成popup window
        	$('.popupRegister1').hide();
//         	$('.popupRegister').hide();
//         	$('.popupRegiSucc').show();
        	alert("您的註冊已完成。\n並已發送驗證信至您所設定之e-mail信箱內，敬請至您的信箱完成驗證。\n完成驗證後，即可獲得  Tripitta 500 元旅遊金。");
        	$('.overlay').hide();
        }
    }, 'json').done(function() { }).fail(function() { }).always(function() { });
}

function send_reset_password(from) {
	if(from == undefined) { return; }
	var account = $('#popup_forget_password_account').val();
	if(account == '') {
		//$('#error_msg_popup_forget_password_account').html('帳號錯誤，請輸入您的帳號');
		alert('帳號錯誤，請輸入您的帳號');
		return;
	}
	var p = {};
    p.func = 'send_reset_password';
    p.account = account;
    console.log(p);
    $.post("/web/ajax/ajax.php", p, function(data) {
        console.log(data);
        if(data.code == '9999'){
            alert(data.msg);
        } else {
            // 顯示註冊完成並顯示重置密碼信件完成popup window
            // alert('新的密碼已寄發至您的信箱');
            $('.popupForgetPwd').hide();
            //$('.popupResetPwd').show();
            $("#forgetPasswordAccount").text(account);
            $('.popupSended').show();
        }
    }, 'json').done(function() { }).fail(function() { }).always(function() { });
}
function set_display_currency(currency_id) {
	var p = {};
    p.func = 'set_display_currency';
    p.currency_id = currency_id;
    console.log(p);
    $.post("/web/ajax/ajax.php", p, function(data) {
        console.log(data);
        if(data.code == '9999'){
            alert(data.msg);
        } else {
            location.reload();
        }
    }, 'json').done(function() { }).fail(function() { }).always(function() { });
}
function show_popup_login() {
	$('.overlay').show();
	$('.popupLogin').show();
	$(window).scrollTop("0");
}
function show_popup_login_account() {
	$('.overlay').show();
	$('.popupLogin1').show();
	$(window).scrollTop("0");
}

function verify_password(pwd) {
	var valid_char = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	var valid_num = "0123456789";
	var error_msg = '';
	if(pwd != '') {
		if(pwd.length < 6) {
			error_msg = '密碼錯誤-格式須為6~20碼英數字';
		}else {
			var has_char = (instr(pwd, valid_char)) ? 1:0;
			var has_num = (instr(pwd, valid_num)) ? 1:0;

			if(has_char == 0 || has_num == 0){
				error_msg = '密碼錯誤-格式須為6~20碼英數字';
    		}
		}
	}
	return error_msg;
}
function instr(src, tar){
	var i = 0;
	for(i=0 ; i < tar.length ; i++){
		if(src.indexOf(tar.substr(i, 1)) >= 0){
			return true;
		}
	}
	return false;
}


// google custom search
(function() {
  var cx = '006336957346262803687:00huuts4rdc';
  var gcse = document.createElement('script');
  gcse.type = 'text/javascript';
  gcse.async = true;
  gcse.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') +
      '//cse.google.com/cse.js?cx=' + cx;
  var s = document.getElementsByTagName('script')[0];
  s.parentNode.insertBefore(gcse, s);
})();

	// 將數字轉換為 1,000 格式
	var formatNumber = function(str, glue) {
	    // 如果傳入必需為數字型參數，不然就噴 isNaN 回去
	    if(isNaN(str)) {
	        return NaN;
	    }
		// 決定三個位數的分隔符號
	    var glue= (typeof glue== 'string') ? glue: ',';
	    var digits = str.toString().split('.'); // 先分左邊跟小數點

	    var integerDigits = digits[0].split(""); // 獎整數的部分切割成陣列
	    var threeDigits = []; // 用來存放3個位數的陣列

	    // 當數字足夠，從後面取出三個位數，轉成字串塞回 threeDigits
	    while (integerDigits.length > 3) {
	        threeDigits.unshift(integerDigits.splice(integerDigits.length - 3, 3).join(""));
	    }

	    threeDigits.unshift(integerDigits.join(""));
	    digits[0] = threeDigits.join(glue);

	    return digits.join(".");
	}

	var chang_header_sort = function(sort){
		$('#header_sort_far').removeClass("selected");
		$('#header_sort_near').removeClass("selected");
		if(sort==1){
			$('#header_sort_far').addClass("selected");
		}else{
			$('#header_sort_near').addClass("selected");
		}
	}
</script>