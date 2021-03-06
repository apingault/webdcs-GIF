<?php
require_once 'functions.php';

loadCSS("datetimepicker.css");
loadJS("datetimepicker.js");


if(isset($_POST['submit'])) {
    
    $t1 = strtotime($_POST['time1']);
    $t2 = strtotime($_POST['time2']);
    $id_name1 = $_POST['param1'];
    $id_name2 = $_POST['param2'];
}
else {

    $t1 = time() - 24*3600;
    $t2 = time();
    $id_name1 = "P";
    $id_name2 = "TIN";
}

$optionsLeft = "";
$optionsRight = "";
foreach($DIP_SUBSCRIPTIONS as $cat) {
    
    $sth1 = $DB['DIP']->prepare("SELECT * FROM subscriptions WHERE table_name = '".$cat."'");
    $sth1->execute();
    $pars = $sth1->fetchAll();
    
    $optionsLeft .= '<option disabled="disabled">'.$pars[0]['category'].'</option>';
    $optionsRight .= '<option disabled="disabled">'.$pars[0]['category'].'</option>';
	
    foreach($pars as $param) {
        
        if($param["id_name"] == $id_name1) $optionsLeft .= '<option selected="selected" value="'.$param["id_name"].'">'.$param["name"].'</option>';
        else $optionsLeft .= '<option value="'.$param["id_name"].'">'.$param["name"].'</option>';
        
        if($param["id_name"] == $id_name2) $optionsRight .= '<option selected="selected" value="'.$param["id_name"].'">'.$param["name"].'</option>';
        else $optionsRight .= '<option value="'.$param["id_name"].'">'.$param["name"].'</option>';
    }
}



$datapoints1 = getDataPointsFromDB($id_name1, $t1, $t2);
$datapoints2 = getDataPointsFromDB($id_name2, $t1, $t2);

getDIPParamInfo($id_name1, $paramName1, $paramUnit1, $DBtable1);
getDIPParamInfo($id_name2, $paramName2, $paramUnit2, $DBtable2);

?>


<script src="http://canvasjs.com/assets/script/canvasjs.min.js"></script>

<h3>Plot monitoring history</h3>
    
<form action="" method="post">
        
    <table>
        <tr>
            <td style="width: 120px;">Start time:</td>
            <td><input id="time1" name="time1" type="text" value="<?php echo date("Y/m/d H:i", $t1); ?>" /></td>
        </tr>
        
	<tr>
            <td>End time:</td>
            <td><input id="time2" name="time2" type="text" value="<?php echo date("Y/m/d H:i", $t2); ?>" /></td>
        </tr>
		
        <tr>
            <td>Parameter left:</td>
            <td><select name="param1"><?php echo $optionsLeft; ?></select> (red)</td>
        </tr>
		
        <tr>
            <td>Parameter right:</td>
            <td><select name="param2"><?php echo $optionsRight; ?></select> (blue)</td>
	</tr>
            
        <tr>
            <td style="height: 30px;"></td>
            <td><input type="submit" name="submit" value="Generate plot"></td>
	</tr>
            
    </table>

</form>

<script type="text/javascript">// <![CDATA[
jQuery(function(){jQuery('#time1').datetimepicker();});
jQuery(function(){jQuery('#time2').datetimepicker();});
// ]]></script>
    
<?php

plotGraph_Time("", $paramName1, $paramUnit1, $datapoints1, $paramName2, $paramUnit2, $datapoints2);
    
