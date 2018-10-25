<?php

if(isset($_POST["download"]) and $_POST["download"]) {

    // Get real path for our folder
    $rootPath = $dir;
    $filename = '/home/webdcs/software/webdcs/public_html/DOWNLOAD/Scan_'.$idstring.'.zip';

    // Initialize archive object
    $zip = new ZipArchive();
    $zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    // Add csv files
    //$zip->addFile($dir."/Currents.csv", "/Currents.csv");
    //$zip->addFile($dir."/Rates.csv", "Rates.csv");

    // Create recursive directory iterator
    /** @var SplFileInfo[] $files */
    $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file)
    {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                    // Get real and relative path for current file
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($rootPath) + 1);

                    // Add current file to archive
                    $zip->addFile($filePath, $relativePath);
            }
    }


    // Zip archive will be created only after closing object
    $zip->close();
    #rename($filename, "downloads/".$filename);
    echo '<script>window.open("/DOWNLOAD/Scan_'.$idstring.'.zip", "_blank");</script>';

}

if(isset($_POST["save"]) and $_POST["save"]) {

	$scantype_spec = filter_input(INPUT_POST, 'scantype_spec');
	$scan_label = filter_input(INPUT_POST, 'scan_label');
    $comments = filter_input(INPUT_POST, 'comments');
	echo $scan_label;

    $sth1 = $DB['MAIN']->prepare("UPDATE hvscan SET comments = :comments, label = :label WHERE id = ".$id);
    $sth1->bindParam(':comments', htmlspecialchars($comments), PDO::PARAM_STR);
	$sth1->bindParam(':label', $scan_label, PDO::PARAM_STR);
    $sth1->execute();

	$sth1 = $DB['MAIN']->prepare("UPDATE hvscan_".$TITLE." SET type = '".$scantype_spec."' WHERE id = ".$id);
    $sth1->execute();

    header("Refresh:0");
}

if(isset($_POST["delete"]) and $_POST["delete"]) {

    // Delete directories
    deleteDir($dir);

    // Remove from DB
    $sth1 = $DB['MAIN']->prepare("DELETE FROM hvscan WHERE id = $id");
    $sth1->execute();
    $sth1 = $DB['MAIN']->prepare("DELETE FROM hvscan_VOLTAGES WHERE scanid = $id");
    $sth1->execute();
    $sth1 = $DB['MAIN']->prepare("DELETE FROM hvscan_DAQ WHERE id = $id");
    $sth1->execute();
    $sth1 = $DB['MAIN']->prepare("DELETE FROM hvscan_CURRENT WHERE id = $id");
    $sth1->execute();

    header("Location: index.php?q=hvscan&p=runregistry&type=".$TYPESCAN);
}

if(isset($_POST['approve'])) {

    $sth1 = $DB['MAIN']->prepare("UPDATE hvscan SET status = 3 WHERE id = ".$id);
    $sth1->execute();
    header("Refresh:0");
}

// Run entire DQM chain
if(isset($_POST['runDQM'])) {

    exec("pgrep -a python | grep 'DQM.py'", $pids);
    if(count($pids) > 0) {

        msg("DQM is running already in the background. Try again later.", "warning");
    }
    else {
        $curDir = sprintf("/var/operation/HVSCAN/%06d", $id);
        // echo shell_exec("nice -19 python /home/webdcs/software/webdcs/CAEN/python/DQM.py --id ". $id." > /dev/null 2>/dev/null &");
        echo shell_exec("nice -19 python /home/webdcs/software/webdcs/CAEN/python/DQM.py --id ". $id." > ". $dir. "/logDQM.txt 2>&1 &");
        msg("DQM started in the background.");
    }
}

?>

