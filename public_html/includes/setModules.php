<?php
/*
 * setModules.php
 */

$sth = $dbh->prepare("SELECT * FROM modules");
$sth->execute();
$modules = $sth->fetchAll();
?>

<div class="content">


    <?php 
    if(!empty($error)) echo '<div class="error">Error: '.$error.'</div>'; 
    elseif(isset($_POST['submit'])) echo '<div class="pass">Configuration successfully saved.</div>';  
    ?>
  
    <div style="display: inline">
        <h3 style="display: inline;">Manage CAEN High Voltage supply mainframes </h3>
        &nbsp;&nbsp;&mdash;&nbsp;&nbsp; <a href="index.php?q=editmodule">Add mainframe</a>
    </div>
    <br /><br /><br />

    <table  class="table">
      
        <thead>
              <tr class="oddrow" style="width: 800px;">
                  <td width="70px"></td>
                  <td width="240px">Power supply name</td>
                  <td width="150px">Address</td>
                  <td width="240px">Comments</td>
                  <td width="100px">Detectors no.</td>
              </tr>
        </thead>
        <tdbody>
        <?php
        $i=0;
        foreach($modules as $value) {

          $class = (($i)%2 == 0) ? 'evenrow' : 'oddrow';
          echo '<tr class="'.$class.'">';
          echo '<td style="padding-left: 10px;"><a href="index.php?q=editmodule&mid='.$value['id'].'"><img src="/config/img/edit.png" /></a> <a onclick="return confirm(\'Are you sure?\')" href="index.php?q=deletemodule&mid='.$value['id'].'"><img src="/config/img/delete.png" /></a></td>';
          echo '<td>'.$value['name'].'</td>';
          echo '<td>'.$value['address'].'</td>';
          echo '<td>'.nl2br(html_entity_decode($value['comments'], ENT_QUOTES)).'</td>';
          echo '<td>'.no_detectors($value['id']).'</td>';
          echo '</tr>';
          $i++;
        }
        ?>
        </tbody>
    </table>
</div>