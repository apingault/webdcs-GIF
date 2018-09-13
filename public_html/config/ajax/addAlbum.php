<?php 

require_once '../config.php';
$name = htmlentities($_POST['name'], ENT_QUOTES);
$desc = htmlentities($_POST['description'], ENT_QUOTES);
$opt = $_POST['opt'];

if($opt == 'def') $option = '1';
else $option = '0';

if(trim($name) == '') echo '<br /><br />Please insert an album name.';
else {
	
	
	// Make directory
	$q1 = mysql_query(" SELECT `id` FROM `pic_album` ORDER BY `id` DESC LIMIT 1") or die("<br /><br />Mysql error");
	$r1 = mysql_fetch_array($q1);
	$id = $r1['id'] + 1;

	$dir = $ALBUM['local_path'].$id.'/';
	mkdir($dir);
	chmod($dir, 0755);

	mysql_query(" INSERT INTO `pic_album` (id, name, description, opt) VALUES ('".$id."', '".$name."', '".$desc."', '".$option."') ") or die("<br /><br />Mysql error");
	echo '1'; // send success
}

?>