<?php 
require_once 'scripts.php';

function sendMail($sub, $msg, $rec) {
    
    mail($rec, $sub, str_replace("~", " ", $msg));
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


// GET LATEST SCAN ID
$q = $dbh->prepare("SELECT * FROM monitoring");
$q->execute();
$notifications = $q->fetchAll();

foreach($notifications as $notification) {
    
    $args = explode(" ", $notification['arguments']);
    $return = call_user_func_array($notification['script'], $args);
    $now = time();
    
    if($return != FALSE) { // NOT OK
        
        $not = false; // true if notification needs to be send
        
        // Update status on change
        
        // Check if mail
        if($notification['status'] == 1 || ($now - $notification['status_change']) > 60*$notification['notification_interval']) $not = true;
        
        
        if($not) {
            
            $q = $dbh->prepare("UPDATE MONITORING SET status = 0, status_change = ".$now." WHERE id = ".$notification['id']);
            $q->execute();
            sendMail("WEBD MONITORING", $return, $notification['notification_addresses']);
        }
    }
    else { // OK
        
        // Update status on change
        if($notification['status'] == 0) {
            
            $q = $dbh->prepare("UPDATE MONITORING SET status = 1, status_change = ".$now." WHERE id = ".$notification['id']);
            $q->execute();
        }
    }
    
}



?>