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
	
	$command = escapeshellcmd('python /home/webdcs/software/webdcs/scripts/longevity_analysis/runPlots.py --chamber=' . $currentChamberName . '  --id='.$id);
	//echo $command;
	$output = shell_exec($command);

	//echo '<pre>'.$output.'</pre>';
}

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
	
	<a href="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TOT_CURR.png?d=<?=$RAND?>"><img width="245px" src="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TOT_CURR.png?d=<?=$RAND?>" /></a>
	<a href="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-BOT_CURR.png?d=<?=$RAND?>"><img width="245px" src="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-BOT_CURR.png?d=<?=$RAND?>" /></a>
	<a href="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TN_CURR.png?d=<?=$RAND?>"><img width="245px" src="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TN_CURR.png?d=<?=$RAND?>" /></a>
	<a href="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TW_CURR.png?d=<?=$RAND?>"><img width="245px" src="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TW_CURR.png?d=<?=$RAND?>" /></a>

	<a href="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TOT_CURR_DISTR.png?d=<?=$RAND?>"><img width="245px" src="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TOT_CURR_DISTR.png?d=<?=$RAND?>" /></a>
	<a href="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-BOT_CURR_DISTR.png?d=<?=$RAND?>"><img width="245px" src="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-BOT_CURR_DISTR.png?d=<?=$RAND?>" /></a>
	<a href="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TN_CURR_DISTR.png?d=<?=$RAND?>"><img width="245px" src="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TN_CURR_DISTR.png?d=<?=$RAND?>" /></a>
	<a href="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TW_CURR_DISTR.png?d=<?=$RAND?>"><img width="245px" src="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TW_CURR_DISTR.png?d=<?=$RAND?>" /></a>

        <a href="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TOT_QINT.png?d=<?=$RAND?>"><img width="245px" src="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TOT_QINT.png?d=<?=$RAND?>" /></a>
	<a href="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-BOT_QINT.png?d=<?=$RAND?>"><img width="245px" src="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-BOT_QINT.png?d=<?=$RAND?>" /></a>
	<a href="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TN_QINT.png?d=<?=$RAND?>"><img width="245px" src="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TN_QINT.png?d=<?=$RAND?>" /></a>
	<a href="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TW_QINT.png?d=<?=$RAND?>"><img width="245px" src="/STABILITY/<?=$idstring?>/plots/<?=$currentChamberName?>-TW_QINT.png?d=<?=$RAND?>" /></a>
	
</div>
