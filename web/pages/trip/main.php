<?
require_once __DIR__ . '/../../config.php';
// 作者最新一筆行程遊記
// 該遊記前15張圖
//printmsg($_GET);printmsg($_POST);
?><!Doctype html>
<html lang="zh-Hant">

<head>
	<? include __DIR__ . "/../common/head.php"; ?>
	<link rel="stylesheet" type="text/css" href="/web/pages/trip/css/frame.css">
	<link rel="stylesheet" type="text/css" href="/web/pages/trip/css/page.css">
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<title>行程遊記 - Tripitta 旅必達</title>
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
	<script src="/web/js/jquery.twbsPagination.js" type="text/javascript"></script>
	<script src="/web/js/common.js" type="text/javascript"></script>
</head>
<?
$area_id = 0;
$area_code = get_val('area_code');
$days = get_num('days');

$tripitta_web_service = new tripitta_web_service();
// 轉換地區
if(!empty($area_code)) {
	$tmp_area_row = $tripitta_web_service->get_area_by_code(get_config_current_lang(), $area_code);
	if(!empty($tmp_area_row)) {
		$area_id = $tmp_area_row["a_id"];
	}
}

// 取得前三名作者
$author_list = $tripitta_web_service->find_valid_author('signed.author', 3);
// 取得前三名作者最後一篇行程遊記
foreach($author_list as $idx => $author_row) {
    $tp = $tripitta_web_service->get_last_valid_travel_plan_by_author_id($author_row["a_id"]);
    $author_list[$idx]["last_travel_plan"] = $tp;
    $tp_detail_list = $tripitta_web_service->find_travel_plan_detail_images_by_travel_plan_id($tp["tp_id"], 100, null, null, 9, 0);
    $author_list[$idx]["last_travel_plan"]["details"] = $tp_detail_list;
}
//printmsg($author_list);

// 撈取觀光區
$area_list = $tripitta_web_service->find_valid_area_for_search_by_category_and_parent_id(get_config_current_lang(), Constants::$CATEGORY_HOME_STAY, 0);

// 撈取第一層Tag
$tag_list = $tripitta_web_service->find_valid_tag_by_parent_id(get_config_current_lang(), 0);


$travel_plan_photo_path = '/travel_plan';
$author_photo_path = '/author';
if(!is_production()) {
    $travel_plan_photo_path = '/travel_plan_alpha';
    $author_photo_path = '/author_alpha';
}

// 因IE在Document Ready無法改變圖檔所以先用PHP先寫死
$main_author = $author_list[0];
if(!empty($main_author["ap_avatar"])) {
    $main_img_head = get_config_image_server() . "/photos/" . $author_photo_path . "/" . $main_author["a_id"] . "/" . $main_author["ap_avatar"] . "." . $main_author["p_content_type"];
}

$next_author = $author_list[1];
if(!empty($next_author["ap_avatar"])) {
    $next_img_head = get_config_image_server() . "/photos/" . $author_photo_path . "/" . $next_author["a_id"] . "/" . $next_author["ap_avatar"] . "." . $next_author["p_content_type"];
}
$prev_author = $author_list[2];
if(!empty($prev_author["ap_avatar"])) {
    $prev_img_head = get_config_image_server() . "/photos/" . $author_photo_path . "/" . $prev_author["a_id"] . "/" . $prev_author["ap_avatar"] . "." . $prev_author["p_content_type"];
}

