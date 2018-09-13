<script>
$(function(){
    $('#periods').on('change', function () {
        var url = $(this).val(); // get selected value
        if (url) { // require a URL
            window.location = url; // redirect
        }
    return false;
    });
});

$(document).ready(function() {
$('.daq-images').magnificPopup({
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

<?php

$id = $_GET['id'];
$HV = $_GET['HV'];
$idstring = sprintf("%06d", $id);

$dir = sprintf("/var/operation/HVSCAN/%06d", $id);
$path = sprintf($dir."/*_HV".($HV)."_*.root", $id);
$files = glob($path);

$sth1 = $dbh->prepare("SELECT * FROM hvscan WHERE id = $id");
$sth1->execute();
$hvscan = $sth1->fetch();

// Check if ID is valid
if($sth1->rowCount() == 0) {
    
    echo '<div class="content"><div class="error">Error: scan ID not found</div></div>';
    exit(1);
}

switch($_GET['filter']) {
    
    default: $filter = "caen"; break;
    case "dip": $filter = "dip"; break;
    case "daq": $filter = "daq"; break;
}
if(!isset($_GET['filter']) && $hvscan['type'] == 'daq') $filter = 'daq';
 
// Get selected trollys
$installedTrolleys = $hvscan['trolley'];
$installedTrolleys = explode(':', $installedTrolleys);



echo '<div class="content">';
echo '<h3>DQM - HV POINT '.$HV.' - SCAN ID '.$idstring.'</h3>';


echo '&raquo; <a href="index.php?q=dqm&id='.$id.'&HV='.$HV.'&filter=caen">CAEN parameters</a><br />';
echo '&raquo; <a href="index.php?q=dqm&id='.$id.'&HV='.$HV.'&filter=dip">DIP parameters</a><br />';
if($hvscan['type'] == "daq") {
echo '&raquo; <a href="index.php?q=dqm&id='.$id.'&HV='.$HV.'&filter=daq">DAQ parameters</a>';
}
echo '</div>';

        



echo '<div style="width: 90%; margin: 0 auto; margin-top: 40px;">';
echo '<div class="daq-images">';

 if($filter == "dip") {
        
    echo '<h3>Environmental parameters</h3>';
    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/P201.png"><img width="33%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/P201.png" /></a>';
    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/T201.png"><img width="33%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/T201.png" /></a>';
    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/RH201.png"><img width="33%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/RH201.png" /></a>';
    
    echo '<h3>Source parameters</h3>';
    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/SourceON.png"><img width="33%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/SourceON.png" /></a>';
    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/AttUEff.png"><img width="33%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/AttUEff.png" /></a>';
    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/AttDEff.png"><img width="33%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/AttDEff.png" /></a>';
    
    
    echo '<h3>Gas parameters</h3>';
    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/C2H2F4.png"><img width="33%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/C2H2F4.png" /></a>';
    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/iC4H10.png"><img width="33%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/iC4H10.png" /></a>';
    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/SF6.png"><img width="33%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/SF6.png" /></a>';
   
    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/RPC_MFC_Humidity.png"><img width="33%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/RPC_MFC_Humidity.png" /></a>';
    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/mixture_with_water.png"><img width="33%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/mixture_with_water.png" /></a>';
    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/mixture_without_water.png"><img width="33%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/DIP/mixture_without_water.png" /></a>';
    
}

foreach($installedTrolleys as $trolley) {
    
    if(str_replace(" ", "", $trolley) == "") continue;
    
    
    if($filter == "caen") {

        $sth1 = $dbh->prepare("SELECT d.slot, d.name FROM detectors d, hvscan_VOLTAGES v WHERE v.HVPoint = $HV AND v.scanid = $id AND v.detectorid = d.id AND d.trolley = $trolley");
        $sth1->execute();
        $gaps = $sth1->fetchAll();
        //print_r($chambers);
        foreach($gaps as $gap) {

            echo '<h3>Trolley '.$trolley.' - Slot '.$gap['slot'].' - '.$gap['name'].'</h3>';
            echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/CAEN/HVeff-'.$gap['name'].'.png"><img width="20%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/CAEN/HVeff-'.$gap['name'].'.png" /></a>';
            echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/CAEN/HVapp-'.$gap['name'].'.png"><img width="20%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/CAEN/HVapp-'.$gap['name'].'.png" /></a>';
            echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/CAEN/HVmon-'.$gap['name'].'.png"><img width="20%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/CAEN/HVmon-'.$gap['name'].'.png" /></a>';
            echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/CAEN/Imon-'.$gap['name'].'.png"><img width="20%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/CAEN/Imon-'.$gap['name'].'.png" /></a>'; 
            echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/CAEN/ADC-'.$gap['name'].'.png"><img width="20%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/CAEN/ADC-'.$gap['name'].'.png" /></a>'; 
        }
    }
    
    if($filter == "daq") {

        $sth1 = $dbh->prepare("SELECT d.slot, d.name, d.chamber FROM detectors d, hvscan_VOLTAGES v WHERE v.HVPoint = $HV AND v.scanid = $id AND v.detectorid = d.id AND d.trolley = $trolley GROUP BY d.slot");
        $sth1->execute();
        $gaps = $sth1->fetchAll();
        //print_r($chambers);
        foreach($gaps as $gap) {
            $T = $trolley;
            $S = $gap['slot'];
            $t = array('A', 'B', 'C', 'D');
            echo '<h3>Trolley '.$trolley.' - Slot '.$gap['slot'].' ('.$gap['chamber'].')</h3>';
            foreach($t as $PARTITION) {
                if(file_exists('/var/operation/HVSCAN/'.$idstring.'/HV'.$HV.'/DAQ/RPC_Hit_Profile_T'.$T.'S'.$S.'_'.$PARTITION.'.png')) {
                    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/DAQ/RPC_Hit_Profile_T'.$T.'S'.$S.'_'.$PARTITION.'.png"><img width="16.6%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/DAQ/RPC_Hit_Profile_T'.$T.'S'.$S.'_'.$PARTITION.'.png" /></a>';
                    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/DAQ/RPC_Beam_Profile_T'.$T.'S'.$S.'_'.$PARTITION.'.png"><img width="16.6%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/DAQ/RPC_Beam_Profile_T'.$T.'S'.$S.'_'.$PARTITION.'.png" /></a>';
                    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/DAQ/RPC_Hit_Multiplicity_T'.$T.'S'.$S.'_'.$PARTITION.'.png"><img width="16.6%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/DAQ/RPC_Hit_Multiplicity_T'.$T.'S'.$S.'_'.$PARTITION.'.png" /></a>';
                    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/DAQ/RPC_Mean_Noise_T'.$T.'S'.$S.'_'.$PARTITION.'.png"><img width="16.6%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/DAQ/RPC_Mean_Noise_T'.$T.'S'.$S.'_'.$PARTITION.'.png" /></a>';
                    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/DAQ/RPC_Instant_Noise_T'.$T.'S'.$S.'_'.$PARTITION.'.png"><img width="16.6%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/DAQ/RPC_Instant_Noise_T'.$T.'S'.$S.'_'.$PARTITION.'.png" /></a>'; 
                    echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/DAQ/RPC_Time_Profile_T'.$T.'S'.$S.'_'.$PARTITION.'.png"><img width="16.6%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/DAQ/RPC_Time_Profile_T'.$T.'S'.$S.'_'.$PARTITION.'.png" /></a>'; 
                }  
            }
        }
    }
    
    echo '<br /><br /><br /><br /><br />';

}

echo '</div>';
?>
