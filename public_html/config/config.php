<?php
if(!defined('INDEX')) die("Access denied");







// Environment settings
$config = array();
$config['dns'] = 'http://webdcsgif.cern.ch/'; // name of the site, with slash at the end!
$config['apache_dir'] = '/var/www/webdcs'; // apache home directory
$config['CAEN_dir'] = '/var/www/software/CAEN/'; // Path to the c executables
$config['meteo_dir'] = '/home/user/meteo/'; // Meteo files location
$config['data_dir'] = '/home/user/data/'; // Data files location
$config['DAQ_dir'] = '/var/operation/HVSCAN_DAQ/'; // Data files location




$config['caen_port'] = '1527';


$hvscan_daq_types = array();
$hvscan_daq_types['rate'] = "Rate Scan";
$hvscan_daq_types['efficiency'] = "Efficiency scan";
$hvscan_daq_types['noise_reference'] = "Noise Reference";
$hvscan_daq_types['calibration'] = "Calibration scan";
$hvscan_daq_types['impaired'] = "Impaired";
$hvscan_daq_types['test'] = "Test scan";

$hvscan_current_types = array();
$hvscan_current_types['default'] = "Default - normal gas mixture";
$hvscan_current_types['argon'] = "Argon resistivity scan";
$hvscan_current_types['test'] = "Test scan";

$trigger_modes = array();
$trigger_modes['external'] = "External";
$trigger_modes['internal'] = "Internal";
$trigger_modes['random'] = "Random";

$position_mode = array();
$position_mode['aging'] = "Aging";
$position_mode['testbeama'] = "Test beam A";
$position_mode['testbeamb'] = "Test beam B";
$position_mode['testbeamc'] = "Test beam C";
$position_mode['maintenance'] = "Maintenance";
$position_mode['na'] = "Not applicable";


$stability_types = array();
$stability_types['default'] = "Default stability";
$stability_types['test'] = "Test run (low voltages)";

$lastHV = array();
$lastHV['15'] = "Turn off";
$lastHV['5000'] = "Standby";
$lastHV['99999'] = "Last HV point, do nothing";

function secondsToTime($seconds) {
    $dtF = new DateTime('@0');
    $dtT = new DateTime("@$seconds");
    //return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes');
}


$scan_labels = array();
$scan_labels['none'] = "None";
/*
$scan_labels['longevity_daily'] = "Longevity: daily scan";
$scan_labels['longevity_current'] = "Longevity: current HV scan";
$scan_labels['longevity_rate'] = "Longevity: rate HV scan";
$scan_labels['longevity_noise'] = "Longevity: noise scan";
$scan_labels['longevity_argon'] = "Longevity: resistivity scan";
*/


$longevity_daily_scan_options = array();
$longevity_daily_scan_options['dg'] = "Double gap";
$longevity_daily_scan_options['sg_bot'] = "Single gap - BOT";
$longevity_daily_scan_options['sg_top'] = "Single gap - TOP";
$longevity_daily_scan_options['sg_topw'] = "Single gap - TOPW";
$longevity_daily_scan_options['sg_topn'] = "Single gap - TOPN";

$longevity_daily_scan_options = array();
$longevity_daily_scan_options['DG_WP'] = "Double gap WP";
$longevity_daily_scan_options['SG_BOT_WP'] = "Single gap - BOT - WP";
$longevity_daily_scan_options['SG_TOP_WP'] = "Single gap - TOP - WP";
$longevity_daily_scan_options['SG_TW_WP'] = "Single gap - TOPW - WP";
$longevity_daily_scan_options['SG_TN_WP'] = "Single gap - TOPN - WP";
$longevity_daily_scan_options['DG_STBY'] = "Double gap STBY";
$longevity_daily_scan_options['SG_BOT_STBY'] = "Single gap - BOT - STBY";
$longevity_daily_scan_options['SG_TOP_STBY'] = "Single gap - TOP - STBY";
$longevity_daily_scan_options['SG_TW_STBY'] = "Single gap - TOPW - STBY";
$longevity_daily_scan_options['SG_TN_STBY'] = "Single gap - TOPN - STBY";

$ICON_CROSS = '<img src="config/images/cross.png" />';
$ICON_TICK = '<img src="config/images/tick.png" />';
$ICON_EDIT = '<img src="config/images/icons/edit.png" />';
$ICON_DELETE = '<img src="config/images/tick.png" />';

// Load libraries!
putenv('ROOTSYS=/usr/local/root/');
putenv('PATH=/usr/local/root/bin:'.getenv("PATH"));
putenv('PATH=~/bin:./bin:.:'.getenv("PATH"));
putenv('LD_LIBRARY_PATH='.getenv("ROOTSYS").'/lib:'.getenv("LD_LIBRARY_PATH"));
putenv('DYLD_LIBRARY_PATH='.getenv("ROOTSYS").'/lib:$DYLD_LIBRARY_PATH');
putenv('PYTHONPATH='.getenv("ROOTSYS").'/lib/:'.getenv("PYTHONPATH"));