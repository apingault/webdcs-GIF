<?php

function getFormattedStatus($status) {
	
    switch($status) {
	case '0' : return '<font color="blue"><b>FINISHED</b></font>'; break;
        case '1' : return '<font><b>ONGOING</b></font>'; break;
        case '2' : return '<font color="red"><b>KILLED</b></font>'; break;
        case '3' : return '<font color="green"><b>APPROVED</b></font>'; break;
        case '4' : return '<font><b>RESUMED</b></font>'; break;
    }
}


function getFormattedSourceStatus($status, $long = false) {
	
	$add = "";
	if($long) $add = "SOURCE ";
	return ($status == 0) ? '<font color="red"><b>'.$add.'OFF</b></font>' : '<font color="green"><b>'.$add.'ON</b></font>'; 
}

function getFormattedBeamStatus($status, $long = false) {
	
	$add = "";
	if($long) $add = "BEAM ";
	return ($status == 0) ? '<font color="red"><b>'.$add.'OFF</b></font>' : '<font color="green"><b>'.$add.'ON</b></font>'; 
}