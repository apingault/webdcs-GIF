<?php

// strip activity and strip mean noise
if(isset($_GET['HV'])) $HV = $_GET['HV'];
else $HV = 1;

// Get all chambers in the current scan
$sth1 = $dbh->prepare("SELECT c.name, c.trolley, c.slot, c.id, c.partitions FROM chambers c, gaps g, hvscan_VOLTAGES v WHERE v.HVPoint = $HV AND v.scanid = $id AND v.gapid = g.id AND g.chamberid = c.id GROUP BY c.name");
$sth1->execute();
$res = $sth1->fetchAll();

if(isset($_GET['chamber'])) $chamber = $_GET['chamber'];
else $chamber = $res[0]['id'];

$chambers = "";
$trolley = "";
$slot = "";
$chamberName = "";
$nopartitions = 0;
foreach($res as $ch) {
    $sel = ($chamber == $ch['id']) ? 'selected="selected"' : "";
    $chambers .= '<option '.$sel.' value="'.$ch['id'].'">'.$ch['name'].'</option>';
    
    if($chamber == $ch['id']) {

        $trolley = $ch['trolley'];
        $slot = $ch['slot'];
        $chamberName = $ch['name'];
        $nopartitions = $ch['partitions'];
    }
}

$mapping = ""; // mapping lines of the chamber under consideration, based on trolley/slot 
$remaining = ""; // mapping files 

// Open current mapping file
$dirFile = "/var/operation/HVSCAN/".$idstring."/";
$mappingFile = $dirFile . "ChannelsMapping.csv";
if(!file_exists($mappingFile)) {
    
    $error = "Mapping file not found.";
}
else {
    
    $ts = $trolley.$slot; // trolley-slot combination, to be searched in mapping file
    
    $handle = fopen($mappingFile, "r");
    while(($line = fgets($handle)) !== false) {
        
        // skip the # lines

        if (strpos($line, '#') !== false) continue;
        $target = substr($line, 0, 2);
       // echo $target. '';
        if($target == $ts) $mapping .= $line;
        else $remaining .= $line;
    }

    fclose($handle);
}


function rmEmptyLines($m) {
    
    return preg_replace('/\n+/', "\n", trim($m));
}


if(isset($_POST['submit'])) {
    
    // Make backup of the channels mapping file
    $modifTime = time();
    if(!file_exists($dirFile . 'BACKUP')) {
        mkdir($dirFile . 'BACKUP');
    }
    $newFileName = $dirFile . 'BACKUP/ChannelsMapping'.$modifTime.'.csv';
    rename($mappingFile, $newFileName);
    
    // Generate new one (write remaining + new mapping)
    $text = rmEmptyLines($_POST['mapping']); // remove empty lines
    $remaining = rmEmptyLines($remaining);
    $newMapping = rmEmptyLines($text."\r\n".$remaining);
    file_put_contents($mappingFile, $newMapping);
    
    header("Refresh:0");
    
    $mapping = $newMapping;
}

// sort the mappingfile based on the last 3 digits of the first column

$mappingEntries = explode("\n", rmEmptyLines($mapping));
asort($mappingEntries);
$mapping = "";
foreach($mappingEntries as $m){ 
    
    $mapping .= trim($m)."\n";
    
}

?>

<script>
$(function(){

    // bind change event to select
    $('#ChamberSelect').on('change', function () {
        var ch = $(this).val(); // get selected value
        if(ch) { // require a URL
            window.location = "index.php?q=hvscan&p=hvscan&id=<?=$id?>&r=<?=$_GET['r']?>&HV=<?=$HV?>&chamber=" + ch; // redirect
        }
        return false;
    });
});
</script>

<div style="float: left; width: 200px;">

    <form action="" method="POST">
        
        <textarea name="mapping" style="width: 200px; height: 400px;"><?php echo $mapping; ?></textarea> 
        <br /><br />
        <input type="submit" name="submit" value="Save mapping" />
      
    </form>
    
</div>

<div style="float: right; width: 750px;">
    
    Select chamber: <select name="ChamberSelect" id="ChamberSelect"><?php echo nl2br($chambers); ?><select>

    <br /><br /><br />
            
    <div class="images">
        
        <?php
        $partitions = array('A', 'B', 'C', 'D', 'E', 'F');
        $width = 100/$nopartitions;
        for($i=0; $i < $nopartitions; $i++) {
            
            echo '<a href="/HVSCAN/'.$idstring.'/HV'.$HV.'/DAQ/Strip_Mean_Noise_'.$chamberName.'_'.$partitions[$i].'.png"><img width="'.$width.'%" src="/HVSCAN/'.$idstring.'/HV'.$HV.'/DAQ/Strip_Mean_Noise_'.$chamberName.'_'.$partitions[$i].'.png" /></a>';
        }
        ?>
   
    </div>
    
</div>


<script>
$(document).ready(function() {
	$('.images').magnificPopup({
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