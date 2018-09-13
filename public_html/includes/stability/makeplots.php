<?php

require_once 'includes/DIP/functions.php';

function time2Qint($chamber, $t) {
	
	global $dbhLONG;
	
	$table = "CORR_QINT_".$chamber;
	$sth1 = $dbhLONG->prepare("SELECT * FROM `".$table."` WHERE timestamp > $t ORDER BY timestamp ASC LIMIT 1");
	$sth1->execute();
	$qint = $sth1->fetch();
	return $qint['QINT_TOT'];
}



if(isset($_POST['submit'])) {
	
	$chamber = $_POST['chamber'];
	$mode = $_POST['mode'];
	$xaxis = $_POST['xaxis'];
	$param1 = $_POST['param1']; 
    $param2 = $_POST['param2']; // DIP
}
else {
	
	$chamber = "RE2-2-NPD-BARC-9";
	$mode = "DG_WP";
	$xaxis = "time";
	$param1 = "rate_tot";
	$param2 = "P";
}


$sth1 = $dbh->prepare("SELECT v.time_start, v.time_end, r.$param1, v.id FROM `RES_LONG_CMS-RE` r, hvscan v WHERE v.id = r.REF_scanid AND scan_mode = '$mode' AND chamber = '$chamber' ");
$sth1->execute();
$results = $sth1->fetchAll();

$datapoints1 = "";
$datapoints2 = "";
foreach($results as $r) {
	
	$par1 = $r[2];
	if($param2 != "-") $par2 = getDataPointsFromDBAverage($param2, $r[0], $r[1]);
	else $par2 = 0;

	if($xaxis == "time") $t = 1000.*$r[0];
	else $t = time2Qint($chamber, $r[0]);
	
    $datapoints1 .= "{ x: ".$t.", y: ".$par1.", label: 'ID ".$r[3]."' }, ";
	$datapoints2 .= "{ x: ".$t.", y: ".$par2.", label: 'ID ".$r[3]."' }, ";
}

$DIPParams = getDIPParams();

$params = array("rate_tot" => "Total rate", "current_tot" => "Total current");
$xaxises = array("time" => "Time", "qint" => "Integrated Charge");

$xtitle = ($xaxis == "time") ? "Date" : "Integrated Charge [mC/cm²]";
$xValueType = ($xaxis == "time") ? "dateTime" : "number";


getDIPParamInfo($param2, $paramName2, $paramUnit2, $DBtable2);
?>

<script type="text/javascript">
    window.onload = function () {
    var chart = new CanvasJS.Chart("chartContainer", 
    {
      title:{
      text: "<?php echo $paramName; ?>"
      },
      axisX:{
        title: "<?=$xtitle?>",
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
        type: "scatter",
        xValueType: "<?=$xValueType?>",
		color: "red",
        dataPoints: [<?php echo $datapoints1; ?>]
      },
      {        
        type: "scatter",
        xValueType: "<?=$xValueType?>",
        axisYType: "secondary",
		color: "blue",
        dataPoints: [<?php echo $datapoints2; ?>]
      }
      ]
    });

    chart.render();
  }
</script>


<h3>Make plots</h3>

    
<form action="" method="post">
        
	<table>
		
        <tr>
            <td style="width: 120px;">Chamber:</td>
            <td style="width: 220px;">
				<select name="chamber" style="width: 180px;">
				<?php
				foreach($longevity_chambers as $i => $p) {
					$sel = ($chamber == $p) ? 'selected="selected"' : '';
					echo '<option '.$sel.' value="'.$p.'">'.$p.'</option>';
				}
				?> 
				</select>
			</td>
			
			<td>Parameter left:</td>
            <td>
				<select name="param1" >
				<?php
				$prevCat = "";
				foreach($params as $i => $p) {
					if(strpos($i, "NONE") !== false) {
						echo '<option disabled="disabled">'.$p.'</option>';
					}
					else {
						//echo $i.' '.$id_name1.'<br>';
						$sel = ($param1 == $i) ? 'selected="selected"' : '';
						echo '<option '.$sel.' value="'.$i.'">'.$p.'</option>';
					}
				}
				?> 
				</select> (red)
			</td>

		</tr>
		
        <tr>
            <td>Scan mode:</td>
            <td>
				<select name="mode" style="width: 180px;">
				<?php
				foreach($longevity_daily_scan_options as $i => $p) {
					$sel = ($mode == $i) ? 'selected="selected"' : '';
					echo '<option '.$sel.' value="'.$i.'">'.$p.'</option>';
				}
				?> 
				</select>
			</td>
			
			
			<td>Parameter right:</td>
            <td>
				<select name="param2">
					<?php
					foreach($DIPParams as $i => $p) {

						if(strpos($i, "NONE") !== false) {
							echo '<option disabled="disabled">'.$p.'</option>';
						}
						else {
							//echo $i.' '.$id_name1.'<br>';
							$sel = ($param2 == $i) ? 'selected="selected"' : '';
							echo '<option '.$sel.' value="'.$i.'">'.$p.'</option>';
						}
					}
					?> 
				</select> (blue)
			</td>
		</tr>
		
		<tr>
			<td>x-axis:</td>
            <td>
				<select name="xaxis" style="width: 180px;">
					<?php
					foreach($xaxises as $i => $p) {

						$sel = ($xaxis == $i) ? 'selected="selected"' : '';
						echo '<option '.$sel.' value="'.$i.'">'.$p.'</option>';
					}
					?> 
				</select>
			</td>
		
     
        <tr>
			<td style="height: 30px;"></td>
			<td><input type="submit" name="submit" value="Generate plot"></td>
		</tr>
            
	</table>

</form>


<script src="http://canvasjs.com/assets/script/canvasjs.min.js"></script>
<div id="chartContainer" style="height: 350px; width: 95%; float: left; margin-top: 15px;">
</div>   