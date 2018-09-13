<?php
if(!defined('INDEX')) die("Access denied");

echo '<div class="content">';
    
if(isset($_GET['p'])) require 'SETTINGS/'.$_GET['p'].'.php';
else require 'SETTINGS/settings.php';

echo '</div>';
echo '<br /><br />';