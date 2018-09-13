<?php
/*
 * setDetectors.php
 */

if(isset($_POST['submit']) AND $_POST['submit'] == 'Save configuration') {

    // Update table
    $i = 0;
    foreach ($_POST['name'] as $value) {

        $id = $_POST['id'][$i];
        $name = stripString($value);
        $slot = $_POST['slot'][$i];
        $channel = $_POST['channel'][$i];
        $comments = htmlentities($_POST['comments'][$i], ENT_QUOTES);
        $process = "0";
        
        // Delete if checked
        if(is_array($_POST['delete']) AND in_array($id, $_POST['delete'])) {
            
            $sth2 = $dbh->prepare("DELETE FROM detectors WHERE id = :id");
            $sth2->execute(array(':id' => $id));
        }
        elseif(str_replace(' ', '', $name) != '' AND str_replace(' ', '', $slot) != '' AND str_replace(' ', '', $channel) != '' AND is_numeric($channel) AND is_numeric($slot)) {
          
            $sth1 = $dbh->prepare("INSERT INTO detectors (id, name, mid, slot, channel, comments, process) 
                              VALUES (:id, :name, :mid, :slot, :channel, :comments, :process)
                              ON DUPLICATE KEY UPDATE name = :name, slot = :slot, channel = :channel, comments = :comments, process = :process");
            $sth1->bindParam(':id', $id);  
            $sth1->bindParam(':mid', $mid);  
            $sth1->bindParam(':name', $name, PDO::PARAM_STR);  
            $sth1->bindParam(':slot', $slot, PDO::PARAM_STR);
            $sth1->bindParam(':channel', $channel, PDO::PARAM_STR);
            $sth1->bindParam(':comments', $comments, PDO::PARAM_STR);
            $sth1->bindParam(':process', $process);
            $sth1->execute();
        }
        $i++;
    }   
}

// Select all detectors
$sth1 = $dbh->prepare("SELECT * FROM detectors WHERE mid = :mid");
$sth1->execute(array(':mid' => $mid));
$detectors = $sth1->fetchAll();
?>
<div class="content">
  
    <div style="display: inline">
      <h3 style="display: inline;">Manage detectors</h3> &nbsp;&nbsp;&mdash;&nbsp;&nbsp; <?php echo changeModule('setdetectors') ?> 
    </div>
    <br /><br />
    <?php 
    if(isset($_POST['submit'])) echo '<div class="pass">Configuration successfully saved.</div>'; 
    ?>
    <br />
    

    <form action="" method="POST">
    <table class="table" style="float: left;"  cellpadding="5px" cellspacing="0">
    <thead>
      <tr>
        <td width="30px" class="oddrow"></td>
        <td width="220px" class="oddrow">Detector name*</td>
        <td width="75px" class="oddrow">Slot</td>
        <td width="75px" class="oddrow">Channel</td>
        <td width="220px" class="oddrow">Comments</td>
        <td width="50px" class="oddrow">Delete</td>
        <td width="50px" class="oddrow">FEB</td>
        <td width="80px" class="oddrow">Group</td>
      </tr>
    </thead>
    <tbody>
    <?php
    $max=20;
    $i=0;
    if(10-no_detectors($mid) < 2) $max=$max+5; 
    
    foreach ($detectors as $value) {
      
        $disabled = ($value['process'] == 0) ? '' : 'disabled="disabled"';
        
        ?>
        <input size="22" name="id[]" value="<?php echo $value['id']; ?>" type="hidden">
        <tr>
            <td class="<?php echo ($i%2 == 0) ? 'evenrow' : 'oddrow'; ?>"></td>
            <td class="<?php echo ($i%2 == 0) ? 'evenrow' : 'oddrow'; ?>"><input <?php echo $disabled ?> size="22" name="name[]" value="<?php echo $value['name']; ?>" type="text" /></td>
            <td class="<?php echo ($i%2 == 0) ? 'evenrow' : 'oddrow'; ?>"><input <?php echo $disabled ?> size="4" name="slot[]" value="<?php echo $value['slot']; ?>" type="text" /></td>
            <td class="<?php echo ($i%2 == 0) ? 'evenrow' : 'oddrow'; ?>"><input <?php echo $disabled ?> size="4" name="channel[]" value="<?php echo $value['channel']; ?>" type="text"></td>
            <td class="<?php echo ($i%2 == 0) ? 'evenrow' : 'oddrow'; ?>"><input <?php echo $disabled ?> size="22" name="comments[]" value="<?php echo html_entity_decode($value['comments'], ENT_QUOTES); ?>" type="text"></td>
            <td class="<?php echo ($i%2 == 0) ? 'evenrow' : 'oddrow'; ?>"><input <?php echo $disabled ?> name="delete[]" type="checkbox" value="<?php echo $value['id']; ?>" /></td>
        </tr> 
        <?php
        $i++;
    }
    
    for($i; $i<$max; $i++) {
      
      ?>
      <tr>
          <td class="<?php echo ($i%2 == 0) ? 'evenrow' : 'oddrow'; ?>"></td>
          <td class="<?php echo ($i%2 == 0) ? 'evenrow' : 'oddrow'; ?>"><input size="22" name="name[]" type="text"></td>
          <td class="<?php echo ($i%2 == 0) ? 'evenrow' : 'oddrow'; ?>"><input size="4" name="slot[]" type="text"></td>
          <td class="<?php echo ($i%2 == 0) ? 'evenrow' : 'oddrow'; ?>"><input size="4" name="channel[]" type="text"></td>
          <td class="<?php echo ($i%2 == 0) ? 'evenrow' : 'oddrow'; ?>"><input size="22" name="comments[]" type="text"></td>
          <td class="<?php echo ($i%2 == 0) ? 'evenrow' : 'oddrow'; ?>"></td>
      </tr>     
      
      <?php
    }
    ?>
      <tr><td height="35px" valign="bottom" colspan="6"><input value="Save configuration" type="submit" name="submit" /></td></tr>
    </tbody>
    </table>
    </form>
</div>