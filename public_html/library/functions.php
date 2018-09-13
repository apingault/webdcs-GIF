<?php

function db_connect() {
	
	global $dbh;
	global $dbhDIP;
	global $dbhLONG;
	
	try {

		$dbh = new PDO("mysql:host=".DB_HOST.";dbname=webdcs", DB_USER, DB_PASSWORD);
		//$dbhLONG = new PDO("mysql:host=".DB_HOST.";dbname=LONGEVITY", DB_USER, DB_PASSWORD);
	}
	catch(PDOException $e) {
		
		die("Database connection failed: ".$e->getMessage());
	}
	
	try {
		$servername = "128.141.143.223"; // Fixed IP for webdcsdip
		//$servername = "webdcsdip.cern.ch";
		$username = "root";
		$password = "UserlabDIP++";
		$dbhDIP = new PDO("mysql:host=$servername;dbname=dip", $username, $password);
   
	}
	catch(PDOException $e) {
		echo "Connection failed: " . $e->getMessage();
	}
}

function db_connect1() {
	
	global $dbh1;
	
	try {

		//$dbh1 = new PDO("mysql:host=".DB_HOST.";dbname=DIP", DB_USER, DB_PASSWORD);
	}
	catch(PDOException $e) {
		
		die("Database connection failed: ".$e->getMessage());
	}
}


// Make good urls
function url($url, $options = FALSE) {
	
	if($options == 'local') {
		
		return ROOT . $url;
	}
	else {
		
		return DOMAIN_ROOT . $url;
	}
}

/** Check for Magic Quotes and remove them **/
function stripSlashesDeep($value) {
	
	$value = is_array($value) ? array_map('stripSlashesDeep', $value) : stripslashes($value);
	return $value;
}

function removeMagicQuotes() {
	if (get_magic_quotes_gpc()) {
		$_GET    = stripSlashesDeep($_GET   );
		$_POST   = stripSlashesDeep($_POST  );
		$_COOKIE = stripSlashesDeep($_COOKIE);
	}
}

/** Check register globals and remove them **/
function unregisterGlobals() {
    
	if (ini_get('register_globals')) {
        
		$array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
        foreach ($array as $value) {
            
			foreach ($GLOBALS[$value] as $key => $var) {
                if ($var === $GLOBALS[$key]) {
                    
					unset($GLOBALS[$key]);
                }
            }
        }
    }
}

/** Autoload any classes that are required **/
function __autoload($className) {
	
	if(file_exists( url('library' . DS . strtolower($className) . '.class.php', 'local'))) {
		
		require_once ( url('library' . DS . strtolower($className) . '.class.php', 'local'));
	}
	else die('Class not found.');
	
}

/** Load CSS OR JS files **/
function loadCSS($t) {
			
	echo '<link href="config/css/'.$t.'" rel="stylesheet" type="text/css" media="screen" />';
}
	
function loadJS($t) {
		
	echo '<script type="text/javascript"  src="config/js/'.$t.'"></script>';
}

function getCurrentRole() {
	
    global $DB;
	
	$sth1 = $DB['MAIN']->prepare("SELECT role FROM users WHERE id = :id LIMIT 1");
	$sth1->execute(array(":id" => $_SESSION['userid']));
	if($sth1->rowCount() == 0) {
    	// logout
	}
	else {
		$res = $sth1->fetch(PDO::FETCH_ASSOC);
		return $res['role'];
	}
}



function loadCore($dir = 'site') {
	
	global $dbh, $dbhDIP;
	
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
				require_once (url('includes/dip.php', 'local'));
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

function selfURL() { 

	$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : ""; 
	$protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s; 
	$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]); 
	return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
} 

function strleft($s1, $s2) { 
	return substr($s1, 0, strpos($s1, $s2)); 
}

