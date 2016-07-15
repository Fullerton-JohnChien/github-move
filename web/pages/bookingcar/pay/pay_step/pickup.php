<?php
/**
 * 說明：交通預定 - 接機 - 付款流程
 * 作者：Casper <casper.lee@fullerton.com.tw>
 * 日期：2016年6月7日
 * 備註：
 */
require_once __DIR__ . '/../../../../config.php';
header("Content-Type:text/html; charset=utf-8");
if(!empty($_SESSION["pay_step"])){
	$checked = array("same", "license", "en_driver", "jp_driver", "ct_driver", "ko_driver",
					 "baby_set", "child_set", "placard", "female", "accessible", "wifi", "policy_chk");
	$selected = array("gender", "living_country_id", "car_gender", "car_living_country_id", "arrival_time", "pickup_time");
	$p_step = $_SESSION["pay_step"];
	$pickup_type = !empty($p_step["pickup_type"]) ? $p_step["pickup_type"] : null;
	$fr_id = $p_step["fr_id"];
	$begin_date = $p_step["begin_date"];
	$car_adult = !empty($p_step["adult"]) ? $p_step["adult"] : null;
	$car_child = !empty($p_step["child"]) ? $p_step["child"] : null;
	$type = $p_step["type"];
	$coupon = $p_step["coupon"];
	$p_content = $p_step;
	unset($p_content["pickup_type"]);
	unset($p_content["fr_id"]);
	unset($p_content["begin_date"]);
	unset($p_content["adult"]);
	unset($p_content["child"]);
	unset($p_content["type"]);
	unset($p_content["coupon"]);
	unset($_SESSION["pay_step"]);
}else{
	$fr_id = get_val("fr_id");
	$pickup_type = get_val("pickup_type");
	$begin_date = get_val("begin_date");
	$car_adult = get_val_with_default("car_adult", 0);
	$car_child = get_val_with_default("car_child", 0);
	$type = get_val("type");
	$coupon = '';
}
$type_name = $pickup_type==2 ? "接機" : "送機";
$type_arrival_name = $pickup_type==2 ? "抵達" : "出發";
$passenger_count = $car_adult+$car_child;
$min_birthday = "1/1/1920";
$max_birthday = date("m/d/Y", strtotime("-365 days"));
$store_service = new store_service();
$tripitta_service = new tripitta_service();
$tripitta_web_service = new tripitta_web_service();
$tripitta_car_service = new tripitta_car_service();
$fleet_route = $tripitta_service->get_fleet_route_detail($type, $fr_id);
$car_id = $fleet_route["c_id"];
$luggage = $tripitta_service->find_car_luggage($car_id, $passenger_count);
//print_r($_SESSION);print_r("<br>");
$name = (isset($_SESSION['travel.ezding.user.data']['name']) && $_SESSION['travel.ezding.user.data']['name'] != "") ? $_SESSION['travel.ezding.user.data']['name'] : "";
$gender = (isset($_SESSION['travel.ezding.user.data']['gender']) && $_SESSION['travel.ezding.user.data']['gender'] != "") ? $_SESSION['travel.ezding.user.data']['gender'] : "";
$living_country_id = (isset($_SESSION['travel.ezding.user.data']['living_country_id']) && $_SESSION['travel.ezding.user.data']['living_country_id'] != "") ? $_SESSION['travel.ezding.user.data']['living_country_id'] : "";
$mobile = (isset($_SESSION['travel.ezding.user.data']['mobile']) && $_SESSION['travel.ezding.user.data']['mobile'] != "") ? $_SESSION['travel.ezding.user.data']['mobile'] : "";
$email = (isset($_SESSION['travel.ezding.user.data']['email']) && $_SESSION['travel.ezding.user.data']['email'] != "") ? $_SESSION['travel.ezding.user.data']['email'] : "";

// 附加服務 start
$store_id = $fleet_route['s_id'];
$fleet_facility = $tripitta_car_service->find_fleet_facility_by_store_id($store_id);
$ff_id = $fleet_facility['ff_id'];

$ffd_category = "tour.guide.license"; //
$facility_detail = $tripitta_car_service->find_fleet_facility_detail_by_ff_id_category($ff_id, $ffd_category);
$license_selected = (empty($facility_detail)) ? 0 : $facility_detail[0]['ffd_designation_price'];
$license_selected = number_format($license_selected);
$license_checkbox = $facility_detail[0]['ffd_designation'];
$license_id = $facility_detail[0]["ffd_id"];

$ffd_category = "lang.support.en"; //
$facility_detail = $tripitta_car_service->find_fleet_facility_detail_by_ff_id_category($ff_id, $ffd_category);
$en_selected = (empty($facility_detail)) ? 0 : $facility_detail[0]['ffd_designation_price'];
$en_selected = number_format($en_selected);
$en_checkbox = $facility_detail[0]['ffd_designation'];
$en_id = $facility_detail[0]["ffd_id"];

$ffd_category = "lang.support.jp"; //
$facility_detail = $tripitta_car_service->find_fleet_facility_detail_by_ff_id_category($ff_id, $ffd_category);
$jp_selected = (empty($facility_detail)) ? 0 : $facility_detail[0]['ffd_designation_price'];
$jp_selected = number_format($jp_selected);
$jp_checkbox = $facility_detail[0]['ffd_designation'];
$jp_id = $facility_detail[0]["ffd_id"];

$ffd_category = "lang.support.ct"; //
$facility_detail = $tripitta_car_service->find_fleet_facility_detail_by_ff_id_category($ff_id, $ffd_category);
$ct_selected = (empty($facility_detail)) ? 0 : $facility_detail[0]['ffd_designation_price'];
$ct_selected = number_format($ct_selected);
$ct_checkbox = $facility_detail[0]['ffd_designation'];
$ct_id = $facility_detail[0]["ffd_id"];

$ffd_category = "lang.support.ko"; //
$facility_detail = $tripitta_car_service->find_fleet_facility_detail_by_ff_id_category($ff_id, $ffd_category);
$ko_selected = (empty($facility_detail)) ? 0 : $facility_detail[0]['ffd_designation_price'];
$ko_selected = number_format($ko_selected);
$ko_checkbox = $facility_detail[0]['ffd_designation'];
$ko_id = $facility_detail[0]["ffd_id"];

$ffd_category = "baby.seat"; //
$facility_detail = $tripitta_car_service->find_fleet_facility_detail_by_ff_id_category($ff_id, $ffd_category);
$baby_selected = (empty($facility_detail)) ? 0 : $facility_detail[0]['ffd_designation_price'];
$baby_selected = number_format($baby_selected);
$baby_checkbox = $facility_detail[0]['ffd_designation'];
$baby_id = $facility_detail[0]["ffd_id"];

