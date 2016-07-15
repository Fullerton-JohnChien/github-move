<?
/**
 * 說明：客服中心
 * 作者：Steak
 * 日期：2015年11月2日
 * 備註：每個選項都一定要兩層
 * 11/3 增加ajax分頁功能
 *
 */
require_once __DIR__ . '/../../config.php';

$pageSize = 5;
$qt_id = get_num('qt_id');
$parent_id = get_num('qt_parent_id');

$qa_type_dao =  Dao_loader::__get_qa_type_dao();
$qa_question_dao = Dao_loader::__get_qa_question_dao();

// 預設
$qaTypeList = $qa_type_dao ->findQaTypeListByCondition('tripitta', 'tw', 0);
if (empty($qt_id)) $qt_id = $qaTypeList[0]['qt_id'];
if (empty($parent_id)){
	$sub_List = $qa_type_dao ->findQaTypeListByCondition('tripitta', 'tw', $qt_id);
	$qt_id = $sub_List[0]['qt_id'];
	$parent_id = $sub_List[0]['qt_parent_id'];
}
// 標題
$type_row = $qa_type_dao ->load($qt_id);
$titleName = $type_row["qt_name"];

// 總共有幾頁
$totalPages = 1;
$qaQuestion_total = $qa_question_dao ->countQaQuestion($qt_id);
if($qaQuestion_total > 0) $totalPages = ceil($qaQuestion_total/$pageSize);

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<? include __DIR__ . "/../common/head.php"; ?>
	<title>QA問與答 - Tripitta 旅必達</title>
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<style type="text/css">
		a{text-decoration: none;}
	</style>
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="/web/js/jquery.twbsPagination.js"></script>
	<script type="text/javascript">
    $(function() {
	    $('#pagination').twbsPagination({
			totalPages: <?= $totalPages ?>,
			visiblePages: 5,
			first: "第一頁",
			prev: "上一頁",
			next: "下一頁",
			last: "最後一頁",
			onPageClick: function (event, page) {
				var p = {};
			    p.func = 'qa';
			    p.qt_id = <?= $qt_id ?>;
			    p.page = page;
			    p.pageSize = <?= $pageSize ?>;
			    console.log(p);
			    $.post("/web/ajax/ajax.php", p, function(data) {
			        if(data.code == '0000'){
						$('#content').html(data.data);
						var $body = (window.opera) ? (document.compatMode == "CSS1Compat" ? $('html') : $('body')) : $('html,body');
						$body.animate({
							scrollTop: 0
						}, 600);
			        }
			    }, 'json');
			}
	    });
    });
	function chgnge_sub(id, p_id) {
		 location.href = '/service/?qt_id='+id+'&qt_parent_id='+p_id;
	}
	function contact(id, p_id){
		location.href = '/contact/?qt_id='+id+'&qt_parent_id='+p_id;
	}
	</script>
</head>
<body>
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="qa-container">
		<h1 class="title"><?= $titleName ?></h1>
		<div class="tile">
			<aside>
				<?php foreach ($qaTypeList as $cl){
						$subQaTypeList = $qa_type_dao ->findQaTypeListByCondition('tripitta', 'tw', $cl['qt_id']);
						if (!empty($subQaTypeList)){
				?>
						<dl>
							<dt <?php echo ($qt_id == $cl['qt_id'] || $parent_id == $cl['qt_id']) ? 'class="active"' : ''?> ><a href="/service/?qt_id=<?= $cl['qt_id']?>&qt_parent_id=<?= $cl['qt_parent_id']?>"><?= $cl['qt_name'] ?></a></dt>
							<?php foreach($subQaTypeList as $s){
							?>
							<dd <?php echo ($qt_id == $s['qt_id']) ? 'class="active"' : ''?> <?= ($qt_id != $cl['qt_id'] ||  ($parent_id != 0) ? $parent_id != $cl['qt_id'] : $qt_id != $cl['qt_id'] )? 'style="display:none;"':'' ?> onclick="chgnge_sub(<?= $s['qt_id'] ?>, <?= $s['qt_parent_id'] ?>)"><?= $s['qt_name'] ?></dd>
							<?php } ?>
						</dl>
						<?php } ?>
				<?php } ?>
			</aside>
			<article>
				<div class="wrapper">
					<div class="btnWrap">
						<input type="button" value="聯絡我們" onclick="contact(<?= $qt_id ?>, <?= $parent_id ?>)" class="submit">
					</div>
					<div class="content" id="content"></div>
					<?php // if($qaQuestion_total > 5){?>
						<ul id="pagination" class="pagination"></ul>
					<?php //} ?>
				</div>
			</article>
		</div>
	</div>
	<footer class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
</body>
</html>