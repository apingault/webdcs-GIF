
<?php
/*
 * settings.php
 */

$pass = "";
$error = "";

if(isset($_POST['submit']) AND $_POST['submit'] == 'Save configuration') {

    $title = filter_input(INPUT_POST, 'title');
    $freq = filter_input(INPUT_POST, 'stabtest_freq');
    $DAQ_HV_points = filter_input(INPUT_POST, 'DAQ_HV_points');
    $waiting_time = filter_input(INPUT_POST, 'waiting_time');
    $standby_voltage = filter_input(INPUT_POST, 'standby_voltage');
    $RPC_mode = filter_input(INPUT_POST, 'RPC_mode');
    $DAQ_HV_template_ID = filter_input(INPUT_POST, 'DAQ_HV_template_ID');
    //$ACTIVE_TROLLEY_ID = filter_input(INPUT_POST, 'ACTIVE_TROLLEY_ID');

    $CURRENT_HV_points = filter_input(INPUT_POST, 'CURRENT_HV_points');
    $CURRENT_measuring_time = filter_input(INPUT_POST, 'CURRENT_measuring_time');
    $measuring_intval = filter_input(INPUT_POST, 'measuring_intval');
    $CURRENT_HV_template_ID = filter_input(INPUT_POST, 'CURRENT_HV_template_ID');

    $STABILITY_template_ID = filter_input(INPUT_POST, 'STABILITY_template_ID');

    $daqini_default = filter_input(INPUT_POST, 'daqini_default');
    $daqini_digitizer = filter_input(INPUT_POST, 'daqini_digitizer');
    $daqtype = filter_input(INPUT_POST, 'daqtype');
    
    
    if(empty($title)) {
        $error = "enter a title.";
    }
    elseif(!is_numeric($standby_voltage)) {
        $error = "enter a measure frequency.";
    }
    else {
        settings("title", $title);
        settings("stabtest_frequency", $freq);
        settings("waiting_time", $waiting_time);
        settings("DAQ_HV_points", $DAQ_HV_points);
        settings("standby_voltage", $standby_voltage);
        settings("RPC_mode", $RPC_mode);
        settings("DAQ_HV_template_ID", $DAQ_HV_template_ID);
        settings("CURRENT_HV_points", $CURRENT_HV_points);
        settings("CURRENT_measuring_time", $CURRENT_measuring_time);
        settings("measuring_intval", $measuring_intval);
        settings("CURRENT_HV_template_ID", $CURRENT_HV_template_ID);
        settings("STABILITY_template_ID", $STABILITY_template_ID);
        settings("daqini_default", $daqini_default);
        settings("daqini_digitizer", $daqini_digitizer);
        settings("daqtype", $daqtype);


        $pass = 'Configuration successfully saved.';
    //setCrontab();
    }

}


$sel = (settings('RPC_mode') == 'double_gap') ? 'selected="selected"' : '';

// list of DAQ-Ini files

?>

