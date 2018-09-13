<?php

// Default configuration
$CFG['default_page'] = "includes/dip.php";

// Timezone and encoding
iconv_set_encoding('internal_encoding', 'UTF-8');
mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Brussels');

/** DEV TRUE OR FALSE **/
define ('DEVELOPMENT_ENVIRONMENT', false);


/** DATABASE SETTINGS **/
define('DB_NAME', 'webdcs');
define('DB_USER', 'root');
define('DB_PASSWORD', 'UserlabGIF++');
define('DB_HOST', 'localhost');

/** SITE SETTINGS **/
define('SITE_NAME', 'WebDCS SHIP');
define('DOMAIN_NAME', 'webdcs904a.cern.ch');
define('REL_PATH', '/'); // relative path to document root (WITH SLASH AT BEGIN AND END)

// SQL connections
$DB_LIB['MAIN']['active']   = true;
$DB_LIB['MAIN']['host']     = "localhost";
$DB_LIB['MAIN']['user']     = "root";
$DB_LIB['MAIN']['pass']     = "UserlabGIF++";
$DB_LIB['MAIN']['db']       = "webdcs";

$DB_LIB['DIP']['active']   = true;
$DB_LIB['DIP']['host']     = "128.141.143.223";
$DB_LIB['DIP']['user']     = "root";
$DB_LIB['DIP']['pass']     = "UserlabDIP++";
$DB_LIB['DIP']['db']       = "dip";



/** ADVANCED SETTINGS **/
$upload_limit = 1024 * 1024 * 20; // in bytes


// Define some directory constants
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', $_SERVER['DOCUMENT_ROOT'] . REL_PATH );
define('DOMAIN_ROOT', 'http://' . $_SERVER['SERVER_NAME'] . REL_PATH );
session_cache_expire(20); // 20 min


session_start();
ob_start();



// Define the experiment
// Interface can change from experiment to experiment
define('EXPERIMENT', 'SHIP'); // GIFPP or 904 or other experiment
define('EXPERIMENT_NAME', 'SHIP'); // experiment name as appear in the interface


// DIP Subscriptions
$DIP_SUBSCRIPTIONS = ["environmental", "gas"];



// DAQ READOUT
$DAQ_TYPES['none']      = "None";
$DAQ_TYPES['default']   = "Default";
$DAQ_TYPES['hardroc']   = "Hardroc";


