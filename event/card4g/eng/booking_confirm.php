<?php
/**
 * 說明：4G卡 - 機場臨櫃 訂購頁
 * 作者：Steak
 * 日期：2016年3月23日
 * 備註：
 */
include_once('../../../web/config.php');
$idealcard_service = new idealcard_service();

$email = get_val("email");
$proId = get_val("proId"); // 商品id
// 取得商品資訊
$idealcard_row = $idealcard_service -> get_prod_by_id($proId);

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<? include "../../../web/pages/common/head.php"; ?>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=1080">
	<title>【Tripitta】 - 4G Pre-Paid Card</title>
	<link rel="stylesheet" href="/event/card4g/css/main.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type="text/javascript">
	var email = '<?= $email ?>';
	$(function(){
		$('#email').blur(function(){ check_user_by_email($(this).val());});
		$('#cheakdata').click(function(){ checkdata(); });
		$('#closeBtn').click(function(){ closeRule(); });
		$('#cardType').change(function(){ showPayChannel();});
		$('#userPassword').blur(function(){ check_password($(this).val());});
		$('#userPassword2').blur(function(){ checkPassword();});
		$('#memberDiv').hide();
		refreshCaptcha();
		//$('#btnRemitDetail').click(function() { $('#exportAct').val('export.allocate.detail'); $('#form2').submit();  });
		if(email != '') { check_user_by_email(email);}
	});

	function checkPassword() {
		msg = '';
		if ($('#userPassword').val() != $('#userPassword2').val()) {
			msg += '密碼與再次輸入的密碼不符合!!\n';
			// $('#userPassword').val('');
			// $('#userPassword2').val('');
		}

	    if(msg != '') {
			alert(msg);
			return;
	    }
	}

	function check_password(password) {
		var msg = '';
		var ret = verify_password(password);
		if(ret != ''){
			msg += ret;
			msg += '\n';
			$('#userPassword').focus();
		}

	    if(msg != '') {
			alert(msg);
			return;
	    }
	}

	function checkdata(){
		var msg = '';
		if ($('#email').val() == '') {
			//$('#email').focus();
			msg += 'Please input email!!\n';
		}

		if ($('#email').val() != '') {
			if (!verifyEmailAddress($('#email').val())) {
				//$('#email').focus();
				msg += 'Check the Email format!!\n';
			}
		}

		if($('#isUser').val() == 0){
			if ($('#userPassword').val() == '') {
				//$('#userPassword').focus();
				msg += 'Please input password!!\n';
			}else{
				var ret = verify_password($('#userPassword').val());
				if(ret != ''){
					msg += ret;
					msg += '\n';
				}
			}
			if ($('#userPassword2').val() == '') {
				// $('#userPassword2').focus();
				msg += 'Please confirm password!!\n';
			}else{
				var ret = verify_password($('#userPassword2').val());
				if(ret != ''){
					msg += ret;
					msg += '\n';
				}
			}
			if($("input[name='policyChk']:checked").length == 0) {
				//$('#policyChk').focus();
				msg += 'Please read and agree tripitta rule!!\n';
			}
		}

		if ($('#cardType').val() == '') {
			//$('#cardType').focus();
			msg += 'Please choice pay method!!\n';
		}

		if($('#cardType').val() == "32") {
			var d = new Date();
			var thisYear = d.getFullYear();
			var thisMonth = d.getMonth() + 1;

			if($('#ccNo1').val() == '' || $('#ccNo2').val() == '' ||　$('#ccNo3').val() == '' || $('#ccNo4').val() == ''){
				msg += 'Please input credit card!\n';
			}

			if($('#ccNo1').val().length != 4 || $('#ccNo2').val().length != 4 || $('#ccNo3').val().length != 4 || $('#ccNo4').val().length != 4){
				msg += 'Card number error!!\n';
			}

// 			if($('#ccNo2').val().length != 4){
// 				msg += 'card error2!!\n';
// 			}

// 			if($('#ccNo3').val().length != 4){
// 				msg += 'card error3!!\n';
// 			}

// 			if($('#ccNo4').val().length != 4){
// 				msg += 'card error4!!\n';
// 			}

			if ($('#cvc2').val() == '') {
				msg += 'please input security code!!\n';
			}
			$('#ccNo').val($('#ccNo1').val() + $('#ccNo2').val() + $('#ccNo3').val() + $('#ccNo4').val());
		}

		if($("input[name='receipt']:checked").length == 0) {
			msg += 'please agree the invoice rule!!\n';
		}

	    if(msg != '') {
			alert(msg);
			return;
	    }

		// 驗證認證碼是否正確
		var data = {'captchaCode': $('#captchaCode').val(), 'type': 'user'};
		$.getJSON('/web/ajax/ajax.php',
			{func: 'checkCaptchaCode', data: data},
			function(jsonData) {
		        if(jsonData.code == '9999'){
			        alert("enter the number error!!");
					return;
		        }else {
		        	$('#form1').submit();
		        }
			}
	    );
	}

	function refreshCaptcha() {
		var timestamp = Number(new Date());
		$('#capImg').attr('src', '/web/ajax/authimg.php?authType=user&act=refresh&' + timestamp);
	}

	function openRule(){
		$("#overlay").show();
		$("#clauseWrap").show();
	}

	function closeRule(){
		$("#overlay").hide();
		$("#clauseWrap").hide();
	}

	// 檢查是否為會員
	function check_user_by_email(email) {
		var p = {};
		// 移除字串中的空白
		var  reg   =   /\s/g;
		var  emails   =   email.replace(reg,   "");
		//alert(emails);

	    p.func = 'check_user_by_email';
	    p.email = emails;
	    console.log(p);
	    $.post("/web/ajax/ajax.php", p, function(data) {
	        console.log(data);
	        if(data.code == '9999'){
	            alert(data.msg);
	        } else {
	            if(data.msg == ''){
					$('#pwdDiv').show();
					$('#isUser').val(0);
					$('#memberDiv').hide();
	            }else {
	            	$('#pwdDiv').hide();
	            	$('#isUser').val(1);
	            	$('#memberDiv').show();
	            }
	            $('#email').val(emails);

	        }
	    }, 'json').done(function() { }).fail(function() { }).always(function() { });
	};

	function showPayChannel(){
		if($('#cardType').val() == "32") {
			$('#payDiv').show();
		}else {
			$('#payDiv').hide();
		}
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

				if(has_char == 0 && has_num == 0){
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

	function verifyEmailAddress(email)
	{
		var pattern = /^([a-zA-Z0-9_.-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/;

		flag = pattern.test(email);

		if(flag)
			return true;
		else
			return false;
	}
	</script>
</head>
<body>
	<div class="eng-step2-container">
		<header>
			<img src="/event/card4g/img/logo.svg" alt="">
			<h4>
				<div class="telecom">Chunghwa Telecom</div>
				<div class="cardName">4G Pre-Paid Card</div>
			</h4>
		</header>
		<main>
		<form id="form1" name="form1" method="post" action="/event/card4g/shopping_cart_to_order.php">
		<input type="hidden" name="lang" id="lang" value="eng"/>
		<input type="hidden" name="proId" id="proId" value="<?= $proId ?>"/>
		<input type="hidden" id="ccNo" name="ccNo"/>
			<h1 class="payTitleEng">Payment</h1>
			<div class="itemWrap">
				<img src="<?php echo get_config_image_server() . '/photos/' . (is_production() ? 'idealcard' : 'idealcard_alpha') . '/prod/'. $idealcard_row['i_photo']. '.svg'?>">
				<hgrounp>
					<h2 class="h2eng">
						<span class="days"><?= $idealcard_row["i_days"] ?> Days Pass</span>
						<span> of Unlimited Data</span>
					</h2>
					<h3 class="h3eng">
						and Airtime TWD <span><?= $idealcard_row["i_call_amount"] ?></span>
					</h3>
					<h4 class="h4eng">
						Sale Price
						<strong>TWD</strong>
						<span class="amount"><?= $idealcard_row["i_price"] ?></span>
					</h4>
				</hgrounp>
			</div>

			<h2 class="personal">Please fill your account information</h2>
			<form>
				<div class="personalData">
					<!--
					<label>
						<select class="sel" id="userCountryCode" name="userCountryCode">
							<option selected>請選擇國籍</option>
							<option value="95">香港</option>
							<option value="148">澳門</option>
							<option value="48">中國</option>
							<option value="198">新加坡</option>
							<option value="158">馬來西亞</option>
						</select>
					</label>
					<label>
						<input type="text" class="passport" placeholder="護照英文姓名" maxlength="30" id="passport" name="passport">
					</label>
					-->
					<label id="emailLabel">
						<input type="text" class="email" placeholder="E-mail" maxlength="50" id="email" name="email" value="<?= $email ?>">
						<input type="hidden" id="isUser" value="0" /><!-- 判斷輸入的emai是否為user -->
					</label>
					<div id="memberDiv" style="font-size: 60px;color:red;">You are already Tripitta  member !!</div>
					<div id="pwdDiv">
						<label>
							<input type="password" class="pwd" placeholder="Password(six yards above)" maxlength="12" name="userPassword" id="userPassword" type="password" >
						</label>
						<label>
							<input type="password" class="pwd2" placeholder="Confirm Password" maxlength="12" name="userPassword2" id="userPassword2" type="password">
						</label>
						<label for="chk" class="clause">
							<input type="checkbox" id="chk" id="policyChk" name="policyChk" class="policyChk" checked>
							<span class="chk">
								I have read and here by <a href="javascript:openRule()">agree the Term Of Use and Privacy Policy on Tripitta.com</a>
							</span>
							<div class="overlay" id="overlay"></div>
							<div class="clauseWrap" id="clauseWrap">
								<div class="closeBtn" id="closeBtn">X</div>
								<div class="visible">
									<h1 class="title">會員服務條款</h1>
									<h2>歡迎您加入成為Tripitta的會員：</h2>
									<div>
										Tripitta（以下稱本服務）係由富爾特股份有限公司（以下稱本公司）依據本會員服務使用條款（以下稱本條款）所提供的服務，本條款訂定之目的，即在於盡可能保護會員的權益，同時確認本網站及商品供應商與會員之間的契約關係。當您完成會員加入後，表示您已經閱讀、瞭解且同意接受本條款所有的內容與約定，並完全接受本服務現有與未來衍生之服務項目。 本網站有權於任何時間基於需要而修改或變更本條款內容，修改後的條款內容將公佈在本服務『會員中心』的網站上，本網站將不會個別通知會員，建議您隨時注意相關修改與變更。您於任何修改或變更之後繼續使用本服務，將視為您已經閱讀、瞭解且同意已完成之相關修改與變更。如果您不同意本條款的內容，您應立即停止使用本服務。
									</div>
									<h2>網站個資蒐集告知</h2>
									<h3>一、蒐集目的及方式</h3>
									<div>
										<ol class="zh">
											<li>為您提供服務： 如進行會員資料管理、提供各項產品訊息、物品寄送，提供優惠權益或查詢、計算、核對、提供、紀錄及決定消費者是否得享有權益服務、提供贈獎、進行內部的統計調查與分析、行銷產品、發相關活動電子報...等。
											</li>
											<li>組織運作必要： 如如辦理存/付/匯款、帳務/稅務管理、稽核/審計作業、金融交易及授權或其他合於營業登記項目或章程所定業務之需要等。
											</li>
										</ol>
									</div>
									<h3>二、 蒐集之個人資料類別</h3>
									<div>
										<ol>
											本網站於網站內蒐集的個人資料包括：
											<li>C001辨識個人者：如姓名、職稱、住址、工作地址、住家電話號碼、電子郵件信箱、教育程度等。</li>
											<li>C002辨識財務者：如信用卡或轉帳帳戶資訊。</li>
											<li>C003政府資料中之辨識者：如身分證字號或護照號碼(外國人)。</li>
											<li>C011個人描述：如年齡、性別、出生年月日、出生地、國籍等。</li>
											<li>C021家庭情形：如婚姻狀況、有無子女等。</li>
											<li>C036生活格調：如使用消費品之種類及服務之細節、個人或家庭之消費模式等。</li>
										</ol>
									</div>
									<h3>三、利用期間、地區、對象及方式</h3>
									<div>
										<ol class="zh">
											<li>期間：本公司將於蒐集目的之存續期間內合理利用您的個人資料。惟因依法律規定、執行業務所必須或經您書面同意者不在此限。</li>
											<li>地區：台灣境內。</li>
											<li>利用對象及方式：您的個人資料蒐集除用於本網站之會員管理、客戶管理之檢索查詢等功能外，將有以下利用：</li>
											<ul>
												<li>物品寄送：於交寄相關商品時，將您的個人資料利用於交付給相關物流、郵寄廠商用於物品寄送之目的。</li>
												<li>金融交易及授權：您所提供之財務相關資訊，將於金融交易過程 (如信用卡授權、請款、退款、轉帳) 時提交給金融機構以完成金融交易。</li>
												<li>行銷：本網站將利用您的姓名、性別、出生年月日、地址、電子郵件、行動電話門號進行本網站或合作廠商商品之宣傳行銷。</li>
											</ul>
										</ol>
									</div>
									<h3>四、您對個人資料之權利</h3>
									<div>
										您交付本網站個人資料者，依個人資料保護法得行使以下權利，您可來電洽詢本網站客服進行申請：
										<ol class="zh">
											<li>查詢、請求閱覽或請求製給複製本，惟本公司依法得酌收必要成本費用，收費標準請參閱本網站之公告。</li>
											<li>請求補充或更正，惟本公司得要求您為適當釋明。</li>
											<li>請求停止蒐集、處理或利用及請求刪除。</li>
										</ol>
										本網站將依據您提出可資確認之身分證明文件的申請，於三十天內回覆。若您委託他人代為申請者，請另外提出可資確認之身分證明，以備查核。若本網站未能於三十天內處理您的申請，將會將原因以書面方式告知您。您亦可在本網站之「會員中心」網頁登入您的帳號及密碼，線上即時查閱您的個人資料檔案。若您委託他人代為登入者，您將負完全的責任。如果您自行洩漏自己的個人資料、會員密碼或付款資料給予第三人使用，您應自行就第三人的行為負責。
									</div>
									<h3>五、若您不提供正確之個人資料予本公司，本公司將無法為您提供特定目的之相關服務。</h3><br><br>
									<h2>會員服務範圍</h2>
									<div>
										本公司所提供的服務範圍，包括 www.Tripitta.com網域下所有網站，以及未來所有可能衍生，屬於本網站並包括使用本網站所提供金流、物流與資訊流平台之所有網站、實體等服務。其服務內容包括但不限於商品買賣、內容瀏覽與利用電子郵件或其他方式進行商品行銷資訊之提供。本網站得依實際情形，增加、修改或是終止相關服務。 本網站將提供的會員服務內容包括但不限於：訂票、集購、會員電子報或任何其他將來新增之會員服務功能。若您欲修改任何會員資料或功能服務時，請至『會員中心』修改。您可透過『會員中心』查詢調閱或修改您的個人資料、查詢訂票紀錄、或查詢歸戶之票券紀錄。
									</div>
									<h2>會員帳號、密碼與安全</h2>
									<div>
										本網站對於您所登錄或留存之個人資料，在未獲得您的同意以前，絕不對非本網站相關業務合作夥伴以外之人揭露您的姓名、手機門號、電子郵件地址及其他依法受保護之個人資料進行蒐集目的外之個人資料利用。  同時為提供行銷、市場分析、統計或研究、或為提供會員個人化服務或加值服務之目的，您同意本網站得記錄、保存、並利用您在本網站所留存或產生之資料及記錄，同時在不揭露各該資料之情形下，得公開或使用統計資料。 在下列的情況下，本網站有可能會提供您的個人資料給相關機關，或主張其權利受侵害並提示司法機關正式文件之第三人：
										<ol class="zh">
											<li>基於法律之規定、或受司法機關與其他有權機關基於法定程序之要求； </li>
											<li>為增進公共利益；</li>
											<li>為維護其他會員或第三人權益之重大危害；</li>
											<li>為免除您生命、身體、自由或財產上之危險； 關於您所登錄或留存之個人資料及其他特定資料（例如交易資料），悉依本網站「隱私權政策」受到保護與規範。
											</li>
										</ol>
									</div>
									<h2>智慧財產權</h2>
									<div>本服務所使用之軟體或程式、網站上所有內容，包括但不限於著作、圖片、檔案、資訊、資料、網站架構、網站畫面的安排、網頁設計，除本公司有特別約定外，皆由本公司或其他權利人依法擁有其智慧財產權，包括但不限於商標權、專利權、著作權、營業秘密與專有技術等。  任何人不得擅自使用、修改、重製、公開播送、改作、散布、發行、公開發表、進行還原工程、解編或反向組譯。任何人欲引用或轉載前述軟體、程式或網站內容，必須依法取得本公司或其他權利人的事前書面同意。如有違反，您應對本公司負損害賠償責任（包括但不限於訴訟費用及律師費用等）。
									</div>
									<h2>責任限制</h2>
									<div>
										本公司以目前一般認為合理之方式及技術，維護本服務之正常運作；但在下列情況之下，本公司將有權暫停或中斷本服務之全部或一部，且對使用者任何直接或間接之損害，均不負任何賠償或補償之責任：
										<ol class="zh">
											<li>對本服務相關軟硬體設備進行搬遷、更換、升級、保養或維修時；</li>
											<li>使用者有任何違反政府法令或本使用條款情形； </li>
											<li>天災或其他不可抗力之因素所導致之服務停止或中斷；</li>
											<li>其他不可歸責於本公司之事由所致之服務停止或中斷；</li>
											<li>非本公司所得控制之事由而致本服務資訊顯示不正確、或遭偽造、竄改、刪除或擷取、或致系統中斷或不能正常運作時。</li>
										</ol>
										本公司針對可預知之軟硬體維護工作，有可能導致系統中斷或是暫停者，將會於該狀況發生前，以適當之方式告知會員。
									</div>
									<h2>會員身份終止與本公司通知之義務</h2>
									<div>
										除非您有前述提供錯誤或不實資料進行會員登錄、未經本人許可而盜刷其信用卡、或其他經查證屬實之不法情事，本公司得終止您的使用本網站權利及會員帳號。 在您決定取消本公司會員資格，並以電子郵件或透過本公司所提供之線上服務等方式通知本公司取消您的會員資格後，將自停止本公司會員身份之日起（以本公司電子郵件發出日期為準），喪失所有本服務所提供之優惠及權益。 您與本公司之權利義務關係，應依網路交易指導原則及中華民國法律定之；若發生任何爭議，以台灣台北地方法院為第一審管轄法院。本公司的任何聲明、條款如有未盡完善之處，將以最大誠意，依誠實信用、平等互惠原則，共商解決之道。
									</div>
									<h2>網頁及Cookies之使用</h2>
									<div>
										<ol>
											<li>本網站的網頁可能提供其他網站的網路連結，您也可經由本網站所提供的連結，點選進入其他網站。但本網站並不保護您於該連結網站中的隱私權。</li>
											<li>當您於本網站站內或其附屬網站中瀏覽或查詢時，伺服器將自動紀錄您使用連線之IP位置、時間及瀏覽相關記錄。這些資料僅供作流量統計分析及網路服務優化，以便於改善服務品質，這些資料僅作為總量上的分析，不會和特定個人相連繫。</li>
											<li>為提供您更完善的個人化服務，本網站可能會使用Cookie以紀錄及分析使用者行為，此系統能夠辨識使用者，例如依您偏好的特定種類資料執行不同動作。</li>
										</ol>
									</div>
									<h2>隱私權保護政策修訂</h2>
									<div>本公司隱私權保護政策將因應需求隨時進行修正，以落實保障使用者隱私權之立意。修正後的條款將刊登於本網站上。</div>
									<h2>本條款之效力、準據法與管轄法院</h2>
									<div>
										本條款中，任何條款之全部或一部份無效時，不影響其他約定之效力。  您與本網站之權利義務關係，應依網路交易指導原則及中華民國法律定之；若發生任何爭議，以台灣台北地方法院為第一審管轄法院。本網站的任何聲明、條款如有未盡完善之處，將以最大誠意，依誠實信用、平等互惠原則，共商解決之道。
									</div>
								</div>
							</div>
						</label>
					</div>
				</div>
				<!-- credit card -->
				<div class="payment">
					<label>
						<select class="paySel" id="cardType" name="cardType">
							<option value="" >請選擇付款方式</option>
							<option value="32" selected>Credit Card (VISA/Master/JCB)</option>
							<!--<option value="31">支付寶</option>-->
						</select>
					</label>
					<div id="payDiv">
					<h4 style="font-size: 3rem;margin-bottom: 15px;">Credit Card No.</h4>
					<div style="padding-bottom: 25px;">
						<div class="cardNumGroup">
							<input type="number" class="n1" maxlength="4" name="ccNo1" id="ccNo1" onkeyup="if(this.value.length == 4)$('#ccNo2').focus();" max="9999">
							<div> - </div>
							<input type="number" class="n2" maxlength="4" name="ccNo2" id="ccNo2" onkeyup="if(this.value.length == 4)$('#ccNo3').focus();" max="9999">
							<div> - </div>
							<input type="number" class="n3" maxlength="4" name="ccNo3" id="ccNo3" onkeyup="if(this.value.length == 4)$('#ccNo4').focus();" max="9999">
							<div> - </div>
							<input type="number" class="n4" maxlength="4" name="ccNo4" id="ccNo4" onkeyup="if(this.value.length == 4)$('#ccExpMonth').focus();" max="9999">
						</div>
					</div>
					<div class="hasFlex" style="padding-bottom: 25px;">
						<h5 class="titleEng">Expiry date</h5>
						<select class="expireMonthSel" id="ccExpMonth" name="ccExpMonth">
							<option value="01">01</option>
							<option value="02">02</option>
							<option value="03">03</option>
							<option value="04">04</option>
							<option value="05">05</option>
							<option value="06">06</option>
							<option value="07">07</option>
							<option value="08">08</option>
							<option value="09">09</option>
							<option value="10">10</option>
							<option value="11">11</option>
							<option value="12">12</option>
						</select>
						<select class="expireYearSel" id="ccExpYear" name="ccExpYear">
							<?php
							$thisYear = date('Y');
							for ($i = $thisYear; $i < $thisYear + 12; $i++) echo '<option value=', $i, '>', $i, '</option>';
							?>
						</select>
					</div>
					<label class="hasFlex">
						<h5 class="titleEng">Security code</h5>
						<input type="number" autocomplete="off" maxlength="3" class="secureCode" name="cvc2" id="cvc2" max="999">
						<img src="/event/card4g/img/card.svg" alt="">
					</label>
					</div>
					<label class="hasFlex">
						<h5 class="titleEng">enter the number</h5>
						<input type="number" autocomplete="off" id="captchaCode" name="captchaCode" class="captcha" maxlength="10">
						<img class="service-pic" id="capImg" src="/web/ajax/authimg.php?authType=user" width="318.5px"/>
					</label>
					<label class="renewCap">
						<div class="btnWrap" onclick="refreshCaptcha();">
							<i class="fa fa-repeat"></i>Regenerate
						</div>
					</label>
					<label for="receipt" class="receiptDonate">
						<input type="checkbox" id="receipt" name="receipt" checked>
						<span>Agree the invoice giving to Sunshine Social Welfare Foundation.</span>
					</label>
				</div>
			</form>
			<!--
			<div class="notice">
				<h4>請注意：</h4>
				<ul>
					<li>您選擇「支付寶」付款，當您按下「確認付款訂 購」，即進入支付寶扣款系統，只要您有支付寶 賬戶且賬戶內餘額充足，即可付款成功完成訂房。</li>
					<li>若賬戶內餘額不足，無法即時完成付款，即可能會 導致無法完成訂購。請務必先完成充值，再進入開 始訂購付款。</li>
					<li>同時請務必於完成付款後，等待跳轉導回【完成訂購】網頁，才為成功訂購。</li>
				</ul>
			</div>
			-->
			<div class="btnWrap">
				<button type="button" class="submit" id="cheakdata" >Pay Now</button>
			</div>
		</form>
		</main>
	</div>
</body>
</html>