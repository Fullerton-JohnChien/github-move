<?php
require_once __DIR__ . '/../../config.php';
// printmsg($_SESSION);
// 預設進入頁面
$default_url = "/".$member_path."/profile/";
$url = get_val('url');
$type = get_val('type');
if(empty($type)){
	$type = "food";
}
$item = get_val("item");
switch ($url){
	default:
	// 編輯會員
	case 'profile':
		$default_url = "/".$member_path."/profile/";
		break;
	// 我的點評
	case 'reviews':
		$default_url = "/".$member_path."/reviews/";
		break;
	// 我的收藏
	case 'collection':
		$default_url = "/".$member_path."/collection_list/".$type."/";
		break;
}
writeLog(time());
?>
<!DOCTYPE html>
<html lang="zh-Hant" prefix="og: http://ogp.me/ns#">
    <head>
    	<style>
    		.oNumber {cursor:pointer;}		
    	</style>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php include __DIR__ . "/../common/head_new.php"; ?>
        <title>Tripitta 旅必達 會員中心</title>
        <link rel="stylesheet" href="/web/css/main.css?01121536">
        <link rel="stylesheet" href="/web/css/main2.css">
        <link rel="stylesheet" href="/web/css/member.css">
		<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css">
		<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
		<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
    </head>
    <body>
        <?php
        $total_orders = 0;
        $total_coupon = 0;
        $collection_total_items = 0;
        $reviews_total_items = 0;
        $tripitta_service = new tripitta_service();
        $tripitta_web_service = new tripitta_web_service();
        $login_user_data = $tripitta_web_service->check_login();
        if(!empty($login_user_data)){
        	$user_serial_id = $login_user_data["serialId"];
	        $tripitta_service = new tripitta_service();
	        // 取得我的收藏總數量
	        $user_favorite_type_ids = array();
	        $limit = 0;
	        $offset = 0;
	        foreach ($collection_type as $t){
	        	$user_favorite_type_ids = $tripitta_service->get_favorite_type_ids ( $t );
	        	$item_list = $tripitta_web_service->list_user_favorite ( 'tw', $user_serial_id, $user_favorite_type_ids, $limit, $offset );
	        	$collection_total_items += count($item_list);
	        }
	        // 取得我的點評總數量
	        $reviews_total_homestay = $tripitta_service->find_user_homestay_reviews_by_user_id($user_serial_id, 'homestay');
	        $reviews_total_items += count($reviews_total_homestay);
	        $reviews_total_car = $tripitta_service->find_user_car_reviews_by_user_id($user_serial_id, 'car');
	        $reviews_total_items += count($reviews_total_car);
	        // 取得我的訂單
	       // $total_orders = 0;
	        $total_orders = $tripitta_service -> count_user_order($user_serial_id);
	        $query_type = 'all';
	        $data = array();
    		$data["user_id"] = $user_serial_id;
	        if('cancel' == $query_type) {
	        	$data["cancel_status"] = 2;
	        } else {
	        	if('not_check_in' == $query_type) {
	        		$data["cancel_status"] = 1;
	        		$data["check_in_status"] = 1;
	        	} else if('check_in' == $query_type) {
	        		$data["cancel_status"] = 1;
	        		$data["check_in_status"] = 2;
	        	}
	        }
	        $ret = $tripitta_web_service->query_odc_order($data);
	        if($ret["code"] == "0000") {
	        	$api_ret = $ret["data"];
	        	if($api_ret["code"] == "0000") {
	        		$order_list = $api_ret["data"];
	        		//$total_orders = count($order_list);
	        	}
	        }

	        // 取得我的優惠卷總數量
	        $coupon_dao = Dao_loader::__get_coupon_dao();
	        $coupon_list = $coupon_dao->getCouponByUser($user_serial_id);
	        $total_coupon = count($coupon_list);
	        $pageno = get_val('pageno');
	        if(empty($pageno)) {
	        	$pageno = 1;
	        }

	        // 取得我的旅遊金
	        $bonus = 0;
	        $marketing_campaign_list = $tripitta_service->find_marketing_campaign_must_possessed_by_user($user_serial_id);
	        foreach ($marketing_campaign_list as $mc) {
	        	$marking_campaign_times = $tripitta_service->find_valid_marking_campaign_times($user_serial_id, $mc['mc_id']);
	        	if ($mc["mc_type"] == 1) $bonus += $marking_campaign_times * 100;
	        }
	        $bonus = number_format($bonus);
        }
        ?>
        <header><?php include __DIR__ . "/../common/header_new.php"; ?></header>
        <?php if ($deviceType == "computer") { ?>
        <main class="memIndex-container">
        <?php } else { ?>
        <main class="memIndex-container mem-content-m-container">
        <?php }?>
            <section class="memInfo">
                <div class="memImg" style="cursor: pointer;">
                <?php if (!empty($avatar)) {?>
					<img src="<?php echo $img_server, $avatar, '?', time();?>" alt="">
				<?php }else{ ?>
                    <img src="http://placehold.it/80x80">
                <?php } ?>
                </div>
                <div class="memDetail">
                    <div class="infoTop">
                        <div class="memName">
                            <?php echo $nickname; ?>
                        </div>
                        <?php if ($deviceType == "phone") { ?>
                        <button id="userInfo" class="menInfoBtn">編輯會員資料</button>
                        <?php } ?>
                    </div>
                    <div class="infoBottom">
                        <label id="collectBtn">
                            <i class="fa fa-heart-o" aria-hidden="true"></i>
                            <span class="labelText">我的收藏(<span><?php echo $collection_total_items; ?></span>)</span>
                        </label>
                        <label id="reviewBtn">
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <span class="labelText">我的點評(<span><?php echo $reviews_total_items; ?></span>)</span>
                        </label>
                        <?php /* 目前尚未有項目內容，先隱藏
                        <label>
                            <i class="fa fa-map-marker" aria-hidden="true"></i>
                            <span class="labelText">我的行程(<span>100</span>)</span>
                        </label>
                        */ ?>
                    </div>
                </div>
                <div class="memOrderList">
                    <div class="oBlock" id="header_btn_my_order">
                        <div class="oNumber"><?php echo $total_orders; ?></div>
                        <div class="oText">我的訂單</div>
                    </div>
                    <div class="oBlock" id="member_menu_my_coupon">
                        <div class="oNumber"><?php echo $total_coupon; ?></div>
                        <div class="oText">我的優惠券</div>
                    </div>
                    <div class="oBlock">
                        <div class="oNumber">0</div>
                        <div class="oText">線上諮詢</div>
                    </div>
                    <div class="oBlock" id="bonusBtn">
						<div class="oNumber"><?php echo $bonus; ?></div>
						<div class="oText">我的旅遊金</div>
					</div>
                </div>
            </section>
            <ul class="infoList-m">
                <li id="my_collection">
                    <i class="fa fa-heart-o" aria-hidden="true"></i>
                    <span>我的收藏</span>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li id="my_reviews">
                    <i class="fa fa-star-o" aria-hidden="true"></i>
                    <span>我的點評</span>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li id="my_plan">
                    <i class="fa fa-map-marker" aria-hidden="true"></i>
                    <span>我的行程</span>
                    <i class="fa fa-angle-right"></i>
                </li>
            </ul>
            <section id="dataBlock" class="dataBlock"></section>

        </main>
        <footer><?php include __DIR__ . "/../common/footer_new.php"; ?></footer>
        <?php include __DIR__ . '/../common/ga.php'; ?>
        <script type="text/javascript">
        	var orig_password_verify = false;
            $(function () {
                $("#collectBtn").click(function () {
                    $("#dataBlock").load("/<?php echo $member_path; ?>/collection_list/<?php echo $type; ?>/");
                });
                $("#userInfo").click(function () {
                    <?php if($deviceType == 'computer'){ ?>
                    if ($(window).width()<=623) {
                    	window.location.href = '/member_m/?url=profile';
                    } else {
                    	$("#dataBlock").load("/<?php echo $member_path; ?>/profile/");
                    }
                    <?php } else { ?>
                    window.location.href = '/member_m/?url=profile';
                    <?php } ?>
                });
                $("#reviewBtn").click(function () {
                    $("#dataBlock").load("/<?php echo $member_path; ?>/reviews_list/");
                });
                $(".infoList-m li").on("click", function(){
                    var id_name = $(this).attr("id");
                    switch(id_name){
                    	default:
                    	case 'my_collection':
                    		window.location.href = '/member_m/?url=collection';
                        	break;
                    	case 'my_reviews':
                    		window.location.href = '/member_m/?url=reviews';
                        	break;
                    	case 'my_plan':
                        	break;
                    }
                });
                $(".memOrderList #header_btn_my_order").on("click", function(){
                	<?php if ($deviceType == "computer") { ?>
                	$( "#dataBlock" ).show();
                	$( "#dataBlock" ).load( "/web/pages/member/embed/orders/orders.php" );
                	<?php } else { ?>
                	$( '#openMenu' ).hide();
                	$( '#prexPage2' ).show();
                	$( ".memInfo" ).hide();
                	$( ".memOrderList" ).hide();
                	$( ".infoList-m" ).hide();
                	$( "#dataBlock" ).show();
                	$( ".dataBlock" ).load( "/web/pages/member/embed/orders/orders.php" );
                	<?php } ?>
                });

                $(".memOrderList #member_menu_my_coupon").on("click", function(){
            		window.location.href = '/member/coupon/';
                });

                $(".memOrderList #bonusBtn").on("click", function(){
                	<?php if ($deviceType == "computer") { ?>
                	$( "#dataBlock" ).show();
                	$( "#dataBlock" ).load( "/web/pages/member/embed/bonus-index.php" );
                	<?php } else { ?>
                	$( '#openMenu' ).hide();
                	$( '#prexPage2' ).show();
                	$( ".memInfo" ).hide();
                	$( ".memOrderList" ).hide();
                	$( ".infoList-m" ).hide();
                	$( "#dataBlock" ).show();
                	$( ".dataBlock" ).load( "/web/pages/member/embed/bonus-index.php" );
                	<?php } ?>
                });
            });

            $( document ).ready(function(){
				<?php if($item == "order") { ?>
            		$( "#dataBlock" ).load( "/web/pages/member/embed/orders/orders.php" );
    			<?php }else if($item == "bonus") { ?>
            		$( "#dataBlock" ).load( "/web/pages/member/embed/bonus-index.php" );
            	<?php }else { ?>
            		$("#dataBlock").load("<?php echo $default_url; ?>");
            	<?php } ?>
            });
        </script>
    </body>
</html>