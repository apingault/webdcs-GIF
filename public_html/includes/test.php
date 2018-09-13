<?php


$sth1 = $dbh->prepare("SELECT * FROM hvscan_daq WHERE id <=1141");
$sth1->execute();
$chambers = $sth1->fetchAll();


foreach($chambers as $s) {
    
    
    $sth1 = $dbh->prepare("SELECT * FROM voltages WHERE hvscan_id = ".$s['id']);
$sth1->execute();
$v = $sth1->fetchAll();

$i=1;

$dir = sprintf("/var/operation/HVSCAN/%06d", $s['id']);
$path = sprintf($dir."/*.root", $id);
$files = glob($path);


if($s["id"] == 1112)    continue;


foreach($files as $d) {
    
    
   // $new = str_replace(".root", "_HV".$i."_DAQ.root", $d);
   // system("mv ".$d." ". $new);
    
    $i++;
}


//break;



/*
foreach($v as $HV) {
    
    echo $HV['detector_id'].'<br />';
    
    /*
    for($i=1; $i < 21; $i++) {
        
        if($HV['HV'.$i] == NULL)  continue;
        
       //echo "HV".$i." \t V=".$HV['HV'.$i]." <br />";
     //  echo "INSERT INTO hvscan_VOLTAGES (scanid, detectorid, HVPoint, HV, maxtriggers) VALUES (".$s['id'].", ".$HV['detector_id'].", $i, ".$HV['HV'.$i].", ".$s['maxtriggers'].")";
      //  echo "<br />";
        
   //    $sth2 = $dbh->prepare("INSERT INTO hvscan_VOLTAGES (scanid, detectorid, HVPoint, HV, maxtriggers) VALUES (".$s['id'].", ".$HV['detector_id'].", ".$i.", ".$HV['HV'.$i].", ".$s['maxtriggers'].")");
        //$sth2->execute();
        //break;
        
    }
     * 
     
   // break;
    // scanid, detectorid, HVPoint, HV, masked, maxtriggers 
}
echo '<br /><br />';
*/

//break;

    
   // $f = sprintf("/var/operation/HVSCAN/%06d/log.txt", $s['id']);


    
}


?>
