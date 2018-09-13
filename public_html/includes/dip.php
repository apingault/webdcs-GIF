<?php

echo '<div class="content">';
    
if(isset($_GET['p'])) require 'DIP/'.$_GET['p'].'.php';
else require 'DIP/monitoring.php';

echo '</div>';
echo '<br /><br />';

