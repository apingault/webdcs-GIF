<?php




// Select all detectors
$sth1 = $dbh->prepare("SELECT * FROM detectors WHERE mid = :mid");
$sth1->execute(array(':mid' => $mid));
$detectors = $sth1->fetchAll();


?>

<div class="content">
    
    <div style="display: inline">
      <h3 style="display: inline;">Edit detector DETECTOR_NAME</h3>
    </div>
    
    <br /><br />
    
    <form method="post" action="" id="hvscan-form">
    <table>
        <tr>
            <td style="height: 30px;">Detector name:</td>
            <td><input name="name" type="text" /></td>
        </tr>
        <tr>
            <td>CAEN module:</td>
            <td><input name="CAEN_module" type="text"/></td> 
        </tr>
        <tr>
            <td>CAEN slot:</td>
            <td><input name="CAEN_slot" type="text"/></td> 
        </tr>
        <tr>
            <td>CAEN channel:</td>
            <td><input name="CAEN_channel" type="text"/></td> 
        </tr>
        <tr>
            <td width="130px" style="height: 30px;">Type scan:</td>
            <td>
                <select name="scantype">
                    <option value="efficiency">Efficiency scan (beam ON)</option>
                    <option value="rate">Rate Scan (beam OFF)</option>
                    <option value="noise_reference">Noise Reference</option>
                    <option value="test">Test scan</option>
                </select>
            </td>
        </tr>
        <tr>
            <td style="height: 30px;">Source configuration:</td>
            <td>
                <select name="source">
                    <option value="0">Source OFF</option>
                    <option value="1">Source ON</option>
                </select>
                &nbsp;&nbsp;&nbsp;U <input size="3" name="attU" type="text" value="111" maxlength="3" /> &nbsp;&nbsp;&nbsp;D <input size="3" name="attD" type="text" value="111" maxlength="3" />
            </td>
        </tr>
        <tr>
            <td style="height: 30px;">Beam configuration:</td>
            <td>
                <select name="beam">
                    <option value="0">Beam OFF</option>
                    <option value="1">Beam ON</option>
                </select>
            </td>
        </tr>
        <tr>
            <td style="height: 30px;">Detector name:</td>
            <td><input size="10" name="maxtriggers" type="text" value="500000" /></td>
        </tr>
        <tr>
            <td>Wait time* [min]:</td>
            <td><input size="10"  name="waiting_time" type="text" value="5"/></td> 
        </tr>
    </table>
    <br /> 
    <table>
        <tr>
            <td width="130px" style="height: 20px;"></td>
            <td width="100px">T1_S1</td>
            <td width="100px">T1_S2</td>
            <td width="100px">T1_S3</td>
            <td width="100px">T1_S4</td>
        </tr>
        <tr>
            <td>High Voltage 1 (eff):</td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="9300"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="9200"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="9000"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="9000"/></td>
        </tr>
        <tr>
            <td>High Voltage 2 (eff):</td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="9500"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="9400"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="9200"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="9200"/></td>
        </tr>
        <tr>
            <td>High Voltage 3 (eff):</td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="9600"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="9500"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="9300"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="9300"/></td>
        </tr>
        <tr>
            <td>High Voltage 4 (eff):</td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="9700"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="6600"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="6400"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="6400"/></td>
        </tr>
        <tr>
            <td>High Voltage 5 (eff):</td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="9800"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="9700"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="9500"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="9500"/></td>
        </tr>
        <tr>
            <td>High Voltage 6 (eff):</td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="10100"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="10000"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="9800"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="9800"/></td>
        </tr>
        <tr style="height: 40px">
            <td>Thresholds:</td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="200"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="200"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="200"/></td>
            <td><input disabled="disabled" size="6"  name="wtime" type="text" value="200"/></td>
        </tr>
    </table>     
    <br />

    <input <?php if($run) echo 'disabled="disabled"'; ?> type="submit" name="startscan" value="Start DAQ HV scan" /> &nbsp;&nbsp; *<font style="font-size: 10px;">This is the measure time after ramping up is completed.</font>
  
    </form>  
    
</div>