<?php 

require_once '../../../config/config.php';
   
echo 'lol';
function func1($id, $chamber, $scan_mode){
	
	$output = array();
	
	
	
	
	//echo json_encode($output);
	echo "it works";
}

if (isset($_POST['callFunc1'])) {
	echo func1($_POST['callFunc1']);
}
?>