<?php

// Get information of a processes by invoking the ps -Al command to extract info/PID
function getProccessInfo($proc, &$running, &$PID = "", &$PS = "") {

	$running = false;
	
	$process = shell_exec("ps -Al | grep ".$proc);
	if($process != "") {
		
		$running = true;
		$PS = nl2br($process);
	}
	else $PS = "n/a";

	$PID = shell_exec("ps -Al | grep ".$proc." | awk '{print $4}'");
	if($PID == "") $PID = "n/a";
	else $PID = nl2br($PID);
}


function systemCodesFormatted($status) {
	
	switch($status) {
		
		default:	return '<font style="font-weight: bold;">UNKNOWN</font>'; break;
		case 0:		return '<font style="color: green; font-weight: bold;">OK</font>'; break;
		case 10:	return '<font style="color: orange; font-weight: bold;">WARNING</font>'; break;
		case 20:	return '<font style="color: red; font-weight: bold;">ERROR</font>'; break;
	}
}