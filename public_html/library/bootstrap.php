<?php 
if(!defined('INDEX')) die("Access denied");

if(DEVELOPMENT_ENVIRONMENT) {
    
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}



// DB connections
$DB = array();
foreach($DB_LIB as $key => $conn) {
    
    if($conn['active'] != True) {
        continue;
    }

    try {

        $DB[$key] = new PDO("mysql:host=".$conn['host'].";dbname=".$conn['db'], $conn['user'], $conn['pass']);
    }
    catch(PDOException $e) {

        die("Database ".$key." connection failed: ".$e->getMessage());
    }
}



// Load core functions
require_once (ROOT.'/library/functions.php');

// Load config and functions
require_once (ROOT.'/config/config.php');
require_once (ROOT.'/config/functions.php');


$dbh = null;
$dbh1 = null;
$dbhDIP = null;
$dbhLONG = null;
// Make database connection(s)
db_connect();
db_connect1();


// Check login or go to login page
require_once url('library/login/controllogin.class.php', 'local');
$controllogin = new ControlLogin();
if($controllogin->CheckSession()) {
    
    // CONTENT
    $q = filter_input(INPUT_GET, 'q');
    
    
    if($q == "ajax") {
		
        $p = filter_input(INPUT_GET, 'p');
        require_once (url('ajax/'.strtolower($p).'.php', 'local'));
    }
    else {

        // HEADER
        require_once(url('includes/header.php', 'local')); // require header

        if(file_exists(url('includes/'.strtolower($q).'.php', 'local'))) {

            require_once (url('includes/'.strtolower($q).'.php', 'local'));
	}
	else {
            
            if(!isset($q) || $q == "") { // default page if $q does not exist
		require_once (url($CFG['default_page'], 'local'));
            }
            else {
                echo '<div class="content">';
		msg("Page not found", "warning");
		echo '</div>';
            }
        }

	// FOOTER
	require_once(url('includes/footer.php', 'local')); // require footer
    }
}
else require_once url('library/login/login.php', 'local');



