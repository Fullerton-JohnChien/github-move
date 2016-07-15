<?php
include_once('./web/config.php');
//$deviceType = 'aaa';
if($deviceType == 'computer'){
	include __DIR__ . '/web/index.php';
}else{
	include __DIR__ . '/web/index-m.php';
}
?>