<?php

require_once 'functions.php';

if((!isset($argv[4]))) die();

$name = $argv[1];
$log = $argv[2];
$cmd = $argv[3];
$arg = $argv[4];

exec("script ".$log." -f -c '".$cmd." ".$arg."'");

if(!exec('grep EXIT_SUCCESS '.$log)) {

	db_connect();
	//sendMail($sub, $msg, $rec)
    sendMail("WEBDCS GIF++", "Script ".$name." crashed", setting("notification_addresses"));
}

?>
