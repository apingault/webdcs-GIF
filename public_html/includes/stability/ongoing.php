<script>
function stopRun() {

    var reason = prompt("Reason for stop/pause the run:", "");
    document.getElementById("abort_comment").value = reason;
}
</script>
<?php

$run = stabilityOngoing();
$idstring = sprintf("%06d", $run);
$runFile = file_get_contents("/var/operation/RUN_STABILITY/run");


if($_POST['stoprun']) {
    
    $comment = $_POST['aborted_comment'];
    if(str_replace(" ", "", $comment) == "") msg("Please enter comment to stop the run", "error");
    else {
   
        // Add KILL reason to LOG file
        $log = sprintf("%s.[WEBDCS] Run stopped by user. Reason: %s\n", date('Y-m-d.H.i.s'), $comment);
        file_put_contents("/var/operation/STABILITY/".$idstring."/log.txt", $log, FILE_APPEND);

        // Update the run file
        file_put_contents("/var/operation/RUN_STABILITY/run", "END");
		msg("Run stopped");
        
        // Update the status in the database
        setRunStatus($run, 1);
    }
}

if(isset($_POST['standbyrun'])) {
    
    file_put_contents("/var/operation/RUN_STABILITY/run", "STANDBY");
    msg("Run put in standby mode");
    
    setRunStatus($run, 3);
    //header("Refresh:0");
}

if(isset($_POST['hvscan'])) {
    
    file_put_contents("/var/operation/RUN_STABILITY/run", "HVSCAN");
    msg("Run put in standby mode");
    
    setRunStatus($run, 4);
    //header("Refresh:0");
}

if(isset($_POST['resumerun'])) {
	
    file_put_contents("/var/operation/RUN_STABILITY/run", "RUN");
    msg("Run resumed.");
    
    $log = sprintf("%s.[WEBDCS] Run resumed by user\n", date('Y-m-d.H.i.s'));
    file_put_contents("/var/operation/STABILITY/".$idstring."/log.txt", $log, FILE_APPEND);
    
    setRunStatus($run, 0);
    //header("Refresh:0");
}



$runFile = file_get_contents("/var/operation/RUN_STABILITY/run");

if($run == -1) msg("No ongoing run", "warning");
else {
	
	$sth1 = $dbh->prepare("SELECT * FROM stability WHERE id = $run LIMIT 1");
	$sth1->execute();
	$res = $sth1->fetch();

	
	echo "<h3>Ongoing run ID: ".$idstring." - ".getFormattedStatus($res['status'])."</h3>";

	
	$dis = (strpos($runFile, 'RUN') !== false || strpos($runFile,'STANDBY') !== false) ? '' : 'disabled="disabled"';
    echo '<form style="float: left;" id="abortScan_form" method="POST" action="" onsubmit="stopRun()">';
    echo '<input type="hidden" name="aborted_comment" id="abort_comment" value="" />';
    echo '<input type="submit" '.$dis.' name="stoprun" value="Stop run" />';
    echo '</form>';

    
	$dis = (strpos($runFile,'RUN') !== false) ? '' : 'disabled="disabled"';
    echo '<form style="float: left;" method="POST" action="" onsubmit="return confirm(\'Do you really want to go to STANDBY/HVSCAN mode?\');">';
    echo '&nbsp;<input type="submit" name="standbyrun" value="Standby mode" />';
    echo '&nbsp;<input type="submit" '.$dis.' name="hvscan" value="HVSCAN" />';
    echo '</form>';
    
	
	$dis = ($runFile == "STANDBY" || $runFile == "HVSCAN") ? '' : 'disabled="disabled"';
    echo '<form style="float: left;" method="POST" action="">';
    echo '&nbsp;<input type="submit" '.$dis.' name="resumerun" value="Resume" />';
    echo '</form>';
	
	echo '&nbsp;<button class="button" onclick="location.href=\'index.php?q=longevity&p=rundqm&id='.$run.'\';">Go to run page</button> ';

	
	echo "<br /><br /><br />";


	
	$file = "/var/operation/STABILITY/".$idstring."/log.txt";
	showLogFile($file, true);
}