$ffd_category = "child.seat"; //
$facility_detail = $tripitta_car_service->find_fleet_facility_detail_by_ff_id_category($ff_id, $ffd_category);
$child_selected = (empty($facility_detail)) ? 0 : $facility_detail[0]['ffd_designation_price'];
$child_selected = number_format($child_selected);
$child_checkbox = $facility_detail[0]['ffd_designation'];
$child_id = $facility_detail[0]["ffd_id"];

$ffd_category = "placard"; //
$facility_detail = $tripitta_car_service->find_fleet_facility_detail_by_ff_id_category($ff_id, $ffd_category);
$placard_selected = (empty($facility_detail)) ? 0 : $facility_detail[0]['ffd_designation_price'];
$placard_selected = number_format($placard_selected);
$placard_checkbox = $facility_detail[0]['ffd_designation'];
$placard_id = $facility_detail[0]["ffd_id"];

$ffd_category = "female.driver"; //
$facility_detail = $tripitta_car_service->find_fleet_facility_detail_by_ff_id_category($ff_id, $ffd_category);
$female_selected = (empty($facility_detail)) ? 0 : $facility_detail[0]['ffd_designation_price'];
$female_selected = number_format($female_selected);
$female_checkbox = $facility_detail[0]['ffd_designation'];
$female_id = $facility_detail[0]["ffd_id"];

$ffd_category = "accessible.car"; //
$facility_detail = $tripitta_car_service->find_fleet_facility_detail_by_ff_id_category($ff_id, $ffd_category);
$accessible_selected = (empty($facility_detail)) ? 0 : $facility_detail[0]['ffd_designation_price'];
$accessible_selected = number_format($accessible_selected);
$accessible_checkbox = $facility_detail[0]['ffd_designation'];
$accessible_id = $facility_detail[0]["ffd_id"];

$ffd_category = "wify"; //
$facility_detail = $tripitta_car_service->find_fleet_facility_detail_by_ff_id_category($ff_id, $ffd_category);
$wifi_selected = (empty($facility_detail)) ? 0 : $facility_detail[0]['ffd_designation_price'];
$wifi_selected = number_format($wifi_selected);
$wifi_checkbox = $facility_detail[0]['ffd_designation'];
$wifi_id = $facility_detail[0]["ffd_id"];
// 附加服務 end

// 取消規定 start
$cancel_rule_array = $store_service->find_cancel_rule_by_store_id($store_id);
$fcrd_day = 0;
$cancel_rule = array();
$i = 1;
foreach ($cancel_rule_array as $value) {
	if ($value['fcrd_percent'] == 0) {
		$fcrd_day = $value['fcrd_day'];
	} else {
		$i++;
		$value['order'] = $i;
		$cancel_rule[] = $value;
	}
}
// 取消規定 end

// 是否有折扣
$bonus = 0;
$total = $fleet_route['fr_price'];
if(empty($coupon) || $coupon == "undefined") {
	$market_list = $tripitta_service -> find_campaign_campaign_must_possessed_by_user_and_type($_SESSION['travel.ezding.user.data']["serialId"], 1);
	if(!empty($market_list)) {
		foreach ($market_list as $ml) {
			$mc_id = $ml["mc_id"];
			$bo_array = $tripitta_service-> cal_marking_campain_discount($total, $mc_id, $_SESSION['travel.ezding.user.data']["serialId"]);
			$bonus = $bo_array["data"]["discount"];
			if($bonus > 0) break;
		}
	}
}

$pay_total = $total - $bonus;


