<?php


usleep(100000);



require_once '../config.php';
require_once '../photo.upload.class.php';

$albumid = '0';

$text = 'dscdcds';

if (isset($_GET['qqfile'])){
    
	$fileName = $_GET['qqfile'];
    
	// xhr request
	$headers = apache_request_headers();
	if ((int)$headers['Content-Length'] == 0) {
		die ('{error: "content length is zero"}');
	}
} 
elseif (isset($_FILES['qqfile'])) {
    	
	$fileName = basename($_FILES['qqfile']['name']);
	// form request
	if ($_FILES['qqfile']['size'] == 0) {
		die ('{error: "file size is zero"}');
	}
} 
else {
		
	die ('{error: "file not passed"}');
}






// UPLOAD PHOTO
	if(isset($_FILES['qqfile'])) {
		
		
		try {
			new UploadPhoto($albumid, $text, $ALBUM['local_path']);
		}
		catch (Exception $e) {
			
			$error = $e->getMessage();
		}
		
		if(empty($error)) die ('{success:true}');
		else die('{error:"'.$error.'"}');
	}
	else die('{error:"No file found"}');

?>