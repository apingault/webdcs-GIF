<?php

function checkDIP() {
    
    $servername = "localhost";
    $username = "DIP";
    $password = "UserlabGIF++";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=DIP", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e) {
        echo "DIP Server offline";
        //echo "Connection failed: " . $e->getMessage();
    }

    $now =time();
    $sth1 = $conn->prepare("SELECT timestamp FROM source ORDER BY timestamp DESC");
    $sth1->execute();
    $dip = $sth1->fetch();
    
    $diptime = $dip['timestamp'];
    
    if($now - $diptime > 300) return true; // Set threshold of 300 seconds
    else return false;
    
    $conn->Close();
}


function getHVOlf($T, $S, $HV, $id, $gapID = false) {
    
    global $DB;
    if($gapID) {
        $sth1 = $DB['MAIN']->prepare("SELECT HV as HV FROM hvscan_VOLTAGES WHERE scanid = ".$id." AND HVPoint = ".$HV." AND detectorid = ".$gapID);
    }
    else {
        $sth1 = $DB['MAIN']->prepare("SELECT v.HV as HV FROM hvscan_VOLTAGES v, detectors d WHERE v.scanid = ".$id." AND v.detectorid = d.id AND v.HVPoint = ".$HV." AND d.trolley = ".$T." AND d.slot = ".$S." GROUP BY d.slot");       
    }
    $sth1->execute();
    $p = $sth1->fetch();
    return $p['HV'];
}

function getMaxTriggers($T, $S, $HV, $id) {
    
    global $DB;

    $sth1 = $DB['MAIN']->prepare("SELECT maxtriggers FROM hvscan_VOLTAGES v, detectors d WHERE v.scanid = ".$id." AND v.detectorid = d.id AND v.HVPoint = ".$HV." AND d.trolley = ".$T." AND d.slot = ".$S." GROUP BY d.trolley, d.slot");
    $sth1->execute();
    $p = $sth1->fetch();
    return $p['maxtriggers'];
}



function getArea($gap) {
    
    global $DB;
    
    
    if(is_numeric($gap)) $sql = "SELECT area FROM detectors WHERE id = ".$gap." LIMIT 1";
    else  $sql = "SELECT area FROM detectors WHERE name = '".$gap."' LIMIT 1";
    $sth1 = $DB['MAIN']->prepare($sql);
    $sth1->execute();
    $p = $sth1->fetch();
    return $p['area'];
}





function startCAEN($program, $id, $opt = "") {
    
    putenv("LD_LIBRARY_PATH=/home/webdcs/software/webdcs/CAEN/lib:/usr/local/root/lib");
    exec("/home/webdcs/software/webdcs/CAEN/bin/".$program." ".$id." ".$opt." > /dev/null 2>&1 &", $t);
    //exec("/home/webdcs/software/webdcs/CAEN/bin/".$program." ".$id." 2>&1", $t);
    //echo "<pre>";
    //print_r($t);
    //echo "</pre>";
}

// Checked: OK



// Checked: OK
function showLogFile($file, $reverse = false) {
	
	?>

    <script type="text/javascript">
    function showLogFile() {

        $.ajax({
            type: 'GET',
            url: 'index.php?q=ajax&p=logfile&file=<?=$file?>&reverse=<?=$reverse?>',
            cache: false,
            success: function(result) {

                $('#logFile').html(result);
            }
        });
		
        setTimeout(function(){showLogFile();}, 2000);
    }
        
    $(document).ready(function() { showLogFile(); });
    </script>

	<div class="logfile" id="logFile">Log file</div>
	
	<?php
}


function msg($msg, $type = "") {
	
	if($type == "error") $color = "red";
	elseif($type == "warning") $color = "orange";
	else $color = "green";
	
	echo '<div style="color: '.$color.'; border: 1px solid '.$color.'; padding: 5px;">'.$msg.'</div>';

}

function generateChart() {
	
	?>
	
	<script type="text/javascript">
		window.onload = function () {
		var chart = new CanvasJS.Chart("chartContainer", 
		{
		  axisX:{
			title: "Date",
			interval:10, 
			gridThickness: 1,
			titleFontSize: 16,
			labelFontSize: 12,
		  },
		  axisY:{
			title: "<?php echo $paramName1.' ['.$paramUnit1.']'?>",
			titleFontSize: 16,
			gridThickness: 1,
			labelFontSize: 12,
			titleFontColor: "red",
		  },
		  axisY2:{ 
			title: "<?php echo $paramName2.' ['.$paramUnit2.']'?>",
			titleFontSize: 16,
			gridThickness: 1,
			titleFontColor: "blue",
			labelFontSize: 12,
		  },
		  zoomEnabled: true, 
		  zoomType: "xy",
		  data: [{        
			type: "line",
			xValueType: "dateTime",
			color: "red",
			dataPoints: [<?php echo $datapoints1; ?>]
		  },
		  {        
			type: "line",
			xValueType: "dateTime",
			axisYType: "secondary",
			color: "blue",
			dataPoints: [<?php echo $datapoints2; ?>]
		  }
		  ]
		});

		chart.render();
	  }
	</script>
	
	<script src="http://canvasjs.com/assets/script/canvasjs.min.js"></script>
	<div id="chartContainer" style="height: 400px; width: 95%; float: left;></div> 
	
	<?php
}