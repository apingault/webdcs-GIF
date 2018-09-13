<?php

require_once url('includes/PMON/functions.php', 'local');
require_once url('includes/DIP/functions.php', 'local');

echo '<div class="content">';
    
if(isset($_GET['p'])) require 'PMON/'.$_GET['p'].'.php';
else require 'PMON/index.php';

echo '</div>';
echo '<br /><br />';

