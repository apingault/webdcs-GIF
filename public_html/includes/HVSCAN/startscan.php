<?php
if(!defined('INDEX')) die("Access denied");

$TBLABEL = "TBApr18";

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


// Get all enabled chambers

function getChambers() {

    global $DB;
    $sth1 = $DB['MAIN']->prepare("SELECT * FROM chambers WHERE enabled = 1");
    $sth1->execute();
    return $sth1->fetchAll();
}

$chambers = getChambers();


function getGaps($chamberid) {

    global $DB;
    $a = $DB['MAIN']->prepare("SELECT * FROM gaps WHERE chamberid = ".$chamberid." ORDER BY name ASC");
    $a->execute();
    return $a->fetchAll();
}


// Get scan type
if($_GET['type'] == 'current') {
    $TYPESCAN = 'current';
    $TITLE = 'CURRENT';
}
else {
    $TYPESCAN = 'daq';
    $TITLE = 'DAQ';
}


// Get maximum HV points
$maxHVPoints = settings($TITLE."_HV_points");

// Get profile
$template_HV_ID = settings($TITLE."_HV_template_ID");






$error = "";

if(filter_input(INPUT_POST, 'startscan') != NULL) {

    $scantype = filter_input(INPUT_POST, 'scantype'); // Both for DAQ and CURRENT scans
    // $daqtype = filter_input(INPUT_POST, 'daqtype'); // Both for DAQ and CURRENT scans
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
        foreach(filter_input(INPUT_POST, 'maxtriggers', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) as $value) {

            if(intval($value) < 10) $error = "Trigger requirement > 10";
            array_push($maxtriggers, $value);
        }
    }


    // Set voltages: depends on double or single gap
    $voltages = array(); // index = gap id, value = array of voltages
    foreach($chambers as $chamber) {

        $gaps = getGaps($chamber['id']);
        if(settings('RPC_mode') == 'double_gap') {

            $v = filter_input(INPUT_POST, 'HV-'.$chamber['id'], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

            // Loop over all the gaps for this chamber
            foreach($gaps as $gap) {
                if($gap['enabled'] == 0) continue;
                $voltages[$gap['id']] = $v;
            }
        }
        else {

            foreach($gaps as $gap) {

                if($gap['enabled'] == 0) continue;
                $voltages[$gap['id']] = filter_input(INPUT_POST, 'HV-'.$gap['id'], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            }
        }
    }


    // VALIDATION PASS OR NOT
    if($error == "") {
        // Generate scan ID
        $q = $DB['MAIN']->prepare("SELECT * FROM hvscan ORDER BY id DESC LIMIT 0, 1");
        $q->execute();
        $f = $q->fetch();
        $id = $f['id'] + 1;
        $now = time();

        // Write HVscan profile to database (generic)
        $sth1 = $DB['MAIN']->prepare("INSERT INTO hvscan (id, time_start,  type, waiting_time,  comments,  maxHVPoints, status, RPC_mode, measure_intval,  measure_time,  lastHV, daqtype)
                              VALUES (               :id, :time_start, :type, :waiting_time, :comments, :maxHVPoints, 1, :RPC_mode, :measure_intval, :measure_time, :lastHV, :daqtype) ");
        $sth1->bindParam(':id', $id);
        $sth1->bindParam(':type', $TYPESCAN);
        $sth1->bindParam(':time_start', $now);
        $sth1->bindParam(':waiting_time', $waiting_time);
        $sth1->bindParam(':measure_time', $measure_time);
        $sth1->bindParam(':comments', htmlspecialchars($comments), PDO::PARAM_STR);
        $sth1->bindParam(':maxHVPoints', $maxHVPoints);
        $sth1->bindParam(':measure_intval', settings("measuring_intval"));
        $sth1->bindParam(':RPC_mode', settings('RPC_mode'));
        $sth1->bindParam(':lastHV', $last_HV);
        $sth1->bindParam(':daqtype', settings('daqtype'));
        //$sth1->bindParam(':label', "");




        // Write HVscan profile to database (specific)
        if($TYPESCAN == 'current') {
            $sth2 = $DB['MAIN']->prepare("INSERT INTO hvscan_CURRENT (id, type) VALUES (:id, :type) ");
            $sth2->bindParam(':id', $id);
            $sth2->bindParam(':type', $scantype);
        }
        else {
            $sth2 = $DB['MAIN']->prepare("INSERT INTO hvscan_DAQ (id, type, trigger_mode, daqtype) VALUES (:id, :type, :trigger_mode, :daqtype) ");
            $sth2->bindParam(':id', $id);
            $sth2->bindParam(':type', $scantype);
            $sth2->bindParam(':trigger_mode', $triggers);
            $sth2->bindParam(':daqtype', settings('daqtype'));
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

                    $sth1 = $DB['MAIN']->prepare("INSERT INTO hvscan_VOLTAGES (scanid, gapid, HVPoint, HV, maxtriggers) VALUES (:scanid, :gapid, :HVPoint, :HV, :maxtriggers) ");
                    $sth1->bindParam(':scanid', $id);
                    $sth1->bindParam(':gapid', $key);
                    $sth1->bindParam(':HVPoint', $HVPoint);
                    $sth1->bindParam(':HV', $v);
                    $sth1->bindParam(':maxtriggers', $t);
                    if(!$sth1->execute()) $error = "Error: failed to submit to the database (1).";
                }
            }

            startCAEN("HVscan", $id);
            header("Location: index.php?q=hvscan&p=ongoing");
        }
    }
}


