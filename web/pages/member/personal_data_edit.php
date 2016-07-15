<!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<meta charset="UTF-8">
<?php
require_once __DIR__ . '/../../config.php';
?>
	<? include __DIR__ . "/../common/head_new.php"; ?>
	<title>Tripitta 旅必達 會員</title>
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<link rel="stylesheet" href="/web/css/member.css">
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
	<script type="text/javascript">
	var my_avatar_src = '';

    function do_update_user_data() {
        var is_checked_epaper_homestay = 0;
        if ($('#epaper_homestay').prop('checked')) is_checked_epaper_homestay = 1;

        var p = {};
        p.func = 'update_user_data';
        p.userId = $('#user_id').val();
        p.email = $('#email').val();
        p.real_name = $('#real_name').val();
        p.gender = $('#gender').val();
        p.nickname = $('#nickname').val();
        p.birthday = $('#birthday').val();
        p.married = $('#married').val();
        p.education = $('#education').val();
        p.family = $('#family').val();
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
                	window.location.href = '/member/profile/';
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
    		window.location.href = '/member/profile/';
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
    	$('#reset').click(function(){ window.location.href = '/member/profile/'; });
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
    });
	</script>
</head>

<body>
	<header><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="personalEdit-container">
		<h1 class="title">個人資料</h1>
		<form>
		<div class="tile">
			<? include __DIR__ . '/member_function_menu.php' ?>
			<article>
				<div class="wrapper">
					<figure>
						<div>
							<input type="file" id="avatar_upload" class="fileUpload">
						    <img id="my_avatar" src="<?php echo get_config_image_server(), $avatar;?>" alt=""<?php if (empty($avatar)) {echo ' style="display:none;"';}?> >
							<span class="img-member-camara"></span>
						</div>
						<figcaption>上傳照片長寬 200 x 200 px<br>檔案上限 130K</figcaption>
					</figure>
