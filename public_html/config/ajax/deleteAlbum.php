<?php

require_once '../config.php';

$albumid = $_POST['albumid'];

if($albumid == 0) die('<br /><br />You cannot delete the default album.');

$q2 = mysql_query(" SELECT * FROM `pic_album` WHERE id = '".$albumid."' ") or die("<br /><br />Mysql error");
$r2 = mysql_fetch_array($q2);
		
$q1 = mysql_query(" SELECT * FROM `pic_photos` WHERE `albumid` = '".$albumid."'") or die("<br /><br />Mysql error");
while ($r1 = mysql_fetch_array($q1)) {
			
	unlink($ALBUM['local_path'].$albumid.'/'.$r1['name']);
	unlink($ALBUM['local_path'].$albumid.'/'.$r1['name']);
}
rmdir($ALBUM['local_path'].$albumid);
mysql_query(" DELETE FROM `pic_photos` WHERE albumid = '".$albumid."'") or die("<br /><br />Mysql error");
mysql_query(" DELETE FROM `pic_album` WHERE id = '".$albumid."'") or die("<br /><br />Mysql error");
echo '1'; // send success

?>