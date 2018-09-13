<?php
if(!defined('INDEX')) die("Access denied");

// Get Scan ID and retrieve information
$id = $_GET['id'];
$idstring = sprintf("%06d", $id);
$sth1 = $dbh->prepare("SELECT * FROM stability WHERE id = $id");
$sth1->execute();
$stability = $sth1->fetch();
$dir = sprintf("/var/operation/STABILITY/%06d", $id);

// Check if ID is valid
if($sth1->rowCount() == 0) {
    echo '<div class="content"><div class="error">Error: stability run ID not found</div></div>';
    exit(1);
}



// Get all gaps in run
$sth1 = $dbh->prepare("SELECT d.name FROM stability_VOLTAGES v, detectors d WHERE v.stabilityid = '".$id."' AND v.detectorid = d.id GROUP BY v.detectorid ORDER BY v.detectorid");
$sth1->execute();
$detectors = $sth1->fetchAll();
$detector_option_form = "";
foreach($detectors as $det) {
    $sel = ($det['name'] == $_GET['g']) ? 'selected="selected"' : "";
    $detector_option_form .= '<option '.$sel.' value="'.$det['name'].'">'.$det['name'].'</option>';
}
$currentGap = (isset($_GET[g])) ? $_GET[g] : $detectors[0]['name'];

?>

<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script>
    $(function() {
        $( "#tabs" ).tabs();
    });

   $(function(){
      // bind change event to select
      $('#channelSelect').on('change', function () {
          var gap = $(this).val(); // get selected value
          if(gap) { // require a URL
              window.location = "index.php?q=stability_result&id=<?=$id?>&p=<?=$_GET['p']?>&g=" + gap; // redirect
          }
          return false;
      });
    });
</script>

<div class="content">
    
    <h3>Stability test -Run ID <?php printf("%06d", $id); ?></h3>
    
    
    <style>
        
        /* Style the list */
ul.tab {
    list-style-type: none;
    margin: 0;
    padding: 0;
    overflow: hidden;
    border: 1px solid #ccc;
    background-color: #f1f1f1;
}

/* Float the list items side by side */
ul.tab li {float: left;}

/* Style the links inside the list items */
ul.tab li a {
    display: inline-block;
    color: black;
    text-align: center;
    padding: 6px 16px;
    text-decoration: none;
    font-size: 12px;
}

/* Change background color of links on hover */
ul.tab li a:hover {background-color: #ddd;}

/* Create an active/current tablink class */
ul.tab li a:focus, .active {background-color: #ccc;}

/* Style the tab content */
.tabcontent {
    display: none;
    padding: 6px 12px;
    border: 1px solid #ccc;
    border-top: none;
}
        
    </style>
    
    
    <div>
       
        <ul class="tab">
            <li><a href="index.php?q=stability_result&id=<?=$id?>">Stability configuration</a></li>
            <li><a href="index.php?q=stability_result&id=<?=$id?>&p=log">Log file</a></li>
            <li><a href="index.php?q=stability_result&id=<?=$id?>&p=current">Current monitoring</a></li>
            <li><a href="index.php?q=stability_result&id=<?=$id?>&p=environmental">Environmental monitoring</a></li>
            <li><a href="index.php?q=stability_result&id=<?=$id?>&p=qint">Integrated Charge</a></li>
            <li style="float: right"><form style="margin-top: 3px; margin-right: 3px;"><select name="channelSelect" id="channelSelect"><?php echo $detector_option_form; ?><select></form></li>
        </ul>
        
    </div>
    <br />
    
    <?php 

    switch($_GET['p']) {
        
        default:        require_once('includes/stability/config.php'); break;
        case 'log':     require_once('includes/stability/log.php'); break;   
        case 'current': require_once('includes/stability/current.php'); break; 
        case 'qint':    require_once('includes/stability/qint.php'); break;
        case 'environmental':    require_once('includes/stability/environmental.php'); break;
    }
    
    ?>
    
    
    </div>
    
</div>