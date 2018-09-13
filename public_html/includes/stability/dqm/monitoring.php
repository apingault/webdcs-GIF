<?php

function getValuesFromFile($file, $index) {
    
    $fh = fopen($file, 'r');
    $datapoints = "";
    while (($line = fgets($fh)) !== false) {
        $tmp = explode("\t", $line); 
        $time = $tmp[0]*1000;
        $datapoints .= "{ x: ".$time.", y: ".$tmp[$index]." }, ";
    }
    fclose($fh);
    return $datapoints;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/DIP/functions.php';


// Define CAEN parameters
$CAENPARAMS = array("CAEN", "ADC", "CAEN_CORR", "ADC_corr", "HVEFF", "HVAPP", "HVMON");
$CAENPARAMS_FILE = array("qint", "qint", "qint", "qint", "dat", "dat", "dat");
$CAENPARAMS_INDEX = array(1, 2, 3, 4, 1, 2, 3);
$CAENPARAMS_TITLE = array("CAEN current", "ADC current", "CAEN corrected current", "ADC corrected current", "HV effective", "HV applied", "HV monitored");
$CAENPARAMS_UNITS = array("uA", "uA", "uA", "uA", "V", "V", "V");



// Get parameters, grouped by table_name
$sth1 = $dbhDIP->prepare("SELECT * FROM subscriptions GROUP BY table_name");
$sth1->execute();
$cats = $sth1->fetchAll();

$DIPPARAMS = array();
foreach($cats as $cat) {

	$sth1 = $dbhDIP->prepare("SELECT * FROM subscriptions WHERE table_name = '".$cat['table_name']."'");
	$sth1->execute();
	$pars = $sth1->fetchAll();
	
	$DIPPARAMS['NONE-'.$cat['table_name']] = $cat['category'];
	foreach($pars as $param) $DIPPARAMS[$param["id_name"]] = $param["name"];
	//array_push($DIPPARAMS, "");
}
array_pop($DIPPARAMS); // remove last empty key






// Get parameters
$t1 = $stability['time_start'];
$t2 = $stability['last_action'];

if(isset($_POST['submit'])) {

    $paramID1 = $_POST['param1'];
    $paramID2 = $_POST['param2'];
}
else {
    
    $paramID1 = "CAEN";
    $paramID2 = "TIN";
}


if(in_array($paramID1, $CAENPARAMS)) {

    $i = array_search($paramID1, $CAENPARAMS);
    $datapoints1 = getValuesFromFile($dir."/".$currentGapName.".".$CAENPARAMS_FILE[$i], $CAENPARAMS_INDEX[$i]);
    $paramName1 = $CAENPARAMS_TITLE[$i];
    $paramUnit1 = $CAENPARAMS_UNITS[$i];
}
elseif(array_key_exists($paramID1, $DIPPARAMS)) {

	$datapoints1 = getDataPointsFromDB($paramID1, $t1, $t2);
	getDIPParamInfo($paramID1, $paramName1, $paramUnit1, $DBtable1);
}

if(in_array($paramID2, $CAENPARAMS)) {
  
    $i = array_search($paramID2, $CAENPARAMS);
    $datapoints2 = getValuesFromFile($dir."/".$currentGapName.".".$CAENPARAMS_FILE[$i], $CAENPARAMS_INDEX[$i]);
    $paramName2 = $CAENPARAMS_TITLE[$i];
    $paramUnit2 = $CAENPARAMS_UNITS[$i];
}
elseif(array_key_exists($paramID2, $DIPPARAMS)) {
 
	$datapoints2 = getDataPointsFromDB($paramID2, $t1, $t2);
	getDIPParamInfo($paramID2, $paramName2, $paramUnit2, $DBtable2);
	
}


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


<form action="" method="post">

    <select name="param1">

        <?php
        echo '<optgroup label="CAEN parameters">';
        foreach($CAENPARAMS as $i => $param) {
            
            
            $sel = ($paramID1 == $param) ? 'selected="selected"' : '';
            echo '<option '.$sel.' value="'.$param.'">'.$CAENPARAMS_TITLE[$i].'</option>';
        }
        echo '</optgroup>';
        echo '<optgroup label="DIP parameters">';

		foreach($DIPPARAMS as $i => $p) {
			if(strpos($i, "NONE") !== false) {
				echo '<option disabled="disabled">'.$p.'</option>';
			}
			else {
				//echo $i.' '.$id_name1.'<br>';
				$sel = ($paramID1 == $i) ? 'selected="selected"' : '';
				echo '<option '.$sel.' value="'.$i.'">'.$p.'</option>';
			}
		}
        echo '</optgroup>';
        
        ?>
        
    </select>
    
    <select name="param2">

        <?php
        echo '<optgroup label="CAEN parameters">';
        foreach($CAENPARAMS as $i => $param) {
            
            
            $sel = ($paramID2 == $param) ? 'selected="selected"' : '';
            echo '<option '.$sel.' value="'.$param.'">'.$CAENPARAMS_TITLE[$i].'</option>';
        }
        echo '</optgroup>';
        echo '<optgroup label="DIP parameters">';
		
		foreach($DIPPARAMS as $i => $p) {
			if(strpos($i, "NONE") !== false) {
				echo '<option disabled="disabled">'.$p.'</option>';
			}
			else {
				//echo $i.' '.$id_name1.'<br>';
				$sel = ($paramID2 == $i) ? 'selected="selected"' : '';
				echo '<option '.$sel.' value="'.$i.'">'.$p.'</option>';
			}
		}


        echo '</optgroup>';
        
        ?>
        
    </select>
    
    <input type="submit" name="submit" value="plot" />

</form>

<script src="http://canvasjs.com/assets/script/canvasjs.min.js"></script>
<div id="chartContainer" style="height: 400px; width: 95%; float: left; margin-top: 15px;"></div> 