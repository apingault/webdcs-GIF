<script>
$(function(){
    $('#periods').on('change', function () {
        var url = $(this).val(); // get selected value
        if (url) { // require a URL
            window.location = url; // redirect
        }
    return false;
    });
});

$(document).ready(function() {
$('.daq-images').magnificPopup({
  delegate: 'a',
  type: 'image',
  gallery: {
            enabled: true,
            navigateByImgClick: true,
            preload: [0,1] // Will preload 0 - before current, and 1 after the current image
          },
});
});
</script>

<?php

// Return option list for each month of data taking
function getPeriodOptions($periods, $currentPeriod) {
    
    foreach($periods as $period) {
        
        $year = substr($period, 0, 4);
        $month = substr($period, 4, 6);
        $dateObj   = DateTime::createFromFormat('!m', $month);
        $monthName = $dateObj->format('F'); // March
        $sel = ($currentPeriod == $period) ? 'selected="selected"' : '';
        echo '<option '.$sel.' value="index.php?q=daqresults&period='.$period.'">'.$year." ".$monthName.'</option>';
    }
}

// Search for all the ROOT files in the /mnt/nfs/   
$files = glob("/mnt/nfs/daq_data/DAQ/*.root");
usort($files, create_function('$a,$b', 'return filemtime($b) - filemtime($a);'));


// Get different data taking periods (delimeter year and month)
$periods = array();
foreach($files as $file) {
    
    // Extract YEARMONTH based on last modification date of file
    $date_modif = filemtime($file); // ROOT file last modification date
    $scandate = date('Y', $date_modif).date('m', $date_modif);
    
    // Extract YEARMONTH based on run ID
    //$scandate = get_string_between($file, "_run", ".root"); // returns 20160419184012
    //$scandate = substr($scandate, 0, 6);
   
    if(!in_array($scandate, $periods) && $scandate != "") array_push($periods, $scandate);
}


// Get selected period
if(!isset($_GET['period'])) $period = $periods[0];
else {
    if(in_array($_GET['period'], $periods)) $period = $_GET['period'];
    else {
        $period = false;
    }
}
   
if(isset($_GET['file'])) {
    
    // Check if the ROOT file is found and the corresponding directory exists with the images
    $found = false;
    foreach($files as $file) {
        
        // Check if 
        if($_GET['file'] == basename($file)) {
            $found = true;
            break;
        }
    }
    
    if($found) {
        
        echo '<div class="content">';
        echo '<h3>DAQ Scan results </h3>';
        
        echo "Filename:<br />";
        echo "HVScan name:<br />";
        echo "HVScan ID:<br />";
        echo "Source status:<br />";
        echo "Beam status:<br />";
        echo "Start-end time:<br />";
        echo "Trigger information:<br />";
        echo "High Voltage information:<br /><br />";
        
        echo '<a href="/DATA/DQM/DAQ/'.basename($file, ".root").'.root">&raquo; Download ROOT file</a><br />';
        echo '<a href="'.$file.'">&raquo; Download images files</a>';
        
        
        echo '</div>';
        
        echo '<div style="width: 90%; margin: 0 auto; margin-top: 40px;">';
        echo '<div class="daq-images">';
        
        echo '<a href="/DATA/DQM/DQM/'.basename($file, ".root").'/RPC_Hit_Profile_T1_S1_A.png"><img width="33%" src="/DATA/DQM/DQM/'.basename($file, ".root").'/RPC_Hit_Profile_T1_S1_A.png" /></a>';
        echo '<a href="/DATA/DQM/DQM/'.basename($file, ".root").'/RPC_Hit_Profile_T1_S1_B.png"><img width="33%" src="/DATA/DQM/DQM/'.basename($file, ".root").'/RPC_Hit_Profile_T1_S1_B.png" /></a>';
        echo '<a href="/DATA/DQM/DQM/'.basename($file, ".root").'/RPC_Hit_Profile_T1_S1_C.png"><img width="33%" src="/DATA/DQM/DQM/'.basename($file, ".root").'/RPC_Hit_Profile_T1_S1_C.png" /></a>';
        
        echo '<img width="33%" src="/DATA/DQM/DQM/'.basename($file, ".root").'/RPC_Hit_Multiplicity_T1_S1_A.png" />';
        echo '<img width="33%" src="/DATA/DQM/DQM/'.basename($file, ".root").'/RPC_Hit_Multiplicity_T1_S1_B.png" />';
        echo '<img width="33%" src="/DATA/DQM/DQM/'.basename($file, ".root").'/RPC_Hit_Multiplicity_T1_S1_C.png" />';
        
        echo '<img width="33%" src="/DATA/DQM/DQM/'.basename($file, ".root").'/RPC_Instant_Noise_T1_S1_A.png" />';
        echo '<img width="33%" src="/DATA/DQM/DQM/'.basename($file, ".root").'/RPC_Instant_Noise_T1_S1_B.png" />';
        echo '<img width="33%" src="/DATA/DQM/DQM/'.basename($file, ".root").'/RPC_Instant_Noise_T1_S1_C.png" />';
        
        echo '<img width="33%" src="/DATA/DQM/DQM/'.basename($file, ".root").'/RPC_Mean_Noise_T1_S1_A.png" />';
        echo '<img width="33%" src="/DATA/DQM/DQM/'.basename($file, ".root").'/RPC_Mean_Noise_T1_S1_B.png" />';
        echo '<img width="33%" src="/DATA/DQM/DQM/'.basename($file, ".root").'/RPC_Mean_Noise_T1_S1_C.png" />';
        
        echo '<img width="33%" src="/DATA/DQM/DQM/'.basename($file, ".root").'/RPC_Time_Profile_T1_S1_A.png" />';
        echo '<img width="33%" src="/DATA/DQM/DQM/'.basename($file, ".root").'/RPC_Time_Profile_T1_S1_B.png" />';
        echo '<img width="33%" src="/DATA/DQM/DQM/'.basename($file, ".root").'/RPC_Time_Profile_T1_S1_C.png" />';
        
        
        
        echo '</div></div>';
    }
    else {
        if(!$period) echo '<div class="error">No file (yet) found. The analysis process can still be ongoing, try again later.</div>';
    }
}
else {
    
    echo '<div class="content">';
    echo '<h3>DAQ Scan results </h3>';
    
    if(!$period) echo '<div class="error">Error: selected period not found.</div>';
    
    // Load period picker
    echo '<form action="" method="post" id="period">';
    echo 'Select period: &nbsp; <select id="periods">';
    echo getPeriodOptions($periods, $period);
    echo '</select>';
    echo '</form>';
    echo '<br />';
    
    // Display all records
    echo '<table class="table monitor" cellpadding="5px" cellspacing="0">';
    echo '<thead><tr><td class="oddrow" width="800px">ROOT Files</td></tr></thead>';
    echo '<tbody>';
    $i = 0;
    foreach($files as $file) {
            
        // We show results if the period matches, or if the period is false
        if(strpos($file, $period) !== false || !$period) {
            
            $class = ($i%2 == 0) ? 'evenrow' : 'oddrow';
            echo '<tr><td class="'.$class.'" style="line-height: 20px;"><a href="index.php?q=daqresults&file='.basename($file).'">'.basename($file).'</a></td></tr>';
            $i++;
        }
    }
    echo '</tbody>';
    echo '</table>';
    echo '<br /><br />';
}

