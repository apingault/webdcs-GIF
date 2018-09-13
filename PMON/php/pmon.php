<?php 
/*
 * PMON.php
 * This script is called every minute by crontab and executes various scripts
 * 
 */

$DIR = realpath(dirname(__FILE__));

$dbh = NULL;

// Include CORE functions
require_once $DIR.'/../../CORE/php/functions.php';
require_once $DIR.'/../../CORE/php/Thread.php';

// Include DIP class
require_once $DIR.'/../../CORE/php/DIP.php';




// Include PMON functions
require_once 'functions.php';
require_once 'scripts.php';



$threads = array();
db_connect(); // connect to the database

// Get all processes
$q = $dbh->prepare("SELECT * FROM PMON WHERE enabled = 1");
$q->execute();
$procs = $q->fetchAll();



foreach($procs as $proc) {

	$t = new Thread($proc['script']); // make new thread 
	$t->start($proc['id'], $proc['arguments']);
	array_push($threads, $t);
}




// keep pmon running until all the threads finish
$running = true;
while($running) {
	
	$tmp = false;
	foreach($threads as $th) {
		
		if($th->isAlive()) $tmp = true;
	}
	$running = $tmp;
}


unset($DIP); // close connection with DIP server