// 價格排序 asc, desc
$selectPriceOrder = get_val('selectPriceOrder');
?>
<body class="main">
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<article class="top-container">
		<div class="container">
			<div class="itinerary_kv" style="background-image: url(/web/img/no-pic.jpg)"></div>
			<!-- //itinerary_kv -->
			<div class="guide">
				<div class="main-writer">
					<a href="javascript:void(0)" class="cover-photo">
						<img src="<?= $main_img_head ?>" width="180" height="180" alt="" onerror="javascript:this.src='/web/img/no-pic.jpg';">
					</a>
					<span class="category"></span>
					<span class="author"></span>
				</div>
				<!-- //main-write -->
				<h2></h2>
				<div class="status">
					<!-- <span><i class="fa fa-map-marker"></i>墾丁-東港大鵬灣</span> -->
					<span>
						<i class="fa fa-heart"></i><span id="ltp_collect"></span></span>
					<span>
						<i class="fa fa-eye"></i><span id="ltp_click"></span></span>
					<span>
						<i class="fa fa-calendar"></i><span id="ltp_date_range"></span></span>
				</div>
				<p class="abstract"></p>
				<a href="" class="readmore">了解更多</a>
				<div class="writer prev">
					<a href="javascript:seek_author(-1)">
						<div class="cover-photo">
							<img src="<?= $prev_img_head ?>" width="100" height="100" alt="" onerror="javascript:this.src='/web/img/no-pic.jpg';">
						</div>
						<span class="category"></span>
						<span class="author"></span>
					</a>
				</div>
				<!-- //write -->
				<div class="writer next">
					<a href="javascript:seek_author(1)">
						<div class="cover-photo">
							<img src="<?= $next_img_head ?>" width="100" height="100" alt="" onerror="javascript:this.src='/web/img/no-pic.jpg';">
						</div>
						<span class="category"></span>
						<span class="author"></span>
					</a>
				</div>
				<!-- //write -->
			</div>
			<!-- //guide -->
			<div class="block-swiper" id="main_photo">
				<div class="swiper-container">
					<div class="swiper-wrapper">
						<? // 這邊由javascript產生 ?>
					</div>
					<!-- Add Pagination -->
					<a id="aLeft" class="swiper-button-prev arrow-left" href="#"></a>
					<a id="aRight" class="swiper-button-next arrow-right" href="#"></a>
				</div>
			</div>
			<!-- //block-swiper -->
		</div>
		<!-- //contaner -->
	</article>
	<!-- //top-contaner -->
	<article class="suitable-trip">
		<div class="container">
			<h1>suitable trip</h1>
			<h2>找到最適合你的行程遊記</h2>
			<div class="find-bar">
				<div class="top">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td>
								<i class="fa fa-map-marker noborder"></i>
								<div class="select">
									<select name="area_id" id="area_id">
										<option value="">觀光地區</option>
										<? foreach($area_list as $area_row) { ?>
										<option value="<?= $area_row["a_id"] ?>" <?= ($area_id == $area_row["a_id"] ? " selected ":"") ?>><?= $area_row["a_name"] ?></option>
										<? } ?>
									</select>
								</div>
							</td>
							<td>
								<i class="fa fa-calendar"></i>
								<div class="select">
									<select name="q_month" id="q_month">
										<option value="">月份</option>
										<? for($i=1 ; $i<=12 ; $i++) { ?>
										<option value="<?= $i ?>"><?= $i ?>月</option>
										<? } ?>
									</select>
								</div>
							</td>
							<td>
								<i>旅遊天數</i>
							</td>
							<td class="pointer">
								<? for($i=1 ; $i<=14 ; $i++) { ?>
								<span class="day-dot " data-day="<?= $i ?>">
									<i class="tip"><?= $i ?> day</i>
								</span>
								<?} ?>
								</ul>
							</td>
							<td class="pl-15" id="total_days">共  天</td>
						</tr>
					</table>
				</div>
				<div class="tag">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td>
								<i class="fa fa-tag"></i>
							</td>
							<td>
								<ul class="tag-list">
									<? foreach ($tag_list as $tag_row) { ?>
									<li data-tag-id="<?= $tag_row["t_id"] ?>"><?= $tag_row["t_tag"] ?></li>
									<? } ?>
								</ul>
							</td>
						</tr>
					</table>
				</div>
				<!-- //tag -->
			</div>
			<div class="search-bar">
				<input type="text" name="keyword" id="keyword" placeholder="臺灣14天環島遊記, 民宿, 家庭">
				<button id="btn_search">
					<i class="fa fa-search"></i>
				</button>
			</div>
			<!-- //search-bar -->
			<div class="sort-bar">
				<div class="select">
					<select class="sort" onchange="selPriceOrder()" id="sortSelect">
						<option value="0" <?php if(empty($selectPriceOrder)){echo 'selected'; }?>>排序選擇</option>
						<option value="tp_create_time,desc" <?php if($selectPriceOrder == "tp_create_time,desc" && !empty($selectPriceOrder)){ echo 'selected'; }?>>時間高 > 低</option>
						<option value="tp_create_time,asc"  <?php if($selectPriceOrder == "tp_create_time,asc" && !empty($selectPriceOrder)){ echo 'selected'; } ?>>時間低 > 高</option>
						<option value="tpe_click_total,desc" <?php if($selectPriceOrder == "tpe_click_total,desc" && !empty($selectPriceOrder)){ echo 'selected'; }?>>點擊數高 > 低</option>
						<option value="tpe_click_total,asc"  <?php if($selectPriceOrder == "tpe_click_total,asc" && !empty($selectPriceOrder)){ echo 'selected'; } ?>>點擊數低 > 高</option>
						<option value="tpe_collect_total,desc" <?php if($selectPriceOrder == "tpe_collect_total,desc" && !empty($selectPriceOrder)){ echo 'selected'; }?>>收藏數高 > 低</option>
						<option value="tpe_collect_total,asc"  <?php if($selectPriceOrder == "tpe_collect_total,asc" && !empty($selectPriceOrder)){ echo 'selected'; } ?>>收藏數低 > 高</option>
					</select>
				</div>
				<span class="sort-result" id="total_record">共 0 筆</span>
			</div>
			<!-- //sort-bar -->
			<ol class="item-list">
				<!-- 內容透過ajax取得 -->
			</ol>
			<!-- //item-list -->

			<ul id="pagination" class="pagination"></ul>
			<div class="text-center">
				<ul id="visible-pages-example" class="pagination"></ul>
    		</div>
			<!--//Pagination-->
		</div>
		<!-- //contaner -->
	</article>
	<!-- //suitable-trip -->

	<footer class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
	<!-- Add fancyBox main JS and CSS files -->
	<script type="text/javascript" src="/web/pages/trip/js/fancybox/jquery.fancybox.js"></script>
	<link rel="stylesheet" type="text/css" href="/web/pages/trip/js/fancybox/jquery.fancybox.css" media="screen" />
	<script src="/web/pages/trip/js/jquery.easing.1.3.js"></script>
	<script src="/web/pages/trip/js/actions.js"></script>
	<script>

	</script>
