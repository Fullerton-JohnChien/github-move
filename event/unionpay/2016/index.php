<?php
include_once('../../../web/config.php');
$homeStayChannelProjectService = new Home_stay_channel_project_service();
$db_reader_travel = Dao_loader::__get_checked_db_reader ();
// 預設
$start_date = date("Y-m-d");
$end_date = date("Y-m-d",strtotime($start_date."+1 day"));
$item = !empty($_REQUEST['item']) ? $_REQUEST['item'] : 1;
$mainArea = !empty($_REQUEST['mainArea']) ? $_REQUEST['mainArea'] : 1; //北中南
$subArea = !empty($_REQUEST['subArea']) ? $_REQUEST['subArea'] : 0;    //地區
// if($item == 4 && empty($_REQUEST['mainArea'])) $mainArea = 2;
$image_server_url = get_config_image_server();
$pageno = isset($_REQUEST["page"])?$_REQUEST["page"]:1;
$pageSize = 9;

//will
$cph_channel_project_id = 1;  //hf_channel_project.cp_id
// $cph_phase_id = 1; 						//hf_channel_project_homestay.cph_phase

$adventure = array();
$homestay_data = $homeStayChannelProjectService->find_all_homestay_by_channel_id($cph_channel_project_id);
if(!empty($homestay_data)){
	$adventure = $homestay_data;
}

$adventure_id_list = array();
foreach($adventure as $v){
	//if($k == ($mainArea - 1)){
	// 主區域全部
	//if($subArea == 0){
	foreach ($v as $v2){
		foreach ($v2 as $v3){
			array_push($adventure_id_list, $v3);
		}
	}
	//}
	//}
}

if($item == 1) $home_stay_row = $adventure;

// 組要顯示的民宿id
$hs_id_array = array();
foreach($home_stay_row as $k => $v){
	if($k == ($mainArea - 1)){
		// 主區域全部
		if($subArea == 0){
			foreach ($v as $k2 => $v2){
				foreach ($v2 as $k3 => $v3){
					array_push($hs_id_array, $v3);
				}
			}
		}else{
			foreach ($v as $k2 => $v2){
				if($k2 == ($subArea - 1)){
					foreach ($v2 as $k3 => $v3){
						array_push($hs_id_array, $v3);
					}
				}
			}
		}
	}
}
//var_dump($hs_id_array);
$idStr = '';
foreach ($hs_id_array as $vs) {
	if($idStr != '') $idStr .= ', ';
	$idStr .= $vs;
}

// 依組出的旅宿ids取得旅宿資訊
$cond = array();
$cond['hs_ids'] = $idStr;
// $cond['start_date'] = $start_date;
// $cond['end_date'] = $end_date;
$homeStayList = $homeStayChannelProjectService->find_home_stay_by_cond($cond);
$totalRow = count($homeStayList);

// 取得旅宿有參加活動專案的房型價格 hsrp_type、售價、折扣金額
$room_price_list = $homeStayChannelProjectService->find_home_stay_room_price_type_and_sell_price_and_discount_by_channel_project_id_and_date($cph_channel_project_id, $start_date);

