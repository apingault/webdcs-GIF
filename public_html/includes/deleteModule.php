<?php
/*
 * deleteModule.php
 */

// Get module ID
$mid = (isset($_GET['mid']) AND is_numeric($_GET['mid'])) ? $_GET['mid'] : '';

// Get mainframe name from ID
$sth = $dbh->prepare("SELECT name FROM modules WHERE id = :mid");
$sth->execute(array(':mid' => $mid)); 
$mainframe=$sth->fetchColumn();

// Delete module from db
$sth = $dbh->prepare("DELETE FROM modules WHERE id = :mid");
$sth->execute(array(':mid' => $mid));

// Delete detectors from db
$sth = $dbh->prepare("DELETE FROM detectors WHERE mid = :mid");
$sth->execute(array(':mid' => $mid));

// Delete all datafiles and directories
exec("rm -rf /home/user/data/hvscan/".$mainframe);
exec("rm -rf /home/user/data/stabilitytest/".$mainframe);
exec("rm -rf /home/user/data/tmp/".$mid);

// Set new default module ID
if(settings("current_mid") == $mid ) {
    $sth = $dbh->prepare("SELECT id FROM modules LIMIT 1");
    $sth->execute();
    $modules = $sth->fetch();
    settings("current_mid", $modules[0]);
}
  
header("Location: index.php?q=setmodules");
  
