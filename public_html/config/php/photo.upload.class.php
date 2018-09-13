<?php 
error_reporting(0);
/*
 * upload.class.php
 * (c) Jan Eysermans 2010
 * Project: photoalbum
 */

class UploadPhoto {
	
	private $albumdir; 		// Directory of images
	private $photoname; 	// Name of the foto
	private $albumid;	 	// AlbumID 
	private $ext;			// Extension of photo
	private $_file;			// Full path and filename
	private $conn;			// Db connection
	private $sort;
	
	public function __construct($albumid, $path, $file, $conn) {
	
		$this->albumid = $albumid;
		$this->photoname = basename($file);
		$this->albumdir = $path.'/'.$albumid.'/';
		$this->_file = $file;
		$this->conn = $conn;
		
		if(file_exists($this->albumdir.$this->photoname)) {
		 	
			$this->photoname .= rand(10, 99);	
		}
		
		// Execute check functions
		$this->checkMime();
		$this->generateSort();
		$this->makeImage(1); // real
		$this->makeImage(2); // thumb
		$this->SqlQuery();	
	}
	
	private function generateSort() {
	
		$query = $this->conn->query("SELECT `sort` FROM `pic_photos` WHERE `albumid` = '".$this->albumid."' ORDER BY SORT DESC LIMIT 1");	
		if(mysql_num_rows($query)) {
			$res = mysql_fetch_array($query);
			$this->sort = $res['sort'] + 1;
		} 
		else $this->sort = 0;
	}
	
	private function checkMime() {
		
		// Check mime type (only for images...)
		$f = getimagesize($this->_file);
			
		switch($f['mime']) {
				
			default: 
			
				throw new Exception("File mime type error.");
				break;
				
			case 'image/png' :		$this->ext = 'png';		break;
			case 'image/jpeg' :		$this->ext = 'jpg';		break;
			case 'image/pjpeg' :	$this->ext = 'jpg';		break;
			case 'image/gif' :		$this->ext = 'gif';		break;
		}
	
		
	}

	/****************
	 * RESIZE UPLOADED IMAGE:
	 * 
	 * thumbnail
	 * real image
	*/
	private function makeImage($type) {
		
		// Type: thumb (2) or real (1)
		// Set prefix for filename
		// s_1.jpg = thumb (2)
		// 1.jpg = real (1)
		if($type == 2) $photoname = 's_'.$this->photoname;
		else $photoname = $this->photoname;
		
		// Set dimensions
		if($type == 1) {
			
			// ************
			// MAKE REAL IMAGE
			// ************
			
			// Only maximum 1000px width, no maximum of height
			$max_width = 1000;
			$max_height = 1000;
	
			// Get original size
			list($width_orig, $height_orig) = getimagesize($this->_file);
		
			// als img plat is (breedte groter dan hoogte)
			if($width_orig > $height_orig) {
				
				if($width_orig > $max_width) {
					
					$ratio = $max_width / $width_orig;
					$new_height = $ratio * $height_orig;
					$new_width = $max_width;
				}
				else {
					$new_width = $width_orig;
					$new_height = $height_orig;
				}
			}
			
			// als img recht is (hoogte groter dan breedte)
			else {
				
				if($height_orig > $max_height) {
					
					$ratio = $max_height / $height_orig;
					$new_width = $ratio * $width_orig;
					$new_height = $max_height;
				}
				else {
					$new_width = $width_orig;
					$new_height = $height_orig;
				}
			}
		}
		else {
			
			// ************
			// MAKE THUMB
			// ************
			
			// Maximum 100px width, max 100px height 
			$max_width = 100;
			$max_height = 100;
			
			// Get original size
			list($width_orig, $height_orig) = getimagesize($this->_file);
		
			// als img plat is (breedte groter dan hoogte)
			if($width_orig > $height_orig) {
				
				if($width_orig > $max_width) {
					
					$ratio = $max_width / $width_orig;
					$new_height = $ratio * $height_orig;
					$new_width = $max_width;
				}
				else {
					$new_width = $width_orig;
					$new_height = $height_orig;
				}
			}
			
			// als img recht is (hoogte groter dan breedte)
			else {
				
				if($height_orig > $max_height) {
					
					$ratio = $max_height / $height_orig;
					$new_width = $ratio * $width_orig;
					$new_height = $max_height;
				}
				else {
					$new_width = $width_orig;
					$new_height = $height_orig;
				}
			}
		}
		
		// Make image
		$image_p = imagecreatetruecolor($new_width, $new_height);
		
		// Save image, with good extension
		switch ($this->ext) {

			case 'jpg' :
				
				$image = imagecreatefromjpeg($this->_file);
				if(!imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig)) throw new Exception("Couldn't make new image");	
				if(!imagejpeg($image_p, $this->albumdir.$photoname)) throw new Exception("Couldn't save jpeg file");
			break;
			
			case 'png' :
				
				$image = imagecreatefrompng($this->_file);
				if(!imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig)) throw new Exception("Couldn't make new image");	
				if(!imagepng($image_p, $this->albumdir.$photoname)) throw new Exception("Couldn't save jpeg file");
			break;
			
			case 'gif' :
				
				$image = imagecreatefromgif($this->_file);
				if(!imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig)) throw new Exception("Couldn't make new image");	
				if(!imagegif($image_p, $this->albumdir.$photoname)) throw new Exception("Couldn't save jpeg file");
			break;	
		}
		
		
		chmod($this->albumdir.$photoname, 0744);
		chmod($this->albumdir.$photoname, 0744);
		
		// Destroy image
		imagedestroy($image_p);
	}
	
	
	
	
	/****************
	 * MYSQL QUERY:
	 * 
	 * Add the photo in the database
	 * 
	*/
	private function SqlQuery() {
		
		// Insert photo in database
		$this->conn->query(" INSERT INTO `pic_photos` (id, photoid, albumid, active, name, sort) VALUES (
				
				'',
				'',
				'".$this->albumid."',
				'1',
				'".$this->photoname."',
				'".$this->sort."'
				 )"); 
		
		unlink($this->_file);
	}
}

?>