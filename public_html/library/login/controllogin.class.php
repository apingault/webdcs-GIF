<?php

class ControlLogin {
	
        private $ip;
	
	public function __construct() {
		
		$this->ip = $_SERVER['REMOTE_ADDR'];
	}
	
	public function Logout() {
		
		global $DB;
		
		if(isset($_SESSION)) {
			
			$sql = "UPDATE `users` SET `lastaction` = ':lastaction' WHERE `id` = ':id'";
			$sth1 = $DB['MAIN']->prepare($sql);
			$sth1->bindParam(':lastaction', $_SESSION['lastaction'], PDO::PARAM_STR);  
			$sth1->bindParam(':id', $_SESSION['userid'], PDO::PARAM_STR);  
			$sth1->execute();     

			session_unset(); 
			session_destroy();
		}
		
		header("Location: index.php");
	}
	
	
	public function CheckSession() {
		
		global $DB;
		//return true;
		$now = time();
		
		if(!isset($_SESSION['lastaction']) or !isset($_SESSION['userid']) or !isset($_SESSION['ipaddr'])) {
			return false;
		}
		elseif(isset( $_SESSION['ipaddr']) AND $_SESSION['ipaddr'] != $this->ip) {
  			$this->Logout();
		}
		elseif(isset($_SESSION['lastaction']) AND ($now - $_SESSION['lastaction']) > 3600) {
			$this->Logout();
		}
		else {
			$_SESSION['lastaction'] = $now;
			$sql = "UPDATE users SET lastaction = :lastaction WHERE id = :id";
			$sth1 = $DB['MAIN']->prepare($sql);
			$sth1->bindParam(':lastaction', $_SESSION['lastaction'], PDO::PARAM_STR);  
			$sth1->bindParam(':id', $_SESSION['userid'], PDO::PARAM_STR);  
			$sth1->execute(); 
			return true;
		}
	}
}
