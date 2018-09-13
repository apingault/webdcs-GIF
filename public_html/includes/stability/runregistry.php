<script>
	
var ids = []
var chamber_default = "RE2-2-NPD-BARC-9"
var scan_mode_default = "DG_WP"
	
function loadContent(scanId, chamber, scanMode) {

	$.ajax({
		url: 'index.php?q=ajax&p=longevityresults',
		type: 'post',
		data: { "getScanResults": "1", "scanid": scanId, "chamber": chamber, "scanMode": scanMode },
		dataType: "JSON",
		success: function(response) {

			$("#rate_" + scanId).html(response[0]);
			$("#current_" + scanId).html(response[1]);
			$("#qint_" + scanId).html(response[2]);
		}
	});
}

function loadContentAll() {


	for (var x in ids) {
		loadContent(ids[x], chamber_default, scan_mode_default)
	}
}
	
$(document).ready(function() {

	// Load initial values
	loadContentAll()
	
	// Displau DQM images
	$('.dqmImage').magnificPopup({
		delegate: 'a',
		type: 'image',
		gallery: {
			enabled: true,
			navigateByImgClick: true,
			preload: [0,1] // Will preload 0 - before current, and 1 after the current image
		},
	});
	
	$('#default_scan_mode').on('change', function() {
	
		$('.changeChamber option[value=default]').attr('selected','selected');
		$('.changeScanMode option[value=default]').attr('selected','selected');
		scan_mode_default = $('#default_scan_mode').val()	
		chamber_default = $('#default_chamber').val()
		loadContentAll()
	});
	
	$('#default_chamber').on('change', function() {
	
		$('.changeChamber option[value=default]').attr('selected','selected');
		$('.changeScanMode option[value=default]').attr('selected','selected');
		scan_mode_default = $('#default_scan_mode').val()	
		chamber_default = $('#default_chamber').val()
		loadContentAll()
	});
	
	$('.changeChamber').on('change', function() {
	
		var chamber = $(this).val()
		var scanId = $(this).attr('scanid')
		var scanMode = $('#changeScanMode_' + scanId).val()	
		loadContent(scanId, chamber, scanMode)
	});
	
	$('.changeScanMode').on('change', function() {
		
		var scanId = $(this).attr('scanid')
		var chamber = $('#changeChamber_' + scanId).val()
		var scanMode = $(this).val()
		loadContent(scanId, chamber, scanMode)
	});
	
	$('.saveScan').on('click', function() {
		
		var scanid = $(this).attr('scanid')
		var comment = $('#comment_' + scanid).val()
		var approved = ($('#approved_' + scanid).is(":checked")) ? "1" : "0"
		
		
		if(approved == "1") $("#hvscan_status_" + scanid ).html('<font color="green"><b>APPROVED</b></font>');
		else $('#hvscan_status_' + scanid).html('<font color="blue"><b>FINISHED</b></font>');


		
		$.ajax({
			url: 'index.php?q=ajax&p=longevityresults',
			type: 'post',
			data: { "saveScan": "1", "scanid": scanid, "comment": comment, "approved": approved },
			success: function(response) {
				alert("Changes saved.")
		}
		
		
	});

	});
	
	
	
	
});
</script>
<?php


// DEFAULT VALUES
global $chamber_default;
global $scan_mode_default;
$chamber_default = "RE2-2-NPD-BARC-9";
$scan_mode_default = "DG_WP";

global $longevity_daily_scan_modes;
$longevity_daily_scan_modes = array();
$longevity_daily_scan_modes['DG_WP'] = "DG - WP";
$longevity_daily_scan_modes['SG_BOT_WP'] = "SG - BOT -WP";
$longevity_daily_scan_modes['SG_TOP_WP'] = "SG - TOP - WP";
$longevity_daily_scan_modes['SG_TN_WP'] = "SG - TOPN - WP";
$longevity_daily_scan_modes['SG_TW_WP'] = "SG - TOPW - WP";
$longevity_daily_scan_modes['DG_STBY'] = "DG - STBY";
$longevity_daily_scan_modes['SG_BOT_STBY'] = "SG - BOT -STBY";
$longevity_daily_scan_modes['SG_TN_STBY'] = "SG - TOPN - STBY";
$longevity_daily_scan_modes['SG_TW_STBY'] = "SG - TOPW - STBY";

