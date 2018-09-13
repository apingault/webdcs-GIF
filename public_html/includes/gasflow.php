<?php

$installedTrolleys = installedTrolleys();
$selectedTrolley = $_GET['trolley'];
if($selectedTrolley == "") $selectedTrolley = 1;

loadCSS("datetimepicker.css");
loadJS("datetimepicker.js");

if(isset($_GET['id'])) {
    
    $sth1 = $dbh->prepare("SELECT * FROM gasflow WHERE id = ".$_GET['id']);
    $sth1->execute();
    $res = $sth1->fetch();
    
    $time = $res['time'];
    $comments = $res['comments'];
    $newGasflow = false;
}
else {
    
    $time = time();
    $comments = "";
    $newGasflow = true;
}

if(isset($_POST['submit'])) {
    

    if($_POST['time'] == "") $error = "Test";
    else {
        
        $time = strtotime($_POST['time']);
        $comments = filter_input(INPUT_POST, 'comments');
        
        // Get detectors for current trolley
        $sth1 = $dbh->prepare("SELECT * FROM detectors WHERE trolley = ".$selectedTrolley);
        $sth1->execute();
        $res = $sth1->fetchAll();
        
        if($newGasflow) { // INSERT
        
            // Insert into gasflow table
            $sth1 = $dbh->prepare("INSERT INTO gasflow (trolley, time, comments) VALUES (:trolley, :time, :comments) "); 
            $sth1->bindParam(':trolley', $selectedTrolley); 
            $sth1->bindParam(':time', $time); 
            $sth1->bindParam(':comments', $comments); 
            $sth1->execute();
            $gasflowid = $dbh->lastInsertId(); // Get ID

            // Insert into gasflow_gaps table

            foreach($res as $det) {

                $gasflow = floatval($_POST['DETID'.$det['id']]);
                if(!is_float($gasflow) || $gasflow < 0) $gasflow = 0.0;

                $sth1 = $dbh->prepare("INSERT INTO gasflow_gaps (gasflowid, detectorid, gasflow) VALUES (:gasflowid, :detectorid, :gasflow)");
                $sth1->bindParam(':gasflowid', $gasflowid); 
                $sth1->bindParam(':detectorid', $det['id']); 
                $sth1->bindParam(':gasflow', $gasflow); 
                $sth1->execute();
                $res = $sth1->fetchAll();
            }
        }
        else { // UPDATE

            $sth1 = $dbh->prepare("UPDATE gasflow SET time = :time, comments = :comments WHERE id = :gasflowid "); 
            $sth1->bindParam(':time', $time); 
            $sth1->bindParam(':comments', $comments); 
            $sth1->bindParam(':gasflowid', $_GET['id']); 
            $sth1->execute();
              
            foreach($res as $det) {

                $gasflow = floatval($_POST['DETID'.$det['id']]);
                if(!is_float($gasflow) || $gasflow < 0) $gasflow = 0.0;

                $sth1 = $dbh->prepare("UPDATE gasflow_gaps SET gasflow = :gasflow WHERE gasflowid = :gasflowid AND detectorid = :detectorid");
                $sth1->bindParam(':gasflowid', $_GET['id']); 
                $sth1->bindParam(':detectorid', $det['id']); 
                $sth1->bindParam(':gasflow', $gasflow); 
                $sth1->execute();
            }
        }
    }
}

if(isset($_POST['deletegasflow'])) {
    
    $sth1 = $dbh->prepare("DELETE FROM gasflow WHERE id = ".$_GET['id']); 
    $sth1->execute(); 
    
    $sth1 = $dbh->prepare("DELETE FROM gasflow_gaps WHERE gasflowid = ".$_GET['id']); 
    $sth1->execute(); 
}


$sth1 = $dbh->prepare("SELECT * FROM gasflow WHERE trolley = $selectedTrolley ORDER BY id DESC");
$sth1->execute();
$gasflow = $sth1->fetchAll();



$trolleys = "";
foreach($installedTrolleys as $trolley) {
    
    $trolleys .= ($trolley['id'] == $selectedTrolley) ? '<option selected="selected" value="'.$trolley['id'].'" >'.$trolley['name'].'</option>' : '<option value="'.$trolley['id'].'" >'.$trolley['name'].'</option>';
}

