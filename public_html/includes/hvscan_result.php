<?php
if(!defined('INDEX')) die("Access denied");

// Get Scan ID and retrieve information
$id = $_GET['id'];
$idstring = sprintf("%06d", $id);
$sth1 = $dbh->prepare("SELECT * FROM hvscan WHERE id = $id");
$sth1->execute();
$hvscan = $sth1->fetch();
$scan_label = $hvscan["label"];

// Check if ID is valid
if($sth1->rowCount() == 0) {
    echo '<div class="content"><div class="error">Error: scan ID not found</div></div>';
    exit(1);
}

// Get scan type
if($hvscan['type'] == 'current') {
    $TYPESCAN = 'current';
    $TITLE = 'CURRENT';
}
elseif($hvscan['type'] == 'daq') {
    $TYPESCAN = 'daq';
    $TITLE = 'DAQ';
}
 
// Get selected trollys
$installedTrolleys = $hvscan['trolley'];
$installedTrolleys = explode(':', $installedTrolleys);
$trolleySQL = "AND ("; // prepare
foreach($installedTrolleys as $value) { 
    if(str_replace(" ", "", $value) == "") continue;
    $trolleySQL .= "trolley = '".$value."' OR ";
}
$trolleySQL .= "trolley = '".$installedTrolleys[count($installedTrolleys)-1]."'";
$trolleySQL .= ") ";


// Some vars...
$dir = sprintf("/var/operation/HVSCAN/%06d", $id);
$maxHV = $hvscan['maxHVPoints'];


// Get specific scan information
if($TYPESCAN == "daq") {
   
    $sth1 = $dbh->prepare("SELECT * FROM hvscan_DAQ WHERE id = $id");
    $sth1->execute();
    $hvscan_spec = $sth1->fetch();
    $scantype_spec = $hvscan_daq_types[$hvscan_spec['type']];
}
elseif($TYPESCAN == "current") {

    $sth1 = $dbh->prepare("SELECT * FROM hvscan_CURRENT WHERE id = $id");
    $sth1->execute();
    $hvscan_spec = $sth1->fetch();
    $scantype_spec = $hvscan_current_types[$hvscan_spec['type']];
}


// Construct array with TROLLEY-SLOT format
$sth1 = $dbh->prepare("SELECT trolley, slot FROM detectors d WHERE d.DAQ = 1 $trolleySQL GROUP BY trolley, slot ORDER by trolley, slot ASC");
$sth1->execute();
$chambers = $sth1->fetchAll();

// If single gap, get individual voltages
if($hvscan['RPC_mode'] == 'single_gap') {
    
    $voltages_single_gap = array();
    $sth1 = $dbh->prepare("SELECT v.*, d.trolley, d.slot, d.id, d.name FROM voltages v, detectors d WHERE hvscan_id = $id AND v.detector_id = d.id ORDER by trolley, slot ASC");
    $sth1->execute();
    $voltages_single_gap = $sth1->fetchAll();
}




if(isset($_POST['delete']) && getCurrentRole() != 0) {
    
    // Delete directories
    deleteDir($dir);
    
    // Remove from DB
    $sth1 = $dbh->prepare("DELETE FROM hvscan WHERE id = $id");
    $sth1->execute();
    $sth1 = $dbh->prepare("DELETE FROM hvscan_VOLTAGES WHERE scanid = $id");
    $sth1->execute();
    $sth1 = $dbh->prepare("DELETE FROM hvscan_DAQ WHERE id = $id");
    $sth1->execute();
    $sth1 = $dbh->prepare("DELETE FROM hvscan_CURRENT WHERE id = $id");
    $sth1->execute();
    
    header("Location: index.php?q=runregistry&type=".$TYPESCAN);
}

if(isset($_POST['approve']) && getCurrentRole() != 0) {

    $sth1 = $dbh->prepare("UPDATE hvscan SET status = 3 WHERE id = ".$id);
    $sth1->execute();
    header("Refresh:0");
}


