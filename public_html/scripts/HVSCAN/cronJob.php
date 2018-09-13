<?php

if(!isset($_POST['submit'])) {
    
    ?>
<form action="" method="POST">
        <input type="submit" name="submit" value="Start scan" />
</form>
    <?php
    //die("end");
}


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
$ini_array = parse_ini_file("/home/webdcs/software/webdcs/public_html/scripts/HVSCAN/cronJob.ini", true);
$hvscan_daq = $ini_array['hvscan_daq'];

$trolley = $hvscan_daq['trolley'];
$slot = $hvscan_daq['slot'];
$maxtriggers = $hvscan_daq['maxtriggers'];
$waiting_time = $hvscan_daq['waiting_time'];
$measuring_intval = $hvscan_daq['measuring_intval'];
$measure_time = $hvscan_daq['measure_time'];
$comments = $hvscan_daq['comments'];
$trigger = $hvscan_daq['trigger'];
$lastHV = $hvscan_daq['lastHV'];
$RPC_mode = "single_gap";
$typescan = "rate";
$type = "daq";
$maxHVPoints = $hvscan_daq['maxHVpoints'];
$beam = 0;
$now = time();


// READ DIP VALUES
$DIP = file_get_contents("/var/operation/RUN/pt");
$i = 0;
foreach(explode("\n", $DIP) as $d){
    if($i == 4) $source = $d;
    if($i == 5) $attU = $d;
    if($i == 6) $attD = $d;
    $i++;
}

// GET LATEST SCAN ID
$q = $dbh->prepare("SELECT * FROM hvscan ORDER BY id DESC LIMIT 0, 1");
$q->execute();
$f = $q->fetch();
$id = (int)$f['id'] + 1;

// Append ID to run flag registry
$q = $dbh->prepare("SELECT * FROM physics_flags WHERE id = 2");
$q->execute();
$f = $q->fetch();
$ids = $f['runids'];
$ids .= ",".$id;
$q = $dbh->prepare("UPDATE physics_flags SET runids = '".$ids."' WHERE id = 2");
//$q->execute();



// Write HVscan profile to database (generic)
$sth1 = $dbh->prepare("INSERT INTO hvscan (id,  trolley,  time_start,  type,  beam,  source,  attU,  attD,  waiting_time,  comments,  maxHVPoints, status,   RPC_mode,  measure_intval,  lastHV,  measure_time)
                                  VALUES (:id, :trolley, :time_start, :type, :beam, :source, :attU, :attD, :waiting_time, :comments, :maxHVPoints, 1,       :RPC_mode, :measure_intval, :lastHV, :measure_time) "); 
$sth1->bindParam(':id', $id); 
$sth1->bindParam(':trolley', $trolley); 
$sth1->bindParam(':type', $type); 
$sth1->bindParam(':time_start', $now);
$sth1->bindParam(':beam', $beam); 
$sth1->bindParam(':source', $source); 
$sth1->bindParam(':attU', $attU); 
$sth1->bindParam(':attD', $attD); 
$sth1->bindParam(':waiting_time', $waiting_time); 
$sth1->bindParam(':comments', $comments); 
$sth1->bindParam(':maxHVPoints', $maxHVPoints);
$sth1->bindParam(':measure_intval', $measuring_intval);
$sth1->bindParam(':RPC_mode', $RPC_mode);
$sth1->bindParam(':lastHV', $lastHV);
$sth1->bindParam(':measure_time', $measure_time);
$sth1->execute();

$sth2 = $dbh->prepare("INSERT INTO hvscan_DAQ (id, type, trigger_mode) VALUES (:id, :type, :trigger_mode) ");
$sth2->bindParam(':id', $id);
$sth2->bindParam(':type', $typescan);
$sth2->bindParam(':trigger_mode', $trigger);
$sth2->execute();


foreach($hvscan_daq['voltages'] as $i => $HV) {
    
    $volts = explode(',', $HV);
    $j = 1;
    if(count($volts) != $maxHVPoints) die("Amount of voltages not correct");
    foreach($volts as $volt) {
    
        $sth1 = $dbh->prepare("INSERT INTO hvscan_VOLTAGES (scanid, detectorid, HVPoint, HV, maxtriggers) VALUES (:scanid, :detectorid, :HVPoint, :HV, :maxtriggers) "); 
        $sth1->bindParam(':scanid', $id); 
        $sth1->bindParam(':detectorid', $i);
        $sth1->bindParam(':HVPoint', $j);
        $sth1->bindParam(':HV', $volt);
        $sth1->bindParam(':maxtriggers', $maxtriggers);
        $sth1->execute(); 
        $j++;
    }
}

// Start scan
putenv("LD_LIBRARY_PATH=/home/webdcs/software/webdcs/CAEN/lib:/usr/local/root/lib");
exec("/home/webdcs/software/webdcs/CAEN/bin/HVscan ".$id." > /dev/null 2>&1 &", $t);


?>
