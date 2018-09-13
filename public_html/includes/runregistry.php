<?php
// Get scan type
if($_GET['type'] == 'current') {
    $TYPESCAN = 'current';
    $TITLE = 'CURRENT';
}
else {
    $TYPESCAN = 'daq';
    $TITLE = 'DAQ';
}

$class = "hidden";

// List of selection options
$filters = array();
$filters['sourceON'] = "Source ON";
$filters['sourceOFF'] = "Source OFF";
$filters['beamON'] = "Beam ON";
$filters['beamOFF'] = "Beam OFF";
$filters['t1'] = "Trolley 1 only";
$filters['t3'] = "Trolley 3 only";
$filters['t13'] = "Trolley 1 and 3";


$filters1 = array();
if($TYPESCAN == "current") foreach($hvscan_current_types as $key => $value) $filters1[$key] = $value;
if($TYPESCAN == "daq") foreach($hvscan_daq_types as $key => $value) $filters1[$key] = $value;


// Build the filter SQL syntax
$SQLFilter = "1=1 ";
if(isset($_POST['submit'])) {
    
    $class = "";
    
    foreach($_POST as $sel => $val) {

        switch($sel) {

            default: $filter = ""; break;
            case 'sourceON': $SQLFilter .= "AND source = 1 "; break;
            case 'sourceOFF': $SQLFilter .= "AND source = 0 "; break;
            case 'beamON': $SQLFilter .= "AND beam = 1 "; break;
            case 'beamOFF': $SQLFilter .= "AND beam = 0 "; break;
            case 't1': $SQLFilter .= "AND trolley LIKE '%1%' AND NOT trolley LIKE '%3%' "; break;
            case 't3': $SQLFilter .= "AND trolley LIKE '%3%' AND NOT trolley LIKE '%1%' "; break;
            case 't13': $SQLFilter .= "AND (trolley LIKE '%1%' AND trolley LIKE '%3%') "; break;
            case 'type': $SQLFilter .= "AND d.type = '".$val."' "; break;
        }
    }
    
    // Flags
    $sqlFlag = "";
    if($_POST['flag'] != "") {
        
        $sqlFlag .= " AND (1=2";
        $sth1 = $dbh->prepare("SELECT runids FROM physics_flags WHERE id = ".$_POST['flag']);
        $sth1->execute();
        $res = $sth1->fetch();
        $runids = explode(",", $res['runids']);
        foreach($runids as $id) $sqlFlag .= " OR h.id = ".$id;
        $sqlFlag .= ") ";
    }
}
else { // Default filter values
    $SQLFilter .= "AND d.type != 'calibration' AND d.type != 'impaired' ";
}


if($TYPESCAN == "current") {
    $sth1 = $dbh->prepare("SELECT h.*, d.type AS scantype FROM hvscan h, hvscan_CURRENT d WHERE h.type = 'current' AND h.id = d.id  AND (".$SQLFilter.") ".$sqlFlag." ORDER BY id DESC");
}
elseif($TYPESCAN == "daq") {
    $sth1 = $dbh->prepare("SELECT h.*, d.type AS scantype FROM hvscan h, hvscan_DAQ d WHERE h.type = 'daq' AND h.id = d.id AND (".$SQLFilter.") ".$sqlFlag." ORDER BY id DESC");
}
$sth1->execute();
$hvscans = $sth1->fetchAll();


// Read flags
$sth1 = $dbh->prepare("SELECT * FROM physics_flags ORDER BY id DESC");
$sth1->execute();
$flags = $sth1->fetchAll();


?>