?>


<h3 style="display: inline;"><?=$TITLE?> High Voltage Scan</h3>

<?php
if(!empty($error)) {
    echo '<br /><br /><div class="error">Error: '.$error.'</div>';
}
?>

<script type="text/javascript">


function validateHV(field) {

    var val = field.value;
    if(isNaN(val) || val > 12000 || val < 0)  {

        $(field).css({"border-color": "red", "border-width":"1px", "border-style":"solid"});
    }
    else {

        $(field).css({"border-color": "#b9b9b9", "border-width":"1px", "border-style":"solid"});
    }
}


function validateTrigger(field) {

    var val = field.value;
    if(isNaN(val) || val < 10)  {

        $(field).css({"border-color": "red", "border-width":"1px", "border-style":"solid"});
    }
    else {

        $(field).css({"border-color": "#b9b9b9", "border-width":"1px", "border-style":"solid"});
    }

}

</script>


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
<!--         <tr>
            <td width="130px" style="height: 30px;">Daq type:</td>
            <td>
                <select name="daqtype" id="daqtype">
                    <?php
                    foreach($daq_types as $key => $type) {
                        echo '<option value="'.$key.'">'.$type.'</option>';
                    }
                    ?>
                </select>
            </td>
        </tr> -->




        <tr>
            <td style="height: 30px;">Waiting time:</td>
            <td><input size="10" name="waiting_time" type="text" value="<?php echo settings("waiting_time"); ?>"/> (min)</td>
        </tr>

        <?php if(false and $TYPESCAN == 'daq') { ?>
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
                <select name="last_HV" id="lastHV">
                    <?php
                    foreach($lastHV as $key => $type) {
                        echo '<option value="'.$key.'">'.$type.'</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>


    </table>
    </div>


</div>

<div class="content" style="font-size: 10px;">
    <br />
    <table class="table">
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

        //$single_gap = (settings('DAQ_RPC_mode') == 'single_gap') ? '(TN/TW/BOT)' : '';

        for($i=0; $i<$maxHVPoints; $i++) {

            $class = ($i%2 == 0) ? 'odd' : 'even';
            echo '<tr class="'.$class.'">';
            echo '<td valign="top">HV<sub>eff</sub> '.($i+1).'</td>';

           // if(settings('RPC_mode') == 'double_gap') echo 'HV<sub>eff</sub> '.($i+1);
           // else echo 'HV<sub>eff</sub> BOT<br /> HV<sub>eff</sub> TOP/TN<br /> HV<sub>eff</sub> TW';


            $j = 0;
            foreach($chambers as $chamber) {

                if(settings('RPC_mode') == 'double_gap') {

                    echo '<td  align="center"  class="'.$class.'"><input onchange="validateHV(this)" size="4" name="HV-'.$chamber['id'].'[]" type="text" value="'.getHV($template_HV_ID, $i+1, $chamber['id']).'"/></td>';
                }
                else {

                    // Get the gaps for this chamber, sorted
                    $gaps = getGaps($chamber['id']);

                    echo '<td valign="top" class="'.$class.'">';
                    foreach($gaps as $gap) {
                        $value = getHV($template_HV_ID, $i+1, $chamber['id'], $gap['id']);
                        echo '<input onchange="validateHV(this)" placeholder="'.$gap['name'].'" size="4" name="HV-'.$gap['id'].'[]" type="text" value="'.$value.'" title="'.$gap['name'].'" alt="'.$gap['name'].'" /><br />';
                    }
                    echo '</td>';
                }
                $j++;
            }


            echo '<td class="'.$class.'" width="'.$widthRemaining.'"></td>';

            if($TYPESCAN == "daq") {
                echo '<td align="center"  valign="top" class="'.$class.'"><input size="6" onchange="validateTrigger(this)" name="maxtriggers[]" type="text" value="'.getMaxTriggers($chamber['trolley'], $chamber['slot'], $i+1, $template_HV_ID).'"/></td>';
            }
            else {
                echo '<td class="'.$class.'" ></td>';
            }
            echo '</tr>';
        }


        ?>
    </table>
    <br />


    <input type="submit" name="startscan" value="Start HV scan" />

    </form>



</div>

