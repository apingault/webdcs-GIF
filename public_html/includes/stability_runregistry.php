<?php

$sth1 = $dbh->prepare("SELECT * FROM stability ORDER BY id DESC");
$sth1->execute();
$hvscans = $sth1->fetchAll();

?>

<div class="content">
    <h3 style="display: inline;">Stability Run Registry </h3>
    <br /><br />

    
 
    <table cellpadding="5px" cellspacing="0">
        <thead style="font-weight: bold;"><tr>
            <td class="oddrow" width="60px">Run ID</td>
            <td class="oddrow" width="135px">Start time</td>
            <td class="oddrow" width="135px">End time/Last action</td>
            <td class="oddrow" width="135px">Duration (days)</td>
            <td class="oddrow" width="60px">Status</td>
         </tr></thead>
        <tbody>
        <?php
        
        $i = 0;
        foreach ($hvscans as $run) {
            
            $class = ($i%2 == 0) ? 'evenrow' : 'oddrow';
            echo '<tr class="'.$class.'">';
            printf('<td><a href="index.php?q=stability_result&id=%d">%06d</td></a>', $run['id'], $run['id']);
            echo '<td>'.date('Y-m-d H:i', $run['time_start']).'</td>';
            echo ($run['time_end'] == NULL) ? '<td>-</td>' : '<td>'.date('Y-m-d H:i', $run['time_end']).'</td>';
            echo '<td>'.$run['duration'].'</td>';
            echo '<td>';
            switch($run['status']) {
                case '1' : echo '<font color="blue"><b>FINISHED</b></font>'; break;
                case '0' : echo '<font><b>ONGOING</b></font>'; break;
                case '2' : echo '<font color="red"><b>KILLED</b></font>'; break;
                case '3' : echo '<font color="green"><b>APPROVED</b></font>'; break;
                case '4' : echo '<font><b>RESUMED</b></font>'; break;
            }
            echo '</td>';
            echo '</tr>';
            $i++;
        }
        ?>
        </tbody>
    </table>
    <br /><br />

