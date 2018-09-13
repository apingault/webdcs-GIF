<?php
if(!defined('INDEX')) die("Access denied");


if(isset($_POST['submit'])) {
	
	if(empty($_POST['name']) OR empty($_POST['email'])) {
		$error = "please fill in a name and email.";
	}
	else {
		
		$name = htmlentities($_POST['name'], ENT_QUOTES);
		$email = htmlentities($_POST['email'], ENT_QUOTES);
		
		if (!empty($_POST['enc_pass1'])) {
			if($_POST['enc_pass1'] !== $_POST['enc_pass2']) {
				$error = "passwords do not match.";
			}
			else {
				
				$sth1 = $dbh->prepare(" UPDATE users SET name = :name, email = :email, password = :pass WHERE id = :id");
				$sth1->execute(array(":name" => $name, ":email" => $email, ":pass" => $_POST['enc_pass1'], ":id" => $_SESSION['userid']));
				$pass = "User settings updated.";
			}
		}
		else {
			$sth1 = $dbh->prepare(" UPDATE users SET name = :name, email = :email WHERE id = :id");
			$sth1->execute(array(":name" => $name, ":email" => $email, ":id" => $_SESSION['userid']));
			$pass = "User settings updated.";
		}
	}
}


$sth1 = $dbh->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
$sth1->execute(array(":id" => $_SESSION['userid']));
$res = $sth1->fetch();

?>

<script type="text/javascript" src="/library/login/sha1.js"></script>
<script type="text/javascript">

function createResponse() {

  	if(document.getElementById('pass1').value == '' && document.getElementById('pass2').value == '') {
  		return true;
  	}
  	else {
		if(document.getElementById('pass1').value == document.getElementById('pass2').value) {

			document.getElementById('enc_pass1').value = hex_sha1(document.getElementById('pass1').value);
			document.getElementById('enc_pass2').value = hex_sha1(document.getElementById('pass2').value);
		  	document.getElementById('pass1').value = "";
		  	document.getElementById('pass2').value = "";
			return true;
		}
		else {
			alert("Please retype the password.");
			return false;
		}
  	}
}

</script>


<div class="content">
	
    <?php 
    if(!empty($error)) { echo '<div class="error">Error: '.$error.'</div>'; }
    elseif($pass != '') { echo '<div class="pass">'.$pass.'</div>'; }
    ?>
    
    <h3 style="display: inline;">Account settings</h3>
   
    <br /><br />
    

	<form action="" method="post" onsubmit="return createResponse();">
		
        <table>
            <tr style="height:25px">
                <td width="150px">Name:</td>
                <td><input style="width: 200px;" type="text" name="name" value="<?php echo $res['name']?>" /></td>
            </tr>
            <tr style="height: 25px">
                <td width="150px">Email:</td>
                <td><input style="width: 200px;" type="text" name="email" value="<?php echo $res['email']?>" /></td>
            </tr>

            <tr style="height: 25px">
                <td width="150px">Login name:</td>
                <td><input style="width: 200px;" disabled="disabled" type="text" name="username" value="<?php echo $res['username']?>" /></td>
            </tr>
            <tr style="height: 25px">
                <td width="150px">Password:</td>
                <td><input style="width: 200px;" type="password" id="pass1" /></td>
            </tr>
            <tr style="height: 25px">
                <td width="150px">Retype password:</td>
                <td><input style="width: 200px;" type="password" id="pass2" /></td>
            </tr>
			
			<tr style="height: 25px">
                <td width="150px"></td>
				<td>
                <input type="hidden" id="enc_pass1" name="enc_pass1" />
				<input type="hidden" id="enc_pass2" name="enc_pass2" />
				<input class="save" type="submit" name="submit" value="Save" />
				</td>
            </tr>

			   
        </table>
		
	</form>

    
</div>
