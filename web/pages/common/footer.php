<div class="width">
	<div class="goTop" onclick="javascript:scrollToConvas('#heads');">
		<i class="fa fa-angle-up fa-3"></i>
	</div>
	<div class="coLink">
		<div class="title">合作夥伴</div>
		<a class="coFrame" href="https://www.tripadvisor.com.tw/" target="_blank">
			<div class="cooperator">
				<img src="/web/img/sec/embed/tripadvisor.png">
			</div>
		</a>
		<a class="coFrame" href="http://travel.ezding.com.tw/" target="_blank">
			<div class="cooperator">
				<img src="/web/img/sec/embed/ezding.png">
			</div>
		</a>
	</div>
	<ul class="mapWall">
		<li class="channel">
			<a href="/trip/">行程遊記</a>
		</li>
		<li class="sTitle">
			<a href="/location/">觀光指南</a>
			<a href="/booking/">旅宿預訂</a>
			<a href="https://www.idealcard.com.tw/prepaidsim/index.php?campaign=tripitta" target="_blank">4G卡預訂</a>
		</li>
		<li class="area">
			<h4>地區介紹</h4>
			<a href="/area/taipei/">台北</a>
			<a href="/area/taichung/">台中</a>
			<a href="/area/tainan/">台南</a>
			<a href="/area/kenting/">墾丁</a>
			<a href="/area/kaohsiung/">高雄</a>
			<a href="/area/hualien/">花蓮</a>
		</li>
		<li>
			<h4>交通資訊</h4>
			<div class="trafWrap">
				<div>
					<h5>陸上交通查詢/訂票</h5>
					<a href="http://www.thsrc.com.tw/index.html" target="_blank">高鐵</a>
					<a href="http://twtraffic.tra.gov.tw/twrail/" target="_blank">台鐵</a>
					<a href="http://www.taiwantrip.com.tw/" target="_blank">台灣好行觀光巴士</a>
				</div>
				<div>
					<h5>國內航空查詢/訂票</h5>
					<a href="http://www.mandarin-airlines.com/" target="_blank">華信</a>
					<a href="http://www.tna.com.tw/" target="_blank">復興</a>
					<a href="https://www.uniair.com.tw/" target="_blank">立榮</a>
				</div>
			</div>
		</li>
		<li>
			<h4>關於Tripitta</h4>
			<a href="/about/" target="_blank">關於我們</a>
			<a href="/contact/" target="_blank">聯絡我們</a>
			<a href="/privacy/" target="_blank">隱私政策</a>
			<a href="/service/">客服中心</a>
			<a href="/terms/">服務條款</a>
			<a href="/member/">會員中心</a>
			<a href="/web/pages/about/business.php">企業合作</a>
			<a href="/web/pages/about/price_guarantee.php">買貴退三倍差價</a>
		</li>
	</ul>
	<div class="copyright">
		Copyright Since 2016 富爾特科技股份有限公司 版權所有。轉載必究
	</div>
</div>
<!-- common use -->
<div class="overlay"></div>


<!-- 新增以social media的Register popup -->
<div class="popupRegister">
	<div class="closeBtn">
		<i class="fa fa-times" aria-hidden="true"></i>
	</div>
	<h4>註冊</h4>
	<div class="wrapper">
		<div class="label fBook" id="popup_register_btn_fb_register">
			<div class="icon">
				<i class="fa fa-facebook" aria-hidden="true"></i>
			</div>
			<div class="input">
				使用Facebook註冊
			</div>
		</div>
		<div class="label gPlus" id="popup_register_btn_gplus_register" data-onsuccess="onSignIn">
			<div class="icon">
				<i class="fa fa-google-plus" aria-hidden="true"></i>
			</div>
			<div class="input">
				使用Google+註冊
			</div>
		</div>
		<div class="btnWrap moreBlock">
			<button class="submit" id="popup_login_btn_register_1">註冊tripitta帳號</button>
		</div>
		<div class="bottom">
			<div class="notyet">
				已經有帳戶了？
				<a href="javascript:void(0)" id="popup_login_right_now_btn_register" class="registerBtn">馬上登入</a>
			</div>
		</div>
	</div>
</div>

