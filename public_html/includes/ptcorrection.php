<?php

require_once 'DIP/functions.php';

$P = "";
$TIN = "";
$name = "";
$unit = "";

getValue("P904", $P, $name, $unit);
getValue("T0904", $TIN, $name, $unit);

$beta = file_get_contents("/var/operation/RUN/PTcorr");


if( (time() - filemtime("/var/operation/RUN/PTcorr") ) > 300) {
    
    $status = '<font color="red"><b>ERROR</b></font>';
}
else $status = '<font color="green"><b>OK</b></font>';

?>

<div class="content">

<h3 style="display: inline;">PT correction</h3>

<br /><br />


<table class="DIPTable">
    
    <tr>
        <td colspan="2"><br /><b>Formula and parameters</b></td>
    </tr>

    <tr>
        <td width="200px">Formula:</td>
        <td width="500px"><img src="http://latex.codecogs.com/gif.latex?HV_{app} = \beta HV_{eff} = HV_{eff} \left( (1-\alpha) + \alpha \tfrac{P}{P_0}\tfrac{T_0}{T} \right)" border="0"/></td>
    </tr>
    
    <tr>
        <td>Parameter &alpha;:</td>
        <td>0.8</td>
    </tr>
    
    <tr>
        <td>Parameter P<sub>0</sub>:</td>
        <td>990 mbar</td>
    </tr>
    
    
    <tr>
        <td>Parameter T<sub>0</sub>:</td>
        <td>293.15 K</td>
    </tr>

    <tr>
        <td colspan="2"><br /><b>Actual values and DIP</b></td>
    </tr>
    

    <tr>
        <td>Status:</td>
        <td><?=$status?></td>
    </tr>

    <tr>
        <td>&beta;:</td>
        <td><?=$beta?></td>
    </tr>
    
    <tr>
        <td>Parameter P:</td>
        <td><?=$P?> mbar (source P)</td>
    </tr>
    
    <tr>
        <td>Parameter T:</td>
        <td><?=$TIN?> degC (source TIN)</td>
    </tr>


</div>