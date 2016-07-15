<?php
/**
 * 說明：會員中心 - 我的收藏 - content (transport)
 * 作者：Casper <casper.lee@fullerton.com.tw>
 * 日期：2016年5月24日
 * 備註：
 */

$image_server_url = get_config_image_server();
if(!isset($item_list)) {
	$item_list = array();
}

$area_dao = Dao_loader::__get_area_dao();
?>

<div class="myCollect">
<?php
	foreach($item_list as $item_row) {
		$item_id = $item_row["fr_id"];
		$item_type = $item_row["uf_type"];
		$item_name = $item_row["cr_name"];
		$item_desc = $item_row["cr_route_description"];
		if(mb_strlen($item_desc) > MAX_DESCRIPTION_LENGTH) {
			$item_desc = mb_substr($item_desc, 0, MAX_DESCRIPTION_LENGTH, 'utf-8') . "...";
		}		
		$area_list = array();
		if(!empty($item_row["s_area_id"])){
			if($item_row["s_area_id"]!=0){
				$area_ids = array();
				$area_ids[] = $item_row["s_area_id"];
				$area_list = $area_dao->find_valid_with_lang_by_ids($lang, $area_ids);
			}
		}
		$desc_area = "";
		foreach ($area_list as $area_row){
			$desc_area = (empty($area_row["aml_name"]) ? $area_row["a_name"] : $area_row["aml_name"]);
		}
		
		$small_area_list = array();
		if(!empty($item_row["s_small_area_id"])){
			if($item_row["s_small_area_id"]!=0){
				$small_area_ids = array();
				$small_area_ids[] = $item_row["s_small_area_id"];
				$small_area_list = $area_dao->find_valid_with_lang_by_ids($lang, $small_area_ids);
			}
		}
		
		$desc_small_area = "";
		foreach($small_area_list as $small_area_row) {
			if($small_area_row["a_id"] == $item_row["s_small_area_id"]) {
				$desc_small_area = (empty($small_area_row["aml_name"]) ? $small_area_row["a_name"] : $small_area_row["aml_name"]);
			}
		}
		if($item_row["fr_main_photo"]!=0){
			$homestay_photo_url = $image_server_url . '/photos/' . (is_production() ? 'car' : 'car_alpha') . '/route/' . $item_row["fr_id"] . '/' . $item_row["fr_main_photo"] . '.jpg';
// 			$homestay_photo_url = $img_server . '/photos/' . (is_production() ? 'car' : 'car_alpha') . '/route/' . $item_row["fr_main_photo"] . '.jpg';
		}
		
		$type_code = 11;
		$price = !empty($item_row["fr_price"]) ? $item_row["fr_price"] : 0;
		$price = 0;
		$oriPrice = 0;
	
	?>
	<figure class="collectWrap">
		<div class="cImg"<?php if(!empty($homestay_photo_url)){ ?> style="background-image: url('<?php echo $homestay_photo_url; ?>');"<?php } ?>></div>
		<div class="tag"><?php echo $type_name; ?></div>
		<div class="myLovest" title="收藏">
			<i class="fa fa-heart" aria-hidden="true" data-type="<?php echo $type_code; ?>" data-id="<?php echo $item_id; ?>"></i>
		</div>
		<figcaption class="collectInfo">
			<a href="/booking/<?= $area_code ?>/<?= $item_id ?>/">
				<h3><?php echo $item_name; ?></h3>
				<div class="lpWrap">
					<div class="location">
						<i class="fa fa-map-marker" aria-hidden="true"></i>
						<span>
						<?php 
						if($desc_area!=''){
							echo $desc_area; 
							if($desc_small_area!=''){
								?> - <?php
								echo $desc_small_area;
							}
						} 
						?>
						</span>
					</div>
					<div class="price">
						<?php if($price>0){ ?>
						<span class="currency">NTD</span> <span class="num"><?php echo number_format($price); ?></span>
						<?php } ?>
					</div>
				</div>
				<div class="oriPrice"><?php if($oriPrice>0){ echo $oriPrice; } ?></div>
			</a>
		</figcaption>
	</figure>
	<?php } ?>
</div>