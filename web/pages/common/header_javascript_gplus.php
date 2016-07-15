<?php
/**
 * 說明：處理 Google Plus 事件
 * 作者：Casper <casper.lee@fullerton.com.tw>
 * 日期：2016年5月26日
 * 備註：
 */
?>
<script src="https://apis.google.com/js/client.js?onload=handleClientLoad"></script>
<script src="https://apis.google.com/js/client:platform.js" type="text/javascript"></script>
<script src="https://apis.google.com/js/platform.js" async="" defer="" gapi_processed="true"></script>
<script type="text/javascript">
	var auth2_status = 0;
	var googleUser = {};
	var startApp = function() {
		  gapi.load('auth2', function(){
		    // Retrieve the singleton for the GoogleAuth library and set up the client.
		    auth2 = gapi.auth2.init({
		      client_id: '<?php echo GOOGLE_CLIENT_ID; ?>.apps.googleusercontent.com',
		      cookiepolicy: 'single_host_origin'//,
		      // Request scopes in addition to 'profile' and 'email'
		      //scope: 'additional_scope'
		    });
		    var button1 = document.getElementById('popup_register_btn_gplus_register');
		    var button2 = document.getElementById('popup_login_btn_gplus');
		    if(auth2_status==1){
			 	// 會員註冊 - Google+ 按鈕
			    attachSignin(button1);
		    }else if(auth2_status==2){
		    	// 會員登入 - Google+ 按鈕
			    attachSignin(button2);
		    }else{
			    attachSignin(button1);
			    attachSignin(button2);
		    }
		  });
	};

	function attachSignin(element) {
	  console.log(element.id);
	  auth2.attachClickHandler(element, {},
	      function(googleUser) {
			  var profile = googleUser.getBasicProfile();
			  console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
			  console.log('Name: ' + profile.getName());
			  console.log('Image URL: ' + profile.getImageUrl());
			  console.log('Email: ' + profile.getEmail());

			  var id_token = googleUser.getAuthResponse().id_token;
			  console.log('ID Token: ' + id_token);
			  var p = {};
			  if(auth2_status==1){
			      p.func = 'register_gplus';
			  }else{
				  p.func = 'login_gplus';
			  }
		      p.token = id_token;
		      console.log(p);
		      $.post("/web/ajax/ajax.php", p, function(data) {
		          console.log('return');
		          console.log(data);
		          if(data.code == '9999'){
		              	alert(data.msg);
		          } else {
			          if(auth2_status==1){
			        	// 顯示註冊完成並顯示註冊完成popup window
//				           	$('.popupRegister1').hide();
				          	$('.popupRegister').hide();
//				           	$('.popupRegiSucc').show();
							$('.overlay').hide();
				          	alert("註冊完成");
			          }else{
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
		          }
		      }, 'json').done(function() { }).fail(function() { }).always(function() { });
	      }, function(error) {
	        alert(JSON.stringify(error, undefined, 2));
	      });
	}

	$(function () {
		// 會員註冊 - Google+
		$('#popup_register_btn_gplus_register').on("click", function(){
			auth2_status = 1;
			startApp();
		});

		// 會員登入 - Google+
		$('#popup_login_btn_gplus').on("click", function(){
			auth2_status = 2;
			startApp();
		});

		// 開始註冊與登入
		startApp();
	});
</script>