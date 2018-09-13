<?php
/*
 * updateCurrentMid.php
 */

require_once '../config/config.php';

if(filter_input(INPUT_GET, 'mid', FILTER_VALIDATE_INT)) {
    
    settings('current_mid', filter_input(INPUT_GET, 'mid')); // Set the new mainfraime ID
}
