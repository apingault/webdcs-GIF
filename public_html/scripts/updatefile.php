<?php

$step = 6; // Steps used for calculating the average
$press = array();
$temp = array();
$rh = array();
$time = array();

if(isset($_POST['updatefile'])) {
    
    if($_FILES["file"]["error"][0] > 0) $error = "error while uploading weather file.";
    elseif($_FILES["file"][1]["error"][1] > 0) $error =  "error while uploading data file.";
    else {
        
        $filename = $_FILES["file"]["name"][1];
        $weather = $_FILES["file"]["tmp_name"][0];
        $data = $_FILES["file"]["tmp_name"][1];
        
        $a=0; // tmp Pressure
        $c=0; // tmp Humidity
        $b=0;
        
        $v = file($weather);
        $i=-12; // neglect 12 first lines of file
        foreach($v as $line_num => $line) {
            if($i > 0) {
                $parts = preg_split('/\s+/', $line);
                $a += $parts[3];
                $b += $parts[5];
                $c += $parts[6];
                if($i%$step == 0) {
                    array_push($press, $a/$step); // store average
                    array_push($temp, $b/$step); // store average
                    array_push($rh, $c/$step); // store average           	
                    $date = date_parse_from_format('d-m-y/H:i:s', $parts[7]);
                    array_push($time, date("d-m-Y H:i:s", strtotime($date['day'].'-'.$date['month'].'-'.$date['year'].' '.$date['hour'].':'.$date['minute'].':'.$date['second'])));
                    $a=$b=$c=0; // reset   
                }
            }
            $i++;
        }
        
        $w = file($data);
        if(!($fh = fopen($filename, 'a'))) {
                $error = "can't open file";
        }
        else {
            $i=0;
            $end = end($time);
            reset($time);
            $error = false;
            foreach($w as $line_num => $line) {

                //reset($time); // reset pointer to beginning of array
                $current = current($time); // Get current index of time array

                $parts = explode(' ', $line);
                if(isset($parts[1])) {
                    $date = date_parse_from_format('YmdHi', $parts[1]);
                    $date = date("d-m-Y H:i:s", strtotime($date['day'].'-'.$date['month'].'-'.$date['year'].' '.$date['hour'].':'.$date['minute'].':'.$date['second']));

                    // Find the matching index from the $time array
                    if($i==0) {



                        $j=0;
                        $start = false;
                        foreach($time as $t) {

                            if($date < $t) {

                                $start = $j; // set start index
                                break;
                            }
                            $j++;
                        }

                        // If no index is found --> break the script
                        // This means that the start time doesn't overlap with the $time array
                        if($start == false) {

                            $error = true;
                            break;
                        } 
                        else {

                            // set array pointer to $start (i.e. previous)
                            prev($time);
                            prev($time);
                        }
                    }


                    // If the weather file is too short to cover the data file
                    if($date > $end) {

                        $error = true;
                        break;
                    }
                    else {

                        $index = key($time);
                        if($date < $time[$index+1]) {

                            $str = $parts[0].' '.$parts[1].' '.$parts[2].' '.$parts[3].' '.$parts[4].' '.$parts[5].' '.$parts[6].' '.$parts[7].' '.$parts[8].' '.$temp[$index-1].' '.$rh[$index-1];
                            fwrite($fh, $str.PHP_EOL);
                        }
                        else {
                            $str = $parts[0].' '.$parts[1].' '.$parts[2].' '.$parts[3].' '.$parts[4].' '.$parts[5].' '.$parts[6].' '.$parts[7].' '.$parts[8].' '.$temp[$index].' '.$rh[$index];
                            fwrite($fh, $str.PHP_EOL);
                            next($time);
                        } 
                    }
                $i++;
                }
            }
            fclose($fh);

            if($error) {

                $error = "Time ranges of both files do not overlap.";
            }

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header("Content-Disposition: attachment; filename=".basename($filename));
            header("Content-Transfer-Encoding: binary");
            header('Content-Length: ' . filesize($filename));
            ob_clean();
            flush();
            readfile($filename);
            //exit;
            unlink($filename);
        }
    }
}