<?php

function gasDIP($pmon_id) {
	
	// Connect to DB for this thread (always needed for handle function)
	$dbh = NULL;
	global $dbh;
	db_connect();
	$DIP = new DIP();

	$returnMSG = "OK"; // default return = false
	$status = 0; // default status = 0 (ok)
	
	$DIP->getValue("SF6", $SF6, $name, $unit);
	$DIP->getValue("C2H2F4", $C2H2F4, $name, $unit);
	$DIP->getValue("iC4H10", $iC4H10, $name, $unit);
	
	unset($DIP);
	
	if($SF6 < 0.1 && $C2H2F4 < 0.1 && $iC4H10 < 0.1) {
		
		$returnMSG = "Gas DIP values zero";
		$status = 20;
	}
	
	handle($pmon_id, $status, $returnMSG);
	db_disconnect();
}




function OPRange($pmon_id) {

	// Connect to DB for this thread (always needed for handle function)
	$dbh = NULL;
	global $dbh;
	db_connect();
	
	$DIP = new DIP();
	
	$returnMSG = "OK"; // default return = false
	$status = 0; // default status = 0 (ok)
	
	// Get the notification addresses
	$notification_addresses = setting('notification_addresses');

	// Get all the to be monitored parameters
	$q = $dbh->prepare("SELECT * FROM PMON_OPERATION_RANGES WHERE enabled = 1");
	$q->execute();
	$res = $q->fetchAll();
	
	foreach($res as $r) {
		
		$DIP->getValue($r['id_name'], $val, $name, $unit);
		$stat = parseStatus($val, $r['min_error_bound'], $r['min_warning_bound'], $r['max_warning_bound'], $r['max_error_bound']);
	
		// If gas flows inside the ID_NAME --> check first gasDIP status
		if(strpos($r['id_name'], "SF6") !== FALSE || strpos($r['id_name'], "C2H2F4") !== FALSE || strpos($r['id_name'], "iC4H10") !== FALSE) {
			
			$DIP->getValue("SF6", $SF6, $name, $unit);
			$DIP->getValue("C2H2F4", $C2H2F4, $name, $unit);
			$DIP->getValue("iC4H10", $iC4H10, $name, $unit);

			if($SF6 < 0.1 && $C2H2F4 < 0.1 && $iC4H10 < 0.1) {

				$stat = -10; // UNKNOWN
			}
		}
		
		$status = ($stat > $status) ? $stat : $status; // Update the global status of this PMON
		
		$msg = $r['name'].": ".systemCodes($r['status'])." -> ".systemCodes($stat)." (".$val.")";
		
		if($r['status'] == $stat) continue;
		elseif($r['status'] < $stat) { // if status increased: notifictation + email

			insertLogEntry($pmon_id, $msg, $stat);
			sendMail("WEBDCS GIF++", $msg, $addresses);
		}
		elseif($r['status'] > $stat) { // if status increased: only log file
			
			insertLogEntry($pmon_id, $msg, $stat);
		}
		
		//print $val." ".$stat."dd \xA";
		
		// Update status
		$q = $dbh->prepare("UPDATE PMON_OPERATION_RANGES SET status = $stat WHERE id = ".$r['id']);
		$q->execute();
		
	}
	
	unset($DIP);
	
	$returnMSG = "Operational ranges ". systemCodes($status);
	handle($pmon_id, $status, $returnMSG);
	db_disconnect();
}

function ping($pmon_id, $arg) {
	
	// Connect to DB for this thread (always needed for handle function)
	$dbh = NULL;
	global $dbh;
	db_connect();
	
	list($ip_address, $dev_name) = explode(" ", $arg);
    
    $returnMSG = "OK"; // default return = false
	$status = 0; // default status = 0 (ok)
    
    $pingresult = exec("ping -c 1 $ip_address", $outcome, $status);
    if($status != 0) {
		$returnMSG = "ERROR: Cannot connect to ".$dev_name." (".$ip_address.")";
		$status = 20;
	}

    handle($pmon_id, $status, $returnMSG);
	db_disconnect();
}

function longevityCheck($ip_address, $dev_name) {
    
    global $dbh;
    
    // Check if longevity is running according to the database
    $q = $dbh->prepare("SELECT * FROM stability WHERE status = 0");
    $q->execute();
    if($q->rowCount() > 0) {
        
        $id = file_get_contents("/var/operation/RUN_STABILITY/id");
        
    }
    
    // Get current longevity ID
    
    
    // Check 
    
    
   
# Check stability script
/*
runfile=$(cat /var/operation/RUN_STABILITY/run)
if ( [ "$runfile" != "CRASHED" ]  && [ "$runfile" != "KILL" ] && [ "$runfile" != "END" ] && (! pgrep Longevity))
then
    /home/webdcs/software/monitoring/sendMail.sh "Stability program crashed, power down detectors"
    #/home/webdcs/software/CAEN/webdcs/StandbyStability.sh $id 10
    #echo "CRASHED" > /var/operation/RUN_STABILITY/run
    #echo `date +"%Y-%m-%d.%H.%M.%S"`.[SUPERVISOR][0] Stability program crashed, power down detectors >> "/var/operation/STABILITY/`pr$
fi
*/
    
    $return = false; // default return = false
    
    $pingresult = exec("ping -c 1 $ip_address", $outcome, $status);
    if($status != 0) $return = "Cannot connect to ".$dev_name." (".$ip_address.")";
    
    return $return; 
}

// Calculate the beta factor for the PT correction and store it in the RUN directory
function PTcorr($pmon_id) {

	// Connect to DB for this thread (always needed for handle function)
	$dbh = NULL;
	global $dbh;
	db_connect();
	
	$DIP = new DIP();
	
	$DIP->getValue("P", $P, $name, $unit);
	$DIP->getValue("TIN", $T, $name, $unit);
	
	unset($DIP);
	
    $alpha = 0.8;
    $p0 = 990.;
    $T0 = 20.;
	

	$beta_min = ((1.0 - $alpha) + $alpha * $P / $p0 * ($T0 + 273.15) / ($T + 273.15));
	$beta_max = ((1.0 - $alpha) + $alpha * $P / $p0 * ($T0 + 273.15) / ($T + 273.15));
	
	$beta = ((1.0 - $alpha) + $alpha * $P / $p0 * ($T0 + 273.15) / ($T + 273.15));
	
	// do some checks on beta...
	file_put_contents("/var/operation/RUN/PTcorr", $beta);
	
	handle($pmon_id, 0, "OK");
	db_disconnect();
}


function longevityDaily($pmon_id) {
	
	date_default_timezone_set("Europe/Brussels");
	
	// Run at 3 AM (1:00)
	if(intval(date('H')) == 3 && intval(date('i')) == 0) {

		exec("php /home/webdcs/software/webdcs/scripts/longevity_daily_scan/cronJob.php > /dev/null 2>/dev/null &");
	}

	handle($pmon_id, 0, "OK");	
}