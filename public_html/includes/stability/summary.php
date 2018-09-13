<?php
loadCSS("datetimepicker.css");
loadJS("datetimepicker.js");

if(isset($_GET['chamber'])) $chamber = $_GET['chamber'];
else $chamber = $longevity_chambers[0];

if(isset($_POST['updateStats'])) {

	putenv('ROOTSYS=/usr/local/root/');
	putenv('PATH=/usr/local/root/bin:'.getenv("PATH"));
	putenv('PATH=~/bin:./bin:.:'.getenv("PATH"));
	putenv('LD_LIBRARY_PATH='.getenv("ROOTSYS").'/lib:'.getenv("LD_LIBRARY_PATH"));
	putenv('DYLD_LIBRARY_PATH='.getenv("ROOTSYS").'/lib:$DYLD_LIBRARY_PATH');
	putenv('PYTHONPATH='.getenv("ROOTSYS").'/lib/:'.getenv("PYTHONPATH"));
	
	$command = escapeshellcmd('/home/webdcs/software/webdcs/scripts/longevity_analysis/statistics.py ' . $chamber . ' RAW');
	$output = shell_exec($command);	
}

if(isset($_POST['updatePlots'])) {
	
	putenv('ROOTSYS=/usr/local/root/');
	putenv('PATH=/usr/local/root/bin:'.getenv("PATH"));
	putenv('PATH=~/bin:./bin:.:'.getenv("PATH"));
	putenv('LD_LIBRARY_PATH='.getenv("ROOTSYS").'/lib:'.getenv("LD_LIBRARY_PATH"));
	putenv('DYLD_LIBRARY_PATH='.getenv("ROOTSYS").'/lib:$DYLD_LIBRARY_PATH');
	putenv('PYTHONPATH='.getenv("ROOTSYS").'/lib/:'.getenv("PYTHONPATH"));
	
	

	# NOTICE: chmod 775 python scripts!
	
	# Step 1: calculate RAW and CORR integrated charge
	$command = escapeshellcmd('/home/webdcs/software/webdcs/scripts/longevity_analysis/calculateQInt.py ' . $chamber . ' RAW');
	$output = shell_exec($command);
	
	$command = escapeshellcmd('/home/webdcs/software/webdcs/scripts/longevity_analysis/calculateQInt.py ' . $chamber . ' CORR');
	$output = shell_exec($command);
	
	# Arguments: ./plotQintCurrents.py RE2-2-NPD-BARC-9 CURR RAW ALL
	$command = escapeshellcmd('/home/webdcs/software/webdcs/scripts/longevity_analysis/plotQintCurrents.py ' . $chamber . ' CURR RAW ALL');
	$output = shell_exec($command);

	$command = escapeshellcmd('/home/webdcs/software/webdcs/scripts/longevity_analysis/plotQintCurrents.py ' . $chamber . ' CURR CORR ALL');
	$output = shell_exec($command);
	
	$command = escapeshellcmd('/home/webdcs/software/webdcs/scripts/longevity_analysis/plotQintCurrents.py ' . $chamber . ' QINT RAW ALL');
	$output = shell_exec($command);
	
	$command = escapeshellcmd('/home/webdcs/software/webdcs/scripts/longevity_analysis/plotQintCurrents.py ' . $chamber . ' QINT CORR ALL');
	$output = shell_exec($command);
	
	//echo '<pre>'.$output.'</pre>';
}



function plot($chamber, $gap, $plot, $mode) {
	
	return '<a href="/STABILITY/SUMMARY/'.$chamber.'/'.$chamber.'-'.$gap.'_'.$plot.'_'.$mode.'.png"><img width="100%" src="/STABILITY/SUMMARY/'.$chamber.'/'.$chamber.'-'.$gap.'_'.$plot.'_'.$mode.'.png" /></a>';
}

$chamberSel = "";
foreach($longevity_chambers as $ch) {
    
    $chamberSel .= ($chamber == $ch) ? '<option selected="selected" value="'.$ch.'" >'.$ch.'</option>' : '<option value="'.$ch.'" >'.$ch.'</option>';
}
?>

<script src="http://canvasjs.com/assets/script/canvasjs.min.js"></script>
<script>
$(document).ready(function() {
$('.qint-images').magnificPopup({
  delegate: 'a',
  type: 'image',
  gallery: {
            enabled: true,
            navigateByImgClick: true,
            preload: [0,1] // Will preload 0 - before current, and 1 after the current image
          },
});
});

$(function(){
	$('#changeChamber').on('change', function () {
		var trolley = $(this).val();
		window.location.href = 'index.php?q=longevity&p=summary&chamber=' + trolley;
	});
}); 

