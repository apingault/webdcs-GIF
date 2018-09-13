<?php

$param = "";
$url = "index.php?q=stability_result&id=".$id."&p=current&param=";
switch($_GET['param']) {
    
    default: 
        $param = "<b><a href=".$url."CAEN>CAEN</a></b> - <a href=".$url."ADC>ADC</a>"; 
        $index = 4;
        break;
    case "ADC": 
        $param = "<a href=".$url."CAEN>CAEN</a> - <b><a href=".$url."ADC>ADC</a></b>";
        $index = 5;
        break;
}


$fh = fopen("/var/operation/STABILITY/".$idstring."/".$currentGap.".dat", 'r');
$min = 9999999.0;
$max = -99999999.0;

$datapoints = ""; // current
$datapoints2 = ""; // voltage
while (($line = fgets($fh)) !== false) {
    $tmp = explode("\t", $line); 
    $time = $tmp[0]*1000;
    $datapoints .= "{ x: ".$time.", y: ".$tmp[$index]." }, ";
    $datapoints2 .= "{ x: ".$time.", y: ".$tmp[1]." }, ";
    if($tmp[$index] < $min) $min = $tmp[$index];
    if($tmp[$index] > $max) $max = $tmp[$index];
}
fclose($fg);


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
        title: "Current (uA)",
        titleFontSize: 16,
        gridThickness: 1,
        labelFontSize: 12,
      },
      axisY2:{ 
        title: "HV eff (V)",
        titleFontSize: 16,
        gridThickness: 1,
        labelFontSize: 12,
      },
      zoomEnabled: true, 
      zoomType: "xy",
      data: [{        
        type: "line",
        xValueType: "dateTime",
        dataPoints: [<?php echo $datapoints; ?>]
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

&nbsp;&raquo; Select value: <?=$param?><br />
&nbsp;&raquo; Min: <?=$min?> uA<br />
&nbsp;&raquo; Max: <?=$max?> uA<br />

<br />


<script src="http://canvasjs.com/assets/script/canvasjs.min.js"></script>
<div id="chartContainer" style="height: 400px; margin-top: 15px;">
</div>   