<style>
table td { valign: top; }
td.leftBorder { border-left: 1px solid #ccc; padding-left: 15px; }
</style>

<script type="text/javascript">
$(document).ready(function() {

	var buttonId;

	$("input").click(function () {
		buttonId = $(this).attr("id");
	});

    $("#hvscanForm").submit(function(e) {

		if (buttonId  == "formDownload") {

			var d = confirm('Do you really want to download the scan files?');
			if(d) {

				$("body").addClass("loading");
				return true
			}
			else return false;
		}

		if (buttonId  == "formApprove") {

			return confirm('Do you really want to approve the scan?');
		}

		if (buttonId  == "formDelete") {

			return confirm('Do you really want to delete the scan?');
		}

		if (buttonId  == "formRunDQM") {

			var d = confirm('Do you really want to run the DQM?');
			if(d) {

				$("body").addClass("loading");
				return true
			}
			else return false;
		}

    });
});
</script>

<form action="" method="POST" id="hvscanForm">

    <table cellspacing="0" cellpadding="0px" style="margin-top: 5px;">

            <tr style="height: 25px;">

        <td width="150px">Source configuration:</td>
        <td width="150px"></td>

        <td width="120px" class="leftBorder">Status:</td>
        <td width="220px"><?php echo getFormattedStatus($hvscan['status']); ?></td>

                    <td width="150px" class="leftBorder">Scan start time:</td>
        <td width="150px"><?php echo date('Y-m-d H:i:s', $hvscan['time_start']) ?></td>

    </tr>

        <tr style="height: 25px;">

            <td>Attenuator upstream:</td>
            <td></td>

			<td class="leftBorder">Scan type:</td>
            <td>
            <?php

            if(getCurrentRole() != 0) {

                if($hvscan['type'] == 'current') $types_scan = $hvscan_current_types;
                elseif($hvscan['type'] == 'daq') $types_scan = $hvscan_daq_types;

                echo '<select name="scantype_spec">';
                foreach($types_scan as $key => $type) {
                    $sel = ($type == $scantype_spec) ? 'selected="selected"' : "";
                    echo '<option '.$sel.' value="'.$key.'">'.$type.'</option>';
                }
                echo '</select>';

            }
            else echo $scantype_spec;
            ?>
            </td>

			<td class="leftBorder">Scan end time:</td>
            <td><?php echo ($hvscan['time_end'] == NULL) ? '-' : date('Y-m-d H:i:s', $hvscan['time_end']); ?></td>


        </tr>

        <tr style="height: 25px;">

			<td>Attenuator downstream:</td>
            <td></td>

			<td class="leftBorder">Scan label:</td>
            <td>
            <?php
            if(getCurrentRole() != 0) {

                echo '<select name="scan_label">';
                foreach($scan_labels as $key => $type) {
                    $sel = ($key == $hvscan['label']) ? 'selected="selected"' : "";
                    echo '<option '.$sel.' value="'.$key.'">'.$type.'</option>';
                }
                echo '</select>';
            }
            else {
                echo $scan_label;
            }
            ?>
            </td>

			<td class="leftBorder">Waiting time (min):</td>
            <td><?php echo $hvscan['waiting_time']; ?></td>

        </tr>

        <tr style="height: 25px;">

            <td>Beam configuration:</td>
            <td></td>

			<td class="leftBorder">HV points:</td>
            <td><?php echo $hvscan['maxHVPoints']; ?></td>

			<td class="leftBorder">Measuring time (min):</td>
            <td><?php echo $hvscan['measure_time'] ?></td>


		</tr>



        <tr style="height: 25px;">

			<td>Trigger modes:</td>
            <td>
                <?php
				if($hvscan['type'] == "current") echo 'n/a';
				else {
					foreach($trigger_modes as $key => $value) {
						if(strpos($hvscan_spec['trigger_mode'], $key) != false) echo $value." ";
					}
				}
                ?>
            </td>

            <td class="leftBorder">Scanned trolleys:</td>
            <td>

            </td>

            <td class="leftBorder">Measure interval </td>
            <td>every <?php echo $hvscan['measure_intval']; ?> seconds</td>

        </tr>

		<tr style="height: 25px;">

			<td colspan="2">Comments:</td>
            <td>

			<td colspan="4"></td>
            <td>

		</tr>

		<tr style="height: 25px;">

			<td colspan="2"><textarea name="comments" style="font-size: 12px; height: 74px; width: 280px;"><?php echo $hvscan['comments']; ?></textarea></td>

			<td style="vertical-align: bottom;" colspan="4">


				<input <?php echo (getCurrentRole() == 0) ? 'disabled="disabled"' : ''; ?> type="submit" name="save" id="formSave" value="Save changes" />

				<input <?php echo ($hvscan["status"] == 1) ? 'disabled="disabled"' : ''; ?> type="submit" name="download" id="formDownload" value="Download files" />

				<input <?php echo ($hvscan["status"] != 0 || getCurrentRole() == 0) ? 'disabled="disabled"' : ''; ?> type="submit" name="approve" id="formApprove" value="Approve scan" />

				<input <?php echo (getCurrentRole() == 0) ? 'disabled="disabled"' : ''; ?> type="submit" name="delete" id="formDelete" value="Delete scan" />

				<input <?php echo (getCurrentRole() == 0) ? '' : ''; ?> type="submit" name="runDQM" id="formRunDQM" value="Run DQM" />

				<?php
				if($_SESSION['userid'] == 6) {

					echo '<input type="submit" name="longevityScript" value="Longevity" />';
				}
				?>
			</td>
		</tr>

	</table>



    </form>

