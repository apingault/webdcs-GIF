<?php

function getDIPParamInfo($paramID, &$paramName, &$paramUnit, &$DBtable) {
   
    $handle = fopen("/var/operation/RUN/DIP_PUBLICATIONS", "r");
    $line = "";
    while(($line = fgets($handle)) !== false) {
        $tmp = explode("\t", $line);
        if($tmp[0] == "DIP_".$paramID) break;
    }
    fclose($handle);
    // Split string on tab and replace ! by white space
    $i = 0;
    $tmp = explode("\t", $line);
    foreach( $tmp as $t) {

        $t = str_replace('!', ' ', $t);
        if($i==1) $paramName = $t;
        if($i==2) $paramUnit = $t;
        if($i==3) $DBtable = $t;
        $i++;
    }
}

function getDataPointsFromDB($paramID, $t1, $t2) {
    
    $datapoints = "";
    $paramName = "";
    $paramUnit = "";
    $DBtable = "";
    getDIPParamInfo($paramID, $paramName, $paramUnit, $DBtable);
    $sql = sprintf("SELECT timestamp, %s FROM %s WHERE timestamp > %d AND timestamp < %d", $paramID, $DBtable, $t1, $t2);
    
    $dbhx = new PDO("mysql:host=".DB_HOST.";dbname=DIP", DB_USER, DB_PASSWORD);
    $sth1 = $dbhx->prepare($sql);
    $sth1->execute();
    $values = $sth1->fetchAll();
    foreach($values as $val) {
        $time = $val[0]*1000;
        if($DBtable) $datapoints .= "{ x: ".$time.", y: ".$val[1]." }, ";
    }
    
    return $datapoints;
}

// Get parameters
$params = file("/var/operation/RUN/DIP_PUBLICATIONS");
$pubs = array();
foreach($params as $i => $p) {
    
    $tmp = explode("\t", $p);
    $params[$i] = str_replace("!", " ", $tmp[1]);
    array_push($pubs, str_replace("DIP_", "", $tmp[0]));
}

$t1 = $stability['time_start'];
$t2 = $stability['time_end'];


if(isset($_POST['submit'])) {

    $paramID1 = $pubs[$_POST['param1']];
    $paramID2 = $pubs[$_POST['param2']];
}
else {
    
    $paramID1 = 33;
    $paramID2 = 34;
}


$datapoints1 = getDataPointsFromDB($pubs[$paramID1], $t1, $t2);
$datapoints2 = getDataPointsFromDB($pubs[$paramID2], $t1, $t2);

getDIPParamInfo($pubs[$paramID1], $paramName1, $paramUnit1, $DBtable1);
getDIPParamInfo($pubs[$paramID2], $paramName2, $paramUnit2, $DBtable2);

?>

<script type="text/javascript">
    window.onload = function () {
    var chart = new CanvasJS.Chart("chartContainer", 
    {
      title:{
      text: "<?php echo $paramName; ?>"
      },
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
      },
      axisY2:{ 
        title: "<?php echo $paramName2.' ['.$paramUnit2.']'?>",
        titleFontSize: 16,
        gridThickness: 1,
        labelFontSize: 12,
      },
      zoomEnabled: true, 
      zoomType: "xy",
      data: [{        
        type: "line",
        xValueType: "dateTime",
        dataPoints: [<?php echo $datapoints1; ?>]
      },
      {        
        type: "line",
        xValueType: "dateTime",
        axisYType: "secondary",
        dataPoints: [<?php echo $datapoints2; ?>]
      }
      ]
    });

    chart.render();
  }
</script>

<?php
echo '<form action="" method="post">';
echo '<select name="param1">';
foreach($params as $i => $p) {
                    
    $sel = ($paramID1 == $i) ? 'selected="selected"' : '';
    echo '<option '.$sel.' value="'.$i.'">'.$p.'</option>';
}
echo '</select> ';

echo '<select name="param2">';
foreach($params as $i => $p) {
                    
    $sel = ($paramID2 == $i) ? 'selected="selected"' : '';
    echo '<option '.$sel.' value="'.$i.'">'.$p.'</option>';
}
echo '</select> ';
    
echo '<input type="submit" name="submit" value="plot" /></form>';
?>



<script src="http://canvasjs.com/assets/script/canvasjs.min.js"></script>
<div id="chartContainer" style="height: 400px; width: 95%; float: left; margin-top: 15px;">
</div>   

