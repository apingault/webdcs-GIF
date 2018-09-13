<?php
if(!defined('INDEX')) die("Access denied");


$runFile = file_get_contents("/var/operation/RUN_STABILITY/run");



if(strpos($runFile,'RUN') !== false) $t = "<font color=\"green\"><b>ONGOING</b></font>";
elseif(strpos($runFile,'STANDBY') !== false) $t = "<font color=\"blue\"><b>STANDBY</b></font>";
elseif(strpos($runFile,'HVSCAN') !== false) $t = "<font color=\"blue\"><b>HVSCAN</b></font>";
elseif(strpos($runFile,'KILL') !== false) $t = "<font color=\"red\"><b>KILLED BY USER</b></font>";
elseif(strpos($runFile,'CRASHED') !== false) $t = "<font color=\"red\"><b>CRASHED</b></font>";
else $t = "NO RUN INFO AVAILABLE";


// Get current or latest scan
$sth1 = $dbh->prepare("SELECT * FROM stability ORDER BY id DESC LIMIT 1");
$sth1->execute();
$res = $sth1->fetch();
$currentId = $res['id'];
$idstring = sprintf("%06d", $currentId);

function setRunStatus($runid, $status) {
    
    global $dbh;
    $sth1 = $dbh->prepare("UPDATE stability SET status = $status WHERE id = $runid");
    $sth1->execute();
}



?>

<script>
function abortScan() {

    var reason = prompt("Reason for stop/pause the scan:", "");
    document.getElementById("abort_comment").value = reason;
}
</script>

<h3 style="display: inline;">Longevity studies (aging)</h3>
<br /><br />
&raquo <a href="index.php?q=longevity&p=startrun">Start run</a><br />
&raquo <a href="index.php?q=longevity&p=rundqm&id=<?=$currentId?>">Go to current/latest run</a><br />
&raquo <a href="index.php?q=longevity&p=summary">Longevity summary</a><br />
    
<br />

    
<h3 style="display: inline;">Current status</h3>
<br /><br />
Current status: <?=$t?><br />
<br />

<?php
echo $runFile;
if(strpos($runFile,'RUN') !== false || strpos($runFile,'STANDBY') !== false) {
    
    echo '<form style="float: left;" id="abortScan_form" method="POST" action="" onsubmit="abortScan()">';
    echo '<input type="hidden" name="aborted_comment" id="abort_comment" value="" />';
    echo '<input type="submit" name="stoprun" value="Stop run" />';
    echo '</form>';
}
if(strpos($runFile,'RUN') !== false) {
    
    echo '<form method="POST" action="" onsubmit="return confirm(\'Do you really want to go to STANDBY/HVSCAN mode?\');">';
    echo '&nbsp;<input type="submit" name="standbyrun" value="Standby mode" />';
    echo '&nbsp;<input type="submit" name="hvscan" value="HVSCAN" />';
    echo '</form>';
    echo '<br />';
    
}
if($runFile == "STANDBY" || $runFile == "HVSCAN") {
    
    echo '<form method="POST" action="">';
    echo '<input type="submit" name="resumerun" value="Resume" />';
    echo '</form>';
    echo '<br />';
}
?>
    
<h3 style="display: inline;">Run registry</h3>
<br /><br />
<?php //require_once('includes/stability/runregistry.php'); ?>
