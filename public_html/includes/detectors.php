<?php

if(!defined('INDEX')) die("Access denied");

echo '<div class="content">';
    
if(isset($_GET['p'])) require 'DETECTORS/'.$_GET['p'].'.php';
else require 'DETECTORS/chambers.php';


echo '</div>';
echo '<br /><br />';

die();

$installedTrolleys = installedTrolleys();
$selectedTrolley = $_GET['trolley'];
if($selectedTrolley == "") $selectedTrolley = 1;

// Select trolleys
$sth1 = $DB['MAIN']->prepare("SELECT trolley, slot FROM detectors d GROUP BY trolley, slot ORDER by trolley, slot ASC");
$sth1->execute();
$chambers = $sth1->fetchAll();

// Select all detectors
$sth1 = $DB['MAIN']->prepare("SELECT * FROM detectors WHERE trolley = :tid ORDER BY name ASC");
$sth1->execute(array(":tid" => $selectedTrolley));
$detectors = $sth1->fetchAll();

$trolleys = "";
foreach($installedTrolleys as $trolley) {
    
    $trolleys .= ($trolley['id'] == $selectedTrolley) ? '<option selected="selected" value="'.$trolley['id'].'" >'.$trolley['name'].'</option>' : '<option value="'.$trolley['id'].'" >'.$trolley['name'].'</option>';
}
?>

<script>
    $(function(){
      $('#changeTrolley').on('change', function () {
          var trolley = $(this).val();
          window.location.href = 'index.php?q=detectors&trolley=' + trolley;
      });
    });
</script>

<div class="content">
  
	<div style="display: inline">
      <h3 style="display: inline;">Configure detectors</h3>&nbsp;&nbsp;&mdash;&nbsp;&nbsp; Select trolley: <form style="display: inline;"><select id="changeTrolley"><?php echo $trolleys; ?></select></form>
    </div>
    <br /><br />
		
    <table class="table">
    <thead>
      <tr>
		<td width="20px"></td>
        <td width="70px">Trolley/Slot</td>
        <td width="160x">Detector name</td>
        <td width="60px">HV slot</td>
        <td width="60px">HV ch.</td>
        <td width="60px">ADC slot</td>
        <td width="60px">ADC ch.</td>
        <td width="40px">DAQ</td>
        <td width="40px">ADC</td>
		<td width="40px">LONG</td>
        <td width="80px">Res. [Ohm]</td>
		<td width="80px">HV WP</td>
		<td width="80px">HV STBY</td>
      </tr>
    </thead>
	
    <tbody>
    <?php
    $i = 0;
    foreach ($chambers as $value) {
        
        $trolley = $value['trolley'];
        $slot = $value['slot'];
		
        foreach($detectors as $det) {
            
			if($det['trolley'] != $trolley or $det['slot'] != $slot) continue;
			
            $img = ($det['enabled'] == 1) ? $ICON_TICK : $ICON_CROSS;

            echo '<tr>';
			echo '<td>'.$img.'</td>';
            echo '<td>T'.$trolley.'_S'.$slot.'</td>';
            echo '<td>'.$det['name'].'</td>';
            echo '<td>'.$det['CAEN_slot'].'</td>';
            echo '<td>'.$det['CAEN_channel'].'</td>';
            echo '<td>'.$det['ADC_slot'].'</td>';
            echo '<td>'.$det['ADC_channel'].'</td>';
            echo '<td>';
            echo ($det['DAQ'] == 0) ? $ICON_TICK : $ICON_CROSS;;
            echo '</td>';
            echo '<td>';
            echo ($det['RCURR'] == 0) ? $ICON_TICK : $ICON_CROSS;;
            echo '</td>';
			echo '<td>';
            echo ($det['stability'] == 0) ? $ICON_TICK : $ICON_CROSS;
            echo '</td>';
            echo '<td>';
            echo ($det['RCURR'] == 0) ? '-' : $det['ADC_resistor'];
            echo '</td>';
			echo '<td>'.$det['hv_wp'].'</td>';
			echo '<td>'.$det['hv_standby'].'</td>';
            echo '</tr>';
			
        }
        $i++;
    }
    ?>
    </tbody>
    </table>
    
</div>

