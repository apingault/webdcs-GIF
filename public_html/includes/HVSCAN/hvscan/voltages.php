<?php


// Get all chambers in current scan
/*
$sth1 = $dbh->prepare("SELECT trolley, slot, chamber FROM detectors d, hvscan_VOLTAGES h WHERE h.scanid = $id AND h.detectorid = d.id GROUP BY d.chamber ORDER by d.trolley, d.slot ASC");
$sth1->execute();
$chambers = $sth1->fetchAll();

$sth1 = $dbh->prepare("SELECT  FROM detectors d, hvscan_VOLTAGES h WHERE h.scanid = $id AND h.detectorid = d.id GROUP BY d.chamber ORDER by d.trolley, d.slot ASC");
$sth1->execute();
$chambers = $sth1->fetchAll();

$sth1 = $dbh->prepare("SELECT g.name, c.name AS chambername FROM gaps g, chambers c, hvscan_VOLTAGES v WHERE v.scanid = $id AND v.gapid = g.id AND c.id = g.chamberid");
$sth1->execute();
$gaps = $sth1->fetchAll();

*/
$sth1 = $DB['MAIN']->prepare("SELECT c.* FROM chambers c, hvscan_VOLTAGES h, gaps g WHERE h.scanid = $id AND h.gapid = g.id AND c.id = g.chamberid GROUP BY c.name ORDER BY c.id ASC");
$sth1->execute();
$chambers = $sth1->fetchAll();



$resume = true;

