<?php


$sth1 = $DB['MAIN']->prepare("SELECT value FROM settings WHERE setting = 'daqtype'");
$sth1->execute();
$p = $sth1->fetch();
msg($p, "warning");


// Get 
$run = file_get_contents("/var/operation/RUN/run");
$HVprocess = shell_exec("ps -Al | grep HV");
$DAQprocess = exec("ps -Al | grep daq");
$StabilityProcess = exec("ps -Al | grep Longevity");

$HVprocess = ($HVprocess == "") ? "no process found" : nl2br($HVprocess);
$DAQprocess = ($DAQprocess == "") ? "no process found" : nl2br($DAQprocess);
$StabilityProcess = ($HVprocess == "") ? "no process found" : nl2br($StabilityProcess);

$DAQPID = shell_exec("ps -Al | grep daq | awk '{print $4}'");
$DCSPID = shell_exec("ps -Al | grep HVscan | awk '{print $4}'");
$STAPID = shell_exec("ps -Al | grep Longevity | awk '{print $4}'");




if(isset($_POST['killDAQ'])) {
    
    $tmp = explode('\n', $DAQPID);
    foreach($tmp as $p) {
        shell_exec("kill $p");
    }
    header("Refresh:0");
}

if(isset($_POST['killDCS'])) {
    
    $tmp = explode('\n', $DCSPID);
    foreach($tmp as $p) {
        shell_exec("kill $p");
    }
    header("Refresh:0");
}

// The nl2br function must be called
$DAQPID = ($DAQPID == "") ? "no process found" : nl2br($DAQPID);
$DCSPID = ($DCSPID == "") ? "no process found" : nl2br($DCSPID);
$STAPID = ($STAPID == "") ? "no process found" : nl2br($STAPID);

?>

<div class="content">
    
    <h3>DCS-DAQ Diagnostics</h3>
    
    <table>


        <tr style="height:25px">
            <td valign="top" width="150px">RUN file:</td>
            <td valign="top"><?php echo $run; ?></td>
        </tr>  
        
        <tr style="height:25px">
            <td valign="top">DAQ ps dump:</td>
            <td valign="top"><?php echo $DAQprocess; ?></td>
        </tr> 
        
        <tr style="height:25px">
            <td valign="top">DAQ PIDs:</td>
            <td valign="top"><?php echo $DAQPID; ?></td>
        </tr> 
        
        <tr style="height:25px">
            <td valign="top" >DCS ps dump:</td>
            <td valign="top"><?php echo $HVprocess; ?></td>
        </tr> 
        
        <tr style="height:25px">
            <td valign="top">DCS PIDs:</td>
            <td valign="top"><?php echo $DCSPID; ?></td>
        </tr> 
        
        <tr style="height:25px">
            <td valign="top" >Stability ps dump:</td>
            <td valign="top"><?php echo $StabilityProcess; ?></td>
        </tr> 
        
        <tr style="height:25px">
            <td valign="top">Stability PIDs:</td>
            <td valign="top"><?php echo $STAPID; ?></td>
        </tr> 
        
        <?php 
        if(getCurrentRole() != 0) {
        ?>
        <tr>
            <td colspan="2">
                <form action="" method="POST">
                    <input type="submit" name="killDAQ" value="Kill DAQ processes" /> <input type="submit" name="killDCS" value="Kill DCS processes" />
                </form>
            </td>
        </tr>
        <?php
        }
        ?>
    
    </table>
    
    
</div>