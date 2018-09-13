<?php 
//die("Maintenance");
error_reporting(E_ALL);
ini_set('display_errors', 1);
//session_start();
//ob_start();
//require_once('library/config.php');
//require_once('library/functions.php');
require_once('library/login/login.class.php');

db_connect();

$error = '';

$challenge = new Challenge();

if(isset($_POST['submit']) AND isset($_POST['username']) AND isset($_POST['response']) AND isset($_SESSION['challenge'])) {
	
	try {
		
		$login = new Login($_POST['username'], $_POST['response']);
		//$login->checkLoginTimes();
		$login->checkUsername();
		$login->Login();
		
	}
	catch (Exception $e) {
		$message = $e->getMessage();
	}
	
	if($message != '') {
		$error = $message;
	}
	else header('Location: index.php');
}

// generate challenge
$challenge->setChallenge();


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">  
	<title>WebDCS - login</title>
    <script type="text/javascript" src="library/login/sha1.js"></script>
	<script type="text/javascript">
		function createResponse() {
			document.getElementById('response').value = hex_sha1(hex_sha1(document.getElementById('password').value)+':'+document.getElementById('challenge').value);
			document.getElementById('password').value = "";
			document.getElementById('challenge').value = "";
			return true;
		}
	</script>
	<link rel='stylesheet' href='http://codepen.io/assets/libs/fullpage/jquery-ui.css'>
    <style type="text/css">
@import url(http://fonts.googleapis.com/css?family=Roboto:400,100);

body {
  font-family: 'Roboto', sans-serif;
}

.login-card {
  padding: 40px;
  width: 274px;
  background-color: #F7F7F7;
  margin: 0 auto 10px;
  border-radius: 2px;
  box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
  overflow: hidden;
}

.login-card h1 {
  font-weight: 100;
  text-align: center;
  font-size: 2.3em;
}

.login-card input[type=submit] {
  width: 100%;
  display: block;
  margin-bottom: 10px;
  position: relative;
}

.login-card input[type=text], input[type=password] {
  height: 44px;
  font-size: 16px;
  width: 100%;
  margin-bottom: 10px;
  -webkit-appearance: none;
  background: #fff;
  border: 1px solid #d9d9d9;
  border-top: 1px solid #c0c0c0;
  /* border-radius: 2px; */
  padding: 0 8px;
  box-sizing: border-box;
  -moz-box-sizing: border-box;
}

.login-card input[type=text]:hover, input[type=password]:hover {
  border: 1px solid #b9b9b9;
  border-top: 1px solid #a0a0a0;
  -moz-box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
  -webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
  box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
}

.login {
  text-align: center;
  font-size: 14px;
  font-family: 'Arial', sans-serif;
  font-weight: 700;
  height: 36px;
  padding: 0 8px;
/* border-radius: 3px; */
/* -webkit-user-select: none;
  user-select: none; */
}

.login-submit {
  /* border: 1px solid #3079ed; */
  border: 0px;
  color: #fff;
  text-shadow: 0 1px rgba(0,0,0,0.1); 
  background-color: #4d90fe;
  /* background-image: -webkit-gradient(linear, 0 0, 0 100%,   from(#4d90fe), to(#4787ed)); */
}

.login-submit:hover {
  /* border: 1px solid #2f5bb7; */
  border: 0px;
  text-shadow: 0 1px rgba(0,0,0,0.3);
  background-color: #357ae8;
  /* background-image: -webkit-gradient(linear, 0 0, 0 100%,   from(#4d90fe), to(#357ae8)); */
}

.login-card a {
  text-decoration: none;
  color: #666;
  font-weight: 400;
  text-align: center;
  display: inline-block;
  opacity: 0.6;
  transition: opacity ease 0.5s;
}

.login-card a:hover {
  opacity: 1;
}

.login-help {
  width: 100%;
  text-align: center;
  font-size: 12px;
}	
	</style>
</head>
<body>
	<div class="login-card">
		<center><img width="50%" src="config/images/cms.gif" /></center><br>
			
		<form action="" method="post" onsubmit="return createResponse();">
			<input type="text" placeholder="Username" name="username">
			<input type="password" placeholder="Password" id="password">
			<input type="submit" name="submit" class="login login-submit" value="login">

			<input type="hidden" id="challenge" value="<?php echo $challenge->getChallenge(); ?>">
			<input type="hidden" name="response" id="response" value="">
		</form>

		<div class="login-help">
		WebDCS <?php echo EXPERIMENT_NAME; ?><!--<a href="#">Register</a> • <a href="#">Forgot Password</a>
		<?php
		if($error != '') {
			
			echo '<br /><br /><font color="red">'.$error.'</font>';
		}
		?>
		</div>
	</div>

<!-- <div id="error"><img src="https://dl.dropboxusercontent.com/u/23299152/Delete-icon.png" /> Your caps-lock is on.</div> -->

  <script src='http://codepen.io/assets/libs/fullpage/jquery_and_jqueryui.js'></script>

</body>

</html>