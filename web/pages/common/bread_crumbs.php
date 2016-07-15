<?
$uri_bread_crumbs = substr($_SERVER["REQUEST_URI"], 0, strrpos($_SERVER["REQUEST_URI"], '/') + 1);
$bread_crumbs = '';

$show_bread_crumbs = true;
if(preg_match('/^\/home\/$/', $uri_bread_crumbs)
        || preg_match('/^\/booking\/$/', $uri_bread_crumbs)
        || preg_match('/^\/area\/(.*)$/', $uri_bread_crumbs)) {
    $show_bread_crumbs = false;
}
if($show_bread_crumbs) {
    if(strpos($uri_bread_crumbs, '/about/') !== false) {
    	$bread_crumbs = '<a href="/about/">關於我們</a>';
    } else if(strpos($uri_bread_crumbs, '/area/') !== false) {
    	$bread_crumbs = '<a href="/area/">地區介紹</a>';
    } else if(strpos($uri_bread_crumbs, '/author/') !== false) {
    	$bread_crumbs = '<a href="/author/">作者專區</a>';
    } else if(strpos($uri_bread_crumbs, '/booking/') !== false) {
    	$bread_crumbs = '<a href="/booking/">旅宿訂房</a>';
    	if(preg_match('/[a-z,A-Z]+/', $uri_bread_crumbs)) {
    	    $cb_area_code = get_val('area_code');
    	    $cb_hs_id = get_val('hs_id');
    	    if(preg_match('/\/booking\/[a-z,A-Z]+\/[0-9]+\/$/', $uri_bread_crumbs) && !empty($cb_hs_id)) {
    	        $cb_homestay_row = $cb_area_row = Dao_loader::__get_home_stay_dao()->loadHomeStayWithLang($hs_id, $lang);
    	        if(!empty($cb_homestay_row)) {
    	            $cb_homestay_name = (empty($cb_area_row["hsml_name"]) ? $cb_area_row["hs_name"] : $cb_area_row["hsml_name"]);
    	            $cb_area_row = Dao_loader::__get_area_dao()->loadAreaWithLang($cb_homestay_row["hs_area_id"], $lang);
    	            if(!empty($cb_area_row)) {
    	                $cb_area_name = (empty($cb_area_row["aml_name"]) ? $cb_area_row["a_name"] : $cb_area_row["aml_name"]);
    	                $bread_crumbs .= ' / ' . '<a href="/booking/' . $cb_area_code . '/">' . $cb_area_name . '</a>';
    	            }
    	            $bread_crumbs .= ' / ' . '<a href="/booking/' . $cb_area_code . '/' . $cb_hs_id . '/">' . $cb_homestay_name . '</a>';
    	        }
    	    } else if(preg_match('/\/booking\/[a-z,A-Z]+\/$/', $uri_bread_crumbs) && !empty($cb_area_code)) {
    	        $cb_area_row = Dao_loader::__get_area_dao()->get_by_code($lang, $cb_area_code);
    	        if(!empty($cb_area_row)) {
    	            $cb_area_name = (empty($cb_area_row["aml_name"]) ? $cb_area_row["a_name"] : $cb_area_row["aml_name"]);
    	            $bread_crumbs .= ' / ' . '<a href="/booking/' . $cb_area_code . '/">' . $cb_area_name . '</a>';
    	        }
            }
    	}
    } else if(strpos($uri_bread_crumbs, '/contact/') !== false) {
    	$bread_crumbs = '<a href="/contact/">聯絡我們</a>';
    } else if(strpos($uri_bread_crumbs, '/cooperation/') !== false) {
    	$bread_crumbs = '<a href="/cooperation/">企業合作</a>';
    } else if(strpos($uri_bread_crumbs, '/location/') !== false) {
    	$bread_crumbs = '<a href="/location/">觀光指南</a>';
    	$tc_id = get_val("tc_id");
    	if(preg_match('/\/location\/[a-z,A-Z]+\/[0-9]+\/$/', $uri_bread_crumbs) && !empty($tc_id)) {
    	    $taiwan_content_service = new taiwan_content_service();
    	    $cb_content_row = $taiwan_content_service->load_taiwan_content_by_lang(get_config_current_lang(), $tc_id);
    	    if(!empty($cb_content_row)) {
    	        $linkurl = $taiwan_content_service->get_tripitta_taiwan_content_link_url($cb_content_row["tc_type"], $tc_id);
    	        $bread_crumbs .= ' / ' . '<a href="' . $linkurl . '">' . $cb_content_row["tc_name"] . '</a>';
    	    }
    	}
    } else if(strpos($uri_bread_crumbs, '/member/') !== false) {
    	$bread_crumbs = '<a href="/member/">會員中心</a>';
    	if(strpos($uri_bread_crumbs, '/member/profile/') !== false) {
    		$bread_crumbs .= ' / ' . '<a href="/member/profile/">個人資料</a>';
    	} else if(strpos($uri_bread_crumbs, '/member/profile_edit/') !== false) {
    		$bread_crumbs .= ' / ' . '<a href="/member/profile_edit/">個人資料編輯</a>';
    	} else if(strpos($uri_bread_crumbs, '/member/collection/') !== false) {
    		$bread_crumbs .= ' / ' . '<a href="/member/collection/">我的收藏</a>';
    	} else if(strpos($uri_bread_crumbs, '/member/coupon/') !== false) {
    		$bread_crumbs .= ' / ' . '<a href="/member/coupon/">優惠劵查詢</a>';
    	} else if(strpos($uri_bread_crumbs, '/member/invoice/') !== false) {
    		$bread_crumbs .= ' / ' . '<a href="/member/invoice/">我的訂單</a>';
    	} else if(strpos($uri_bread_crumbs, '/member/update_password/') !== false) {
    		$bread_crumbs .= ' / ' . '<a href="/member/update_password/">更新密碼</a>';
    	} else if(strpos($uri_bread_crumbs, '/member/message/') !== false) {
    		$bread_crumbs .= ' / ' . '<a href="/member/message/">訊息通知</a>';
    	}
    } else if(strpos($uri_bread_crumbs, '/privacy/') !== false) {
    	$bread_crumbs = '<a href="/privacy/">隱私政策</a>';
    } else if(strpos($uri_bread_crumbs, '/service/') !== false) {
    	$bread_crumbs = '<a href="/service/">客服中心</a>';
    } else if(strpos($uri_bread_crumbs, '/terms/') !== false) {
    	$bread_crumbs = '<a href="/terms/">服務條款</a>';
    } else if(strpos($uri_bread_crumbs, '/topic/') !== false) {
    	$bread_crumbs = '<a href="javascript:void(0)">主題企劃</a>';
    } else if(strpos($uri_bread_crumbs, '/trip/') !== false) {
    	$bread_crumbs = '<a href="/trip/">行程遊記</a>';
    	$tp_id = get_val("tp_id");
    	if(preg_match('/\/trip\/[0-9]+\/$/', $uri_bread_crumbs) && !empty($tp_id)) {
    	    $trave_plan_service = new travel_plan_service();
    	    $cb_content_row = $trave_plan_service->load_travel_plan($tp_id);
    	    if(!empty($cb_content_row)) {
    	        $linkurl = '/trip/' . $tp_id . '/';
    	        $bread_crumbs .= ' / ' . '<a href="' . $linkurl . '">' . $cb_content_row["tp_title"] . '</a>';
    	    }
    	}
    } else {
    	$bread_crumbs = '';
    }
}
if($show_bread_crumbs && !empty($bread_crumbs)) {
?>
<div class="breadWrap">
	<div class="bread">
		<i class="img-member-home"></i> /		<span class="breadPath">
			<?= $bread_crumbs ?>
		</span>
	</div>
</div>
<? } ?>