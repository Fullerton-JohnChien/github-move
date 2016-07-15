<?php
/**
 * 說明：買貴退差價三倍
 * 作者：bobby-luo <bobby.luo@fullerton.com.tw>
 * 日期：2016年01月06日
 * 備註：
 */
include_once('../../config.php');
include_once "upload.php";

// 檢查會員是否登入
$tripitta_web_service = new tripitta_web_service();

$login_user_data = $tripitta_web_service->check_login();

$name = (!empty($login_user_data)) ? $login_user_data['nickname'] : "";
$email = (!empty($login_user_data)) ? $login_user_data['email'] : "";
$mobile = (!empty($login_user_data)) ? $login_user_data['mobile'] : "";
$user_id = (!empty($login_user_data)) ? $login_user_data['serialId'] : "";
$lowestPriceGuaranteedDao = Dao_loader::__get_lowest_price_guaranteed_dao();
$ezding_hf_photo = Dao_loader::__get_photo_dao();
$tripitta_api_client_service = tripitta_api_client_service::__get_instance(tripitta_api_client_service::SITE_TRIPITTA_WEB_TW);
if (isset($_REQUEST['act']) && $_REQUEST['act'] == 'add.go') {
	$transactionId = strtoupper($_REQUEST["transactionId"]);
	$userName = $_REQUEST["userName"];
	$userEmail = $_REQUEST["userEmail"];
	$userMobile = $_REQUEST["userMobile"];
	$areaName = $_REQUEST["areaName"];
	$hsName = $_REQUEST["hsName"];
	$checkInDate = $_REQUEST["userCheckInDate"];
	$price = $_REQUEST["price"];
	$website = $_REQUEST["website"];
	if(!empty($transactionId)) {
		$cond = array();
		$cond['trans_id'] = $transactionId;
	}
	$orderList = $tripitta_api_client_service->get_order($cond);
	if (empty($orderList)){
		$remote_addr = $_SERVER["REMOTE_ADDR"];
		$body = "IP ： " . $remote_addr;
		$body .= "<br>userId ： " . $_SESSION['travel.ezding.user.data']['serialId'];
		$body .= "<br>transactionId ： " . $transactionId;
		sendmail($mail_from, array('lewis.liang@fullerton.com.tw', 'odtmilk@yahoo.com.tw'), $siteStr.'Tripitta - 買貴退三倍差價申請郵件-Err', $body);
		alertmsg('參數錯誤!', "/");
		exit();
	}
	if (empty($transactionId) || empty($website) || empty($price) || empty($userName) || empty($userEmail) || empty($userMobile)){
		$remote_addr = $_SERVER["REMOTE_ADDR"];
		$body = "IP ： " . $remote_addr;
		$body .= "<br>userId ： " . $_SESSION['travel.ezding.user.data']['serialId'];
		sendmail($mail_from, array('lewis.liang@fullerton.com.tw', 'odtmilk@yahoo.com.tw'), $siteStr.'Tripitta - 買貴退三倍差價申請郵件-Err', $body);
		alertmsg('參數錯誤!', "/");
		exit();
	}
	$item = array();
	$item['lpg_category'] = "tripitta";
	$item['lpg_transaction_id'] = $transactionId;
	$item['lpg_user_name'] = $userName;
	$item['lpg_user_email'] = $userEmail;
	$item['lpg_user_mobile'] = $userMobile;
	$item['lpg_hs_name'] = $hsName;
	$item['lpg_area_name'] = $areaName;
	$item['lpg_check_in_date'] = $checkInDate;
	$item['lpg_price'] = $price;
	$item['lpg_website'] = $website;
	$item['lpg_create_time'] = date('Y-m-d H:i:s');
	$item['lpg_create_user'] = $user_id;
	$newId = $lowestPriceGuaranteedDao->save($item);
	// 新增照片
	for ($i=1;$i<=3;$i++) {
		if($_FILES['pic'.$i]['size']>0){
			$photo_item = array();
			$photo_item['p_category'] = 'price_guarantee';
			$photo_item['p_reference_id'] = $newId;
			$photo_item['p_content_type'] = substr($_FILES['pic'.$i]['name'], strrpos($_FILES['pic'.$i]['name'] , ".")+1);
			$photo_item['p_owner'] = $user_id;
			$photo_item['p_status'] = 1;
			$photo_item['p_create_time'] = date("Y-m-d H:i:s");
			$photo_item['p_display_order'] = $i;
			$photo_item['p_create_user'] = $user_id;
			$p_Id = $ezding_hf_photo->saveHfPhotoByItem($photo_item);
			unset($photo_item);
			$upload = new upload();
			$file_dir = $price_guarantee."/".$newId.'/';
			$upload->mkdir_r($file_dir);
			$upload->uploaddir = $price_guarantee;
			$picpath = $upload->uploadSingle('pic'.$i, $newId.'/'.$p_Id);
			//啟用雲端同步圖片
			if ( $enableSynPicToClouds ) {
				$fname = empty($_FILES[$name]['name'])?'':$_FILES[$name]['name']; //echo $fname;exit;
				$extName = substr(strrchr($fname, "."), 1);
				$tmpPath = $file_dir.$p_Id.'.'.$extName;
				$url = 'https://www.tripitta.com'.$tmpPath;
				$sync_avatar_url = 'http://api.ezding.com.tw/travel/ftc/common/sync_file.php';
				if (!is_production()) $sync_avatar_url = 'http://alpha.api.ezding.com.tw/travel/ftc/common/sync_file.php';
				$post_data = array();
				$post_data['u'] = $url;
				$post_data['p'] = $file_dir;
				$post_data['f'] = basename($url);
				writeLog(__FUNCTION__ . ' ' . __LINE__ . ' ' . json_encode($post_data, JSON_UNESCAPED_UNICODE));
				$ret = open_url($sync_avatar_url, NULL, NULL, 30, $post_data);
				unlink($tmpPath);
				writeLog(__FUNCTION__ . ' ' . __LINE__ . ' ' . json_encode($ret, JSON_UNESCAPED_UNICODE));
			}

		}
	}
	$body = "申請日期：". date("Y-m-d");
	$body .= "<br>申請人訂單號碼：" . $transactionId;
	$body .= "<br>申請人姓名：" . $userName;
	$body .= "<br>申請人行動電話：" . $userMobile;
	$body .= "<br>申請人Email：" . $userEmail;
	$body .= "<br>訂購之區域/旅宿名稱：" . $areaName . '-'. $hsName;
	$body .= "<br>訂購的之住日期：" . $checkInDate;
	$body .= "<br>找到較低的房價：" . $price;
	$body .= "<br>價格較低的網址：" . $website;
	$row = $lowestPriceGuaranteedDao->load($newId);
	if (!empty($row['lpg_transaction_id']) && !empty($row['lpg_website'])){
		sendmail(get_config_mail_from(), Constants::$TRAVEL_PM_EMAILS, $siteStr.'Tripitta - 買貴退三倍差價申請郵件', $body);
		alertmsg('Tripitta已收到您的申請，確認後將會由專人回覆，謝謝您。', "/");
	} else {
		sendmail(get_config_mail_from(), Constants::$TRAVEL_PM_EMAILS, $siteStr.'Tripitta - 買貴退三倍差價申請郵件-Err', $body);
		alertmsg('很抱歉，提交中發生問題，請聯絡客服人員，謝謝!!', "/");
	}
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<meta charset="UTF-8">
	<title>買貴退三倍差價 - Tripitta 旅必達</title>
	<link rel="stylesheet" href="../../css/main.css?01121536">
	<link rel="stylesheet" href="../../css/banner.css">
	<? include __DIR__ . "/../common/head.php"; ?>
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type="text/javascript">
		var userId = '<?php echo $user_id ?>';
		function showEdit()
		{
			if (userId == '') {
				alert('請先登入會員，謝謝!');
				show_popup_login();
			} else {
				$('#edit').show();
				$("html,body").animate({scrollTop: $('#edit').offset().top}, 1000);
				$('#name').text('<?php echo $name; ?>');
				$('#email').text('<?php echo $email; ?>');
				$('#mobile').text('<?php echo $mobile; ?>');
			}
		}

		function findOrderByTransactionId()
		{
			var trans_id = $('#transactionId').val();
			var user_id = '<?php echo $user_id ?>';
			trans_id = trans_id.toUpperCase();
			if(trans_id != ''){
				$.getJSON('../../ajax/ajax.php',
					{func: 'find_order_by_trans_id', 'trans_id': trans_id, 'user_id': user_id},
					function(data) {
						if (data.code == "0000") {
							$("#name").text(data.data.om_buyer_name);
							$("#email").text(data.data.om_buyer_email);
							$("#mobile").text(data.data.om_buyer_mobile);
							$("#userAreaNameAndHomestyName").text(data.data.areaName+' / '+data.data.hsName);
							$("#checkInDate").text(data.data.oh_check_in_date);
							$("#price").attr('disabled', false);
							$("#website").attr('disabled', false);
							$('#submit').show();
						} else if (data.code == "9999") {
							alert(data.msg);
							$('#transactionId').focus();
							$('#transactionId').val('');
						}
					}
				);
			}
		}

		var imgWidthLimit = 600; // 限制圖片寬
		var imgHeightLimit = 400; // 限制圖片高
		var imgExtension = /\.(bmp|jpg|jpeg|png|gif)$/i; // 允許的圖片副檔名
		function PreviewImage(id) {
		    var oFReader = new FileReader();
		    oFReader.readAsDataURL(document.getElementById("pic"+id).files[0]);
		    var x = document.getElementById("pic"+id);
		    if(!x || !x.value) return;
		    if(!imgExtension.test(x.value)){
		     alert("格式錯誤\n\n只支援上傳副檔名為 bmp、jpg、jpeg、png、gif");
		     x.value = '';
		     return;
		    }
		    var size = document.getElementById("pic"+id).files.item(0).size;
			if(size > (1024*1024)){
				alert('上傳圖片大小限制須小於 1.0 MB\n\n目前要上傳的大小為 ' + formatFloat((size/1024/1024), 2) + ' MB');
				x.value = '';
				return;
			}
			var tmpImg = true;
		    oFReader.onload = function (oFREvent) {
		        document.getElementById("showPic"+id).style.backgroundImage = 'url('+oFREvent.target.result+')';
		        $('#showPic'+id).show();
		    };
		};

		function chkData()
		{
			if($('#transactionId').val() == '')
			{
				alert('請輸入您的訂單號碼');
				$('#transactionId').focus();
				return;
			}
			else if($('#currency').val() == 0)
			{
				alert('請選擇幣別!');
				$('#currency').focus();
				return;
			}
			else if($('#price').val() == '')
			{
				alert('請輸入您找到的較低房價');
				$('#price').focus();
				return;
			}
			else if($('#website').val() == '')
			{
				alert('請輸入您找到價格較低的網站');
				$('#website').focus();
				return;
			}
			else if($('#pic1').val() == '' && $('#pic2').val() == '' && $('#pic3').val() == '')
			{
				alert('請您至少上傳一張圖片');
				return;
			}
			else
			{
				var tmp = $("#userAreaNameAndHomestyName").text();
				tmp2 = tmp.split(' / ');
				$('#act').val("add.go");
				$('#areaName').val(tmp2[0]);
				$('#hsName').val(tmp2[1]);
				$('#userName').val($("#name").text());
				$('#userEmail').val($("#email").text());
				$('#userMobile').val($("#mobile").text());
				$('#userCheckInDate').val($("#checkInDate").text());
				if($('#areaName').val() == '' || $('#hsName').val() == '' || $('#userName').val() == '' || $('#userEmail').val() == '' || $('#userMobile').val() == '' || $('#userCheckInDate').val() == '')
				{
					alert('右側資料為空，請輸入正確的訂單編號');
					return;
				}
				if(confirm('確定提交!?')){
					$('#act').val('add.go');
					$('#form1').submit();
				}
			}
		}

		function clear_website() {
			$('#website').attr('placeholder', "");
		}
	</script>
</head>
<body>
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="bestpriceguarantee_banner"></div>
	<article class="guarantee-container">
		<section>
			<h1>申請補償</h1>
			<p>
				請蒐集下列資料，並填寫<a class="showEdit" href="javascript:showEdit();">買貴退三倍差價補償申請表</a>
			</p>
			<ul class="ul">
				<li>您在Tripitta 完成訂房的 訂單編號。</li>
				<li>提供我們您找到可以用更低價訂房的網站網址。</li>
				<li>飯店名稱、所在國家與城市。</li>
				<li>可最多附上3張網頁截圖，來請附上網頁截圖畫面，截圖需能夠清楚出示房間的空房狀況、房型、促銷類型，同時也要能顯示相同預訂日期所提供結帳時的最後房價(需含相關的稅及服務費用)，詳細規定請見下方條款與規範之第二點。</li>
				<li>請注意： 您不需要在對方網站上執行一筆真實的訂房，只需要提供我們要求的相關資訊來向我們證實房價的差異。</li>
			</ul>
		</section>
		<section>
			<h2>條款與規範</h2>
			<ul class="ul">
				<li>只有已確認的訂單才適用「買貴退三倍差價」。申請補償時必須提供您的Tripitta的訂單編號。</li>
				<li>以下皆為申請補償必須遵守的條件：
					<ul>
						<li>相同的民宿。</li>
						<li>相同的住宿期間。</li>
						<li>相同的房型/床型。</li>
						<li>相同的提前預訂、付款和押金要求（若有請提供）。</li>
						<li>相同的取消條款和修改訂單規定。</li>
						<li>此房間在我們的查核人員執行預訂確認時，必需要能提供立即訂房確認。</li>
					</ul>
				</li>
				<li>「買貴退三倍差價」不適用非公開的價格，包括但不限於：
					<ul>
						<li>私人價格。</li>
						<li>團購價或促銷價。</li>
						<li>包括在團體旅遊行程中的客房價格。「買貴退三倍差價」不適用於非公眾的價格，包括但不限於：</li>
						<li>公司/私人的折扣價(內部價格)。</li>
						<li>團體價或行銷活動促銷價。</li>
						<li>任一種會員價格（例如：特殊會員價或VIP價）。</li>
						<li>忠誠客戶獎勵計劃價格。</li>
						<li>任何專為特定人士所提供，且不面向一般大眾推出的價格。</li>
					</ul>
				</li>
				<li>Tripitta有權可自由裁定補償申請是否有效，包括但不限於有權去界定是否為同一間民宿、同一種房型、同樣的住宿時間，以及申請是否符合所有條款與規範和申請程序要求。</li>
				<li>我們的「買貴退三倍差價」不可與其他優惠價格、特價或促銷活動共同合併使用。</li>
				<li>Tripitta有權在任何時間修訂、修改、補充、暫停或終止「買貴退三倍差價」。<br>
					若有任何關於申請補償的問題，請直接與我們的客服中心聯絡。<br>
					客服中心電話：886-2-8912-6600 <br>
					服務時間：週一~六09:00~21:00 ，週日及國定假日 09:00~18:00
				</li>
			</ul>
		</section>
	</article>
	<div class="wrapper_1" id="edit">
		<div class="sheet">
			<form name="form1" id="form1" action="price_guarantee.php" method="post" enctype="multipart/form-data">
				<input type="hidden" name="act" id="act" value="" />
				<input type="hidden" name="userName" id="userName" value="" />
				<input type="hidden" name="userEmail" id="userEmail" value="" />
				<input type="hidden" name="userMobile" id="userMobile" value="" />
				<input type="hidden" name="areaName" id="areaName" value="" />
				<input type="hidden" name="hsName" id="hsName" value="" />
				<input type="hidden" name="userCheckInDate" id="userCheckInDate" value="" />
				<h6>最低價格保證補償申請表</h6>
				<div class="abc01">
					<div class="member_row">
						<span class="name_title">訂單號碼：</span>
						<input name="transactionId" type="text" id="transactionId" onblur="findOrderByTransactionId();" class="textfield-01" placeholder="">
					</div>
					<div class="member_row">
						<span class="name_title">找到的較低房價：</span>
						<select class="currency" id="currency" style="">
							<option value="0">幣別</option>
							<option value="1">NTD</option>
							<option value="5">HKD</option>
							<option value="3">RMB</option>
						</select>
						<input name="price" type="text" class="textfield-02" id="price" placeholder="" disabled />
					</div>
					<div class="member_row">
						<span class="name_title">價格較低的網站：</span>
						<input name="website" type="text" class="textfield-01" id="website" onclick="clear_website();" placeholder="http://" disabled />
					</div>
				</div>
				<div class="abc02">
					<div class="member_row">
						<span class="name_title">姓名</span>
						<span class="name_title_2" id="name"></span>
					</div>
					<div class="member_row">
						<span class="name_title">E-mail</span>
						<span class="name_title_2" id="email"></span>
					</div>
					<div class="member_row">
						<span class="name_title">行動電話</span>
						<span class="name_title_2" id="mobile"></span>
					</div>
					<div class="member_row">
						<span class="name_title">區域/旅宿名稱</span>
						<span class="name_title_2" id="userAreaNameAndHomestyName"></span>
					</div>
					<div class="member_row">
						<span class="name_title">訂購之入住日期</span>
						<span class="name_title_2" id="checkInDate"></span>
					</div>
				</div>
				<div class="abc03">
					<div class="member_row">
						<span class="name_title">截圖一：</span>
						<label class="choose-file" id="upload" for="pic1"></label>
						<input name="pic1" type="file" class="textfield-02" id="pic1" onchange="PreviewImage(1);">
						<img id="showPic1" class="photo-pic" style="display: none;" />
					</div>
					<div class="member_row">
						<span class="name_title">截圖二：</span>
						<label class="choose-file" id="upload" for="pic2"></label>
						<input name="pic2" type="file" class="textfield-02" id="pic2" onchange="PreviewImage(2);">
						<img id="showPic2" class="photo-pic" style="display: none;" />
					</div>
					<div class="member_row">
						<span class="name_title">截圖三：</span>
						<label class="choose-file" id="upload" for="pic3"></label>
						<input name="pic3" type="file" class="textfield-02" id="pic3" onchange="PreviewImage(3);">
						<img id="showPic3" class="photo-pic" style="display: none;" />
					</div>
					<span class="txt_02">(圖片必需含有旅宿名稱/住宿日期/房型名稱/價格，圖片容量不可起過1MB, 適用bmp、jpg、jpeg、png、gif之格式。)</span>
				</div>
				<a id="submit" href="javascript:chkData();" class="bnt" style="display:none;">送出</a>
			</form>
		</div>
	</div>
	<footer class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<script src="../../js/lib/jquery/jquery.js"></script>
	<script src="../../js/embed.js"></script>
</body>

</html>
