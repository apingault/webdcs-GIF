<?php
require_once '../config.php';
$order = explode(',', $_POST['order']);

for($i = 0; $i < count($order); $i++) {
	
	mysql_query(" UPDATE `pic_photos` SET `sort` = '".$i."' WHERE `id` = '".$order[$i]."' AND `albumid` = '".$_POST['albumid']."'  ") or die("Mysql error");
	
}
?>