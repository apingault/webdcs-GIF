<?php
$daqfile = "/HVSCAN/".$idstring."/Scan".$idstring."_HV".$HV."_Offline.root";
$caenfile = "/HVSCAN/".$idstring."/Scan".$idstring."_HV".$HV."_CAEN.root";
$rawdaqfile = "/HVSCAN/".$idstring."/Scan".$idstring."_HV".$HV."_DAQ.root";
?>
<iframe frameborder="0" width="100%" height="600" src="includes/ROOTJS/index.htm?files=[<?=$daqfile?>,<?=$caenfile?>,<?=$rawdaqfile?>]">
</iframe>
