<?php
/*
 * stabilitytest.php
 */

if(isset($_POST['save'])) {
 
    $time1 = $_POST['time1'];
    $time2 = $_POST['time2'];
    $mtime = $_POST['mtime'];
    $wtime = $_POST['wtime'];
    $step = $_POST['step'];

    if($time1 == '' OR $time2 == '' OR $mtime == '' OR $wtime == '' OR $step == '') {
        $error = "please fill in all fields.";
    }
    elseif($mtime%$step != 0) {
        $error = "measure time interval must be a multiple of the total measure time.";
    }
    else {
        
        $sth = $dbh->prepare("UPDATE stabilitytest_config SET time1 = :time1, time2 = :time2, mtime = :mtime, wtime = :wtime, step = :step WHERE mid = :mid");
        $sth->bindParam(':mid', $mid);  
        $sth->bindParam(':time1', $time1);  
        $sth->bindParam(':time2', $time2);  
        $sth->bindParam(':mtime', $mtime); 
        $sth->bindParam(':wtime', $wtime); 
        $sth->bindParam(':step', $step); 
        $sth->execute();
    }
    
    setCrontab(); // TO BE CHECKED
    $pass = "Settings successfully saved.";
}

// Get stabilitytest information for current module
$sth = $dbh->prepare("SELECT * FROM stabilitytest_config WHERE mid = :mid");
$sth->execute(array(':mid' => $mid));
$prop = $sth->fetch();

?>

<div class="content">
    
    <div style="display: inline">
        <h3 style="display: inline;">Stability Test settings</h3> &nbsp;&nbsp;&mdash;&nbsp;&nbsp; <?php echo changeModule('stabilitytest', $mid) ?> 
    </div>
    <br /><br />
  
    <?php 
    if(!empty($error)) { echo '<div class="error">Error: '.$error.'</div>'; }
    elseif($pass != '') { echo '<div class="pass">'.$pass.'</div>'; }

    if(!checkMeteo()) {
        echo '<div class="error">Error: meteo station offline</div>'; 
    }
    if(!checkMainFrame()) {
        echo '<div class="error">Error: mainframe offline</div>'; 
    }
    ?>

    <form method="post" action="" id="hvscan-form">
    <table>
        <tr>
            <td width="150px"># days HV1 [d]:</td>
            <td width="150px"><input size="12" name="time1" type="text" value="<?php echo $prop['time1']; ?>" /></td>
            <td width="150px">Measuring frequency [min]:</td>
            <td width="150px"><input size="12" disabled="disabled" name="frequency" type="text" value="<?php echo settings("stabtest_frequency"); ?>" /></td>
        </tr>
        <tr>
            <td># days HV2 [d]:</td>
            <td><input size="12" name="time2" type="text" value="<?php echo $prop['time2']; ?>" /></td>  
            <td>Waiting time [s]:</td>
            <td><input size="12" name="wtime" type="text" value="<?php echo $prop['wtime']; ?>" /></td>
        </tr>
        <tr>
            <td>Measure time [s]:</td>
            <td><input size="12" name="mtime" type="text" value="<?php echo $prop['mtime']; ?>" /></td>
            <td>Measure time interval [s]:</td>
            <td><input size="12" name="step" type="text" value="<?php echo $prop['step']; ?>" /></td>
        </tr>  
    </table>
    <br />
    <input type="submit" name="save" value="Save settings" /> &nbsp;&nbsp;&nbsp; *<font style="font-size: 10px">The program will start with HV1</font>
    </form>
  
<?php

