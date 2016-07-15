<?
require_once __DIR__ . '/../../config.php';
$tp_id = get_num('tp_id');
if($tp_id > 0) {
    if(is_readable('sec_' . $tp_id . '.php')) {
	   include 'sec_' . $tp_id . '.php';
    } else {
        alertmsg('檔案不存在', '/');
    }
}
?>