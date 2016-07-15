					<div class="dataList">
<?


    if(!isset($item_list)) {
        $item_list = array();
    }

    $small_area_ids = array();
    foreach($item_list as $item_row) {
        $small_area_ids[] = $item_row["hs_small_area_id"];
    }
    $area_ids = array();
    foreach($item_list as $item_row) {
        $area_ids[] = $item_row["hs_area_id"];
    }
    $area_dao = Dao_loader::__get_area_dao();
    $area_list = $area_dao->find_valid_with_lang_by_ids($lang, $area_ids);
    $small_area_list = $area_dao->find_valid_with_lang_by_ids($lang, $small_area_ids);


    foreach($item_list as $item_row) {
        $item_id = $item_row["hs_id"];
        $item_type = $item_row["uf_type"];
        $item_name = empty($item_row["hsml_name"]) ? $item_row["hs_name"] : $item_row["hsml_name"];
        $item_desc = empty($item_row["hsml_desc"]) ? $item_row["hs_desc"] : $item_row["hsml_desc"];
        if(mb_strlen($item_desc) > MAX_DESCRIPTION_LENGTH) {
            $item_desc = mb_substr($item_desc, 0, MAX_DESCRIPTION_LENGTH, 'utf-8') . "...";
        }
        $desc_small_area = "";
        foreach($small_area_list as $small_area_row) {
            if($small_area_row["a_id"] == $item_row["hs_small_area_id"]) {
                $desc_small_area = (empty($small_area_row["aml_name"]) ? $small_area_row["a_name"] : $small_area_row["aml_name"]);
                break;
            }
        }
        $area_code = 'test';
        foreach ($area_list as $area_row) {
            if($area_row["a_id"] == $item_row["hs_area_id"]) {
                $area_code = $area_row["a_code"];
                break;
            }
        }

        $homestay_photo_url = $img_server . '/photos/travel/home_stay/' . $item_row["hs_id"] . '/' . $item_row["hs_main_photo"] . '_middle.jpg';
?>
						<section>
							<a href="/booking/<?= $area_code ?>/<?= $item_id ?>/">
								<img src="<?= $homestay_photo_url ?>" alt="">
							</a>
							<article>
								<div class="del" title="刪除" data-id="<?= $item_id ?>" data-type="<?= $item_type ?>">
									<i class="fa fa-trash-o fa-2"></i>
								</div>
								<a href="/booking/<?= $area_code ?>/<?= $item_id ?>/">
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