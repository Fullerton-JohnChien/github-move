<?php
/**
 * 說明：滿千折百
 * 作者：Steak
 * 日期：2016年6月19日
 */
require_once __DIR__ . '/../../config.php';
header("Content-Type:text/html; charset=utf-8");

?>
<!DOCTYPE html>
<html lang="zh-Hant" prefix="og: http://ogp.me/ns#">
<head>
	<?php include __DIR__ . "/../common/head_new.php"; ?>
    <link rel="stylesheet" href="/web/css/main.css">
    <link rel="stylesheet" href="/web/css/main2.css">
   	<script src="/web/js/lib/jquery/jquery.js"></script>
</head>
<body>
	<header><?php include __DIR__ . "/../common/header_new.php"; ?></header>
	<main class="otr-campaign-container">
		<div class="wrap">
			<h1>
				新會員 500元旅遊金活動
			</h1>
			<div class="bannerImg" style="background: url('/web/img/sec/otr/campaign.png') center no-repeat;"></div>
		</div>
		<h2>
			<div class="text">
				Tripitta『500元旅遊金』使用說明
			</div>
		</h2>
		<div class="rule">
			<h3>
				500元旅遊金取得方式
			</h3>
			<div class="content">
				於2016年07月12日-2016年12月31日期限內加入Tripitta網站並且通過驗證的新會員。
			</div>
			<h3>
				500元旅遊金使用期限
			</h3>
			<div class="content">
				2016年07月12日起至2018年06月30日於Tripitta網站上預訂現有服務商品，可於結帳時使用旅遊金，逾期恕無法使用旅遊金（例如：於2018年08月15日才進行預訂下單，無法使用旅遊金）；預訂服務日期在使用期限外之服務商品，亦恕無法使用旅遊金（例如：預訂2018年08月15日入住的住宿房間，亦無法使用旅遊金）。
			</div>
			<h3>
				500元旅遊金使用方式
			</h3>
			<div class="content">
				每筆訂單滿新台幣1,000元以上(含)之單筆訂單得以折抵新台幣100元旅遊金，每筆訂單滿新台幣<span class="mark">2,000</span>元以上(含)之單筆訂單得折抵<span class="mark">200</span>元旅遊金，以此類推。每個會員帳號最高可折抵新台幣500元旅遊金。
			</div>
			<h3>
				如何查詢剩餘旅遊金
			</h3>
			<div class="content">
				點擊會員名稱進入<a href="javascript:query()">會員中心→我的旅遊金</a>，即可查詢目前剩餘旅遊金金額。
			</div>
			<div class="policy">
				<ol>
					<li>
						旅遊金優惠僅限於Tripitta網站現有服務商品折抵使用，不得兌現，或轉換為本活動使用說明以外之其他使用。
					</li>
					<li>
						若取消訂單時，訂購金額與旅遊金的退款與退回，悉依各配合業者之規定。若於配合業者免費取消期間取消訂單時，皆可取回付款及取回旅遊金，再重新使用；若已於業者扣款期間取消訂單，即無法取回付款及亦無法取回旅遊金。
					</li>
					<li>
						旅遊金之折抵使用，必須於Tripitta網站訂購現有服務商品時，於結帳頁面時使用，並連同一併完成付款程序；若未於結帳時使用，或未連同一併完成付款程序，恕無法事後要求折抵。
					</li>
					<li>
						如訂購Tripitta網站現有服務商品之所在地或旅客出發地，於使用當日因天災（主管機關發佈因颱風地震產生相關重大影響等）或其他重大災害（人力不可抗力因素）影響，或有主要交通中斷情形發生，致使無法如期前往時，敬請於發佈訊息後3日內，請您儘速與本網站客服人員連繫，更改或取消您的訂單，本網站將會依各業者取消、更改規定辦理相關手續。
					</li>
					<li>
						本活動之旅遊金名額，數量有限，發完為止。
					</li>
					<li>
						本活動服務商品資訊，以Tripitta官網所刊登的內容為準，詳細資訊請參考Tripitta官網：<a href="/">www.tripitta.com</a>
					</li>
					<li>
						Tripitta將視參加本活動者，均已瞭解並願意確實遵守本活動使用說明；若有資料填寫不完整、不實或以不當方式取得資格者，主辦單位將取消其領取資格；違反者，Tripitta有權取消其領取資格。Tripitta保留隨時修改、變更、取消本活動相關內容之權利。如發生不可歸責於Tripitta之事由，而使旅遊金無法發放或是折抵（如電信通訊網路故障等等），Tripitta對此不負相關責任。
					</li>
				</ol>
			</div>
		</div>
		<!-- 手機板會先固定在底部, 當滑動到它原本的位置會改為回歸原位 -->
		<div class="btnWrap">
			<button class="btn query2">
				查詢旅遊金
			</button>
			<button class="btn" id="register">
				註冊獲得旅遊金
			</button>
		</div>
	</main>
	<footer><? include __DIR__ . "/../common/footer_new.php"; ?></footer>
   	<script type="text/javascript">
	$(function(){
		$('#register').click(function() {
			$('#register_is_click').val(1);
    		$('.overlay').show();
    		$('.popupRegister').show();
    		$(window).scrollTop("0");
		});

		$('.query2').click(function() {
            var data = '<?php echo $header_is_login; ?>';
            if(data == 1){
            	location.href = '/member/?item=bonus';
            }else{
            	show_popup_login();
            }
		});
	});

	function query() {
        var data = '<?php echo $header_is_login; ?>';
        if(data == 1){
        	location.href = '/member/?item=bonus';
        }else{
        	show_popup_login();
        }
	}
	</script>
	<script src="../../js/lib/jquery/jquery.js"></script>
	<script src="../../js/main-min.js"></script>
</body>
</html>