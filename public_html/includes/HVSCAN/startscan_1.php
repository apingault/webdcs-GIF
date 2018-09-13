<?php
if(!defined('INDEX')) die("Access denied");

// Get scan type
if($_GET['type'] == 'current') {
    $TYPESCAN = 'current';
    $TITLE = 'CURRENT';
}
else {
    $TYPESCAN = 'daq';
    $TITLE = 'DAQ';
}


// Get selected trollys
$installedTrolleys = settings("ACTIVE_TROLLEYS");
$installedTrolleys = explode(':', $installedTrolleys);
$trolleySQL = "AND ("; // prepare
foreach($installedTrolleys as $value) { 
    if(str_replace(" ", "", $value) == "") continue;
    $trolleySQL .= "trolley = '".$value."' OR ";
}
$trolleySQL .= "trolley = '".$installedTrolleys[count($installedTrolleys)-1]."'";
$trolleySQL .= ") ";

// Get maximum HV points
$maxHVPoints = settings($TITLE."_HV_points");

// Get profile
$template_HV_ID = settings($TITLE."_HV_template_ID");


// Get current attenuator values from DIP
global $dbhDIP;
$sth1 = $dbhDIP->prepare("SELECT * FROM attenuator ORDER BY timestamp DESC LIMIT 1");
$sth1->execute();
$dip = $sth1->fetch();
$attU = $dip['AttUA'].$dip['AttUB'].$dip['AttUC'];
$attD = $dip['AttDA'].$dip['AttDB'].$dip['AttDC'];

$sth1 = $dbhDIP->prepare("SELECT * FROM source ORDER BY timestamp DESC LIMIT 1");
$sth1->execute();
$dip = $sth1->fetch();
$source = $dip['SourceON'];



// Check if daq hvscan is ongoing
$run = HVscanOngoing();
if($run != -1) { // ongoing
    $c = "stop";
    $template_HV_ID = $run;
}
else $c = "start";


// Construct array with TROLLEY-SLOT format     d.DAQ = 1 
$sth1 = $dbh->prepare("SELECT trolley, slot, chamber FROM detectors d WHERE d.enabled = 1 $trolleySQL GROUP BY trolley, slot ORDER by trolley, slot ASC");
$sth1->execute();
$chambers = $sth1->fetchAll();
$trolleys = array();
foreach($chambers as $chamber) array_push($trolleys, $chamber['trolley'].'-'.$chamber['slot']);

// Stora all detector IDs in array, detectors must have DAQ enabled!
$sth1 = $dbh->prepare("SELECT id FROM detectors");
$sth1->execute();
$detector_ids = $sth1->fetchAll();
$detectors = array();
foreach($detector_ids as $det) array_push ($detectors, $det['id']);



// Function: get trolley-slot for a given detector ID
 /*
function getTrolleySlot($id) {
    
    global $dbh;
    $sth1 = $dbh->prepare("SELECT trolley, slot FROM detectors where id = $id");
    $sth1->execute();
    $res = $sth1->fetch();
    return $res['trolley'].'-'.$res['slot'];
}
*/