function Object($name) {
	
	$query = mysql_query(" SELECT `content` FROM `content` WHERE `id` = '".$name."' LIMIT 1 ") or die("Database error");
	$res = mysql_fetch_array($query);
	echo html_entity_decode($res['content'], ENT_QUOTES);
}


	
function Config($name) {
	
	$query = mysql_query(" SELECT `value` FROM `config` WHERE `id` = '".$name."' LIMIT 1 ") or die("Database error");
	$res = mysql_fetch_array($query);
	return $res['value'];
}

	
	
	
	
	
	
	
	
	
	
	

// Calculates the number of detectors in a mainframe
function no_detectors($mid) {
  
    global $dbh;
  
    $sth = $dbh->prepare("SELECT count(*) FROM detectors  WHERE mid = :mid");
    $sth->execute(array(':mid' => $mid)); 
    return $sth->fetchColumn();
}

// Get or set result to the settings table
function settings($setting, $value = null) {
  
    global $DB;
    
    if(isset($value)) {
      
        $sth = $DB['MAIN']->prepare("UPDATE settings SET value = :value WHERE setting = :setting");
        $sth->execute(array(':setting' => $setting, ':value' => $value));
    }
    else {
  
        $sth = $DB['MAIN']->prepare("SELECT value FROM settings WHERE setting = :setting");
        $sth->execute(array(':setting' => $setting)); 
        return $sth->fetchColumn();
    }
}

// Set the new current mainframe ID
function changeModule($page) {
  
    global $dbh, $mid;
  
    $sth = $dbh->prepare("SELECT id, name FROM modules");
    $sth->execute();
    $mids = $sth->fetchAll();
  
    $return = 'Current mainframe: &nbsp;';
    $return .= '<form action="" style="display: inline;">';
    $return .= '<input type="hidden" id="changeCurrentMid_page" value="'.$page.'" />';
    $return .= '<select id="changeCurrentMid">';
    $return .= '<option disabled="disabled">Select module</option>';
    foreach ($mids as $value) {
        $sel = ($value['id'] == $mid) ? 'selected="selected"' : '';
        $return .= '<option '.$sel.' value="'.$value['id'].'">'.$value['name'].'</option>';
    }
    $return .= '</select></form>';
    
    return $return;
}

// Convert a string to alphanumeric string (with underscore and dashes)
function stripString($str) {
    
    // More info: http://stackoverflow.com/questions/7128856/strip-out-html-and-special-characters
    $clear = str_replace(" ", "", trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9-_ ]/', ' ', urldecode(html_entity_decode(strip_tags($str)))))));
    return $clear;
}

// Check if current PID is still active
function checkPid($pid) {
    // create our system command
    $cmd = "ps $pid";
 
    // run the system command and assign output to a variable ($output)
    exec($cmd, $output, $result);
 
    // check the number of lines that were returned
    if(count($output) >= 2){
 
        return true; // the process is still alive
    }
    else {
         
        return false; // the process is dead
    }  
}

// Update the crontab file with all the mainframe settings
function setCrontab() {
    
    global $dbh;
    
    // Select the measure frequency (in minutes)
    $frequency = settings("stabtest_frequency");
    
    // Select all the installed mainframes and store the IDs in a string
    $sth = $dbh->prepare("SELECT n.id FROM modules n, stabilitytest_config p WHERE n.id = p.mid");
    $sth->execute();
    $m = $sth->fetchAll();
    $mids = '';
    foreach ($m as $value) { 
        $mids .= ' '.$value['id'];
    }
    
    // Update the crontab file
    $cron .= '*/'.$frequency.' * * * * /home/user/CAEN1527/CAEN1527_monitoring/StabilityTest'.$mids.''.PHP_EOL;
    $cron .= '*/30 * * * * /home/user/www/cgi-bin/readmeteo.csh'.PHP_EOL;
    file_put_contents('/home/user/www/cron.tmp', $cron);
    exec('crontab -r'); // remove all existing crontabs
    exec('crontab /home/user/www/cron.tmp'); 
    unlink('/home/user/www/cron.tmp');
}




