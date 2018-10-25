<?php
if(!defined('INDEX')) die("Access denied");

// DEFAULTS
	putenv('ROOTSYS=/usr/local/root/');
	putenv('PATH=/usr/local/root/bin:'.getenv("PATH"));
	putenv('PATH=~/bin:./bin:.:'.getenv("PATH"));
	putenv('LD_LIBRARY_PATH='.getenv("ROOTSYS").'/lib:'.getenv("LD_LIBRARY_PATH"));
	putenv('DYLD_LIBRARY_PATH='.getenv("ROOTSYS").'/lib:$DYLD_LIBRARY_PATH');
	putenv('PYTHONPATH='.getenv("ROOTSYS").'/lib/:'.getenv("PYTHONPATH"));

require_once 'stability/functions.php';

// CONFIG
$longevity_chambers = array('RE2-2-NPD-BARC-9', 'RE4-2-CERN-166', 'SPARE1', 'SPARE2');

// Define some functions


echo '<div class="content">';
    
if(isset($_GET['p'])) require 'stability/'.$_GET['p'].'.php';
else require 'stability/index.php';

echo '</div>';
echo '<br /><br />';
