<?php
$sth1 = $DB['MAIN']->prepare("SELECT * FROM PMON_LOG ORDER BY time DESC");
$sth1->execute();
$logs = $sth1->fetchAll();
?>


<div class="content">
  
	<div style="display: inline">
      <h3 style="display: inline;">WebDCS LOG</h3>
    </div>
    <br /><br />
		
    <table class="table">
    <thead>
      <tr>
		<td width="120px">Time</td>
        <td width="150px">PMON name</td>
        <td width="650x">Message</td>
        <td width="80px">Status</td>
      </tr>
    </thead>
	
    <tbody>
    <?php
    $i = 0;
    foreach ($logs as $log) {
		
		$sth1 = $DB['MAIN']->prepare("SELECT name FROM PMON WHERE id = ".$log['pmon_id']);
		$sth1->execute();
		$d = $sth1->fetch();

        echo '<tr>';
		echo '<td>'.date('Y-m-d H:i', $log['time']).'</td>';
        echo '<td>'.$d[0].'</td>';
        echo '<td>'.$log['message'].'</td>';
        echo '<td>'.systemCodesFormatted($log['status']).'</td>';
        echo '</tr>';
			
    }
    ?>
    </tbody>
    </table>
    
</div>

