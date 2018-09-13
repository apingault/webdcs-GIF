<?php

if(isset($_POST['loaddb'])) {
	
	foreach($chambers as $chamber) {
		fillDB($id, $chamber['chamber']);
	}
}


if(isset($_POST['plots'])) {
	
	putenv('ROOTSYS=/usr/local/root/');
	putenv('PATH=/usr/local/root/bin:'.getenv("PATH"));
	putenv('PATH=~/bin:./bin:.:'.getenv("PATH"));
	putenv('LD_LIBRARY_PATH='.getenv("ROOTSYS").'/lib:'.getenv("LD_LIBRARY_PATH"));
	putenv('DYLD_LIBRARY_PATH='.getenv("ROOTSYS").'/lib:$DYLD_LIBRARY_PATH');
	putenv('PYTHONPATH='.getenv("ROOTSYS").'/lib/:'.getenv("PYTHONPATH"));

	# NOTICE: chmod 775 python scripts!
	
	
	# Arguments: ./plotQintCurrents.py RE2-2-NPD-BARC-9 CURR RAW ALL
	$command = escapeshellcmd('/home/webdcs/software/webdcs/scripts/longevity_analysis/plotQintCurrents.py ' . $currentChamberName . ' CURR RAW '.$id);
	//echo $command;
	$output = shell_exec($command);

	$command = escapeshellcmd('/home/webdcs/software/webdcs/scripts/longevity_analysis/plotQintCurrents.py ' . $currentChamberName . ' CURR CORR '.$id);
	$output = shell_exec($command);

	
	//echo '<pre>'.$output.'</pre>';
}


?>

<?php
if($_SESSION['userid'] == 6) {
?>

<form action="" method="POST">
	<input type="submit" name="loaddb" value="Load to DB" />
    <input type="submit" name="plots" value="Make/update plots" />
</form>
<br /><br />
<?php
}
?>

<script>
$(document).ready(function() {
$('.qint-images').magnificPopup({
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

<div class="qint-images">
	
	<a href="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TOT_CURR_RAW.png"><img width="245px" src="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TOT_CURR_RAW.png" /></a>
	<a href="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-BOT_CURR_RAW.png"><img width="245px" src="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-BOT_CURR_RAW.png" /></a>
	<a href="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TN_CURR_RAW.png"><img width="245px" src="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TN_CURR_RAW.png" /></a>
	<a href="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TW_CURR_RAW.png"><img width="245px" src="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TW_CURR_RAW.png" /></a>

	<a href="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TOT_CURR_CORR.png"><img width="245px" src="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TOT_CURR_CORR.png" /></a>
	<a href="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-BOT_CURR_CORR.png"><img width="245px" src="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-BOT_CURR_CORR.png" /></a>
	<a href="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TN_CURR_CORR.png"><img width="245px" src="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TN_CURR_CORR.png" /></a>
	<a href="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TW_CURR_CORR.png"><img width="245px" src="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TW_CURR_CORR.png" /></a>
	
</div>
