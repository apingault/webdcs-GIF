<?php

$sth1 = $dbh->prepare("SELECT h.*, d.type AS scantype FROM hvscan h, hvscan_DAQ d WHERE h.type = 'daq' AND h.id = d.id ORDER BY id DESC");
$sth1->execute();
$hvscans = $sth1->fetchAll();


foreach($hvscans as $hv) {
	
	
	$sth2 = $dbh->prepare("SELECT d.chamber, d.trolley, d.slot FROM detectors d, hvscan_VOLTAGES h WHERE h.detectorid = d.id AND h.scanid = ".$hv['id']." GROUP BY d.chamber");
$sth2->execute();
$chambers = $sth2->fetchAll();

echo $hv['id'].' ';
foreach($chambers as $ch) {
	
	//echo $ch["chamber"].' ';
	echo $ch["trolley"].'-'.$ch["slot"]." ";
}


echo '<br />';
	
	
}


?>
