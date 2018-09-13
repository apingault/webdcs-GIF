<?php
if(!defined('INDEX')) die("Access denied");
loadCSS("tab.css");

// Get Scan ID and retrieve information
$id = $_GET['id'];
$idstring = sprintf("%06d", $id);
$sth1 = $dbh->prepare("SELECT * FROM stability WHERE id = $id");
$sth1->execute();
global $stability;
$stability = $sth1->fetch();
$dir = sprintf("/var/operation/STABILITY/%06d", $id);

// Check if ID is valid
if($sth1->rowCount() == 0) {
    echo '<div class="content"><div class="error">Error: stability run ID not found</div></div>';
    exit(1);
}

// Get all gaps in current run
$sth1 = $dbh->prepare("SELECT d.id, d.name, d.area, d.chamber FROM stability_VOLTAGES v, detectors d WHERE v.stabilityid = '".$id."' AND v.detectorid = d.id GROUP BY v.detectorid ORDER BY v.detectorid");
$sth1->execute();
$detectors = $sth1->fetchAll();
$detector_option_form = "";
foreach($detectors as $det) {
    $sel = ($det['id'] == $_GET['g']) ? 'selected="selected"' : "";
    $detector_option_form .= '<option '.$sel.' value="'.$det['id'].'">'.$det['name'].'</option>';
}

// Get all chambers in current run
$sth1 = $dbh->prepare("SELECT d.chamber FROM stability_VOLTAGES v, detectors d WHERE v.stabilityid = '".$id."' AND v.detectorid = d.id GROUP BY d.chamber");
$sth1->execute();
$chambers = $sth1->fetchAll();

global $currentGapId;
$currentGapId = (isset($_GET[g])) ? $_GET['g'] : $detectors[0]['id'];
foreach($detectors as $det) {
    if($det['id'] == $currentGapId) {
        
        $currentGapName = $det['name'];
        $currentGapArea = $det['area'];
		$currentChamberName = $det['chamber'];
    }
}

?>

<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script>
    $(function() {
        $( "#tabs" ).tabs();
    });

    $(function(){
        // bind change event to select
        $('#channelSelect').on('change', function () {
            var gap = $(this).val(); // get selected value
            if(gap) { // require a URL
                window.location = "index.php?q=longevity&p=rundqm&id=<?=$id?>&r=<?=$_GET['r']?>&g=" + gap; // redirect
            }
            return false;
        });
    });
</script>


    
<h3>Longevity -Run ID <?php printf("%06d", $id); ?></h3>
       
<ul class="tab">
    <li><a href="index.php?q=longevity&p=rundqm&id=<?=$id?>&r=config&g=<?=$currentGapId?>">Summary</a></li>
    <li><a href="index.php?q=longevity&p=rundqm&id=<?=$id?>&r=voltages&g=<?=$currentGapId?>">Voltages</a></li>
	<li><a href="index.php?q=longevity&p=rundqm&id=<?=$id?>&r=log&g=<?=$currentGapId?>">Log file</a></li>
    <li><a href="index.php?q=longevity&p=rundqm&id=<?=$id?>&r=monitoring&g=<?=$currentGapId?>">Monitoring</a></li>
    
    <li><a href="index.php?q=longevity&p=rundqm&id=<?=$id?>&r=qint&g=<?=$currentGapId?>">Integrated Charge</a></li>
    <li><a href="index.php?q=longevity&p=rundqm&id=<?=$id?>&r=plots&g=<?=$currentGapId?>">Plots</a></li>
	<li style="float: right"><form style="margin-top: 3px; margin-right: 3px;"><select name="channelSelect" id="channelSelect"><?php echo $detector_option_form; ?><select></form></li>
</ul>
       
<br />
    
<?php 
if(isset($_GET['r'])) require 'dqm/'.$_GET['r'].'.php';
else require 'dqm/config.php';
// 
?>

