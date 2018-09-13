<?php

function setRunStatus($runid, $status) {
    
    global $dbh;
    $sth1 = $dbh->prepare("UPDATE stability SET status = $status WHERE id = $runid");
    $sth1->execute();
}

function getFormattedStatus($status) {

    switch($status) {
        case '0' : return '<font><b>ONGOING</b></font>';
        case '1' : return '<font color="blue"><b>FINISHED</b></font>';
        case '2' : return '<font color="green"><b>APPROVED</b></font>';
        case '3' : return '<font><b>STANDBY</b></font>';
        case '4' : return '<font><b>HVSCAN</b></font>';
        case '5' : return '<font><b>CRASHED</b></font>';
    }
}

// Load the files to the DB
function fillDB($RUNID, $chamber) {
	
	// Values will not be overwritten as the timestamp is unique
	
	$dbh = new PDO("mysql:host=localhost;dbname=LONGEVITY", "root", "UserlabGIF++");

	
	
	$sth1 = $dbh->prepare("SELECT timestamp FROM `RAW_CURR_".$chamber."` ORDER BY timestamp DESC LIMIT 1 ");
	$sth1->execute();
	$q = $sth1->fetch();
	
	$TIMES = array();
	$BOT = array();
	$TN = array();
	$TW = array();
	$STAT_BOT = array();
	$STAT_TN = array();
	$STAT_TW = array();
	$HVEFF_BOT = array();
	$HVEFF_TN = array();
	$HVEFF_TW = array();
	$SOURCE = array();
	$idstring = sprintf("%06d", $RUNID);
	
	$handle = fopen("/var/operation/STABILITY/".$idstring."/".$chamber."-BOT.dat", "r");
	echo "/var/operation/STABILITY/".$idstring."/".$chamber."-BOT.dat";
	while(($line = fgets($handle)) !== false) {
		 $tmp = explode("\t", $line);
		 array_push($BOT, $tmp[4]);
		 array_push($STAT_BOT, $tmp[6]);
		 array_push($HVEFF_BOT, $tmp[1]);
		 array_push($TIMES, $tmp[0]);
		 array_push($SOURCE, $tmp[10]);
	}
	fclose($handle);

	$handle = fopen("/var/operation/STABILITY/".$idstring."/".$chamber."-TN.dat", "r");
	while(($line = fgets($handle)) !== false) {
		 $tmp = explode("\t", $line);
		 array_push($STAT_TN, $tmp[6]);
		 array_push($HVEFF_TN, $tmp[1]);
		 array_push($TN, $tmp[4]);
	}
	fclose($handle);

	$handle = fopen("/var/operation/STABILITY/".$idstring."/".$chamber."-TW.dat", "r");
	while(($line = fgets($handle)) !== false) {
		 $tmp = explode("\t", $line);
		 array_push($STAT_TW, $tmp[6]);
		 array_push($HVEFF_TW, $tmp[1]);
		 array_push($TW, $tmp[4]);
	}
	fclose($handle);


	for($i=0; $i<count($TIMES); $i++) {

		$tot = floatval($BOT[$i])+floatval($TN[$i])+floatval($TW[$i]);
		$sth1 = $dbh->prepare("INSERT INTO `RAW_CURR_".$chamber."` (timestamp, RUN_ID, I_BOT, I_TN, I_TW, I_TOT, HVEFF_BOT, HVEFF_TN, HVEFF_TW, STAT_BOT, STAT_TN, STAT_TW, SOURCE) VALUES (:timestamp, :runid, :bot, :tn, :tw, :tot, :hveff_bot, :hveff_tn, :hveff_tw, :stat_bot, :stat_tn, :stat_tw, :source)");
		$sth1->bindParam(':timestamp', $TIMES[$i]); 
		$sth1->bindParam(':runid', $RUNID); 
		$sth1->bindParam(':bot', $BOT[$i]); 
		$sth1->bindParam(':tn', $TN[$i]); 
		$sth1->bindParam(':tw', $TW[$i]); 
		$sth1->bindParam(':tot', $tot); 
		$sth1->bindParam(':hveff_bot', $HVEFF_BOT[$i]); 
		$sth1->bindParam(':hveff_tn', $HVEFF_TN[$i]); 
		$sth1->bindParam(':hveff_tw', $HVEFF_TW[$i]); 
		$sth1->bindParam(':stat_bot', $STAT_BOT[$i]); 
		$sth1->bindParam(':stat_tn', $STAT_TN[$i]); 
		$sth1->bindParam(':stat_tw', $STAT_TW[$i]); 
		$sth1->bindParam(':source', $SOURCE[$i]); 
		if(!$sth1->execute()) {
			print_r($sth1->errorInfo());
		}


	}
	
	echo "ok";

	/*
	// add zero 
	$i = count($TIMES)-1; // latest index
	$LAST = $TIMES[$i]+1; // plus 1 second
	$sth1 = $dbh->prepare("INSERT INTO `CORR_RE2-2-NPD-BARC-9` (timestamp, RUN_ID, I_BOT, I_TN, I_TW, I_TOT, HVEFF_BOT, HVEFF_TN, HVEFF_TW, STAT_BOT, STAT_TN, STAT_TW, SOURCE) VALUES (:timestamp, :runid, :bot, :tn, :tw, :tot, :hveff_bot, :hveff_tn, :hveff_tw, :stat_bot, :stat_tn, :stat_tw, :source)");
	$sth1->bindParam(':timestamp', $LAST); 
	$sth1->bindParam(':runid', $RUNID); 
	$sth1->bindParam(':bot', $BOT[$i]); 
	$sth1->bindParam(':tn', $TN[$i]); 
	$sth1->bindParam(':tw', $TW[$i]); 
	$sth1->bindParam(':tot', $tot); 
	$sth1->bindParam(':hveff_bot', $HVEFF_BOT[$i]); 
	$sth1->bindParam(':hveff_tn', $HVEFF_TN[$i]); 
	$sth1->bindParam(':hveff_tw', $HVEFF_TW[$i]); 
	$sth1->bindParam(':stat_bot', $STAT_BOT[$i]); 
	$sth1->bindParam(':stat_tn', $STAT_TN[$i]); 
	$sth1->bindParam(':stat_tw', $STAT_TW[$i]); 
	$sth1->bindParam(':source', $SOURCE[$i]); 
	if(!$sth1->execute()) {
		print_r($sth1->errorInfo());
	}
	*/
}