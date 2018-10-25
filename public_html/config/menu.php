<?php
if(!defined('INDEX')) die("Access denied");


$menu = array(
	"Monitoring" => array("DIP" => "index.php?q=dip", "PMON" => "index.php?q=pmon", "Log file" => "index.php?q=pmon&p=logfile", "System info" => "index.php?q=sysinfo", "PT correction" => "index.php?q=ptcorrection",),
	"HVscan" => array("DAQ Run Registry" => "index.php?q=hvscan&p=runregistry&type=daq", "Current Run Registry" => "index.php?q=hvscan&p=runregistry&type=current",),
	#"Longevity" => array("Run registry" => "index.php?q=longevity&p=runregistry", "Summary" => "index.php?q=longevity&p=summary"),
    "Hardware" => array("Chambers" => "index.php?q=detectors", "Gaps" => "index.php?q=detectors&p=gaps"),
	//"Hardware" => array("Gas flows" => "index.php?q=gasflow", "Trolley position" => "index.php?q=position", "Detectors" => "index.php?q=detectors"),
	"Settings" => array("DCS Settings" => "index.php?q=dcssettings", "DAQ Ini config" => "index.php?q=dcssettings&p=daqini"),
	"Account" => array("Settings" => "index.php?q=account", "Log out" => "index.php?q=logout"),
);



if(HVscanOngoing() == -1) {
	
	$menu["HVscan"]["Start DAQ HVscan"] = "index.php?q=hvscan&p=startscan&type=daq";
	$menu["HVscan"]["Start Current HVscan"] = "index.php?q=hvscan&p=startscan&type=current";
}
else $menu["HVscan"]["Ongoing run"] = "index.php?q=hvscan&p=ongoing";


//if(stabilityOngoing() == -1) $menu["Longevity"]["Start run"] = "index.php?q=longevity&p=startrun";
//else $menu["Longevity"]["Ongoing run"] = "index.php?q=longevity&p=ongoing";
