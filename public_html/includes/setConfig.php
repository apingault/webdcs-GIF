<?php
/*
 * setConfig.php
 */

//require_once 'config/readConfigFile.php'; 
?>


<script type="text/javascript"> 
  $(document).ready(function() { 
    
    <?php
    if(!$hvscan) echo 'disableForms(false);';
    else echo 'disableForms(true);';
    ?> 
  }); 
   
</script> 
<div class="content">

<?php

// Save config file
if(isset($_POST['submit']) AND $_POST['submit'] == 'Save configuration') {
  
  $handle = fopen($file, 'w'); // open file for writing
  if($handle == FALSE) $error = "Can't open the file"; 
  
  $namecaen = str_replace(" ", "-", $_POST['namecaen']);
  if(!empty($namecaen)) fwrite($handle, $namecaen."\n");
  else $error = "enter a power supply name.";
  
  $ipaddress = $_POST['ipaddress'];
  if(!empty($ipaddress)) fwrite($handle, $ipaddress."\n");
  else $error = "enter an IP address.";
  
  $chambers = $_POST['chamber'];
  $channels = $_POST['channel'];
  $slots = $_POST['slot'];
  
  for($i = 0; $i < 12; $i++) {
    if(str_replace(" ", "", $chambers[$i]) != "") {
      
      // Check if slot/channel input is OK
      if($slots[$i] == "" OR $channels[$i] == "" OR !is_numeric($slots[$i]) OR !is_numeric($channels[$i])) {
        
        $error = "enter a valid slot or channel.";
        break;
      }
      fwrite($handle, str_replace(" ", "_", $chambers[$i])." ".$slots[$i]." ".$channels[$i]." \n");
    }
  }
  fclose($handle);
  $chambers = str_replace(" ", "_", $chambers);
}

?>

 <?php 
    if(!empty($error)) echo '<div class="error">Error: '.$error.'</div>'; 
    elseif(isset($_POST['submit'])) echo '<div class="pass">Configuration successfully saved.</div>'; 
  ?>
 
  <form method="post" action="" id="detectors-form">
    <table>
      <tr>
        <td width="150px">IP address:</td>
        <td><input size="18" name="ipaddress" value="<?php echo $ipaddress; ?>" type="text"></td>
        <td width="200px">CAEN Power supply Username:</td>
        <td><input size="18" name="ipaddress" value="<?php echo $ipaddress; ?>" type="text"></td>        
      </tr>
      <tr>
        <td>Power supply name*:</td>
        <td><input size="18" name="namecaen" value="<?php echo $namecaen; ?>" type="text"></td>
        <td>CAEN Power supply password*:</td>
        <td><input size="18" name="namecaen" value="<?php echo $namecaen; ?>" type="text"></td>
      </tr>
    </table>
  
  <br /><br />

  <table class="chambers_con" cellpadding="5px" cellspacing="0">
    <thead>
      <tr>
        <td class="oddrow">DETECTOR NAME*</td>
        <td class="oddrow">SLOT</td>
        <td class="oddrow">CHANNEL</td>
        <td class="oddrow">COMMENT</td>
      </tr>
    </thead>
    <tbody>
    <?php
    for($i=0; $i<12; $i++) {
      
      ?>
      <tr>
        <td class="<?php echo ($i%2 == 0) ? 'evenrow' : 'oddrow'; ?>"><input size="22" name="chamber[<?php echo $i ?>]" value="<?php echo $chambers[$i] ?>" type="text"></td>
        <td class="<?php echo ($i%2 == 0) ? 'evenrow' : 'oddrow'; ?>"><input size="4" name="slot[<?php echo $i ?>]" value="<?php echo $slots[$i] ?>" type="text"></td>
        <td class="<?php echo ($i%2 == 0) ? 'evenrow' : 'oddrow'; ?>"><input size="4" name="channel[<?php echo $i ?>]" value="<?php echo $channels[$i] ?>" type="text"></td>
        <td class="<?php echo ($i%2 == 0) ? 'evenrow' : 'oddrow'; ?>"><input size="22" name="chamber[<?php echo $i ?>]" value="<?php echo $chambers[$i] ?>" type="text"></td>
      </tr>     
      
      <?php
    }
    ?>
    </tbody>
  </table>
  <br />
  <input value="Save configuration" type="submit" name="submit" />  <font style="margin-left: 10px; font-size: 10px;">*use only characters/numbers</font>

</div>