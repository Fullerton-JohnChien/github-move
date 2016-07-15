<?php
require_once 'config.php';
// $t1 = microtime(true);

$travel_dbReader = Dao_loader::__get_checked_db_reader();

$hs_sql = "SELECT hs_id,hs_name,hs_internal_view_status,hs_status,a_code FROM hf_home_stay ";
$hs_sql .= "INNER JOIN hf_area ON a_id = hs_area_id ";
// $hs_sql .= "WHERE hs_status = 1 ";
$hs_list = $travel_dbReader->executeReader($hs_sql);
// printmsg($hs_list);


$tc_sql = "SELECT tc_id,tc_type,tc_name,tc_status FROM hf_taiwan_content ";
$tc_sql .= "INNER JOIN hf_taiwan_content_detail_tw ON tc_detail_id = tc_id ";
$tc_sql .= "WHERE tc_type IN (7,8) ";
// $tc_sql .= "AND tc_status = 1 ";
$tc_list = $travel_dbReader->executeReader($tc_sql);
// printmsg($tc_list);

$a_sql = "SELECT a_code,a_status FROM hf_area ";
$a_sql .= "WHERE a_parent_id = 0 ";
$a_sql .= "AND a_category = 'homestay' ";
// $a_sql .= "AND a_status = 1 ";
$a_sql .= "ORDER BY a_display_order ";
$a_list = $travel_dbReader->executeReader($a_sql);
// printmsg($a_list);



$xml_str = '<?xml version="1.0" encoding="UTF-8"?>';
$xml_str .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">';

$expire_date = date('Y-m-d', strtotime('-1day'));

// https://www.tripitta.com/booking/hualien/1596/
foreach($hs_list as $hs) {
	if (1 != $hs['hs_status'] || ($hs['hs_internal_view_status'] != 2 && $hs['hs_internal_view_status'] != 3)) continue;

    $link = 'https://www.tripitta.com/booking/' . $hs['a_code'] . '/' . $hs['hs_id'] . '/';
//     echo '<a href="', $link, '">', $hs['hs_name'], '</a><br>';
    $xml_str .= '<url><loc>' . $link . '</loc>';
//     if (1 != $hs['hs_status'] || ($hs['hs_internal_view_status'] != 2 && $hs['hs_internal_view_status'] != 3)) $xml_str .= '<expires>' . $expire_date . '</expires>';
    $xml_str .= '</url>';
}

// tc_type 7:美食 8:景點
// https://www.tripitta.com/location/spot/7296/
foreach ($tc_list as $tc) {
	if (1 != $tc['tc_status']) continue;

    $category = 'food';
    if (8 == $tc['tc_type']) $category = 'spot';
    $link = 'https://www.tripitta.com/location/' . $category . '/' . $tc['tc_id'] . '/';
//     echo '<a href="', $link, '">', $tc['tc_name'], '</a><br>';
    $xml_str .= '<url><loc>' . $link . '</loc>';
//     if (1 != $tc['tc_status']) $xml_str .= '<expires>' . $expire_date . '</expires>';
    $xml_str .= '</url>';
}
// printmsg('秏時：' . (microtime(true) - $t1));

// https://www.tripitta.com/booking/taipei/
foreach ($a_list as $a) {
	if (empty($a['a_status'])) continue;

    $link = 'https://www.tripitta.com/booking/' . $a['a_code'] . '/';
    $xml_str .= '<url><loc>' . $link . '</loc>';
//     if (empty($a['a_status'])) $xml_str .= '<expires>' . $expire_date . '</expires>';
    $xml_str .= '</url>';
}

$xml_str .= '</urlset>';

$file = fopen('sitemap.xml','w'); //開啟檔案
fwrite($file, $xml_str);
fclose($file);
?>