$area_dao = Dao_loader::__get_area_dao();
$cr_boarding = $area_dao->loadHfArea($fleet_route["cr_boarding"]);
$cr_boarding_name = $cr_boarding["a_name"];
$cr_get_off = $area_dao->loadHfArea($fleet_route["cr_get_off"]);
$cr_get_off_name = $cr_get_off["a_name"];
?>
<form id="form1">
<section id="step1">
	<div class="productList">
		<div class="pBlock">
			<input type="hidden" name="fr_id" value="<?php echo $fr_id; ?>" />
			<input type="hidden" id="type" name="type" value="<?php echo $type; ?>" />
			<input type="hidden" name="begin_date" value="<?php echo $begin_date; ?>" />
			<input type="hidden" name="adult" value="<?php echo $car_adult; ?>" />
			<input type="hidden" name="child" value="<?php echo $car_child; ?>" />
			<div class="pbLeft">
				<div class="listBlock">
					<div class="ptitle">行程</div>
					<div class="pdata"><?php echo $fleet_route['cr_name']; ?></div>
				</div>
				<div class="listBlock">
					<div class="ptitle"><?php echo $type_name; ?>日期</div>
					<div class="pdata"><?php echo $begin_date; ?></div>
				</div>
				<div class="listBlock">
					<div class="ptitle">應付總額</div>
					<div class="pdata onge" id="pay_total">NTD <?php echo number_format($pay_total); ?><span>(約 NTD <?php echo number_format($pay_total); ?>)</span></div>
				</div>
			</div>
			<!--
			<div class="pbRight">
				<div class="btnBlock">
					<div class="left textInp">
						<input type="text" maxlength="20" placeholder="輸入優惠代碼" name="coupon" id="coupon" value="<?= $coupon ?>">
					</div>
					<button type="button" class="submit" id="user_coupon">送出</button>
					<div class="left">
						<a href="/member/coupon/" target="_blank"><查詢我的優惠券></a>
					</div>
				</div>
			</div>
			-->
			<input type="hidden" maxlength="20" placeholder="輸入優惠代碼" name="coupon" id="coupon" value="<?= $coupon ?>">
		</div>
		<input type="checkbox" id="switchHid" value="">
		<div class="pBlock">
			<div class="pbLeft">
				<div class="listBlock">
					<div class="ptitle">車隊名稱</div>
					<div class="pdata"><?php echo $fleet_route['sml_name']; ?></div>
				</div>
				<div class="listBlock">
					<div class="ptitle">車款</div>
					<div class="pdata"><?php echo $fleet_route['c_name']; ?><span>(或同級車款)</span></div>
				</div>
				<div class="listBlock">
					<div class="ptitle">基本時數</div>
					<div class="pdata"><?php echo $fleet_route['frd_category_value']; ?>小時</div>
				</div>
				<div class="listBlock">
					<div class="ptitle">成人</div>
					<div class="pdata"><?php echo $car_adult; ?> 人</div>
				</div>
				<div class="listBlock">
					<div class="ptitle">孩童</div>
					<div class="pdata"><?php echo $car_child; ?> 人<span>(五歲以上)</span></div>
				</div>
				<?php
					if(!empty($luggage)){
						$i = 0;
						foreach ($luggage as $l){
					?>
				<div class="listBlock">
					<div class="ptitle"><?php if($i==0){ ?>行李<?php } ?></div>
					<div class="pdata"><?php echo $l["ca_capacity_luggage"];?>件<span>(28吋以上)</span></div>
				</div>
					<?php
						$i++;
						}
					}
				?>
			</div>
			<div class="pbRight">
				<!-- 最上面三塊做為空白與左方的值對齊 -->
				<div class="listBlock non">&nbsp;</div>
				<div class="listBlock non">&nbsp;</div>
				<div class="listBlock non">&nbsp;</div>
				<div class="listBlock">
					<div class="ptitle">產品總額</div>
					<div class="pCurrency">NTD</div>
					<div class="pNum"><?php echo number_format($total); ?></div>
				</div>
				<!--
				<div class="listBlock">
					<div class="ptitle">紅利折抵</div>
					<div class="pCurrency">NTD</div>
					<div class="pNum">0</div>
					<a href="javascript:void(0)" class="fa fa-trash-o" aria-hidden="true"></a>
				</div>
				-->
				<div class="listBlock">
					<div class="ptitle">優惠折扣</div>
					<div class="pCurrency">NTD</div>
					<div class="pNum" id="bonus" >-<?= $bonus ?></div>
				</div>
			</div>
			<div class="pNote">
				此筆訂單金額不含附加服務，請於現場付款本網站最後刷卡金額皆以台幣計算。
			</div>
		</div>
		<label class="closeBlock" for="switchHid">
			<i class="fa fa-angle-down" aria-hidden="true"></i>
			<i class="fa fa-angle-up" aria-hidden="true"></i>
		</label>
	</div>
	<div class="fillblankBlock sec">
		<h3>聯絡人資料</h3>
		<div class="colBlock">
			<label class="blank fName">
				<input type="text" id="name" name="name" value="<?php echo $name; ?>" maxlength="20" placeholder="姓名">
			</label>
			<label class="blank gender">
				<select id="gender" name="gender">
					<option value="" disabled="true" selected>性別</option>
					<option value="M" <?php echo $gender == "M" ? 'selected="selected"' : ""; ?>>男</option>
					<option value="F" <?php echo $gender == "F" ? 'selected="selected"' : ""; ?>>女</option>
				</select>
				<i class="fa fa-angle-down" aria-hidden="true"></i>
			</label>
		</div>
		<div class="colBlock">
			<label class="blank pCounty">
				<select id="living_country_id" name="living_country_id">
					<option value="" disabled="true" selected>電話區號</option>
					<?php
						foreach(constants_user_center::$LIVING_COUNTRY_TEXT as $key => $value) {
						    echo '<option value="', $key, '"';
						    $country_row = $tripitta_web_service->load_country($key);
						    if ($key == $living_country_id) echo ' selected';
						    echo '>', $value . $country_row['c_tel_code'], '</option>';
						}
					?>
				</select>
				<i class="fa fa-angle-down" aria-hidden="true"></i>
			</label>
			<label class="blank pNum">
				<input type="text" id="mobile" name="mobile" value="<?php echo $mobile; ?>" maxlength="15" placeholder="電話號碼">
			</label>
		</div>
		<div class="colBlock">
			<label class="blank email">
				<input type="text" id="email" name="email" value="<?php echo $email; ?>" maxlength="50" placeholder="E-mail">
			</label>
		</div>
		<div class="colBlock social">
			<label class="blank socialMedia">
				<input type="text" id="wechat_id" name="wechat_id" maxlength="25" placeholder="WeChat ID">
			</label>
			<label class="blank socialMedia">
				<input type="text" id="line_id" name="line_id" maxlength="25" placeholder="Line ID">
			</label>
			<label class="blank socialMedia">
				<input type="text" id="whatsapp_id" name="whatsapp_id" maxlength="25" placeholder="Whatsapp ID">
			</label>
		</div>
	</div>

	<!-- 暫時隱藏 style="display: none;" -->
	<div class="fillblankBlock sec">
		<h3>乘車聯絡人資料</h3>
		<label class="checkLabel">
			<div class="checkSignWrap">
				<input type="checkbox" id="same" name="same" class="checkSwitch">
				<i class="fa fa-check-square-o" aria-hidden="true"></i>
				<i class="fa fa-square-o" aria-hidden="true"></i>
			</div>
			<div class="textBlock">
				<div>
					同訂購人
				</div>
			</div>
		</label>
		<div class="colBlock">
			<label class="blank fName">
				<input type="text" id="car_name" name="car_name" maxlength="20" placeholder="姓名">
			</label>
			<label class="blank gender">
				<select id="car_gender" name="car_gender">
					<option value="" disabled="true" selected>性別</option>
					<option value="M">男</option>
					<option value="F">女</option>
				</select>
				<i class="fa fa-angle-down" aria-hidden="true"></i>
			</label>
		</div>
		<div class="colBlock">
			<label class="blank pCounty">
				<select id="car_living_country_id" name="car_living_country_id">
					<option value="" disabled="true" selected>電話區號</option>
					<?php
						foreach(constants_user_center::$LIVING_COUNTRY_TEXT as $key => $value) {
						    echo '<option value="', $key, '"';
						    $country_row = $tripitta_web_service->load_country($key);
						    echo '>', $value . $country_row['c_tel_code'], '</option>';
						}
					?>
				</select>
				<i class="fa fa-angle-down" aria-hidden="true"></i>
			</label>
			<label class="blank pNum">
				<input type="text" id="car_mobile" name="car_mobile" maxlength="15" placeholder="電話號碼">
			</label>
		</div>
		<div class="colBlock">
			<label class="blank email">
				<input type="text" id="car_email" name="car_email" maxlength="50" placeholder="E-mail">
			</label>
		</div>
		<div class="colBlock social">
			<label class="blank socialMedia">
				<input type="text" id="car_wechat_id" name="car_wechat_id" maxlength="25" placeholder="WeChat ID">
			</label>
			<label class="blank socialMedia">
				<input type="text" id="car_line_id" name="car_line_id" maxlength="25" placeholder="Line ID">
			</label>
			<label class="blank socialMedia">
				<input type="text" id="car_whatsapp_id" name="car_whatsapp_id" maxlength="25" placeholder="Whatsapp ID">
			</label>
		</div>
	</div>
	<div class="btnWrap-m">
		<button type="button" class="pre">上一步</button>
		<button type="button" class="next">下一步</button>
	</div>
