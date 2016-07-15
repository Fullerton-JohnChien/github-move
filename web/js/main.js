var ftc = {
	ua: navigator.userAgent.toLowerCase(),
	msie: ["msie 6", "msie 7", "msie 8", "msie 9", "msie 10"],
	temp: null,
	num: 0,

	http: location.protocol=="http:" ? "//" : "https://",
	API: function () {
		switch(location.host){
			case "alpha.www.tripitta.com":
				return "api-alpha.www.tripitta.com/"
			break;
			case "www.tripitta.com":
				return "api.www.tripitta.com/"
			break;
		}
	},
	detectBrowser: function(){
		for (var i = 0; i < this.msie.length; i++) {
			if (this.ua.search(this.msie[i]) != -1) {
				alert("您的瀏覽器支援度不足，建議您下載 Firefox 或 Chrome瀏覽網頁，它會是您最佳的選擇！\n\n" + "按下確定後將自動轉到下載頁。")
				location.href = "https://www.mozilla.org/en-US/firefox/all/"
			}
		}		
	},
	// 載入 header, footer
	embed: function () {
		//目前只針對這些區塊做新的做法
		var ary = ["assistance", "card4G", "charter", "pickup", "seeOff", "tourBus"];

		$.each(ary, function (i, item) {
			if(document.URL.search(item) != -1){
				$("body").append("<script src=../../sec/"+ item + "/js/main2.js></script>")
			}
		})

		//每個頁都載入
		function embedHTM (a) {
			$("header").load(a + "header.htm");
			$("footer").load(a + "footer.htm");
		}

		switch(location.host){
			case "localhost:8001":
				embedHTM("/tripitta-frontend/sec/embed/");
			break;
			case "demo.ezding.com.tw":
				embedHTM("/pitta2015/layout/sec/embed/");
			break;
			case "www.tripitta.com":
				embedHTM();
			break;
			case "alpha.www.tripitta.com":
				embedHTM();
			break;
		}
	},
	// append data to head
	head: function (a, b, c, d, e) {
		a = a || "Tripitta 台灣自由行旅遊網";
		b = b || "Tripitta旅必達，提供中國、香港、澳門等亞洲地區旅行者到台灣自由行旅遊攻略，民宿訂房及行程規劃。Tripitta旅必達，您最佳的行程規劃助手！";
		c = c || "index, follow";
		d = d || "width=1440";
		e = e || "";

		var meta = "";
		meta += '<title>'+ a +'</title>';
		meta += '<meta name="​​description" content="'+ b +'"> ';
		meta += '<meta name="keywords" content="台灣自由行,旅遊攻略,民宿訂房,行程規劃,觀光指南">';
		meta += '<meta name="robots" content="'+ c +'">';
		meta += '<meta http-equiv="X-UA-Compatible" content="IE=Edge">';

		meta += '<meta name="author" content="Tripitta 旅必達">';
		meta += '<meta property="og:type" content="website">';
		meta += '<meta property="og:title" content="'+ a +'">';
		meta += '<meta property="og:site_name" content="Tripitta 旅必達">';
		meta += '<meta property="og:description" content="'+ b +'">';
		meta += '<meta property="og:locale" content="zh_TW">';
		meta += '<meta property="og:url" content="https://www.tripitta.com/">';
		meta += '<meta property="og:image" content="'+ e +'">';
		meta += '<meta property="og:app_id" content="474784012720774">';
		meta += '<meta name="google-site-verification" content="HTVGwlmAfdHwjizl0l0XZ4ceMoEoOq02y0K3QaiGgzg" />';
		meta += '<link rel="stylesheet" href="../../css/main.css">';
		meta += '<link rel="stylesheet" href="../../css/main2.css">';

		meta += '<meta name="image" content="https://www.tripitta.com/web/img/logo.jpg" />';
		meta += '<meta name="thumbail" content="https://www.tripitta.com/web/img/logo.jpg" />';
		meta += '<meta name="msapplication-TileColor" content="#ffffff">';
		meta += '<meta name="msapplication-TileImage" content="https://www.tripitta.com/web/img/favicon/ms-icon-144x144.png">';
		meta += '<meta name="theme-color" content="#ffffff">';
		meta += '<link rel="apple-touch-icon" sizes="57x57" href="https://www.tripitta.com/web/img/favicon/apple-icon-57x57.png">';
		meta += '<link rel="apple-touch-icon" sizes="60x60" href="https://www.tripitta.com/web/img/favicon/apple-icon-60x60.png">';
		meta += '<link rel="apple-touch-icon" sizes="72x72" href="https://www.tripitta.com/web/img/favicon/apple-icon-72x72.png">';
		meta += '<link rel="apple-touch-icon" sizes="76x76" href="https://www.tripitta.com/web/img/favicon/apple-icon-76x76.png">';
		meta += '<link rel="apple-touch-icon" sizes="114x114" href="https://www.tripitta.com/web/img/favicon/apple-icon-114x114.png">';
		meta += '<link rel="apple-touch-icon" sizes="120x120" href="https://www.tripitta.com/web/img/favicon/apple-icon-120x120.png">';
		meta += '<link rel="apple-touch-icon" sizes="144x144" href="https://www.tripitta.com/web/img/favicon/apple-icon-144x144.png">';
		meta += '<link rel="apple-touch-icon" sizes="152x152" href="https://www.tripitta.com/web/img/favicon/apple-icon-152x152.png">';
		meta += '<link rel="apple-touch-icon" sizes="180x180" href="https://www.tripitta.com/web/img/favicon/apple-icon-180x180.png">';
		meta += '<link rel="icon" type="image/png" sizes="192x192" href="https://www.tripitta.com/web/img/favicon/android-icon-192x192.png">';
		meta += '<link rel="icon" type="image/png" sizes="32x32" href="https://www.tripitta.com/web/img/favicon/favicon-32x32.png">';
		meta += '<link rel="icon" type="image/png" sizes="96x96" href="https://www.tripitta.com/web/img/favicon/favicon-96x96.png">';
		meta += '<link rel="icon" type="image/png" sizes="16x16" href="https://www.tripitta.com/web/img/favicon/favicon-16x16.png">	';

		$("head").prepend(meta);
	},
	init: function(){
		this.detectBrowser();
		if(location.host == "localhost:8001" || location.host == "demo.ezding.com.tw"){
			this.head();
			this.embed();
		}
		this.googleTagManager();
		this.googleAnalytics();
	},
	googleTagManager: function () {
		if(location.host == "www.tripitta.com"){
			(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
			new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
			j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
			'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
			})(window,document,'script','wis_dataLayer','GTM-N565GC');		
		}
	},
	googleAnalytics: function () {
		if(location.host == "www.tripitta.com"){
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

			ga('create', 'UA-70704198-1', 'auto');
			ga('send', 'pageview');		
		}
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