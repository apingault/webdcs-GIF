<?php

// Get DIP values
$sth1 = $dbh->prepare("SELECT * FROM DIP where id = '1'");
$sth1->execute();
$dip = $sth1->fetch();
?>

<div class="content">
    
    <h3>GIF++ Online Monitoring</h3>
    
    &raquo; <a href="index.php?q=plotmonitoring">Plot monitoring history</a><br /><br />

    <script type="text/javascript"> $(document).ready(function() { GIFMonitoring(); }); </script>
    <div id="GIFMonitoring"></div>
    
</div>
