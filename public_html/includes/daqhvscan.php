<?php

// Get maximum HV points
$maxHVPoints = settings("DAQ_HV_points");


// Get current trolley position
$trolleyPosition = getTrolleyPosition(1);


// Get current attenuator values from DIP
$sth1 = $dbh->prepare("SELECT * FROM DIP where id = '1'");
$sth1->execute();
$dip = $sth1->fetch();
$attU = $dip['attUA'].$dip['attUB'].$dip['attUC'];
$attD = $dip['attDA'].$dip['attDB'].$dip['attDC'];

// Check if daq hvscan is ongoing
$run = DAQHvscanOngoing();
$c = ($run) ? "stop" : "start";
//$run = true;

// Construct array with TROLLEY-SLOT format
$sth1 = $dbh->prepare("SELECT trolley, slot FROM detectors d WHERE d.DAQ = 1 GROUP BY trolley, slot ORDER by trolley, slot ASC");
$sth1->execute();
$chambers = $sth1->fetchAll();
$trolleys = array();
foreach($chambers as $chamber) array_push($trolleys, $chamber['trolley'].'-'.$chamber['slot']);

// Stora all detector IDs in array, detectors must have DAQ enabled!
$sth1 = $dbh->prepare("SELECT id FROM detectors WHERE DAQ = 1");
$sth1->execute();
$detector_ids = $sth1->fetchAll();
$detectors = array();
foreach($detector_ids as $det) array_push ($detectors, $det['id']);

// Get latest scan
$sth1 = $dbh->prepare("SELECT * FROM hvscan_daq WHERE RPC_mode = '".settings('DAQ_RPC_mode')."' ORDER BY id DESC LIMIT 1");
$sth1->execute();
$prevscan = $sth1->fetch();

if(str_replace(" ", "", settings("DAQ_HV_template_ID")) != "") {
    $template_HV_ID = settings("DAQ_HV_template_ID");
}
else $template_HV_ID = $prevscan['id'];




// Function: get trolley-slot for a given detector ID
function getTrolleySlot($id) {
    
    global $dbh;
    $sth1 = $dbh->prepare("SELECT trolley, slot FROM detectors where id = $id");
    $sth1->execute();
    $res = $sth1->fetch();
    return $res['trolley'].'-'.$res['slot'];
}




