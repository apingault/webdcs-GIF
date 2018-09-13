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

        $sth1 = $DB['MAIN']->prepare("SELECT * FROM gaps WHERE id = ".$id);
        $sth1->execute();
        $res = $sth1->fetch();

        if(count($res) == 0) {
                msg("No gap found", "error");
                die();
        }

        $trolley = $res['trolley'];
        $slot = $res['slot'];

        if($trolley == 0) $pos = "S".$slot;
        else $pos = "T".$trolley.'S'.$slot;
        
    }
    
    if(isset($_POST['submit'])) {
        
        
        $name = $_POST['name'];
        $CAEN_channel = $_POST['CAEN_channel'];
        $CAEN_slot = $_POST['CAEN_slot'];
		$area = $_POST['area'];
        $chamberid = $_POST['chamberid'];
        
      	if($add) {

    		$sth1 = $DB['MAIN']->prepare("INSERT INTO gaps (id, name, CAEN_channel, CAEN_slot, chamberid, area) VALUES ('', :name, :CAEN_channel, :CAEN_slot, :chamberid, :area) "); 
        
			$sth1->bindParam(':name', $name, PDO::PARAM_STR); 
			$sth1->bindParam(':CAEN_slot', $CAEN_slot); 
			$sth1->bindParam(':CAEN_channel', $CAEN_channel);
			$sth1->bindParam(':area', $area);
			$sth1->bindParam(':chamberid', $chamberid);
			if(!$sth1->execute()) {
				print_r($sth1->errorInfo());
				die('<div class="error">Error: failed to submit to the database (1).</div>');
			}
		}
		else {

    		$sth1 = $DB['MAIN']->prepare("UPDATE gaps SET name = :name, CAEN_slot = :CAEN_slot, CAEN_channel = :CAEN_channel, chamberid = :chamberid, area = :area WHERE id = :id"); 
        
			$sth1->bindParam(':name', $name, PDO::PARAM_STR); 
			$sth1->bindParam(':CAEN_slot', $CAEN_slot); 
			$sth1->bindParam(':CAEN_channel', $CAEN_channel);
			$sth1->bindParam(':area', $area);
			$sth1->bindParam(':chamberid', $chamberid);
            $sth1->bindParam(':id', $id);
			if(!$sth1->execute()) {
				print_r($sth1->errorInfo());
				die('<div class="error">Error: failed to submit to the database (1).</div>');
			}        

		}
        
        echo header("Location: index.php?q=detectors&p=gaps");
        
    }
    
    if(isset($_POST['delete'])) {
        
        $sth1 = $DB['MAIN']->prepare("DELETE FROM gaps WHERE id = :id");
        $sth1->bindParam(':id', $id);
        if(!$sth1->execute()) {
            print_r($sth1->errorInfo());
			die('<div class="error">Error: failed to submit to the database (1).</div>');
		}
    }
    
    // select all chambers
    $sth1 = $DB['MAIN']->prepare("SELECT * FROM chambers");
    $sth1->execute();
    $chambers = $sth1->fetchAll();

    
    ?>

    <h3 style="display: inline;"><?=$title?> chamber</h3>
    
    
    <br /><br />
    
    <form action="" method="POST" id="chamberForm">
    
    <table cellspacing="0" cellpadding="0px" style="margin-top: 5px;">
		
        
        <tr style="height: 25px;">
			
            <td width="150px">Name:</td>
            <td width="400px"><input type="text" name="name" value="<?php echo $res['name']; ?>" /> (w/o chamber name, e.g. TOP, BOT)</td>
			

        </tr> 
        
        <tr style="height: 25px;">
			
            <td width="150px">Chamber:</td>
            <td width="220px">
                <select name="chamberid" style="width: 140px">
                    <?php foreach($chambers as $val) {
                        
                        $sel = ($res['chamberid'] == $val['id']) ? 'selected="selected"' : '';
                        echo '<option '.$sel.' value="'.$val['id'].'">'.$val['name'].'</option>';
                                
                    }?>
                </select>
            </td>
        </tr>
			
        <tr style="height: 25px;">
			
            <td width="150px">CAEN channel:</td>
            <td width="220px">
                <select name="CAEN_channel" style="width: 140px">
                    <?php for($i = 0; $i < 6; $i++) {
                        
                        $sel = ($res['CAEN_channel'] == $i) ? 'selected="selected"' : '';
                        echo '<option '.$sel.' value="'.$i.'">'.$i.'</option>';
                                
                    }?>
                </select>
            </td>
        </tr>
		
        <tr style="height: 25px;">
		
            <td width="150px">CAEN slot:</td>
            <td width="220px">
                <select name="CAEN_slot" style="width: 140px">
                    <?php for($i = 0; $i < 20; $i++) {
                        $sel = ($res['CAEN_slot'] == $i) ? 'selected="selected"' : '';
                        echo '<option '.$sel.' value="'.$i.'">'.$i.'</option>'; 
                    }?>
                </select>
            </td>
	
        </tr>
		
		
        <tr style="height: 25px;">
            <td width="150px" class="leftBorder">Area:</td>
            <td width="150px"><input type="text" name="area" value="<?php echo $res['area']; ?>" /></td>
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
    $sth1 = $DB['MAIN']->prepare("SELECT g.*, c.name AS chambername FROM gaps g, chambers c WHERE g.chamberid = c.id ORDER BY id ASC");
    $sth1->execute();
    $chambers = $sth1->fetchAll();

    if(isset($_POST['submit'])) {

        foreach ($chambers as $value) {
            
            if($_POST['en'.$value['id']] == "on") $enabled = 1;
            else $enabled = 0;
            
            $sth1 = $DB['MAIN']->prepare("UPDATE gaps SET enabled = ".$enabled." WHERE id = ".$value['id']);
            $sth1->execute();
            header("Refresh:0");
        }
    }

    ?>


    <div style="display: inline">
        <h3 style="display: inline;">Configure gaps</h3>&nbsp;&nbsp;&mdash;&nbsp;&nbsp; <a href="index.php?q=detectors&p=gaps&action=add">Add gap</a>
    </div>

    <br /><br />

    <form action="" method="POST">
    <table class="table">
        
        <thead>
        <tr>
        <td width="50px">Action</td>
            <td width="200x">Gap name</td>
            <td width="100px">CAEN HV</td>
            <td width="100px">Area (cm2)</td>
        </tr>
        </thead>
        
        <tbody>
        <?php
        $i = 0;
        foreach ($chambers as $value) {
     
            $CAENHV = sprintf("%02d.%03d", $value['CAEN_slot'], $value['CAEN_channel']);
            

            
            $en = ($value['enabled'] == 1) ? 'checked="checked"' : '';
        $img = ($value['enabled'] == 1) ? $ICON_TICK : $ICON_CROSS;
        echo '<td>'.$img.'&nbsp;&nbsp; <a href="index.php?q=detectors&p=gaps&action=edit&id='.$value['id'].'">'.$ICON_EDIT.'</a> <input type="checkbox" name="en'.$value['id'].'" '.$en.' /></td>';

            
            
       // echo '<td>'.$img.'&nbsp;&nbsp; <input type="checkbox" name="en'.$value['id'].'" '.$en.' /></td>';
            
        echo '<td>'.$value['chambername'].'-'.$value['name'].'</td>';
        echo '<td>'.$CAENHV.'</td>';
        echo '<td>'.$value['area'].'</td>';
        echo '</tr>';

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
