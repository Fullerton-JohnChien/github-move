<?php
require_once __DIR__ . '/../../config.php';
$author_service = new author_service();

$a_id = get_val('a_id');
$url = get_config_image_server();

$title = "作者專區 - Tripitta 旅必達";

// 作者
$plan_dao = Dao_loader::__get_plan_dao();
$author_list = array();
$author_list = $author_service->get_author_by_author_id($a_id);
$article_list = $plan_dao->get_plan_by_author_id($a_id);
$author_list['avatar'] = str_replace('<br />', '', $author_list['avatar']);
$author_list['avatar'] = str_replace('width="100"', 'width="165"', $author_list['avatar']);
$article_list_count = 0;
foreach ($article_list as $key => $value) {
	if ($value['tp_status'] == 1) {
		$article_list_count++;
	}
}

if (!isset($author_list['author_row']['a_status']) || $author_list['author_row']['a_status'] != 1) {
	alertmsg("查無作者!!", '/');
	exit();
}
?>
<!Doctype html>
<html lang="zh-Hant">
<head>
	<? include __DIR__ . "/../common/head_new.php"; ?>
	<link rel="stylesheet" type="text/css" href="/web/pages/trip/css/frame.css">
	<link rel="stylesheet" type="text/css" href="/web/pages/trip/css/page.css">
	<link rel="stylesheet" type="text/css" href="/web/css/main.css?01121536">
	<link rel="stylesheet" href="/web/pages/trip/css/home_stay.css"  type="text/css"/>
	<title><?= $title ?></title>
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<!-- Add fancyBox main JS and CSS files -->
	<script src="/web/pages/trip/js/jquery.easing.1.3.js"></script>
	<script src="/web/pages/trip/js/jquery.scrollTo.min.js"></script>
	<!-- //author_area -->
	<script src="/web/pages/trip/js/actions.js"></script>
	<script>
		<?php if ($author_list['ap_banner_image'] != "") { ?>
		$(function() {
			$('#author_kv').css('background-image', 'url("<?php echo $author_list['ap_banner_image']; ?>")');
			$('#author_kv').css('background-repeat', 'repeat');
		});
		<?php } ?>
	</script>
</head>
<body class="main">
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<article class="top-container">
		<div class="container">
			<div class="author_kv" id="author_kv">
				<div class="intro">
					<h1 class="heading">每個人一生都總在自由和歸屬之間掙紮，那是條窄徑</h1>
					<div class="essay">
						每個人一生都總在自由和歸屬之間掙紮，那是條窄徑。
						<br/> 我有時候會為得到歸屬而犧牲掉自由，
						<br/> 但更多時候我已經對歸屬放棄了一切希望。—— 珍妮特·溫特森
					</div>
				</div>
			</div>
			<!-- //author_kv -->
		</div>
		<!-- //contaner -->
	</article>
	<!-- //top-contaner -->
	<article class="author-area pt40">
		<div class="container">
			<div class="left">
				<div class="sort-bar">
					<div class="select">
						<select name="" id="">
							<option value="時間排序">行程遊記</option>
						</select>
					</div>
					<span class="sort-result">共<?php echo $article_list_count; ?>筆</span>
				</div>
				<!-- //sort-bar -->
				<ol class="item-list">
					<?php
						foreach ($article_list as $key => $value) {
							if ($value['tp_status'] == 1) {
								$value['tp_begin_date'] = str_replace("-", "/", $value['tp_begin_date']);
								$value['tp_end_date'] = str_replace("-", "/", $value['tp_end_date']);
								mb_internal_encoding("UTF-8");
								$count = mb_strlen($value['tp_foreword'], 'UTF-8');
								$value['tp_foreword'] = ($count >= 71) ? mb_substr($value['tp_foreword'], 0, 71)."..." : $value['tp_foreword'];
								$plan_detail = $plan_dao->get_plan_detail_by_plan_id($value['tp_id'], 2);
								$plan_detail[0]['tpd_image'] = ($plan_detail[0]['tpd_image'] == "") ? "1545.jpg" : $plan_detail[0]['tpd_image'];
								$author_pic = (isset($plan_detail[0]['tpd_image']) && $plan_detail[0]['tpd_image'] != "" ) ? $url."/photos/".(is_production() ? 'travel_plan' : 'travel_plan_alpha')."/".$value['tp_id']."/data_image/".$plan_detail[0]['tpd_image'] : "";
					?>
					<li>
						<a href="/trip/<?php echo $value['tp_id']; ?>/">
							<?php if ($author_pic != "") { ?>
							<img src="<?php echo $author_pic; ?>" alt="">
							<?php } else { ?>
							<img src="img/author_pic.jpg" alt="">
							<?php } ?>
							<div class="day">
								<span><?php echo $value['tp_days']; ?></span>
								<span>DAY</span>
							</div>
							<h4 class="title"><?php echo $value['tp_title']; ?></h4>
							<div class="status">
								<span class="category">行程遊記</span>
								<span>
									<i class="fa fa-heart"></i><?php echo $value['tpe_collect_total']; ?></span>
								<span>
									<i class="fa fa-eye"></i><?php echo $value['tpe_click_total']; ?></span>
								<span>
									<i class="fa fa-calendar"></i><?php echo $value['tp_begin_date']; ?>-<?php echo $value['tp_end_date']; ?></span>
							</div>
							<!-- //status -->
							<div class="con"><?php echo $value['tp_foreword']; ?></div>
						</a>
					</li>
					<?php
							}
						}
					?>
				</ol>
				<ul id="pagination" class="pagination"></ul>
			</div>

			<div class="right">
				<div class="main-writer">
					<a class="cover-photo">
						<img src="<?php echo $author_list['avatar_img']; ?>" width="180" height="180" alt="">
					</a>
					<span class="category">旅遊媒體</span>
					<span class="author"><?php echo $author_list['author_row']['a_title']; ?><?php if ($author_list['author_row']['a_nickname'] != "") { ?>-<?php } ?><?php echo $author_list['author_row']['a_nickname']; ?></span>
					<?php if ($author_list['author_row']['a_blog_url'] != "") { ?>
					<span class="category">
						<a href="<?php echo $author_list['author_row']['a_blog_url']; ?>" target="_blank" style="color:blue;">
							作者部落格
						</a>
					</span>
					<?php } ?>
					<p><?php echo $author_list['author_row']['ap_intro']; ?></p>
				</div>
			</div>
		</div>
	</article>
	<footer class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
</body>
</html>