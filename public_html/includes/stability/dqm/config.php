
<table style="margin-top: 5px;">
        
    <tr style="height: 25px;">
        
        <td valign="top" width="170px">Stability type:</td>
        <td valign="top" width="200px" ><?php echo $stability_types[$stability['type']]; ?></td>
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
        <td valign="top"><?php echo ($stability['time_end'] != 0) ? date('Y-m-d H:i:s', $stability['time_end']) : '-'; ?></td>
        <td colspan="2"></td>
    </tr>
                
    <tr style="height: 25px;">
        <td valign="top">Comments:</td>
        <td colspan="3" valign="top"><?php echo nl2br($stability['comments']); ?></td>
    </tr>
        
</table>
