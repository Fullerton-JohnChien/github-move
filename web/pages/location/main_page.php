<?
/**
 * 說明：觀光指南 - 背端程式
 * 作者：cheans <cheans.huang@fullerton.com.tw>
 * 日期：2015年12月15日
 * 備註：
 *
 * 2015/12/17 : cheans
 * 因Vincent要求每個選擇都要切換所以此程式已不再使用，改採直接換頁方式處理
 *
 */
require_once __DIR__ . '/../../config.php';

// printmsg($_POST);
// printmsg($_GET);
$folder_list = array('food', 'scenic', 'homestay', 'activity', 'gift');
$sf_list = array('restaurant', 'snack');
$area_list = array('north', 'east', 'south', 'west', 'islands');
$params_str = '';
$selected_tags = [];
$f = get_val('f');
$sf = get_val('sf');
$areas = get_val('areas');
$tags = get_val('tags');
if(!in_array($f, $folder_list)) {
    $f = '';
}
if(!in_array($sf, $sf_list)) {
    $sf = '';
}
if(!empty($areas) && !in_array($areas, $area_list)) {
    $areas = '';
}
if(!empty($tags) && !empty($tags)) {
    $selected_tags = preg_split('/,/', $tags);
}
$city_ids = array();
if(!empty($areas)) {
    $area_list = preg_split('/,/', $areas);
    foreach($area_list as $t) {
        if('north' == strtolower($t)) {
            $city_ids = array_merge(array(1,2,3,4,5,6,7), $city_ids);
        } else if('east' == strtolower($t)) {
            $city_ids = array_merge(array(17,18,19), $city_ids);
        } else if('south' == strtolower($t)) {
            $city_ids = array_merge(array(10,11,12,14,15,16), $city_ids);
        } else if('west' == strtolower($t)) {
            $city_ids = array_merge(array(8,9,13,21), $city_ids);
        } else if('islands' == strtolower($t)) {
            $city_ids = array_merge(array(22,20), $city_ids);
        }
    }
}
$pageSize = 9;
$pageno = get_val('pageno');
if(empty($pageno)) {
    $pageno = 1;
}

$cond = [];
if(!empty($f)) {
    $params_str = "f=";
    $cond["folder"] = $f;
}
if(!empty($sf)) {
    $cond["sub_folder"] = $sf;
}
if(!empty($selected_tags)) {
    $cond["tags"] = $selected_tags;
}
if(!empty($city_ids)) {
    $cond["citys"] = $city_ids;
}


$tripitta_web_service = new tripitta_web_service();
$tripitta_homestay_service = new tripitta_homestay_service();
$login_user_data = $tripitta_web_service->check_login();
$favorite_list = array();
if(!empty($login_user_data)) {
    $user_favorite_type_ids = $tripitta_web_service->get_user_favorite_type_ids($f);
    $favorite_list = $tripitta_web_service->find_user_favorite_by_user_id_and_ref_type_ids($login_user_data["serialId"], $user_favorite_type_ids);
}
$total_items = $tripitta_web_service->count_valid_taiwan_content_for_location_home($cond);
$total_page = getTotalPage($total_items, $pageSize);
if($pageno > $total_page && $total_page > 0){
    $pageno = $total_page;
}
if($total_page <= 0) {
    $total_page = 1;
}
// printmsg('total_items:' . $total_items);
// printmsg('total_page:' . $total_page);
// printmsg('pageno:' . $pageno);
// printmsg($cond);
$cond["limit"] = $pageSize;
$cond["offset"] = ($pageno - 1) * $pageSize;
$content_list = $tripitta_web_service->find_valid_type_and_ids_for_location_home($cond);
$content_detail_list = $tripitta_web_service->find_valid_taiwan_content_for_location_home($content_list);
//printmsg($content_detail_list);
$image_server_url = get_config_image_server();


// 取得顯示幣別、匯率
$display_currency_id = $tripitta_web_service->get_display_currency();
$currency_code = NULL;
$exchange_rate = 1;
$point_length = 1;
if (1 == $display_currency_id) {
    $currency_code = 'NTD';
    $exchange_rate = 1;
}
else {
    $exchange = $tripitta_homestay_service->get_exchange_by_currency_id($display_currency_id);
    $currency_code = $exchange['cr_code'];
    $exchange_rate = $exchange['erd_rate'];
    $point_length = $exchange["cr_point_length"];
}

$homestay_ids = [];
foreach($content_list as $content_row) {
    if($content_row["folder"] == 10) {
        $homestay_ids[] = $content_row["id"];
    }
}
$homestay_min_price_list = [];
if(!empty($homestay_ids)) {
    $homestay_min_price_list = $tripitta_web_service->find_homestay_min_price_by_homestay_ids($homestay_ids);
}

?>
	<!-- //top-container -->
	<article class="contents">
		<div class="container">
			<div class="sort-bar">
				<div class="select">
					<select name="" id="">
						<option value="人氣指數">人氣指數</option>
					</select>
				</div>
				<span class="sort-result">共 <?= $total_items ?> 筆</span>
			</div>
			<!-- //sort-bar -->
			<ol class="item-list">