// 依折扣後金額取最低價格顯示活動前台頁面(每晚TWD xxx起)
$homeStayList = $homeStayChannelProjectService->cal_price_choose_cheapest_for_web($homeStayList, $room_price_list, $start_date);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<? include "../../../web/pages/common/head.php"; ?>
<title>【Tripitta】銀聯卡專屬優惠</title>
<link href="css/reset.css" rel="stylesheet" type="text/css">
<link href="css/cnunionpay.css" rel="stylesheet" type="text/css">
<link href="css/font-awesome.css" rel="stylesheet" type="text/css">
<link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link href="css/pagination.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="/web/css/main.css?01121536">
<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
<script type="text/javascript">
var item = <?= $item ?>;
var mainArea = <?= $mainArea ?>;
var subArea = <?= $subArea ?>;
$(function(){
	for(i=1;i<=5;i++){
		if(mainArea != i) $('#areaId_'+i).hide();
		//$('#mainAreaLink_'+i).attr('class', 'bt01');
	}
});
/*
function showArea(id) {
	for(i=1;i<=5;i++){
		$('#areaId_'+i).hide();
		$('#mainAreaLink_'+i).attr('class', 'bt01');
	}
	$('#mainAreaLink_'+id).attr('class', 'bt01 active');
	$('#areaId_'+id).toggle();
}*/
// $(document).click(function () {
// 	for(i=1;i<=5;i++){
// 		$('#areaId_'+i).hide();
// 		//$('#mainAreaLink_'+i).attr('class', 'bt01');
// 	}
// });
function setPage(pageno){
	if($('#page')!=null) $('#page').val(pageno);
	location.href='index.php?mainArea=<?= $mainArea ?>&subArea=<?= $subArea ?>&page='+pageno;
}
function showMember(){
	$('#member').toggle();
}
function showlang(){
	$('#lang').toggle();
}
function showcurrency(){
	$('#currency').toggle();
}
</script>
</head>
<body>
<header><?php include '../../../web/pages/common/header.php';?></header>
<div class="Banner_pic" style="background-image: url(images/index_banner_big_3.gif);"><div class="all"><div class="cnunionpay" style="background-image: url(images/cnunionpay.png);"></div></div></div>
<div class="memubar">
<div class="bar001">
<ul>
<li><a href="index.php?mainArea=1" id="" class="bt01 <?= ($mainArea == 1)? 'active':'' ?>">北部旅宿</a></li>
<li><a href="index.php?mainArea=2" id="" class="bt01 <?= ($mainArea == 2)? 'active':'' ?>">中部旅宿</a></li>
<li><a href="index.php?mainArea=3" id="" class="bt01 <?= ($mainArea == 3)? 'active':'' ?>">南部旅宿</a></li>
<li><a href="index.php?mainArea=4" id="" class="bt01 <?= ($mainArea == 4)? 'active':'' ?>">東部旅宿</a></li>
<!-- <li><a href="#" class="bt01">溫泉旅宿</a></li> -->
</ul>
</div>
<div class="bar002" id="areaId_1" style="display: <?= ($mainArea == 1)? ';':'none;' ?>">
        <ul>
        <?php if(isset($home_stay_row[0][0])){ ?><?php if(count($home_stay_row[0][0]) > 0){?><li><a class="bt02 <?= ($subArea == 1 && $mainArea == 1)? 'active':'' ?>" href="index.php?mainArea=1&subArea=1">台北 (<?= count($home_stay_row[0][0]) ?>) </a></li><?php } ?><?php }else{ ?><li></li><?php } ?>
        <?php if(isset($home_stay_row[0][1])){ ?><?php if(count($home_stay_row[0][1]) > 0){?><li><a class="bt02 <?= ($subArea == 2 && $mainArea == 1)? 'active':'' ?>" href="index.php?mainArea=1&subArea=2">九份.金瓜石 (<?= count($home_stay_row[0][1]) ?>) </a></li><?php } ?><?php }else{ ?><li></li><?php } ?>
        <?php if(isset($home_stay_row[0][2])){ ?><?php if(count($home_stay_row[0][2]) > 0){?><li><a class="bt02 <?= ($subArea == 3 && $mainArea == 1)? 'active':'' ?>" href="index.php?mainArea=1&subArea=3">宜蘭 (<?= count($home_stay_row[0][2]) ?>) </a></li><?php } ?><?php }else{ ?><li></li><?php } ?>
        <?php if(isset($home_stay_row[0][3])){ ?><?php if(count($home_stay_row[0][3]) > 0){?><li><a class="bt02 <?= ($subArea == 4 && $mainArea == 1)? 'active':'' ?>" href="index.php?mainArea=1&subArea=4">礁溪溫泉 (<?= count($home_stay_row[0][3]) ?>) </a></li><?php } ?><?php }else{ ?><li></li><?php } ?>
        </ul>
