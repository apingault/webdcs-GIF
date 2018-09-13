<?php

require_once '../config.php';

$id = $_POST['id'];

$query = mysql_query(" SELECT `name`, `albumid` FROM `pic_photos` WHERE `id` = '".$id."' LIMIT 1 ") or die("<br /><br />Mysql error");
$res = mysql_fetch_array($query);

unlink($ALBUM['local_path'].$res['albumid'].'/'.$res['name']);
unlink($ALBUM['local_path'].$res['albumid'].'/s_'.$res['name']);

mysql_query(" DELETE FROM `pic_photos` WHERE id = '".$id."'") or die("<br /><br />Mysql error");
echo '1'; // send success

?>