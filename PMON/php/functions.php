<?php

// Returns WARNING/ERROR depending on the actual value
function parseStatus($val, $min_err, $min_war, $max_war, $max_err) {
	
	if($val < $min_err) return 20;		// error
	elseif($val < $min_war) return 10;	// warning
	
	if($val > $max_err) return 20;		// error
	elseif($val > $max_war) return 10;	// warning
	
	return 0; // OK
}

function insertLogEntry($pmon_id, $msg, $status) {
	
	global $dbh;
	$now = time();
	
	$q = $dbh->prepare("INSERT INTO PMON_LOG (id, pmon_id, time, message, status) VALUES ('', $pmon_id, $now, '$msg', $status)");
	$q->execute();
}


function handle($id, $trueStatus, $return) {
	
	// Status: 0: OK, 10: WARNING, 20: ERROR
	global $dbh;
	$now = time();
	
	// Get notification addresses
	$notification_addresses = setting('notification_addresses');
	
	// Check previous status
	$q = $dbh->prepare("SELECT * FROM PMON WHERE id = ".$id." LIMIT 1");
	$q->execute();
	$proc = $q->fetch();
	
	
	
	
	$prevStatus = $proc['status']; 
	
	$prevTrueStatus = $proc['true_status']; // true previous status
	$prevTrueStatusChange = $proc['status_change']; // timestamp when the true status has been changed
	
	$deadband = $proc['deadband'];
	
	
	// If changes exceeding time sideband --> $x = true
	$x = ($now - $prevTrueStatusChange > $deadband) ? true : false;
	

	// GENERAL RULE: compare the status always with the true status
	
	// If status changed outside sideband --> log entry
	if($trueStatus != $prevStatus && $x) insertLogEntry($id, $return, $trueStatus);
	
	// If increase of status (e.g. OK --> WARNING, WARNING --> ERROR): send email and update global status
	if($trueStatus > $prevStatus && $x) {
		
		sendMail("WEBDCS GIF++", $return, $notification_addresses);
	}
	
	// Update global status
	if($x) $status = $trueStatus;
	else $status = $prevStatus;
	
	// Update true previous status change
	if($trueStatus == $prevTrueStatus) $p = $prevTrueStatusChange;
	else $p = $now;
	

	
	// Update PMON status
	$q = $dbh->prepare("UPDATE PMON SET true_status = $trueStatus, status = $status, status_change = $p, last_update = ".$now." WHERE id = ".$id);
    $q->execute();	
}


function systemCodes($status) {
	
	switch($status) {
		
		default:	return "UNKNOWN"; break;
		case 0:		return "OK"; break;
		case 10:	return "WARNING"; break;
		case 20:	return "ERROR"; break;
	}
}

function systemCodesFormatted($status) {
	
	switch($status) {
		
		default:	return '<font style="font-weight: bold;">UNKNOWN</font>'; break;
		case 0:		return '<font style="color: green; font-weight: bold;">OK</font>'; break;
		case 10:	return '<font style="color: orange; font-weight: bold;">WARNING</font>'; break;
		case 20:	return '<font style="color: red; font-weight: bold;">ERROR</font>'; break;
	}
}

function getOperationRange($id_name, &$ref, &$warning_bound, &$error_bound) {
	
	global $dbh;
	
	$q = $dbh->prepare("SELECT * FROM PMON_OPERATION_RANGES WHERE id_name = '$id_name'");
	$q->execute();
	$res = $q->fetch();
	
	$ref = $res['reference'];
	$warning_bound = $res['warning_bound'];
	$error_bound = $res['error_bound'];
}