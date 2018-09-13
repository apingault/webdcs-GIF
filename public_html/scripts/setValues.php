<?php


if(isset($_POST['poweroff'])) {
  
  // Get ID
  $t = array_keys($_POST['poweroff']);
  $id = $t[0];
  $command = $config['exe_dir'].'SetPower_single';
  $stringaexec = $command.' '.$mid.' '.$_POST['slot_'.$id].' '.$_POST['channel_'.$id].' 0';
  exec($stringaexec, $return);
  $error = ($return[0] == "1") ? '' : $return[0];
}

if(isset($_POST['poweron'])) {
  
  // Get ID
  $t = array_keys($_POST['poweron']);
  $id = $t[0];
  $command = $config['exe_dir'].'SetPower_single';
  $stringaexec = $command.' '.$mid.' '.$_POST['slot_'.$id].' '.$_POST['channel_'.$id].' 1';
  exec($stringaexec, $return);
  $error = ($return[0] == "1") ? '' : $return[0];
}


if(isset($_POST['submit_hvset'])) {
  
  // Get ID
  $t = array_keys($_POST['submit_hvset']);
  $id = $t[0];
  $hvset = $_POST['hvset_'.$id.''];
  $command = $config['exe_dir'].'SetV0_single';
  $stringaexec = $command.' '.$mid.' '.$_POST['slot_'.$id].' '.$_POST['channel_'.$id].' '.$hvset;
  exec($stringaexec, $return);
  $error = ($return[0] == "1") ? '' : $return[0];
}


if(isset($_POST['submit_i0set'])) {
  
  // Get ID
  $t = array_keys($_POST['submit_i0set']);
  $id = $t[0];
  $i0set = $_POST['i0set_'.$id.''];
  $command = $config['exe_dir'].'SetI0_single';
  $stringaexec = $command.' '.$mid.' '.$_POST['slot_'.$id].' '.$_POST['channel_'.$id].' '.$i0set;
  exec($stringaexec, $return);
  $error = ($return[0] == "1") ? '' : $return[0];
}


if(isset($_POST['submit_tripset'])) {
  
  // Get ID
  $t = array_keys($_POST['submit_tripset']);
  $id = $t[0];
  $tripset = $_POST['tripset_'.$id.''];
  $command = $config['exe_dir'].'SetTrip_single';
  $stringaexec = $command.' '.$mid.' '.$_POST['slot_'.$id].' '.$_POST['channel_'.$id].' '.$tripset;
  exec($stringaexec, $return);
  $error = ($return[0] == "1") ? '' : $return[0];
}



?>