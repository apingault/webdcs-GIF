<?php

function sendMail($sub, $msg, $rec) {
    
    mail($rec, $sub, str_replace("~", " ", $msg));
}

function DIP() {
	
	
}

function checkGas() {
	
	
}


function checkEnvironmental() {
	
	
}

function ping($ip_address, $dev_name) {
    
    $return = false; // default return = false
    
    $pingresult = exec("ping -c 1 $ip_address", $outcome, $status);
    if($status != 0) $return = "Cannot connect to ".$dev_name." (".$ip_address.")";
    
    return $return; 
}

function longevity($ip_address, $dev_name) {
    
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