<?php
$file = glob("/mnt/nfs/daq_data/DAQ_RUN/log"); // search for .log file
echo '<pre>';
$log = array_reverse(file($file[0]));
foreach ($log as $line) {
    echo trim($line) . '<br />';
}
echo '</pre>';

