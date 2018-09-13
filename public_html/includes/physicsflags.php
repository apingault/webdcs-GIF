<?php
if(isset($_GET['id'])) {
    
    $sth1 = $dbh->prepare("SELECT * FROM physics_flags WHERE id = ".$_GET['id']);
    $sth1->execute();
    $res = $sth1->fetch();
    
    $flagname = $res['flagname'];
    $runids = str_replace(" ", "", $res['runids']);
    $comments = $res['comments'];
    $newFlag = false;
}
else {
    
    $flagName = "";
    $runids = "";
    $comments = "";
    $newFlag = true;
}

if(isset($_POST['submit'])) {
    
    $flagname = filter_input(INPUT_POST, 'flagname');
    $runids = filter_input(INPUT_POST, 'runids');
    $comments = filter_input(INPUT_POST, 'comments');
    
    if(trim($flagname) == "") $error = "Test";
    else {
        
        if($newFlag) {
            $sth1 = $dbh->prepare("INSERT INTO physics_flags (flagname, runids, comments) VALUES (:flagname, :runids, :comments) "); 
            $sth1->bindParam(':flagname', $flagname); 
            $sth1->bindParam(':runids', $runids); 
            $sth1->bindParam(':comments', $comments); 
            $sth1->execute();
        }
        else {
            $sth1 = $dbh->prepare("UPDATE physics_flags SET flagname = :flagname, runids = :runids, comments = :comments WHERE id = ".$_GET['id']); 
            $sth1->bindParam(':flagname', $flagname); 
            $sth1->bindParam(':runids', $runids); 
            $sth1->bindParam(':comments', $comments); 
            $sth1->execute();
        }
    }
}

if(isset($_POST['deleteflag'])) {
    
    $sth1 = $dbh->prepare("DELETE FROM physics_flags WHERE id = ".$_GET['id']); 
    $sth1->execute(); 
}


$sth1 = $dbh->prepare("SELECT * FROM physics_flags ORDER BY id DESC");
$sth1->execute();
$flags = $sth1->fetchAll();

?>


<div class="content">
    
    <h3>Physics flags administration</h3>
    
    <form method="post" action="">
        <table>
            
            <tr style="height:25px">
                <td width="150px">Flag name:</td>
                <td><input style="width: 250px;" name="flagname" value="<?=$flagname?>" type="text"></td>
            </tr>
            <tr style="height: 25px">
                <td valign="top">Run IDs:</td>
                <td><textarea rows="5" style="width: 400px;" name="runids"><?php echo $runids; ?></textarea></td>
            </tr>
            <tr style="height: 25px">
                <td valign="top">Comments:</td>
                <td><textarea rows="3" style="width: 400px;" name="comments"><?php echo $comments; ?></textarea></td>            
            </tr>
            <tr style="height: 25px">
                <td></td>
                <td><input type="submit" value="<?php echo ($newFlag) ? 'Add flag' : 'Update flag'; ?>" name="submit" /><?php echo (!$newFlag) ? ' <input type="submit" name="deleteflag" value="Delete flag" />' : ''; ?></td>            
            </tr>
        </table>
    </form>
    
    <br /><br />

    <table style="width: 1000px;" cellpadding="5px" cellspacing="0">

        <thead style="font-weight: bold;">
            <tr>
                <td class="oddrow" width="10%">Flag ID</td>
                <td class="oddrow" width="20%">Flag name</td>
                <td class="oddrow" width="40%">Run ids</td>
                <td class="oddrow" width="30%">Comment</td>
            </tr>
        </thead>

        <tbody>
        <?php

        $i = 0;
        foreach ($flags as $f) {

            $class = ($i%2 == 0) ? 'evenrow' : 'oddrow';
            echo '<tr data-href="index.php?q=physicsflags&id='.$f['id'].'" class="'.$class.' clickable-row">';
            echo '<td>'.$f['id'].'</td>';
            echo '<td>'.$f['flagname'].'</td>';
            echo '<td style="word-break: break-all;">'.$f['runids'].'</td>';
            echo '<td valign="top">'.$f['comments'].'</td>';
            echo '</tr>';
            $i++;
        }
        ?>

        </tbody>
    </table>
    
</div>