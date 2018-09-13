<?php 
//die("Intervention");

define('INDEX', true); // used for secuirity: all php files must originate from this index.php and therefore the INDEX global var should exist 

$post = $_POST;

require_once('library/config.php'); // require all settings
require_once('library/bootstrap.php');