function checkMainFrame() {
    
    global $dbh, $mid, $config;
    /*
    $sth2 = $dbh->prepare("SELECT address FROM modules WHERE id = :mid");
    $sth2->execute(array(':mid' => $mid));
    $ip = $sth2->fetchColumn();
    $res = fsockopen($ip, $config['caen_port']);
    return $res;
     * 
     */
    return true;
}

// Check if hvscan is ongoing, and clear database if there are some conflicts
function hvscan_ongoing() {
    
    global $dbh, $mid, $config;
    
    // Check if program is still running
    $t = $dbh->prepare("SELECT hvscan_pid FROM modules WHERE id = :mid");
    $t->execute(array(':mid' => $mid));
    $pid = $t->fetch();
    $proc = checkPid($pid[0]); // true if ongoing
    
    // Check if hvscan config is stored in the database
    $t = $dbh->prepare("SELECT count(*) FROM hvscan WHERE mid = :mid"); 
    $t->execute(array(':mid' => $mid));
    $hvscan = ($t->fetchColumn() == 1) ? true : false; // true if in database
    
   
    // Check if detectors are subjected to hvscan
    $t = $dbh->prepare("SELECT count(*) FROM detectors WHERE process = 1 AND mid = :mid"); 
    $t->execute(array(':mid' => $mid));
    $det = ($t->fetchColumn() != 0) ? true : false; // true if at least one detetor is subjected to the hvscan
    
    if($proc AND ($hvscan AND $det)) {
        
        return true;
    }
    else {
        
        // Clear up database and process PID
        $t = $dbh->prepare("DELETE FROM hvscan WHERE mid = :mid"); 
        $t->execute(array(':mid' => $mid));
        
        $t = $dbh->prepare("UPDATE detectors SET process = 0 WHERE mid = :mid"); 
        $t->execute(array(':mid' => $mid));
        
        exec("kill ".$pid[0]); // Kill process
        
        return false;
    }
}


function get_string_between($string, $start, $end) {
    
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}


function effAttenuation($k) {
    
    $attA[1] = 1;
    $attA[2] = 10;
    $attA[3] = 100;
    
    $attB[1] = 1;
    $attB[2] = 1.5;
    $attB[3] = 100;
    
    $attC[1] = 1;
    $attC[2] = 2.2;
    $attC[3] = 4.6;
    
    list($a, $b, $c) = str_split($k);
    return $attA[$a]*$attB[$b]*$attC[$c];
}



