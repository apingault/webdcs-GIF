<?php

$id = $_GET['id'];


// Select trolleys
$sth1 = $DB['MAIN']->prepare("SELECT * FROM chambers WHERE id = ".$id);
$sth1->execute();
$res = $sth1->fetch();

if(count($res) == 0) {
	msg("No chamber found", "error");
	die();
}

$trolley = $res['trolley'];
$slot = $res['slot'];
		
if($trolley == 0) $pos = "S".$slot;
else $pos = "T".$trolley.'S'.$slot;

?>

<h3>Edit chamber</h3>


<form action="" method="POST" id="hvscanForm">
    
	<table cellspacing="0" cellpadding="0px" style="margin-top: 5px;">
		
		<tr style="height: 25px;">
			
            <td width="150px">Name:</td>
            <td width="150px"><?php echo $res['name']; ?></td>  
			
			<td width="300px">Dimensions:</td>
			
			<td width="300px">Default DAQ mapping:</td>

        </tr> 
			
        <tr>
			
			<td width="120px">Position:</td>
            <td width="220px"><?php echo $pos; ?></td>
			
			<td rowspan="9" width="300px">
				<textarea rows="15" cols="30"><?php echo $res['dimensions']; ?></textarea>
			</td>
			
			<td rowspan="9" width="300px">
				<textarea rows="15" cols="30"><?php echo $res['mapping']; ?></textarea>
			</td>
			
        </tr>
		
		<tr style="height: 25px;">
			<td width="150px">Gaps:</td>
            <td width="150px"><?php echo $res['gaps']; ?></td>
		</tr>
		
		<tr style="height: 25px;">
			<td width="150px">Partitions:</td>
            <td width="150px"><?php echo $res['partitions']; ?></td>
		</tr>
		
		<tr style="height: 25px;">
			<td width="150px" class="leftBorder">Strips:</td>
            <td width="150px"><?php echo $res['strips']; ?></td>
		</tr>
		
		<tr style="height: 25px;">
			<td width="150px" class="leftBorder">Area:</td>
            <td width="150px"><?php echo $res['area']; ?></td>
		</tr>
		
		<tr style="height: 25px;">
			<td width="150px" class="leftBorder">HV working point:</td>
            <td width="150px"><?php echo $res['HV_WP']; ?></td>
		</tr>
				
		<tr style="height: 25px;">
			 <td>HV Standby</td>
            <td><?php echo $res['HV_STBY']; ?></td>
		</tr>
		
			
        
        <tr style="height: 25px;">
			<td>DAQ type:</td>
            <td><?php echo $res['daq_type']; ?></td>
		</tr>
			
		
			
		<tr style="height: 25px;">
			<td class="leftBorder">Enabled:</td>
            <td><?php echo "d"; ?></td>
        </tr>
		
		
        
	</table>
	
	<br />
	
	
	
	
       
            
</form>
