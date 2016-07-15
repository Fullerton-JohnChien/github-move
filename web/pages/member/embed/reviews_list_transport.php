<?php
/**
 * 說明：會員中心 - 我的點評- content (transport)
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
		$fr_id = $item_row["fr_id"];
		$cr_id = $item_row["cr_id"];
		if($item_row["cr_main_photo"]!=0){
			$homestay_photo_url = $image_server_url . '/photos/' . (is_production() ? 'car' : 'car_alpha') . '/route/' . $cr_id . '/' . $item_row["cr_main_photo"] . '.jpg';
			// 			$homestay_photo_url = $img_server . '/photos/' . (is_production() ? 'car' : 'car_alpha') . '/route/' . $item_row["fr_main_photo"] . '.jpg';
		}
		$ur_evaluation = $item_row["ur_evaluation"];
	?>
	<figure class="review">
		<div class="rLeft">
			<img src="<?php echo $homestay_photo_url; ?>" class="rImg">
		</div>
		<figcaption class="rRight">
			<div class="storeWrap">
				<div class="storeName">
					<h2>
						<a href="/bookingcar/charter/<?php echo $fr_id; ?>/" target="_blank"><?php echo $item_row["cr_name"]; ?></a>
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