<?php
$servername = "localhost";
$username = "root";
$password = "UserlabGIF++";
$conn = new PDO("mysql:host=$servername;dbname=LONGEVITY", $username, $password);


$sth1 = $conn->prepare("SELECT * FROM `RAW_RE2-2-NPD-BARC-9` GROUP BY RUN_ID");
$sth1->execute();
$runids = $sth1->fetchAll();

$qint = 11.406;

$QINT = array();


foreach($runids as $runid) {
	
	$sth1 = $conn->prepare("SELECT * FROM `RAW_RE2-2-NPD-BARC-9` WHERE RUN_ID = ".$runid['RUN_ID']." ORDER BY timestamp");
	$sth1->execute();
	$pts = $sth1->fetchAll();
	
	$qint = 0;
	for($i=0; $i < count($pts)-1; $i++) {
		
		$qint += 
		echo $i.'<br />';
		echo $pts[$i]['I_TOT'].'<br />';
	
	
	
	}
}




?>