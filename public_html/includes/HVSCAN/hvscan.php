<?php
if(!defined('INDEX')) die("Access denied");

require_once 'functions.php';
loadCSS("tab.css");

// Get Scan ID and retrieve information
$id = $_GET['id'];
$HV =  (isset($_GET['HV'])) ? $_GET['HV'] : 1;
$idstring = sprintf("%06d", $id);
$sth1 = $DB['MAIN']->prepare("SELECT * FROM hvscan WHERE id = $id");
$sth1->execute();
global $hvscan;
$hvscan = $sth1->fetch();
$dir = sprintf("/var/operation/HVSCAN/%06d", $id);



// Check if ID is valid
if($sth1->rowCount() == 0) {
    echo '<div class="content"><div class="error">Error: scan ID not found</div></div>';
    exit(1);
}

// Get scan type
if($hvscan['type'] == 'current') {
    $TYPESCAN = 'current';
    $TITLE = 'CURRENT';
}
elseif($hvscan['type'] == 'daq') {
    $TYPESCAN = 'daq';
    $TITLE = 'DAQ';
}

// Get specific scan information
if($TYPESCAN == "daq") {
   
    $sth1 = $DB['MAIN']->prepare("SELECT * FROM hvscan_DAQ WHERE id = $id");
    $sth1->execute();
    $hvscan_spec = $sth1->fetch();
    $scantype_spec = $hvscan_daq_types[$hvscan_spec['type']];
}
elseif($TYPESCAN == "current") {

    $sth1 = $DB['MAIN']->prepare("SELECT * FROM hvscan_CURRENT WHERE id = $id");
    $sth1->execute();
    $hvscan_spec = $sth1->fetch();
    $scantype_spec = $hvscan_current_types[$hvscan_spec['type']];
}

// HV point options for DQM
$hvpoints = '<option disabled="disabled">Select HV</option>';
for($i = 1; $i <= $hvscan['maxHVPoints']; $i++) {
    $sel = (isset($_GET['HV']) and $_GET['HV'] == $i) ? 'selected="selected"' : "";
    $hvpoints .= '<option '.$sel.' value="'.$i.'">HV point '.$i.'</option>';
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
        $('#HVSelect').on('change', function () {
            var hv = $(this).val(); // get selected value
            if(hv) { // require a URL
                window.location = "index.php?q=hvscan&p=hvscan&id=<?=$id?>&r=<?=$_GET['r']?>&HV=" + hv; // redirect
            }
            return false;
        });
    });
</script>

    
<h3>HVscan - ID <?php printf("%06d", $id); ?></h3>
       
<ul class="tab" style="width: 100%">
    <li><a href="index.php?q=hvscan&p=hvscan&id=<?=$id?>&r=overview">Scan overview</a></li>
    <li><a href="index.php?q=hvscan&p=hvscan&id=<?=$id?>&r=voltages">Voltages</a></li>
    <li><a href="index.php?q=hvscan&p=hvscan&id=<?=$id?>&r=log">Log file</a></li>
    <li><a href="index.php?q=hvscan&p=hvscan&id=<?=$id?>&r=log_caen">CaenLog</a></li>
    <li><a href="index.php?q=hvscan&p=hvscan&id=<?=$id?>&r=rootjs">ROOT Browser</a></li>
    <?php if($TYPESCAN == "daq") { ?><li><a href="index.php?q=hvscan&p=hvscan&id=<?=$id?>&r=maskingtool">Masking tool</a></li><?php } ?>
	<li style="float: right"><form style="margin-top: 3px; margin-right: 3px;"><select name="HVSelect" id="HVSelect"><?php echo $hvpoints; ?><select></form></li>
	<?php if($TYPESCAN == "daq") { ?><li style="float: right;"><a href="index.php?q=hvscan&p=hvscan&id=<?=$id?>&r=dqm_daq&HV=<?=$HV?>">DQM DAQ</a></li><?php }?>
	<li style="float: right;"><a href="index.php?q=hvscan&p=hvscan&id=<?=$id?>&r=dqm_dip&HV=<?=$HV?>">DQM DIP</a></li>
    <li style="float: right;"><a href="index.php?q=hvscan&p=hvscan&id=<?=$id?>&r=dqm_log&HV=<?=$HV?>">DQM Log</a></li>
	<li style="float: right;"><a href="index.php?q=hvscan&p=hvscan&id=<?=$id?>&r=dqm_caen&HV=<?=$HV?>">DQM CAEN</a></li>
</ul>
       
<br />
    
<?php
if(isset($_GET['r'])) require_once 'hvscan/'.$_GET['r'].'.php';
else require_once 'hvscan/overview.php';
?>

