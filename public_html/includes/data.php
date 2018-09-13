<?php
/*
 * data.php
 */

$hvscandir=$config['data_dir'].'hvscan/'.$mainframe.'/';

$hvlogs = array_reverse(glob($hvscandir."*.log")); 
$hvcsvs = array_reverse(glob($hvscandir."*.csv")); 

$stabtestdir=$config['data_dir'].'stabilitytest/'.$mainframe.'/';

$stabtestlogs = array_reverse(glob($stabtestdir."*.log")); 
$stabtestcsvs = array_reverse(glob($stabtestdir."*.csv")); 


if(isset($_GET['deletecsvfile']) AND isset($_GET['deletelogfile'])) {
  
    unlink($_GET['deletecsvfile']);
    unlink($_GET['deletelogfile']);
    header("Location: index.php?q=data");
}

?>

<div class="content">
    
    <div style="display: inline">
        <h3 style="display: inline;">HVscan files</h3> &nbsp;&nbsp;&mdash;&nbsp;&nbsp; <?php echo changeModule('data', $mid) ?> 
    </div>
    
    <br /><br />
        
    <table class="files">

      <thead>
        <tr class="oddrow">
          <td width="30px"></td>
          <td width="300px">Data file</td>
          <td width="300px">Log file</td>
        </tr>
      </thead>
      <tbody>
      <?php
      for($i=0; $i<count($hvlogs); $i++) {

        $class = (($i)%2 == 0) ? 'evenrow' : 'oddrow';
        echo '<tr class="'.$class.'">';
        echo '<td><a onclick="return confirm(\'Are you sure?\')" href="index.php?q=data&deletelogfile='.$hvlogs[$i].'&deletecsvfile='.$hvcsvs[$i].'"><img src="/config/img/delete.png" /></a></td>';
        echo '<td><a href="/config/downloadfile.php?dir='.$hvscandir.'&type=csv&file='.$hvcsvs[$i].'">'.basename($hvcsvs[$i]).'</a></td>';
        echo '<td><a target="_blank" href="/config/downloadfile.php?dir='.$hvscandir.'&type=log&file='.$hvlogs[$i].'">'.basename($hvlogs[$i]).'</a></td>';
        echo '</tr>';
      }

      ?>
      </tbody>
    </table>
    
    
    <h3>Stability test files</h3>
    <br />
    <table class="files">

      <thead>
        <tr class="oddrow">
          <td width="30px"></td>
          <td>Data file</td>
          <td>Plot options</td>
        </tr>
      </thead>
      <tbody>
      <?php
      for($i=0; $i<count($stabtestcsvs); $i++) {

        $class = (($i)%2 == 0) ? 'evenrow' : 'oddrow';
        echo '<tr class="'.$class.'">';
        echo '<td><a onclick="return confirm(\'Are you sure?\')" href="index.php?q=data&deletelogfile='.$stabtestlogs[$i].'&deletecsvfile='.$stabtestcsvs[$i].'"><img src="/config/img/delete.png" /></a></td>';
        echo '<td><a href="/config/downloadfile.php?dir='.$stabtestscandir.'&type=csv&file='.$stabtestcsvs[$i].'">'.basename($stabtestcsvs[$i]).'</a></td>';
        echo '<td><a href="index.php?q=plotstability&file='.$stabtestcsvs[$i].'">Plot</a> &nbsp; - Dropbox &nbsp;</td>';
        echo '</tr>';
      }

      ?>
      </tbody>
    </table>    
    
    
</div>