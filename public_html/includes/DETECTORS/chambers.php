<script type="text/javascript">
$(document).ready(function() {

	var buttonId;

    $("#chamberForm").submit(function(e) {

		if (buttonId  == "formDelete") {
		 
			var d = confirm('Do you really want to delete this chamber?');
			if(d) return true
			else return false;
		}
    });
});
</script>
<?php

switch($_GET['action']) {
    
    
    default: overview(); break;
    case 'add': edit(true); break;
    case 'edit': edit(false); break;
}


function sel($name, $no) {
    
    $str = '<select name="'.$name.'">';
    for($i = 1; $i <= $no; $i++)  $str .= '<option value="'.$i.'">'.$i.'</option>';
    $str .= '</select>';
    
    return $str;
}


function edit($add = false) {
    
    global $DB, $ICONS, $daq_types;
    
    if($add) {
        $res = array();
        $title = "Add";
       
    }
    else {
     
        $id = $_GET['id'];
        $title = "Edit";

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
        
    }
    
    if(isset($_POST['submit'])) {
        
        
        $name = $_POST['name'];
        $slot = $_POST['position'];
        $gaps = $_POST['gaps'];
        $partitions = $_POST['partitions'];
        $strips = $_POST['strips'];
		$area = $_POST['area'];
		$HV_WP = $_POST['HV_WP'];
		$HV_STBY = $_POST['HV_STBY'];
		$area = $_POST['area'];
        $mapping = $_POST['mapping'];
        $dimensions = $_POST['dimensions'];
        $trolley = 0;
        
      	if($add) {

    		$sth1 = $DB['MAIN']->prepare("INSERT INTO chambers (id, name, trolley, slot, gaps, partitions, strips, area, dimensions, mapping, daq_type, HV_WP, HV_STBY, status, enabled) VALUES ('', :name, :trolley, :slot, :gaps, :partitions, :strips, :area, :dimensions, :mapping, 'DEFAULT', :HV_WP, :HV_STBY, 0, 0) "); 
        
			$sth1->bindParam(':name', $name, PDO::PARAM_STR); 
			$sth1->bindParam(':trolley', $trolley); 
			$sth1->bindParam(':slot', $slot); 
			$sth1->bindParam(':gaps', $gaps);
			$sth1->bindParam(':partitions', $partitions);
			$sth1->bindParam(':strips', $strips);
			$sth1->bindParam(':area', $area);
			$sth1->bindParam(':dimensions', $dimensions);
			$sth1->bindParam(':mapping', $mapping);
			$sth1->bindParam(':HV_WP', $HV_WP);
			$sth1->bindParam(':HV_STBY', $HV_STBY);
			if(!$sth1->execute()) {
				print_r($sth1->errorInfo());
				die('<div class="error">Error: failed to submit to the database (1).</div>');
			}
		}
		else {

    		$sth1 = $DB['MAIN']->prepare("UPDATE chambers SET name = :name, slot = :slot, gaps = :gaps, partitions = :partitions, strips = :strips, area = :area, dimensions = :dimensions, mapping = :mapping, HV_WP = :HV_WP, HV_STBY = :HV_STBY WHERE id = :id"); 
        
			$sth1->bindParam(':name', $name, PDO::PARAM_STR); 
			$sth1->bindParam(':slot', $slot); 
			$sth1->bindParam(':gaps', $gaps);
			$sth1->bindParam(':partitions', $partitions);
			$sth1->bindParam(':strips', $strips);
			$sth1->bindParam(':area', $area);
			$sth1->bindParam(':dimensions', $dimensions);
			$sth1->bindParam(':mapping', $mapping);
			$sth1->bindParam(':HV_WP', $HV_WP);
			$sth1->bindParam(':HV_STBY', $HV_STBY);
            $sth1->bindParam(':id', $id);
			if(!$sth1->execute()) {
				print_r($sth1->errorInfo());
				die('<div class="error">Error: failed to submit to the database (1).</div>');
			}        

		}
        
        echo header("Location: index.php?q=detectors");
        
    }
    
    if(isset($_POST['delete'])) {
        
        $sth1 = $DB['MAIN']->prepare("DELETE FROM chambers WHERE id = :id");
        $sth1->bindParam(':id', $id);
        if(!$sth1->execute()) {
            print_r($sth1->errorInfo());
			die('<div class="error">Error: failed to submit to the database (1).</div>');
		}
    }
    

    
    
    
    
    ?>

    <h3 style="display: inline;"><?=$title?> chamber</h3>
    
    
    <br /><br />
    
    <form action="" method="POST" id="chamberForm">
    
    <table cellspacing="0" cellpadding="0px" style="margin-top: 5px;">
		
        
        <tr>
			
            <td width="150px">Name:</td>
            <td width="150px"><input type="text" name="name" value="<?php echo $res['name']; ?>" /></td>
			
            <td rowspan="9" width="300px">Dimensions: <textarea name="dimensions" rows="15" cols="30"><?php echo $res['dimensions']; ?></textarea></td>	
            <td rowspan="9" width="300px">Default DAQ mapping: <textarea name="mapping" rows="15" cols="30"><?php echo $res['mapping']; ?></textarea></td>

        </tr> 
			
        <tr>
			
            <td width="150px">Slot:</td>
            <td width="220px">
                <select name="position" style="width: 140px">
                    <?php for($i = 1; $i < 11; $i++) {
                        
                        $sel = ($res['slot'] == $i) ? 'selected="selected"' : '';
                        echo '<option '.$sel.' value="'.$i.'">'.$i.'</option>';
                                
                    }?>
                </select>
            </td>
			
        </tr>
		
	<tr style="height: 25px;">
		
            <td width="150px">Gaps:</td>
            <td width="220px">
                <select name="gaps" style="width: 140px">
                    <?php for($i = 1; $i < 6; $i++) {
                        $sel = ($res['gaps'] == $i) ? 'selected="selected"' : '';
                        echo '<option '.$sel.' value="'.$i.'">'.$i.'</option>'; 
                    }?>
                </select>
            </td>
	
        </tr>
		
	<tr style="height: 25px;">
	
            <td width="150px">Partitions:</td>
            <td width="220px">
                <select name="partitions" style="width: 140px">
                    <?php for($i = 1; $i < 6; $i++) {
                        $sel = ($res['partitions'] == $i) ? 'selected="selected"' : '';
                        echo '<option '.$sel.' value="'.$i.'">'.$i.'</option>'; 
                    }?>
                </select>
            </td>
        </tr>
		
        <tr style="height: 25px;">
            <td width="150px" class="leftBorder">Strips:</td>
            <td width="150px"><input type="text" name="strips" value="<?php echo $res['strips']; ?>" /></td>
        </tr>
		
        <tr style="height: 25px;">
            <td width="150px" class="leftBorder">Area:</td>
            <td width="150px"><input type="text" name="area" value="<?php echo $res['area']; ?>" /></td>
        </tr>
		
        <tr style="height: 25px;">
            <td width="150px" class="leftBorder">HV working point:</td>
            <td width="150px"><input type="text" name="HV_WP" value="<?php echo $res['HV_WP']; ?>" /></td>
        </tr>
				
        <tr style="height: 25px;">
            <td width="150px" class="leftBorder">HV Standby</td>
            <td><input type="text" name="HV_STBY" value="<?php echo $res['HV_STBY']; ?>" /></td>
        </tr>
		
			
        
        <tr style="height: 25px;">
			<td>DAQ type:</td>
                        <td>
                            <select name="daqtype">
					<?php

					foreach($daq_types as $key => $val) {
						if(settings("daqtype") == $key) $sel = 'selected="selected"';
						else $sel = "";
						echo '<option '.$sel.' value="'.$key.'">'.$val.'</option>';
					}
					?>
					</select>
                            
                        </td>
		</tr>
			
		

		
		
        
	</table>
	
	<br /><br />
        <input value="Save configuration" type="submit" name="submit" /> 

	
	
	
	
       
            
    </form>
    
    <?php
}