if(isset($_POST['resume'])) { // STATUS = 4 = RESUME
    
    // Loop over all points
    for($i=1; $i<=$maxHV; $i++) {
        
        if(isset($_POST['HV'.$i])) {
        
            // Unmask the selected points
            $sth1 = $dbh->prepare("UPDATE hvscan_VOLTAGES SET masked = 0 WHERE HVPoint = $i AND scanid = ".$id);
            $sth1->execute();
            
            // Delete corresponding files
            shell_exec("rm /var/operation/HVSCAN/".$idstring."/*_HV".$i."_CAEN.root");
            shell_exec("rm /var/operation/HVSCAN/".$idstring."/*_HV".$i."_DIP.root");
            shell_exec("rm /var/operation/HVSCAN/".$idstring."/*_HV".$i."_DAQ.root");
            shell_exec("rm /var/operation/HVSCAN/".$idstring."/*_HV".$i."_DAQ-Rate.root");
            shell_exec("rm -rf /var/operation/HVSCAN/".$idstring."/HV".$i);            
        }
        
        else {
            
            // Mask the non-selected points
            $sth1 = $dbh->prepare("UPDATE hvscan_VOLTAGES SET masked = 1 WHERE HVPoint = $i AND scanid = ".$id);
            $sth1->execute();
        }
    }
    
    // Update status
    $sth1 = $dbh->prepare("UPDATE hvscan SET status = 4 WHERE id = ".$id);
    $sth1->execute();
    
    // Start scan  
    $pid = shell_exec("/home/webdcs/software/CAEN/webdcs/HVscan.sh ".$id);
    
    // Write pid process to db
    $t = $dbh->prepare("UPDATE modules SET hvscan_pid = :pid WHERE id = 1");
    $t->execute(array(':pid' => $pid));
    
    // Append line to log file
    $log = sprintf("%s.[WEBDCS] HVscan resumed by user (PID=%d)\n", date('Y-m-d.H.i.s'), $pid);
    $logfile = sprintf("/var/operation/HVSCAN/%06d/log.txt", $id);
    file_put_contents($logfile, $log, FILE_APPEND);

    
    header("Location: index.php?q=hvscan&type=current");
}

if(isset($_POST['download'])) { // STATUS = 4 = RESUME
    
    $filename = "HVSCAN_".$idstring.".tar.gz";
    $mime = "application/gzip.";

    ob_clean();
    //header('Content-Description: File Transfer');
    //header('Content-Type: application/octet-stream');
   // header('Content-Length: ' . filesize($f_location));
    //header('Content-Disposition: attachment; filename=' . $filename);

    //header("Content-Type: " . $mime);
    //header('Content-Disposition: attachment; filename="' . $filename . '"');
    $cmd = "tar -czv /var/operation/HVSCAN/".$idstring." .";
    echo $cmd;
    
    header('Content-Type: application/x-gzip');
    $content_disp = ( ereg('MSIE ([0-9].[0-9]{1,2})', $HTTP_USER_AGENT) == 'IE') ? 'inline' : 'attachment';
    header('Content-Disposition: ' . $content_disp . '; filename="'.$filename.'"');
    header('Pragma: no-cache');
    header('Expires: 0');

// create the gzipped tarfile.
//passthru( "tar cz /your/directory/here");

    passthru($cmd);
}


