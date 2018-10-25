<?php
require_once require_once url('config/menu.php', 'local');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<title><?php echo settings('title'); ?></title>
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
	<?php 
	loadCSS("screen.css");
	loadCSS("magnific-popup.css");
	//loadJS("jquery-1.10.2.min.js");
	
	loadJS("jquery.tablesorter.min.js");
	loadJS("jquery.tablesorter.widgets.js");
        loadJS("excellentexport.min.js");
	loadCSS("tablesorter.css");
	
	loadJS("ajax.js");
	loadJS("js.js");
	loadJS("multiselect.js");
	loadJS("jquery.magnific-popup.min.js"); 
	?>

	<!--<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>-->
	
</head>

<body>

    <div class="header">    
        
        <div class="headertext">
            WebDCS <?php echo EXPERIMENT_NAME ?>
            <div class="logo"><img alt="" src="config/images/cms.gif" width="75px" /></div>
        </div>
    </div>
	
    <div class="navigation">
        
	<div class="nav">
            <ul>
            <?php
            foreach($menu as $key => $val) {
		
		if(is_array($val)) {
                    echo '<li><a href="#">'.$key.'</a><ul>';
                    foreach($val as $key1 => $val1) {
                	echo '<li><a href="'.$val1.'">'.$key1.'</a></li>';
                    }
                    echo '</ul></li>';
                }
                else echo '<li><a href="'.$val.'">'.$key.'</a></li>';
				
            }
            ?>
        
            </ul>
		
        </div>
    </div>
