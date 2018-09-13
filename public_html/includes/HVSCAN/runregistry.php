<?php
require_once 'functions.php';


// Get scan type
if($_GET['type'] == 'current') {
    $TYPESCAN = 'current';
    $TITLE = 'CURRENT';
    $sth1 = $DB['MAIN']->prepare("SELECT h.*, d.type AS scantype FROM hvscan h, hvscan_CURRENT d WHERE h.type = 'current' AND h.id = d.id  ORDER BY id DESC");
}
else if($_GET['type'] == 'daq') {
    $TYPESCAN = 'daq';
    $TITLE = 'DAQ';
    $sth1 = $DB['MAIN']->prepare("SELECT h.*, d.type AS scantype FROM hvscan h, hvscan_DAQ d WHERE h.type = 'daq' AND h.id = d.id ORDER BY id DESC");
}
else {
    
    if(isset($_GET['label'])) {
        
        $sth1 = $DB['MAIN']->prepare("SELECT h.*, d.type AS scantype FROM hvscan h, hvscan_DAQ d WHERE h.label = '".$_GET['label']."' AND h.id = d.id ORDER BY id DESC");
    }
    else die();
    
}

$sth1->execute();
$hvscans = $sth1->fetchAll();



?>

<script type="text/javascript">
$(function() {

  // call the tablesorter plugin
  $(".RunRegistrySorter").tablesorter({
    theme: 'blue',

    // hidden filter input/selects will resize the columns, so try to minimize the change
    //widthFixed : true,

    // initialize zebra striping and filter widgets
    widgets: ["zebra", "filter"],

    // headers: { 5: { sorter: false, filter: false } },
	headers: { 0: { sorter: false, filter: true }, 2: { sorter: false, filter: false }, 3: { sorter: false, filter: false } },

    widgetOptions : {

      // extra css class applied to the table row containing the filters & the inputs within that row
      filter_cssFilter   : '',

      // If there are child rows in the table (rows with class name from "cssChildRow" option)
      // and this option is true and a match is found anywhere in the child row, then it will make that row
      // visible; default is false
      filter_childRows   : false,

      // if true, filters are collapsed initially, but can be revealed by hovering over the grey bar immediately
      // below the header row. Additionally, tabbing through the document will open the filter row when an input gets focus
      filter_hideFilters : false,

      // Set this option to false to make the searches case sensitive
      filter_ignoreCase  : true,

      // jQuery selector string of an element used to reset the filters
      filter_reset : '.reset',

      // Use the $.tablesorter.storage utility to save the most recent filters
      filter_saveFilters : false,

      // Delay in milliseconds before the filter widget starts searching; This option prevents searching for
      // every character while typing and should make searching large tables faster.
      filter_searchDelay : 300,

      // Set this option to true to use the filter to find text from the start of the column
      // So typing in "a" will find "albert" but not "frank", both have a's; default is false
      filter_startsWith  : false,

      // Add select box to 4th column (zero-based index)
      // each option has an associated function that returns a boolean
      // function variables:
      // e = exact text from cell
      // n = normalized value returned by the column parser
      // f = search filter input value
      // i = column index
      filter_functions : {

        // Add select menu to this column
        // set the column value to true, and/or add "filter-select" class name to header
        // '.first-name' : true,


      }

    }

  });

});	
	
</script>



<h3 style="display: inline;">HVscan <?=$TITLE?> Run Registry </h3>
<br /><br />

<style>
	.ui-icon { display: inline-block; }

	
</style>
   
<table class="RunRegistrySorter" cellpadding="5px" cellspacing="0">
	
    <thead><tr>
        <th class="oddrow" width="70px">Scan ID</th>
        <th class="oddrow filter-select" data-placeholder="Select scan type" width="150px">Scan type</th>
        <th class="oddrow" width="130px">Start time</th>
        <th class="oddrow filter-select" data-placeholder="Select label" width="170px">Label</th>
        <th class="oddrow filter-select" data-placeholder="Select status" width="60px">Status</th>
        <th class="oddrow" data-value="" width="30px">#HV</th>
	</tr></thead>
	<tbody>
        <?php
        
        $i = 0;
        foreach ($hvscans as $hvscan) {


            echo '<tr>';
            // data-href="index.php?q=hvscan&p=hvscan&id='.$hvscan['id'].'"
			//  class="clickable-row"
			printf('<td>%06d<a href="index.php?q=hvscan&p=hvscan&id='.$hvscan['id'].'"><span class="ui-icon ui-icon-extlink"></span></a></td>', $hvscan['id'], $hvscan['id']);
			//printf('<td style="height: 30px;">%06d</td>', $hvscan['id']);
            
			if($hvscan['type'] == 'current') $l = $hvscan_current_types;
                        elseif($hvscan['type'] == 'daq') $l = $hvscan_daq_types; 
                        echo '<td>'.$l[$hvscan['scantype']].'</td>';
            
			echo '<td>'.date('Y-m-d H:i', $hvscan['time_start']).'</td>';
            
			
            

			echo '<td>';
			echo (array_key_exists($hvscan['label'], $scan_labels)) ? $scan_labels[$hvscan['label']] : $hvscan['label'];
			echo '</td>';
			
			echo '<td>'.getFormattedStatus($hvscan['status']).'</td>';

            echo '<td>'.$hvscan['maxHVPoints'].'</td>';
            echo '</tr>';
            $i++;
        }
        ?>
    </tbody>
</table>