</section>
<section id="step2">
	<div class="require-pickup sec">
		<h3>您的需求為</h3>
		<div class="colBlock">
			<label class="blank rblock">
				<input type="text" id="arrival_date" name="arrival_date" placeholder="航班<?php echo $type_arrival_name; ?>日期" maxlength="20" />
				<i class="fa fa-angle-down" aria-hidden="true"></i>
			</label>
			<label class="blank rblock">
				<select id="arrival_time" name="arrival_time">
					<option value="" disabled="true" selected>航班<?php echo $type_arrival_name; ?>時間</option>
					<?php
						for($i=1;$i<=24;$i++){
							for($j=0;$j<60;$j+=30){
								$time = sprintf("%02d:%02d",$i,$j);
							?>
						<option value="<?php echo $time; ?>"><?php echo $time; ?></option>
							<?php
							}
						}
					?>
				</select>
				<i class="fa fa-angle-down" aria-hidden="true"></i>
			</label>
			<label class="blank rblock">
				<input type="text" name="flight_number" maxlength="25" placeholder="航班編號">
			</label>
		</div>
		<div class="colBlock">
			<label class="rblock text">
				<span class="rTitle"><?php echo $type_name; ?>日期</span>
				<span><?php echo $begin_date; ?></span>
			</label>
			<label class="blank rblock">
				<select id="pickup_time" name="pickup_time">
					<option value="" disabled="true" selected><?php echo $type_name; ?>時間</option>
					<?php
						for($i=1;$i<=24;$i++){
							for($j=0;$j<60;$j+=30){
								$time = sprintf("%02d:%02d",$i,$j);
							?>
						<option value="<?php echo $time; ?>"><?php echo $time; ?></option>
							<?php
							}
						}
					?>
				</select>
				<i class="fa fa-angle-down" aria-hidden="true"></i>
			</label>
		</div>
		<?php if($pickup_type==2){ ?>
		<div class="colBlock">
			<label class="rblock text">
				<span class="rTitle">出發地</span>
				<span><?php echo $cr_boarding_name; ?></span>
			</label>
			<label class="rblock text long">
				<span class="onge">
					<!-- 如送機時間逢深夜凌晨(23：00~07：00)，每小時加收NTD400元，訂單價格不包含此加價，請於現場付予司機。 -->
					如<?php echo $type_name; ?>時間逢深夜凌晨(23：00~07：00)，每小時加收NTD400元，訂單價格不包含此加價，請於現場付予司機。
				</span>
			</label>
		</div>
		<div class="colBlock">
			<label class="rblock text">
				<span class="rTitle">目的地</span>
				<span><?php echo $cr_get_off_name; ?></span>
			</label>
			<label class="blank rblock long">
				<input type="text" id="get_off_address" name="get_off_address" maxlength="50" placeholder="目的地地址 (必填)">
			</label>
		</div>
		<?php }else{ ?>
		<div class="colBlock">
			<label class="rblock text">
				<span class="rTitle">出發地</span>
				<span><?php echo $cr_boarding_name; ?></span>
			</label>
			<label class="blank rblock long">
				<input type="text" id="boarding_address" name="boarding_address" maxlength="50" placeholder="出發地地址 (必填)">
			</label>
		</div>
		<div class="colBlock">
			<label class="rblock text">
				<span class="rTitle">目的地</span>
				<span><?php echo $cr_get_off_name; ?></span>
			</label>
			<label class="rblock text long">
				<span class="onge">
					<!-- 如送機時間逢深夜凌晨(23：00~07：00)，每小時加收NTD400元，訂單價格不包含此加價，請於現場付予司機。 -->
					如<?php echo $type_name; ?>時間逢深夜凌晨(23：00~07：00)，每小時加收NTD400元，訂單價格不包含此加價，請於現場付予司機。
				</span>
			</label>
		</div>
		<?php } ?>
	</div>
	<div class="insurance sec">
		<h3>乘車人保險用資料</h3>
		<!-- 根據使用者先前填的人數以迴圈自動產生相對應人數 -->
		<?php
			if($passenger_count > 0){
				for ($i=0; $i<$passenger_count; $i++){
				?>
		<div class="passenger">
			<label class="blank pName">
				<input type="text" name="passenger_name[<?= $i ?>]" maxlength="50" value="" placeholder="姓名">
			</label>
			<div class="pInfo">
				<label class="blank birth">
					<input type="text" name="passenger_birthday[<?= $i ?>]" maxlength="10" value="" placeholder="生日 1990-01-01">
				</label>
				<label class="blank county">
					<select id="passenger_country_<?php echo $i; ?>" name="passenger_country[<?= $i ?>]">
						<option value="" disabled="true" selected>國籍</option>
						<?php foreach(constants_user_center::$LIVING_COUNTRY_TEXT as $key => $value) { ?>
						<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
						<?php }	?>
					</select>
					<i class="fa fa-angle-down" aria-hidden="true"></i>
				</label>
				<label class="blank passNum">
					<input type="text" name="passenger_number[<?= $i ?>]" maxlength="20" placeholder="證號號碼">
				</label>
			</div>
		</div>
				<?php
				}
			}
		?>
		<div class="note">
			注意：
			<br>以上資料請正確填寫，以利為您辦理保險，若提供錯誤請於出發前2日，登入包車訂單明細做修改，若未提出導致無法投保或理賠，責任由預訂者承擔。
			<br>若暫時無入台證號、護照號或身分證字號可訂購後候補，但請務必於出發前2日，登入包車訂單明細做修改，請留意。
		</div>
	</div>
	<div class="fillblankBlock">
		<div class="btnWrap-m">
			<button type="button" class="pre">上一步</button>
			<button type="button" class="next">下一步</button>
		</div>
	</div>
