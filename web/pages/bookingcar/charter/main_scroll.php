<?php
/**
 * 說明：交通預定列表 - scroll 功能
 * 作者：Casper <casper.lee@fullerton.com.tw>
 * 日期：2016年5月13日
 * 備註：
 */
require_once __DIR__ . '/../../../config.php';
header("Content-Type:text/html; charset=utf-8");

// 頁面基本參數
$pageSize = 9;
$limit = $pageSize;
$pageno = get_val('pageno');
if (empty($pageno) || $pageno <= 0) {
    $pageno = 1;
}
$offset = 0;
if ($pageno == 1) {
    $offset = $pageno - 1;
} else {
    $offset = $pageSize * ($pageno - 1);
}
// 1:包車、2:接送機、3:觀巴
$cr_type = 1;

$image_server_url = get_config_image_server();

// 頁面傳送資料
$begin_date = get_val('begin_date');
$begin_area = get_val('begin_area');
$end_area = get_val('end_area');
$car_day = get_val('car_day');
$car_adult = get_val('car_adult');
$car_child = get_val('car_child');
$sort = get_val('sort');
$cr_boarding = $begin_area;
$cr_get_off = $end_area;
$cnt = $car_adult + $car_child;
if ($car_adult == 0 && $car_child == 0) {
    $cnt = null;
}

$cr_days = null;
if ($car_day != 0 && !empty($car_day)) {
    $cr_days = $car_day;
}

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
?>
<?php
$favourite_type = 11;

$tripitta_web_service = new tripitta_web_service();
$login_user_data = $tripitta_web_service->check_login();
$is_login = false;
if(!empty($login_user_data)) {
	$is_login = true;
	$user_favorite_type_ids = array("11");
	$favorite_list = $tripitta_web_service->find_user_favorite_by_user_id_and_ref_type_ids($login_user_data["serialId"], $user_favorite_type_ids);
}

// 顯示列表內容
if (!empty($fleet_route_list)) {
    foreach ($fleet_route_list as $fr) {
        $url = "/bookingcar/charter/" . $fr["fr_id"] . "/";
        $url .= "?car_day=" . $car_day . "&begin_date=" . $begin_date . "&car_adult=" . $car_adult . "&car_child=" . $car_child;
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
            <a class="img" href="<?php echo $url; ?>" style="background-image: url('<?php echo $img; ?>');">
            </a>
            <div class="myFavourite" data-type="<?php echo $favourite_type; ?>" data-id="<?php echo $fr["fr_id"]; ?>">
                <i class="fa <?php echo $favorite_class; ?>" id="<?php echo $favourite_type; ?>_<?php echo $fr["fr_id"]; ?>"></i>
                <!-- 點選後隱藏fa-heart-o 改顯示<i class="fa fa-heart"></i> -->
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
                    <a href="javascript:void(0)" class="reviews">
                       	 看評價
                    </a>
                </div>
                <h4><i class="fa fa-car"></i><?php echo $fr["sml_name"]; ?></h4>
                <h3>
                    <a href="<?php echo $url; ?>"><?php echo $fr["cr_name"]; ?></a>
                </h3>
                <h4><?php echo $fr["c_name"]; ?></h4>
                <div class="price">
                    <?php /* 暫時先隱藏
                      <div class="tag">促銷活動</div>
                     */ ?>
                    <div class="pWrap">
                        <div class="pTitle">優惠價 NTD</div>
                        <div class="pNum"><?php echo number_format($fr["fr_price"]); ?></div>
                    </div>
                </div>
                <div class="buyer">已有 <?php echo $fr["sell_count"]; ?> 人購買</div>
            </figcaption>
        </figure>
        <?php
    }
}
echo '<input type="hidden" class="pagenum" value="' . ($pageno+1) . '" /><input type="hidden" class="total-page" value="' . $total_page . '" />';
?>
<script>
	var is_login = <?php echo ($is_login) ? 1:0; ?>;
	$(function () {
		// 加入收藏
        $('.myFavourite').each(function() {
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
</script>