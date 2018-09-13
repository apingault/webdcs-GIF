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
    case "daq": $filter = "daq"; break;
}

if(!isset($_GET['filter']) && $hvscan['type'] == 'daq') $filter = 'daq';
 
// Get selected trollys
$installedTrolleys = $hvscan['trolley'];
$installedTrolleys = explode(':', $installedTrolleys);

$p = ($filter == "caen") ? "Current" : "Rate";



echo '<div class="content">';
echo '<h3>Offline plots (Scan ID '.$idstring.')</h3>';

if($hvscan['type'] == "daq") {
    echo '&raquo; <a href="index.php?q=offline&id='.$id.'&filter=caen">Current plots</a><br />';
    echo '&raquo; <a href="index.php?q=offline&id='.$id.'&filter=daq">DAQ parameters</a><br />';
    echo '&raquo; <a href="index.php?q=offline&id='.$id.'&filter=dip">DIP parameters</a>';
}

if($hvscan['type'] == "current") {
    echo '&raquo; <a href="index.php?q=offline&id='.$id.'&filter=caen">Current plots</a><br />';
    echo '&raquo; <a href="index.php?q=offline&id='.$id.'&filter=dip">DIP parameters</a>';
}

if($_GET['filter'] == "dip") {
    
    $params = file("/var/operation/RUN/DIP_PUBLICATIONS");
    $pubs = array();
    foreach($params as $i => $p) {
    
        $tmp = explode("\t", $p);
        $params[$i] = str_replace("!", " ", $tmp[1]);
        array_push($pubs, str_replace("DIP_", "", $tmp[0]));
    }
    
    if(isset($_POST['submit'])) {
        
        $param = $pubs[$_POST['param']];
    }
    else {
        
        $param = "T201";
    }
    
    
    ?>

    <br /><br />
    <form action="" method="POST">
        <select name="param">
            <?php
            foreach($params as $i => $p) {
                    
                 $sel = ($param == $pubs[$i]) ? 'selected="selected"' : '';
                 echo '<option '.$sel.' value="'.$i.'">'.$p.'</option>';
            }
            ?> 
        </select> <input type="submit" name="submit" value="Plot" />
    </form>
    
    <br />
    <center><img src="/HVSCAN/<?=$idstring?>/Online/DIP/DIP-<?=$param?>.png" /></center>

    <?php
}
else {
foreach($installedTrolleys as $trolley) {
    

    if(str_replace(" ", "", $trolley) == "") continue;
    
    $sth1 = $dbh->prepare("SELECT slot, name, chamber FROM detectors WHERE trolley = $trolley GROUP BY chamber ORDER BY slot ASC");
    $sth1->execute();
    $chambers = $sth1->fetchAll();
    
    foreach($chambers as $chamber) {
            
        echo '<h3>Trolley '.$trolley.' - Slot '.$chamber['slot'].' ('.$chamber['chamber'].')</h3>';
        if($filter == "caen") {
            echo '<center><img width="49%" src="/HVSCAN/'.$idstring.'/Online/Current-'.$chamber['chamber'].'.png" />';
            echo '<img width="49%" src="/HVSCAN/'.$idstring.'/Online/ADC-'.$chamber['chamber'].'.png" /></center>';
        }
        if($filter == "daq") {
            echo '<center><img width="75%" src="/HVSCAN/'.$idstring.'/Online/Rate-'.$chamber['chamber'].'.png" /></center>';
        }

    }
}
}
echo '</div>';
?>
