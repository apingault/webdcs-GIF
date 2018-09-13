<?php 

require_once '../config.php';
$id = $_POST['id'];
$desc = htmlentities($_POST['desc'], ENT_QUOTES, "UTF-8");

//$desc = htmlspecialchars($_POST['desc']);
$active = $_POST['active'];

mysql_query(" UPDATE `pic_photos` SET `comment` = '".$desc."',  `active` = '".$active."' WHERE `id` = '".$id."'  ") or die("<br /><br />Mysql error");
echo '1'; // send success


?>