</body>

</html>
<script>
var def_area_id = '<?= $area_id ?>';
var def_days = '<?= $days ?>';
var author_pos = 0;
var author_list = <?= json_encode($author_list) ?>;
var image_server_url = '<?= get_config_image_server() ?>';
var author_photo_path = '<?= $author_photo_path ?>';
var travel_plan_photo_path = '<?= $travel_plan_photo_path ?>';
$(function() {
	$('.swiper-slide').fancybox();
	$('.suitable-trip .pointer span').each(function() {
		$(this).click(function() { reset_search_day(parseInt($(this).attr('data-day'))); });
	});
	$('.suitable-trip .tag-list li').click(function() {
 	 	$(this).hasClass('selected') ? $(this).removeClass('selected').css('color', '#666') : $(this).addClass('selected').css('color', '#fee600');
 	});
	$('#btn_search').click(function () {
		query_travel_plan(1, true);
		scrollTo('.suitable-trip .item-list');
	});
	if(def_area_id != null && def_area_id != '' && def_area_id != 0){ $('#area_id').val(def_area_id); }
	if(def_days != null && def_days != '' && def_days != 0){ reset_search_day(def_days); }
	query_travel_plan(1, true);
	change_author();

});

function seek_author(seek) {
	var p = author_pos;
	p += seek;
	if(p < 0) {
		p = author_list.length - 1;
	} else if(p >= author_list.length) {
		p = 0;
	}
	author_pos = p;
	change_author();
}

function change_author(){
	reset_travel_plan(author_pos);
	reset_author(author_pos);
}

function selPriceOrder() {
	$("#selectPriceOrder").val($('#sortSelect').val());
	location.href = '/trip/?selectPriceOrder=' + $('#sortSelect').val();
}

