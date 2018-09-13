<?php
$attenuators = array("000", "999", "111", "112", "113", "121", "122", "123", "131", "132", "133", "211", "212", "213", "221", "222", "223", "231", "232", "233", "311", "312", "313", "321", "322", "323", "331", "332", "333");

function getHVfromAtt($att) {
	
	global $dbh;
	global $stability;
	global $currentGapId;
	$sth1 = $dbh->prepare("SELECT * FROM stability_VOLTAGES WHERE stabilityid = ".$stability['id']." AND detectorid = $currentGapId AND attU = $att");
    $sth1->execute();
    $res = $sth1->fetch();
	
	return $res["HV"];
}


?>
<table class="table">
	
	<thead>
		<tr>
			<td width="100px">Att U</td>
			<td width="100px">Att U eff</td>
			<td width="100px">HV<sub>eff</sub></td>
			<td width="700px"></td>
		</tr>
	</thead>
	
	<tbody>
	
<?php
foreach($attenuators as $att) {
	
	echo '<tr>';
	
    if($att == "000") {
		$txt = 'Source OFF'; 
		$eff = '';
	}
    elseif($att == "999") {
		$txt = 'STANDBY';
		$eff = '';
	}
    else {
		$txt = $att;
		$eff = effAttenuation($att);
	}
	
	echo '<td>'.$txt.'</td><td>'.$eff.'</td><td>'.getHVfromAtt($att).'</td>';
	
	echo '<td></td>';
	echo '</tr>';
}


?>
	</tbody>
</table>