</section>
<section id="step3">
	<div class="choServiceBlock sec">
		<h3>附加服務</h3>
		<div class="subTitle">
			請於下方勾選，並於現場付款。(需求項目我們會盡量幫您安排，實際情況將於最晚出發前2天確認是否預訂成功)。
		</div>
		<div class="csBlockWrap">
			<?php if ($license_checkbox == "Y") { ?>
			<label class="csBlock">
				<input type="checkbox" name="license" value="<?= $license_id ?>">
				<div class="csFrame">
					<div class="csTitle">導遊執照司機</div>
					<div class="csWrap">
						<div class="csSub">現場付款</div>
						<div class="csPrice">
							<?php if ($license_selected == 0) { ?>
							<span class="Num"><?php echo "不加價"; ?></span>
							<?php } else { ?>
							<span class="currency">NTD</span>
							<span class="Num"><?php echo $license_selected; ?></span>
							<?php } ?>
						</div>
					</div>
				</div>
			</label>
			<?php } ?>
			<?php if ($en_checkbox == "Y") { ?>
			<label class="csBlock">
				<input type="checkbox" name="en_driver" value="<?= $en_id ?>">
				<div class="csFrame">
					<div class="csTitle">外語司機(英語)</div>
					<div class="csWrap">
						<div class="csSub">現場付款</div>
						<div class="csPrice">
							<?php if ($en_selected == 0) { ?>
							<span class="Num"><?php echo "不加價"; ?></span>
							<?php } else { ?>
							<span class="currency">NTD</span>
							<span class="Num"><?php echo $en_selected; ?></span>
							<?php } ?>
						</div>
					</div>
				</div>
			</label>
			<?php } ?>
			<?php if ($jp_checkbox == "Y") { ?>
			<label class="csBlock">
				<input type="checkbox" name="jp_driver" value="<?= $jp_id ?>">
				<div class="csFrame">
					<div class="csTitle">外語司機(日語)</div>
					<div class="csWrap">
						<div class="csSub">現場付款</div>
						<div class="csPrice">
							<?php if ($jp_selected == 0) { ?>
							<span class="Num"><?php echo "不加價"; ?></span>
							<?php } else { ?>
							<span class="currency">NTD</span>
							<span class="Num"><?php echo $jp_selected; ?></span>
							<?php } ?>
						</div>
					</div>
				</div>
			</label>
			<?php } ?>
			<?php if ($ct_checkbox == "Y") { ?>
			<label class="csBlock">
				<input type="checkbox" name="ct_driver" value="<?= $ct_id ?>">
				<div class="csFrame">
					<div class="csTitle">外語司機(粵語)</div>
					<div class="csWrap">
						<div class="csSub">現場付款</div>
						<div class="csPrice">
							<?php if ($ct_selected == 0) { ?>
							<span class="Num"><?php echo "不加價"; ?></span>
							<?php } else { ?>
							<span class="currency">NTD</span>
							<span class="Num"><?php echo $ct_selected; ?></span>
							<?php } ?>
						</div>
					</div>
				</div>
			</label>
			<?php } ?>
			<?php if ($ko_checkbox == "Y") { ?>
			<label class="csBlock">
				<input type="checkbox" name="ko_driver" value="<?= $ko_id ?>">
				<div class="csFrame">
					<div class="csTitle">外語司機(韓語)</div>
					<div class="csWrap">
						<div class="csSub">現場付款</div>
						<div class="csPrice">
							<?php if ($ko_selected == 0) { ?>
							<span class="Num"><?php echo "不加價"; ?></span>
							<?php } else { ?>
							<span class="currency">NTD</span>
							<span class="Num"><?php echo $ko_selected; ?></span>
							<?php } ?>
						</div>
					</div>
				</div>
			</label>
			<?php } ?>
			<?php if ($baby_checkbox == "Y") { ?>
			<label class="csBlock">
				<input type="checkbox" name="baby_set" value="<?= $baby_id ?>">
				<div class="csFrame">
					<div class="csTitle">嬰兒座椅(0-2歲)</div>
					<div class="csWrap">
						<div class="csSub">現場付款</div>
						<div class="csPrice">
							<?php if ($baby_selected == 0) { ?>
							<span class="Num"><?php echo "不加價"; ?></span>
							<?php } else { ?>
							<span class="currency">NTD</span>
							<span class="Num"><?php echo $baby_selected; ?></span>
							<?php } ?>
						</div>
					</div>
				</div>
			</label>
			<?php } ?>
			<?php if ($child_checkbox == "Y") { ?>
			<label class="csBlock">
				<input type="checkbox" name="child_set" value="<?= $child_id ?>">
				<div class="csFrame">
					<div class="csTitle">兒童座椅(2-12歲)</div>
					<div class="csWrap">
						<div class="csSub">現場付款</div>
						<div class="csPrice">
							<?php if ($child_selected == 0) { ?>
							<span class="Num"><?php echo "不加價"; ?></span>
							<?php } else { ?>
							<span class="currency">NTD</span>
							<span class="Num"><?php echo $child_selected; ?></span>
							<?php } ?>
						</div>
					</div>
				</div>
			</label>
			<?php } ?>
			<?php if ($placard_checkbox == "Y") { ?>
			<label class="csBlock">
				<input type="checkbox" name="placard" value="<?= $placard_id ?>">
				<div class="csFrame">
					<div class="csTitle">舉牌接送</div>
					<div class="csWrap">
						<div class="csSub">現場付款</div>
						<div class="csPrice">
							<?php if ($placard_selected == 0) { ?>
							<span class="Num"><?php echo "不加價"; ?></span>
							<?php } else { ?>
							<span class="currency">NTD</span>
							<span class="Num"><?php echo $placard_selected; ?></span>
							<?php } ?>
						</div>
					</div>
				</div>
			</label>
			<?php } ?>
			<?php if ($female_checkbox == "Y") { ?>
			<label class="csBlock">
				<input type="checkbox" name="female" value="<?= $female_id ?>">
				<div class="csFrame">
					<div class="csTitle">女性司機</div>
					<div class="csWrap">
						<div class="csSub">現場付款</div>
						<div class="csPrice">
							<?php if ($female_selected == 0) { ?>
							<span class="Num"><?php echo "不加價"; ?></span>
							<?php } else { ?>
							<span class="currency">NTD</span>
							<span class="Num"><?php echo $female_selected; ?></span>
							<?php } ?>
						</div>
					</div>
				</div>
			</label>
			<?php } ?>
			<?php if ($accessible_checkbox == "Y") { ?>
			<label class="csBlock">
				<input type="checkbox" name="accessible" value="<?= $accessible_id ?>">
				<div class="csFrame">
					<div class="csTitle">無障礙車種</div>
					<div class="csWrap">
						<div class="csSub">現場付款</div>
						<div class="csPrice">
							<?php if ($accessible_selected == 0) { ?>
							<span class="Num"><?php echo "不加價"; ?></span>
							<?php } else { ?>
							<span class="currency">NTD</span>
							<span class="Num"><?php echo $accessible_selected; ?></span>
							<?php } ?>
						</div>
					</div>
				</div>
			</label>
			<?php } ?>
			<?php if ($wifi_checkbox == "Y") { ?>
			<label class="csBlock">
				<input type="checkbox" name="wifi" value="<?= $wifi_id ?>">
				<div class="csFrame">
					<div class="csTitle">車上Wifi</div>
					<div class="csWrap">
						<div class="csSub">現場付款</div>
						<div class="csPrice">
							<?php if ($wifi_selected == 0) { ?>
							<span class="Num"><?php echo "不加價"; ?></span>
							<?php } else { ?>
							<span class="currency">NTD</span>
							<span class="Num"><?php echo $wifi_selected; ?></span>
							<?php } ?>
						</div>
					</div>
				</div>
			</label>
			<?php } ?>
		</div>
	</div>
