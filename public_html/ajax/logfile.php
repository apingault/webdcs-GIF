<?php

$filename = $_GET['file'];
$reverse = ($_GET['reverse'] == 1) ? true : false;

echo "<pre>";
if(file_exists($filename)) {

    $file = glob($filename); // search for .log file
    if($reverse) $log = array_reverse(file($file[0]));
    foreach ($log as $line) {
        echo trim($line) . '<br />';
    }
}
// else echo "No log file found";
else echo "File ".$filename." not found";
echo "</pre>";
