<?php
/*
 * setModules.php
 */

$mid = (isset($_GET['mid'])) ? $_GET['mid'] : '';

// Get all module id's
$sth = $dbh->prepare("SELECT id FROM modules");
$sth->execute();
$mids = $sth->fetchAll(PDO::FETCH_COLUMN, 0);

if($mid != '' AND in_array($mid, $mids)) {

    $sth = $dbh->prepare("SELECT * FROM modules WHERE id = :mid");
    $sth->execute(array(':mid' => $mid));
    $module = $sth->fetch(PDO::FETCH_ASSOC);


    $name = $module['name'];
    $address = $module['address'];
    $username = $module['username'];
    $password = $module['password'];
    $meteo_file = $module['meteo_file'];
    $comments = $module['comments']; 
    $ref_pressure = $module['ref_pressure'];
    $ref_temperature = $module['ref_temperature'];
}
else {
  
    $name = "";
    $address = "";
    $username = "";
    $password = "";
    $meteo_file = "";
    $comments = "";
    $ref_pressure ="970";
    $ref_temperature ="20";
}

// Save config file
if(isset($_POST['submit']) AND $_POST['submit'] == 'Save configuration') {
  
        $name = stripString($_POST['name']);
        $address = $_POST['address'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $meteo_file = $_POST['meteo_file'];
        $ref_pressure = $_POST['ref_pressure'];
        $ref_temperature = $_POST['ref_temperature'];
        $comments = htmlentities($_POST['comments'], ENT_QUOTES);

    if(str_replace(' ', '', $name) == '' OR str_replace(' ', '', $address) == '' OR str_replace(' ', '', $username) == '' OR str_replace(' ', '', $password) == '' OR str_replace(' ', '', $meteo_file) == '' OR str_replace(' ', '', $ref_pressure) == '') {

        $error = 'please fill in all fields.';
    }
    elseif(!filter_var($address, FILTER_VALIDATE_IP)) {

        $error = 'incorrect address IP.';
    }
    else {
        
        if($mid == '') {
            
            $sql = "INSERT INTO modules (name, address, username, password, meteo_file, comments, ref_pressure, ref_temperature) VALUES (:name, :address, :username, :password, :meteo_file, :comments, :ref_pressure; ref_temperature)";
            $sth1 = $dbh->prepare($sql);
            $sth1->bindParam(':name', $name, PDO::PARAM_STR);  
            $sth1->bindParam(':address', $address, PDO::PARAM_STR);
            $sth1->bindParam(':username', $username, PDO::PARAM_STR);
            $sth1->bindParam(':password', $password, PDO::PARAM_STR);
            $sth1->bindParam(':meteo_file', $meteo_file, PDO::PARAM_STR);
            $sth1->bindParam(':ref_pressure', $ref_pressure, PDO::PARAM_STR);
            $sth1->bindParam(':ref_temperature', $ref_temperature, PDO::PARAM_STR);
            $sth1->bindParam(':comments', $comments, PDO::PARAM_STR);
            $sth1->execute();  
            
            // Insert values into stabilitytest_config
            $id = $dbh->lastInsertId();
            $sth1 = $dbh->prepare("INSERT INTO stabilitytest_config (mid) VALUES(:id) ");
            $sth1->execute(array('id' => $id));
            
            // Make directories
            mkdir("/home/user/data/stabilitytest/".$name, 0755);
            mkdir("/home/user/data/hvscan/".$name, 0755);
            mkdir("/home/user/data/tmp/".$id, 0755);
        }
        else {
            $sql = "UPDATE modules SET name=:name, address=:address, username=:username, password=:password, meteo_file=:meteo_file, comments=:comments, ref_pressure=:ref_pressure, ref_temperature=:ref_temperature WHERE id = :id";
            $sth1 = $dbh->prepare($sql);
            $sth1->bindParam(':id', $mid);  
            $sth1->bindParam(':name', $name, PDO::PARAM_STR);  
            $sth1->bindParam(':address', $address, PDO::PARAM_STR);
            $sth1->bindParam(':username', $username, PDO::PARAM_STR);
            $sth1->bindParam(':password', $password, PDO::PARAM_STR);
            $sth1->bindParam(':meteo_file', $meteo_file, PDO::PARAM_STR);
            $sth1->bindParam(':ref_pressure', $ref_pressure, PDO::PARAM_STR);
            $sth1->bindParam(':ref_temperature', $ref_temperature, PDO::PARAM_STR);
            $sth1->bindParam(':comments', $comments, PDO::PARAM_STR);
            $sth1->execute();     
        } 
    }
}
?>



<div class="content">
  
    <h3>Add or edit CAEN High Voltage supply mainframe</h3>
    
    <?php 
    if(!empty($error)) echo '<div class="error">Error: '.$error.'</div>'; 
    elseif(isset($_POST['submit'])) echo '<div class="pass">Configuration successfully saved.</div>'; 
    ?>
  
    <table>
 
        <form method="post" action="" id="detectors-form" autocomplete="off">   
   
        <tr>
            <td width="175px">Power supply name:</td>
            <td width="250px"><input style="width: 175px;" name="name" value="<?php echo $name; ?>" type="text"></td>
            
        </tr>
        <tr>
            <td>Power supply IP address:</td>
            <td><input style="width: 175px;" name="address" value="<?php echo $address; ?>" type="text"></td>
            
        </tr>
        <tr>
            <td>Username:</td>
            <td><input style="width: 175px;" name="username" value="<?php echo $username; ?>" type="text"></td>
        </tr>
        <tr>
            <td>Password:</td>
            <td><input style="width: 175px;" name="password" value="<?php echo $password; ?>" type="password"></td>
        </tr>
        <tr>
            <td>Meteo file:</td>
            <td>
                <select name="meteo_file">
                    <option value="nometeo">No meteo file</option>
                    <?php
                    if($handle = opendir($config['meteo_dir'])) {
                        while (false !== ($entry = readdir($handle))) {
                            if ($entry != "." && $entry != "..") {
                                $sel = ($meteo_file == $entry) ? 'selected="selected"' : '';
                                echo '<option '.$sel.' value="'.$entry.'">'.$entry.'</option>';
                            }
                        }
                    }
                    ?>
                </select>
                
            
            </td>
        </tr>
        <tr>
            <td>Reference pressure:</td>
            <td><input style="width: 175px;" name="ref_pressure" value="<?php echo $ref_pressure; ?>" type="text"> (mbar)</td>
        </tr>    
        <tr>
            <td>Reference temperature*:</td>
            <td><input style="width: 175px;" name="ref_temperature" value="<?php echo $ref_temperature; ?>" type="text"> (Celcius)</td>
        </tr>    
        <tr valign="top">
            <td style="padding-top: 3px">Additional information:</td>
            <td><textarea name="comments" style="width: 175px" rows="2"><?php echo html_entity_decode($comments, ENT_QUOTES) ?></textarea></td>
        </tr>
        
        <tr>
          <td colspan="2"><input value="Save configuration" type="submit" name="submit" /></td>
        </tr>
        
        </form>
    </table>
    
    <br />
    <font style="font-size: 10px">* to disable the temperature correction, set the reference value to zero.</font>
</div>