if(filter_input(INPUT_POST, 'startscan') != NULL) {	

    $scantype = filter_input(INPUT_POST, 'scantype'); // Both for DAQ and CURRENT scans
    $source = filter_input(INPUT_POST, 'source');
    $attU = filter_input(INPUT_POST, 'attU');
    $attD = filter_input(INPUT_POST, 'attD');
    $comments = filter_input(INPUT_POST, 'comments');
    $beam = filter_input(INPUT_POST, 'beam');
    $waiting_time = filter_input(INPUT_POST, 'waiting_time');
    $measure_time = filter_input(INPUT_POST, 'measure_time');
    $last_HV = filter_input(INPUT_POST, 'last_HV');
    
    if($TYPESCAN == "daq") {
        
        // Read maxtriggers
        $maxtriggers = array();
        foreach($_POST['maxtriggers'] as $value) {
            
            array_push($maxtriggers, $value);
        }  
		
        // Read trigger mode
        $triggers = "";
        foreach($trigger_modes as $key => $value) {

            if(isset($_POST[$key])) $triggers .= ':'.$key;
        }
  

    }
    else {
    }

    // Set voltages: depends on double or single gap
    $voltages = array(); // index = detector id, value = array of voltages
    foreach($trolleys as $trolley) {
       
        list($t, $s) = explode("-", $trolley); // get trolley and slot number
        
        // Skip the ones which are not selected
        if(!isset($_POST['ON-'.$t.'-'.$s])) continue;
        
        // Select all gaps in current trolley and slot
        $sth1 = $dbh->prepare("SELECT * FROM detectors WHERE trolley = $t AND slot = $s");
        $sth1->execute();
        $gaps = $sth1->fetchAll();
        
        if(settings('RPC_mode') == 'double_gap') {
            
            $post = $_POST['HV-'.$trolley]; // array of voltages per trolley-slot 
            
            // Validate HVs
            foreach($post as $HV) if(str_replace(" ", "", $HV) == '' || !is_numeric($HV) || $HV > 11000 || $HV < 0) $HVerror = true;

            // Loop over all the gaps in current trolley-slot
            foreach($gaps as $gap) $voltages[$gap['id']] = $post;    
        }
        else {
            
            // Loop over all the gaps in current trolley-slot
            foreach($gaps as $gap) {
                $voltages[$gap['id']] = $_POST['DETID'.$gap['id']];
            }       
        } 
    }


    // Validate input
    if($attU == '' OR $attD == '') {
        $error = "please fill in all fields.";
    }
    //elseif(!is_numeric($start1) OR !is_numeric($step1) OR !is_numeric($stop1) OR !is_numeric($step2) OR !is_numeric($stop2) OR !is_numeric($step3) OR !is_numeric($mtime) OR !is_numeric($wtime) OR !is_numeric($step)) {
    //    $error = "all fields must be numeric.";
    //}
    
    else {

        // Generate scan ID
        $q = $dbh->prepare("SELECT * FROM hvscan ORDER BY id DESC LIMIT 0, 1");
        $q->execute();
        $f = $q->fetch();
        $id = $f['id'] + 1;
        $now = time();

        // Write HVscan profile to database (generic)
        $sth1 = $dbh->prepare("INSERT INTO hvscan (id,  trolley,  time_start,  type,  beam,  source,  attU,  attD,  waiting_time,  comments,  maxHVPoints, status, RPC_mode,  measure_intval,  measure_time,  lastHV)
                              VALUES (            :id, :trolley, :time_start, :type, :beam, :source, :attU, :attD, :waiting_time, :comments, :maxHVPoints, 1,     :RPC_mode, :measure_intval, :measure_time, :lastHV) "); 
        $sth1->bindParam(':id', $id); 
        $sth1->bindParam(':trolley', settings("ACTIVE_TROLLEYS")); 
        $sth1->bindParam(':type', $TYPESCAN); 
        $sth1->bindParam(':time_start', $now);
        $sth1->bindParam(':beam', $beam); 
        $sth1->bindParam(':source', $source); 
        $sth1->bindParam(':attU', $attU); 
        $sth1->bindParam(':attD', $attD); 
        $sth1->bindParam(':waiting_time', $waiting_time); 
        $sth1->bindParam(':measure_time', $measure_time); 
        $sth1->bindParam(':comments', htmlspecialchars($comments), PDO::PARAM_STR); 
        $sth1->bindParam(':maxHVPoints', $maxHVPoints);
        $sth1->bindParam(':measure_intval', settings("measuring_intval"));
        $sth1->bindParam(':RPC_mode', settings('RPC_mode'));
        $sth1->bindParam(':lastHV', $last_HV);
        
        // Write HVscan profile to database (specific)
        if($TYPESCAN == 'current') {
            $sth2 = $dbh->prepare("INSERT INTO hvscan_CURRENT (id, type) VALUES (:id, :type) ");
            $sth2->bindParam(':id', $id);
            $sth2->bindParam(':type', $scantype);
        }
        else {
            $sth2 = $dbh->prepare("INSERT INTO hvscan_DAQ (id, type, trigger_mode) VALUES (:id, :type, :trigger_mode) ");
            $sth2->bindParam(':id', $id);
            $sth2->bindParam(':type', $scantype);
            $sth2->bindParam(':trigger_mode', $triggers);
        }
        
        if(!$sth1->execute() || !$sth2->execute()) {
            print_r($sth1->errorInfo());
            print_r($sth2->errorInfo());
            $error = "Error: failed to submit to the database.";
        }
        else {
            
            // Add record to voltages table
            foreach($voltages as $key => $HV) { // Loop over all gaps
				
                foreach($HV as $rr => $v) {
					
                    $HVPoint = (int)$rr+1;
                    $t = $maxtriggers[$rr];
					
                    $sth1 = $dbh->prepare("INSERT INTO hvscan_VOLTAGES (scanid, detectorid, HVPoint, HV, maxtriggers) VALUES (:scanid, :detectorid, :HVPoint, :HV, :maxtriggers) "); 
                    $sth1->bindParam(':scanid', $id); 
                    $sth1->bindParam(':detectorid', $key);
                    $sth1->bindParam(':HVPoint', $HVPoint);
                    $sth1->bindParam(':HV', $v);
                    $sth1->bindParam(':maxtriggers', $t);
                    if(!$sth1->execute()) $error = "Error: failed to submit to the database (1)."; 
                }
            }

            /*
            $pid = shell_exec("/home/webdcs/software/CAEN/webdcs/HVscan.sh ".$id);
            // Write pid process to db
            $t = $dbh->prepare("UPDATE modules SET hvscan_pid = :pid WHERE id = 1");
            $t->execute(array(':pid' => $pid));
            
            // Append line to log file
            $log = sprintf("%s.[WEBDCS] HVSCAN STARTED (PID=%d, ID=%d)\n", date('Y-m-d.h.i.s'), $pid, $id);
            $logfile = sprintf("/var/operation/HVSCAN/%06d/log.txt", $id);
            file_put_contents($logfile, $log, FILE_APPEND);
            */
            
            startCAEN("HVscan", $id);
            header("Location: index.php?q=hvscan&p=ongoing");
        }
    }
}





?>



    
<h3 style="display: inline;"><?=$TITLE?> High Voltage Scan</h3>

<?php 
if(!empty($error)) { echo '<br /><br /><div class="error">Error: '.$error.'</div>'; }
elseif($pass != '') { echo '<br /><br /><div class="pass">'.$pass.'</div>'; }
?>

   
<div class="content" style="display: flex; margin-top: 15px;">
    
    <form method="POST" action="" id="hvscan-form" onsubmit="return confirm('Do you really want to start this scan?');">

    <!-- LEFT COLUMN: generic information (both CURRENT and DAQ -->
    <div style="width: 450px; float: left;"><table>
            
        <tr>
            <td width="130px" style="height: 30px;">Type scan:</td>
            <td>
                <select name="scantype" id="scantype">
                    <?php
                    if($TYPESCAN == 'current') $types_scan = $hvscan_current_types;
                    elseif($TYPESCAN == 'daq') $types_scan = $hvscan_daq_types;
                 
                    foreach($types_scan as $key => $type) {
                        echo '<option value="'.$key.'">'.$type.'</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
        
        <tr>
            <td style="height: 30px;">Source configuration:</td>
            <td>
                <select name="source" id="source">
                    <option value="0">Source OFF</option>
                    <option <?php echo ($source == 1) ? 'selected="selected"' : ''; ?> value="1">Source ON</option>
                </select>
                &nbsp;&nbsp;&nbsp;U <input size="3" name="attU" id="attU" type="text" value="<?php echo $attU; ?>" maxlength="3" /> &nbsp;&nbsp;&nbsp;D <input size="3" name="attD" id="attD" type="text" value="<?php echo $attD; ?>" maxlength="3" />
            </td>
        </tr>
        
        <tr>
            <td style="height: 30px;">Beam configuration:</td>
            <td>
                <select name="beam" id="beam">
                    <option value="0">Beam OFF</option>
                    <option value="1">Beam ON</option>
                </select>
            </td>
        </tr>

        <tr>
            <td style="height: 30px;">Waiting time:</td>
            <td><input size="10" name="waiting_time" type="text" value="<?php echo settings("waiting_time"); ?>"/> (min)</td> 
        </tr>
        
        <?php if($TYPESCAN == 'daq') { ?>
        <tr>
        <td style="height: 30px;">Trigger mode:</td>
        <td>
            <?php
            foreach($trigger_modes as $key => $value) {
                echo '<input style="width: 15px" type="checkbox" name="'.$key.'" /> '.$value.'&nbsp;&nbsp;&nbsp;';
            }
            ?>
        </td>
        </tr>
        <?php 
        }  
        ?>
        
        <tr>
            <td style="height: 30px;"><?php echo ($TYPESCAN == 'current') ? 'Measure' : 'Minimal measure';?> time:</td>
            <td><input size="10"  name="measure_time" type="text" value="<?php echo settings("CURRENT_measuring_time"); ?>"/> (min)</td> 
        </tr>
      

    </table></div>
    
    <div style="width: 450px; float: right;">
    <table>
        <tr>
            <td style="height: 30px; vertical-align: top">Comments:</td>
            <td><textarea  name="comments" cols="35" rows="3"></textarea></td>
        </tr>

        
        <tr>
            <td width="130px" style="height: 30px;">HV after scan:</td>
            <td>
                <select name="last_HV" id="scantype">
                    <?php
                    foreach($lastHV as $key => $type) {
                        echo '<option value="'.$key.'">'.$type.'</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
        
        <?php 
        foreach($installedTrolleys as $value) { 
            if(str_replace(" ", "", $value) == "") continue;
            $pos = getTrolleyPosition($value);
            echo '<tr><td style="width: 100px; height: 30px;">Position trolley '.$value.':</td><td>'.$position_mode[$pos['position']].'</td></tr>';
        }      
        ?>
        
    </table>
    </div>
    
</div>

<div class="content">
    <br /> 
    <table class="table">
        <?php
        $tot = count($voltages);
        $remaining = 900 - 130 - count($chambers)*70 -100;
        
        echo '<thead><tr>';
        echo '<td class="oddrow" width="130px"></th>';
        foreach($chambers as $chamber) {
            echo '<td class="oddrow" width="70px"><div class="tooltip">T'.$chamber['trolley'].'_S'.$chamber['slot'].'<span class="tooltiptext">'.$chamber['chamber'].'</span></div> <input type="checkbox" checked name="ON-'.$chamber['trolley'].'-'.$chamber['slot'].'" style="vertical-align:middle;  width: 15px;" /></td>';
        }
        if($TYPESCAN == "daq") {
            echo '<td width="100px" class="oddrow">Max triggers</td>';
        }
        else echo '<td width="100px" class="oddrow"></td>';
        echo '<td class="oddrow" width="'.$remaining.'"></td>';
        echo '</tr></thead>';
        
        //$single_gap = (settings('DAQ_RPC_mode') == 'single_gap') ? '(TN/TW/BOT)' : '';
        
        for($i=0; $i<$maxHVPoints; $i++) {
            
            $class = ($i%2 == 0) ? 'odd' : 'even';
            echo '<tr class="'.$class.'">';
            echo '<td >';
            if(settings('RPC_mode') == 'double_gap') echo 'HV<sub>eff</sub> '.($i+1);
            else echo 'HV<sub>eff</sub> BOT<br /> HV<sub>eff</sub> TOP/TN<br /> HV<sub>eff</sub> TW';
            echo '</td>';
            $j = 0;
            foreach($chambers as $chamber) {
                if(settings('RPC_mode') == 'double_gap') {
                    echo '<td class="'.$class.'"><input size="4" name="HV-'.$chamber['trolley'].'-'.$chamber['slot'].'[]" type="text" value="'.getHV($chamber['trolley'], $chamber['slot'], $i+1, $template_HV_ID).'"/></td>';
                    //echo '<td class="'.$class.'"><input size="5" name="HV-'.$chamber['trolley'].'-'.$chamber['slot'].'[]" type="text" value="'.(500 + $i*50).'"/></td>';

                    }
                else {
                    
                    // Check the amount of gaps for this slot
                    $a = $dbh->prepare("SELECT * FROM detectors WHERE trolley = ".$chamber['trolley']." AND slot = ".$chamber['slot']." ORDER BY name ASC ");
                    $a->execute();
                    $gaps = $a->fetchAll();
                    $noGaps = $a->rowCount();
    
                    echo '<td valign="top" class="'.$class.'">';
                    foreach($gaps as $gap) {
                        $value = getHV($chamber['trolley'], $chamber['slot'], $i+1, $template_HV_ID, $gap['id']);
                        echo '<input size="4" name="DETID'.$gap['id'].'[]" type="text" value="'.$value.'"/>';
                    }
                    echo '</td>';
                }
                $j++;
            }
            if($TYPESCAN == "daq") {
                echo '<td valign="top" class="'.$class.'"><input size="6" name="maxtriggers[]" type="text" value="'.getMaxTriggers($chamber['trolley'], $chamber['slot'], $i+1, $template_HV_ID).'"/></td>';
            }
            else echo '<td class="'.$class.'" ></td>';
            echo '<td class="'.$class.'" width="'.$remaining.'"></td>';
            echo '</tr>';
        }
        

        ?>
    </table>     
    <br />


    <input type="submit" name="startscan" value="Start HV scan" />

    </form>  
    
 

</div>