<div class="content">
    <h3 style="display: inline;">HVscan Run Registry </h3>&nbsp;&nbsp;&mdash;&nbsp;&nbsp; <a href="#" id="filterRunRegistry">Filter</a>
    
    <br /><br />
    
        <div id="filterRunRegistryDiv" class="<?php echo $class; ?>" style="border: 1px solid #000; padding: 10px;">
        <form method="post" action="">
            <div style="float: left; width: 200px;">
            <?php 
            foreach($filters as $key => $value) {
                
                $checked = (array_key_exists($key, $_POST)) ? "checked" : "";
                echo '<label><input style="vertical-align:-2px;" '.$checked.' type="checkbox" name='.$key.' /> '.$value.'</label><br />';
            }
            ?>
            </div>
            <div style="float: left; width: 220px;">
            <?php 
            foreach($filters1 as $key => $value) {
                
                $checked = ($_POST['type'] == $key) ? "checked" : "";
                echo '<label><input style="vertical-align:-2px;" '.$checked.' type="radio" name="type" value="'.$key.'" /> '.$value.'</label><br />';
            }
            ?>
            </div>
            <div style="float: left; width: 300px;">
            Physics flag:<br />
            <select style="margin-top: 3px;" name="flag">
                <option value="">No flag</option>
            <?php 
            foreach($flags as $key => $value) {
                
                $checked = ($_POST['flag'] == $value['id']) ? "selected" : "";
                echo '<option value="'.$value['id'].'" /> '.$value['flagname'].'</option>';
            }
            ?>
            </select>
            </div>
            <div style="width: 400px; padding-top: 10px; clear: both;">
                <input value="Apply filter" type="submit" name="submit" />  
            </div>
    </form>
    </div>
    
</div>

<div style="margin: 0 auto; width: 1050px; margin-top: 10px;">

    
 
    <table cellpadding="5px" cellspacing="0">
        <thead style="font-weight: bold;"><tr>
            <td class="oddrow" width="60px">Scan ID</td>
            <td class="oddrow" width="175px">Scan type</td>
            <td class="oddrow" width="135px">Start time</td>
            <td class="oddrow" width="135px">End time</td>
            <td class="oddrow" width="135px">Trolley 1 position</td>
            <td class="oddrow" width="50px">Source</td>
            <td class="oddrow" width="80px">Attenuator U</td>
            <td class="oddrow" width="80px">Attenuator D</td>
            <td class="oddrow" width="40px">Beam</td>
            <td class="oddrow" width="60px">Status</td>
            <td class="oddrow" width="50px">HVPoints</td>
         </tr></thead>
        <tbody>
        <?php
        
        $i = 0;
        foreach ($hvscans as $hvscan) {

            $pos = getTrolleyPosition(1, $hvscan['time_start']);


            $class = ($i%2 == 0) ? 'evenrow' : 'oddrow';
            echo '<tr class="'.$class.'">';
            printf('<td>%06d</td>', $hvscan['id']);
            if($TYPESCAN == 'current') $l = $hvscan_current_types;
            elseif($TYPESCAN == 'daq') $l = $hvscan_daq_types; 
            echo '<td><a href="index.php?q=hvscan_result&id='.$hvscan['id'].'">'.$l[$hvscan['scantype']].'</a></td>';
            echo '<td>'.date('Y-m-d H:i', $hvscan['time_start']).'</td>';
            echo ($hvscan['time_end'] == NULL) ? '<td>-</td>' : '<td>'.date('Y-m-d H:i', $hvscan['time_end']).'</td>';
            if(!$pos) echo '<td>not found</td>';
            else echo '<td>'.$position_mode[$pos['position']].' (id '.$pos['id'].')</td>';
            echo ($hvscan['source'] == 0) ? '<td><font color="red"><b>OFF</b></font></td>' : '<td><font color="green"><b>ON</b></font></td>';
            echo '<td>'.effAttenuation($hvscan['attU']).'</td>';
            echo '<td>'.effAttenuation($hvscan['attD']).'</td>';
            echo ($hvscan['beam'] == 0) ? '<td><font color="red"><b>OFF</b></font></td>' : '<td><font color="green"><b>ON</b></font></td>';
            echo '<td>';
            switch($hvscan['status']) {
                case '0' : echo '<font color="blue"><b>FINISHED</b></font>'; break;
                case '1' : echo '<font><b>ONGOING</b></font>'; break;
                case '2' : echo '<font color="red"><b>KILLED</b></font>'; break;
                case '3' : echo '<font color="green"><b>APPROVED</b></font>'; break;
                case '4' : echo '<font><b>RESUMED</b></font>'; break;
            }
            echo '</td>';
            echo '<td>'.$hvscan['maxHVPoints'].'</td>';
            echo '</tr>';
            $i++;
        }
        ?>
        </tbody>
    </table>
    <br /><br />

