<?php
// Get all chambers
$sth1 = $DB['MAIN']->prepare("SELECT daqtype FROM hvscan_DAQ WHERE id = $id LIMIT 1");
$sth1->execute();
$daqtype = $sth1->fetch();


if($daqtype[0] == "lyondaq") {


    $dir = sprintf("/var/operation/HVSCAN/%06d/HV%d/DAQ/", $id, $HV);
    $files = scandir($dir);

    $wwwdir = sprintf("/HVSCAN/%06d/HV%d/DAQ/", $id, $HV);


    foreach($files as $f) {

        if(!(strpos($f, ".png") > -1)) continue;

        //echo $f."<br >";
        echo '<img src="'.$wwwdir.$f.'" ?>';
    }


    die();
}



// Get all chambers
$sth1 = $DB['MAIN']->prepare("SELECT c.name, c.partitions FROM chambers c, gaps g, hvscan_VOLTAGES v WHERE v.HVPoint = $HV AND v.scanid = $id AND v.gapid = g.id AND g.chamberid = c.id GROUP BY c.name");
$sth1->execute();
$chambers = $sth1->fetchAll();


$partitions = array('A', 'B', 'C', 'D', 'E', 'F');



echo '<div class="dqm-images">';

/*
 * Time_Profile
Hit_Profile
Hit_Multiplicity
Strip_Mean_Noise
Strip_Activity
Strip_Homogeneity
mask_Strip_Mean_Noise
mask_Strip_Activity
NoiseCSize_H
NoiseCMult_H
Chip_Mean_Noise
Chip_Activity
Chip_Homogeneity
Beam_Profile
L0_Efficiency
MuonCSize_H
MuonCMult_H
 */

$plots = array("Hit_Profile", "Hit_Multiplicity", "Strip_Mean_Noise", "Time_Profile", "NoiseCSize_H", "NoiseCMult_H");
$width = 100/count($plots);

foreach($chambers as $chamber) {

    for($i=0; $i < $chamber['partitions']; $i++) {

        echo '<h3>'.$chamber['name'].' - partition '.$partitions[$i].'</h3>';

        foreach($plots as $plot) {

            echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/DAQ/'.$plot.'_'.$chamber['name'].'_'.$partitions[$i].'.png"><img width="'.$width.'%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/DAQ/'.$plot.'_'.$chamber['name'].'_'.$partitions[$i].'.png" /></a>';
        }
    }


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
