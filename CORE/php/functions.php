<?php
require_once 'const.php';

function db_connect() {
	
	global $dbh, $dbhDIP;

	try {

		$dbh = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);
		$dbhDIP = new PDO("mysql:host=128.141.143.223;dbname=dip", "root", "UserlabDIP++");
	}
	catch(PDOException $e) {
		
		die("Database connection failed: ".$e->getMessage());
	}
}



function db_disconnect() {

	global $dbh;
	//global $dbhDIP;
	
	$dbh = null;
	//$dbhDIP = null;
}


function runCommand($cmd, &$return, $bkg = true) {
    
    //putenv("LD_LIBRARY_PATH=/home/webdcs/software/webdcs/CAEN/lib:/usr/local/root/lib");
	if($bkg) $cmd .= " > /dev/null 2>&1 &";
	exec($cmd, $return);
}




function sendMail($sub, $msg, $rec) {
    
    mail($rec, str_replace("~", " ", $sub), str_replace("~", " ", $msg));
}

function setting($setting, $value = null) {
  
    global $dbh;
    
    if(isset($value)) {
      
        $sth = $dbh->prepare("UPDATE settings SET value = :value WHERE setting = :setting");
        $sth->execute(array(':setting' => $setting, ':value' => $value)); 
    }
    else {
  
        $sth = $dbh->prepare("SELECT value FROM settings WHERE setting = :setting");
        $sth->execute(array(':setting' => $setting)); 
        return $sth->fetchColumn();
    }
}