function deleteDir($dirPath) {
    
    if(!is_dir($dirPath)) {
        echo "$dirPath must be a directory";
    }
    if(substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}

function cmd($name, $log, $cmd, $arg) {
	
	putenv("LD_LIBRARY_PATH=/home/webdcs/software/webdcs/CAEN/lib:/usr/local/root/lib");
    exec('php /home/webdcs/software/webdcs/CORE/php/command.php "'.$name.'" "'.$log.'" "'.$cmd.'" "'.$arg.'" > /dev/null 2>&1 &', $t);
	
}

function startHVscan_DAQ($id) {
    
    global $dbh;
            /*
            echo 'Current script owner: ' . get_current_user();
            //echo shell_exec('whoami');
            echo '<br />';
            echo '<pre>'.shell_exec('ls -l').'</pre>';
           // echo shell_exec("/var/www/software/CAEN/test");
            //shell_exec('/var/www/webdcs/software/CAEN/HVscan_DAQ 1 2>&1');
            //echo shell_exec("ls -l /var/www/software/CAEN ");
            exec('./test 2>&1', $output, $return_var);
            var_dump($output, $return_var);
            //echo shell_exec($config['CAEN_dir']."HVscan_DAQ 1");
             */
       
    //$pid = shell_exec($config['CAEN_dir']."HVscan_DAQ ".$id." > /dev/null 2>/dev/null & echo $!");
    $pid = shell_exec($config['CAEN_dir']."HVscan_DAQ ".$id." > WEBDCS_DAQ".$id.".log & echo $!");

    // Write pid process to db
    $t = $dbh->prepare("UPDATE modules SET hvscan_pid = :pid WHERE id = 1");
    $t->execute(array(':pid' => $pid));
   
    echo $pid;
    //header("Location: index.php?q=daqhvscan");
}

function getTrolleyPosition($trolley, $time = "") {
    
    global $dbh;
    
    if($time == "") $time = time();
    
    $sth1 = $dbh->prepare("SELECT * FROM position WHERE $time > time AND trolley_id = $trolley ORDER BY time DESC LIMIT 1");
    $sth1->execute();
    if($sth1->rowCount() > 0) {
        $position = $sth1->fetch();
    }
    else $position = false;
       
    return $position;
}

function getTrolleyPositionFromId($id) {
    
    global $dbh;
    
    $sth1 = $dbh->prepare("SELECT * FROM position WHERE id = :id LIMIT 1");
    $sth1->execute(array(":id" => $id));
    if($sth1->rowCount() > 0) {
        $position = $sth1->fetch();
        $position = $position['position'];
    }
    else $position = false;
       
    return $position;
}

function installedTrolleys() {
    
    global $dbh;
    
    $sth1 = $dbh->prepare("SELECT * FROM trolley");
    $sth1->execute();
    $res = $sth1->fetchAll();
    return $res;
}













////////////////////////////:
function HVscanOngoing() {
    
    global $DB;
    
    $sth1 = $DB['MAIN']->prepare("SELECT * FROM hvscan where status = '1' OR status = '4' ORDER BY id DESC LIMIT 1");
    $sth1->execute();
    $current_hvscan = $sth1->fetch();
    $run = -1;
    if($sth1->rowCount() > 0) $run = $current_hvscan['id'];
    return $run;
}

function stabilityOngoing() {
    
    global $DB;
	
	$sth1 = $DB['MAIN']->prepare("SELECT * FROM stability WHERE status = 0 OR status = 3 or status = 4 ORDER BY id DESC LIMIT 1");
	$sth1->execute();
	$res = $sth1->fetch();
	$run = -1;
	if($sth1->rowCount() > 0) $run = $res['id'];

    return $run;
	

	$run = false;
    $runFile = file_get_contents("/var/operation/RUN/RUN_STABILITY");
    if($runFile != "STOP" && is_numeric($runFile)) $run = $runFile;
    
    return $run;
}



function plotGraph_Time($name, $leftName, $leftUnit, $leftData, $rightName, $rightUnit, $rightData) {
    
    
?>
<script type="text/javascript">
    window.onload = function () {
    var chart = new CanvasJS.Chart("chartContainer", 
    {
      title:{
      text: "<?php echo $name; ?>"
      },
      axisX:{
        title: "Date",
        interval:10, 
        gridThickness: 1,
        titleFontSize: 16,
        labelFontSize: 12,
      },
      axisY:{
        title: "<?php echo $leftName.' ['.$leftUnit.']'?>",
        titleFontSize: 16,
        gridThickness: 1,
        labelFontSize: 12,
		titleFontColor: "red",
      },
      axisY2:{ 
        title: "<?php echo $rightName.' ['.$rightUnit.']'?>",
        titleFontSize: 16,
        gridThickness: 1,
        labelFontSize: 12,
		titleFontColor: "blue",
      },
      zoomEnabled: true, 
      zoomType: "xy",
      data: [{        
        type: "line",
        xValueType: "dateTime",
		color: "red",
        dataPoints: [<?php echo $leftData; ?>]
      },
      {        
        type: "line",
        xValueType: "dateTime",
        axisYType: "secondary",
		color: "blue",
        dataPoints: [<?php echo $rightData; ?>]
      }
      ]
    });

    chart.render();
  }
</script>

<script src="http://canvasjs.com/assets/script/canvasjs.min.js"></script>
<div id="chartContainer" style="height: 350px; width: 95%; float: left; margin-top: 15px;">
</div>   


<?php
    
    
}
