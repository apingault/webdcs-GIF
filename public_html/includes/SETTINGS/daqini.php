<?php

$id = $_GET['id'];

if(isset($_POST['save'])) {
	
	$name = strtolower(filter_input(INPUT_POST, 'name'));
	$content = filter_input(INPUT_POST, 'content');
	$id = filter_input(INPUT_POST, 'config');
    $daqtype = filter_input(INPUT_POST, 'daqtype'); 
	
	if($id == "new") {
		
        $sth2 = $dbh->prepare("INSERT INTO daqini (id, name, daqtype, content) VALUES ('', :name, :daqtype, :content) ");
        $sth2->bindParam(':name', $name);
        $sth2->bindParam(':content', $content);
        $sth2->bindParam(':daqtype', $daqtype);
		$sth2->execute();
				
		msg("Configuration ".$name." added", "pass");
		$id = settings("daqini");
	}
	else {
		
		
        $sth2 = $dbh->prepare("UPDATE daqini SET name = :name, content = :content, daqtype = :daqtype WHERE id = ".$id);
        $sth2->bindParam(':name', $name);
        $sth2->bindParam(':content', $content);
        $sth2->bindParam(':daqtype', $daqtype);
		$sth2->execute();
		
		msg("Configuration ".$name." updated", "pass");
	}
}



if($id == "new") {
	
	$content = "";
	$name = "";
    $daqtype = "default";
}
else {
	
	if(!is_numeric($id)) $id = settings("daqini");

	
    $q = $dbh->prepare("SELECT * FROM daqini WHERE id = ".$id);
	$q->execute();
	$f = $q->fetch();
	$content = $f['content'];
	$name = $f['name'];
    $daqtype = $f['daqtype'];
}



$q = $dbh->prepare("SELECT * FROM daqini ORDER BY id DESC");
$q->execute();
$f = $q->fetchAll();

?>

<script>
$(function(){
	$('#changeConfig').on('change', function () {
		var url = "index.php?q=dcssettings&p=daqini&id=" + $(this).val();
		if(url) window.location = url;
		return false;
	});
});
</script>

<h3>DAQ Ini configuration</h3>


<form action="" method="POST">
	
	Select DAQ INI configuration: 
	<select id="changeConfig" name="config">
		<option disabled="disabled">Select configuration</option>
		<option <?php echo ($id == "new") ? 'selected="selected"' : ''; ?> value="new">New configuration</option>
		<?php
		foreach($f as $x) {
			if($id == $x['id']) $sel = 'selected="selected"';
			else $sel = "";
			echo '<option '.$sel.' value="'.$x['id'].'">'.$x['name'].'</option>';
		}
		?>
	</select>
	
	<br /><br />
	
	Name: <input type="text" name="name" value="<?=$name?>" />
    <br /><br />
    DAQ Type: <select name="daqtype">
    <?php
    foreach($daq_types as $t => $val) {
        if($t == $daqtype) $sel = 'selected="selected"';
        else $sel = "";
        echo '<option '.$sel.' value="'.$t.'">'.$val.'</option>';
    }
    ?>
    </select>
	
	<br /><br />
	
	<textarea name="content" style="width: 100%; height: 600px;"><?php echo $content; ?></textarea>
	
	<br /><br />
	
	<?php
	if(getCurrentRole() != 0) echo '<input type="submit" name="save" value="Save" />';
	?>
	
	
	
	
</form>