// Run entire DQM chain
if(isset($_POST['rundqm'])) {
    
    // Check if all ROOT files exist
    
    $dir = sprintf("/var/operation/HVSCAN/%06d", $id);
    
    $f1 = $dir.'/Offline-Rate.csv';
    $f2 = $dir.'/Offline-Current.csv';
    $f3 = $dir.'/Offline-DIP.csv';
    
    putenv("LD_LIBRARY_PATH=/home/webdcs/software/webdcs/CAEN/lib:/usr/local/root/lib");
    
    // Remove the csv files
    for($i = 0; $i < $hvscan['maxHVPoints']; $i++) {
        
        $f1 = $dir.'/Offline-Rate.csv';
        $f2 = $dir.'/Offline-Current.csv';
        $f3 = $dir.'/Offline-DIP.csv';
        shell_exec("rm ".$f1);
        shell_exec("rm ".$f2);
        shell_exec("rm ".$f3);
    }
  
    // Run DQM for all HV
    for($i = 0; $i < $hvscan['maxHVPoints']; $i++) {

       // $f1 = $dir.'/Offline-Rate.csv';
       // $f2 = $dir.'/Offline-Current.csv';
        
        $path = sprintf($dir."/*_HV".($i+1)."_*.root", $id);
        $files = glob($path);
        
        foreach($files as $file) { // Select DAQ file
            if(strpos($file, "_DAQ.root")) {
                $daqfile = $file;
                break;
            }
        }
        foreach($files as $file) { // Select CAEN file
            if(strpos($file, "_CAEN")) {
                $caenfile = $file;
                break;
            }
        }
        
        
        
        
        //bin/offlineanalysis /var/operation/HVSCAN/XXXXXX/ScanXXXX_HVX
        //$cmd = "/home/onanalysis/software/GIF_OfflineAnalysis/run.sh ".$daqfile." ".$caenfile;
        $filebasename = sprintf("/var/operation/HVSCAN/%06d/Scan%06d_HV%d", $id, $id, $i+1);
        $cmd = "cd /home/onanalysis/software/GIF_OfflineAnalysis && ./bin/offlineanalysis ".$filebasename;
        //$str = shell_exec($cmd);     
        echo $cmd.'<br />';
        exec($cmd, $t);
    }
    
    // Run the ONLINE analysis
    $cmd = "cd /home/onanalysis/software/GIF_Online/ && ./bin/onlineanalysis /var/operation/HVSCAN/".$idstring;
    echo $cmd.'<br />';
    $str = shell_exec($cmd);
 
    // check if everything is OK
    
    $pass = "DQM ok!";
}

if(isset($_POST['save'])) {
    
    $scantype_spec = filter_input(INPUT_POST, 'scantype_spec');
	$scan_label = filter_input(INPUT_POST, 'scan_label');
    $comments = filter_input(INPUT_POST, 'comments');
    
    $sth1 = $dbh->prepare("UPDATE hvscan SET comments = :comments, label = :label WHERE id = ".$id);
    $sth1->bindParam(':comments', htmlspecialchars($comments), PDO::PARAM_STR);
	$sth1->bindParam(':label', $scan_label, PDO::PARAM_STR);
    $sth1->execute();
    
    $sth1 = $dbh->prepare("UPDATE hvscan_".$TITLE." SET type = '".$scantype_spec."' WHERE id = ".$id);
    $sth1->execute();
    
    header("Refresh:0");
    
}

if(isset($_POST['viewplots'])) header("Location: index.php?q=offline&id=".$hvscan['id']);

$approved_disabled = ($hvscan["status"] == 2 || $hvscan["status"] == 1) ? 'disabled="disabled"' : '';
$delete_disabled = ($hvscan["status"] == 1) ? 'disabled="disabled"' : '';
$rundqm_disabled = "";

if($hvscan['status'] == 2) $resume = true;
else $resume = false;

//$pos = getTrolleyPositionFromId($hvscan['position']);


?>


