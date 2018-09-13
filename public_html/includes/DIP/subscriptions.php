<?php

$sth1 = $DB['DIP']->prepare("SELECT * FROM subscriptions ORDER BY category ASC, name ASC");
$sth1->execute();
$subscriptions = $sth1->fetchAll();

?>

    <h3 style="display: inline;">DIP subscriptions</h3>
    
    <br /><br />

    <table class="table">
        <thead style="font-weight: bold;"><tr>
            <td width="130px">Parameter name</td>
            <td width="50px">Unit</td>
            <td width="50px">Type</td>
            <td width="100px">Category</td>
            <td width="100px">DIP Identifier</td>
            <td width="300px">DIP Subscription</td>
			<td width="100px">Parameter ID</td>
         </tr></thead>
        <tbody>
		<?php
		foreach($subscriptions as $sub) {
                    
                    if(!in_array($sub['table_name'], $DIP_SUBSCRIPTIONS)) continue;
			
			echo '<tr>';
			echo '<td>'.$sub['name'].'</td>';
			echo '<td>'.$sub['unit'].'</td>';
			echo '<td>'.$sub['type'].'</td>';
			echo '<td>'.$sub['category'].'</td>';
			echo '<td>'.$sub['dip_identifier'].'</td>';
			echo '<td>'.$sub['dip_subscription'].'</td>';
			echo '<td>'.$sub['id_name'].'</td>';
			echo '</tr>';
		}
		
		?>
			
		</tbody>
			
	</table>
	