if(isset($_POST['start'])) {
    
    $t = array_keys($_POST['start']);
    $id = $t[0];
    
    // Update the stabilitytest table
    $now = time();
    $days = $_POST['days_'.$id];
    $HV1 = $_POST['hv1_'.$id];
    $HV2 = $_POST['hv2_'.$id];
    $stop = $now+$days*24*3600;
    //echo ($stop-$now)/(3600*24);
    $sth = $dbh->prepare("INSERT INTO stabilitytest (detectorid, starttest, stoptest, HV1, HV2) VALUES (:id, :starttest, :stoptest, :HV1, :HV2) ON DUPLICATE KEY UPDATE starttest = :starttest, stoptest = :stoptest, HV1 = :HV1, HV2 = :HV2");
    $sth->bindParam(':id', $id);  
    $sth->bindParam(':starttest', $now);
    $sth->bindParam(':stoptest', $stop);
    $sth->bindParam(':HV1', $HV1);
    $sth->bindParam(':HV2', $HV2);
    $sth->execute();
    //print_r($dbh->errorInfo());
    
    // Update the process of the detector
    $sth = $dbh->prepare("UPDATE detectors SET process = 2 WHERE id = :id");
    $sth->bindParam(':id', $id);  
    $sth->execute();
}
elseif(isset($_POST['stop'])) {
    
    // In order to stop a test, we set the stoptest timestamp in the past (i.e. NOW-1000).
    // In this way, the stabilitytest executable will clean up the database automatically:
    // * remove detector from 'stabilitytest' table
    // * set process = 1 in 'detectors' table
    
    $t = array_keys($_POST['stop']);
    $id = $t[0];
    $sth = $dbh->prepare("UPDATE stabilitytest SET stoptest = UNIX_TIMESTAMP()-1000 WHERE detectorid = :id");
    $sth->bindParam(':id', $id);  
    $sth->execute();
    
    // Set process of detector to three --> status code for stopping stability test
    $sth = $dbh->prepare("UPDATE detectors SET process = 3 WHERE id = :id");
    $sth->bindParam(':id', $id);  
    $sth->execute();
}
elseif(isset($_POST['unsuspend'])) {
     
    $t = array_keys($_POST['unsuspend']);
    $id = $t[0];
    
    // Set process of detector to three --> status code for stopping stability test
    $sth = $dbh->prepare("UPDATE detectors SET process = 2 WHERE id = :id");
    $sth->bindParam(':id', $id);  
    $sth->execute();
}
elseif(isset($_POST['clearlog'])) {
    
    // Delete log file
    exec("rm -rf /home/user/data/tmp/".$mid."/stabilitytest.log");
}

// Select all detectors
$sth1 = $dbh->prepare("SELECT * FROM detectors WHERE mid = :mid");
$sth1->execute(array(':mid' => $mid));
$detectors = $sth1->fetchAll();

