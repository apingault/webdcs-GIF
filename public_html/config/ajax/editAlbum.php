<?php 

require_once '../config.php';
$id = $_POST['albumid'];
if($id == 0) die('You cannot edit the default album.');
$name = htmlentities($_POST['name'], ENT_QUOTES);
$desc = htmlentities($_POST['description'], ENT_QUOTES);
$opt = $_POST['opt'];

if($opt == 'def') $option = '1';
else $option = '0';

if(trim($name) == '') echo '<br /><br />Please insert an album name.';
else {

	mysql_query(" UPDATE `pic_album` SET `name` = '".$name."', `description` = '".$desc."', `opt` = '".$option."' WHERE `id` = '".$id."'  ") or die("<br /><br />Mysql error");
	echo '1'; // send success
}

?>