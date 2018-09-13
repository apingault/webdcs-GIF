<?php

class Login {
	
	private $username;
	private $password;
	private $userid;
	private $ip;
	private $response;
	private $challenge;
	private $conn;
	
	public function __construct($conn, $username = '', $response = '') {
		
		$this->username = $username;
		$this->response = $response;
		$this->conn = $conn;

		// Get IP-address
		$this->ip = $_SERVER['REMOTE_ADDR'];
	}
	
	
	
	public function Login() {
		
		$query = "SELECT * FROM `users` WHERE `username` = '".$this->username."'";
  		$result = $this->conn->query($query) or trigger_error(mysql_error());
  
  		if(mysql_num_rows($result) == 0) {
    		throw new Exception('Login incorrect.');
  		}
  		else {
    
  			$res = mysql_fetch_array($result);
			$key = sha1($res['password'].':'.$_SESSION['challenge']);

    		if($key != $this->response) {
      			throw new Exception('Login incorrect.');
    		}
    		else {
    			// unset sessions
    			unset($_SESSION['logintry']);
    			unset($_SESSION['challenge']);
    			
    			// set time session (last action)
    			$_SESSION['lastaction'] = time();
    			$_SESSION['userid'] = $res['id'];
    			
    			return true;
    		}
  		}
	}
	
	public function checkLoginTimes() {
		// Als de sessie bestaat ..
		if($_SESSION['logintry'] !== FALSE) {
			$logintimes = 3; // STANDARD OP 3, voor te testen op 100

			if($_SESSION['logintry'] > $logintimes) {
				throw new Exception('Too many login times.');
			}
			else {
				$_SESSION['logintry']++;
				return true;
			}
		}
		// Als de sessie niet bestaat, sessie maken met waarde 0
		else {
			$_SESSION['logintry'] = 0;
			return true;
		}
	}
	
	public function checkUsername() {
		
		if(trim($this->username) == '') throw new Exception('Enter a username.');
		elseif(strlen(trim($this->username)) < 3) throw new Exception('Username to short.');
		elseif(strlen(trim($this->username)) > 25) throw new Exception('Username to long.');
		else return true;
	}
}

class Challenge {
	
	private $challenge;

	public function __construct() {
		
		// Zet IP-adres sessie om session hijacking tegen te gaan!
		if(isset($_SESSION['ipaddr']) == FALSE ) {
  			$_SESSION['ipaddr'] = $_SERVER['REMOTE_ADDR'];
		}	
	}
	
	public function getChallenge() {
		return $this->challenge;
	}
	
	public function setChallenge() {
		
		// Per page-reload, genereer nieuwe challenge
		$this->challenge = $this->generate_string(255);
		$_SESSION['challenge'] = $this->challenge;
	}
	
	private function generate_string($length) {
		
	  	srand(((double) microtime()) * 1000000);
	  	$string = '';
	 
		$t = 'abcdefghijklmnopqrstuvwxyz';
		$t .= 'ABCDEFGHIJKLMNOPQRSTUWXYZ';
		$t .= '01234567890123456789';
		 
		for($i = 0; $i < $length; $i++) {
	 		$string .= $t{ rand( 0, (strlen($t) - 1)) };
		}
		return $string;
	}
}

class ControlLogin {
	
	private $ip;
	private $conn;
	
	public function __construct($conn) {
		
		$this->conn = $conn;
		$this->ip = $_SERVER['REMOTE_ADDR'];
	}
	
	public function Logout() {

		die('brol');
		//$this->conn->query(" UPDATE `users` SET `lastaction` = '".$_SESSION['lastaction']."' WHERE `id` = '".$_SESSION['userid']."' ") or die("Database error");
		session_unset(); 
		session_destroy();
		header("Location: login.php");
	}
	
	public function CheckSession() {

		$now = time();
		
		// session hijacking ...
		if(isset( $_SESSION['ipaddr']) AND $_SESSION['ipaddr'] != $this->ip) {
  			$this->Logout();
		}
		elseif(!isset($_SESSION['lastaction'])) {
			$this->Logout();
		}
		// als 30 min (1800sec) geen activiteit.. destroy;
		elseif(isset($_SESSION['lastaction']) AND (time() - $_SESSION['lastaction']) > 10 ) {
			$this->Logout();
		}
		elseif (!isset($_SESSION['userid'])) {
			$this->Logout();
		}
		else {
			$_SESSION['lastaction'] = $now;
			return true;
		}
	}
}
?>