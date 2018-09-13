<?php

require_once '../config/config.php';

$mid = settings('current_mid');

$sth = $dbh->prepare("SELECT * FROM modules WHERE id = :mid");
$sth->execute(array(':mid' => $mid));
$detector = $sth->fetch();

$stringaexec = $config['exe_dir'].'HVmon '.$mid;
exec($stringaexec, $return);

//echo '<pre>';
//print_r($return);
//echo '</pre>';

// If single-line error: global CAEN connection error
if(count($return) == 1) {
    
    echo json_encode($return[0]);
    
    exit();
}
else {
   
    $i=0;
    foreach ($return as $value) {
        
        $data = explode(" ", $value);
        //$data = array_values(array_filter($data, '_remove_empty_internal')); // Remove empty array values    
        foreach($data as $key=>$value) {
            if(is_null($value) || $value == '')
            unset($data[$key]);
        }

        $data = array_values($data);

        // If multiple-line error: detector connection error
        if($value == "detector_connection_error") {
            
            $post[$i][0] = $data[0]; // detecor ID
            $post[$i][5] = $data[1]; // Error
        }
        else {

            $post[$i][0] = $data[0]; // ID
            $post[$i][1] = $data[1]; // Vset
            $post[$i][2] = $data[2]; // Vmon
            $post[$i][3] = $data[3]; // Iset
            $post[$i][4] = $data[4]; // Imon
            $post[$i][5] = $data[5]; // Status
            $post[$i][6] = $data[6]; // Pw 
            $post[$i][7] = $data[7]; // Process: 1 for HVscan, 2 for Stabilitytest, 0 for no process, 3 for terminating stabillitytest, 4 for suspended
        }
        $i++;
    }
}

echo json_encode($post);
exit();

?>