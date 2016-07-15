					<div class="dataList">
<?
    $small_area_ids = array();
    foreach($item_list as $item_row) {
        $small_area_ids[] = $item_row["tc_small_area_id"];
    }
    $area_dao = Dao_loader::__get_area_dao();
    $small_area_list = $area_dao->find_valid_with_lang_by_ids($lang, $small_area_ids);

    if(!isset($item_list)) {
        $item_list = array();
    }

    foreach($item_list as $item_row) {
        $item_id = $item_row["tc_id"];
        $item_type = $item_row["uf_type"];
        $item_name = $item_row["tc_name"];
        $item_desc = $item_row["tc_full_desc"];
        if(mb_strlen($item_desc) > MAX_DESCRIPTION_LENGTH) {
            $item_desc = mb_substr($item_desc, 0, MAX_DESCRIPTION_LENGTH, 'utf-8') . "...";
        }
        $desc_small_area = "";
        foreach($small_area_list as $small_area_row) {
            if($small_area_row["a_id"] == $item_row["tc_small_area_id"]) {
                $desc_small_area = (empty($small_area_row["aml_name"]) ? $small_area_row["a_name"] : $small_area_row["aml_name"]);
            }
        }
        if(is_production()) {
            $homestay_photo_url = $img_server . '/photos/taiwan_content/' . $item_row["tc_id"] . '/' . $item_row["tc_main_photo"] . '.jpg';
        } else {
            $homestay_photo_url = $img_server . '/photos/taiwan_content_alpha/' . $item_row["tc_id"] . '/' . $item_row["tc_main_photo"] . '.jpg';
        }
        // printmsg($item_row["tc_type"]);
        $type_code = '';
        if($item_row["tc_type"] == 7) {
            $type_code = 'food';
        } else if($item_row["tc_type"] == 8) {
            $type_code = 'scenic';
        } else if($item_row["tc_type"] == 82) {
            $type_code = 'gift';
        } else if($item_row["tc_type"] == 12 || $item_row["tc_type"] == 14 || $item_row["tc_type"] == 15) {
            $type_code = 'event';
        }

?>
						<section>
							<a href="/location/<?= $type_code ?>/<?= $item_id ?>/">
								<img src="<?= $homestay_photo_url ?>" alt="">
							</a>
							<article>
								<div class="del" title="刪除" data-id="<?= $item_id ?>" data-type="<?= $item_type ?>">
									<i class="fa fa-trash-o fa-2"></i>
								</div>
								<a href="/location/<?= $type_code ?>/<?= $item_id ?>/">
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