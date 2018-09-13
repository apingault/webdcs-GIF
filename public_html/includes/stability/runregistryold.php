<?php

$sth1 = $dbh->prepare("SELECT * FROM stability ORDER BY id DESC");
$sth1->execute();
$hvscans = $sth1->fetchAll();

?>
 
<table cellpadding="5px" cellspacing="0">

    <thead style="font-weight: bold;">
        <tr>

        <td class="oddrow" width="12%">Run ID</td>
        <td class="oddrow" width="20%">Start time</td>
        <td class="oddrow" width="20%">End time/Last action</td>
        <td class="oddrow" width="25%">Duration</td>
        <td class="oddrow" width="30%">Comment</td>
        <td class="oddrow" width="13%">Status</td>
        
        </tr>
    </thead>
    
    <tbody>
    <?php
        
    $i = 0;
    foreach ($hvscans as $run) {
            
        $class = ($i%2 == 0) ? 'evenrow' : 'oddrow';
        echo '<tr data-href="index.php?q=longevity&p=rundqm&id='.$run['id'].'" class="'.$class.' clickable-row">';
        printf('<td>%06d</td>', $run['id']);
        echo '<td>'.date('Y-m-d H:i', $run['time_start']).'</td>';
        echo '<td>'.date('Y-m-d H:i', $run['time_end']).'</td>';
        echo '<td>'.secondsToTime($run['time_end'] - $run['time_start']).'</td>';
        echo '<td>'.$run['comments'].'</td>';
        echo '<td>'.getFormattedStatus($run['status']).'</td>';
        echo '</tr>';
        $i++;
    }
    ?>
    
    </tbody>
</table>

