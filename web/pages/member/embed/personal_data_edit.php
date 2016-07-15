<?php
/**
 * 說明：個人資料(編輯會員)
 * 作者：Casper <casper.lee@fullerton.com.tw>
 * 日期：2016年5月18日
 * 備註：
 */
require_once __DIR__ . '/../../../config.php';
$tripitta_web_service = new tripitta_web_service();
$login_user_data = $tripitta_web_service->check_login();
if(empty($login_user_data)){
	exit;
}
include_once '../../common/member_header.php';
$living_country_tel_code = '';
$living_country_name = '';
if(!empty($login_user_data)) {
	//     printmsg($login_user_data);
	$living_country_id = $login_user_data["living_country_id"];
	$country_row = $tripitta_web_service->load_country($living_country_id);
	if(!empty($country_row)) {
		$living_country_tel_code = $country_row["c_tel_code"];
		$living_country_name = $country_row["c_name"];
	}
}
?>
<?php //include __DIR__ . '/../member_function_menu.php' ?>

	<script type="text/javascript">
	var my_avatar_src = '';

    function do_update_user_data() {
        var is_checked_epaper_homestay = 0;
        if ($('#epaper_homestay').prop('checked')) is_checked_epaper_homestay = 1;

        var p = {};
        p.func = 'update_user_data';
        p.userId = $('#user_id').val();
        p.email = $('#email').val();
//         p.real_name = $('#real_name').val();
        p.gender = $('#gender').val();
        p.nickname = $('#nickname').val();
        p.birthday = $('#birthday').val();
        p.married = $('#married').val();
        p.education = $('#education').val();
        p.family = $('#family').val();
        p.living_tel_country_id = $('#living_tel_country_id').val();
        p.living_country_id = $('#living_country_id').val();
        p.mobile = $('#mobile').val();
        // p.living_area_id = $('#living_area_id').val();
        p.living_address = $('#living_address').val();
        p.epaper_homestay = is_checked_epaper_homestay;

        $.post('/web/ajax/ajax.php', p, function(data) {
//             console.log(data);

            if ('0000' == data.code) {
                if ($('#avatar_upload').val().length > 0) {
                	upload_avatar_image();
                }
                else {
                	alert(data.msg);
                	window.location.href = '/<?php echo $member_path; ?>/';
                }
            }
            else {
            	alert(data.msg);
            }
    	}, 'json')
    	.done(function() {
//     		alert('second success');
    	})
    	.fail(function() {
    		alert('upload_avatar_data error');
    	})
    	.always(function() {
//     		alert('finished');
    	});
    }

    function upload_avatar_image() {
    	var form_data = new FormData();
    	var file_data = $('#avatar_upload').prop("files")[0];
    	form_data.append('avatar_image', file_data);
    	form_data.append('func', 'update_user_avatar');
    	form_data.append('serial_id', $('#user_serial_id').val());
    	form_data.append('user_id', $('#user_id').val());

        $.ajax({
        	url: '/web/ajax/ajax.php',
        	type: 'post',
        	data: form_data,
        	dataType: 'json',
        	cache: false,
        	contentType: false,
        	processData: false
        })
    	.done(function(rtn) {
    		if ('0000' == rtn.code) {
    			my_avatar_src = '<?php echo get_config_image_server()?>' + rtn.data['avatar_path'];
    			console.log(my_avatar_src);
    			sync_image_to_ezding();
    		}
        	alert(rtn.msg);
    	})
    	.fail(function(xhr, status, error) {
    		alert('upload_avatar_image error');
    	})
    	.always(function(rtn) {
//     		alert('finished');
    	});
    }

    function sync_image_to_ezding() {
    	var p = {};
        p.func = 'sync_avatar_image';
        p.avatar_image = my_avatar_src;

    	$.post('/web/ajax/ajax.php', p, function(data) {
//             console.log(data);
    	}, 'json')
    	.done(function() {
//     		alert('second success');
    		window.location.href = '/<?php echo $member_path; ?>/';
    	})
    	.fail(function() {
    		alert('sync_image_to_ezding error');
    	})
    	.always(function() {
//     		alert('finished');
    	});
    }

	function render_avatar_image(avatar_img) {
		// 先取得圖檔 url
// 		my_avatar_src = $('#my_avatar').prop('src');

		// 目前只接受 image/jpeg
		if (avatar_img.type == 'image/jpeg') {
			var imgReader = new FileReader();
			imgReader.onload = function(event) {
				var img = new Image();
				img.onload = function() {
					if (this.width < 200) {
						alert('照片寬度需大於 200px (目前 ' + this.width + 'px)');
						return;
					}
					if (this.height < 200) {
						alert('照片高度需大於 200px (目前 ' + this.height + 'px)');
						return;
					}
				};
				img.src = imgReader.result;

				$('#my_avatar').prop('src', event.target.result);
			};

			imgReader.readAsDataURL(avatar_img);
			$('#my_avatar').show();
		}
		else {
			alert("請上傳 jpeg 圖檔");
			return;
		}
	}

	function resend_verify_mail() {
		var p = {};
	    p.func = 'send_member_verify_email';
	    p.user_id = $('#user_serial_id').val();
	    console.log(p);
		$.post("/web/ajax/ajax.php", p, function(data) {
			console.log(data);
	        if(data.code == '9999'){
	            alert(data.msg);
	        } else {
	            // 先暫時做頁面reload，視狀況自行調整
	            // location.href = '/web/';
	            //location.reload();
	            alert('系統將迅速發送認證信至您信箱，請查看您的信箱並點擊啟用。');
	        }
	    }, 'json').done(function() { }).fail(function() { }).always(function() { });
	}

	function change_gender_icon(gender) {
		if(gender == 'F') {
    		$('#gender_icon').removeClass('fa-mars').addClass('fa-venus');
    	} else {
    		$('#gender_icon').removeClass('fa-venus').addClass('fa-mars');
    	}
	}

    $(function() {
    	$('#save_edit_btn').click(function() { do_update_user_data(); });
    	$('#reset').click(function(){ window.location.href = '/<?php echo $member_path; ?>/profile/'; });
    	$('#avatar_upload').change(function() {
//         	console.log(this.files);
            render_avatar_image(this.files[0]);
    	});

    	if($('#birthday').length > 0){
    		$('#birthday').datepicker(<?= json_encode(array_merge(array("yearRange"=>"1900:" . date('Y')), Constants::$CALENDAR_OPTIONS)) ?>);
    	}
    	$('#gender').change(function() {
        	var gender = $(this).val();
        	change_gender_icon(gender);
        });
    	change_gender_icon($('#gender').val());

        $('.modifyPwdWrap .modifyPwd').on("click", function(){
    		$('.overlay').show();
        	$(".popupMPWD").show();
        	$(window).scrollTop("0");
        });
        $('.popupMPWD .closeBtn').on("click", function(){
    		$('.overlay').hide();
        	$(".popupMPWD").hide();
        });

        $('input:text').focus(function() {
            $(this).closest(".infoFrame").addClass("focused");
        })
        .blur(function() {
        	$(this).closest(".infoFrame").removeClass("focused");
        });
        
    });
	</script>
