<?php


loadCSS("datetimepicker.css");
loadJS("datetimepicker.js");

// Get parameters
$params = file("/var/operation/RUN/DIP_PUBLICATIONS");
$pubs = array();
foreach($params as $i => $p) {
    
    $tmp = explode("\t", $p);
    $params[$i] = str_replace("!", " ", $tmp[1]);
    array_push($pubs, str_replace("DIP_", "", $tmp[0]));
}

if(isset($_POST['submit'])) {
    
    $t1 = strtotime($_POST['time1']);
    $t2 = strtotime($_POST['time2']);
    $paramID1 = $_POST['param1'];
    $paramID2 = $_POST['param2'];
}
else {

    $t1 = time() - 24*3600;
    $t2 = time();
    $paramID1 = 42;
    $paramID2 = 43;
}


$datapoints1 = getDataPointsFromDB($pubs[$paramID1], $t1, $t2);
$datapoints2 = getDataPointsFromDB($pubs[$paramID2], $t1, $t2);

getDIPParamInfo($pubs[$paramID1], $paramName1, $paramUnit1, $DBtable1);
getDIPParamInfo($pubs[$paramID2], $paramName2, $paramUnit2, $DBtable2);
?>


<script src="http://canvasjs.com/assets/script/canvasjs.min.js"></script>



<div class="content">
    
    <h3>Plot monitoring history</h3>
    
    <form action="" method="post">
        
        <table>
            <tr>
                <td style="width: 120px;">Start time:</td>
                <td><input id="time1" name="time1" type="text" value="<?php echo date("Y/m/d H:i", $t1); ?>" /></td>
            </tr>
            <tr>
                <td>End time:</td>
                <td><input id="time2" name="time2" type="text" value="<?php echo date("Y/m/d H:i", $t2); ?>" /></td>
            </tr>
            <tr>
                <td>Parameter 1:</td>
                <td><select name="param1">
                <?php
                foreach($params as $i => $p) {
                    
                    $sel = ($paramID1 == $i) ? 'selected="selected"' : '';
                    echo '<option '.$sel.' value="'.$i.'">'.$p.'</option>';
                }
                ?> 
                </select> (blue)</td>
            </tr>
            <tr>
                <td>Parameter 2:</td>
                <td><select name="param2">
                        <option name="99999">None</option>
                        <option name="100000"></option>
                <?php
                foreach($params as $i => $p) {
                    
                    $sel = ($paramID2 == $i) ? 'selected="selected"' : '';
                    echo '<option '.$sel.' value="'.$i.'">'.$p.'</option>';
                }
                ?> 
                </select> (red)</td>
            </tr>
            
            <tr>
                <td style="height: 30px;"></td>
                <td><input type="submit" name="submit" value="Generate plot"></td>
            </tr>
            
        </table>


        
    </form>
    
 
  
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
		titleFontColor: "red",
      },
      axisY2:{ 
        title: "<?php echo $paramName2.' ['.$paramUnit2.']'?>",
        titleFontSize: 16,
        gridThickness: 1,
        labelFontSize: 12,
		titleFontColor: "blue",
      },
      zoomEnabled: true, 
      zoomType: "xy",
      data: [{        
        type: "line",
		color: "red",
        xValueType: "dateTime",
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
<div id="chartContainer" style="height: 350px; width: 95%; float: left; margin-top: 15px;">
</div>   




    <script type="text/javascript">// <![CDATA[
    jQuery(function(){jQuery('#time1').datetimepicker();});
    jQuery(function(){jQuery('#time2').datetimepicker();});
    // ]]></script>
    
    <br /><br />
    
</div>
