<?php 

require_once '../config.php';

// $name: table-heading, $options: var or array, allowed options
function parseAlbum($name, $options) {
	
	echo '<br />';
	echo '<tr style="font-size: 14px" valign="top" height="15px">';
		echo '<td colspan="5"><b>'.$name.'</b></td>';
	echo '</tr>';
	
	echo '<tr height="30px" >';
	echo '<td width="60px"><b>Action</b></td>';
	echo '<td width="30px"><b>Id</b></td>';
	echo '<td width="150px"><b>Name</b></td>';
	echo '<td width=300px"><b>Description</b></td>';
	echo '<td width="50px"><b>Images</b></td>';
	echo '</tr>';
	
	if(is_array($options)) {
		
		$sql = '';
		
		for($i = 0; $i < count($options); $i++) {
		
			$sql .=  ' `opt` = '.$options[$i];
			$sql .= ($i == (count($options)-1)) ? '' : ' OR ';
		}	
	}
	else $sql = ' `opt` = '. $options;
	
	$query = mysql_query(" SELECT * FROM `pic_album` WHERE ".$sql." ORDER BY `id` DESC ") or die("Mysql error");
	
	if(mysql_num_rows($query)) {
		
		while ($res = mysql_fetch_array($query)) {
			
			$q = mysql_query(" SELECT COUNT(photoid) FROM `pic_photos` WHERE `albumid` = '".$res['id']."' " ) or die("Mysql error");
			$r = mysql_fetch_array($q);
			
		echo '<tr height="20px">';
			echo '<td> 
					<a href="index.php?q=editalbum&id='.$res['id'].'"><img title="Add photos" width="12px" height="12px" src="config/images/add.png" /></a> 
					<a href="#"><img title="Edit album" id="'.$res['id'].'" class="openEditDialog" src="config/images/edit.png" /></a>
					<a href="#"><img title="Delete album" class="openDeleteDialog" id="'.$res['id'].'"  width="12px" height="12px" src="config/images/delete.png" /></a>
				  </td>';
			echo '<td>'.$res['id'].'</td>';
			echo '<td id="albumName_'.$res['id'].'">'.$res['name'].'</td>';
			echo '<td id="albumDesc_'.$res['id'].'">'.$res['description'].'</td>';
			echo '<td>'.$r['COUNT(photoid)'].'</td>';
		echo '</tr>';
		
		echo '<span style="display: none; position: relative;" id="albumOpt_'.$res['id'].'">'.$res['opt'].'</span>';
	
		}
	} 
	else {
		
		echo '<tr height="20px">';
			echo '<td colspan="5">Nu albums found.</td>';
		echo '</tr>';
	}
}

echo '<table style="margin-left: 15px" cellpadding="0px" cellspacing="0px" >';
	
	parseAlbum('Default albums', 0);
	parseAlbum('Other albums', 1);
	

echo '</table>';


?>