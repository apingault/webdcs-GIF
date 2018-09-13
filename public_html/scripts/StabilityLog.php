<?php
$id = $_GET['id'];
$f = sprintf("/var/operation/STABILITY/%06d/log.txt", $id);
$file = glob($f); // search for .log file
echo '<pre>';
$log = array_reverse(file($file[0]));
foreach ($log as $line) {
    echo trim($line) . '<br />';
}
echo '</pre>';