<div class="content">
    
    <h3><?=$TITLE?> HVscan summary - ID <?php printf("%06d", $id); ?></h3>
    
    <?php 
    if(!empty($error)) { echo '<div class="error">Error: '.$error.'</div>'; }
    elseif($pass != '') { echo '<div class="pass">'.$pass.'</div>'; }
    ?>
    
    <b>Scan paramerters</b><br />
    
    <form action="" method="POST">
    <table style="margin-top: 5px;">
        
        <tr style="height: 25px;">
            <td valign="top" width="170px">Source configuration:</td>
            <td valign="top" width="200px" ><?php echo ($hvscan['source'] == 0) ? '<font color="red"><b>SOURCE OFF</b></font>' : '<font color="green"><b>SOURCE ON</b></font>'; ?></td>
            <td valign="top" width="150px">Status:</td>
            <td valign="top" width="150px">
            <?php 
            switch($hvscan['status']) {
                case '0' : echo '<font color="blue"><b>FINISHED</b></font>'; break;
                case '1' : echo '<font><b>ONGOING</b></font>'; break;
                case '2' : echo '<font color="red"><b>KILLED</b></font>'; break;
                case '3' : echo '<font color="green"><b>APPROVED</b></font>'; break;
                case '4' : echo '<font><b>RESUMED</b></font>'; break;
            }
            ?>
            </td>
        </tr> 
        
        <tr style="height: 25px;">
            <td valign="top">Attenuator configuration:</td>
            <td valign="top"><?php echo 'U'.$hvscan['attU'].' D '.$hvscan['attD'] ?></td>
            <td valign="top">Scan type:</td>
            <td valign="top">
            <?php 
            if(getCurrentRole() != 0) {
                
                if($TYPESCAN == 'current') $types_scan = $hvscan_current_types;
                elseif($TYPESCAN == 'daq') $types_scan = $hvscan_daq_types;
                 
                echo '<select name="scantype_spec">';
                foreach($types_scan as $key => $type) {
                    $sel = ($type == $scantype_spec) ? 'selected="selected"' : "";
                    echo '<option '.$sel.' value="'.$key.'">'.$type.'</option>';
                }
                echo '</select>';
            }
            else {
                echo $scantype_spec; 
            }
            ?>
            </td>
            
        </tr>
        
        <tr style="height: 25px;">
            <td valign="top">Beam configuration:</td>
            <td valign="top"><?php echo ($hvscan['beam'] == 0) ? '<font color="red"><b>BEAM OFF</b></font>' : '<font color="green"><b>BEAM ON</b></font>'; ?></td>
            <td valign="top">Scan start time:</td>
            <td valign="top"><?php echo date('Y-m-d H:i:s', $hvscan['time_start']) ?></td>
        </tr>
        
        <tr style="height: 25px;">
            <td valign="top">Waiting time (min):</td>
            <td valign="top"><?php echo $hvscan['waiting_time']; ?></td>
            <td valign="top">Scan end time:</td>
            <td valign="top"><?php echo ($hvscan['time_end'] == NULL) ? '-' : date('Y-m-d H:i:s', $hvscan['time_end']); ?></td>
        </tr>
        
        <tr style="height: 25px;">
            <td valign="top">Scanned trolleys:</td>
            <td valign="top">
            <?php 
            foreach($installedTrolleys as $value) { 
                if(str_replace(" ", "", $value) == "") continue;
                $pos = getTrolleyPosition($value, $hvscan['time_start']);
                echo 'Position trolley '.$value.': '.$position_mode[$pos['position']].' ('.$pos['id'].')<br />';
            }      
            ?>
            </td>
            <td valign="top">Measure interval </td>
            <td valign="top">every <?php echo $hvscan['measure_intval']; ?> seconds</td>
        </tr>
        
        <?php
        if($TYPESCAN == "daq") {
        ?>
        <tr style="height: 25px;">
            <td>Trigger modes:</td>
            <td>
                <?php
                foreach($trigger_modes as $key => $value) {
                    if(strpos($hvscan_spec['trigger_mode'], $key) != false) echo $value." ";
                }
                ?>
            </td>
            <td valign="top">Scan label:</td>
            <td valign="top">
            <?php 
            if(getCurrentRole() != 0) {
                
                echo '<select name="scan_label">';
                foreach($scan_labels as $key => $type) {
                    $sel = ($key == $scan_label) ? 'selected="selected"' : "";
                    echo '<option '.$sel.' value="'.$key.'">'.$type.'</option>';
                }
                echo '</select>';
            }
            else {
                echo $scan_label; 
            }
            ?>
            </td>
        </tr>
        <?php
        }
        elseif($TYPESCAN == "current") {
        ?>
        <tr style="height: 25px;">
            <td>Measuring time (min):</td>
            <td><?php echo $hvscan_spec['measure_time'] ?></td>
            <td></td>
            <td></td>
        </tr>
        <?php
        }
        ?>
        <tr style="height: 25px;">
            <td valign="top">Comments:</td>
            <td colspan="3">
            <?php 
            if(getCurrentRole() != 0) {
                echo '<textarea name="comments" cols="30" rows="3">'.$hvscan['comments'].'</textarea>';
            } 
            else {
                echo (trim($hvscan['comments']) == "") ? 'No comments' : nl2br($hvscan['comments']); 
            }
            ?>
            </td>
        </tr>

        <tr style="height: 25px;">
            <td>Actions:</td>
            <td colspan="3">
                
                <span style="float: left;">
                    <form action="" method="POST"><input disabled="disabled" <?php echo $download_disabled; ?> type="submit" name="download" value="Download files" /></form>
                </span>
                
                <span style="float: left; margin-left: 5px;">
                    <form action="" method="POST"><input <?php echo $viewplots_disabled; ?> type="submit" name="viewplots" value="View plots" /></form>
                </span>
                
                <?php
                if(getCurrentRole() != 0) {
                ?>
                
                <span style="float: left; margin-left: 5px;">
                    <form action="" method="POST" onsubmit="return confirm('Do you really want to approve this scan?');"> <input <?php echo $approved_disabled; ?> type="submit" name="approve" value="Approve scan" /></form>
                </span>
                
                <span style="float: left; margin-left: 5px;">
                    <form action="" method="POST" onsubmit="return confirm('Do you really want to delete this scan?');"><input <?php echo $delete_disabled; ?> type="submit" name="delete" value="Delete scan" /></form>
                </span>

                <span style="float: left; margin-left: 5px;">
                    <form action="" method="POST"><input <?php echo $rundqm_disabled; ?>  type="submit" name="rundqm" value="Run DQM" /></form>
                </span>
                
                <span style="float: left; margin-left: 5px;">
                   <input  type="submit" name="save" value="Save changes" />
                </span>

                
                <?php 
                }
                ?>
             </td>
        </tr>
    </form>
    </table>    
    
    <br />
   
    <form action="" method="POST">
    <b>Scan results</b><br /><br />
    <table cellspacing="0" cellpadding="5px">
        <thead>
            
            <?php
            $remaining = ($TYPESCAN == "daq") ? 900 - 100 - count($chambers)*60 - 220 - 30 : 900 - 100 - count($chambers)*60 -220 - 30;
            echo '<tr>';
            echo '<td class="oddrow" width="100px"><b>High Voltage</b></td>';
            foreach($chambers as $chamber) {

                echo '<td class="oddrow" width="60px"><b>T'.$chamber['trolley'].'_S'.$chamber['slot'].'</b></td>';
            }
            if($TYPESCAN == "daq") {
                echo '<td width="100px" class="oddrow"><b>Max triggers</b></td>';
            }
            echo '<td class="oddrow" width="'.$remaining.'"></td>';
            echo '<td class="oddrow" width="220x"><b>ROOT files</b></td>';
            echo '<td class="oddrow" width="30px"><b>DQM</b></td>';
            echo '</tr>';
            ?> 
         </tr></thead>
        <tbody>
        <?php
        for($i = 0; $i < $hvscan['maxHVPoints']; $i++) {
            
            // Get list of files containing current HV point
            $path = sprintf($dir."/*_HV".($i+1)."_*.root", $id);
            $files = glob($path);
            
            list($file, $ext) = explode(".", basename($files[$i]));
            
            $class = ($i%2 == 0) ? 'evenrow' : 'oddrow';
            echo '<tr class="'.$class.'">';
            
            if($hvscan['RPC_mode'] == 'double_gap') {
                if($resume) echo '<td><label><input type="checkbox" name="HV'.($i+1).'" />HV'.($i+1).' (eff)</label></td>';
                else echo '<td>HV'.($i+1).' (eff)</td>';
            }
            //else echo "<td>HV<sub>eff</sub> BOT<br />HV<sub>eff</sub> TW<br />HV<sub>eff</sub> BOT</td>";
            else echo '<td>HV<sub>eff</sub> BOT<br /> HV<sub>eff</sub> TOP/TN<br /> HV<sub>eff</sub> TW</td>';
            
            
            $j = 0;
            foreach($chambers as $chamber) {
            
                if($hvscan['RPC_mode'] == 'double_gap') {
                    echo '<td class="'.$class.'">'.getHV($chamber['trolley'], $chamber['slot'], $i+1, $id).'</td>';
                }
                else {
                    
                    // Check the amount of gaps for this slot
                    $a = $dbh->prepare("SELECT * FROM detectors WHERE trolley = ".$chamber['trolley']." AND slot = ".$chamber['slot']." ORDER BY name ASC ");
                    $a->execute();
                    $gaps = $a->fetchAll();
                    $noGaps = $a->rowCount();

                    
                    
                    echo '<td valign="top" class="'.$class.'">';
                    foreach($gaps as $gap) {
                        echo getHV($chamber['trolley'], $chamber['slot'], $i+1, $id, $gap['id']).'<br />';
                    }
                    echo '</td>';
                }
                $j++;
            }
     
            if($TYPESCAN == "daq") {
                echo '<td valign="top">'.getMaxTriggers($chamber['trolley'], $chamber['slot'], $i+1, $id).'</td>';
            }
            echo '<td width="'.$remaining.'"></td>';
            echo '<td valign="top">';
            foreach($files as $file) { // Select CAEN file
                if(strpos($file, "_CAEN")) {
                    $tmp = sprintf("/HVSCAN/%06d/%s", $id, basename($file));
                    echo '<a href="'.$tmp.'">CAEN</a> - ';
                }
            }
            foreach($files as $file) { // Select DIP file
                if(strpos($file, "_DIP")) {
                    $tmp = sprintf("/HVSCAN/%06d/%s", $id, basename($file));
                    echo '<a href="'.$tmp.'">DIP</a>';
                }
            }
            
            foreach($files as $file) { // Select DAQ file
                if(strpos($file, "_DAQ.root")) {
                    $tmp = sprintf("/HVSCAN/%06d/%s", $id, basename($file));
                    echo ' - <a href="'.$tmp.'">DAQ</a>';
                }
            }
            
            foreach($files as $file) { // Select DAQ RATE file
                if(strpos($file, "-Rate.root")) {
                    $tmp = sprintf("/HVSCAN/%06d/%s", $id, basename($file));
                    echo ' - <a href="'.$tmp.'">RATE</a>';
                }
            }
            
            
            echo '</td>';
            echo '<td valign="top"><a href="index.php?q=dqm&id='.$id.'&HV='.($i+1).'">View</a></td>';
            echo '</tr>';
        }
        
        $class = ($hvscan['maxHVPoints']%2 == 0) ? 'evenrow' : 'oddrow';
        if($TYPESCAN == "daq") {
            echo '<tr class="'.$class.'">';
            echo '<td>Thresholds</td>';
            foreach($voltages as $trolley) {

                echo '<td>'.$trolley['threshold'].'</td>';
            } 
            echo '<td width="<?php echo $remaining ?>"></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '</tr>';
        }
        
        ?>
        </tbody>
    </table>
    
    <?php
    if($resume) {
        echo '<br /><input type="submit" name="resume" value="Resume selected HV points" />';
    }
    ?>
    </form>
    
    <br />
    <b>Log file</b><br /><br />
    <div class="logfile">
        <?php
        $file = glob($dir."/log.txt"); // search for .log file
        echo '<pre>';
        $log = array_reverse(file($file[0]));
        foreach ($log as $line) {
            echo trim($line) . '<br />';
        }
        echo '</pre>';
        ?>
    </div>
    
</div>

<br /><br />

