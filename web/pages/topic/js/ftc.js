var ftc = {
	ua: navigator.userAgent.toLowerCase(),
	msie: ["msie 6", "msie 7", "msie 8", "msie 9", "msie 10"],
	host: location.host == "demo.ezding.com.tw" ? true : false,
	//http: location.protocol=="http:" ? "http://" : "https://",
	//host: location.hostname == "localhost" ? "api-alpha.ezding.com.tw" : "api.ezding.com.tw",
	temp: "",
	num: 0,

	detectBrowser: function(){
		for (var i = 0; i < this.msie.length; i++) {
			if (this.ua.search(this.msie[i]) != -1) {
				alert("您的瀏覽器支援度不足，建議您下載 Firefox 或 Chrome瀏覽網頁，它會是您最佳的選擇！\n\n" + "按下確定後將自動轉到下載頁。")
				location.href = "http://ftp.mozilla.org/pub/mozilla.org/firefox/releases/12.0/win32/zh-TW/Firefox%20Setup%2012.0.exe"
			}
		}		
	},
	embed: function () {
		if(this.host){
			$("header").load("/pitta01/layout/sec/embed/header.htm");
			$("footer").load("/pitta01/layout/sec/embed/footer.htm");
		} 	
	},
	init: function(){
		this.detectBrowser();
		this.embed();
	}
}

ftc.init();


