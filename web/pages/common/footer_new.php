<div class="footer-container">
	<div class="block">
		<div class="companyInfo">
			<div class="infoWrap">
				<div class="img-logo-white"></div>
				<div href="" class="iconBlock">
					<a href="https://www.facebook.com/Tripitta" class="wrap">
						<i class="fa fa-facebook" aria-hidden="true"></i>
					</a>
					<!--
					<a href="javascript:void(0)" class="wrap">
						<i class="fa fa-google-plus" aria-hidden="true"></i>
					</a>
					-->
					<a href="https://www.instagram.com/tripitta/" class="wrap">
						<i class="fa fa-instagram" aria-hidden="true"></i>
					</a>
				</div>
			</div>
			<div class="copyright">
				<div>Copyright Since 2016</div>
				<div>富爾特科技股份有限公司版權所有轉載必究</div>
			</div>
		</div>
		<div class="connect">
			<div class="cList">
				<a href="/service/" class="blk">
					網站QA
				</a>
				<a href="/terms/" class="blk">
					服務條款
				</a>
				<a href="/privacy/" class="blk">
					隱私政策
				</a>
				<a href="/contact/" class="blk">
					聯繫我們
				</a>
				<a href="/web/pages/about/business.php" class="blk">
					合作夥伴
				</a>
				<a href="/about/" class="blk">
					關於我們
				</a>
				<a href="/web/pages/about/price_guarantee.php" class="blk">
					買貴退三倍差價
				</a>
			</div>
		</div>
		<!-- div class="qrcode">
			<div class="qWrap">
				<img src="http://placehold.it/80x80">
				<div class="qText">android</div>
			</div>
			<div class="qWrap">
				<img src="http://placehold.it/80x80">
				<div class="qText">IOS</div>
			</div>
		</div -->
	</div>
</div>

<!-- common use -->
<!-- overlay -->
<div class="overlay"></div>

<!-- Register step 1 popup -->
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
		<div class="label gPlus" id="popup_register_btn_gplus_register">
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
				<a href="javascript:void(0)" id="popup_login_right_now_btn_register" class="btn">馬上登入</a>
			</div>
		</div>
	</div>
</div>

<!-- Register step 2 popup -->
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
		<div class="label gPlus" id="popup_login_btn_gplus">
			<div class="icon">
				<i class="fa fa-google-plus" aria-hidden="true"></i>
			</div>
			<div class="input">
				使用Google+登入
			</div>
		</div>
		<div class="btnWrap moreBlock" id="popup_login_right_now_btn_login">
			<button class="submit">tripitta帳號登入</button>
		</div>
		<div class="bottom">
			<div class="notyet">
				尚未有帳戶？
				<a href="javascript:void(0)" id="popup_login_btn_register_login" class="btn">馬上註冊</a>
			</div>
		</div>
	</div>
</div>

<!-- login step 2 popup -->
<div class="popupLogin1">
	<div class="closeBtn" id="closeBtn2">
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

<!-- mail sended -->
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

<!-- forget password popup -->
<div class="popupForgetPwd">
	<div class="closeBtn" id="closeBtn3">
		<i class="fa fa-times" aria-hidden="true"></i>
	</div>
	<h4>忘記密碼</h4>
	<div class="wrapper">
		<div class="label selected">
			<div class="icon">
				<i class="fa fa-envelope-o" aria-hidden="true"></i>
			</div>
			<div class="input">
				<input type="text" id="popup_forget_password_account" autocomplete="off" placeholder="請輸入您的信箱" maxlength="50">
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


<!-- Register successfully popup -->
<div class="popupRegiSucc">
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
			<button class="submit">回到畫面</button>
		</div>
	</div>
</div>


<!-- authentication successfully popup -->
<div class="popupAuthSucc">
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
			<button class="submit">回到畫面</button>
		</div>
	</div>
</div>


<!-- authentication failure popup -->
<div class="popupAuthFail">
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
				</div>
				<div class="input">
					<input type="text" id="auth_email" autocomplete="off" placeholder="請輸入E-mail" maxlength="50">
				</div>
			</div>
		</div>

		<div class="btnWrap">
			<button id="popup_auth_fail_back_home_page" class="goHome">回到首頁</button>
			<button id="resend_email" class="submit">重寄認證信</button>
		</div>
	</div>
</div>


<!-- 交易進行中 共用 popup -->
<div class="popupProcessing">
	<div id="floatingCirclesG">
		<div class="f_circleG" id="frotateG_01"></div>
		<div class="f_circleG" id="frotateG_02"></div>
		<div class="f_circleG" id="frotateG_03"></div>
		<div class="f_circleG" id="frotateG_04"></div>
		<div class="f_circleG" id="frotateG_05"></div>
		<div class="f_circleG" id="frotateG_06"></div>
		<div class="f_circleG" id="frotateG_07"></div>
		<div class="f_circleG" id="frotateG_08"></div>
	</div>
	<h4>交易進行中</h4>
	<h5>請勿重新整理或關閉視窗</h5>
</div>

<!-- TripAdvisor popup -->
<div id="tripadvisorPopup" class="popupTripadvisor">
	<div id="tripadvisorPopupCloseBtn" class="closeBtn">
		<i class="fa fa-times" aria-hidden="true"></i>
	</div>
	<div class="wrapper">
		<iframe id="tripadvisorPopupIframe" src="https://www.tripadvisor.com/WidgetEmbed-cdspropertydetail?locationId=6554270&partnerId=CB56EED944AF4459B7E92BBF9B292AC6&lang=zh_TW&allowMobile&display=true">
		</iframe>
	</div>	
</div>