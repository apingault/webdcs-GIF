<?php
if(!defined('INDEX')) die("Access denied");

echo '<div class="content">';
    
if(isset($_GET['p'])) require 'HVSCAN/'.$_GET['p'].'.php';
else require 'HVSCAN/runregistry.php';

echo '</div>';
echo '<br /><br />';
