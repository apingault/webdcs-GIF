<?php

$sth1 = $DB['MAIN']->prepare("SELECT g.name, c.name AS chambername, c.trolley, c.slot FROM gaps g, chambers c, hvscan_VOLTAGES v WHERE v.HVPoint = $HV AND v.scanid = $id AND v.gapid = g.id AND c.id = g.chamberid");
$sth1->execute();
$gaps = $sth1->fetchAll();

echo '<div class="dqm-images">';
foreach($gaps as $gap) {

    $gapname = $gap['chambername']."-".$gap['name'];
    echo '<h3>Trolley '.$gap['trolley'].' - Slot '.$gap['slot'].' - '.$gapname.'</h3>';
    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/CAEN/HVeff_'.$gapname.'.png"><img width="25%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/CAEN/HVeff_'.$gapname.'.png" /></a>';
    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/CAEN/HVapp_'.$gapname.'.png"><img width="25%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/CAEN/HVapp_'.$gapname.'.png" /></a>';
    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/CAEN/HVmon_'.$gapname.'.png"><img width="25%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/CAEN/HVmon_'.$gapname.'.png" /></a>';
    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/CAEN/Imon_'.$gapname.'.png"><img width="25%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/CAEN/Imon_'.$gapname.'.png" /></a>'; 	
    echo '<br /><br />';
}

echo '</div>';
?>


<script>
$(document).ready(function() {
	$('.dqm-images').magnificPopup({
	  delegate: 'a',
	  type: 'image',
	  gallery: {
				enabled: true,
				navigateByImgClick: true,
				preload: [0,1] // Will preload 0 - before current, and 1 after the current image
			  },
	});
});
</script>