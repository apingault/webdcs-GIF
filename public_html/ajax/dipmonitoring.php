<?php

require_once 'includes/DIP/functions.php';

$name = "";
$value = "";
$unit = "";
$flow_tot = 0;

function formatted($id_name, $nameOver = "", $unitOver = "") {
	
	$str = "";
	$name = "";
	$value = "";
	$unit = "";
	
	getValue($id_name, $value, $name, $unit);
	
	if($nameOver != "") $name = $nameOver;
	if($unitOver != "") $unit = $unitOver;
	
	$str .= "<tr>";
	$str .= "<td width=\"50%\">".$name.":</td>";
	$str .= "<td width=\"50%\">".$value." ".$unit."</td>";
	$str .= "</tr>";
	
	return $str;
}


?>

<style>
.DIPTable td { height: 20px; }
</style>

<div style="width: 1000px; overflow: hidden; ">
	
	<div style="width: 500px; float:left;">
	
		<table class="DIPTable">
        <tr>
            <td colspan="2" width="400px"><b>Source parameters:</b></td>
        </tr>
        <tr>
            <td width="50%">Source status:</td>
            <td width="50%">
                <?php 
				getValue("SourceON", $value, $name, $unit);
				echo ($value == 1) ? '<font color="green"><b>SOURCE ON</b></font>' : '<font color="red"><b>SOURCE OFF</b></font>' 
				?>
            </td>
        </tr>
		
        <tr>
            <td width="50%">Upstream attenuation:</td>
            <td width="50%">
                <?php 
				getValue("AttUA", $AttUA, $name, $unit);
				getValue("AttUB", $AttUB, $name, $unit);
				getValue("AttUC", $AttUC, $name, $unit);
				getValue("AttUEff", $AttUEff, $name, $unit);
				echo $AttUEff.' ['.$AttUA.' '.$AttUB.' '.$AttUC.']';
				?>
            </td>
        </tr>
		
        <tr>
            <td width="50%">Downstream attenuation:</td>
            <td width="50%">
                <?php 
				getValue("AttDA", $AttDA,$name,  $unit);
				getValue("AttDB", $AttDB,$name,  $unit);
				getValue("AttDC", $AttDC,$name,  $unit);
				getValue("AttDEff", $AttDEff,$name,  $unit);
				echo $AttDEff.' ['.$AttDA.' '.$AttDB.' '.$AttDC.']';
				?>
            </td>
        </tr>
		
		
		<tr>
            <td colspan="2" width="400px"></td>
        </tr>
		
		
		<tr>
            <td colspan="2" width="400px"><b>Gas parameters:</b></td>
        </tr>
		
		<?php echo formatted("C2H2F4"); ?>
		<?php echo formatted("iC4H10"); ?>
		<?php echo formatted("SF6"); ?>
		
		<?php echo formatted("100*[C2H2F4]/([C2H2F4]+[iC4H10]+[SF6])", "Mixture C2H2F4", "%"); ?>
		<?php echo formatted("100*[iC4H10]/([C2H2F4]+[iC4H10]+[SF6])", "Mixture iC4H10", "%"); ?>
		<?php echo formatted("100*[SF6]/([C2H2F4]+[iC4H10]+[SF6])", "Mixture SF6", "%"); ?>
		
		<?php echo formatted("mixture_with_water"); ?>
		<?php echo formatted("mixture_without_water"); ?>
		<?php echo formatted("iC4H10_BINOS1"); ?>
		<?php echo formatted("iC4H10_BINOS2"); ?>
		<?php echo formatted("P102"); ?>
		<?php echo formatted("T102"); ?>
		<?php echo formatted("RH102"); ?>
		
		
		</table>
		
		
		
		
	</div>
	
	
	
	<div style="overflow: hidden;">

		<table class="DIPTable">
        <tr>
            <td colspan="2" width="500px"><b>Environmental parameters:</b></td>
        </tr>

		
		<?php echo formatted("P201"); ?>
		<?php echo formatted("T201"); ?>
		<?php echo formatted("RH201"); ?>
		
		<?php echo formatted("P"); ?>
		<?php echo formatted("TIN"); ?>
		<?php echo formatted("RHIN"); ?>
		
		<?php echo formatted("P202"); ?>
		<?php echo formatted("T202"); ?>
		<?php echo formatted("RH202"); ?>
		
		
		<tr><td></td><td></td></tr>
		
		<tr><td colspan="2"><b>Radmon dose measurements:</b></td></tr>
		
		<?php echo formatted("D1"); ?>
		<?php echo formatted("D2"); ?>
		<?php echo formatted("D3"); ?>
		<?php echo formatted("D4"); ?>
		<?php echo formatted("D5"); ?>
		<?php echo formatted("D6"); ?>
		<?php echo formatted("D7"); ?>
		<?php echo formatted("D8"); ?>
		
		</table>		
		
		
	</div>
	
</div>