<!-- 	<div class="sec"> -->
<!-- 		<h3>亞洲萬里通卡號</h3> -->
<!-- 		<label class="blank"> -->
<!-- 			<input type="text" name="" maxlength="50" placeholder="輸入卡號"> -->
<!-- 		</label> -->
<!-- 	</div> -->
	<div class="sec">
		<h3>備註留言</h3>
		<textarea rows="5" id="memo" name="memo"></textarea>
		<label class="checkLabel">
			<div class="checkSignWrap">
				<input type="checkbox" id="policy_chk" name="policy_chk" class="checkSwitch">
				<i class="fa fa-check-square-o" aria-hidden="true"></i>
				<i class="fa fa-square-o" aria-hidden="true"></i>
			</div>
			<div class="textBlock">
				<div>
					我已閱讀並同意<a href="javascript:void(0)" id="popup_policy"><?php echo $type_name; ?>條款</a>
				</div>
			</div>
		</label>
	</div>
	<div class="creditBlock sec">
		<h3>選擇付款信用卡</h3>
		<label class="creditLabel">
			<input type="radio" value="tripitta.hitrust.car" name="credit" checked="true">
			<div class="creditFrame">
				<i class="fa fa-check-square-o" aria-hidden="true"></i>
				<i class="fa fa-square-o" aria-hidden="true"></i>
				<span class="creditTitle">信用卡/debit卡</span>
				<div class="img-visa"></div>
			</div>
		</label>
		<label class="creditLabel ">
			<input type="radio" value="tripitta.china.union.car" name="credit">
			<div class="creditFrame">
				<i class="fa fa-check-square-o" aria-hidden="true"></i>
				<i class="fa fa-square-o" aria-hidden="true"></i>
				<span class="creditTitle">銀聯卡</span>
				<div class="img-unionpay"></div>
				<span class="text">銀聯卡(頁面將跳轉至銀聯卡頁面)</span>
			</div>
		</label>
		<?php /*
		<label class="creditLabel">
			<input type="radio" value="credit" name="credit">
			<div class="creditFrame">
				<i class="fa fa-check-square-o" aria-hidden="true"></i>
				<i class="fa fa-square-o" aria-hidden="true"></i>
				<span class="creditTitle">支付寶</span>
				<div class="img-alipay"></div>
				<span class="text">支付寶(頁面將跳轉至支付寶頁面)</span>
			</div>
		</label>
		*/ ?>
	</div>
	<div class="sec">
		<div class="btnWrap">
			<button type="button" class="pre">上一步</button>
			<button type="button" class="next" id="from_submit">下一步</button>
		</div>
	</div>
</section>
<!-- popupChartPolicy -->
<?php
	$partner_service = new partner_service();
	$partner_row = $partner_service->get_store($store_id);
	$half_day_hour = $tripitta_car_service->find_fleet_rule_detail_by_fr_id_category($fr_id, 'half.day.hour');
	$full_day_hour = $tripitta_car_service->find_fleet_rule_detail_by_fr_id_category($fr_id, 'day.hour');
	$over_time_add_price = $tripitta_car_service->find_fleet_rule_detail_by_fr_id_category($fr_id, 'over.time.add_price');
	$airport_midnight = $tripitta_car_service->find_fleet_rule_detail_by_fr_id_category($fr_id, 'airport.midnight.add.price');
	$chinesenewyear = $tripitta_car_service->find_fleet_rule_detail_by_fr_id_category($fr_id, 'chinesenewyear.add.price');
	$zero_percent = 0;
	$part_percent = 0;
	foreach ($cancel_rule as $value) {
		if ($value['fcrd_day'] == 0) {
			$zero_percent = $value['fcrd_percent'];
		} else {
			$part_percent = $value['fcrd_percent'];
		}
	}
?>
<div class="popupChartPolicy">
	<div class="closeBtn">
		<i class="fa fa-times" aria-hidden="true"></i>
	</div>
	<h4>訂車條款和取消規定</h4>
	<div class="content">
		<ul class="popupPolicy">
			<li>本服務由<span style="color:blue"><?=$partner_row['sml_name']?></span>提供。</li>
			<li>本訂單視同車輛租賃合約，請確認上述信息準確無誤。</li>
			<li>若有行程或車輛上的調動的問題，我們將於訂單成立後一個工作天內與您聯絡，如未
				能符合您的需求，我們將全額退費給您。</li>
			<li>最晚於出發日前24小時我們將會提供您車輛的車牌號碼、司機全名及手機號碼。</li>
			<?if(count($chinesenewyear)>0){?>
			<li>
				<?for($i=0;$i<count($chinesenewyear);$i++){?>
				若您訂購春節期間(<span style="color:blue"><?=$chinesenewyear[$i]['frd_begin_date']?></span>~<span style="color:blue"><?=$chinesenewyear[$i]['frd_begin_date']?></span>)，
				一日行程加價NTD<span style="color:blue"><?=number_format($chinesenewyear[$i]['frd_category_value2'])?></span>元，半日行程加價NTD<span style="color:blue"><?=number_format($chinesenewyear[$i]['frd_category_value'])?></span>元
				<?if($i<count($chinesenewyear)-1){?>
						；
					<?}else{?>
						，
					<?}?>
				<?}?>
				請於現場付予司機。
			</li>
			<?}?>
			<?if(count($over_time_add_price)>0&&count($airport_midnight)>0){?>
			<li>
				超時，每小時加收NTD <span style="color:blue"><?=number_format($over_time_add_price[0]['frd_category_value'])?></span>元；
				<?for($i=0;$i<count($airport_midnight);$i++){?>
					深夜清晨(<span style="color:blue"><?=$airport_midnight[$i]['frd_begin_time']?></span>~<span style="color:blue"><?=$airport_midnight[$i]['frd_end_time']?></span>)，
					每小時加收NTD <span style="color:blue"><?=number_format($airport_midnight[$i]['frd_category_value'])?></span>元
					<?if($i<count($airport_midnight)-1){?>
						；
					<?}else{?>
						，
					<?}?>
				<?}?>
				訂單價格不包含超時、深夜清晨及附加服務的加價費，請於現場付予司機。
			</li>
			<?}?>
			<li>若您有需求附加服務而需加價，實際情況我們最晚於出發前2天通知您是否預訂成功，屆時請於現場付予司機。</li>
			<li>送機時，請於預定時間及地點準時上車。</li>
			<li>接機時，請在班機抵達後立即打開手機以利聯繫；可快速通關或無託運行李者，預約時請先告知，以縮短候車時間。</li>
			<li>若接機航班延誤，在原定接機時間24小時前通知車隊可更改調度，24小時內則須請旅客自理，恕不另補償或退費。</li>
			<li>接送皆最多等候15分鐘，逾時則司機無法繼續等候，且視同已使用該項服務，不予退費。如因資料提供不完整而無法完成接送，恕不另補償或退費。</li>
			<li>取消規定：乘車日<span style="color:blue"><?=$fcrd_day?></span>天前取消，全額退款。乘車日<span style="color:blue"><?=$fcrd_day?></span>天內取消，收取<span style="color:blue"><?=$part_percent?>％</span>取消費。乘車
				日當天取消，收取<span style="color:blue"><?=$zero_percent?>%</span>取消費。</li>
			<li>如遇不可抗力之因素，如颱風、地震、交通中斷等導致您無法如期出發或車行不能發
				車，將全額退款或依您意願延後包車日期。</li>
			<li>本公司為代收代付平台（本服務恕無法開立發票），若需求乘車收據，請務必於乘車
				時向司機索取，若當日未索取，視為棄權，無法補發服重寄。</li>
			<li>訂車後若有任何狀況或問題發生，請先行向業者反應，以獲得最即時的處理，如業者
				無法妥善處理，再向Tripitta客服人員反應。</li>
		</ul>
	</div>
	<div class="lightbox-close"></div>