<form>
<div class="userInfo">
	<div class="userImgBlock">
		<div class="imgWrap">
			<?php if (!empty($avatar)) {?>
				<img id="my_avatar" src="<?php echo get_config_image_server(), $avatar;?>" alt=""<?php if (empty($avatar)) { echo ' style="display:none;"'; } ?> class="userImg">
			<?php  }else{ ?>
 				<img id="my_avatar" src="http://placehold.it/180x180" alt="" class="userImg"> 
			<?php } ?>
			<label class="imgUpload">
				<input type="file" id="avatar_upload" class="upImgFile"></input>
				<i class="fa fa-cloud-upload" aria-hidden="true"></i>
			</label>
		</div>
		<div class="userMail"><?php echo $email; ?></div>
		<div class="verifyBlock">
			<?php if($email_verify_status != 1) { ?>
			<div class="vText">
				<i class="fa fa-exclamation-circle" aria-hidden="true"></i>此帳號未通過認證
			</div>			
			<div class="vBtn">
				<button type="button" class="submit" onclick="javascript:resend_verify_mail();" >發送認證信</button>
			</div>
			<?php } ?>
		</div>
		<div class="modifyPwdWrap">
			<button type="button" class="modifyPwd"><i class="fa fa-lock" aria-hidden="true"></i>修改密碼</button>
		</div>
	</div>
	<div class="userInfoBlock">
		<div class="wrap">
			<label class="infoFrame">
				<i class="fa fa-user" aria-hidden="true"></i>
				<input type="text" id="nickname" name="nickname" maxlength="25" placeholder="暱稱" value="<?php echo $nickname?>" />
			</label>
		</div>
		<div class="wrap">
			<label class="infoFrame">
				<i class="fa fa-transgender" aria-hidden="true"></i>
				<select id="gender">
					<option disabled selected>性別</option>
					<?php
						foreach(constants_user_center::$GENDER_TEXT as $key => $value) {
						    if (empty($key)) continue;
						    echo '<option value="', $key, '"';
						    if ($key == $gender) echo ' selected';
						    echo '>', $value, '</option>';
						}
					?>
				</select>
				<i class="fa fa-angle-down" aria-hidden="true"></i>
			</label>
		</div>
		<div class="wrap">
			<label class="infoFrame">
				<i class="fa fa-gift" aria-hidden="true"></i>
				<input type="text" id="birthday" value="<?php echo $birthday?>" maxlength="15" placeholder="生日" />
			</label>
		</div>
		<div class="wrap">
			<label class="infoFrame countryNum">
				<i class="fa fa-mobile" aria-hidden="true"></i>
				<select id="living_tel_country_id" name="living_tel_country_id">
					<?php
						foreach(constants_user_center::$LIVING_COUNTRY_TEXT as $key => $value) {	
						    echo '<option value="', $key, '"';
						    $country_row = $tripitta_web_service->load_country($key);
						    if ($key == $living_tel_country_id) echo ' selected';
						    echo '>', $country_row['c_tel_code'], '</option>';
						}
					?>
				</select>
				<i class="fa fa-angle-down" aria-hidden="true"></i>
			</label>
			<label class="infoFrame phoneNum">
				<input type="text" id="mobile" name="mobile" autocomplete="off" maxlength="20" placeholder="電話號碼" value="<?php echo $mobile?>" />
			</label>
		</div>
		<div class="wrap">
			<label class="infoFrame">
				<i class="fa fa-map-marker" aria-hidden="true"></i>
				<select id="living_country_id" name="living_country_id">
					<option disabled selected>國家</option>
					<?php
						foreach(constants_user_center::$LIVING_COUNTRY_TEXT as $key => $value) {
						    echo '<option value="', $key, '"';
						    if ($key == $living_country_id) echo ' selected';
						    echo '>', $value, '</option>';
						}
					?>
				</select>
				<i class="fa fa-angle-down" aria-hidden="true"></i>
			</label>
		</div>
		<div class="wrap">
			<?php /*
			<label class="infoFrame">
				<i class="fa fa-map-marker" aria-hidden="true"></i>
				<select>
					<option disabled  selected>縣市</option>
					<option>天龍</option>
					<option>慶記</option>
				</select>
				<i class="fa fa-angle-down" aria-hidden="true"></i>
			</label>
			*/ ?>
		</div>
		<div class="wrap full">
			<label class="infoFrame">
				<i class="fa fa-map-marker" aria-hidden="true"></i>
				<input type="text" id="living_address" name="living_address" autocomplete="off" maxlength="200" placeholder="地址" value="<?php echo $living_address?>" />
			</label>
		</div>
		<div class="wrap">
			<label class="infoFrame">
				<i class="fa fa-users" aria-hidden="true"></i>
				<select id="family" name="family">
					<option disabled selected>家庭人數</option>
					<?php for($i=0;$i<=10;$i++){
						$select = '';
						if($i == $family) $select = 'selected';
					?>
					<option value="<?= $i ?>" <?= $select ?>><?= $i ?>人</option>
					<?php } ?>
				</select>
				<i class="fa fa-angle-down" aria-hidden="true"></i>
			</label>
		</div>
		<div class="wrap">
			<label class="infoFrame">
				<i class="fa fa-transgender" aria-hidden="true"></i>
				<select id="married" name="married">
					<option disabled selected>婚姻狀況</option>
					<?php
						foreach(constants_user_center::$MARRIED_TEXT as $key => $value) {
						    if (empty($key)) continue;
						    echo '<option value="', $key, '"';
						    if ($key == $married) echo ' selected';
						    echo '>', $value, '</option>';
						}
					?>
				</select>
				<i class="fa fa-angle-down" aria-hidden="true"></i>
			</label>
		</div>		
		<div class="userEnd">
			<label class="newsLetterWrap">
				<input type="checkbox" id="epaper_homestay" name="epaper_homestay"<?php echo (1 == $epaper_homestay) ? ' checked' : '';?> /> 是否訂閱電子報
			</label>
			<div class="btnWrap">