if(filter_input(INPUT_POST, 'startscan') != NULL) {

    $scantype  = filter_input(INPUT_POST, 'scantype');
    $source = filter_input(INPUT_POST, 'source');
    $beam = filter_input(INPUT_POST, 'beam');
    $maxtriggers = filter_input(INPUT_POST, 'maxtriggers');
    $attU = filter_input(INPUT_POST, 'attU');
    $attD = filter_input(INPUT_POST, 'attD');
    //$waiting_time = filter_input(INPUT_POST, 'waiting_time');
    $waiting_time = settings("DAQ_waiting_time");
    //$position = filter_input(INPUT_POST, 'position');
    $comments = filter_input(INPUT_POST, 'comments');
    $thresholds = $_POST['thresholds'];
    //$partition = $_POST['partition'];
    
    $position = getTrolleyPosition(1); // Get current trolley position
    $position = 
    
    // Read trigger mode
    $triggers = "";
    foreach($trigger_modes as $key => $value) {
        
        if(isset($_POST[$key])) $triggers .= ':'.$key;
    }
    
    // Make array: key = trolley-slot, value = threshold
    // This is based on the order of the trolleys
    $ths = array();
    $i=0;
    foreach($trolleys as $trolley) {
        
        $ths[$trolley] = $thresholds[$i];
        $i++;
    }
    
    // Set voltages: depends on double or single gap
   
    $voltages = array(); // index = detector id, value = array of voltages
    
    // Loop over all trolleys
    foreach($trolleys as $trolley) {
       
        list($t, $s) = explode("-", $trolley); // get trolley and slot number
        
        // Select all gaps in current trolley and slot
        $sth1 = $dbh->prepare("SELECT * FROM detectors WHERE trolley = $t AND slot = $s");
        $sth1->execute();
        $gaps = $sth1->fetchAll();
        
        if(settings('DAQ_RPC_mode') == 'double_gap') {
            
            $post = $_POST['HV-'.$trolley]; // array of voltages per trolley-slot 
            
            foreach($post as $HV) if(str_replace(" ", "", $HV) == '' || !is_numeric($HV) || $HV > 11000 || $HV < 0) $HVerror = true;
        
            // Loop over all the gaps in current trolley-slot
            foreach($gaps as $gap) $voltages[$gap['id']] = $post;    
        }
        else {
            
            $post1 = $_POST['HV-TN-'.$trolley]; // array of TN voltages per trolley-slot 
            $post2 = $_POST['HV-TW-'.$trolley]; // array of TW voltages per trolley-slot 
            $post3 = $_POST['HV-BOT-'.$trolley]; // array of BOT voltages per trolley-slot 
            
            foreach($post1 as $HVTN)  if(str_replace(" ", "", $HVTN)  == '' || !is_numeric($HVTN)  || $HVTN > 11000  || $HVTN < 0) $HVerror = true;
            foreach($post2 as $HVTW)  if(str_replace(" ", "", $HVTW)  == '' || !is_numeric($HVTW)  || $HVTW > 11000  || $HVTW < 0) $HVerror = true;
            foreach($post3 as $HVBOT) if(str_replace(" ", "", $HVBOT) == '' || !is_numeric($HVBOT) || $HVBOT > 11000 || $HVBOT < 0) $HVerror = true;
        
            // Loop over all the gaps in current trolley-slot
            foreach($gaps as $gap) {
                
                // Find bottom gap
                if(strpos($gap['name'], "BOT")) $voltages[$gap['id']] = $post3;
                elseif(strpos($gap['name'], "TN")) $voltages[$gap['id']] = $post1;
                elseif(strpos($gap['name'], "TW")) $voltages[$gap['id']] = $post2;
            }       
        } 
    }

    // Validate input
    if($attU == '' OR $attD == '' OR $waiting_time == '') {
        $error = "please fill in all fields.";
    }
    //elseif(!is_numeric($start1) OR !is_numeric($step1) OR !is_numeric($stop1) OR !is_numeric($step2) OR !is_numeric($stop2) OR !is_numeric($step3) OR !is_numeric($mtime) OR !is_numeric($wtime) OR !is_numeric($step)) {
    //    $error = "all fields must be numeric.";
    //}
    
    else {
   
        // Generate scan ID
        $q = $dbh->prepare("SELECT * FROM hvscan_daq ORDER BY id DESC LIMIT 0, 1");
        $q->execute();
        $f = $q->fetch();
        $id = $f['id'] + 1;
        $now = time();
        $log = sprintf("%s.[WEBDCS] HVscan started by user (ID: %d)\n", date('Y-m-d.h.i.s'), $id);

        // Write HVscan profile to database
        $sth1 = $dbh->prepare("INSERT INTO hvscan_daq (id, time_start, scantype, maxtriggers, beam, source, attU, attD, waiting_time, position, comments, maxHVPoints, status, RPC_mode, approved, trigger_mode, log)
                              VALUES (:id, :time_start, :scantype, :maxtriggers, :beam, :source, :attU, :attD, :waiting_time, :position, :comments, :maxHVPoints, 1, :RPC_mode, 0, :trigger_mode, :log) "); 
        $sth1->bindParam(':id', $id); 
        $sth1->bindParam(':time_start', $now);
        $sth1->bindParam(':scantype', $scantype, PDO::PARAM_STR); 
        $sth1->bindParam(':maxtriggers', $maxtriggers);
        $sth1->bindParam(':beam', $beam); 
        $sth1->bindParam(':source', $source); 
        $sth1->bindParam(':attU', $attU); 
        $sth1->bindParam(':attD', $attD); 
        $sth1->bindParam(':waiting_time', $waiting_time); 
        $sth1->bindParam(':position', $trolleyPosition['id']); 
        //$sth1->bindParam(':partition', $partition, PDO::PARAM_STR); 
        $sth1->bindParam(':comments', htmlspecialchars($comments), PDO::PARAM_STR); 
        $sth1->bindParam(':trigger_mode', $triggers, PDO::PARAM_STR); 
        $sth1->bindParam(':maxHVPoints', $maxHVPoints);
        $sth1->bindParam(':RPC_mode', settings('DAQ_RPC_mode'));
        $sth1->bindParam(':log', $log, PDO::PARAM_STR);
        
        
        if(!$sth1->execute()) $error = "Error: failed to submit to the database.";
        else {
            
            // Add record to voltages table
            // Loop over all detectors..
            //print_r($voltages);
            foreach($voltages as $key => $HV) {
                
                $trolley = getTrolleySlot($key);
                
                $sth1 = $dbh->prepare("INSERT INTO voltages (hvscan_id, detector_id, HV1, HV2, HV3, HV4, HV5, HV6, HV7, HV8, HV9, HV10, HV11, HV12, HV13, HV14, HV15, HV16, HV17, HV18, HV19, HV20, threshold)
                              VALUES (:hvscan_id, :detector_id, :HV1, :HV2, :HV3, :HV4, :HV5, :HV6, :HV7, :HV8, :HV9, :HV10, :HV11, :HV12, :HV13, :HV14, :HV15, :HV16, :HV17, :HV18, :HV19, :HV20, :th) "); 
                $sth1->bindParam(':hvscan_id', $id); 
                $sth1->bindParam(':detector_id', $key);
                
                $sth1->bindParam(':HV1', array_key_exists(0, $HV) ? $HV[0] : null);
                $sth1->bindParam(':HV2', array_key_exists(1, $HV) ? $HV[1] : null);
                $sth1->bindParam(':HV3', array_key_exists(2, $HV) ? $HV[2] : null);
                $sth1->bindParam(':HV4', array_key_exists(3, $HV) ? $HV[3] : null);
                $sth1->bindParam(':HV5', array_key_exists(4, $HV) ? $HV[4] : null);
                $sth1->bindParam(':HV6', array_key_exists(5, $HV) ? $HV[5] : null);
                $sth1->bindParam(':HV7', array_key_exists(6, $HV) ? $HV[6] : null);
                $sth1->bindParam(':HV8', array_key_exists(7, $HV) ? $HV[7] : null);
                $sth1->bindParam(':HV9', array_key_exists(8, $HV) ? $HV[8] : null);
                $sth1->bindParam(':HV10', array_key_exists(9, $HV) ? $HV[9] : null);
                $sth1->bindParam(':HV11', array_key_exists(10, $HV) ? $HV[10] : null);
                $sth1->bindParam(':HV12', array_key_exists(11, $HV) ? $HV[11] : null);
                $sth1->bindParam(':HV13', array_key_exists(12, $HV) ? $HV[12] : null);
                $sth1->bindParam(':HV14', array_key_exists(13, $HV) ? $HV[13] : null);
                $sth1->bindParam(':HV15', array_key_exists(14, $HV) ? $HV[14] : null);
                $sth1->bindParam(':HV16', array_key_exists(15, $HV) ? $HV[15] : null);
                $sth1->bindParam(':HV17', array_key_exists(16, $HV) ? $HV[16] : null);
                $sth1->bindParam(':HV18', array_key_exists(17, $HV) ? $HV[17] : null);
                $sth1->bindParam(':HV19', array_key_exists(18, $HV) ? $HV[18] : null);
                $sth1->bindParam(':HV20', array_key_exists(19, $HV) ? $HV[19] : null);
                $sth1->bindParam(':th', $ths[$trolley]); 
                
                if(!$sth1->execute()) $error = "Error: failed to submit to the database (1)."; 
            }


            // Open template file and fill it
            $inputfile = "";
            $handle = fopen("/mnt/nfs/daq_data/DAQ_RUN/daqgifpp.ini.template", "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    
                    if(strstr($line, "\$runtype")) $inputfile .= str_replace ("\$runtype", $scantype, $line);
                    elseif(strstr($line, "\$scanid")) $inputfile .= str_replace ("\$scanid", $id, $line);
                    elseif(strstr($line, "\$maxtriggers")) $inputfile .= str_replace ("\$maxtriggers", $maxtriggers, $line);
                    elseif(strstr($line, "\$beam")) {
                        if($beam == 0) $tmp = "OFF";
                        else $tmp = "ON";
                        $inputfile .= str_replace ("\$beam", $tmp, $line);
                    }
                    elseif(strstr($line, "\$source")) {
                        if($source == 0) $tmp = "OFF";
                        else $tmp = "ON";
                        $inputfile .= str_replace ("\$source", $tmp, $line);
                    }
                    elseif(strstr($line, "\$attu")) $inputfile .= str_replace ("\$attu", $attU, $line);
                    elseif(strstr($line, "\$attd")) $inputfile .= str_replace ("\$attd", $attD, $line);
                    elseif(strstr($line, "\$thresholds")) {
                        
                        $k = 0;
                        $thresholds = "";
                        foreach($ths as $key => $th) {

                            $ret = ($k != count($ths)-1) ? "\n" : "";
                            $thresholds .= "TH_".$key."=".$th.$ret;
                            $k++;
                        }
                        $inputfile .= str_replace("\$thresholds", $thresholds, $line);
                    }
                    
                    else $inputfile .= $line;
                }
                fclose($handle);
                
                // Write new DAQ input file
                file_put_contents("/mnt/nfs/daq_data/DAQ_RUN/daqgifpp.ini", $inputfile);
                
            } else {
                echo "failed";
                $error = "Error: cannot opend the DAQ input template file.";
            }
            
       
            
            /*
            echo 'Current script owner: ' . get_current_user();
            //echo shell_exec('whoami');
            echo '<br />';
            echo '<pre>'.shell_exec('ls -l').'</pre>';
           // echo shell_exec("/var/www/software/CAEN/test");
            //shell_exec('/var/www/webdcs/software/CAEN/HVscan_DAQ 1 2>&1');
            //echo shell_exec("ls -l /var/www/software/CAEN ");
            exec('./test 2>&1', $output, $return_var);
            var_dump($output, $return_var);
            //echo shell_exec($config['CAEN_dir']."HVscan_DAQ 1");
             */
            
            //$pid = shell_exec($config['CAEN_dir']."HVscan_DAQ ".$id." > /dev/null 2>/dev/null & echo $!");
            $pid = shell_exec($config['CAEN_dir']."HVscan_DAQ ".$id." > WEBDCS_DAQ".$id.".log & echo $!");
            echo $pid;
            // Write pid process to db
            $t = $dbh->prepare("UPDATE modules SET hvscan_pid = :pid WHERE id = 1");
            $t->execute(array(':pid' => $pid));
            header("Location: index.php?q=daqhvscan");
          
        }
    }
    
}
elseif($_POST['stopscan']) {
    
    $killed_comment = $_POST['killed_comment'];
    if(str_replace(" ", "", $killed_comment) == "") {
        echo '<div class="content"><div class="error">Error: please enter a reason to stop the HVscan</div></div>';
    }
    else {
   
    // Get HVscan pid process for selected mainframe (remember, only one HVscan per mainframe)
    $t = $dbh->prepare("SELECT hvscan_pid FROM modules WHERE id = 1");
    $t->execute();
    $pid = $t->fetch();
    
    // Kill WEBDCS process 
    exec("kill ".$pid[0], $out);
    
    // send KILL to DAQ
    file_put_contents("/mnt/nfs/daq_data/DAQ_RUN/run", "KILL");
    
    // Add KILL reason to LOG file
    $log = sprintf("%s.[WEBDCS] HVscan killed by user. Reason: %s\n", date('Y-m-d.h.i.s'), $killed_comment);
    file_put_contents("/mnt/nfs/daq_data/DAQ_RUN/log", $log, FILE_APPEND);
    
    // Update database
    $end = time();
    $t = $dbh->prepare("UPDATE hvscan_daq SET log = CONCAT(log, LOAD_FILE('/mnt/nfs/daq_data/DAQ_RUN/log')), status = 2, time_end = $end WHERE id = ".$prevscan['id']);
    $t->execute();
 
    
    // Power down detectors (standby)
    
    //$command = $config['exe_dir'].'HVscanStop '.$mid;
    //exec($command, $output, $value); // Clean up database and tmp files
    //if($output[0] == '0') $pass = 'HVscan stopped.';
    //else $error = 'Error while stopping HVscan: '.$output[0].'<br />Please contact the software administrator and report this error.';

    }  
   
}




// Select voltages
if(settings('DAQ_RPC_mode') == 'double_gap') {
    
    $sth1 = $dbh->prepare("SELECT * FROM detectors d, voltages v WHERE v.hvscan_id = ".$template_HV_ID." AND v.detector_id = d.id GROUP BY d.trolley, d.slot ORDER BY trolley, slot");
    $sth1->execute();
    $prevvoltages = $sth1->fetchAll();
    
}



?>

<script>
function killScan() {

    var reason = prompt("Reason for kill the scan:", "");
    document.getElementById("killed_comment").value = reason;
}
</script>

<div class="content">
    
    <div style="display: inline">
        <h3 style="display: inline;">DAQ High Voltage Scan</h3>
    </div>
    <br /><br />
  
    <?php 
  
    if(!empty($error)) { echo '<div class="error">Error: '.$error.'</div>'; }
    elseif($pass != '') { echo '<div class="pass">'.$pass.'</div>'; }
    
    $disabled = ($run) ? 'disabled="disabled"' : '';
    
    
    if(checkDIP()) {
        
        echo '<div class="error">DIP ERROR: no response since 5 min</div>';
    }
    //
    
    ?>
    
    <!--<div class="error">JAN: Please do not start a scan, I'm investigating the error.</div>-->
    
    <form method="POST" action="" id="hvscan-form" <?php if(!$run) echo 'onsubmit="return confirm(\'Do you really want to start this scan?\');"'; ?>>
    <table>
        <tr>
            <td width="130px" style="height: 30px;">Type scan:</td>
            <td>
                <select <?php echo $disabled; ?> name="scantype" id="scantype">
                    <?php
                    foreach($hvscan_daq_types as $key => $type) {
                        $sel = ($prevscan['scantype'] == $key) ? 'selected="selected"' : "";
                        echo '<option '.$sel.' value="'.$key.'">'.$type.'</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td style="height: 30px;">Source configuration:</td>
            <td>
                <select <?php echo $disabled; ?> name="source" id="source">
                    <option value="0">Source OFF</option>
                    <option <?php echo ($prevscan['source'] == 1) ? 'selected="selected"' : ""; ?> value="1">Source ON</option>
                </select>
                &nbsp;&nbsp;&nbsp;U <input size="3" <?php echo $disabled; ?> name="attU" id="attU" type="text" value="<?php echo $attU; ?>" maxlength="3" /> &nbsp;&nbsp;&nbsp;D <input size="3" <?php echo $disabled; ?> name="attD" id="attD" type="text" value="<?php echo $attD; ?>" maxlength="3" />
            </td>
        </tr>
        <tr>
            <td style="height: 30px;">Beam configuration:</td>
            <td>
                <select <?php echo $disabled; ?> name="beam" id="beam">
                    <option value="0">Beam OFF</option>
                    <option <?php echo ($prevscan['beam'] == 1) ? 'selected="selected"' : ""; ?> value="1">Beam ON</option>
                </select>
            </td>
        </tr>
        <tr>
            <td style="height: 30px;">Maximum triggers:</td>
            <td><input size="10" <?php echo $disabled; ?> name="maxtriggers" type="text" value="<?php echo $prevscan['maxtriggers'] ?>" /></td>
        </tr>
        <tr>
            <td style="height: 30px;">Trigger mode:</td>
            <td>
                <?php
                foreach($trigger_modes as $key => $value) {
                    echo '<input '.$disabled.' style="width: 15px" type=checkbox name="'.$key.'" /> '.$value.'&nbsp;&nbsp;&nbsp;';
                }
                ?>
            </td>
        </tr>
        <tr>
            <td>Waiting time* [min]:</td>
            <td><input size="10" disabled="disabled" name="waiting_time" type="text" value="<?php echo settings("DAQ_waiting_time"); ?>"/></td> 
        </tr>
        <tr>
            <td style="height: 30px;">Trolley position:</td>
            <td><input size="10" disabled="disabled" name="position" type="text" value="<?php echo $position_mode[$trolleyPosition['position']]; ?>" /></td>
        </tr>
        <!--
        <tr>
            <td style="height: 30px;">Partition position:</td>
            <td><select id="partition" name="partition" <?php //echo $disabled; ?>>
                <option id="partitionNone" value="none">Not applicable</option>
                <option <?php //echo ($prevscan['scanned_partition'] == "A") ? 'selected="selected"' : ""; ?> id="partitionA" value="A">Partition A</option>
                <option <?php //echo ($prevscan['scanned_partition'] == "B") ? 'selected="selected"' : ""; ?> id="partitionB" value="B">Partition B</option>
                <option <?php //echo ($prevscan['scanned_partition'] == "C") ? 'selected="selected"' : ""; ?> id="partitionC" value="C">Partition C</option>
            </select></td>
        </tr>
        -->
        <tr>
            <td style="height: 30px;">Comments:</td>
            <td><textarea <?php echo $disabled; ?> name="comments" cols="30" rows="2"></textarea></td>
        </tr>
    </table>
    <br /> 
    <table class="" cellpadding="5px" cellspacing="0">
        <?php
        $tot = count($voltages);
        $remaining = 800 - 130 - count($chambers)*100;
        
        echo '<tr valign="middle" style="height: 25px;">';
        echo '<td class="oddrow" width="140px"></td>';
        foreach($chambers as $chamber) {
            
            echo '<td class="oddrow" width="100px"><b>T'.$chamber['trolley'].'_S'.$chamber['slot'].'</b></td>';
        }
        echo '<td class="oddrow" width="'.$remaining.'"></td>';
        echo '</tr>';
        
        $single_gap = (settings('DAQ_RPC_mode') == 'single_gap') ? '(TN/TW/BOT)' : '';
        
        
        for($i=0; $i<$maxHVPoints; $i++) {
            
            $class = ($i%2 == 0) ? 'evenrow' : 'oddrow';
            echo '<tr class="HVline">';
            echo '<td style="vertical-align: middle" class="'.$class.'">';
                if(settings('DAQ_RPC_mode') == 'double_gap') echo 'HV<sub>eff</sub> '.($i+1);
                else echo 'HV<sub>eff</sub> TN<br /> HV<sub>eff</sub> TW<br /> HV<sub>eff</sub> BOT';
            echo '</td>';
            $j = 0;
            foreach($chambers as $chamber) {
            
                if(settings('DAQ_RPC_mode') == 'double_gap') {
                    echo '<td class="'.$class.'"><input '.$disabled.' size="4" name="HV-'.$chamber['trolley'].'-'.$chamber['slot'].'[]" type="text" value="'.$prevvoltages[$j]['HV'.($i+1)].'"/></td>';
                }
                else {
                    echo '<td class="'.$class.'">';
                    echo '<input '.$disabled.' size="4" name="HV-TN-'.$chamber['trolley'].'-'.$chamber['slot'].'[]" type="text" value="'.settings("standby_voltage").'"/><br />';
                    echo '<input '.$disabled.' size="4" name="HV-TW-'.$chamber['trolley'].'-'.$chamber['slot'].'[]" type="text" value="'.settings("standby_voltage").'"/><br />';
                    echo '<input '.$disabled.' size="4" name="HV-BOT-'.$chamber['trolley'].'-'.$chamber['slot'].'[]" type="text" value="'.settings("standby_voltage").'"/>';
                    echo '</td>';
                }
                $j++;
            }
            echo '<td class="'.$class.'" width="'.$remaining.'"></td>';
            echo '</tr>';
        }
        
        $class = ($i%2 == 0) ? 'evenrow' : 'oddrow';
        echo '<tr style="height: 20px;">';
        echo '<td class="'.$class.'" width="140px">Thresholds</td>';
        foreach($chambers as $chamber) {
            
            echo '<td class="'.$class.'" width="100px"><input '.$disabled.' size="4" name="thresholds[]" type="text" value="220"/></td>';
        }
        echo '<td class="'.$class.'" width="'.$remaining.'"></td>';
        echo '</tr>';
        ?>
    </table>     
    <br />

    <?php
    if(!$run) {
        echo '<input type="submit" name="startscan" value="Start DAQ HV scan" />';
    }
    ?>
    </form>  
    
    <?php
    if($run) {
        
        echo '<form id="killScan_form" method="POST" action="" onsubmit="killScan()">';
        echo '<input type="hidden" name="killed_comment" id="killed_comment" value="" />';
        echo '<input type="submit" name="stopscan" value="Stop DAQ HV scan" />';
        echo '</form>';
    }
    
    
    
    if($run) $title = "HVscan log file";
    else $title = "Log file previous HVscan";
    ?>
    
    <br /><br />
    
    <div style="display: inline">
        <h3 style="display: inline;"><?php echo $title; ?></h3>
    </div>
    <br /><br />
    <script type="text/javascript"> $(document).ready(function() { readDAQLogFile(); }); </script> 
    <div class="logfile" id="logFile">Log file</div>

</div>

<br /><br />