// Get all slots for selected trolley
$sth1 = $dbh->prepare("SELECT * FROM detectors WHERE trolley = $selectedTrolley GROUP BY slot ORDER by slot ASC");
$sth1->execute();
$slots = $sth1->fetchAll();


function getGasFlowValue($gasflowid, $detectorid) {
     
    global $dbh;
    $sth1 = $dbh->prepare("SELECT gasflow FROM gasflow_gaps WHERE gasflowid = $gasflowid AND detectorid = $detectorid");
    $sth1->execute();
    $res = $sth1->fetch();
    return $res['gasflow'];
}


?>

<script>
    $(function(){
      $('#changeTrolley').on('change', function () {
          var trolley = $(this).val();
          window.location.href = 'index.php?q=gasflow&trolley=' + trolley;
      });
    });
</script>


<div class="content">
    
    <h3 style="display: inline;">Gas flows </h3>&nbsp;&nbsp;&mdash;&nbsp;&nbsp; Select trolley: <form style="display: inline;"><select id="changeTrolley"><?php echo $trolleys; ?></select></form>

    <br /><br />
    
    <form method="post" action="">
        <table>
            
            <tr style="height:25px">
                <td width="150px">Time:</td>
                <td><input name="time" id="time" value="<?php echo date("Y/m/d H:i", $time); ?>" type="text"></td>
            </tr>
            <?php
            foreach($slots as $slot) {
                
                // Select detectors in current slot
                $sth1 = $dbh->prepare("SELECT * FROM detectors WHERE trolley = $selectedTrolley AND slot = ".$slot['slot']." ORDER by name ASC");
                $sth1->execute();
                $res = $sth1->fetchAll();
                
                ?>
                <tr style="height: 25px">
                    <td>Gas flow T<?=$selectedTrolley?>_S<?=$slot['slot']?>:</td>
                    <td>
                        <?php
                        foreach($res as $t) {
                            
                            // Get gas flow value
                            if($newGasflow) $value = "";
                            else $value = getGasFlowValue($_GET['id'], $t['id']);
                            
                            $g = explode('-', $t['name']); // get possix
                            echo '<input style="width: 70px;" name="DETID'.$t['id'].'" value="'.$value.'" placeholder="'.end($g).'" type="text">';
                            echo '&nbsp;';  
                        }
                        echo ' ('.$slot['chamber'].')';
                        ?>
                    </td>
                </tr>
                
            
                <?php
            }
            ?>
            <tr style="height: 25px">
                <td valign="top">Comments:</td>
                <td><textarea rows="3" style="width: 250px;" name="comments"><?php echo $comments; ?></textarea></td>            
            </tr>
                
            <tr style="height: 25px">
                <td></td>
                <td><input type="submit" value="<?php echo ($newGasflow) ? 'Add flows' : 'Update flows'; ?>" name="submit" /><?php echo (!$newGasFlow) ? ' <input type="submit" name="deletegasflow" value="Delete flag" />' : ''; ?></td>            
            </tr>
        </table>
    </form>
    
    <br /><br />

    <table class="table">

        <thead style="font-weight: bold;">
            <tr>
                <td width="10%">ID</td>
                <td width="20%">From ...</td>
                <td width="20%">To ...</td>
                <td width="50%">Comment</td>
            </tr>
        </thead>

        <tbody>
        <?php

        $i = 0;
        $prev = "up to now";
        foreach ($gasflow as $f) {

            $class = ($i%2 == 0) ? 'evenrow' : 'oddrow';
            echo '<tr data-href="index.php?q=gasflow&trolley='.$selectedTrolley.'&id='.$f['id'].'" class="'.$class.' clickable-row">';
            echo '<td>'.$f['id'].'</td>';
            echo '<td>'.date("Y-m-d H:i", $f['time']).'</td>';
            echo '<td>'.$prev.'</td>';
            echo '<td>'.$f['comments'].'</td>';
            echo '</tr>';
            $prev = date("Y-m-d H:i", $f['time']);
            $i++;
        }
        ?>

        </tbody>
    </table>
    
    
    <script type="text/javascript">
        jQuery(function(){jQuery('#time').datetimepicker();});
    </script>
    
</div>