<!-- 					<div class="progress"> -->
<!-- 						<span>資料填寫進度</span> -->
<!-- 						<span class="percent">20</span> -->
<!-- 						<span>%</span> -->
<!-- 					</div> -->
					<div class="progressBar">
						<span class="percentBar"></span>
					</div>
					<section class="s1">
						<div class="group">
							<div class="icon">
								<span class="img-member-mail"></span>
							</div>
							<input type="text" id="email" autocomplete="off" maxlength="100" value="<?php echo $email?>">
							<? if($email_verify_status != 1) { ?>
							<div class="errAuthMsg">
								<i class="img-member-cross-small"></i><a href="javascript:resend_verify_mail();">尚未認證，點我認證</a>
							</div>
							<? } else { ?>
							<div class="succAuthMsg" style="display: block;">
								<i class="img-member-check-small"></i><span>已認證</span>
							</div>
							<? } ?>
						</div>
					</section>
					<section class="s2">
						<div class="group">
							<div class="icon">
								<span class="img-member-user"></span>
							</div>
							<div class="nameWrap">
								<input type="text" id="real_name" autocomplete="off" placeholder="真實姓名" maxlength="96" value="<?php echo $name?>">
								<div class="genderSelect">
									<i id="gender_icon" class="fa fa-venus"></i>
									<select id="gender" class="gender">
									<?php
									foreach(constants_user_center::$GENDER_TEXT as $key => $value) {
									    if (empty($key)) continue;
									    echo '<option value="', $key, '"';
									    if ($key == $gender) echo ' selected';
									    echo '>', $value, '</option>';
									}
									?>
									</select>
									<i class="fa fa-angle-down"></i>
								</div>
							</div>
						</div>
						<span class="errMsg" style="display: none;">密碼錯誤</span>
					</section>
					<section class="s3">
						<div class="group">
							<div class="icon">
								<span class="img-member-nickname"></span>
							</div>
							<input type="text" id="nickname" autocomplete="off" placeholder="暱稱" maxlength="32" value="<?php echo $nickname?>">
						</div>
						<span class="errMsg" style="display: none;">密碼錯誤</span>
					</section>
					<section class="s4">
						<div class="birthWrap">
							<div class="icon">
								<span class="img-member-birthday"></span>
							</div>
							<input type="text" id="birthday" value="<?php echo $birthday?>"> <!-- 套jqueryUI date plugin，此訊息可刪 -->
						</div>
						<div class="marryWrap">
							<div class="icon">
								<span class="img-member-marry"></span>
							</div>
							<div class="marrySelect">
								<select id="married" class="marry">
								<?php
								foreach(constants_user_center::$MARRIED_TEXT as $key => $value) {
								    if (empty($key)) continue;
								    echo '<option value="', $key, '"';
								    if ($key == $married) echo ' selected';
								    echo '>', $value, '</option>';
								}
								?>
								</select>
								<i class="fa fa-angle-down"></i>
							</div>
						</div>
					</section>
					<section class="s5">
						<div class="eduWrap">
							<div class="icon">
								<span class="img-member-education"></span>
							</div>
							<div class="eduSelect">
								<select id="education" class="edu">
								<?php
								foreach(constants_user_center::$EDUCATION_TEXT as $key => $value) {
								    if (empty($key)) continue;
								    echo '<option value="', $key, '"';
								    if ($key == $education) echo ' selected';
								    echo '>', $value, '</option>';
								}
								?>
								</select>
								<i class="fa fa-angle-down"></i>
							</div>
						</div>
						<div class="familyWrap">
							<div class="icon">
								<span class="img-member-family"></span>
							</div>
							<div class="familySelect">
								<select class="family" id="family">
									<?php for($i=0;$i<=10;$i++){
										$select = '';
										if($i == $family) $select = 'selected';
									?>
									<option value="<?= $i ?>" <?= $select ?>><?= $i ?>人</option>
									<?php } ?>
								</select>
							</div>
						</div>
					</section>
					<section class="s6">
						<div class="phoneWrap">
							<div class="icon">
								<span class="img-member-phone"></span>
							</div>
							<div class="phoneSelect">
								<select id="living_country_id" class="phone">
								<?php
								foreach(constants_user_center::$LIVING_COUNTRY_TEXT as $key => $value) {
								    echo '<option value="', $key, '"';
								    if ($key == $living_country_id) echo ' selected';
								    echo '>', $value, '</option>';
								}
								?>
								</select>
								<i class="fa fa-angle-down"></i>
							</div>
							<input type="text" id="mobile" autocomplete="off" maxlength="20" value="<?php echo $mobile?>">
						</div>
						<span class="errMsg" style="display: none;">密碼錯誤</span>
					</section>
					<section class="s7">
						<div class="locationWrap">
							<div class="icon">
								<span class="img-member-location"></span>
							</div>
							<!--
							<div class="locationSelect">
								<select id="living_area_id" class="location">
								<?php
								foreach(constants_user_center::$LIVING_AREA_TEXT as $key => $value) {
								    echo '<option value="', $key, '"';
								    if ($key == $living_area_id) echo ' selected';
								    echo '>', $value, '</option>';
								}
								?>
								</select>
								<i class="fa fa-angle-down"></i>
							</div>
							 -->
							<input type="text" id="living_address" autocomplete="off" maxlength="200" value="<?php echo $living_address?>" placeholder="地址" style="width: 276px;">
						</div>
						<span class="errMsg" style="display: none;">密碼錯誤</span>
					</section>
					<section class="s8">
						<div class="epaperWrap">
							<div class="icon">
								<span class="img-member-newspaper"></span>
							</div>
							<div class="epaperSelect">
								<label>
									<input type="checkbox" id="epaper_homestay"<?php echo (1 == $epaper_homestay) ? ' checked' : '';?>>訂閱電子報
								</label>
							</div>
						</div>
					</section>


					<div class="btnWrap">
						<input type="button" id="reset" value="取消" class="reset">
						<input type="button" id="save_edit_btn" value="儲存" class="submit">
					</div>
				</div>
			</article>
		</div>
		</form>
	</div>
	<footer><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
	<input type="hidden" id="user_id" value="<?php echo $login_user_data['id']?>">
	<input type="hidden" id="user_serial_id" value="<?php echo $login_user_data['serialId']?>">
</body>
</html>
