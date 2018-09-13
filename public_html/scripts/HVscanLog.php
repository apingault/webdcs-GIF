<?php
require_once '../config/config.php';

$dir = $config['data_dir'].'tmp/'.$mid.'/'; 
$hvscan = hvscan_ongoing();

// If HVscan is ongoing, display log file
if($hvscan) {
    
    $file = glob($dir."*.log"); // search for .log file
    if($file[0] == 'stabilitytest.log') next($file); // skip stability test logfile
    echo '<pre>';
    $log = array_reverse(file($file[0]));
    foreach ($log as $line) {
        echo '> '.trim($line) . '<br />';
    }
    echo '</pre>';
}

// Else display last log file from last HVscan
else {
    
    // Get latest log file
    $dir = $config['data_dir'].'hvscan/'.$mainframe.'/'; 
    $files = glob($dir."*.log"); // search for .log file
    $files = array_combine(array_map("filemtime", $files), $files);
    
    arsort($files);
    $file = key($files);
    
    if(file_exists($files[$file])) {
    
        echo '<pre>';

        $log = array_reverse(file($files[$file]));
        foreach ($log as $line) {
            echo '> '.trim($line) . '<br />';
        }
        echo '</pre>';
    }
    else echo '<pre>No past HVscan.<pre>';
}