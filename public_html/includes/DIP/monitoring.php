<?php
require_once 'functions.php';
?>

<h3 style="display: inline;">DIP manager</h3>
    
<br /><br />
&raquo; <a href="index.php?q=dip&p=plothistory">Plot monitoring history</a><br />
&raquo; <a href="index.php?q=dip&p=subscriptions">DIP Subscriptions</a><br />


<script type="text/javascript">
function DIPMonitor() {

    $.ajax({
        type: 'POST',
        url: 'index.php?q=ajax&p=dipmonitoring',
        cache: false,
        success: function(result) {
            
            $('#DIPMonitor').html(result);
        }
    });
    setTimeout(function(){DIPMonitor();},60000);
}
    
$(document).ready(function() {
    
    DIPMonitor(); 
});
</script>

<div id="DIPMonitor"></div>