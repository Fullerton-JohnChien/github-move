<?php
/**
 * 說明：交通預定 - 觀光巴士列表
 * 作者：Bobby <bobby.luo@fullerton.com.tw>
 * 日期：2016年05月31日
 * 備註：
 */
require_once __DIR__ . '/../../../config.php';
header("Content-Type:text/html; charset=utf-8");

// 預設清除 pay_step session
if(!empty($_SESSION["pay_step"])){
	unset($_SESSION["pay_step"]);
}

// 頁面基本參數
$favourite_type = 11;
$pageSize = 9;
$limit = $pageSize;
$pageno = get_val('pageno');
$user_serial_id = (isset($_SESSION['travel.ezding.user.data']['serialId'])) ? $_SESSION['travel.ezding.user.data']['serialId'] : 0;
if (empty($pageno) || $pageno <= 0) {
	$pageno = 1;
}
$offset = 0;
if ($pageno == 1) {
	$offset = $pageno - 1;
} else {
	$offset = $pageSize * ($pageno - 1);
}
$tripitta_web_service = new tripitta_web_service();
$login_user_data = $tripitta_web_service->check_login();
$is_login = false;
if(!empty($login_user_data)) {
	$is_login = true;
	$user_favorite_type_ids = array("11");
	$favorite_list = $tripitta_web_service->find_user_favorite_by_user_id_and_ref_type_ids($login_user_data["serialId"], $user_favorite_type_ids);
}

// 1:包車、2:接送機、3:觀巴
$cr_type = 3;

// 包車天數內容(預設間隔0.5)
$day_count = 3;
$day_space = 0.5;
$image_server_url = get_config_image_server();

// 排序選擇內容
$sort_list = array(   1 => "價格高~低"
		, 2 => "價格低~高"
);

// 頁面傳送資料
$begin_area = get_val('begin_area');
$end_area = get_val('end_area');
$begin_date = get_val('begin_date');
$sort = get_val('sort');
$cr_boarding = $begin_area;
$cr_get_off = $end_area;
if (empty($begin_date)) {
    $begin_date_default = date('Y-m-d', strtotime(date('Y-m-d') . " +1 day"));
} else {
    $begin_date_default = $begin_date;
}
$cnt = null;
$cr_days = null;

// 取得區域資料
$area_category = 'car';
$area_dao = Dao_loader::__get_area_dao();
$area_list = $area_dao->findAreasWithLangByCategoryAndParentId(get_config_current_lang(), $area_category, 0);

// 頁面搜尋資料
$tripitta_service = new tripitta_service();
// 取得全部資料
$fleet_route_all_list = $tripitta_service->find_fleet_route_search_list($cr_type, $cr_boarding, $cr_get_off, $cnt, $cr_days);
$total_row_count = count($fleet_route_all_list);

// 取得當頁資料內容
$total_page = getTotalPage($total_row_count, $pageSize);
if ($pageno > $total_page && $total_page > 0) {
	$pageno = $total_page;
}
if ($total_page <= 0) {
	$total_page = 1;
}
// 搜尋條件
$fleet_route_list = $tripitta_service->find_fleet_route_search_list($cr_type, $cr_boarding, $cr_get_off, $cnt, $cr_days, $limit, $offset, $sort);


// 取得區域名稱
function get_area_name_by_id($area_list, $area_id){
	$ret = null;
	if(!empty($area_list)){
		$data_count = 1;
		foreach ($area_list as $a) {
			if(($area_id == $a["a_id"]) || ($area_id==0 && $data_count==1)){
				$ret = $a["a_name"];
			}
			$data_count++;
		}
	}
	return $ret;
}
?>
<!DOCTYPE html>
<html lang="zh-Hant" prefix="og: http://ogp.me/ns#">
<head>
	<?php include __DIR__ . "/../../common/head_new.php"; ?>
        <style>
            .kv{
                width: 100%;
                height: 100%;
                background: url(/web/img/location/location_banner_food.jpg) 50% 50%/cover no-repeat;
            }
            .selectWrap input{
                border: 0px;
                margin-top: -0.2em;
            }
        </style>
        <title>交通預定 - Tripitta 旅必達</title>
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css">
        <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
        <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
        <script src="/web/js/jquery.twbsPagination.js" type="text/javascript"></script>
        <link rel="stylesheet" href="/web/css/main.css">
        <link rel="stylesheet" href="/web/css/main2.css">