?>
    
    <br />
    <h3>Manage stability tests</h3>
    <form action="" method="post" id="monitor-form">
    <table class="table monitor" cellpadding="5px" cellspacing="0">
        <thead>
        <tr>
            <td class="oddrow" width="310px">Detector</td>
            <td class="oddrow" width="250px">Test Status</td>
            <td class="oddrow" width="120px">Action</td>
            <td class="oddrow" width="60px">HV1</td>
            <td class="oddrow" width="60px">HV2</td>
        </tr>
        </thead>
        <tbody>              
        <?php
        $i=0;
        foreach ($detectors as $value) {
            
            $class = ($i%2 == 0) ? 'evenrow' : 'oddrow';
            $id = $value['id'];
            echo '<tr style="display: none;"><td>';
            echo '<input type="hidden" name="slot_'.$id.'" value="'.$value['slot'].'" />';
            echo '<input type="hidden" name="channel_'.$id.'" value="'.$value['channel'].'" />';
            echo '</td></tr>';
            echo '<tr>';
            echo '<td class="'.$class.'" style="line-height: 25px;">'.$value['name'].'</td>';
            $sth2 = $dbh->prepare("SELECT * FROM stabilitytest WHERE detectorid = :id AND stoptest > UNIX_TIMESTAMP() ");
            $sth2->execute(array(':id' => $value['id']));
            $res = $sth2->fetch();
            if($sth2->rowCount() > 0 AND $value['process'] != 4) {
                echo '<td class="'.$class.'" style="line-height: 25px;"><font style="color: green; font-weight: bold;">Stability test until '.date('Y-m-d H:i', $res['stoptest']).'</font></td>';
                echo '<td class="'.$class.'" style="line-height: 25px;"><input type="submit" name="stop['.$id.']" value="stop" /> <input disabled="disabled" type="text" style="width: 30px;" name="days_'.$id.'" value="'.($res['stoptest']-$res['starttest'])/(3600*24).'" /> (d)</td>';
                echo '<td class="'.$class.'"><input disabled="disabled" type="text" style="width: 40px;" name="hv1_'.$id.'" value="'.$res['HV1'].'" /></td>';
                echo '<td class="'.$class.'"><input disabled="disabled" type="text" style="width: 40px;" name="hv2_'.$id.'" value="'.$res['HV2'].'" /></td>';
            }
            else if($value['process'] == 1) {
                echo '<td class="'.$class.'" style="line-height: 25px;"><font style="color: green; font-weight: bold;">HVscan</font></td>';
                echo '<td class="'.$class.'" style="line-height: 25px;"><input disabled="disabled" type="submit" name="start['.$id.']" value="start" /> <input disabled="disabled" type="text" style="width: 30px;" name="days_'.$id.'" value="14" /> (d)</td>';        
                echo '<td class="'.$class.'"><input disabled="disabled" type="text" style="width: 40px;" name="hv1_'.$id.'" value="'.$res['HV1'].'" /></td>';
                echo '<td class="'.$class.'"><input disabled="disabled" type="text" style="width: 40px;" name="hv2_'.$id.'" value="'.$res['HV2'].'" /></td>';
            }
            else if($value['process'] == 3) {
                echo '<td class="'.$class.'" style="line-height: 25px;"><font style="color: green; font-weight: bold;">Terminating stabilitytest</font></td>';
                echo '<td class="'.$class.'" style="line-height: 25px;"><input disabled="disabled" type="submit" name="start['.$id.']" value="start" /> <input disabled="disabled" type="text" style="width: 30px;" name="days_'.$id.'" value="14" /> (d)</td>';        
                echo '<td class="'.$class.'"><input disabled="disabled" type="text" style="width: 40px;" name="hv1_'.$id.'" value="'.$res['HV1'].'" /></td>';
                echo '<td class="'.$class.'"><input disabled="disabled" type="text" style="width: 40px;" name="hv2_'.$id.'" value="'.$res['HV2'].'" /></td>';
            }
            else if($value['process'] == 4) {
                echo '<td class="'.$class.'" style="line-height: 25px;"><font style="color: orange; font-weight: bold;">Suspended</font> &nbsp;&nbsp; <input type="submit" name="unsuspend['.$id.']" value="unsuspend" /></td>';
                echo '<td class="'.$class.'" style="line-height: 25px;"><input type="submit" name="stop['.$id.']" value="stop" /> <input disabled="disabled" type="text" style="width: 30px;" name="days_'.$id.'" value="'.($res['stoptest']-$res['starttest'])/(3600*24).'" /> (d)</td>';
                echo '<td class="'.$class.'"><input disabled="disabled" type="text" style="width: 40px;" name="hv1_'.$id.'" value="'.$res['HV1'].'" /></td>';
                echo '<td class="'.$class.'"><input disabled="disabled" type="text" style="width: 40px;" name="hv2_'.$id.'" value="'.$res['HV2'].'" /></td>';
            }
            else {
                echo '<td class="'.$class.'" style="line-height: 25px;">No test running</td>';
                echo '<td class="'.$class.'" style="line-height: 25px;"><input type="submit" name="start['.$id.']" value="start" /> <input type="text" style="width: 30px;" name="days_'.$id.'" value="14" /> (d)</td>';        
                echo '<td class="'.$class.'"><input type="text" style="width: 40px;" name="hv1_'.$id.'" value="6000" /></td>';
                echo '<td class="'.$class.'"><input type="text" style="width: 40px;" name="hv2_'.$id.'" value="9000" /></td>';
            }
            echo '</tr>';

            $i++;
        }
        ?>  
        </tbody>
    </table>
    </form>
    <br />When clicking on "stop", the detector under consideration will be turned off within 10 minutes and a low voltage will be set.    
    <h3>Log file</h3>
    <form onsubmit="return confirm('Do you really want to clear the log file?');" action="" method="post">
        <input type="submit" name="clearlog" value="Clear log file" />
    </form>
    <br />
    <div class="logfile" id="logFile">
        <table style="width: 100%">
            <tr>
                <td height="25px" width="120px"><b>Date</b></td><td><b>Error message</b></td>
            </tr>
        <?php
        $input = file("/home/user/data/tmp/".$mid."/stabilitytest.log");
        if($input) { 
            $rev_input = array_reverse($input);
            foreach ($rev_input as $line) {

                echo '<tr valign="top">';
                $t = explode(",", $line);
                $timestamp=  strtotime($t[0]);
                echo '<td>'.date('Y-m-d H:i',$timestamp).'</td><td>'.$t[1].'</td>';
                echo '</tr>';
            } 
        } 
        else {
            echo '<tr><td colspan="2">No log file available.</td></tr>';
        }
        ?>
        </table>
    </div>
</div>
