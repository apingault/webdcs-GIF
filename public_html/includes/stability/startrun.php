<?php
if(!defined('INDEX')) die("Access denied");
$attenuators = array("000", "999", "111", "112", "113", "121", "122", "123", "131", "132", "133", "211", "212", "213", "221", "222", "223", "231", "232", "233", "311", "312", "313", "321", "322", "323", "331", "332", "333");
$error = "";


function getHV_WP($target) {
	
	// Target can be chamber or detector ID
	global $dbh;
	
	if(is_int($target)) $sql = "SELECT hv_wp FROM detectors WHERE id = ".$target;
	else $sql = "SELECT hv_wp FROM detectors WHERE chamber = '".$target."' LIMIT 1";
	$sth1 = $dbh->prepare($sql);
    $sth1->execute();
    $res = $sth1->fetch();

	return $res["hv_wp"];
}

function getHV_STBY() {
	
}

// Load current run file
$runFile = file_get_contents("/var/operation/RUN_STABILITY/run");
if(strpos($runFile, 'RUN') !== false) { // If current run ongoing --> load current run values
    $run = true;
    $sth1 = $dbh->prepare("SELECT * FROM stability ORDER BY id ASC LIMIT 1");
    $sth1->execute();
    $res = $sth1->fetch();
    $template_ID = $res['id']; 
}
else {
    $run = false;
    $template_ID = settings("STABILITY_template_ID");
}

// Get profile HV values
function getHVfromAtt($att, $template_ID) {
    
    global $dbh;
    $sth1 = $dbh->prepare("SELECT HV FROM stability_VOLTAGES WHERE stabilityid = ".$template_ID." AND attU = ".$att." LIMIT 1");
    $sth1->execute();
    $t = $sth1->fetch();
    return $t[0];
}


// Construct array with TROLLEY-SLOT format
$sth1 = $dbh->prepare("SELECT trolley, slot, chamber FROM detectors WHERE stability = 1 AND enabled = 1 GROUP BY trolley, slot ORDER by trolley, slot ASC");
$sth1->execute();
$chambers = $sth1->fetchAll();
$trolleys = array();
foreach($chambers as $chamber) array_push($trolleys, $chamber['trolley'].'-'.$chamber['slot']);

// Store all detector IDs in array
$sth1 = $dbh->prepare("SELECT id FROM detectors");
$sth1->execute();
$detector_ids = $sth1->fetchAll();
$detectors = array();
foreach($detector_ids as $det) array_push ($detectors, $det['id']);


// Function: get trolley-slot for a given detector ID
function getTrolleySlot($id) {
    
    global $dbh;
    $sth1 = $dbh->prepare("SELECT trolley, slot FROM detectors where id = $id");
    $sth1->execute();
    $res = $sth1->fetch();
    return $res['trolley'].'-'.$res['slot'];
}

