<?php

if(isset($_POST['submit'])) {
    
    
    
}



function form_textfield($name, $label, $value, $enabled = true, $size = 20) {
    
    $f = (!$enabled) ? 'disabled="disabled"' : "";
    echo '<div class="block">';
    echo '<label>'.$label.'</label>';
    echo '<input name="'.$name.'" '.$f.' type="text" value="'.$value.'" size="'.$size.'" />';
    echo '</div>';
}


?>


<h3>Add chamber</h3>

<style>
    
    .block {
        
        height: 25px;
    }
    
    label {
  display: inline-block;
  width: 160px;
  text-align: left;
}​
</style>


<form action="" method="POST">
    
    <?php 
    
    form_textfield("name", "Name", "");
    form_textfield("npartitions", "Number of gaps", "will be calculated automatically", false);
    form_textfield("ngaps", "Number of partitions", "");
    
    ?>
    
    
<div class="block">
    <label>Label with more text</label>
    <input type="text" />
</div>
<div class="block">
    <label>Short</label>
    <input type="text" />
</div>
    
    
    
</form>

