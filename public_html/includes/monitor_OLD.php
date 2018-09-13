<?php
/*
 * monitor.php
 */


$sth1 = $dbh->prepare("SELECT v.HV1, d.CAEN_slot, d.CAEN_channel, d.name  FROM voltages v, detectors d WHERE v.hvscan_id = 25 AND v.detector_id = d.id");
$sth1->execute();
$detectors = $sth1->fetchAll();

print_r($detectors);


require_once 'scripts/setValues.php';

// Select all detectors
$sth1 = $dbh->prepare("SELECT * FROM detectors WHERE mid = :mid");
$sth1->execute(array(':mid' => $mid));
$detectors = $sth1->fetchAll();

?>

<script type="text/javascript">$(document).ready(function() { updateValues(); });</script> 
<div class="content">
  
    <div style="display: inline">
        <h3 style="display: inline;">Monitor detectors</h3> &nbsp;&nbsp;&mdash;&nbsp;&nbsp; <?php echo changeModule('monitor') ?> &nbsp;&nbsp;<div id="moduleStatus" style="display: inline; color: red;"></div>
    </div>
    <br /><br />
    
    <?php 
    if(!empty($error)) echo '<div class="error">Error: '.$error.'</div>'; 
 
    if(!checkMeteo()) {
        
        $sth = $dbh->prepare("SELECT meteolastpoint FROM modules WHERE id = :mid");
        $sth->execute(array(':mid' => $mid)); 
        $dated = date("Y-m-d H:i",$sth->fetchColumn());
        
        echo '<div class="error">Error: meteo station offline since '.$dated.'</div>'; 
    }
    if(!checkMainFrame()) {
        echo '<div class="error">Error: mainframe offline</div>'; 
    }
    ?>
    
    <form action="" method="post" id="monitor-form">
    <table class="table monitor" cellpadding="5px" cellspacing="0">
        <thead>
        <tr>
            <td class="oddrow" width="280px">Detector</td>
            <td class="oddrow" width="200px">Settings</td>
            <td class="oddrow" width="155px">Set values</td>
            <td class="oddrow" width="165px">Monitored values</td>
        </tr>
        </thead>
        <tbody>
        
        <?php  
        $i=0;
        foreach ($detectors as $value) { 

            ?>
            <tr style="display: none;">
                <td>
                    <input type="hidden" name="channel_<?php echo $value['id'] ?>" value="<?php echo $value['channel'] ?>" />
                    <input type="hidden" name="slot_<?php echo $value['id'] ?>" value="<?php echo $value['slot'] ?>" />                  
                </td>
            </tr>
            <tr id="detector_<?php echo $value['id']; ?>">
                <td class="<?php echo ($i%2 == 0) ? 'evenrow' : 'oddrow'; ?>" style="line-height: 25px;">
                    
                    <b><?php echo $value['name']; ?></b><br />
                    Slot: <?php echo $value['slot']; ?> &ndash; Channel: <?php echo $value['channel'] ?><br />
                    <input type="submit" id="poweron_<?php echo $value['id']; ?>" name="poweron[<?php echo $value['id']; ?>]" value="ON" /> <input type="submit" id="poweroff_<?php echo $value['id']; ?>" name="poweroff[<?php echo $value['id']; ?>]" value="OFF" /> <div style="float: right; width: 160px; text-align: left;  margin-right: 5px;" id="status_<?php echo $value['id'] ?>">Status</div>
                </td>
                <td class="<?php echo ($i%2 == 0) ? 'evenrow' : 'oddrow'; ?>" style="vertical-align: middle;">
                    <table class="settings_mon" style="border: 0px;" cellpadding="0px" cellspacing="0px">
                        <tr style="vertical-align: middle;">
                            <td style="border: 0px;" width="75px">HV<sub>eff</sub> set [V]:</td>
                            <td style="border: 0px;"><input <?php echo $disabled ?> style="width: 40px;" type="text" name="hvset_<?php echo $value['id']; ?>" /> <input value="set" type="submit" id="submit_hvset_<?php echo $value['id']; ?>" name="submit_hvset[<?php echo $value['id']; ?>]" /></td>
                        </tr>
                        <tr style="vertical-align: middle;">
                            <td style="border: 0px;">I0 set [uA]:</td>
                            <td style="border: 0px;"><input <?php echo $disabled ?> style="width: 40px;" type="text" name="i0set_<?php echo $value['id']; ?>" /> <input value="set" type="submit" id="submit_i0set_<?php echo $value['id']; ?>" name="submit_i0set[<?php echo $value['id']; ?>]" /></td>
                        </tr>
                        <tr style="vertical-align: middle;">
                            <td style="border: 0px;">Trip set [s]:</td>
                            <td style="border: 0px;"><input <?php echo $disabled ?> style="width: 40px;" type="text" name="tripset_<?php echo $value['id']; ?>" /> <input value="set" type="submit" id="submit_tripset_<?php echo $value['id']; ?>" name="submit_tripset[<?php echo $value['id']; ?>]" /></td>
                        </tr>
                    </table>  
                </td>
                <td style="line-height: 25px;" class="<?php echo ($i%2 == 0) ? 'evenrow' : 'oddrow'; ?>"><div id="setValue_<?php echo $value['id']; ?>">value</div></td>
                <td style="line-height: 25px;" class="<?php echo ($i%2 == 0) ? 'evenrow' : 'oddrow'; ?>"><div id="getValue_<?php echo $value['id']; ?>">value</div></td>
            </tr>
            <?php
            $i++;
        }
      ?>
        </tbody> 
  </table>
  </form>
</div>