function getFormattedStatusHVScan($status, $id) {
	
	switch($status) {
        case '0' : return '<font color="blue"><b>FINISHED</b></font>'; break;
		case '1' : return '<font><b>ONGOING</b></font>'; break;
        case '2' : return '<font color="red"><b>KILLED</b></font>'; break;
        case '3' : return '<font color="green"><b>APPROVED</b></font>'; break;
		case '4' : return '<font><b>RESUMED</b></font>'; break;
	}
}

function showScanDetailed($id) {

	$idstring = sprintf("%06d", $id);
	global $dbh;
	global $longevity_daily_scan_modes;
	global $chamber_default;
	global $scan_mode_default;
	//echo $scan_mode_default;
	// Retrieve scanned chambers
	$sth1 = $dbh->prepare("SELECT * FROM hvscan WHERE id = ".$id);
	$sth1->execute();
	$scan = $sth1->fetch();

	
	// Retrieve scanned chambers
	$sth1 = $dbh->prepare("SELECT d.chamber, d.trolley, d.slot, d.trolley FROM detectors d, hvscan_VOLTAGES h WHERE h.detectorid = d.id AND h.scanid = ".$id." GROUP BY d.chamber");
	$sth1->execute();
	$chambers = $sth1->fetchAll();
	

	echo '<table class="topalign" cellpadding="" cellspacing="">';
	echo '<tr style="color: #424242;">';

	// SELECTION
	echo '<td style="width: 165px; line-height: 16px;">';
	echo '<select class="changeChamber" scanid="'.$id.'" id="changeChamber_'.$id.'" style="width: 150px; height: 15px; font-size: 10px;">';
		echo '<option value="default" disabled="disabled">Select chamber</option>';
		foreach($chambers as $chamber) echo '<option value="'.$chamber['chamber'].'">'.$chamber['chamber'].'</option>';
	echo '</select>';	
	
	echo '<select class="changeScanMode" scanid="'.$id.'" id="changeScanMode_'.$id.'"  style="width: 150px; height: 15px; font-size: 10px; margin-top: 5px;">';
		echo '<option value="default" disabled="disabled">Select scan mode</option>';
		foreach($longevity_daily_scan_modes as $key => $scan_mode) echo '<option value="'.$key.'">'.$scan_mode.'</option>';
	echo '</select>';	
	
	echo "<br /><br />";
	echo "<a target=\"_blank\" href=\"index.php?q=hvscan&p=hvscan&id=".$id."\">&raquo; Go to scan page</a>";
	
	echo '</td>';

	
	// RATES AND CURRENTS
	echo '<td style="width: 80px; line-height: 16px; padding-left: 5px; border-left: 2px solid #e8e8e8;">';
	echo '<b>Partition/Gap</b><br />Total<br />A/BOT<br />B/TOPN<br />C/TOPW';
	echo '</td>';
	
	// RATES
	echo '<td style="width: 80px; line-height: 16px;">';
	echo '<div class="dqmImage" id="rate_'.$id.'"></div>'; 
	echo '</td>';

	// CURRENTS
	echo '<td style="width: 80px; line-height: 16px;">';
	echo '<div class="dqmImage" id="current_'.$id.'"></div>'; 
	echo '</td>';
	
	// HOMOGENITY FACTOR
	echo '<td style="width: 80px; line-height: 16px;">';
	echo '<div class="dqmImage" id="homogenity_'.$id.'"><b>Homogenity</b><br />n/a<br />n/a<br />n/a<br />n/a</div>'; 
	echo '</td>';
	
	echo '<td style="width: 120px; padding-left: 5px; border-left: 2px solid #e8e8e8; line-height: 16px;">';
	echo 'Int. Charge [mC/cm²]:<br />Charge Dep. [mC/hit]:<br />';
	echo '</td>';
	
	echo '<td style="width: 100px; line-height: 16px;">';
	echo '<div id="qint_'.$id.'"></div>';
	echo '</td>';
	
	echo '<td style="width: 20px; line-height: 16px;">';
	echo '';
	echo '</td>';
	
	echo '<td style="width: 200px; border-left: 2px solid #e8e8e8; padding-left: 5px;">';
	echo '<textarea id="comment_'.$id.'" style="font-size: 10px; width: 100%; height: 45px;">'.$scan['comments'].'</textarea><br />';
	$checked = ($scan['status'] == 3) ? 'checked="checked"' : '';
	echo '<label><input '.$checked.' id="approved_'.$id.'" style="margin-top: 5px; vertical-align:bottom;" type="checkbox" name="aproved" /> Approved</label> <input style="margin-left: 20px; margin-top: 5px;" class="saveScan" scanid="'.$id.'" type="submit" name="submit" value="Save changes" />';
	echo '</td>';	
	
	echo '</tr>';
	
	echo '</tr></table>';
	
	// Load the default values
	echo "<script>ids.push(".$id.")</script>";
	// loadContent(".$id.", '".$chamber_default."', '".$scan_mode_default."');
}

