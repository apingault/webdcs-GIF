<?php
// GET LATEST SCAN ID
$q = $DB['MAIN']->prepare("SELECT * FROM PMON");
$q->execute();
$notifications = $q->fetchAll();

$q = $DB['MAIN']->prepare("SELECT * FROM PMON_OPERATION_RANGES");
$q->execute();
$op = $q->fetchAll();

$DCSPID = shell_exec("ps -Al | grep HVscan | awk '{print $4}'"); // Get the PID from the running process
$DAQPID = shell_exec("ps -Al | grep daq | awk '{print $4}'");
$DQMPID = shell_exec("pgrep -a python | grep 'DQM.py' | awk '{print $1}'");
$DQMPS = exec("pgrep -a python | grep 'DQM.py'");

if(isset($_POST['killDAQ'])) {
    
    $tmp = explode('\n', $DAQPID);
    foreach($tmp as $p) {
        shell_exec("kill -9 $p");
	//echo "kill -9 $p";
    }
    header("Refresh:0");
}

if(isset($_POST['killDCS'])) {
    
    $tmp = explode('\n', $DCSPID);
    foreach($tmp as $p) {
        shell_exec("kill -9 $p");
    }
    header("Refresh:0");
}

// LYON DAQ STUFF
$ldaq = exec("/home/webdcs/test.py");
$ldaq = explode("_", $ldaq);

getProccessInfo("daq", $running_DAQ, $PID_DAQ, $PS_DAQ);
getProccessInfo("HVscan", $running_DCS, $PID_DCS, $PS_DCS);
getProccessInfo("Longevity", $running_DAQ, $PID_STA, $PS_STA);

$run_hvscan = file_get_contents("/var/operation/RUN/run");
$run_stability = file_get_contents("/var/operation/RUN_STABILITY/run");


if(isset($_POST['resetRUNDAQ'])) {
	
	if($PID_DAQ == "n/a") {
		file_put_contents("/var/operation/RUN/run", "STOP");
		header("Refresh:0");
	}
	else msg("Cannot change runfile during a scan", "error");
}

if(isset($_POST['resetRUNSTABILITY'])) {

	if($PID_STA == "n/a") {
		file_put_contents("/var/operation/RUN_STABILITY/run", "END");
		header("Refresh:0");
	}
	else msg("Cannot change runfile during a stability run", "error");
}

?>
<div class="tooltip"><h3 style="display: inline;">Process monitoring</h3><span class="tooltiptext">Status and diagnostics of the running HVscans or stability program</span></div>

<br /><br />

<table class="table">
	
	<thead>
		<tr>
			<td width="150px"></td>
			<td width="150px">DAQ process</td>
			<td width="150px">DCS process</td>
			<td width="150px">Stability process</td>
            <td width="150px">DQM process</td>
            <td width="150px">LYON DAQ</td>
		</tr>
	</thead>
	
	<tbody>
		
		<tr>
			<td>Status:</td>
			<td><b><?php echo ($PID_DAQ == "n/a") ? '<font>NOT RUNNING</font>' : '<font style="color: green">RUNNING</font>' ?></b></td>
			<td><b><?php echo ($PID_DCS == "n/a") ? '<font>NOT RUNNING</font>' : '<font style="color: green">RUNNING</font>' ?></b></td>
			<td><b><?php echo ($PID_STA == "n/a") ? '<font>NOT RUNNING</font>' : '<font style="color: green">RUNNING</font>' ?></b></td>
			<td><b><?php echo (empty($DQMPID)) ? '<font>NOT RUNNING</font>' : '<font style="color: green">RUNNING</font>' ?></b></td>
            <td><b><abbr title="0=OK, 1=DAQ CONNECTION ERROR, 2=DAQ 404">Connection:</abbr> <?php echo $ldaq[3]; ?></b></td>
		</tr>
		
		<tr>
			<td>PIDs:</td>
			<td><?php echo $PID_DAQ; ?></td>
			<td><?php echo $PID_DCS; ?></td>
			<td><?php echo $PID_STA; ?></td>
            <td><?php echo $DQMPID; ?></td>
            <td>Event: <?php echo $ldaq[0]; ?></td>
		</tr>
		
		<tr>
			<td>Dump ps:</td>
			<td><?php echo $PS_DAQ; ?></td>
			<td><?php echo $PS_DCS; ?></td>
			<td><?php echo $PS_STA ?></td>
            <td><?php echo $DQMPS; ?></td>
            <td>Run: <?php echo $ldaq[1]; ?></td>
		</tr>
		
		<tr>
			<td>Run file:</td>
			<td><?php echo $run_hvscan; ?></td>
			<td><?php echo $run_hvscan; ?></td>
			<td><?php echo $run_stability; ?></td>
            <td>n/a</td>
            <td>n/a</td>
		</tr>
                
                

