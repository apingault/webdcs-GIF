<?php

/** DATABASE SETTINGS **/
define('DB_NAME', 'webdcs');
define('DB_USER', 'root');
define('DB_PASSWORD', 'UserlabGIF++');
define('DB_HOST', 'localhost');

try {
    $dbh = new PDO("mysql:host=".DB_HOST.";dbname=webdcs", DB_USER, DB_PASSWORD);
}
catch(PDOException $e) {
    die("Database connection failed: ".$e->getMessage());
}

// READ INI FILE
$ini_array = parse_ini_file("cronJob.ini", true);
$hvscan_daq = $ini_array['hvscan_daq'];

$trolley = $hvscan_daq['trolley'];
$slot = $hvscan_daq['slot'];
$maxtriggers = $hvscan_daq['maxtriggers'];
$waiting_time = $hvscan_daq['waiting_time'];
$measuring_intval = $hvscan_daq['measuring_intval'];
$comments = $hvscan_daq['comments'];
$trigger = $hvscan_daq['trigger'];
$lastHV = $hvscan_daq['lastHV'];
$RPC_mode = "double_gap";
$typescan = "rate";
$type = "daq";
$beam = 0;



// READ DIP VALUES
$DIP = file_get_contents("/var/operation/RUN/pt");
$i = 0;
foreach(explode("\n", $DIP) as $d){
    if($i == 4) $source = $d;
    if($i == 5) $attU = $d;
    if($i == 6) $attD = $d;
    $i++;
}


// GET TROLLEY POSITION
$q = $dbh->prepare("SELECT id FROM position WHERE trolley_id = '".$trolley."' ORDER BY id DESC LIMIT 1");
$q->execute();
$res = $q->fetch();
$pos = $res['id'];


// GET LATEST SCAN ID
$q = $dbh->prepare("SELECT * FROM hvscan ORDER BY id DESC LIMIT 0, 1");
$q->execute();
$f = $q->fetch();
$id = (int)$f['id'] + 1;


$now = time();
$maxHVPoints = count($hvscan_daq['voltages']);


// Write HVscan profile to database (generic)
$sth1 = $dbh->prepare("INSERT INTO hvscan (id, trolley, time_start, type, beam, source, attU, attD, waiting_time, position, comments, maxHVPoints, status, RPC_mode, measure_intval, last_HV)
                     VALUES (:id, :trolley, :time_start, :type, :beam, :source, :attU, :attD, :waiting_time, :position, :comments, :maxHVPoints, 1, :RPC_mode, :measure_intval, :last_HV) "); 
$sth1->bindParam(':id', $id); 
$sth1->bindParam(':trolley', $trolley); 
$sth1->bindParam(':type', $type); 
$sth1->bindParam(':time_start', $now);
$sth1->bindParam(':beam', $beam); 
$sth1->bindParam(':source', $source); 
$sth1->bindParam(':attU', $attU); 
$sth1->bindParam(':attD', $attD); 
$sth1->bindParam(':waiting_time', $waiting_time); 
$sth1->bindParam(':position', $pos); 
$sth1->bindParam(':comments', $comments); 
$sth1->bindParam(':maxHVPoints', $maxHVPoints);
$sth1->bindParam(':measure_intval', $measuring_intval);
$sth1->bindParam(':RPC_mode', $RPC_mode);
$sth1->bindParam(':last_HV', $lastHV);
//$sth1->execute();
        

$sth2 = $dbh->prepare("INSERT INTO hvscan_DAQ (id, type, trigger_mode, min_time) VALUES (:id, :type, :trigger_mode, :min_time) ");
$sth2->bindParam(':id', $id);
$sth2->bindParam(':type', $typescan);
$sth2->bindParam(':trigger_mode', $trigger);
//$sth2->execute();

foreach($hvscan_daq['voltages'] as $i => $HV) {
    
    foreach($hvscan_daq['detectors'] as $det) {
        
        echo $det;
        echo '\n';
        $sth1 = $dbh->prepare("INSERT INTO hvscan_VOLTAGES (scanid, detectorid, HVPoint, HV, maxtriggers) VALUES (:scanid, :detectorid, :HVPoint, :HV, :maxtriggers) "); 
        $sth1->bindParam(':scanid', $id); 
        $sth1->bindParam(':detectorid', $det);
        $sth1->bindParam(':HVPoint', $i);
        $sth1->bindParam(':HV', $HV);
        $sth1->bindParam(':maxtriggers', $maxtriggers);
        //$sth1->execute(); 
        
    }
}




?>
