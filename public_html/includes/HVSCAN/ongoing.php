<script>
function killScan() {

    var reason = prompt("Reason for kill the scan:", "");
    document.getElementById("killed_comment").value = reason;
    
    var hv = prompt("Set voltages to (channels turned off if HV < 100):", "");
    document.getElementById("killed_hv").value = hv;
}
</script>

<?php

if(isset($_POST['stop']) and $_POST['stop']) {
    
    $killed_comment = $_POST['killed_comment'];
    $killed_hv = $_POST['killed_hv'];
    if(str_replace(" ", "", $killed_comment) == "") {
        msg("Please enter a reason to stop the HVscan", "error");
    }
    elseif($killed_hv < 0 || $killed_hv > 10000) {
        msg("The kill voltage should be between 0 and 10 kV", "error");
    }
    else {
        $run = HVscanOngoing();
        // msg("run $id : Kill voltage = '$killed_hv'", "error");
        // if (str_replace(" ", "", $killed_hv) == ""){
        //     msg("No value given for kill voltage, getting from settings", "warning");
        //     $sqlKill = $DB['MAIN']->prepare("SELECT lastHV FROM hvscan WHERE id = $id LIMIT 1");
        //     $sqlKill->execute();
        //     $sql_hv = $sqlKill->fetch();
        //     msg("Kill voltage = '$sql_hv'", "error");
        // }

    file_put_contents("/var/operation/RUN/run", "KILL"); // Send KILL command to DAQ

    $timePassed = 0;
    while ($timePassed < 10)
    {
        $runVal = file_get_contents("/var/operation/RUN/run");
        if ($runVal == "STOP"){
            break;
        }
        $timePassed += 1;
        sleep(1);
    }
		
    $run = HVscanOngoing();
        
    // The ongoing scan is stored in the variable $run

    $DCSPID = shell_exec("ps -Al | grep HVscan | awk '{print $4}'"); // Get the PID from the running process
    $DAQPID = shell_exec("ps -Al | grep daq | awk '{print $4}'");

    $daqtdb = $DB['MAIN']->prepare("SELECT value FROM settings WHERE setting = 'daqtype'");
    $daqtdb->execute();
    $daqtype = $daqtdb->fetch();
    if ($daqtype == 'digitizer'){
        shell_exec("pkill -f wavedump");
    }

    shell_exec("pkill -f daq");
    shell_exec("pkill -f HVscan");


    
    // SEND LYON STOP
    exec("/home/webdcs/stop.py");


    $logfile = sprintf("/var/operation/HVSCAN/%06d/log.txt", $run);

    // Add KILL reason to LOG file
    $log = sprintf("%s.[WEBDCS] HVscan killed by user. Reason: %s\n", date('Y-m-d.H.i.s'), $killed_comment);
    file_put_contents($logfile, $log, FILE_APPEND);
    $log = sprintf("%s.[WEBDCS] Lower voltage on detectors\n", date('Y-m-d.H.i.s'));
    file_put_contents($logfile, $log, FILE_APPEND);

    // if($kill_hv <= 20) {
    //     $log = sprintf("%s.[WEBDCS] Set low HV on detectors and turn off \n", date('Y-m-d.H.i.s'));
    //     file_put_contents($logfile, $log, FILE_APPEND);
    // }
    // else if($kill_hv == 99999) {
    //     $sqlVolt = $DB['MAIN']->prepare("SELECT HV FROM hvscan_VOLTAGES WHERE id=$id AND time_end IS NOT NULL ORDER BY HV DESC LIMIT 1");
    //     $sqlVolt->execute();
    //     $kill_hv = $sqlVolt->fetch();
    //     $log = sprintf("%s.[WEBDCS] Keep latest voltages ($kill_hv) on detectors \n", date('Y-m-d.H.i.s'));
    //     file_put_contents($logfile, $log, FILE_APPEND);

    // }
    // else {
    //     $sqlVolt = $DB['MAIN']->prepare("SELECT value FROM settings WHERE setting='standby_voltage'");
    //     $sqlVolt->execute();
    //     $kill_hv = $sqlVolt->fetch();
    //     $log = sprintf("%s.[WEBDCS] Set HV to $killed_hv V \n", date('Y-m-d.H.i.s'));
    //     file_put_contents($logfile, $log, FILE_APPEND);
    // }

    // Update database
    $end = time();
    $t = $DB['MAIN']->prepare("UPDATE hvscan SET status = 2, time_end = $end WHERE id = ".$run);
    $t->execute();

    // Power down detectors (standby mode)
    startCAEN("HVscan", $run, intval($killed_hv));

    // Refresh current page
   // header("Refresh:0");
    }
}

if(isset($_POST['pause']) and $_POST['pause']) {
    
    
    file_put_contents("/var/operation/RUN/run", "DAQ_INIT_PAUSE"); // Send KILL command to DAQ
    
}

if(isset($_POST['resume']) and $_POST['resume']) {
    
    
    file_put_contents("/var/operation/RUN/run", "DAQ_INIT_RESUME"); // Send KILL command to DAQ
    
}



$run = HVscanOngoing(); // Get 
if($run == -1) msg("No ongoing scan", "warning");
else {
	
    $idstring = sprintf("%06d", $run);

    echo "<h3>Ongoing run ID: ".$idstring."</h3>";

    echo '<form style="float: left;" id="killScan_form" method="POST" action="" onsubmit="killScan()">';
    echo '<input type="hidden" name="killed_comment" id="killed_comment" value="" />';
    echo '<input type="hidden" name="killed_hv" id="killed_hv" value="" />';
    echo '<input type="submit" name="stop" value="Stop HV scan" />';  
    echo '</form>';
    
    echo ' <form style="float: left; margin-left: 3px;" method="POST" action="">';    
    $pause = str_replace(array("\r", "\n"), '', file_get_contents("/var/operation/RUN/run"));
    //echo $pause;
   
    if($pause == "RUNNING") {
            echo '<input type="submit" name="pause" value="Pause LYONDAQ" />';
    }
    if($pause == "DAQ_PAUSE" or $pause == "DAQ_INIT_PAUSE") {
            echo '<input type="submit" name="resume" value="Resume LYONDAQ" />';
    }
    
    
    echo '</form>';

    echo '&nbsp;<button class="button" onclick="location.href=\'index.php?q=hvscan&p=hvscan&id='.$run.'\';">Go to scan page</button> ';
    echo '<br /><br />';

    $file = "/var/operation/HVSCAN/".$idstring."/log.txt";
    showLogFile($file, true);
}