</div>
<div class="bar002" id="areaId_2" style="display: <?= ($mainArea == 2)? ';':'none;' ?>">
        <ul>
        <?php if(isset($home_stay_row[1][0])){ ?><?php if(count($home_stay_row[1][0]) > 0){?><li><a class="bt02 <?= ($subArea == 1 && $mainArea == 2)? 'active':'' ?>" href="index.php?mainArea=2&subArea=1">台中 (<?= count($home_stay_row[1][0]) ?>) </a></li><?php } ?><?php }else{ ?><li></li><?php } ?>
        <?php if(isset($home_stay_row[1][1])){ ?><?php if(count($home_stay_row[1][1]) > 0){?><li><a class="bt02 <?= ($subArea == 2 && $mainArea == 2)? 'active':'' ?>" href="index.php?mainArea=2&subArea=2">南投 (<?= count($home_stay_row[1][1]) ?>) </a></li><?php } ?><?php }else{ ?><li></li><?php } ?>
        <li></li>
        <li></li>
        </ul>
</div>
<div class="bar002" id="areaId_3" style="display: <?= ($mainArea == 3)? ';':'none;' ?>">
        <ul>
        <?php if(isset($home_stay_row[2][0])){ ?><?php if(count($home_stay_row[2][0]) > 0){?><li><a class="bt02 <?= ($subArea == 1 && $mainArea == 3)? 'active':'' ?>" href="index.php?mainArea=3&subArea=1">台南 (<?= count($home_stay_row[2][0]) ?>) </a></li><?php } ?><?php }else{ ?><li></li><?php } ?>
        <?php if(isset($home_stay_row[2][1])){ ?><?php if(count($home_stay_row[2][1]) > 0){?><li><a class="bt02 <?= ($subArea == 2 && $mainArea == 3)? 'active':'' ?>" href="index.php?mainArea=3&subArea=2">高雄 (<?= count($home_stay_row[2][1]) ?>) </a></li><?php } ?><?php }else{ ?><li></li><?php } ?>
 		<?php if(isset($home_stay_row[2][2])){ ?><?php if(count($home_stay_row[2][2]) > 0){?><li><a class="bt02 <?= ($subArea == 3 && $mainArea == 3)? 'active':'' ?>" href="index.php?mainArea=3&subArea=3">墾丁 (<?= count($home_stay_row[2][2]) ?>) </a></li><?php } ?><?php }else{ ?><li></li><?php } ?>
        <li></li>
        </ul>
</div>
<div class="bar002" id="areaId_4" style="display: <?= ($mainArea == 4)? ';':'none;' ?>">
        <ul>
        <?php if(isset($home_stay_row[3][0])){ ?><?php if(count($home_stay_row[3][0]) > 0){?><li><a class="bt02 <?= ($subArea == 1 && $mainArea == 4)? 'active':'' ?>" href="index.php?mainArea=4&subArea=1">花蓮 (<?= count($home_stay_row[3][0]) ?>) </a></li><?php } ?><?php }else{ ?><li></li><?php } ?>
        <?php if(isset($home_stay_row[3][1])){ ?><?php if(count($home_stay_row[3][1]) > 0){?><li><a class="bt02 <?= ($subArea == 2 && $mainArea == 4)? 'active':'' ?>" href="index.php?mainArea=4&subArea=2">台東 (<?= count($home_stay_row[3][1]) ?>) </a></li><?php } ?><?php }else{ ?><li></li><?php } ?>
        <li></li>
        <li></li>
        </ul>
