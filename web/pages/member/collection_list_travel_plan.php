					<div class="dataList">
<?
    foreach($item_list as $item_row) {
        $item_id = $item_row["tp_id"];
        $item_type = $item_row["uf_type"];
        $item_name = $item_row["tp_title"];
        $item_desc = $item_row["tp_foreword"];
        if(mb_strlen($item_desc) > MAX_DESCRIPTION_LENGTH) {
            $item_desc = mb_substr($item_desc, 0, MAX_DESCRIPTION_LENGTH, 'utf-8') . "...";
        }
        $desc_small_area = "";
        if(is_production()) {
            $homestay_photo_url = $img_server . '/photos/travel_plan/' . $item_row["tp_id"] . '/data_image/' . $item_row["tp_main_photo"] . '.jpg';
        } else {
            $homestay_photo_url = $img_server . '/photos/travel_plan_alpha/' . $item_row["tp_id"] . '/data_image/' . $item_row["tp_main_photo"] . '.jpg';
        }
?>
						<section>
							<a href="/trip/<?= $item_id ?>/">
								<img src="<?= $homestay_photo_url ?>" alt="">
							</a>
							<article>
								<div class="del" title="刪除" data-id="<?= $item_id ?>" data-type="<?= $item_type ?>">
									<i class="fa fa-trash-o fa-2"></i>
								</div>
								<a href="/trip/<?= $item_id ?>/">
									<div class="tagWrap">
										<i class="fa fa-map-marker"></i>
										<div class="tags">
											<span><?= $desc_small_area ?></span>
										</div>
									</div>
									<h1><?= $item_name ?></h1>
									<div class="content">
										<?= $item_desc ?>
									</div>
								</a>
							</article>
						</section>
<?
    }
?>