<!-- 原Register popup, class名稱改為popupRegister1  -->
<div class="popupRegister1">
	<!-- 關閉btn修改 -->
	<div class="closeBtn">
		<i class="fa fa-times" aria-hidden="true"></i>
	</div>
	<h4>註冊</h4>
	<div class="wrapper">
		<!-- 原本為label 點選input時加入 selected -->
		<div class="label">
			<div class="icon">
				<i class="fa fa-envelope-o" aria-hidden="true"></i>
			</div>
			<div class="input">
				<input type="text" id="popup_register_account" autocomplete="off" placeholder="請輸入E-mail帳號" maxlength="50" value="" />
				<div class="errMsg" id="error_msg_popup_register_account"></div>
			</div>
		</div>
		<div class="label">
			<div class="icon">
				<i class="fa fa-lock" aria-hidden="true"></i>
			</div>
			<div class="input">
				<input type="password" id="popup_register_password" autocomplete="off" placeholder="請輸入密碼(格式:6~20碼英數字)" maxlength="20" value="" />
				<div class="errMsg" id="error_msg_popup_register_password"></div>
			</div>
		</div>
		<div class="label">
			<div class="icon">
				<i class="fa fa-lock" aria-hidden="true"></i>
			</div>
			<div class="input">
				<input type="password" id="popup_register_password_confirm" autocomplete="off" placeholder="請再次輸入密碼(格式:6~20碼英數字)" maxlength="20" value="" />
				<div class="errMsg" id="error_msg_popup_register_password_confirm"></div>
			</div>
		</div>
		<div class="btnWrap">
			<input type="checkbox" id="popup_register_agreement" value="1">
			<label for="popup_register_agreement">
				<span>
					已詳閱<a href="https://www.tripitta.com/terms/" target="_blank" class="policy">資料使用政策與使用條款，</a>同意使用 Tripitta 所提供的服務。
				</span>
			</label>
		</div>
		<div class="btnWrap">
			<input type="hidden" id="popup_register_nickname" autocomplete="off"value="">
			<button id="popup_register_btn_register" class="submit">註冊</button>
		</div>
	</div>
</div>

<!-- login step 1 popup -->
<div class="popupLogin">
	<div class="closeBtn">
		<i class="fa fa-times" aria-hidden="true"></i>
	</div>
	<h4>登入</h4>
	<div class="wrapper">
		<div class="label fBook" id="popup_login_btn_fb">
			<div class="icon">
				<i class="fa fa-facebook" aria-hidden="true"></i>
			</div>
			<div class="input">
				使用Facebook登入
			</div>
		</div>
		<div class="label gPlus" id="popup_login_btn_gplus" data-onsuccess="onSignIn2">
			<div class="icon">
				<i class="fa fa-google-plus" aria-hidden="true"></i>
			</div>
			<div class="input">
				使用Google+登入
			</div>
		</div>
		<div class="btnWrap moreBlock">
			<button id="popup_login_right_now_btn_login" class="submit">tripitta帳號登入</button>
		</div>
		<div class="bottom">
			<div class="notyet">
				尚未有帳戶？
				<a href="javascript:void(0)" id="popup_login_btn_register_login" class="btn">馬上註冊</a>
			</div>
		</div>
	</div>
</div>

<!-- login popup -->
<div class="popupLogin1">
	<div class="closeBtn" id="closeBtn">
		<i class="fa fa-times" aria-hidden="true"></i>
	</div>
	<h4>登入</h4>
	<div class="wrapper">
		<div class="label selected">
			<div class="icon">
				<i class="fa fa-envelope-o" aria-hidden="true"></i>
			</div>
			<div class="input">
				<input type="text" id="popup_login_account" autocomplete="off" placeholder="請輸入E-mail帳號" maxlength="50" value="">
			</div>
		</div>
		<div class="label">
			<div class="icon">
				<i class="fa fa-lock" aria-hidden="true"></i>
			</div>
			<div class="input">
				<div class="input2">
					<input type="password" id="popup_login_password" autocomplete="off" placeholder="請輸入密碼" maxlength="20" value="">
				</div>
			</div>
		</div>
		<div class="mid">
			<label>
				<input type="checkbox" id="popup_login_auto_login">記住我
			</label>
			<a href="javascript:void(0)" id="popup_login_btn_forget_password" class="forgetPwd">忘記密碼?</a>
		</div>
		<div class="btnWrap">
			<input type="button" id="popup_login_btn_login" class="submit" value="登入">
		</div>
		<div class="bottom">
			<div class="notyet">
				還沒有帳戶嗎?
				<a href="javascript:void(0)" id="popup_login_btn_register" class="registerBtn">馬上註冊</a>
			</div>
		</div>
	</div>
</div>

