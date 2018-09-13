$(document).ready(function() {

    // Handle changing mainframes
    $('#changeCurrentMid').on('change', function() {
          
        var value = this.value;
        var page = $('#changeCurrentMid_page').val();
        $.ajax({
            url: "scripts/updateCurrentMid.php?mid="+value,
            context: document.body
        }).done(function() {
            window.location.href = 'index.php?q='+page;
        });
    });
    
    // Handle changing hvscan type
    $('#hvscan_type').on('change', function() {
          
        var value = this.value;
        window.location.href = 'index.php?q=hvscan&type='+value;
    });



    // DAQ HVSCAN FUNCTIONS
    $('#scantype').change(function() {
        
        if($(this).val() == 'efficiency') {
            $('#beam').val('1');
            $('#partition').val('A');
        }
        else if($(this).val() == 'rate') {
            $('#beam').val('0');
            //$('#partitionNone').removeAttr('disabled','disabled');
            $('#partitionNone').prop("disabled", false);
            $('#partition').val('none');
        }
        else if($(this).val() == 'noise_reference') {
            $('#beam').val('0');
            $('#partition').val('none');
        }
  
    });



    // Add HV line button
 

    $("#addHV").click( function() {

        var rowCount = $('#tableVoltages tr').length;

        var $tr = $(this).closest('.HVline');
        var $clone = $tr.clone();

        //$clone.find(':text').val('');
        $tr.after($clone);
        //alert($clone.attr('class'));

         alert("ddd");


    });


    $("#editPositionLink").click(function () {
        $("#editPositionDiv").toggleClass("hidden unhidden");
    });


    $("#filterRunRegistry").click(function () {
        $("#filterRunRegistryDiv").toggleClass("hidden unhidden");
    });

    
    $('.clickable-row').css('cursor', 'pointer');
    $(".clickable-row").click(function() {
        window.document.location = $(this).data("href");
    });


   
});


function logout() {
	
	var b = confirm("Are you sure you want to logout?");
	if(b) window.location.href = 'index.php?q=logout';

}