</div>
<input type="hidden" id="pickup_type" name="pickup_type" value="<?php echo $pickup_type; ?>" />
</form>
<form id="form2" method="post" action="/web/pages/bookingcar/pay/pay_step_credit.php">
	<input type="hidden" id="pay_step" name="pay_step" />
</form>
<form id="form3" method="post" action="/web/pages/bookingcar/shopping_cart_to_order.php">
	<input type="hidden" id="pay_step2" name="pay_step2" />
</form>
<script type="text/javascript">
	var caneldar_option = <?php echo json_encode(Constants::$CALENDAR_OPTIONS); ?>;
	$('#arrival_date').datepicker(caneldar_option).datepicker('option', {minDate: new Date()});
	$('input[name^="passenger_birthday"]').datepicker(caneldar_option).datepicker('option', {maxDate: new Date('<?php echo $max_birthday; ?>'), minDate: new Date('<?php echo $min_birthday; ?>'), defaultDate: new Date('<?php echo $max_birthday; ?>'), yearRange: '-100:+100'});

	var check_passenger = function(input_name, item){
		var result = "OK";
		if(item==""){ item = 1;	}
		if(item==1){
			$("input[name^='"+input_name+"']").each(function(){
				if($(this).val()=="" && result=="OK"){
					$(this).focus();
					result = "FAIL";
				}
			});
		}else if(item == 2){
			$('[id^="'+input_name+'"]').each(function(){
				if($(this).val()==null && result=="OK"){
					$(this).focus();
					result = "FAIL";
				}
			});
		}
		return result;
	}

	var checkdata = function(){
		var msg = '';
		// 檢查步驟1內容
		if(check_step1()=="OK"){
			// 檢查步驟2內容
			if(check_step2()=="OK"){
				// 條款同意項目
				if($("input[name='policy_chk']:checked").length == 0) {
					$('#policy_chk').focus();
					alert('請先閱讀，並同意<?php echo $type_name; ?>條款和取消規定之內容!!');
					return;
				}
				if($("input[name='credit']:checked").length == 0) {
					$('#credit').focus();
					alert('請選擇付款方式!!');
					return;
				}

				var credit_type = $('input[name="credit"]:checked').val();
				if(credit_type == "tripitta.hitrust.car") {
					var serialized = $('#form1').serializeArray();
			        var s = '';
			        var data = {};
			        for(s in serialized){
			            data[serialized[s]['name']] = serialized[s]['value']
			        }
					$("#pay_step").val(JSON.stringify(data));
					$('#form2').submit();
				}else if(credit_type == "tripitta.china.union.car")  {
					msg = "提醒您，當您按下「銀聯卡付款」，即進入銀聯卡扣款系統，當您輸入卡號及相關資料完成認証後，訂單即成立，且同時直接於您的銀聯卡帳戶中扣款完成，若您想要更改或取消訂單，相關扣款規定，請務必詳閱「訂購須知」。";
					if (confirm(msg)) {
						var serialized = $('#form1').serializeArray();
				        var s = '';
				        var data = {};
				        for(s in serialized){
				            data[serialized[s]['name']] = serialized[s]['value']
				        }
						$("#pay_step2").val(JSON.stringify(data));
						$('#form3').submit();
					}
				}
			}
		}
	}

	var check_step1 = function(){
		var msg = '';
		if ($('#name').val() == '') {
			$('#name').focus();
			alert('請輸入聯絡人姓名!!');
			return;
		}
		if ($('#gender :selected').val() == '') {
			$('#gender').focus();
			alert('請輸入聯絡人性別!!');
			return;
		}
		if ($('#living_country_id :selected').val() == '') {
			$('#living_country_id').focus();
			alert('請輸入聯絡人電話區號!!');
			return;
		}
		if ($('#mobile').val() == '') {
			$('#mobile').focus();
			alert('請輸入聯絡人電話號碼!!');
			return;
		}
		if ($('#email').val() == '') {
			$('#email').focus();
			alert('請輸入聯絡人E-mail!!');
			return;
		}
		if (!verifyEmailAddress($('#email').val())) {
			$('#email').focus();
			alert('請檢查聯絡人E-mail格式是否正確!!');
			return;
		}
		if ($('#car_name').val() == '') {
			$('#car_name').focus();
			alert('請輸入乘車聯絡人姓名!!');
			return;
		}
		if ($('#car_gender :selected').val() == '') {
			$('#car_gender').focus();
			alert('請輸入乘車聯絡人性別!!');
			return;
		}
		if ($('#car_living_country_id :selected').val() == '') {
			$('#car_living_country_id').focus();
			alert('請輸入乘車聯絡人電話區號!!');
			return;
		}
		if ($('#car_mobile').val() == '') {
			$('#car_mobile').focus();
			alert('請輸入乘車聯絡人電話號碼!!');
			return;
		}
		if ($('#car_email').val() == '') {
			$('#car_email').focus();
			alert('請輸入乘車聯絡人E-mail!!');
			return;
		}
		if (!verifyEmailAddress($('#car_email').val())) {
			$('#car_email').focus();
			alert('請檢查乘車聯絡人E-mail格式是否正確!!');
			return;
		}
		if(msg==''){
			return "OK";
		}
	}

	var check_step2 = function(){
		var msg = '';
		// 接機檢查項目
		if ($('#arrival_date').val() == '') {
			$('#arrival_date').focus();
			alert('請輸入航班<?php echo $type_arrival_name; ?>日期!!');
			return;
		}
		if ($('#arrival_time :selected').val() == '') {
			$('#arrival_time').focus();
			alert('請輸入航班<?php echo $type_arrival_name; ?>時間!!');
			return;
		}
		if ($('#flight_number').val() == '') {
			$('#flight_number').focus();
			alert('請輸入航班編號!!');
			return;
		}
		if ($('#pickup_time :selected').val() == '') {
			$('#pickup_time').focus();
			alert('請輸入<?php echo $type_name; ?>時間!!');
			return;
		}
		<?php if($pickup_type==2){ ?>
		if ($('#get_off_address').val() == '') {
			$('#boarding_address').focus();
			alert('請輸入<?php echo $type_name; ?>時間!!');
			return;
		}
		<?php }else{ ?>
		if ($('#boarding_address').val() == '') {
			$('#boarding_address').focus();
			alert('請輸入<?php echo $type_name; ?>時間!!');
			return;
		}
		<?php } ?>
		// 乘車人保險用資料
		if(check_passenger("passenger_name", 1)=="FAIL"){
			alert('請輸入乘車人保險用姓名!!');
			return;
		}
		if(check_passenger("passenger_country", 2)=="FAIL"){
			alert('請輸入乘車人保險用國籍!!');
			return;
		}
		if(check_passenger("passenger_number", 1)=="FAIL"){
			alert('請輸入乘車人保險用證號號碼!!');
			return;
		}
		if(check_passenger("passenger_birthday", 1)=="FAIL"){
			alert('請輸入乘車人保險用生日!!');
			return;
		}
		if(msg==''){
			return "OK";
		}
	}

	var showNotice = function (objId) {
		$(".overlay").show();
	    //$("#" + objId).css("left", ($(window).width() - $("#" + objId).width()) / 2);
	    $("#" + objId).show();
	}

	var step_control = function(step){
		switch(step){
			default:
			case 1:
				$('#step1').show();
				$('#step2').hide();
                $('#step3').hide();
				break;
			case 2:
				$('#step1').hide();
				$('#step2').show();
                $('#step3').hide();
				break;
			case 3:
				$('#step1').hide();
                $('#step2').hide();
                $('#step3').show();
				break;
		}
        step_selected(step);
	}

	var step_selected = function(step){
		if(step>0){
			var step_count = 1;
			clear_selected();
			$('.step-m div').each(function(){
	            if(step_count==step){
	            	$(this).addClass('selected');
	            }
	            step_count++;
			});
		}else{
			$('#step1').show();
			$('#step2').show();
            $('#step3').show();
		}
		$(window).scrollTop(0);
	}

	var clear_selected = function(){
		$('.step-m div').each(function(){
			var circle_selected = $(this).attr("class");
            if(circle_selected.match('selected')){
            	$(this).removeClass('selected');
            }
		});
	}

	$(function(){
		$('input[name="same"]').on('click',function(){
			var v = $("input[name='same']").is(":checked");
			if (v) {
				var name = $("#name").val();
				var gender = $("#gender").val();
				var living_country_id = $("#living_country_id").val();
				var mobile = $("#mobile").val();
				var email = $("#email").val();
				var wechat_id = $("#wechat_id").val();
				var line_id = $("#line_id").val();
				var whatsapp_id = $("#whatsapp_id").val();
				$("#car_name").val(name);
				$("#car_gender").val(gender);
				$("#car_living_country_id").val(living_country_id);
				$("#car_mobile").val(mobile);
				$("#car_email").val(email);
				$("#car_wechat_id").val(wechat_id);
				$("#car_line_id").val(line_id);
				$("#car_whatsapp_id").val(whatsapp_id);
			}<?php /* else {
				$("#car_name").val('');
				$("#car_gender").val('');
				$("#car_living_country_id").val('');
				$("#car_mobile").val('');
				$("#car_email").val('');
				$("#car_wechat_id").val('');
				$("#car_line_id").val('');
				$("#car_whatsapp_id").val('');
			}*/ ?>
		});

		$('#popup_policy').on("click",function(){
			$('.popupChartPolicy').show();
			$(".overlay").show();
		});

		$('.popupChartPolicy .closeBtn').on("click",function(){
			$('.popupChartPolicy').hide();
			$(".overlay").hide();
		});

		$('#from_submit').on("click",function(){
			checkdata();
		});

        $('#step1 .pre').on("click", function(){
            var url = "/bookingcar/pickup/<?php echo $fr_id; ?>/";
            url += "?pickup_type=<?php echo $pickup_type; ?>&begin_date=<?php echo $begin_date; ?>&car_adult=<?php echo $car_adult; ?>&car_child=<?php echo $car_child; ?>";
            window.location.href = url;
        });

        $('#step1 .next').on("click", function(){
            if(check_step1()=="OK"){
        		step_control(2);
            }
        });

        $('#step2 .pre').on("click", function(){
        	step_control(1);
        });

        $('#step2 .next').on("click", function(){
        	if(check_step2()=="OK"){
        		step_control(3);
        	}
        });

        $('#step3 .pre').on("click", function(){
        	var window_width = $(window).width();
        	if(window_width <= <?php echo $mobile_width; ?>){
        		step_control(2);
        	}else{
        		var url = "/bookingcar/pickup/<?php echo $fr_id; ?>/";
                url += "?pickup_type=<?php echo $pickup_type; ?>&begin_date=<?php echo $begin_date; ?>&car_adult=<?php echo $car_adult; ?>&car_child=<?php echo $car_child; ?>";
                window.location.href = url;
        	}
        });

        $(window).resize(function(){
            var window_width = $(window).width();
            if(window_width <= <?php echo $mobile_width; ?>){
                var div_count = 1;
                $('.step-m div').each(function(){
                    var circle_selected = $(this).attr("class");
                    if(circle_selected.match('selected')){
                    	step_control(div_count);
                    }
                    div_count++;
                });
            }else{
            	step_control(0);
            }
        });

    	// 偵測頁寬
    	var window_width = $(window).width();
    	if(window_width <= <?php echo $mobile_width; ?>){
        	step_control(1);
        }else{
        	step_control(0);
        }

        var default_data = function(){
            <?php
            if(!empty($p_content)){
            	foreach ($p_content as $k => $v){
            		if(in_array($k, $checked)){
            		?>
            			$('input[name="<?php echo $k; ?>"]').prop("checked", true);
            <?php   }elseif (in_array($k, $selected)){ ?>
            			$('#<?php echo $k; ?>').val('<?php echo $v; ?>');
            		<?php
					}else{
            			if($k=="memo"){
            			?>
            			$('textarea[name="<?php echo $k; ?>"]').html('<?php echo $v; ?>');
            		<?php }else if(preg_match('/passenger\_country(.*)$/', $k)){ ?>
            			$('select[name="<?php echo $k; ?>"]').val('<?php echo $v; ?>');
            		<?php }else{ ?>
            			$('input[name="<?php echo $k; ?>"]').val('<?php echo $v; ?>');
						<?php
						}
					}
            	}
			}
            ?>
        }
        default_data();

        // coupon
        $('#user_coupon').click(function () {
        	if ($('#coupon').val() == '') {
				alert("尚未輸入優惠代碼");
				return;
			}
            // 先還原
            $('#bonus').html("-0");
            $('#pay_total').html("NTD <?php echo number_format($total); ?><span>(約 NTD <?php echo number_format($total); ?>)</span>");
           	// 在折扣
           	// @todo

        });
	});
</script>