if(filter_input(INPUT_POST, 'startrun') != NULL) {
   

    $type = filter_input(INPUT_POST, 'type'); // Both for DAQ and CURRENT scans
    $comments = filter_input(INPUT_POST, 'comments');

    // Set voltages: depends on double or single gap
    $voltages = array(); // index = detector id, value = array of voltages
    $i0 = array();
    foreach($trolleys as $trolley) {
       
        list($t, $s) = explode("-", $trolley); // get trolley and slot number
        
        // Select all gaps in current trolley and slot
        $sth1 = $dbh->prepare("SELECT * FROM detectors WHERE trolley = $t AND slot = $s");
        $sth1->execute();
        $gaps = $sth1->fetchAll();
        
        if(settings('RPC_mode') == 'double_gap') {
            
            // Check i0 setting
            if($_POST['I0-'.$trolley] <= 0 || $_POST['I0-'.$trolley] > 1000) {
                $error = "Error: i0 values not correct.";
            }
            else {
                foreach($gaps as $gap) {
                    $i0[$gap['id']] = $_POST['I0-'.$trolley];
                }
            }

            // Loop over all the gaps in current trolley-slot
            foreach($attenuators as $att) {
                foreach($gaps as $gap) {
                    if($_POST['HV-'.$att.'-'.$trolley] > 0 && $_POST['HV-'.$att.'-'.$trolley] < 11000) {
                         $voltages[$att][$gap['id']] = $_POST['HV-'.$att.'-'.$trolley];
                    }
                    else {
                         $error = "Error: voltages not correct.";
                    }
                }
            }
        }
        else {
            
            // Loop over all the gaps in current trolley-slot
            foreach($gaps as $gap) {
                $voltages[$gap['id']] = $_POST['DETID'.$gap['id']];
            }       
        } 
    }
   

    // Generate scan ID
    $q = $dbh->prepare("SELECT * FROM stability ORDER BY id DESC LIMIT 0, 1");
    $q->execute();
    $f = $q->fetch();
    $id = $f['id'] + 1;
    $now = time();


    // Write HVscan profile to database (generic)
    $sth1 = $dbh->prepare("INSERT INTO stability (id, time_start, time_end, type, comments, RPC_mode, status)
                          VALUES (:id, :time_start, :time_start, :type, :comments, :RPC_mode, 0) "); 
        
    $sth1->bindParam(':id', $id); 
    $sth1->bindParam(':type', $type); 
    $sth1->bindParam(':time_start', $now); 
    $sth1->bindParam(':comments', htmlspecialchars($comments), PDO::PARAM_STR); 
    $sth1->bindParam(':RPC_mode', settings('RPC_mode'));
    if(!$sth1->execute()) {
        print_r($sth1->errorInfo());
        die('<div class="error">Error: failed to submit to the database (1).</div>');
    }
    else {
            
        foreach($i0 as $key => $value) {
                
            $sth1 = $dbh->prepare("UPDATE detectors SET i0 = :i0 WHERE id = :id "); 
            $sth1->bindParam(':id', $key); 
            $sth1->bindParam(':i0', $value);
            if(!$sth1->execute()) $error = "Error: failed to submit to the database (1)."; 
        }
            
        // Add record to voltages table
        foreach($attenuators as $att) {

            foreach($voltages[$att] as $key => $HV) { // Loop over all gaps

                $sth1 = $dbh->prepare("INSERT INTO stability_VOLTAGES (stabilityid, detectorid, HV, attU) VALUES (:stabilityid, :detectorid, :HV, :attU) "); 
                $sth1->bindParam(':stabilityid', $id); 
                $sth1->bindParam(':detectorid', $key);
                $sth1->bindParam(':HV', $HV);
                $sth1->bindParam(':attU', $att);
                if(!$sth1->execute()) die('<div class="error">Error: failed to submit to the database (2).</div>'); 
            }
         }
            

         // Write ID to file
         file_put_contents("/var/operation/RUN_STABILITY/id", $id);
         startCAEN("Longevity", $id);
            
         //shell_exec("/home/webdcs/software/CAEN/webdcs/Stability.sh ".$id." > /dev/null &"); // Debug
         //$mm = shell_exec("/home/webdcs/software/CAEN/webdcs/Stability.sh ".$id);


    }
}


$disabled = ($run) ? 'disabled="disabled"' : '';


?>


<h3 style="display: inline;">Start run</h3>

   
<form method="POST" action="" id="stability-form" onsubmit="return confirm(\'Do you really want to start this scan?\');">
    
<table>
            
    <tr>
        <td width="130px" style="height: 30px;">Stability type:</td>
        <td>
        <select <?php echo $disabled; ?> name="type" id="scantype">
        <?php
        foreach($stability_types as $key => $type) {
            echo '<option value="'.$key.'">'.$type.'</option>';
        }
        ?>
        </select>
        </td>
    </tr>
        
    <tr>
        <td style="height: 30px;">Comments:</td>
        <td><textarea <?php echo $disabled; ?> name="comments" cols="30" rows="2"></textarea></td>
    </tr>
        
</table>
    
<br /><br />

<table class="tablesorter-blue" cellpadding="5px" cellspacing="0">
    
    <?php
    $remaining = 900 - 130 - count($chambers)*60;
        
    // HEADER
    echo '<thead><tr>';
    echo '<th class="oddrow" width="130px"></th>';
    foreach($chambers as $chamber) {   
		echo '<th class="oddrow" width="70px"><div class="tooltip">T'.$chamber['trolley'].'_S'.$chamber['slot'].'<span class="tooltiptext">'.$chamber['chamber'].'</span></div></th>';
    }
    echo '<th width="'.$remaining.'px"></th>';
    echo '</tr></thead>';
        
    // I0
    echo '<tr>';
    echo '<td class="even">';
    if(settings('RPC_mode') == 'double_gap') echo 'I<sub>0</sub> Setting';
    else echo 'I<sub>0</sub> BOT<br /> I<sub>0</sub> TOP/TN<br /> I<sub>0</sub> TW';
    echo '</td>';
    $j = 0;
    foreach($chambers as $chamber) {
        if(settings('RPC_mode') == 'double_gap') {
            echo '<td class="evenrow"><input '.$disabled.' size="4" name="I0-'.$chamber['trolley'].'-'.$chamber['slot'].'" type="text" value=""/></td>';
        }
        else {
                    
            // Check the amount of gaps for this slot
            $a = $dbh->prepare("SELECT * FROM detectors WHERE trolley = ".$chamber['trolley']." AND slot = ".$chamber['slot']." ");
            $a->execute();
            $gaps = $a->fetchAll();
            $noGaps = $a->rowCount();
    
            echo '<td valign="top" class="evenrow">';
            foreach($gaps as $gap) {
                $value = getHV($chamber['trolley'], $chamber['slot'], $i+1, $template_HV_ID, $gap['id']);
                echo '<input '.$disabled.' size="4" name="I0-DETID'.$gap['id'].'[]" type="text" value="'.$value.'"/>';
            }
            echo '</td>';
        }
        $j++;
    }
    echo '<td class="evenrow"></td>';
    echo '</tr>';
        
    // VOLTAGES DIFFERENT ATTENUATORS
    $i = 1;
    foreach($attenuators as $att) {
       $class = ($i%2 == 0) ? 'odd' : 'even';
        echo '<tr class="'.$class.'">';
        echo '<td class="'.$class.'">';
        if(settings('RPC_mode') == 'double_gap') {
            $tmp = effAttenuation($att);
            if($att == "000") echo 'HV<sub>eff</sub> Source OFF';   
            elseif($att == "999") echo 'HV<sub>eff</sub> STANDBY';   
            else echo 'HV<sub>eff</sub> ATT '.$att.'('.$tmp.')';
        }
        else echo 'HV<sub>eff</sub> BOT<br /> HV<sub>eff</sub> TOP/TN<br /> HV<sub>eff</sub> TW';
        echo '</td>';
        $j = 0;
        foreach($chambers as $chamber) {

			echo '<td class="'.$class.'"><input '.$disabled.' size="4" name="HV-'.$att.'-'.$chamber['trolley'].'-'.$chamber['slot'].'" type="text" value="'.getHV_WP($chamber["chamber"]).'"/></td>';
            $j++;
        }
       
        echo '<td class="'.$class.'"></td>';
        echo '</tr>';
        $i++;
    }
    ?>
    
</table>     

<br />

    <input <?=$disabled?> type="submit" name="startrun" value="Start run" />

</form>