</head>
<body>
	<header><?php include __DIR__ . "/../../common/header_new.php"; ?></header>
	<main class="tourBus-index-container">
        <input type="hidden" id="user_serial_id" value="<?php echo $user_serial_id; ?>">
		<!-- A popup when mobile mode -->
		<section class="selector col-tourBus" id="popupSelectList">
			<div class="closeBtn">
				<i class="fa fa-times" aria-hidden="true"></i>
			</div>
			<div class="wrap">
				<div class="stitle">出發地</div>
				<div class="selectWrap leftRadius">
					<select id="begin_area" name="begin_area">
						<option value='' selected>選擇出發地</option>
                        <?php
                            if (!empty($area_list)) {
                                foreach ($area_list as $a) {
                                    ?>
                                    <option value="<?php echo $a["a_id"]; ?>" <?php echo $begin_area == $a["a_id"] ? 'selected="selected"' : ''; ?>><?php echo $a["a_name"]; ?></option>
                                    <?php
                                }
                            }
                        ?>
					</select>
					<i class="fa fa-angle-down"></i>
				</div>
			</div>
			<div class="wrap">
				<div class="stitle">目的地</div>
				<div class="selectWrap">
					<select id="end_area" name="end_area">
						<option value='' selected>選擇目的地</option>
                        <?php
                            if (!empty($area_list)) {
                                foreach ($area_list as $a) {
                                    ?>
                                    <option value="<?php echo $a["a_id"]; ?>" <?php echo $end_area == $a["a_id"] ? 'selected="selected"' : ''; ?>><?php echo $a["a_name"]; ?></option>
                                    <?php
                                }
                            }
                        ?>
					</select>
					<i class="fa fa-angle-down"></i>
				</div>
			</div>
			<div class="wrap">
				<div class="stitle">出發日期</div>
				<div class="selectWrap rightRadius">
					<input type="text" id="begin_date" name="begin_date" placeholder="出發日期" class="checkIn" maxlength="20" style="width:100%;" />
					<i class="fa fa-angle-down"></i>
				</div>
			</div>
			<label class="btnWrap">
				<button id="bus_search" class="submit">查詢</button>
			</label>
		</section>

		<!-- mobile -->
		<section class="selector-m">
			<ul class="selectorList">
				<li>
					<div>出發地:</div>
                    <div id="begin_area_mobile"><?php echo get_area_name_by_id($area_list, $begin_area); ?></div>
				</li>
				<li>
					<div>出發日:</div>
                    <div class="begin-date-mobile"><?php echo $begin_date_default; ?></div>
				</li>
				<li>
					<div>目的地:</div>
                    <div id="end_area_mobile"><?php echo get_area_name_by_id($area_list, $end_area); ?></div>
				</li>
			</ul>
			<label class="btnWrap">
				<button class="submit" id="car_search_mobile">重新查詢</button>
			</label>
		</section>
		<section class="search">
			<div class="selectWrap">
				<select id="sort" nama="sort">
                    <option value="" <?php echo is_null($sort) ? 'selected="selected"' : ''; ?>>排序選擇</option>
					<?php
                        if (!empty($sort_list)) {
                            foreach ($sort_list as $k => $csl) {
                                ?>
                                <option value="<?php echo $k; ?>"<?php echo $sort==$k ? ' selected="selected"' : ''; ?>><?php echo $csl; ?></option>
                                <?php
                            }
                        }
                    ?>
				</select>
				<i class="fa fa-angle-down"></i>
			</div>
		</section>
		<section class="product">
			<?php
                // 顯示列表內容
                if (!empty($fleet_route_list)) {
                    foreach ($fleet_route_list as $fr) {
                        $url = "/bookingcar/tourbus/" . $fr["fr_id"] . "/";
                        $url .= "?begin_date=" . $begin_date;
                        $img = '/web/img/no-pic.jpg';
                        if (!empty($fr["cr_main_photo"])) {
                            $img = $image_server_url . '/photos/' . (is_production() ? 'car' : 'car_alpha') . '/route/' . $fr["cr_id"] . '/' . $fr["cr_main_photo"] . ".jpg";
                        }
                        $favorite_class = "fa-heart-o";
                        if (!empty($favorite_list)) {
	                        foreach($favorite_list as $favorite_row) {
	                        	if ($favorite_row['uf_home_stay_id'] == $fr["fr_id"]) {
	                        		$favorite_class = "fa-heart";
	                        		break;
	                        	}
	                        }
                        }
                        ?>
			<figure>
				<a class="img" href="<?php echo $url; ?>" style="background-image: url('<?php echo $img; ?>');"></a>
				<div class="myFavourite" data-type="<?php echo $favourite_type; ?>" data-id="<?php echo $fr["fr_id"]; ?>">
					<i class="fa <?php echo $favorite_class; ?>" id="<?php echo $favourite_type; ?>_<?php echo $fr["fr_id"]; ?>"></i>
				</div>
				<figcaption class="info">
					<div class="rank">
						<div class="stars">
							<?php
                            	$star = 5;
                                $cnt = !empty($fr["commont"]) ? $fr["commont"] : 0;
                                for ($i = 1; $i <= $star; $i++) {
                                	$class = "fa-star-o";
                                    if ($i <= intval($cnt)) {
                                    	$class = "fa-star";
                                    } else {
                                    	if ((1 - ($i - $cnt)) > 0 && is_float($cnt - $i)) {
                                        	$class = "fa-star-half-o";
                                    	}
                               		}
                               ?>
                               <i class="fa <?php echo $class; ?>" aria-hidden="true"></i>
                               <?php
                               }
                            ?>
						</div>
						<a href="/bookingcar/<?php echo $fr['s_id']; ?>/" target="_blank" class="reviews">
							看評價
						</a>
					</div>
					<h4><i class="fa fa-bus" aria-hidden="true"></i><?php echo $fr["sml_name"]; ?></h4>
					<h3>
						<a href="<?php echo $url; ?>"><?php echo $fr["cr_name"]; ?></a>
					</h3>
					<h4>發車時間 <span><?php echo date("H:i", strtotime($fr["fr_boarding_time"])); ?></span></h4>
					<div class="price">
						<?php /* 暫時先隱藏
						<div class="tag">促銷活動</div>
						*/ ?>
						<div class="pWrap">
							<div class="pTitle">優惠價 NTD</div>
							<div class="pNum"><?php echo number_format($fr["fr_child_price"]); ?></div>
						</div>
					</div>
					<div class="buyer">已有 <?php echo $fr["sell_count"]; ?> 人購買</div>
				</figcaption>
			</figure>
					<?php
                    }
                }
                ?>
                <?php if($deviceType == 'computer'){ ?>
                <div class="text-center">
                    <ul id="visible-pages-example" class="pagination"></ul>
                </div>
                <!--//Pagination-->
                <?php
                }else{
                	echo '<input type="hidden" class="pagenum" value="' . $pageno . '" /><input type="hidden" class="total-page" value="' . $total_page . '" />';
                }
                ?>
		</section>
	</main>
	<footer><? include __DIR__ . "/../../common/footer_new.php"; ?></footer>
	<?php include __DIR__ . '/../../common/ga.php';?>
	<script type="text/javascript">
		var is_login = <?= ($is_login) ? 1:0 ?>;
		$(function(){

	        // 搜尋
	        var caneldar_option = <?php echo json_encode(Constants::$CALENDAR_OPTIONS); ?>;
	        var begin_date = new Date();
	        $('#begin_date').datepicker(caneldar_option).datepicker('option', {minDate: new Date()});
	        $('#begin_date_m').datepicker(caneldar_option).datepicker('option', {minDate: new Date()});
	        <?php if(!empty($begin_date)){ ?>
	        $('#begin_date').val('<?php echo $begin_date; ?>');
	        <?php }else if(!empty($begin_date_default) && empty($begin_date)){?>
	        $('#begin_date').val('<?php echo $begin_date_default; ?>');
	        <?php } ?>

	        // 偵測重新查詢按鈕
	        $('#bus_search').click(function () {
	            query_car();
	        });

	        // 交通預定 - 重新查詢 - 處理內容
	        query_car = function () {
	            var begin_area = $('#begin_area :selected').val();
	            var end_area = $('#end_area :selected').val();
	            var begin_date = $('#begin_date').val();
	            var url = '/tourbus/?begin_area=' + encodeURIComponent(begin_area);
	            url += '&end_area=' + encodeURIComponent(end_area);
	            url += '&begin_date=' + encodeURIComponent(begin_date);
	            location.href = url;
	        }

	        // 偵測重新查詢按鈕
	        $('#car_search_mobile').click(function () {
        		$('.overlay').show();
	            $('#popupSelectList').css('visibility', 'visible');
	            $('#popupSelectList').show();
	        });

	        // 偵測排序選擇功能表
	        $('#sort').change(function () {
	            query_sort();
	        });

	        // 排序選擇 - 處理內容
	        query_sort = function () {
	            var params = '';
	            var and = '';
	        	<?php if (!empty($begin_area)) { ?>
	            var b_area = $('#popupSelectList #begin_area :selected').val();
	            if (params != "") {
	                and = "&";
	            }
	            params += and + 'begin_area=' + encodeURIComponent(b_area);
				<?php } ?>
				<?php if (!empty($end_area)) { ?>
	            var e_area = $('#popupSelectList #end_area :selected').val();
	            if (params != "") {
	                and = "&";
	            }
	            params += and + 'end_area=' + encodeURIComponent(e_area);
				<?php } ?>
				<?php if (!empty($begin_date)) { ?>
	            var b_date = $('#popupSelectList #begin_date').val();
	            if (params != "") {
	                and = "&";
	            }
	            params += and + 'begin_date=' + encodeURIComponent(b_date);
				<?php } ?>
	            var sort = $('#sort').val();
	            if (params != "") {
	                and = "&";
	            }
	            params += and + 'sort=' + encodeURIComponent(sort);
	            var url = '/tourbus/?' + params;
	            location.href = url;
	        }

	        // 處理頁次內容
	        var total_items = '<?php echo $total_row_count; ?>';
	        if (total_items > 0) {
	            $('#visible-pages-example').twbsPagination({
	                totalPages: <?php echo $total_page; ?>,
	                startPage: <?php echo $pageno; ?>,
	                first: "第一頁",
	                prev: "上一頁",
	                next: "下一頁",
	                last: "最後一頁",
	                initiateStartPageClick: false,
	                onPageClick: function (event, page) {
	                    location_query(page);
	                }
	            });
	        }

	        // 依據頁面條件處理頁面資訊
	        function location_query(pageno) {
	            var url = '';
	            var params = '';
	            var and = '';
	<?php if (!empty($begin_area)) { ?>
	                var b_area = $('#popupSelectList #begin_area :selected').val();
	                if (params != "") {
	                    and = "&";
	                }
	                params += and + 'begin_area=' + encodeURIComponent(b_area);
	<?php } ?>
	<?php if (!empty($end_area)) { ?>
	                var e_area = $('#popupSelectList #end_area :selected').val();
	                if (params != "") {
	                    and = "&";
	                }
	                params += and + 'end_area=' + encodeURIComponent(e_area);
	<?php } ?>
	<?php if (!empty($begin_date)) { ?>
	                var b_date = $('#popupSelectList #begin_date').val();
	                if (params != "") {
	                    and = "&";
	                }
	                params += and + 'begin_date=' + encodeURIComponent(b_date);
	<?php } ?>
	<?php if (!empty($sort)) { ?>
	                var sort = $('#sort :selected').val();
	                if (params != "") {
	                    and = "&";
	                }
	                params += and + 'sort=' + encodeURIComponent(sort);
	<?php } ?>

	            pageno = (pageno == undefined) ? 1 : pageno;
	            if (params != "") {
	                and = "&";
	            }
	            params += and + 'pageno=' + parseInt(pageno);
	            console.log(url);
	            location.href = '/tourbus/?' + params;
	        }

	        // 關閉重新查詢視窗
	        $("#popupSelectList .closeBtn").click(function(){
        		$('.overlay').hide();
	            $("#popupSelectList").css('visibility', '');
	        });

	        // 加入收藏
	        $('.product .myFavourite').each(function() {
	            $(this).click(function() {
	            	var ref_type = $(this).attr('data-type');
	                var ref_id = $(this).attr('data-id');
	    			var add = $('#' + ref_type + '_' + ref_id).hasClass('fa-heart-o') ? 1 : 0;
	                if (add == 1) {
	                	add_favorite('#' + ref_type + '_' + ref_id, ref_type, ref_id);
	                } else {
	                	remove_favorite('#' + ref_type + '_' + ref_id, ref_type, ref_id);
	                }
	            });
	        });
		});

	    function add_favorite(convas, ref_type, ref_id) {
	    	if(!is_login) {
	    		show_popup_login();
	    		return;
	    	}
	    	var p = {};
	        p.func = 'add_favorite';
	        p.user_id = $('#user_serial_id').val();
	        p.ref_type = ref_type;
	        p.ref_id = ref_id;
	        // console.log(p);
	        $.post("/web/ajax/ajax.php", p, function(data) {
	            console.log(data);
	            if(data.code == '9999'){
	                alert(data.msg);
	            } else {
	                // 顯示註冊完成並顯示註冊完成popup window
	    			alert('已加至我的收藏');
	    			$(convas).removeClass('fa-heart-o').addClass('fa-heart');
	            }
	        }, 'json').done(function() { }).fail(function() { }).always(function() { });
	    }

	    function remove_favorite(convas, ref_type, ref_id) {
	    	if(!is_login) {
	    		show_popup_login();
	    		return;
	    	}
	    	remove_items = [];
	        remove_items.push({'type_id':ref_type,'ref_id':ref_id});

	    	var p = {};
	        p.func = 'remove_user_favorite';
	        p.user_id = $('#user_serial_id').val();
	        p.items = remove_items;
	        //console.log(p);
	        $.post("/web/ajax/ajax.php", p, function(data) {
	            console.log(data);
	            if(data.code == '9999'){
	                alert(data.msg);
	            } else {
	                // 顯示註冊完成並顯示註冊完成popup window
	    			alert('已從我的收藏移除');
	    			$(convas).removeClass('fa-heart').addClass('fa-heart-o');
	            }
	        }, 'json').done(function() { }).fail(function() { }).always(function() { });
	    }

	    <?php if($deviceType == 'phone'){ ?>
	    // 處理 phone scroll
	    $(document).ready(function(){
	    	$(window).scroll(function(){
	    		if ($(window).scrollTop() == $(document).height() - $(window).height()){
	    			if($(".pagenum:last").val() <= $(".total-page").val()) {
	    				var pagenum = parseInt($(".pagenum:last").val()) + 1;
	    				getresult(pagenum);
	    			}
	    		}
	    	});
	    });

		function getresult(pageno) {
			var begin_area = $('#begin_area :selected').val();
	        var end_area = $('#end_area :selected').val();
	        var begin_date = $('#begin_date').val();
	        var sort = $('#sort :selected').val();
	        var url = '/tourbus/scroll/?begin_area=' + encodeURIComponent(begin_area);
	        url += '&end_area=' + encodeURIComponent(end_area);
	        url += '&begin_date=' + encodeURIComponent(begin_date);
	        url += '&sort=' + encodeURIComponent(sort);
			$.ajax({
				url: url,
				type: "GET",
				data:  {
					begin_area: begin_area,
					end_area: end_area,
					begin_date: begin_date,
					sort: sort,
					pageno: pageno
	    		},
				beforeSend: function(){
//	 				$('#loader-icon').show();
				},
				complete: function(){
//	 				$('#loader-icon').hide();
				},
				success: function(data){
					$(".product").append(data);
				},
				error: function(){}
		   });
		}
	    <?php } ?>
	</script>
</body>
</html>