<?
foreach($content_list as $content_type_id_row) {
    $content_row = [];
    foreach($content_detail_list as $content_detail_row) {
        if($content_detail_row["folder"] == $content_type_id_row["folder"] && $content_detail_row["id"] == $content_type_id_row["id"]) {
            $content_row = $content_detail_row;
            break;
        }
    }
    if(empty($content_row)) {
        continue;
    }
    $img = '/web/img/no-pic.jpg';
    if(!empty($content_row["main_photo"])) {
        if($content_row["folder"] == 10) {
            $img = $image_server_url . '/photos/' . (is_production() ? 'travel' : 'alpha_travel') . '/home_stay/' . $content_row["id"] . '/' . $content_row['main_photo'] . "_middle.jpg";
        } else {
            $img = $image_server_url . '/photos/' . (is_production() ? 'taiwan_content' : 'taiwan_content_alpha') . '/' . $content_row["id"] . '/' . $content_row['main_photo'] . ".jpg";
        }
    }
    $min_price = 0;
    if($content_row["folder"] == 10) {
        foreach($homestay_min_price_list as $homestay_min_price_row) {
            if($homestay_min_price_row["hsrpm_home_stay_id"] == $content_row["id"]) {
                $min_price = $homestay_min_price_row["min_price"];
            }
        }
    }
    $folder = $content_row["folder"];
    $favorite_class = "fa-heart-o";
    foreach($favorite_list as $favorite_row) {
        if($folder == 10 && ($favorite_row["uf_type"] == 0 || $favorite_row["uf_type"] == $folder) && $favorite_row["uf_home_stay_id"] == $content_row["id"]) {
            $favorite_class = "fa-heart";
            break;
        }else if($folder != 10 && $favorite_row["uf_type"] == $folder && $favorite_row["uf_home_stay_id"] == $content_row["id"]) {
            $favorite_class = "fa-heart";
            break;
        }
    }
    $linkurl = '';
    if($folder == 10) {
        $linkurl = '/booking/' . $content_row["a_code"] . '/' . $content_row["id"] . '/';
    } else {
        $type_str = '';
        if(7 == $folder) {
            $type_str = 'food';
        } else if(8 == $folder) {
            $type_str = 'spot';
        } else if(82 == $folder) {
            $type_str = 'gift';
        } else if(12 == $folder || 15 == $folder) {
            $type_str = 'event';
        }
        $linkurl = '/location/' . $type_str . '/' . $content_row["id"] . '/';
    }
    $content_name = $content_row["name"];
    if(mb_strlen($content_name, 'utf-8') > 14) {
        $content_name = mb_substr($content_name, 0, 14, 'utf-8') . '...';
    }
?>
				<li>
					<span class="img-collect" data-type="<?= $content_row["folder"] ?>" data-id="<?= $content_row["id"] ?>">
						<i class="fa <?= $favorite_class ?>"  id="<?= $content_row["folder"] . "_" . $content_row["id"] ?>" ></i>
					</span>
					<a href="<?= $linkurl ?>">
						<img src="<?= $img ?>" alt="" style="width:300px; height:208px" onerror="javascript:this.src='/web/img/no-pic.jpg';">
						<h4 class="hotel-name"><?= $content_name ?></h4>
						<div class="status">
							<span>
								<i class="fa fa-map-marker"></i><?= $content_row["a_name"] ?></span>
							<span>
								<i class="fa fa-heart"></i><?= $content_row["cnt_collect"] ?></span>
							<span>
								<i class="fa fa-eye"></i><?= $content_row["cnt_view"] ?></span>
						</div>
						<div class="price" style="height: 24px">
							<? if($min_price != 0){ ?><span class="small"><?= $currency_code ?></span><?= number_format(getCeil($min_price/$exchange_rate, $point_length))?><? } ?>
						</div>
					</a>
						<div class="advisor" style="height: 66px">
							<? if(!empty($content_row["tari_id"])) { ?>
							<a href="javascript:open_trip_advisor_review(<?= $content_row["tari_id"] ?>)" class="tripadvisorLogo" target="_blank">
							<img src="http://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/<?= $content_row["tari_average_rating"] ?>-33123-4.gif" style="width:118px;height:20px;"/><br />
							<?= $content_row["tari_review_count"] ?>則評論
							</a>
    						<? } ?>
						</div>


				</li>
<?
}
?>

			</ol>
			<!-- //item-list -->
			<div class="text-center">
				<ul id="visible-pages-example" class="pagination"></ul>
    		</div>
			<!--//Pagination-->
		</div>
		<!-- //container -->
	</article>
	<!-- //contents -->
<script>
    $(function() {
	    $('#visible-pages-example').twbsPagination({
    		totalPages: <?= $total_page ?>,
    		startPage: <?= $pageno ?>,
    	    first: "第一頁",
    	    prev: "上一頁",
    	    next: "下一頁",
    	    last: "最後一頁",
    		initiateStartPageClick:false,
    		onPageClick: function (event, page) {
    			var url = '/location/?pageno=' + page + '<?= $params_str ?>';
    			//location.href = url;
    			// console.log(url);
    		    // $('#page-content').text('Page ' + page);
    		    query_data(page);
    		}
	    });
	    $('.contents .img-collect').each(function() {
	        $(this).click(function() {
	        	var ref_type = $(this).attr('data-type');
	            var ref_id = $(this).attr('data-id');
				var add = $('#' + ref_type + '_' + ref_id).hasClass('fa-heart-o') ? 1 : 0;
	            if(add == 1) {
	            	add_favorite('#' + ref_type + '_' + ref_id, ref_type, ref_id);
	            } else {
	            	remove_favorite('#' + ref_type + '_' + ref_id, ref_type, ref_id);
	            }
	        });
	    });
    });

 </script>