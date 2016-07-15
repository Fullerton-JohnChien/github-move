<?php
/**
 * 說明：會員中心 - 我的點評- content (homestay)
 * 作者：Casper <casper.lee@fullerton.com.tw>
 * 日期：2016年5月31日
 * 備註：
 */

$image_server_url = get_config_image_server();
$star = 5;
if(!isset($item_list)) {
	$item_list = array();
}

?>

<div class="myReviews">
<?php
	foreach($item_list as $item_row) {
		$homestay_photo_url = "http://placehold.it/60x60";
		$hs_id = $item_row["hs_id"];
		if($item_row["hs_main_photo"]!=0){
			$homestay_photo_url = $img_server . '/photos/travel/home_stay/' . $hs_id . '/' . $item_row["hs_main_photo"] . '_middle.jpg';
		}
		$ur_evaluation = $item_row["ur_evaluation"];
		$area_code = 'test';
		if(!empty($item_row["a_code"])){
			$area_code = $item_row["a_code"];
		}
	?>
	<figure class="review">
		<div class="rLeft">
			<img src="<?php echo $homestay_photo_url; ?>" class="rImg">
		</div>
		<figcaption class="rRight">
			<div class="storeWrap">
				<div class="storeName">
					<h2>
						<a href="/booking/<?php echo $area_code; ?>/<?php echo $hs_id; ?>/" target="_blank"><?php echo $item_row["hs_name"]; ?></a>
					</h2>
					<div class="dateWrap">
					<?php echo date("Y/m/d", strtotime($item_row["ur_create_time"])); ?>
					</div>
				</div>
				<div class="storeInfo">
					<span class="stars">
						<?php
                        	$cnt = $ur_evaluation;
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
                                <i class="fa <?php echo $class; ?>"></i>
                                <?php
                             }
                        ?>
					</span>
					<span class="storeNumWrap">
						<span class="storeNum"><?php echo $ur_evaluation; ?></span> / 5
					</span>
				</div>
			</div>
		</figcaption>
		<div class="rLeft">
			&nbsp;
		</div>
		<figcaption class="rRight">
			<a class="rtitle">
				<h3>
				<?php echo $item_row["ur_title"]; ?>
				</h3>
			</a>
			<p class="rcontent">
				<?php echo $item_row["ur_content"]; ?>
			</p>
			<div class="readMore" data-times="0" data-height="0">
			</div>
		</figcaption>
	</figure>
<?php } ?>
</div>