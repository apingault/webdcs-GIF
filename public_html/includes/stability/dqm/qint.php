<?php

$url = "index.php?q=longevity&p=rundqm&id=".$id."&r=qint&g=".$_GET['g']."&param=";

switch($_GET['param']) {
    
    default: 
        $param = "<b><a href=".$url."CAEN>CAEN</a></b> - <a href=".$url."ADC>ADC</a> - <a href=".$url."CAENCORR>CAEN (corrected)</a> - <a href=".$url."ADCCORR>ADC (corrected)</a>"; 
        $index = 5;
        break;
    case "ADC": 
        $param = "<a href=".$url."CAEN>CAEN</a> - <b><a href=".$url."ADC>ADC</a></b> - <a href=".$url."CAENCORR>CAEN (corrected)</a> - <a href=".$url."ADCCORR>ADC (corrected)</a>"; 
        $index = 6;
        break;
    case "CAENCORR": 
        $param = "<a href=".$url."CAEN>CAEN</a> - <a href=".$url."ADC>ADC</a> - <b><a href=".$url."CAENCORR>CAEN (corrected)</a></b> - <a href=".$url."ADCCORR>ADC (corrected)</a>"; 
        $index = 7;
        break;
    case "ADCCORR": 
        $param = "<a href=".$url."CAEN>CAEN</a> - <a href=".$url."ADC>ADC</a> - <a href=".$url."CAENCORR>CAEN (corrected)</a> - <b><a href=".$url."ADCCORR>ADC (corrected)</a></b>"; 
        $index = 8;
        break;
}

$fh = fopen("/var/operation/STABILITY/".$idstring."/".$currentGapName.".qint", 'r');

$datapoints = "";
while (($line = fgets($fh)) !== false) {
    
    $tmp = explode("\t", $line); 
    $time = $tmp[0]*1000;
    $qint = $tmp[$index]/1000./$currentGapArea;
    $datapoints .= "{ x: ".$time.", y: ".$qint." }, ";
}
fclose($fh);

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
        title: "Integrated Charge (mC/cm²)",
        titleFontSize: 16,
        gridThickness: 1,
        labelFontSize: 12,
      },
      zoomEnabled: true, 
      zoomType: "xy",
      data: [
      {        
        type: "line",
        xValueType: "dateTime",
        color: "red",
        dataPoints: [
            <?php echo $datapoints; ?>
        ]
      }
      ]
    });

    chart.render();
  }
  
</script>

&nbsp;&raquo; Select value: <?=$param?><br />
&nbsp;&raquo; Integrated charge during run: <?php echo sprintf("%3f", $qint);?> mC/cm²<br />
&nbsp;&raquo; Total integrated charge: <?=$max?> uA<br />

<br />


<script src="http://canvasjs.com/assets/script/canvasjs.min.js"></script>
<div id="chartContainer" style="height: 400px; width: 95%; float: left; margin-top: 15px;">
</div>   