$sth1 = $dbh->prepare("SELECT * FROM stability ORDER BY id DESC");
$sth1->execute();
$longevity_runs = $sth1->fetchAll();


// Get all scans
$sth1 = $dbh->prepare("SELECT * FROM hvscan WHERE label = 'longevity_current' OR label = 'longevity_noise' OR label = 'longevity_daily' OR label = 'longevity_rate' ORDER BY id DESC");
$sth1->execute();
$longevity_scans = $sth1->fetchAll();


?>

<script type="text/javascript">
$( document ).ready(function() {

	$(".scans_run").hide(); // hide all on page load

	$('.showRunScans').click(function() {
		$(".scans_" + this.id).toggle("fast");
	});
	
	$('.showRunScansDetailed').click(function() {
		$("#detailed_" + this.id).toggle("fast");
	});
	
	$('#showAll').click(function() {
		$(".scans_run").show();
	});
	
	$('#hideAll').click(function() {
		$(".scans_run").hide();
	});
});
</script>


<style>
table.topalign td { vertical-align: top }
	
</style>

<h3>Longevity run registry</h3>

&raquo <a href="index.php?q=longevity&p=makeplots">Make plots</a><br />
&raquo <a id="showAll" href="#">Show all</a> / <a id="hideAll" href="#">Hide all</a><br />

<br /><br />

<?php

$chs = array("RE2-2-NPD-BARC-9", "RE4-2-CERN-165", "RE4-2-CERN-166", "RE2-2-NPD-BARC-8");


echo 'Default view: <select id="default_scan_mode" style="width: 120px; height: 15px; font-size: 10px;">';
	echo '<option selected="selected" disabled="disabled">Select scan mode</option>';
	foreach($longevity_daily_scan_modes as $key => $scan_mode) echo '<option value="'.$key.'">'.$scan_mode.'</option>';
echo '</select>';

echo ' <select id="default_chamber" style="width: 120px; height: 15px; font-size: 10px;">';
	echo '<option selected="selected" disabled="disabled">Select chamber</option>';
	foreach($chs as $ch) echo '<option value="'.$ch.'">'.$ch.'</option>';
echo '</select>';
echo '<br /><br />';
?>
 
<style>
	.ui-icon { display: inline-block; }

	
</style>

