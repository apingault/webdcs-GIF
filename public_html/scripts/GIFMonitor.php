<?php
$conn = new mysqli("localhost", "root", "UserlabGIF++", "DIP"); // connect to remote host
if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Get DIP values
$q = $conn->query("SELECT * FROM source ORDER BY timestamp DESC LIMIT 1") or die(mysql_error());
$source = $q->fetch_assoc();

$q = $conn->query("SELECT * FROM attenuator ORDER BY timestamp DESC LIMIT 1") or die(mysql_error());
$attenuator = $q->fetch_assoc();

$q = $conn->query("SELECT * FROM environmental ORDER BY timestamp DESC LIMIT 1") or die(mysql_error());
$env = $q->fetch_assoc();

$q = $conn->query("SELECT * FROM gas ORDER BY timestamp DESC LIMIT 1") or die(mysql_error());
$gas = $q->fetch_assoc();

$q = $conn->query("SELECT * FROM radmon ORDER BY timestamp DESC LIMIT 1") or die(mysql_error());
$radmon = $q->fetch_assoc();

$conn->Close();


$totflow = $gas['SF6'] + $gas['C2H2F4'] + $gas['iC4H10'];
?>

    <table>
        <tr>
            <td colspan="2" width="400px" style="height: 20px;"><b>Source parameters:</b></td>
            <td colspan="2" width="400px" style="height: 20px;"><b>Environmental parameters:</b></td>
        </tr>
        <tr>
            <td width="150px" style="height: 20px;">Source status:</td>
            <td width="150px">
                <?php echo ($source['SourceON'] == 1) ? '<font color="green"><b>SOURCE ON</b></font>' : '<font color="red"><b>SOURCE OFF</b></font>' ?>
            </td>
            
            <td width="150px" style="height: 20px;">Pressure upstream:</td>
            <td width="150px"><?php echo $env['P201']; ?> mbar</td>
        </tr>
        <tr>
            <td style="height: 20px;">Upstream attenuation:</td>
            <td>
                <?php echo $attenuator['AttUEff'].' ['.$attenuator['AttUA'].' '.$attenuator['AttUB'].' '.$attenuator['AttUC'].']' ?>
            </td>
            
            <td style="height: 20px;">Temperature upstream:</td>
            <td><?php echo $env['T201']; ?> °C</td>
            
        </tr>
        <tr>
            <td style="height: 20px;">Downstream attenuation:</td>
            <td>
                <?php echo $attenuator['AttDEff'].' ['.$attenuator['AttDA'].' '.$attenuator['AttDB'].' '.$attenuator['AttDC'].']' ?>
            </td>
            
            <td style="height: 20px;">Humidity upstream:</td>
            <td><?php echo $env['RH201']; ?> %</td>
            
        </tr>
        <tr>
            <td style="height: 20px;">Attenuator status:</td>
            <td><?php echo ($gas['moving'] == 1) ? "moving" : "stable"; ?></td>
            
            <td style="height: 20px;">Pressure near source:</td>
            <td><?php echo $env['P']; ?> mbar</td>
            
        </tr>
        <tr>
            <td style="height: 20px;">Veto:</td>
            <td><?php echo ($source['veto'] == 1) ? "yes" : "no"; ?></td>
            
            <td style="height: 20px;">Temperature near source:</td>
            <td><?php echo $env['TIN']; ?> °C</td>
            
        </tr>
        <tr>
            <td colspan="2"></td>
            
            <td style="height: 20px;">Humidity near source:</td>
            <td><?php echo $env['RHIN']; ?> %</td>
        </tr>
        <tr>
            <td colspan="2" width="400px"><b>Gas parameters:</b></td>
            <td style="height: 20px;">Pressure downstream:</td>
            <td><?php echo $env['P202']; ?> mbar</td>
        </tr>
        
        <tr>
            <td style="height: 20px;">Flow C2H2F4:</td>
            <td><?php printf("%.2f %% (%.2f l/h)", 100*$gas['C2H2F4']/$totflow, $gas['C2H2F4']); ?></td>
            
            <td style="height: 20px;">Temperature downstream:</td>
            <td><?php echo $env['T202']; ?> °C</td>
            
        </tr>
        <tr>
            <td style="height: 20px;">Flow iC4H10:</td>
            <td><?php printf("%.2f %% (%.2f l/h)", 100*$gas['iC4H10']/$totflow, $gas['iC4H10']); ?></td>
            
            <td style="height: 20px;">Humidity downstream:</td>
            <td><?php echo $env['RH202']; ?> %</td> 
        </tr>
        <tr>
            <td style="height: 20px;">Gas SF6:</td>
            <td><?php printf("%.2f %% (%.2f l/h)", 100*$gas['SF6']/$totflow, $gas['SF6']); ?></td>
            
            <td colspan="2" width="400px"></td>
        </tr>
        <tr>
            <td style="height: 20px;">Flow mixture with water:</td>
            <td><?php echo $gas['mixture_with_water']; ?> l/h</td>
            
            <td colspan="2" width="400px" style="height: 20px;"><b>Radmon dose measurements:</b></td>
            
        </tr>
        <tr>
            <td style="height: 20px;">Flow mixture without water:</td>
            <td><?php echo $gas['mixture_without_water']; ?> l/h</td>
            
            <td style="height: 20px;">Radmon 1:</td>
            <td><?php echo $radmon['D1']; ?> Gy</td>
            
        </tr>
        <tr>
            <td style="height: 20px;">Dew point:</td>
            <td><?php echo $gas['RPC_MFC_Humidity']; ?> °C</td>
            
            <td style="height: 20px;">Radmon 2:</td>
            <td><?php echo $radmon['D2']; ?> Gy</td>
            
        </tr>
        <tr>
            <td style="height: 20px;">IR iC4H10_BINOS1/2:</td>
            <td><?php echo $gas['iC4H10_BINOS1']; ?> % / <?php echo $gas['iC4H10_BINOS2']; ?> %</td>
            
            <td style="height: 20px;">Radmon 3:</td>
            <td><?php echo $radmon['D3']; ?> Gy</td>
            
        </tr>
        <tr>
            <td style="height: 20px;">Pressure gas box 102:</td>
            <td><?php echo $gas['P102']; ?> mbar</td>
            
            <td style="height: 20px;">Radmon 4:</td>
            <td><?php echo $radmon['D4']; ?> Gy</td>
        </tr>
        
        <tr>
            <td style="height: 20px;">Temperature gas box 102:</td>
            <td><?php echo $gas['T102']; ?> °C</td>
            
            <td style="height: 20px;">Radmon 5:</td>
            <td><?php echo $radmon['D5']; ?> Gy</td>
        </tr>
        
        <tr>
            <td style="height: 20px;">Humidity gas box 102:</td>
            <td><?php echo $gas['RH102']; ?> %</td>
            
            <td style="height: 20px;">Radmon 6:</td>
            <td><?php echo $radmon['D6']; ?> Gy</td>
        </tr>
        
        <tr>
            <td style="height: 20px;"></td>
            <td><?php ?></td>
            
            <td style="height: 20px;">Radmon 7:</td>
            <td><?php echo $radmon['D7']; ?> Gy</td>
        </tr>
        
        <tr>
            <td style="height: 20px;"></td>
            <td><?php ?> </td>
            
            <td style="height: 20px;">Radmon 8:</td>
            <td><?php echo $radmon['D8']; ?> Gy</td>
        </tr>
        
    </table>
   