//google map callback
function footerMapInit() {
	$('.g-map').each(function(e) {
		var lat = parseFloat($(this).data('lat')) || 0;
		var lng = parseFloat($(this).data('lng')) || 0;
		console.log(lat, lng);
		var mapOptions = {
			zoom: 16,
			center: new google.maps.LatLng(lat, lng),
			disableDefaultUI: true,
			draggable: true,
			scrollwheel: true,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		var map = new google.maps.Map(this, mapOptions);
		var marker = new google.maps.Marker({
			position: new google.maps.LatLng(lat, lng),
			map: map
		});
		marker.setMap(map);

		$(window).load(function() {
			google.maps.event.trigger(map, 'resize');
		})
	});
}



$(function(){
	ftc.init();

	if($(".social-button").length){
		var desc = $("meta[name=description]").attr("content"),
			win = "height=450, width=640, toolbar=no, menubar=no, scrollbars=no, resizable=yes,location=no, status=no";
		$("#fbShare").click(function() {
			// var p = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(location.href);
			// window.open(p, "sharing", win)
			FB.ui({
					method: 'share',
					href: location.href,
				}, function(response){});
		})

		$("#twitterShare").click(function() {
			var p = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(desc) + '&url=' + encodeURIComponent(location.href);
			window.open(p, "sharing", win)
		})

		$("#weiboShare").click(function() {
			var p = 'http://v.t.sina.com.cn/share/share.php?title=' + encodeURIComponent(desc) + '&url=' + encodeURIComponent(location.href);
			window.open(p, "sharing", win)
		})

		$("#weixinShare").click(function() {
			var Qcode = "https://chart.googleapis.com/chart?cht=qr&chl=" + encodeURIComponent(location.href) + "&chs=180x180&choe=UTF-8&chld=L|2"
			$(".weixinQcode img").attr({ src: Qcode});
			$(".weixinQcode").fadeIn(300);
			$(".overlay").css({ display: "block" });
		})

		//closed
		$(".weixinQcode .fa").click(function() {
			$(".weixinQcode").hide();
			$(".overlay").css({ display: "none" });
		})			
	}


	//Topic plan - tainan
	if($(".topic4-container").length){
		$(window).load(function() {
			$('.flexslider').eq(0).flexslider({
				animation: "slide"
			});
		});
		$('.js-tab').each(function(index) {
			$(this).on("click", function() {
				$('#planing-tab-content' + (index + 1)).find('.flexslider').flexslider({
					animation: "slide"
				});
				$map = $('#planing-tab-content' + (index + 1)).find('.g-map'),
					lat = parseFloat($map.data('lat')) || 0,
					lng = parseFloat($map.data('lng')) || 0;

				var mapOptions = {
					zoom: 16,
					center: new google.maps.LatLng(lat, lng),
					disableDefaultUI: true,
					draggable: true,
					scrollwheel: true,
					mapTypeId: google.maps.MapTypeId.ROADMAP
				};
				var map = new google.maps.Map($map[0], mapOptions);
				var marker = new google.maps.Marker({
					position: new google.maps.LatLng(lat, lng),
					map: map
				});
				marker.setMap(map);
			});
		});


		// Google Map
		var script = document.createElement('script');
			script.type = 'text/javascript';
			script.src = 'https://maps.googleapis.com/maps/api/js?v=3.exp&callback=footerMapInit';
		document.body.appendChild(script);
	}


	//Topic plan - bicycle
	if($(".topic5-container").length){
		//bicycle station
		ftc.temp = ["s2", "s5", "s7"];
		$.each(ftc.temp, function (i, item) {
			$("." + item + " .wall").slidesjs({
				width: 400,
				height: 195,
				play: { active: false, auto: false },
				navigation: { active: false }
			});				
		})

		//bicycle slide page

		$( ".slide_btn" ).click(function() {
			var secName = $( this ).data("secid" );
			$( "section" ).each(function( index ) {
				$( this ).fadeOut();
			});
			$( secName ).fadeIn().css( "display", "flex" );
		});
	}

	//Topic plan - HongKong
	if($(".topic8-container").length){
		//Hong Kong station
		var index_num = 70000;
		$( "ul.personslist > li" ).each(function( index, element ) {
			if ( index == 3) {
				$( this).children(".slide_lbtn").show();
				$( this).children(".slide_rbtn").show();
			}
			else {
				$( this).children(".slide_lbtn").hide();
				$( this).children(".slide_rbtn").hide();
			}
		});

		$( ".slide_rbtn" ).click(function() {
			index_num = index_num + 1;
			personalData(index_num);
			$( "ul.personslist > li" ).each(function( index, element ) {
				var number = (index + index_num) % 7;
				switch(number) {
					case 0:
						$( this).animate({ 
							"left": "0px",
							"z-index": "1"
						}, "slow" );
						$( this).children("img").animate({
							"height": "200px",
							"width": "200px"
						}, "slow" );
						break;
					case 1:
						$( this).animate({ 
							"top" : "50px",
							"left": "155px",
							"z-index": "2"
						}, "slow" );
						$( this).children("img").animate({ 
							"height": "200px",
							"width": "200px"
						}, "slow" );
						break;
					case 2:
						$( this).animate({ 
							"top" : "50px",
							"left": "310px",
							"z-index": "3"
						}, "slow" );
						$( this).children("img").animate({ 
							"height": "200px",
							"width": "200px"
						}, "slow" );
						$( this).children(".slide_lbtn").hide();
						$( this).children(".slide_rbtn").hide();
						break;
					case 3:
						$( this).animate({ 
							"top" : "0px",
							"left": "455px",
							"z-index": "4"
						}, "slow" );
						$( this).children("img").animate({ 
							"height": "300px",
							"width": "300px"
						}, "slow" );
						$( this).children(".slide_lbtn").delay(600).show(0);
						$( this).children(".slide_rbtn").delay(600).show(0);
						break;
					case 4:
						$( this).animate({ 
							"top" : "50px",
							"left": "715px",
							"z-index": "3"
						}, "slow" );
						$( this).children("img").animate({ 
							"height": "200px",
							"width": "200px"
						}, "slow" );
						$( this).children(".slide_lbtn").hide();
						$( this).children(".slide_rbtn").hide();
						break;
					case 5:
						$( this).animate({ 
							"top" : "50px",
							"left": "870px",
							"z-index": "2"
						}, "slow" );
						$( this).children("img").animate({ 
							"height": "200px",
							"width": "200px"
						}, "slow" );
						break;
					case 6:
						$( this).animate({ 
							"top" : "50px",
							"left": "1025px",
							"z-index": "1"
						}, "slow" );
						$( this).children("img").animate({ 
							"height": "200px",
							"width": "200px"
						}, "slow" );
						break;
				}
			});
		});

		$( ".slide_lbtn" ).click(function(){
			index_num = index_num - 1;
			personalData(index_num);
			$( "ul.personslist > li" ).each(function( index, element ) {
				var number = (index + index_num) % 7;
				switch(number) {
					case 0:
						$( this).animate({ 
							"top" : "50px",
							"left": "0px",
							"z-index": "1"
						}, "slow" );
						$( this).children("img").animate({ 
							"height": "200px",
							"width": "200px"
						}, "slow" );
						break;
					case 1:
						$( this).animate({ 
							"top" : "50px",
							"left": "155px",
							"z-index": "2"
						}, "slow" );
						$( this).children("img").animate({ 
							"height": "200px",
							"width": "200px"
						}, "slow" );
						break;
					case 2:
						$( this).animate({ 
							"top" : "50px",
							"left": "310px",
							"z-index": "3"
						}, "slow" );
						$( this).children("img").animate({ 
							"height": "200px",
							"width": "200px"
						}, "slow" );
						$( this).children(".slide_lbtn").hide();
						$( this).children(".slide_rbtn").hide();
						break;
					case 3:
						$( this).animate({ 
							"top" : "0px",
							"left": "455px",
							"z-index": "4"
						}, "slow" );
						$( this).children("img").animate({ 
							"height": "300px",
							"width": "300px",
						}, "slow" );
						$( this).children(".slide_lbtn").delay(600).show(0);
						$( this).children(".slide_rbtn").delay(600).show(0);
						break;
					case 4:
						$( this).animate({ 
							"top" : "50px",
							"left": "715px",
							"z-index": "3"
						}, "slow" );
						$( this).children("img").animate({ 
							"height": "200px",
							"width": "200px"
						}, "slow" );
						break;
					case 5:
						$( this).animate({ 
							"top" : "50px",
							"left": "870px",
							"z-index": "2"
						}, "slow" );
						$( this).children("img").animate({ 
							"height": "200px",
							"width": "200px"
						}, "slow" );
						break;
					case 6:
						$( this).animate({ 
							"top" : "50px",
							"left": "1025px",
							"z-index": "1"
						}, "slow" );
						$( this).children("img").animate({ 
							"height": "200px",
							"width": "200px"
						}, "slow" );
						break;
				}
			});
		});
		
		function personalData(number) {
			var temp = (number + 3) % 7;
			switch(temp) {
				case 6:
					$( ".personName" ).html( "連峻" );
					$( ".personJob" ).html( "心理治療師，來台時間9個月" );
					break;
				case 5:
					$( ".personName" ).html( "Willam" );
					$( ".personJob" ).html( "法務相關領域，在台時間約20年" );
					break;
				case 4:
					$( ".personName" ).html( "Flora" );
					$( ".personJob" ).html( "糕餅產業市場行銷，在台時間ㄧ年" );
					break;
				case 3:
					$( ".personName" ).html( "阿Bee" );
					$( ".personJob" ).html( "美髮造型師，在台時間約10年" );
					break;
				case 2:
					$( ".personName" ).html( "國良" );
					$( ".personJob" ).html( "台大大二交換學生，在台時間一年半" );
					break;
				case 1:
					$( ".personName" ).html( "郭卡卡" );
					$( ".personJob" ).html( "客服專員訓練師，在台時間約一年" );
					break;
				case 0:
					$( ".personName" ).html( "Luna" );
					$( ".personJob" ).html( "臨床研究員，來台時間約10年" );
					break;
			}
		}


		$( ".page2" ).hide();
		$( ".page3" ).hide();

		$( "#slide1_btn" ).click(function() {
			$( ".page2" ).fadeOut();
			$( ".page3" ).fadeOut();
			$( ".page1" ).fadeIn("slow");
		});
		$( "#slide2_btn" ).click(function() {
			$( ".page1" ).fadeOut();
			$( ".page3" ).fadeOut();
			$( ".page2" ).fadeIn();
		});
		$( "#slide3_btn" ).click(function() {
			$( ".page1" ).fadeOut();
			$( ".page2" ).fadeOut();
			$( ".page3" ).fadeIn();
		});
	}

	//Topic plan - KangXi
	if($(".topic3-container").length){
		$("img").lazyload({ effect : "fadeIn"},200);

		var btnCount = 0;
		$( "#slide1_lbtn" ).click(function() {
			btnCount = btnCount - 1;
			if( btnCount >= 0) {
				$( ".slide1").animate({ 
					"left": "+=346px"
				}, "slow" );
			}
			else{
				btnCount = 0;
			}
		});
		$( "#slide1_rbtn" ).click(function() {
			btnCount = btnCount + 1;
			if( btnCount <= 4){
				$( ".slide1").animate({ 
					"left": "-=346px"
				}, "slow" );
			}
			else {
				btnCount = 4;
			}
		});
		$( ".slide_morebtn" ).click(function() {
			initialSec();
			var FIN = $( this ).attr( "sec" );
			console.log(FIN);
			$( FIN ).fadeIn(1500);
		});

		$( ".title_frame_rightDownArrow" ).click(function() {
			initialSec();
			var FIN = $( this ).attr( "sec" );
			console.log(FIN);
			$( FIN ).fadeIn(1500);
		});
		$( ".title_frame_rightUpArrow" ).click(function() {
			initialSec();
			var FIN = $( this ).attr( "sec" );
			console.log(FIN);
			$( FIN ).fadeIn(1500);
		});

		$( "#sec4" ).fadeOut();
		$( "#sec5" ).fadeOut();
		$( "#sec6" ).fadeOut();
		$( "#sec7" ).fadeOut();
		$( "#sec8" ).fadeOut();
		$( "#sec9" ).fadeOut();
		function initialSec() {
			$( "#sec3" ).hide();
			$( "#sec4" ).hide();
			$( "#sec5" ).hide();
			$( "#sec6" ).hide();
			$( "#sec7" ).hide();
			$( "#sec8" ).hide();
			$( "#sec9" ).hide();
		}

		$(function() {
			var galleries = $('.ad-gallery').adGallery();

			$('#switch-effect').change(
				function() {
					galleries[0].settings.effect = $(this).val();
					return false;
				}
			);
			$('#toggle-slideshow').click(
				function() {
					galleries[0].slideshow.toggle();
					return false;
				}
			);
			$('#toggle-description').click(
				function() {
					if(!galleries[0].settings.description_wrapper) {
						galleries[0].settings.description_wrapper = $('#descriptions');
					} else {
						galleries[0].settings.description_wrapper = false;
					}
					return false;
				}
			);
		});
	}

	//Topic plan - newyear2016
	if($(".topic6-container").length){
		$(function() {
			//init page
			$( "#sec4, #sec5, #sec6, #sec7, #sec8, #sec9, #sec10, #sec11, #sec12" ).fadeOut();

			var Sec9btnCount = 0;
			$( "#slide9_lbtn" ).click(function() {
				Sec9btnCount = Sec9btnCount - 1;
				if( Sec9btnCount >= 0) {
					$( "#Sec9Slide").animate({ 
						"left": "+=326px"
					}, "slow" );
				}
				else{
					Sec9btnCount = 4;
					$( "#Sec9Slide").animate({ 
						"left": "-1312px"
					}, "slow" );
				}
			});
			$( "#slide9_rbtn" ).click(function() {
				Sec9btnCount = Sec9btnCount + 1;
				if( Sec9btnCount <= 4){
					$( "#Sec9Slide").animate({ 
						"left": "-=326px"
					}, "slow" );
				}
				else {
					Sec9btnCount = 0;
					$( "#Sec9Slide").animate({ 
						"left": "-8px"
					}, "slow" );
				}
			});

			var Sec12btnCount = 0;
			$( "#slide12_lbtn" ).click(function() {
				Sec12btnCount = Sec12btnCount - 1;
				if( Sec12btnCount == 0) {
					$( "#Sec12Slide").animate({ 
						"left": "+=326px"
					}, "slow" );
				}
				else{
					Sec12btnCount = 1;
					$( "#Sec12Slide").animate({ 
						"left": "-334px"
					}, "slow" );
				}
			});
			$( "#slide12_rbtn" ).click(function() {
				Sec12btnCount = Sec12btnCount + 1;
				if( Sec12btnCount == 1){
					$( "#Sec12Slide").animate({ 
						"left": "-=326px"
					}, "slow" );
				}
				else {
					Sec12btnCount = 0;
					$( "#Sec12Slide").animate({ 
						"left": "-8px"
					}, "slow" );
				}
			});

			var galleries = $('.ad-gallery').adGallery();

			$('#switch-effect').change(
				function() {
					galleries[0].settings.effect = $(this).val();
					return false;
				}
			);

			$('#toggle-slideshow').click(
				function() {
					galleries[0].slideshow.toggle();
					return false;
				}
			);
			$('#toggle-description').click(
				function() {
					if(!galleries[0].settings.description_wrapper) {
						galleries[0].settings.description_wrapper = $('#descriptions');
					} else {
						galleries[0].settings.description_wrapper = false;
					}
					return false;
				}
			);

			var totalPageNumber = 12
			var secNumber = 3;
			$( ".BtnPrev" ).click(function() {
				secNumber = secNumber - 1;
				if ( secNumber < 3 )
					secNumber = totalPageNumber;
				var tempPage = "#sec" + secNumber;
				var tempPageText = (secNumber - 2) + " / 10";
				$( ".Page" ).text( tempPageText );
				HideInit();
				$( tempPage ).fadeIn(2000);
				var $body = (window.opera) ? (document.compatMode == "CSS1Compat" ? $('html') : $('body')) : $('html,body');
				$body.animate({
					scrollTop: 1250
				}, 1000);
			});
			$( ".BtnNext" ).click(function() {
				secNumber = secNumber + 1;
				if ( secNumber > totalPageNumber )
					secNumber = 3;
				var tempPage = "#sec" + secNumber;
				var tempPageText = (secNumber - 2) + " / 10";
				$( ".Page" ).text( tempPageText );
				HideInit();
				$( tempPage ).fadeIn(2000);
				var $body = (window.opera) ? (document.compatMode == "CSS1Compat" ? $('html') : $('body')) : $('html,body');
				$body.animate({
					scrollTop: 1250
				}, 1000);
			});
		});
		function HideInit(){
			$( "#sec3, #sec4, #sec5, #sec6, #sec7, #sec8, #sec9, #sec10, #sec11, #sec12" ).hide();
		}
	}

	//Topic plan - KangXi II
	if($(".topic7-container").length){
		$("img").lazyload({ effect : "fadeIn"},200);

		var btnPressCount = 0;
		var figNumber = ($( "div.slide1 > figure" ).length - 3 );
		var widthArr = [];
		var slide1Position = 0;
		$( "div.slide1 > figure" ).each(function( index, val){
			widthArr[ index ] = $( this ).outerWidth(true);
			//console.log( widthArr );
		});
		$( "#slide1_lbtn" ).click(function() {
			btnPressCount = btnPressCount - 1;
			if( btnPressCount >= 0) {
				slide1Position = slide1Position + widthArr[ (btnPressCount + 1) ];
				$( ".slide1").animate({ 
					"left": slide1Position
				}, "slow" );
			}
			else{
				btnPressCount = 0;
			}
		});
		$( "#slide1_rbtn" ).click(function() {
			btnPressCount = btnPressCount + 1;
			if( btnPressCount <= figNumber ){
				slide1Position = slide1Position - widthArr[ (btnPressCount - 1) ];
				$( ".slide1").animate({ 
					"left": slide1Position
				}, "slow" );
			}
			else {
				btnPressCount = figNumber;
			}
		});
		$( ".slide_morebtn" ).click(function() {
			initialSec();
			var FIN = $( this ).attr( "sec" );
			console.log(FIN);
			$( FIN ).fadeIn(1500);
			var $body = (window.opera) ? (document.compatMode == "CSS1Compat" ? $('html') : $('body')) : $('html,body');
			$body.animate({
				scrollTop: 1900
			}, 1000);
		});

		$( ".title_frame_rightDownArrow" ).click(function() {
			initialSec();
			var FIN = $( this ).attr( "sec" );
			console.log(FIN);
			$( FIN ).fadeIn(1500);
		});
		$( ".title_frame_rightUpArrow" ).click(function() {
			initialSec();
			var FIN = $( this ).attr( "sec" );
			console.log(FIN);
			$( FIN ).fadeIn(1500);
		});

		$( "#sec4, #sec5, #sec6, #sec7, #sec8" ).fadeOut();
		function initialSec() {
			$( "#sec3, #sec4, #sec5, #sec6, #sec7, #sec8" ).hide();
		}

		$(function() {
			var galleries = $('.ad-gallery').adGallery();

			$('#switch-effect').change(
				function() {
					galleries[0].settings.effect = $(this).val();
					return false;
				}
			);
			$('#toggle-slideshow').click(
				function() {
					galleries[0].slideshow.toggle();
					return false;
				}
			);
			$('#toggle-description').click(
				function() {
					if(!galleries[0].settings.description_wrapper) {
						galleries[0].settings.description_wrapper = $('#descriptions');
					} else {
						galleries[0].settings.description_wrapper = false;
					}
					return false;
				}
			);
		});
	}

	//Topic plan - sakura
	if($(".topic12-container").length){
		$("img").lazyload({ effect : "fadeIn"},200);

		var btnPressCount = 0;
		var figNumber = ($( "div.slide1 > figure" ).length - 3 );
		var widthArr = [];
		var slide1Position = 0;
		$( "div.slide1 > figure" ).each(function( index, val){
			widthArr[ index ] = $( this ).outerWidth(true);
			//console.log( widthArr );
		});
		$( "#slide1_lbtn" ).click(function() {
			btnPressCount = btnPressCount - 1;
			if( btnPressCount >= 0) {
				slide1Position = slide1Position + widthArr[ (btnPressCount + 1) ];
				$( ".slide1").animate({ 
					"left": slide1Position
				}, "slow" );
			}
			else{
				btnPressCount = 0;
			}
		});
		$( "#slide1_rbtn" ).click(function() {
			btnPressCount = btnPressCount + 1;
			if( btnPressCount <= figNumber ){
				slide1Position = slide1Position - widthArr[ (btnPressCount - 1) ];
				$( ".slide1").animate({ 
					"left": slide1Position
				}, "slow" );
			}
			else {
				btnPressCount = figNumber;
			}
		});
		$( ".slide_morebtn" ).click(function() {
			initialSec();
			var FIN = $( this ).attr( "sec" );
			console.log(FIN);
			$( FIN ).fadeIn(1500);
			var $body = (window.opera) ? (document.compatMode == "CSS1Compat" ? $('html') : $('body')) : $('html,body');
			$body.animate({
				scrollTop: 1900
			}, 1000);
		});

		$( ".title_frame_rightDownArrow" ).click(function() {
			initialSec();
			var FIN = $( this ).attr( "sec" );
			console.log(FIN);
			$( FIN ).fadeIn(1500);
		});
		$( ".title_frame_rightUpArrow" ).click(function() {
			initialSec();
			var FIN = $( this ).attr( "sec" );
			console.log(FIN);
			$( FIN ).fadeIn(1500);
		});

		$( "#sec4, #sec5, #sec6, #sec7, #sec8, #sec9, #sec10" ).fadeOut();
		function initialSec() {
			$( "#sec3, #sec4, #sec5, #sec6, #sec7, #sec8, #sec9, #sec10" ).hide();
		}

		$(function() {
			var galleries = $('.ad-gallery').adGallery();

			$('#switch-effect').change(
				function() {
					galleries[0].settings.effect = $(this).val();
					return false;
				}
			);
			$('#toggle-slideshow').click(
				function() {
					galleries[0].slideshow.toggle();
					return false;
				}
			);
			$('#toggle-description').click(
				function() {
					if(!galleries[0].settings.description_wrapper) {
						galleries[0].settings.description_wrapper = $('#descriptions');
					} else {
						galleries[0].settings.description_wrapper = false;
					}
					return false;
				}
			);
		});
	}

	//Topic plan vol13
	if($(".topic13-container").length){
		$("img").lazyload({ effect : "fadeIn"},200);

		var btnPressCount = 0;
		var figNumber = ($( "div.slide1 > figure" ).length - 3 );
		var widthArr = [];
		var slide1Position = 0;
		$( "div.slide1 > figure" ).each(function( index, val){
			widthArr[ index ] = $( this ).outerWidth(true);
			//console.log( widthArr );
		});
		$( "#slide1_lbtn" ).click(function() {
			btnPressCount = btnPressCount - 1;
			if( btnPressCount >= 0) {
				slide1Position = slide1Position + widthArr[ (btnPressCount + 1) ];
				$( ".slide1").animate({ 
					"left": slide1Position
				}, "slow" );
			}
			else{
				btnPressCount = 0;
			}
		});
		$( "#slide1_rbtn" ).click(function() {
			btnPressCount = btnPressCount + 1;
			if( btnPressCount <= figNumber ){
				slide1Position = slide1Position - widthArr[ (btnPressCount - 1) ];
				$( ".slide1").animate({ 
					"left": slide1Position
				}, "slow" );
			}
			else {
				btnPressCount = figNumber;
			}
		});
		$( ".slide_morebtn" ).click(function() {
			initialSec();
			var FIN = $( this ).attr( "sec" );
			console.log(FIN);
			$( FIN ).fadeIn(1500);
			var $body = (window.opera) ? (document.compatMode == "CSS1Compat" ? $('html') : $('body')) : $('html,body');
			$body.animate({
				scrollTop: 1900
			}, 1000);
		});

		$( ".title_frame_rightDownArrow" ).click(function() {
			initialSec();
			var FIN = $( this ).attr( "sec" );
			console.log(FIN);
			$( FIN ).fadeIn(1500);
		});
		$( ".title_frame_rightUpArrow" ).click(function() {
			initialSec();
			var FIN = $( this ).attr( "sec" );
			console.log(FIN);
			$( FIN ).fadeIn(1500);
		});

		$( "#sec4, #sec5, #sec6, #sec7, #sec8").fadeOut();
		function initialSec() {
			$( "#sec3, #sec4, #sec5, #sec6, #sec7, #sec8" ).hide();
		}

		$(function() {
			var galleries = $('.ad-gallery').adGallery();

			$('#switch-effect').change(
				function() {
					galleries[0].settings.effect = $(this).val();
					return false;
				}
			);
			$('#toggle-slideshow').click(
				function() {
					galleries[0].slideshow.toggle();
					return false;
				}
			);
			$('#toggle-description').click(
				function() {
					if(!galleries[0].settings.description_wrapper) {
						galleries[0].settings.description_wrapper = $('#descriptions');
					} else {
						galleries[0].settings.description_wrapper = false;
					}
					return false;
				}
			);
		});
	}
})