function overview() {
    
    global $DB, $ICONS, $ICON_CROSS, $ICON_TICK, $ICON_EDIT;
    
    // Select all chambers
    $sth1 = $DB['MAIN']->prepare("SELECT * FROM chambers ORDER by trolley, slot ASC");
    $sth1->execute();
    $chambers = $sth1->fetchAll();

    if(isset($_POST['submit'])) {

        foreach ($chambers as $value) {

            if($_POST['en'.$value['id']] == "on") $enabled = 1;
            else $enabled = 0;

            $sth1 = $DB['MAIN']->prepare("UPDATE chambers SET enabled = ".$enabled." WHERE id = ".$value['id']);
            $sth1->execute();
            header("Refresh:0");
        }
    }

    ?>

    <div style="display: inline">
        <h3 style="display: inline;">Configure chambers</h3>&nbsp;&nbsp;&mdash;&nbsp;&nbsp; <a href="index.php?q=detectors&action=add">Add chamber</a>

    </div>

    <br /><br />

    <form action="" method="POST">
    <table class="table">

        <thead>
        <tr>
            <td width="50px">Action</td>
            <td width="160x">Gap name</td>
            <td width="70px">Position</td>
            <td width="60px">Gaps</td>
            <td width="40px">Partitions</td>
            <td width="40px">Strips</td>
            <td width="40px">DAQ type</td>
            <td width="80px">HV WP</td>
            <td width="80px">HV STBY</td>
        </tr>
        </thead>

        <tbody>
        <?php
        $i = 0;
        foreach ($chambers as $value) {

            $trolley = $value['trolley'];
            $slot = $value['slot'];

            if($trolley == 0) $pos = "S".$slot;
            else $pos = "T".$trolley.'S'.$slot;

                    $en = ($value['enabled'] == 1) ? 'checked="checked"' : '';
            $img = ($value['enabled'] == 1) ? $ICON_TICK : $ICON_CROSS;


            echo '<td>'.$img.'&nbsp;&nbsp; <a href="index.php?q=detectors&action=edit&id='.$value['id'].'">'.$ICON_EDIT.'</a> <input type="checkbox" name="en'.$value['id'].'" '.$en.' /></td>';

            echo '<td>'.$value['name'].'</td>';
            echo '<td>'.$pos.'</td>';
            echo '<td>'.$value['gaps'].'</td>';
            echo '<td>'.$value['partitions'].'</td>';
            echo '<td>'.$value['strips'].'</td>';
            echo '<td>'.$value['daq_type'].'</td>';
            echo '<td>'.$value['HV_WP'].'</td>';
            echo '<td>'.$value['HV_STBY'].'</td>';

            echo '</tr>';
                    /*
            foreach($detectors as $det) {

                            if($det['trolley'] != $trolley or $det['slot'] != $slot) continue;



                echo '<tr>';
                            echo '<td>'.$img.'</td>';
                echo '<td>T'.$trolley.'_S'.$slot.'</td>';
                echo '<td>'.$det['name'].'</td>';
                echo '<td>'.$det['CAEN_slot'].'</td>';
                echo '<td>'.$det['CAEN_channel'].'</td>';
                echo '<td>'.$det['ADC_slot'].'</td>';
                echo '<td>'.$det['ADC_channel'].'</td>';
                echo '<td>';
                echo ($det['DAQ'] == 0) ? $ICON_TICK : $ICON_CROSS;;
                echo '</td>';
                echo '<td>';
                echo ($det['RCURR'] == 0) ? $ICON_TICK : $ICON_CROSS;;
                echo '</td>';
                            echo '<td>';
                echo ($det['stability'] == 0) ? $ICON_TICK : $ICON_CROSS;
                echo '</td>';
                echo '<td>';
                echo ($det['RCURR'] == 0) ? '-' : $det['ADC_resistor'];
                echo '</td>';
                            echo '<td>'.$det['hv_wp'].'</td>';
                            echo '<td>'.$det['hv_standby'].'</td>';
                echo '</tr>';

            }*/
            $i++;
        }
        ?>
        </tbody>
    </table>

    <br />
    <input type="submit" name="submit" value="Save">

    </form>
    
    <?php
    }
 ?>