<!-- 				<button type="button" id="reset" class="cancel">取消</button> -->
				<button type="button" id="save_edit_btn" class="submit">儲存</button>
				<input type="hidden" id="user_id" value="<?php echo $login_user_data['id']?>">
				<input type="hidden" id="user_serial_id" value="<?php echo $login_user_data['serialId']?>">
			</div>
		</div>
	</div>
</div>
</form>

<!-- modify pwd popup -->
            <form>
            <div class="popupMPWD">
                <div class="closeBtn">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </div>
                <h4>修改密碼</h4>
                <div class="wrapper">
                    <div class="label selected">
                        <div class="icon">
                            <i class="fa fa-lock" aria-hidden="true"></i>
                        </div>
                        <div class="input">
                            <input type="password" id="orig_password" autocomplete="off" placeholder="輸入舊密碼" maxlength="20" />
                            <div class="errMsg" id="error_msg_orig_password"></div>
                        </div>
                    </div>
                    <div class="label">
                        <div class="icon">
                            <i class="fa fa-lock" aria-hidden="true"></i>
                        </div>
                        <div class="input">
                            <div class="input2">
                                <input type="password" id="password" autocomplete="off" placeholder="輸入新密碼" maxlength="20" />
                                <div class="errMsg" id="error_msg_password"></div>
                            </div>
                        </div>
                    </div>
                    <div class="label">
                        <div class="icon">
                            <i class="fa fa-lock" aria-hidden="true"></i>
                        </div>
                        <div class="input">
                            <div class="input2">
                                <input type="password" id="password_confirm" autocomplete="off" placeholder="再次確認密碼" maxlength="20" />
                                <div class="errMsg" id="error_msg_password_confirm"></div>
                            </div>
                        </div>
                    </div>
                    <div class="btnWrap">
                        <input type="button" id="btn_update_password" class="submit" value="送出">
                        <input type="hidden" id="user_id" value="<?php echo $login_user_data['id']?>">
                    </div>
                </div>	
            </div>
            </form>
            
