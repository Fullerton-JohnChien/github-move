<?php
/**
 * 說明：處理 Facebook 事件
 * 作者：Casper <casper.lee@fullerton.com.tw>
 * 日期：2016年5月26日
 * 備註：
 */
?>
<script id="facebook-jssdk" src="//connect.facebook.net/en_US/sdk.js"></script>
<script>
	// This is called with the results from from FB.getLoginStatus().
    function statusChangeCallback(response, type) {
        console.log('statusChangeCallback');
        console.log(response);
        // The response object is returned with a status field that lets the
        // app know the current login status of the person.
        // Full docs on the response object can be found in the documentation
        // for FB.getLoginStatus().
        if(isiPhone()){
			iosFbLogin();
		}else{
	        if (response.status === 'connected') {
	            // Logged into your app and Facebook.
	            var token = response.authResponse.accessToken;
	            switch(type){
					case 1:
	            		register_facebook(token);
	            		break;
					case 2:
	            		login_facebook(token);
						break;
	            }
	        } else if (response.status === 'not_authorized') {
	            // The person is logged into Facebook, but not your app.
	        	facebook_login(type);
	        } else {
	            // The person is not logged into Facebook, so we're not sure if
	            // they are logged into this app or not.
	        	facebook_login(type);
	        }
		}
    }

	// This function is called when someone finishes with the Login
	// Button.  See the onlogin handler attached to it in the sample
	// code below.
    function checkLoginState(type) {
        FB.getLoginStatus(function (response) {
            statusChangeCallback(response, type);
        }, true);
    }

    function isiPhone(){
        // return (/(iPhone|iPad|iPod)/i.test(navigator.userAgent));
        return true;
    }

	function iosFbLogin(){
		var url = '<?php echo $full_serverName; ?>';
		window.location.href = 'https://www.facebook.com/dialog/oauth?client_id=<?php echo FACEBOOK_APP_ID; ?>&scope=email,public_profile&redirect_uri=' + url;
	}

	function facebook_login(type){
		FB.login(function(response){
            if(response.status=='connected'){
                var token = response.authResponse.accessToken;
                if(token!=''){
                    switch(type){
	                    case 1:
	                		register_facebook(token);
	                		break;
	                    case 2:
	                    	login_facebook(token);
	                        break;
                    }
                }
            }
        },{scope: 'email,public_profile'});
	}

    function register_facebook(token){
    	var p = {};
        p.func = 'register_facebook';
        p.token = token;
        console.log(p);
        $.post("/web/ajax/ajax.php", p, function(data) {
            console.log('return');
            console.log(data);
            if(data.code == '9999'){
                alert(data.msg);
            } else {
                // 顯示註冊完成並顯示註冊完成popup window
//             	$('.popupRegister1').hide();
            	$('.popupRegister').hide();
//             	$('.popupRegiSucc').show();
				$('.overlay').hide();
            	alert("註冊完成");
            }
        }, 'json').done(function() { }).fail(function() { }).always(function() { });
    }

    function login_facebook(token){
    	var p = {};
        p.func = 'login_facebook';
        p.token = token;
        console.log(p);
        $.post("/web/ajax/ajax.php", p, function(data) {
            console.log('return');
            console.log(data);
            if(data.code == '9999'){
                alert(data.msg);
            } else {
                // 完成登入popup window
            	$('.popupLogin').hide();
				$('.overlay').hide();
				if ($('#go_next_page') && $('#go_next_page').val()) {
					location.href = $('#go_next_page').val();
				}
				else {
					location.reload();
				}
            }
        }, 'json').done(function() { }).fail(function() { }).always(function() { });
    }

    function login_ios_facebook(code){
    	var p = {};
        p.func = 'login_ios_facebook';
        p.code = code;
        console.log(p);
        $.post("/web/ajax/ajax.php", p, function(data) {
            console.log('return');
            console.log(data);
            if(data.code == '9999'){
                alert(data.msg);
            } else {
                // 完成登入popup window
            	$('.popupRegister').hide();
            	$('.popupLogin').hide();
				$('.overlay').hide();
            	if ($('#go_next_page') && $('#go_next_page').val()) {
					location.href = $('#go_next_page').val();
				}
				else {
// 					location.reload();
					location.href="<?=$full_serverName?>";
				}
            }
        }, 'json').done(function() { }).fail(function() { }).always(function() { });
    }

    window.fbAsyncInit = function () {
        FB.init({
            appId: '<?php echo FACEBOOK_APP_ID;?>',
            cookie: true, // enable cookies to allow the server to access
            // the session
            xfbml: true, // parse social plugins on this page
            version: 'v2.5' // use graph api version 2.5
        });
    };

	// Load the SDK asynchronously
    (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id))
            return;
        js = d.createElement(s);
        js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    $(function (){
		// 會員註冊 - Facebook
    	$('#popup_register_btn_fb_register').on("click", function(){
    		checkLoginState(1);
    	});
		// 會員登入 - Facebook
    	$('#popup_login_btn_fb').on("click", function(){
    		checkLoginState(2);
    	});
    	<?php if(!empty($ios_fb_code)){ ?>
    	login_ios_facebook('<?php echo $ios_fb_code; ?>');
    	<?php } ?>
    });
</script>