<?php 
//header('Content-Type: application/json');

function getScanResults($post) {
	
	global $dbh, $dbhLONG;
	$output = array();
	
	//$output = array("a","b","c","d");
	
	$id = $post["scanid"];
	$chamber = $post["chamber"];
	$scan_mode = $post["scanMode"];
	$idstring = sprintf("%06d", $id);
	
	// Retrieve chamber
	$sth1 = $dbh->prepare("SELECT d.chamber, d.trolley, d.slot, d.trolley FROM detectors d WHERE d.chamber = '$chamber'");
	$sth1->execute();
	$chamber = $sth1->fetch();

	// Get rates and currents
	$sth1 = $dbh->prepare("SELECT * FROM `RES_LONG_CMS-RE` WHERE REF_scanid = $id AND chamber = '".$chamber['chamber']."' AND scan_mode = '$scan_mode' LIMIT 1");
	$sth1->execute();
	$result = $sth1->fetchAll();
	
	$rates = "<b>Rate [Hz/cm²]</b><br />";
	$currents = "<b>Current [uA]</b><br />";
	
	if(count($result) == 1) {
	
		$result = $result[0];
		$rates .= number_format($result["rate_tot"], 2).'<br />';
		$rates .= number_format($result["rate_partitionA"], 2).'<a href="/HVSCAN/'.$idstring.'/HV'.$result["REF_hvpoint"].'/DAQ/RPC_Mean_Noise_T'.$chamber['trolley'].'S'.$chamber['slot'].'_A.png"><span class="ui-icon ui-icon-extlink" style="display: inline-block; width: 12px; height: 12px;"></span></a><br />';
		$rates .= number_format($result["rate_partitionB"], 2).'<a href="/HVSCAN/'.$idstring.'/HV'.$result["REF_hvpoint"].'/DAQ/RPC_Mean_Noise_T'.$chamber['trolley'].'S'.$chamber['slot'].'_B.png"><span class="ui-icon ui-icon-extlink" style="display: inline-block; width: 12px; height: 12px;"></span></a><br />';
		$rates .= number_format($result["rate_partitionC"], 2).'<a href="/HVSCAN/'.$idstring.'/HV'.$result["REF_hvpoint"].'/DAQ/RPC_Mean_Noise_T'.$chamber['trolley'].'S'.$chamber['slot'].'_C.png"><span class="ui-icon ui-icon-extlink" style="display: inline-block; width: 12px; height: 12px;"></span></a><br />';


		$currents .= number_format($result["current_tot"], 2).'<br />';
		$currents .= number_format($result["current_BOT"], 2).'<a href="/HVSCAN/'.$idstring.'/HV'.$result["REF_hvpoint"].'/CAEN/ADC-'.$result['chamber'].'-BOT.png"><span class="ui-icon ui-icon-extlink" style="display: inline-block; width: 12px; height: 12px;"></span></a><br />';
		$currents .= number_format($result["current_TN"], 2).'<a href="/HVSCAN/'.$idstring.'/HV'.$result["REF_hvpoint"].'/CAEN/ADC-'.$result['chamber'].'-TN.png"><span class="ui-icon ui-icon-extlink" style="display: inline-block; width: 12px; height: 12px;"></span></a><br />';
		$currents .= number_format($result["current_TW"], 2).'<a href="/HVSCAN/'.$idstring.'/HV'.$result["REF_hvpoint"].'/CAEN/ADC-'.$result['chamber'].'-TW.png"><span class="ui-icon ui-icon-extlink" style="display: inline-block; width: 12px; height: 12px;"></span></a><br />';
	}
	else {
		
		$rates .= "n/a<br />n/a<br />n/a<br />n/a";
		$currents .= "n/a<br />n/a<br />n/a<br />n/a";
	}
	
	// Integrated charge
	$sth1 = $dbh->prepare("SELECT * FROM hvscan WHERE id = $id LIMIT 1");
	$sth1->execute();
	$time = $sth1->fetch();
	$time = $time['time_start'];
	
	$table = "CORR_QINT_".$chamber['chamber'];
	$sth1 = $dbhLONG->prepare("SELECT * FROM `".$table."` WHERE timestamp > $time ORDER BY timestamp ASC LIMIT 1");
	$sth1->execute();
	$qint = $sth1->fetch();
	$qint = $qint['QINT_TOT'];
	
	// Charge deposition (DG_WP)
	$sth1 = $dbh->prepare("SELECT * FROM `RES_LONG_CMS-RE` WHERE REF_scanid = $id AND chamber = '".$chamber['chamber']."' AND scan_mode = '$scan_mode' LIMIT 1");
	$sth1->execute();
	$result = $sth1->fetch();
	$chdep = $result['charge_dep'];
	
	// aux
	$aux = number_format($qint, 2).'<br />'.number_format($chdep, 2);
	
	array_push($output, $rates);
	array_push($output, $currents);
	array_push($output, $aux);
	
	echo json_encode($output);
}

if (isset($_POST['getScanResults'])) getScanResults($_POST);

else if (isset($_POST['saveScan'])) {
	
	$id = filter_input(INPUT_POST, 'scanid');
	$approved = filter_input(INPUT_POST, 'approved');
    $comments = filter_input(INPUT_POST, 'comment');
	
	if($approved == 1) $status = 3;
	else $status = 0;

    $sth1 = $dbh->prepare("UPDATE hvscan SET comments = :comments, status = :status WHERE id = ".$id);
    $sth1->bindParam(':comments', htmlspecialchars($comments), PDO::PARAM_STR);
	$sth1->bindParam(':status', $status);
    $sth1->execute();

}


//getScanResults();
?>