</table>
<br />
<?php 
if(getCurrentRole() != 0) {
?>
<form style="float: left;" action="" method="POST" onsubmit="return confirm('Do you really want to kill the process?')">
	<input type="submit" name="killDAQ" value="Kill DAQ processes" /> <input type="submit" name="killDCS" value="Kill DCS processes" /> 
</form>
 
<form action="" method="POST">
&nbsp;<input type="submit" name="resetRUNDAQ" value="Reset HVscan runfile" /> <input type="submit" name="resetRUNSTABILITY" value="Reset stability runfile" />
</form>
<?php
}
?>
<br /><br />


<div class="tooltip"><h3 style="display: inline;">Operational ranges</h3><span class="tooltiptext">List of DIP parameters to be checked (see DIP monitoring below)</span></div>

<br /><br />

<table class="table">
        
	<thead><tr>
		<td width="20px"></td>
        <td width="180px">Name</td>
		<td width="300px">Parameter</td>
        <td width="80px">Min. error</td>
        <td width="80px">Min. warning</td>
        <td width="80px">Max. warning</td>
        <td width="80px">Max. error</td>
		<td width="120px">Current value</td>
		<td width="80px">Status</td>
	</tr></thead>
        
	<tbody>
    
    <?php

    foreach($op as $o) {
		
        getValue($o["id_name"], $value, $name, $unit);
        $enabled = ($o['enabled'] == 1) ? $ICON_TICK : $ICON_CROSS;

        echo "<tr>";
	echo "<td>".$enabled."</td>";
        echo "<td>".$o["name"]."</td>";
	echo "<td>".str_replace("[", "", str_replace("]", "", $o["id_name"]))."</td>";
        echo "<td style=\"color: red;\">".$o["min_error_bound"]."</td>";
	echo "<td style=\"color: orange;\">".$o["min_warning_bound"]."</td>";
	echo "<td style=\"color: orange;\">".$o["max_warning_bound"]."</td>";
	echo "<td style=\"color: red;\">".$o["max_error_bound"]."</td>";

	echo "<td>".$value."</td>";
	echo "<td>";
	echo systemCodesFormatted($o["status"]);
	echo "</td>";
        echo "</tr>";
    }
    
    ?>
</tbody>   
</table>



<br /><br />
<div class="tooltip"><h3 style="display: inline;">Running cronjobs</h3><span class="tooltiptext">List of running cronjobs, i.e. processes which are executed periodically</span></div>

<br /><br />

    
<table class="table">
        
	<thead><tr>
		<td width="20px"></td>
        <td width="190px">Monitor name</td>
		<td width="400px">Description</td>
        <td width="100px">Script</td>
        <td width="250px">Arguments</td>
        <td width="80px">Status</td>
	</tr></thead>
        
	<tbody>
    
    <?php
    foreach($notifications as $i => $notification) {

	$enabled = ($notification['enabled'] == 1) ? $ICON_TICK : $ICON_CROSS;

        echo "<tr>";
	echo "<td>".$enabled."</td>";
        echo "<td>".$notification["name"]."</td>";
	echo "<td>".$notification["comment"]."</td>";
        echo "<td>".$notification["script"]."</td>";
        echo "<td>".$notification["arguments"]."</td>";
        echo "<td>";
	echo systemCodesFormatted($notification["status"]);
	echo "</td>";
        echo "</tr>";
    }
    
    ?>
</tbody>   
</table>