window.onload = function () {

	CanvasJS.addColorSet("greenShades",
		[//colorSet Array
			"#4d90fe",
            "#9f9f9f",
            "#2E8B57",
            "#3CB371",
            "#90EE90"                
         ]);

	var chart = new CanvasJS.Chart("chartContainer",
	{
		theme: "theme1",
		animationEnabled: false,
		colorSet: "greenShades",
		title:{
			text: "Longevity statistics <?=$chamber?>",
			fontSize: 15
		},
		toolTip: {
			shared: true
		},			
		axisY: {
			title: "Int. charge [mC/cm²]",
			labelFontSize: 12,
			labelFontColor: "black",
			titleFontSize: 14,
			titleFontColor: "black",
		},
		axisY2: {
			title: "Time efficiency [%]",
			labelFontSize: 12,
			labelFontColor: "black",
			titleFontSize: 14,
			titleFontColor: "black",
		},			
		data: [ 
		{
			type: "column",	
			name: "Integrated charge",
			indexLabel: "{y}",
			indexLabelOrientation: "vertical",
			legendText: "Integrated charge",
			showInLegend: true, 
			dataPoints:[<?php echo file_get_contents("/var/operation/STABILITY/SUMMARY/".$chamber."/".$chamber.".qint.chart") ?>]
		},
		{
			type: "column",	
			name: "Time efficiency",
			legendText: "Time efficiency",
			indexLabel: "{y}",
			indexLabelOrientation: "vertical",
			axisYType: "secondary",
			showInLegend: true,
			dataPoints:[<?php echo file_get_contents("/var/operation/STABILITY/SUMMARY/".$chamber."/".$chamber.".eff.chart") ?>]
		}
		],
		legend:{
            cursor:"pointer",
            itemclick: function(e){
              if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
              	e.dataSeries.visible = false;
              }
              else {
                e.dataSeries.visible = true;
              }
            	chart.render();
		}
	},
});

chart.render();
}
</script>


<h3 style="display: inline;">Longevity summary </h3>&nbsp;&nbsp;&mdash;&nbsp;&nbsp; Select chamber: <form style="display: inline;"><select id="changeChamber"><?php echo $chamberSel; ?></select></form>


<?php
if($_SESSION['userid'] == 6) {
?>
<br /><br />

<form action="" method="POST">
    <input type="submit" name="updatePlots" value="Update plots" />
	<input type="submit" name="updateStats" value="Update statistics" />
</form>
<?php
}
?>

<br /><br />



<div style="height: 320px; width: 45%; float: left;">
	<h3>Calculate integrated charge at timestamp</h3>
	
	<form action="" method="POST">
		<input id="time" name="time" type="text" value="<?php echo date("Y/m/d H:i", $t1); ?>" />
		<input type="submit" name="qint_time" value="Calculate" />
	</form>
	
	
	<script type="text/javascript">// <![CDATA[
    jQuery(function(){jQuery('#time').datetimepicker();});
    // ]]></script>
	

	
</div>

<div id="chartContainer" style="height: 300px; float: left;"></div>

<br /><br />

<div class="qint-images">
	
<style>
table.table tr:hover td { background-color: #FFF; }
</style>

<table class="table">
	
	<thead>
		<tr>
			<td style="width: 25%">Integrated Charge raw</td>
			<td style="width: 25%">Integrated Charge corrected</td>
			<td style="width: 25%">Current raw</td>
			<td style="width: 25%">Current corrected</td>
		</tr>
	</thead>
	
	<tbody>
		<tr>
			<td><?php echo plot($chamber, "TOT", "QINT", "RAW"); ?></td>
			<td><?php echo plot($chamber, "TOT", "QINT", "CORR"); ?></td>
			<td><?php echo plot($chamber, "TOT", "CURR", "RAW"); ?></td>
			<td><?php echo plot($chamber, "TOT", "CURR", "CORR"); ?></td>
		</tr>
		
		<tr><td colspan="4">&nbsp;</td></tr>
		
		<tr>
			<td><?php echo plot($chamber, "BOT", "QINT", "RAW"); ?></td>
			<td><?php echo plot($chamber, "BOT", "QINT", "CORR"); ?></td>
			<td><?php echo plot($chamber, "BOT", "CURR", "RAW"); ?></td>
			<td><?php echo plot($chamber, "BOT", "CURR", "CORR"); ?></td>
		</tr>
		
		<tr><td colspan="4">&nbsp;</td></tr>
		
		<tr>
			<td><?php echo plot($chamber, "TN", "QINT", "RAW"); ?></td>
			<td><?php echo plot($chamber, "TN", "QINT", "CORR"); ?></td>
			<td><?php echo plot($chamber, "TN", "CURR", "RAW"); ?></td>
			<td><?php echo plot($chamber, "TN", "CURR", "CORR"); ?></td>
		</tr>
		
		<tr><td colspan="4">&nbsp;</td></tr>
		
		<tr>
			<td><?php echo plot($chamber, "TW", "QINT", "RAW"); ?></td>
			<td><?php echo plot($chamber, "TW", "QINT", "CORR"); ?></td>
			<td><?php echo plot($chamber, "TW", "CURR", "RAW"); ?></td>
			<td><?php echo plot($chamber, "TW", "CURR", "CORR"); ?></td>
		</tr>
	</tbody>
	
</table>

</div>
