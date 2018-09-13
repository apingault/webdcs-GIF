
// Function for updating the monitoring page
function updateValues() {

    $.ajax({
        type: 'POST',
        url: 'scripts/readValues.php',
        dataType: 'json',
        cache: false,
        success: function(result) {
            
            // EXAMPLE
 
            /*
            var result;
            result[0][0] = 1;
            result[0][1] = '1.00';
            result[0][2] = '1.00';
            result[0][3] = '1.00';
            result[0][4] = '1.00';
            result[0][5] = 'Status';
            result[0][6] = 1;
            result[0][7] = 0;
            /*
            result[1][0] = '2';
            result[1][1] = '1.00';
            result[1][2] = '1.00';
            result[1][3] = '1.00';
            result[1][4] = '1.00';
            result[1][5] = 'Status';
            result[1][6] = 0;
            result[1][7] = 0;
            
            result[2][0] = '4';
            result[2][1] = '1.00';
            result[2][2] = '1.00';
            result[2][3] = '1.00';
            result[2][4] = '1.00';
            result[2][5] = '1.00';
            result[2][6] = 1;
            result[2][7] = 1;
     
            result[3][0] = '6';
            result[3][1] = '1.00';
            result[3][2] = '1.00';
            result[3][3] = '1.00';
            result[3][4] = '1.00';
            result[3][5] = 'detector_connection_error';
            result[3][6] = 1;
            result[3][7] = 1;
            
            result[4][0] = '7';
            result[4][1] = '1.00';
            result[4][2] = '1.00';
            result[4][3] = '1.00';
            result[4][4] = '1.00';
            result[4][5] = '512';
            result[4][6] = 1;
            result[4][7] = 0;
            */

            // Single-line error
            if(result == "database_connection_error") {
                $('#moduleStatus').html('Database connection error');
                $("#monitor-form :input").attr("disabled", true);
            }
            // Single-line error
            else if(result == "module_connection_error") {
                $('#moduleStatus').html('Module connection error');
                $("#monitor-form :input").attr("disabled", true);
            }
            else {

                //alert("dd"); 
                
                /* RETURN STRUCTURE
                 *      result[0]: detector ID
                 *      result[1]: Vset
                 *      result[2]: Vmon
                 *      result[3]: Iset
                 *      result[4]: Imon
                 *      result[5]: Status: detector_connection_error or CAEN status code
                 *      result[6]: Power (0 or 1)
                 *      result[7]: Process (0, 1 or 2)
                 */
      
                var i;
                var id;
                var tripped;
                var status; 
                for (i = 0; i < result.length; ++i) {  
                    
                    id = result[i][0]; // Detector ID 
                    if(result[i][2] == 'detector_connection_error') {
                        $("#detector_"+id+" :input").attr("disabled", true); // Disable all forms in the row
                        status = '<font style="color: red; font-weight: bold;">Readout error</font>';
                    }
                    else {
                        
                        // If HVscan
                        if(result[i][7] == '1') {
                            $("#detector_"+id+" :input").attr("disabled", true); // Disable all forms in the row
                            status = '<font style="color: green; font-weight: bold;">HVscan</font>';
                        }
                        // If Stabilitytest
                        else if(result[i][7] == '2') {
                            $("#detector_"+id+" :input").attr("disabled", true); // Disable all forms in the row
                            status = '<font style="color: green; font-weight: bold;">Monitoring</font>';
                        }
                        // If terminating Stabilitytest
                        else if(result[i][7] == '3') {
                            $("#detector_"+id+" :input").attr("disabled", true); // Disable all forms in the row
                            status = '<font style="color: green; font-weight: bold;">Terminating monitoring</font>';
                        }
                        // If suspended stabilitytest
                        else if(result[i][7] == '4') {
                            $("#detector_"+id+" :input").attr("disabled", true); // Disable all forms in the row
                            status = '<font style="color: orange; font-weight: bold;">Monitoring, suspended</font>';
                        }
                        else {

                            // If power on
                            if(result[i][6] == '1') {
                                $("#detector_"+id+" :input").attr("disabled", false);
                                $("#poweron_"+id).attr('disabled',true);
                                status = '<font style="color: green; font-weight: bold;">Power ON</font>';
                            }
                            else {
                                $("#detector_"+id+" :input").attr("disabled", true);
                                $("#poweron_"+id).attr('disabled',false);
                                status = '<font style="color: red; font-weight: bold;">Power OFF</font>';
                            } 
                        } 
                    } 
                    
                    // Show status
                    if(result[i][5] == '512') tripped = '<font style="color: red; font-weight: bold;">512 TRIPPED</font>';
                    else tripped = '<font style="font-weight: bold;">CAEN Status: '+result[i][5]+'</font>';
                    

                    $('#status_'+id).html(status);
                    $('#setValue_'+id).html('V<sub>app</sub> = '+result[i][1]+' V<br />I0<sub>set</sub> = '+result[i][3]+' uA');
                    $('#getValue_'+id).html('V<sub>mon</sub> = '+result[i][2]+' V<br />I<sub>mon</sub> = '+result[i][4]+' uA<br />'+tripped);
                }
            }
        },
    });
    
    // Update monitoring page each 2 seconds
    setTimeout(function(){updateValues();},2000);
}
    


function readStabilityLogFile(id) {

    $.ajax({
        type: 'POST',
        url: 'scripts/StabilityLog.php?id='+id,
        cache: false,
        success: function(result) {
      
            
            $('#logFile').html(result);
        }
    });
    setTimeout(function(){readStabilityLogFile(id);},2000);
}

function GIFMonitoring() {

    $.ajax({
        type: 'POST',
        url: 'scripts/GIFMonitor.php',
        cache: false,
        success: function(result) {
            
            $('#GIFMonitoring').html(result);
        }
    });
    setTimeout(function(){GIFMonitoring();},60000);
}



  
  