if(isset($_POST['resume'])) { // STATUS = 4 = RESUME
    
    // Loop over all points
    for($i=1; $i <= $hvscan['maxHVPoints']; $i++) {
        

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
	startCAEN("HVscan", $id);
    //$pid = shell_exec("/home/webdcs/software/CAEN/webdcs/HVscan.sh ".$id);
    
    // Write pid process to db
    //$t = $dbh->prepare("UPDATE modules SET hvscan_pid = :pid WHERE id = 1");
    //$t->execute(array(':pid' => $pid));
    
    // Append line to log file
    $log = sprintf("%s.[WEBDCS] HVscan resumed by user (PID=%d)\n", date('Y-m-d.H.i.s'), $pid);
    $logfile = sprintf("/var/operation/HVSCAN/%06d/log.txt", $id);
    file_put_contents($logfile, $log, FILE_APPEND);

    header("Location: index.php?q=hvscan&p=ongoing");
    //header("Location: index.php?q=hvscan&type=current");
}


function getGaps($chamberid) {
    
    global $DB;
    $a = $DB['MAIN']->prepare("SELECT * FROM gaps WHERE chamberid = ".$chamberid." ORDER BY name ASC");
    $a->execute();
    return $a->fetchAll();
}

function getHV($scanid, $HVPoint, $chamberid, $gapID = false) {
    
    global $DB;
    if($gapID) {
        $sth1 = $DB['MAIN']->prepare("SELECT HV as HV FROM hvscan_VOLTAGES WHERE scanid = ".$scanid." AND HVPoint = ".$HVPoint." AND gapid = ".$gapID);
    }
    else {
        $sth1 = $DB['MAIN']->prepare("SELECT v.HV as HV FROM hvscan_VOLTAGES v, gaps d WHERE v.scanid = ".$scanid." AND v.gapid = d.id AND v.HVPoint = ".$HVPoint." AND d.chamberid = $chamberid GROUP BY d.chamberid");       
    }
    $sth1->execute();
    $p = $sth1->fetch();
    return $p['HV'];
}

?>

<form action="" method="POST">
<table class="table"  style="font-size: 10px;">

            
    <?php
    
       // $tot = count($voltages);
        $widthChamberCol = 140;
        $widthRemaining = 1000 - 50 - count($chambers)*$widthChamberCol - 100;
        
        echo '<thead><tr>';
        echo '<td class="oddrow" width="50px">Chamber</td>';
        foreach($chambers as $chamber) {
            echo '<td align="center" class="oddrow" width="'.$widthChamberCol.'px">'.$chamber['name'].'</td>';
        }
        echo '<td class="oddrow" width="'.$widthRemaining.'"></td>';
        if($TYPESCAN == "daq") {
            echo '<td  align="center"  width="100px" class="oddrow">Max triggers</td>';
        }
        else {
            echo '<td width="100px" class="oddrow"></td>';
        }
        
        echo '</tr></thead>';
        
        
      for($i=0; $i<$hvscan['maxHVPoints']; $i++) {
            
            $class = ($i%2 == 0) ? 'odd' : 'even';
            echo '<tr class="'.$class.'">';
            if($resume) echo '<td>HV<sub>eff</sub> '.($i+1).' <input style="height: 10px;" type="checkbox" name="HV'.($i+1).'" /></td>';
            else echo '<td>'.($i+1).'</td>';
            //echo '<td valign="top">HV<sub>eff</sub> '.($i+1).'</td>';
            
           // if(settings('RPC_mode') == 'double_gap') echo 'HV<sub>eff</sub> '.($i+1);
           // else echo 'HV<sub>eff</sub> BOT<br /> HV<sub>eff</sub> TOP/TN<br /> HV<sub>eff</sub> TW';
            
              
            $j = 0;
            foreach($chambers as $chamber) {
                
                if($hvscan['RPC_mode'] == 'double_gap') {
                    
                    echo '<td  align="center"  class="'.$class.'">'.getHV($id, $i+1, $chamber['id']).'</td>';
                }
                else {

                    // Get the gaps for this chamber, sorted
                    $gaps = getGaps($chamber['id']);
    
                    echo '<td valign="top" class="'.$class.'">';
                    foreach($gaps as $gap) {
                        $value = getHV($id, $i+1, $chamber['id'], $gap['id']);
                        echo '<span title="'.$gap['name'].'">'.$value.'</span><br />';
                    }
                    echo '</td>';
                }
                $j++;
            }
            
            
            echo '<td class="'.$class.'" width="'.$widthRemaining.'"></td>';
            
            if($TYPESCAN == "daq") {
                // GET TRIGGERS
                $a = $dbh->prepare("SELECT maxtriggers FROM hvscan_VOLTAGES WHERE scanid = $id AND HVPoint = ".($i+1)." LIMIT 1 ");
                $a->execute();
                $trig = $a->fetch();
                
                echo '<td align="center"  valign="top" class="'.$class.'">'.$trig['maxtriggers'].'</td>';
            }
            else {
                echo '<td class="'.$class.'" ></td>';
            }
            echo '</tr>';
        }
        

    
    /*
    $remaining = ($TYPESCAN == "daq") ? 1000 - 100 - count($chambers)*60 - 220 - 30 : 900 - 100 - count($chambers)*60 -220 - 30;
    
    echo '<tr>';
    echo '<td class="oddrow" width="70px">HV<sub>eff</sub></td>';
    echo '<td class="oddrow" width="20px">DQM</td>';
		
    if($TYPESCAN == "daq") echo '<td width="50px" class="oddrow">Triggers</td>';
    foreach($chambers as $chamber) {

        echo '<td class="oddrow" width="60px"><div class="tooltip">T'.$chamber['trolley'].'_S'.$chamber['slot'].'<span class="tooltiptext">'.$chamber['chamber'].'</span></div></td>';
    }
	
    echo '<td class="oddrow" width="'.$remaining.'"></td>';
    echo '</tr></thead>';

    echo '<tbody>';
        for($i = 0; $i < $hvscan['maxHVPoints']; $i++) {
            
            $class = ($i%2 == 0) ? 'odd' : 'even';
            echo '<tr class="'.$class.'">';
                          //  if($resume) echo '<td>'.($i+1).' <input style="height: 10px;" type="checkbox" name="HV'.($i+1).'" /></td>';

            if($hvscan['RPC_mode'] == 'double_gap') {
                if($resume) echo '<td>'.($i+1).' <input style="height: 10px;" type="checkbox" name="HV'.($i+1).'" /></td>';
                else echo '<td>'.($i+1).'</td>';
            }
            else echo '<td>BOT '.($i+1).'<br />TOP/TN '.($i+1).'<br />TW '.($i+1).'</td>';
            
			echo '<td style="border-right: 1px solid #EEE; border-left: 1px solid #EEE;"><a href="index.php?q=hvscan&p=hvscan&id='.$id.'&r=dqm_caen&HV='.($i+1).'">DQM</a></td>';
            
			if($TYPESCAN == "daq") {
                echo '<td style="border-right: 1px solid #EEE;">'.getMaxTriggers($chamber['trolley'], $chamber['slot'], $i+1, $id).'</td>';
            }
			
			
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
     
            
            echo '<td width="'.$remaining.'"></td>';
            
            echo '</tr>';
        }
        
        $class = ($hvscan['maxHVPoints']%2 == 0) ? 'evenrow' : 'oddrow';
        
        */
        ?>
    </tbody>
</table>
	
<?php
if($resume) {
	

	echo '<br /><input type="submit" name="resume" value="Resume" />';
}

?>
	
</form>
    