</div>
</div>
<div class="contents">
    <ul>
    <?php
        $num = ($pageno-1)*$pageSize;
        for ($i=$num;$i<count($homeStayList);$i++){
            $hs = $homeStayList[$i];
    	    if ($num >= $pageSize*$pageno) continue;

    	    $img = '/web/img/no-pic.jpg';
    	    if(!empty($hs['hs_main_photo'])) {
    		 	$img = $image_server_url . '/photos/travel/home_stay/' . $hs['hs_id'] . '/' . $hs['hs_main_photo'] . "_middle.jpg";
    	    }
    		$link = '/booking/' . $hs["a_code"] . '/' . $hs['hs_id'] . '/?event_code=2016unionpay';

    		if($hs['rtp_price'] > 0){
    		 	$sell_price = $hs['rtp_price'];
    		}else{
    		 	$sell_price = $hs['rtp_price'];
            }
    ?>
    <li>
    	<a href="javascript:void(0);" class="product">
        <div class="bnbphoto" style="background-image: url(<?= $img ?>);" alt="<?= $hs['hs_name'] ?>"></div>
        <h4 style="font-weight: normal;"><i class="fa_map fa-map-marker mr-5"></i> <?= $hs['small_area_name']?></h4>
        <h1 style="font-weight: normal;"><?= $hs['hs_name'] ?></h1>
        <span class="sele">每晚TWD <?= number_format($sell_price) ?>起</span>
        </a>
        <div class="product-hover">
        	<a href="<?= $link ?>" class="botton" target="_blank"></a>
        </div>
    </li>
    <?php
        $num++;
    	}
    ?>
    </ul>
</div>
<!--換下一頁-->
<div class="pagination2 manu" style="margin-bottom: 50px;">
<form name="mainpage" id="mainpage" action="web.php" method="post" >
	<input type="hidden" id="page" name="page" value="<?= $pageno ?>" >
	<input type="hidden" id="mainArea" name="mainArea" value="<?= $mainArea ?>" >
	<input type="hidden" id="subArea" name="subArea" value="<?= $subArea ?>" >
</form>
<?php $totalPage=getTotalPage($totalRow,$pageSize) ?>
<?php echo getPageLinkForFront($pageno, $totalPage, 'setPage') ?>
</div>
<!--end 換下一頁-->
<div class="precautions">
<span class="a1">活動注意事項</span>
<ul>
<li>本優惠活動適用所有銀聯卡(卡號以62開頭)之持卡人。</li>
<li>優惠活動期間：2016年01月01日至2016年12月31日。</li>
<li>優惠活動內容：平日訂房3折起之優惠。</li>
<li>優惠活動適用資格/條件：<br>
  (1) 本活動提供平日訂房3折起優惠，需透過本活動頁面線上付款即可享受此優惠。<br>
(2) 如直接進入 <a href="https://www.tripitta.com" target="_blank" style="font-size: 14px;">&lt;Tripitta旅必達官網&gt;</a> 之訂房交易，僅限有特別標示銀聯卡卡友優惠之民宿適用於本活動優惠。 </li>
<li>本活動僅適用於Tripitta旅必達指定之民宿及房型。</li>
<li>本活動頁面標示之價格，已為銀聯卡卡友折扣優惠後之價格。</li>
<li>本優惠活動於台灣春節期間不適用(即2016年02月06日至2016年02月14日)。</li>
<li>本活動之民宿訂房商品及服務由Tripitta旅必達提供，訂位額滿恕不再提供。各民宿商品適用之房型、數量、期間依Tripitta旅必達網站公告為主。銀聯國際僅提供各項優惠訊息，並非提供訂房保證，亦非民宿訂房服務之出售人，與Tripitta旅必達之間並無代理或提供保證。持卡人對於提供訂房服務內容有任何爭議，請洽Tripitta旅必達客服尋求協助。</li>
<li>平日、國定假日之定義依各民宿業者規定而有不同，請於預訂時詳細閱讀各民宿之條款與細則。<br>
  所有訂房條款請查閱<a href="https://www.tripitta.com/service/?qt_id=29&qt_parent_id=0" target="_blank" style="font-size: 14px;">&lt;客服中心訂房公告&gt;</a></li>
<li>銀聯國際及Tripitta旅必達皆保留變更及終止本活動之權利。</li>
</ul>
</div>
<footer><? include "../../../web/pages/common/footer.php"; ?></footer>
<?php if (is_production()) { ?>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
  ga('create', 'UA-70704198-2', 'auto');
  ga('send', 'pageview');
</script>
<?php } ?>
</body>
</html>
