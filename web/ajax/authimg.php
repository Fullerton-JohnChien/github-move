<?php 
	include_once "../config.php";
	// Filename	: authimg.php
	// Author	: marx
	// purpose	: 產生 PNG 圖形驗證碼


	//header("Content-type: image/PNG");

	$authType = $_REQUEST['authType'];
	$act = (isset($_REQUEST['act'])) ? $_REQUEST['act'] : "";
	if (empty($authType)) $authType = 'payment';

	$captchaType = null;
	if ('payment' == $authType) $captchaType = CAPTCHA_PAYMENT;
	if ('user' == $authType) $captchaType = CAPTCHA_USER;

	if(!empty($captchaType)){
		if (empty($_SESSION[$captchaType])) $_SESSION[$captchaType] = generateSerno(4);
		
		if("refresh" == $act){
			$auth_code = generateSerno(4);
			$_SESSION[$captchaType] = $auth_code;
			$string = $auth_code;
		}else{
			$string = $_SESSION[$captchaType];
		}
	}else{
		exit();
	}
	if(empty($string))
		exit();

	$words = strlen($string);	// 計算驗證碼長度
	$imgWidth = $words*12+4;	// 計算圖片寬度
	$imgHeight = $words*3.5+4;	// 計算圖片高度

	// 建立空白圖片
	$im = imagecreate($imgWidth, $imgHeight);

	//$string = chunk_split($string, "1" , "|");	// 為了要切割字申, 先將每一個字後加上"|".
	//$string = explode("|" , $string);		// 切割字串, 將字串以"|"切割放入陣列中.
	$string = str_split($string);

	$bg = ImageColorAllocate($im, 255, 255, 255);	// 背景顏色
	$text = ImageColorAllocate($im, 50,50,255);	// 文字顏色

	// 產生彩色滿天星
	for ( $i = 1; $i <= $words*3; $i++ ){
		$color = ImageColorallocate($im, rand(160, 255), rand(160, 255), rand(160, 255));
		imageString( $im, 5, rand()%$imgWidth, rand()%$imgHeight, "*", $color);
	}

	// 產生文字影像
	for($i=0;$i<$words;$i++){
		$text = ImageColorallocate($im, rand(0, 160), rand(0, 160), rand(0, 160));
		imagestring($im, 5, $i*12+4, mt_rand(0, $words*2-4), $string[$i], $text);
	}

	imagepng($im); 
	imagedestroy($im);
?>