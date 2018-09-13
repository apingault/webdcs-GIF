<?php
error_reporting(-1);
require_once '../CORE/php/DIP.php';



$DIP = new DIP();

$DIP->getValue("SF6", $SF6, $name, $unit);
echo "p";
echo $SF6;

?>