function convert_display_date_str(date_str) {
	var d = new Date(date_str);
	return d.getFullYear() + '/' + (d.getMonth() + 1) + '/' + d.getDate();
}
function get_trip_info(row) {
	var html = '';
	var img_travel_plan = '/web/img/no-pic.jpg';
	var img_author_head = '/web/img/no-pic.jpg';
	if(row.tp_main_photo != null && row.tp_main_photo != '' && row.tp_main_photo != 0) {
		img_travel_plan = image_server_url + '/photos' + travel_plan_photo_path + '/' + row.tp_id + '/data_image/' + row.tp_main_photo + '.jpg';
	}
	//console.log(img_travel_plan);
	if(row.ap_avatar != '' && row.ap_avatar != '0' && row.ap_avatar != null) {
		img_author_head = image_server_url + '/photos' + author_photo_path + '/' + row.a_id + '/' + row.ap_avatar + '.' + row.p_content_type;
	}
	var foreword = row.tp_foreword;
	if(foreword.length > 100) {
		foreword = foreword.substr(0, 100);
	}
	html = '<li> ';
	html += '	<a href="/trip/' + row.tp_id + '/"> ';
	html += '		<img src="' + img_travel_plan + '" alt="" style="height:174px" onerror="javascript:this.src=\'/web/img/no-pic.jpg\';"> ';
	html += '		<div class="day"> ';
	html += '			<span>' + row.tp_days + '</span> ';
	html += '			<span>DAY</span> ';
	html += '		</div> ';
	html += '		<h4 class="title">' + row.tp_title + '</h4> ';
	html += '		<div class="con" style="height:128px;">' + foreword + '...</div> ';
	html += '	</a> ';
	html += '	<div class="writer"> ';
	html += '		<a href="/author/' + row.a_id + '/">';
	html += '			<div class="cover-photo"> ';
	html += '					<img src="' + img_author_head + '" width="24" height="24" alt="" onerror="javascript:this.src=\'/web/img/no-pic.jpg\';">';
	html += '			</div> ';
	html += '			<span class="category">' + row.a_title + '</span> ';
	html += '			<span class="author">' + row.a_nickname + '</span> ';
	html += '		</a> ';
	html += '	</div> ';
	html += '	<!-- //write --> ';
	html += '	<div class="status"> ';
	html += '		<!--<span> ';
	html += '			<i class="fa fa-map-marker"></i>墾丁-東港大鵬灣</span>--> ';
	html += '		<span>';
	html += '			<i class="fa fa-heart"></i>'+ numberFormat(row.tpe_collect_total) + '</span> ';
	html += '		<span> ';
	html += '			<i class="fa fa-eye"></i>' + numberFormat(row.tpe_click_total) + '</span> ';
	html += '		<span> ';
	html += '			<i class="fa fa-calendar"></i>' + convert_display_date_str(row.tp_begin_date) + '-' + convert_display_date_str(row.tp_end_date) + '</span> ';
	html += '	</div> ';
	html += '	<!-- //status --> ';
	html += '</li> ';
	return html
}
function init_page_page(total_page, pageno) {
	$('.suitable-trip .text-center').html('<ul id="visible-pages-example" class="pagination"></ul>');
	$('#visible-pages-example').twbsPagination({
		totalPages: total_page,
		startPage: pageno,
	    first: "第一頁",
	    prev: "上一頁",
	    next: "下一頁",
	    last: "最後一頁",
		initiateStartPageClick:false,
		onPageClick: function (event, page) {
			query_travel_plan(page, false);
			scrollTo('.suitable-trip .item-list');
		}
    });
}
function query_travel_plan(pageno, init_page) {
	var area_id = $('#area_id').val();
	var q_month = $('#q_month').val();
	var days = '';
	$('.suitable-trip .pointer span').each(function(){
		if($(this).hasClass('selected')){
			days = $(this).attr('data-day');
			return false;
		}
	});
	var tag_ids = '';
	$('.suitable-trip .tag-list li').each(function(){
		if($(this).hasClass('selected')){
			if(tag_ids != '') tag_ids += ',';
			tag_ids += $(this).attr('data-tag-id');
		}
	});

	var p = {};
	p.func = 'find_valid_travel_plan_for_trip_home';
	p.pageno = pageno;
	p.area_id = area_id;
	p.q_month = q_month;
	p.days = days;
	p.tags = tag_ids;
	p.keyword = $('#keyword').val();
	p.order = '<?= $selectPriceOrder ?>';
	p.page_size = 9;
	console.log(p);
	$.post("/web/ajax/ajax.php", p, function(data) {
		if(data.code == '9999'){
            alert(data.msg);
        } else {
            // 顯示註冊完成並顯示註冊完成popup window
            //console.log(data);
            $('#total_record').html('共 ' + data.data.total_records + ' 筆');
            var html = '';
			for(var i=0 ; i<data.data.data.length ; i++) {
				var r = get_trip_info(data.data.data[i]);
				html += r;
			}
			$('.suitable-trip .item-list').html(html);
			//console.log('init_page', init_page);
            if(init_page) {
            	init_page_page(data.data.total_page, data.data.pageno);
            }
        }
    }, 'json').done(function() { }).fail(function() { }).always(function() { });
}
function reset_author(pos) {

	reset_author_data('.main-writer', author_pos);
	var prev_id = (author_pos - 1 >= 0) ? author_pos - 1 : author_list.length - 1;
	var next_id = (author_pos + 1 < author_list.length) ? author_pos + 1 : 0;
	// console.log(pos, prev_id, next_id);
	reset_author_data('.prev', prev_id);
	reset_author_data('.next', next_id);
}