<script type="text/javascript">
        	var orig_password_verify = false;
            $(function () {
				// 修改密碼使用
                $('#btn_update_password').click(function() {
        			update_user_password();
        		});
        		$('#btn_reset').click(function() {
        			$('#error_msg_orig_password').html('');
        			$('#error_msg_password').html('');
        			$('#error_msg_password_confirm').html('');

        		});

        		$('#orig_password').blur(function() {
        			check_orig_password();
        		});
        		//$('#password').blur(function() { check_register_password('update_password'); });
        		//$('#password_confirm').blur(function() { check_register_password('update_password'); });

        		$(".popupMPWD").hide();
            });
            
        	function update_user_password() {
        		check_orig_password();
        		check_register_password('update_password');
        		console.log('popup_register_password_verify:' + popup_register_password_verify);
        		console.log('orig_password_verify:' + orig_password_verify);
        		if(!popup_register_password_verify || !orig_password_verify){
        			return;
        		}

        		var p = {};
        	    p.func = 'update_user_password';
        	    p.user_id = $('#user_id').val();
        	    p.password = $('#password').val();
        	    p.orig_password = $('#orig_password').val();
        	    console.log(p);
        		$.post("/web/ajax/ajax.php", p, function(data) {
        			console.log(data);
        	        if(data.code == '9999'){
        	            alert(data.msg);
        	        } else {
        	        	alert('密碼已更新');
        	            // 先暫時做頁面reload，視狀況自行調整
        	            // location.href = '/web/';
        	            location.reload();
        	        }
        	    }, 'json').done(function() { }).fail(function() { }).always(function() { });
        	}
        	function check_orig_password() {
        		if($('#orig_password').val() == '') {
        			$('#error_msg_orig_password').html('請輸入您的舊密碼');
        			orig_password_verify = false;
        		} else {
        			$('#error_msg_orig_password').html('');
        			orig_password_verify = true;
        		}
        	}
        </script>            