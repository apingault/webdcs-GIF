<?php
if(!defined('INDEX')) die("Access denied");

$installedTrolleys = installedTrolleys();
$selectedTrolley = $_GET['trolley'];
if($selectedTrolley == "") $selectedTrolley = 1;

$sth1 = $dbh->prepare("SELECT * FROM position WHERE trolley_id = :tid ORDER BY time DESC");
$sth1->execute(array(":tid" => $selectedTrolley));
$positions = $sth1->fetchAll();


if(isset($_POST['submit']) && getCurrentRole() != 0) {

    $xco = filter_input(INPUT_POST, 'x');
    $zco = filter_input(INPUT_POST, 'z');
    $time = filter_input(INPUT_POST, 'time');
    $date = filter_input(INPUT_POST, 'date');
    $comment = filter_input(INPUT_POST, 'comment');
    $positon_mode = filter_input(INPUT_POST, 'position_mode');
    list($d, $m, $y) = explode("-", $date);
    list($h, $min) = explode(":", $time);
    
    if(!is_numeric($xco)) {
        $error = "enter an x-coordinate.";      
    }
    elseif(!is_numeric($zco)) {
        $error = "enter an z-coordinate.";      
    }
    elseif(!is_numeric($h) || !is_numeric($min) || $h > 23 || $h < 0 || $min > 60 || $min < 0) {
        $error = "time not valid.";  
    }
    elseif(!checkdate($m , $d, $y)) {
        $error = "date not valid.";  
    }
    else {
        
        $tmp = mktime($h, $min, 0, $m, $d, $y);
        $sth1 = $dbh->prepare("INSERT INTO position (trolley_id, time, position, coordinate_x, coordinate_z, comment) VALUES ('".$selectedTrolley."', '".$tmp."', '".$positon_mode."', '".$xco."', '".$zco."', '".$comment."')");
        $sth1->execute();
        $pass = "trolley position successfully updated!";
    }
        
}

$trolleys = "";
foreach($installedTrolleys as $trolley) {
    
    $trolleys .= ($trolley['id'] == $selectedTrolley) ? '<option selected="selected" value="'.$trolley['id'].'" >'.$trolley['name'].'</option>' : '<option value="'.$trolley['id'].'" >'.$trolley['name'].'</option>';
}
 
?>

<script>
    $(function(){
      $('#changeTrolley').on('change', function () {
          var trolley = $(this).val();
          window.location.href = 'index.php?q=position&trolley=' + trolley;
      });
    });
</script>



<div class="content">

    <?php 
    if(!empty($error)) { echo '<div class="error">Error: '.$error.'</div>'; }
    elseif($pass != '') { echo '<div class="pass">'.$pass.'</div>'; }
    ?>

    <h3 style="display: inline;">Trolley position </h3>&nbsp;&nbsp;&mdash;&nbsp;&nbsp; Select trolley: <form style="display: inline;"><select id="changeTrolley"><?php echo $trolleys; ?></select></form>

    <br /><br />
    <b>Current position:</b> <?php echo $position_mode[$positions[0]['position']]; ?> since <?php echo date("Y-m-d H:i", $positions[0]['time']) ?><br />
    
    <?php 
    if(getCurrentRole() != 0) {?>
		<br />
		&raquo; <a href="#" id="editPositionLink">Update position</a>
		<br /><br />
	<?php } ?>
    
    
    <div id="editPositionDiv" class="hidden" style="border: 1px solid #000; padding: 10px;">
        <b>Warning: only update the position information in case of a physical change of the trolley position!</b><br /><br />
    <form method="post" action="" id="editPositionForm" onsubmit="return confirm('Do you really want to update the position of trolley <?php echo $trolley; ?>?');">
        <table>
            <tr style="height: 25px">
                <td width="150px">Update date :</td>
                <td><input id="date" name="date" type="text"> dd-mm-yyy</td>
            </tr>
            <tr style="height: 25px">
                <td width="150px">Update time :</td>
                <td><input id="time" name="time" type="text"> HH:mm</td>
            </tr>
            <tr style="height: 25px">
                <td width="150px">Position label:</td>
                <td>
                    <select name="position_mode">
                        <?php
                        foreach($position_mode as $key => $value) {
                            
                            echo '<option value="'.$key.'">'.$value.'</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr style="height: 25px">
                <td width="150px">Coordinate x:</td>
                <td><input id="x" name="x" type="text"> mm</td>
            </tr>
            <tr style="height: 25px">
                <td width="150px">Coordinate z:</td>
                <td><input id="z" name="z" type="text"> mm</td>
            </tr>
            <tr style="height: 25px">
                <td width="150px">Comment:</td>
                <td><input name="comment" type="text"></td>
            </tr>
        </table>

        <br />
        <input value="Update trolley position" type="submit" name="submit" /> 
    </form>
    </div>
    
 
    
    <br /><br />
    
    <table class="table">
        <thead style="font-weight: bold;"><tr>
            <td width="100px">Position label</td>
            <td width="100px">Position Id</td>
            <td width="130px">From ...</td>
            <td width="130px">To ...</td>
            <td width="100px">laser x</td>
            <td width="100px">laser z</td>
            <td width="100px">x</td>
            <td width="100px">z</td>
            <td width="240px">Comment</td>
         </tr></thead>
        <tbody>
        <?php
        
        $i = 0;
        $prev = "up to now";
        foreach ($positions as $pos) {

            echo '<tr>';
            echo '<td>'.$position_mode[$pos['position']].'</td>';
            echo '<td>'.$pos['id'].'</td>';
            echo '<td>'.date("Y-m-d H:i", $pos['time']).'</td>';
            echo '<td>'.$prev.'</td>';
            echo '<td>'.$pos['coordinate_x'].'</td>';
            echo '<td>'.$pos['coordinate_z'].'</td>';
            echo '<td>'.(3278-$pos['coordinate_x']).'</td>';
            echo '<td>'.(-6177+$pos['coordinate_z']).'</td>';
            echo '<td>'.$pos['comment'].'</td>';
            echo '</tr>';
            $prev = date("Y-m-d H:i", $pos['time']);
            $i++;
        }
        ?>
        </tbody>
    </table>
    <br /><br />


</div>
