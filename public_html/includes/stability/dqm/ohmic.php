<?php



if(isset($_POST['submit'])) {
    
    $p0 = floatval($_POST['p0']);
    $p1 = floatval($_POST['p1']);
    $ref = $_POST['ref'];
    
    $sth1 = $dbh->prepare("UPDATE stability_qint SET p0 = :p0, p1 = :p1, hvscan_id = :ref WHERE runid = :runid AND detectorid = :detectorid"); 
    $sth1->bindParam(':p0', $p0); 
    $sth1->bindParam(':p1', $p1); 
    $sth1->bindParam(':ref', $ref); 
    $sth1->bindParam(':runid', $id);
    $sth1->bindParam(':detectorid', $currentGapId);
    $sth1->execute();
    
    $source = $dir.'/'.$currentGapName.'.dat';
    $target = $dir.'/'.$currentGapName.'.qint';
    $p0_corr = $p0*$currentGapArea;
    $p1_corr = $p1*$currentGapArea;

    shell_exec("/home/offanalysis/software/qint/qint_run ".$source." ".$target." ".$p0_corr." ".$p1_corr);
}
else { // load current values
    
    // Get current values
    $sth1 = $dbh->prepare("SELECT * FROM stability_qint WHERE runid = $id AND detectorid = $currentGapId");
    $sth1->execute();
    $ohmic = $sth1->fetch();
    $p0 = $ohmic['p0'];
    $p1 = $ohmic['p1'];
    $ref = $ohmic['hvscan_id'];
}


$disabled = false;
if($run['status'] == 2) {
    
    $disabled = true;
}
    $disabled = true;
?>

Update <b>surface normalized</b> ohmic regression coefficients and recalculate the integrated charge.
<br /><br />

<form method="POST" action=""  <?php if(!$run) echo 'onsubmit="return confirm(\'Do you really want to update the ohmic coefficients and recalculate the integrated charge?\');"'; ?>>

<table>
    
    <tr>
        <td width="130px" style="height: 20px;">Detector name</td>
        <td><?=$currentGapName?></td>
    </tr> 
    
    <tr>
        <td style="height: 20px;">Area</td>
        <td><?=$currentGapArea?> cm²</td>
    </tr> 
    
    <tr>
        <td style="height: 30px;">Offset (p0)</td>
        <td><input size="10" name="p0" type="text" value="<?=$p0?>" /> (uA/cm²)</td>
    </tr>
        
    <tr>
        <td style="height: 30px;">Slope (p1)</td>
        <td><input size="10" name="p1" type="text" value="<?=$p1?>" /> (uA/Vcm²)</td>
    </tr> 
    
    <tr>
        <td style="height: 30px;">Reference HVscan ID</td>
        <td><input size="10" name="ref" type="text" value="<?=$ref?>" /></td>
    </tr> 
    
    <tr>
        <td colspan="2" style="height: 30px;"><input type="submit" <?php echo ($disabled) ? 'disabled="disabled"' : ""; ?> name="submit" value="Update" /></td>
    </tr> 
        
</table>