<div class="content">

    <?php
    if(!empty($error)) { echo '<div class="error">Error: '.$error.'</div>'; }
    elseif($pass != '') { echo '<div class="pass">'.$pass.'</div>'; }
    ?>

    <h3>Settings</h3>
 
    <form method="post" action="">
        <table>
            <tr style="height: 25px">
                <td width="150px" colspan="2"><b>General settings</b></td>
            </tr>
            <tr style="height:25px">
                <td width="150px">Title (header):</td>
                <td><input name="title" value="<?php echo settings("title"); ?>" type="text"></td>
            </tr>
            <tr style="height: 25px">
                <td width="150px">Standby voltage:</td>
                <td><input name="standby_voltage" value="<?php echo settings("standby_voltage"); ?>" type="text"> V</td>
            </tr>
            <tr style="height: 25px">
                <td width="150px">RPC mode:</td>
                <td>
                    <select name="RPC_mode">
                        <option value="single_gap">Single gap</option>
                        <option <?php echo $sel; ?> value="double_gap">Double gap</option>
                    </select>
                </td>
            </tr>
            <tr style="height: 25px">
                <td width="150px">Measure interval:</td>
                <td><input name="measuring_intval" value="<?php echo settings("measuring_intval"); ?>" type="text"> (s)</td>
            </tr>
            <tr style="height: 25px">
                <td width="150px">Waiting time:</td>
                <td><input name="waiting_time" value="<?php echo settings("waiting_time"); ?>" type="text"> (min)</td>
            </tr>
            <tr style="height: 25px">
                <td width="150px" colspan="2"><b>DAQ HVscan settings</b></td>
            </tr>
            <tr style="height: 25px">
                <td width="150px">HV points:</td>
                <td><input name="DAQ_HV_points" value="<?php echo settings("DAQ_HV_points"); ?>" type="text"></td>
            </tr>
            
            <tr style="height: 25px">
                <td width="150px">HV Template scan ID:</td>
                <td><input name="DAQ_HV_template_ID" value="<?php echo settings("DAQ_HV_template_ID"); ?>" type="text"></td>
            </tr>
			<tr style="height: 25px">
                <td width="150px">Default DAQ INI file:</td>
                <td>
                <select name="daqini_default">
                <?php
                $q = $DB['MAIN']->prepare("SELECT * FROM daqini WHERE daqtype = 'default' ORDER BY id DESC");
                $q->execute();
                $f = $q->fetchAll();
                foreach($f as $x) {
                    if(settings("daqini_default") == $x['id']) $sel = 'selected="selected"';
                    else $sel = "";
                    echo '<option '.$sel.' value="'.$x['id'].'">'.$x['name'].'</option>';
                }
                ?>
                </select>
                </td>
            </tr>
            <tr style="height: 25px">
                <td width="150px">Digitizer DAQ INI file:</td>
                <td>
                <select name="daqini_digitizer">
                <?php
                $q = $DB['MAIN']->prepare("SELECT * FROM daqini WHERE daqtype = 'digitizer' ORDER BY id DESC");
                $q->execute();
                $f = $q->fetchAll();
                foreach($f as $x) {
                    if(settings("daqini_digitizer") == $x['id']) $sel = 'selected="selected"';
                    else $sel = "";
                    echo '<option '.$sel.' value="'.$x['id'].'">'.$x['name'].'</option>';
                }
                ?>
                </select>
                </td>
            </tr>
            
		<tr style="height: 25px">
                <td width="150px">Default DAQ:</td>
                <td>
					<select name="daqtype">
					<?php

					foreach($daq_types as $key => $val) {
						if(settings("daqtype") == $key) $sel = 'selected="selected"';
						else $sel = "";
						echo '<option '.$sel.' value="'.$key.'">'.$val.'</option>';
					}
					?>
					</select>
				</td>
            </tr>
            
            <tr style="height: 25px">
                <td width="150px" colspan="2"><b>CURRENT HVscan settings</b></td>
            </tr>
            <tr style="height: 25px">
                <td width="150px">HV points:</td>
                <td><input name="CURRENT_HV_points" value="<?php echo settings("CURRENT_HV_points"); ?>" type="text"></td>
            </tr>
            <tr style="height: 25px">
                <td width="150px">HV Template scan ID:</td>
                <td><input name="CURRENT_HV_template_ID" value="<?php echo settings("CURRENT_HV_template_ID"); ?>" type="text"></td>
            </tr>
            <tr style="height: 25px">
                <td width="150px">Measure time:</td>
                <td><input name="CURRENT_measuring_time" value="<?php echo settings("CURRENT_measuring_time"); ?>" type="text"> (min)</td>
            </tr>
            <tr style="height: 25px">
                <td width="150px" colspan="2"><b>Stability test settings</b></td>
            </tr>
            <tr style="height: 25px">
                <td width="150px">Template ID:</td>
                <td><input name="STABILITY_template_ID" value="<?php echo settings("STABILITY_template_ID"); ?>" type="text"></td>
            </tr>
        </table>

        <br />
        <input value="Save configuration" type="submit" name="submit" />
    </form>




    <h3>CAEN error codes</h3>

    <table class="table" cellpadding="5px" cellspacing="0">
        <thead>
            <tr>
                <td class="oddrow" width="40px">bit</td>
                <td class="oddrow" width="100px">decimal code</td>
                <td class="oddrow" width="660px">explanation</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>-</td>
                <td>0</td>
                <td>Channel is OFF</td>
            </tr>
            <tr>
                <td>0</td>
                <td>1</td>
                <td>Channel is ON</td>
            </tr>
            <tr>
                <td>1</td>
                <td>2</td>
                <td>Channel is ramping up</td>
            </tr>
            <tr>
                <td>2</td>
                <td>4</td>
                <td>Channel is ramping down</td>
            </tr>
            <tr>
                <td>3</td>
                <td>8</td>
                <td>Channel is in overcurrent</td>
            </tr>
            <tr>
                <td>4</td>
                <td>16</td>
                <td>Channel is in overvoltage</td>
            </tr>
            <tr>
                <td>5</td>
                <td>32</td>
                <td>Channel is in undervoltage</td>
            </tr>
            <tr>
                <td>6</td>
                <td>64</td>
                <td>Channel is in external trip</td>
            </tr>
            <tr>
                <td>7</td>
                <td>128</td>
                <td>Channel is in max V</td>
            </tr>
            <tr>
                <td>8</td>
                <td>256</td>
                <td>Channel is in external disable</td>
            </tr>
            <tr>
                <td>9</td>
                <td>512</td>
                <td>Channel is in internal trip</td>
            </tr>
            <tr>
                <td>10</td>
                <td>1024</td>
                <td>Channel is in calibration error</td>
            </tr>
            <tr>
                <td>11</td>
                <td>2048</td>
                <td>Channel is unplugged</td>
            </tr>
            <tr>
                <td>12</td>
                <td>4096</td>
                <td>Reserved forced to 0</td>
            </tr>
            <tr>
                <td>13</td>
                <td>8192</td>
                <td>Channel is in OverVoltage Protection</td>
            </tr>
            <tr>
                <td>14</td>
                <td>16384</td>
                <td>Channel is in Power Fail</td>
            </tr>
            <tr>
                <td>15</td>
                <td>32768</td>
                <td>Channel is in Temperature Error</td>
            </tr>
            <tr>
                <td>16-31</td>
                <td>-</td>
                <td>Reserved, forced to 0</td>
            </tr>
        </tbody>
    </table>
    <br /><br />


</div>