Legend: <span style="color: #0000FF">Daily longevity scan</span> <span style="color: #FF0000">rate HV scan</span> <span style="color: #00FFFF">noise scan</span> <span style="color: #00FF00">current scan</span>
<table class="" cellpadding="5px" cellspacing="0">

    <thead style="background-color: #4d90fe;">
	
        <tr>

        <td width="12%">Run ID</td>
        <td width="20%">Start time</td>
        <td width="20%">End time/Last action</td>
        <td width="25%">Duration</td>
        <td width="30%">Comment</td>
        <td width="13%">Status</td>
        
        </tr>
    </thead>
    
    <tbody>
    <?php
        
    $i = 0;
    foreach ($longevity_runs as $run) {
            
        $class = ($i%2 == 0) ? 'evenrow' : 'oddrow';
        //echo '<tr data-href="index.php?q=longevity&p=rundqm&id='.$run['id'].'" class="'.$class.' clickable-row">';
        echo '<tr id="runid_'.$run['id'].'" class="showRunScans oddrow">';
        printf('<td>%06d<a href="index.php?q=longevity&p=rundqm&id=%d"><span class="ui-icon ui-icon-extlink"></span></a></td>', $run['id'], $run['id']);
        echo '<td>'.date('Y-m-d H:i', $run['time_start']).'</td>';
        echo '<td>'.date('Y-m-d H:i', $run['last_action']).'</td>';
        echo '<td>'.secondsToTime($run['last_action'] - $run['time_start']).'</td>';
        echo '<td>'.$run['comments'].'</td>';
        echo '<td>'.getFormattedStatus($run['status']).'</td>';
        echo '</tr>';
		
		foreach($longevity_scans as $scan) {
			
			if($scan['label'] == 'longevity_daily') $color = "#0000FF";
			if($scan['label'] == 'longevity_rate') $color = "#FF0000";
			if($scan['label'] == 'longevity_noise') $color = "#00FFFF";
			if($scan['label'] == 'longevity_current') $color = "#00FF00";
			


			// do not display the scans not taken during this longevity run
			if($scan['time_start'] < $run['time_start'] || $scan['time_start'] > $run['last_action']) continue;
			
			// First row: HVscan header
			echo '<tr class="scans_runid_'.$run['id'].' scans_run showRunScansDetailed" id="scanid_'.$scan['id'].'">';
			printf('<td style="color: '.$color.'; border-left: 1px solid #e8e8e8; border-top: 1px solid #e8e8e8;">%06d<a target=\"_blank\" href="index.php?q=hvscan&p=hvscan&id=%d"><span class="ui-icon ui-icon-extlink"></span></a></td>', $scan['id'], $scan['id']);
			echo '<td style="border-top: 1px solid #e8e8e8;">Start: '.date('Y-m-d H:i', $scan['time_start']).'</td>';
			echo '<td style="border-top: 1px solid #e8e8e8;">'.date('Y-m-d H:i', $scan['time_end']).'</td>';
			echo '<td style="border-top: 1px solid #e8e8e8;">'.secondsToTime($scan['time_end'] - $scan['time_start']).'</td>';
			echo '<td style="border-top: 1px solid #e8e8e8;">AttU:'.effAttenuation($scan['attU']).' ['.$scan['attU'].'] - AttD: '.effAttenuation($scan['attD']).' ['.$scan['attD'].']</td>';
			echo '<td style="border-right: 1px solid #e8e8e8; border-top: 1px solid #e8e8e8;"><span id="hvscan_status_'.$scan['id'].'">'.getFormattedStatusHVScan($scan['status']).'</span></td>';
			echo '</tr>';
			
			// Second row: all scan details
			echo '<tr style="padding: 0px;" class="scans_run" id="detailed_scanid_'.$scan['id'].'">';
			
			
			
			echo '<td style="padding: 5px; border-top: 1px dashed #e8e8e8; border-left: 1px solid #e8e8e8; border-right: 1px solid #e8e8e8;" colspan="6">';
			showScanDetailed($scan['id']);
			echo '</td>';
		
			echo '</tr>';
		}
		
        $i++;
    }
    ?>
    
    </tbody>
</table>