function reset_search_day(sel_day) {
	$('.suitable-trip .pointer span').each(function() {
		var day = parseInt($(this).attr('data-day'));
		// console.log(sel_day, day);
		if(day < sel_day){
			$(this).removeClass('selected').css('background-position', "-67px -38px");
		} else if(day == sel_day) {
			$(this).addClass('selected').css('background-position', "-67px -51px");;
		}else if(day > sel_day) {
			$(this).removeClass('selected').css('background-position', "-67px -25px");
		}
	});
	$('#total_days').html('共 ' + sel_day + ' 天');
}
function reset_travel_plan(pos) {
	var tp = author_list[author_pos].last_travel_plan;
	var tpi = tp.details;
	//console.log(tp);
	$('.guide h2').html('<a href="/trip/' + tp.tp_id + '/">'+tp.tp_title+'</a>');
	$('#ltp_collect').html(numberFormat(tp.tpe_collect_total));
	$('#ltp_click').html(numberFormat(tp.tpe_click_total));
	$('#ltp_date_range').html(convert_display_date_str(tp.tp_begin_date) + '-' + convert_display_date_str(tp.tp_end_date));
	$('.guide .abstract').html(tp.tp_foreword);
	$('.guide .readmore').prop('href', '/trip/' + tp.tp_id + '/');
	if(tpi.length > 0) {
		var html = '';
		for(var i=0 ; i<tpi.length ; i++) {
			row = tpi[i];
			if(row.tpd_image == null || row.tpd_image == '' || row.tp_main_photo == 0) {
				continue;
			}
			var img = image_server_url + '/photos' + travel_plan_photo_path + '/' + tp.tp_id + '/data_image/' + row.tpd_image;
			html += '<a href="' + img + '" class="swiper-slide" rel="gallery1"><img src="' + img + '" alt="" style="width:200px; height:114" onerror="javascript:this.src=\'/web/img/no-pic.jpg\';"></a>'
		}
		//console.log(html);
		$('.block-swiper .swiper-wrapper').html(html);
		$('.block-swiper').show();
	} else {
		$('.block-swiper').hide();
	}
}

function reset_author_data(folder, pos) {
	var author = author_list[pos];
	var no_img = '/web/img/no-pic.jpg';
	var img_head = no_img;
	var img_banner = no_img;
	// console.log(folder, pos, author);
	if(author.ap_avatar != '' && author.ap_avatar != null) {
		img_head = image_server_url + '/photos' + author_photo_path + '/' + author.a_id + '/' + author.ap_avatar + '.' + author.p_content_type;
	}
	if(author.ap_banner_image != '' && author.ap_banner_image != null) {
		img_banner = image_server_url + '/photos' + author_photo_path + '/' + author.a_id + '/' + author.ap_banner_image + '.jpg';
	}

	$('.guide ' + folder + ' .category').html(author.a_title.length > 6 ? author.a_title.substr(0, 6) : author.a_title);
	$('.guide ' + folder + ' .author').html(author.a_nickname);
	$('.guide ' + folder + ' img').prop('src', img_head);
	if(folder == '.main-writer') {
		$('.top-container .itinerary_kv').css('background-image', 'url(' + img_banner + ')');
	}
}

</script>