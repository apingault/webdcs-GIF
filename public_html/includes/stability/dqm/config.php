<?php

if(isset($_POST['save'])) {
    
    $attU = filter_input(INPUT_POST, 'attenuator');
    $comments = filter_input(INPUT_POST, 'comments');


    $sth1 = $dbh->prepare("UPDATE stability SET comments = :comments, attU = :attU WHERE id = ".$stability['id']);
    $sth1->bindParam(':comments', htmlspecialchars($comments), PDO::PARAM_STR);
    $sth1->bindParam(':attU', $attU, PDO::PARAM_STR);
    $sth1->execute();
    
    header("Refresh:0");
}

$attssel = "";
foreach($attenuators as $att) {
    
    $sel = ($stability['attU'] == $att) ? 'selected="selected"' : '';
    $attssel .= '<option '.$sel.' value="'.$att.'">'.effAttenuation($att).'</option>';
    
}

?>


<form action="" method="POST" id="hvscanForm">
<table style="margin-top: 5px;">
        
    <tr style="height: 25px;">
        
        <td valign="top" width="170px">Stability type:</td>
        <td valign="top" width="400px" ><?php echo $stability_types[$stability['type']]; ?></td>
        <td valign="top" width="150px">Status:</td>
        <td valign="top" width="200px"><?php echo getFormattedStatus($stability['status']); ?>
        </td>
        
    </tr> 
        
    <tr style="height: 25px;">
        <td valign="top">Run start:</td>
        <td valign="top"><?php echo date('Y-m-d H:i:s', $stability['time_start']) ?></td> 
        <td valign="top">Duration:</td>
        <td valign="top"><?php echo secondsToTime($stability['last_action'] - $stability['time_start']); ?></td>          
    </tr>
        
    <tr style="height: 25px;">
        <td valign="top">Run end/last action:</td>
        <td valign="top"><?php echo ($stability['time_end'] != 0) ? date('Y-m-d H:i:s', $stability['last_action']) : '-'; ?></td>
        <td valign="top">Upstream attenuator:</td>
        <td valign="top"><select style="width: 100px;" name="attenuator"><?php echo $attssel; ?></select></td>
    </tr>
                
    <tr style="height: 25px;">
        <td valign="top">Comments:</td>
        <td colspan="3"><textarea name="comments" style="font-size: 12px; height: 74px; width: 280px;"><?php echo $stability['comments']; ?></textarea></td>
        <td colspan="2"></td>
    </tr>
    
    <tr style="height: 25px;">
        <td valign="top"></td>
        <td valign="top"><input <?php echo (getCurrentRole() == 0) ? 'disabled="disabled"' : ''; ?> type="submit" name="save" id="formSave" value="Save changes" /></td>
    </tr>
    
    				

        
</table>
</form>