<!-- forget password popup -->
<div class="popupForgetPwd">
	<div class="closeBtn" id="closeBtn3">
		<i class="fa fa-times" aria-hidden="true"></i>
	</div>
	<h4>忘記密碼</h4>
	<div class="wrapper">
		<div class="label">
			<div class="icon">
				<i class="fa fa-envelope-o" aria-hidden="true"></i>
			</div>
			<div class="input">
				<input type="text" id="popup_forget_password_account" autocomplete="off" placeholder="請輸入E-mail帳號" maxlength="50">
			</div>
		</div>
		<div class="btnWrap2">
			<!-- 取消為回到登入block -->
			<button id="popup_forget_password_btn_reset_data" class="cancel">取消</button>
			<!-- 發送則到mail寄送頁面 -->
			<button id="popup_forget_password_btn_reset_password" class="submit">重設密碼</button>
		</div>
		<div class="bottom">
			<div class="notyet">
				您需要幫忙嗎?
				<a href="javascript:void(0)" id="popup_forget_password_btn_contact_us" class="registerBtn">聯絡我們</a>
			</div>
		</div>
	</div>
</div>

<!-- 新增這段 mail sended, 用於重新設定密碼與認證信用 -->
<div class="popupSended">
	<div class="closeBtn">
		<i class="fa fa-times" aria-hidden="true"></i>
	</div>
	<div class="mailImg">
		<div class="mLine"></div>
		<i class="fa fa-envelope-o" aria-hidden="true"></i>
	</div>
	<h4>信件已寄送</h4>
	<div class="wrapper">
		<!-- 根據每個人所填的mail帶入 -->
		<div class="userMail">
			<span id="forgetPasswordAccount"></span>
		</div>
		<!-- 用於遺失密碼 -->
		<div class="note">重置密碼連結已寄送至您的信箱, 請依步驟重置密碼謝謝。</div>
		<!-- 用於發送認證信 <div class="note">系統將迅速發送認證信至您的信箱，請查看您的信箱並點擊啟用。</div> -->

		<div class="btnWrap">
			<button class="goBackLogin submit">返回登入頁</button>
		</div>
	</div>
</div>

<!-- Register successfully popup -->
<div class="popupRegiSucc">
	<!-- 增加關閉按鈕 -->
	<div class="closeBtn">
		<i class="fa fa-times" aria-hidden="true"></i>
	</div>
	<h4>註冊成功</h4>
	<div class="wrapper">
		<div class="label">
			<i class="img-member-check-big"></i>
			<p>系統將迅速發送認證信至您信箱，請查看您的信箱並點擊啟用。</p>
		</div>
		<div class="btnWrap">
			<button id="popup_register_success_btn_back_to_screen" class="submit">回到畫面</button>
			<!-- <input type="button" id="popup_register_success_btn_back_to_screen" class="submit" value="回到畫面"> -->
		</div>
	</div>
</div>
<!-- authentication successfully popup -->
<div class="popupAuthSucc">
	<!-- 增加關閉按鈕 -->
	<div class="closeBtn">
		<i class="fa fa-times" aria-hidden="true"></i>
	</div>
	<h4>認證成功</h4>
	<div class="wrapper">
		<div class="label">
			<i class="img-member-check-big"></i>
			<p>感謝您的啟用，您將可獲得更多 Tripitta 提供的優惠服務。</p>
		</div>
		<div class="btnWrap">
			<button id="popup_auth_succ_back_home_page" class="submit">回到畫面</button>
			<!-- <input type="button" class="submit" id="popup_auth_succ_back_home_page" value="回到畫面"> -->
		</div>
	</div>
</div>
<!-- authentication failure popup -->
<div class="popupAuthFail">
	<!-- 增加關閉按鈕 -->
	<div class="closeBtn">
		<i class="fa fa-times" aria-hidden="true"></i>
	</div>
	<h4>認證失敗</h4>
	<div class="wrapper">
		<div class="label">
			<i class="img-member-cross-big"></i>
			<p>Oops...連結可能失效囉，請重填 E-mail 重新認證</p>
			<div class="emailAuth selected">
				<div class="icon">
					<i class="fa fa-envelope-o" aria-hidden="true"></i>
					<!-- 圖片改為font-awesome <i class="img-member-mail"></i> -->
				</div>
				<div class="input">
					<input type="text" autocomplete="off" id="auth_email" name="auth_email" placeholder="請輸入E-mail" maxlength="50">
				</div>
			</div>
		</div>
		<div class="btnWrap">
			<button id="popup_auth_fail_back_home_page" class="goHome">回到首頁</button>
			<button id="resend_email" class="submit">重寄認證信</button>
		</div>
	</div>
</div>