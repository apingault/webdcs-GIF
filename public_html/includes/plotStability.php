<?php
// this is the full file name (with path "home/user/data/stabilitytest/GIF/")
// It cannot be loaded for plots because the file is not inside the
// root directory of the web server (/home/user/www/htdocs)
// therefore files are symbolically linked to /home/user/www/htdocs/AMCHARTS/GIF
//
$file = (isset($_GET['file'])) ? $_GET['file'] : false;
// split the file name
$names=split("/", $file);
// this is the mainframe, passed to the html form (see bottom)
$mf_name=$names[5];
// this is chamber name (without ".csv") and passed to the html form (see bottom)
$ch_name=substr($names[6],0,-4);
?>

<div class="content">
  
    <div style="display: inline">
        <h3 style="display: inline;">Meteo</h3> &nbsp;&nbsp;&mdash;&nbsp;&nbsp; <?php echo changeModule('monitor') ?> &nbsp;&nbsp;<div id="moduleStatus" style="display: inline; color: red;"></div>
    </div>
    <br /><br /><br /> 
    
    <?php 
    if(!empty($error)) echo '<div class="error">Error: '.$error.'</div>'; 
 
    if(!checkMeteo()) {
        echo '<div class="error">Error: meteo station offline</div>'; 
    }
    if(!checkMainFrame()) {
        echo '<div class="error">Error: mainframe offline</div>'; 
    }
    ?>


<script src="./AMCHARTS/amcharts/amcharts.js" type="text/javascript"></script>
<script src="./AMCHARTS/amcharts/serial.js" type="text/javascript"></script>


    <head>

        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="Author" content="Alessandro Braghieri, INFN-Pavia">
        <meta name="Description" content="Feb 2014, HV and I on line monitor for CMS">

        <title>Current & Voltage plot</title> 

        <script src="./AMCHARTS/amcharts/amcharts.js" type="text/javascript"></script>
	<script src="./AMCHARTS/amcharts/serial.js" type="text/javascript"></script>
        <script type="text/javascript">

        // declaring variables

        var chart;
	var chn;
        var dataProvider;

        // this method called after all page contents are loaded

        window.onload = function() {

// get selected mainframe and chamber name from html form (se bottom)
// NB: the file cannot be loaded form "/home/user/data/stabilitytest/GIF"
// because IT IS NOT inside the root directory of the web server!!!
//
            mfm = document.chname.nsetup.value; 
            chn = document.chname.ch.value; 

            fnam="AMCHARTS/" + mfm + "/" + chn + ".csv";

            createChart();            

// load data from external file
            loadHV(fnam);                                    

        }


        // method which loads external data

        function loadHV(file) {

            if (window.XMLHttpRequest) {

                // IE7+, Firefox, Chrome, Opera, Safari

                var request = new XMLHttpRequest();

            }

            else {

                // code for IE6, IE5

                var request = new ActiveXObject('Microsoft.XMLHTTP');

            }

            // load

            request.open('GET', file, false);

            request.send();

            // check if file exists
            if (request.status !=404) {
                parseHV(request.responseText);
            } else{
                alert("Non-existent Data");
            }
        }


        // method which parses data

        function parseHV(data){ 

            //replace UNIX new lines

            data = data.replace (/\r\n/g, "\n");

            //replace MAC new lines

            data = data.replace (/\r/g, "\n");

            // all the file content is in the string data.
            // split the string into an array

            var rows = data.split("\n");

            // create array which will hold our graph-data:

            dataProvider = [];

            rows.start=0;

	    // set the length
//            rows.length=i+npts;

            // loop through all the selected rows
            for (var i = rows.start; i < rows.length-1; i++){
                // this line helps to skip empty rows
                if (rows[i]) {                    

                    // our fields are separated by space
                    var column = rows[i].split(" ");  
                    // column is array now 

		    // 1st  item is site (GIF)
		    var nsetup=column[0];

                    // 2nd item is date & time (format: YYYYMMDDHHMM)	
                    var ddtt=column[1];
                    // get date: YYYYMMDD
                    var mdate = ddtt.slice(0,8);
                    // get time: HHMM
                    var mtime = ddtt.slice(8);

		    // SPLITTING DATE AND TIME
                    // yyyy
		    var my =ddtt.slice(0,4);
                    // mm
		    var mm =ddtt.slice(4,6);
                    // dd
		    var md =ddtt.slice(6,8);
                    // hh
		    var mh =ddtt.slice(8,10);
                    // mm
		    var mf =ddtt.slice(10,12);

// format time measument
                    var cdate = new Date(my, mm-1, md, mh, mf);
// from the second value check time to discover holes
		    if (i !=rows.start) {
// the difference in ms between actual and previous measurement
		    var tdif=cdate.getTime()-ndate.getTime();

		      if (tdif != 0) {
		        var nadd = Math.floor(tdif/600000);
			var adate = ndate;
// add null measurement
			for (var k = 1; k <= nadd; k++) {
			  var adate = new Date(adate.getTime() + (10 * 60 * 1000));
                          var dataObject = {mdate:cdate};
                          dataProvider.push(dataObject);
			}
  		      }

		    }

		    // 3rd  item is chamber name
		    var chname=column[2];

		    // 4th  item is slot
		    var slot = 1.*column[3];

		    // 5th  item is channel
		    var nchn = 1.*column[4];

		    // 6th  item is HV
		    var vmon = Math.round(column[5]);

		    // 7th  item is Current
		    var imon = Math.round(10*column[6])/10.;

		    // 8th  item is stat register
		    var rstat = column[7];

		    // 9th  item is meteo pressure
		    var meteo_p = Math.round(10*column[8])/10.;

		    // 10th  item is meteo temp
		    var meteo_t = Math.round(10*column[9])/10.;

		    // 11th  item is meteo humidity
		    var meteo_h = Math.round(10*column[10])/10.;
                   // create object which contains all these items:

                    var dataObject = {mdate:cdate, vmon:vmon, imon:imon};

                    // add object to dataProvider array

                    dataProvider.push(dataObject);

// this is the next expected time in ms (=10 min later)
		    var ndate = new Date(cdate.getTime() + (10 * 60 * 1000));

                }

            }

            // set data provider to the chart

            chart.dataProvider = dataProvider;

            // this will force chart to rebuild using new data            

            chart.validateData();

           // pass setup and chamber name to html using a form
//           document.chname.nsetup.value = nsetup
//           document.chname.ch.value = chname
        }

        // method which creates chart

        function createChart(){

            // chart variable is declared in the top

            chart = new AmCharts.AmSerialChart();
            chart.categoryField = "mdate";
//            chart.marginTop = 50;
            chart.addTitle(chn,15);
	    var categoryAxis = chart.categoryAxis;
	    categoryAxis.parseDates = true; 
	    categoryAxis.minPeriod = "mm"; 

            // CURSOR
            chartCursor = new AmCharts.ChartCursor();
            chartCursor.cursorPosition = "mouse";
            chartCursor.categoryBalloonDateFormat = "MMM DD,YYYY HH:MM";
            chart.addChartCursor(chartCursor);

            // key legend
            var legend = new AmCharts.AmLegend();
            legend.markerSize = 5;
            chart.addLegend(legend);

            // first y axis for voltage (on the left)
            var valueAxis1 = new AmCharts.ValueAxis();
            valueAxis1.title = 'Voltage (V)';

            // upper limit for y axis can be set by user
//            y1min = 1 * <?php echo $_POST['y1min']; ?>;
//            y1max = 1 * <?php echo $_POST['y1max']; ?>;
	y1min = 0;
	y1max = 0;
            if (y1min > 0){
              valueAxis1.minimum = y1min;
            }
            if (y1max > 0){
              valueAxis1.maximum = y1max;
            }
            chart.addValueAxis(valueAxis1);

            // second y axis for current (on the right)
            var valueAxis2 = new AmCharts.ValueAxis();
            valueAxis2.position = "right";
            valueAxis2.title = 'Current (uA)';

            // upper limit for y axis can be set by user
//            y2min = 1 * <?php echo $_POST['y2min']; ?>;
//            y2max = 1 * <?php echo $_POST['y2max']; ?>;
	y2min = 0;
	y2max = 0;
            if (y2min > 0){
              valueAxis2.minimum = y2min;
            }
            if (y2max > 0){
              valueAxis2.maximum = y2max;
            }

//            valueAxis2.logarithmic = true;

            valueAxis2.gridAlpha = 0;
            chart.addValueAxis(valueAxis2);

            // 1st graph for voltage

            var graph1 = new AmCharts.AmGraph();
            graph1.valueAxis = valueAxis1;

            graph1.lineThickness = 0;
            graph1.title = "Voltage (V)";
            graph1.valueField = "vmon";
            graph1.bullet = "triangleUp";
            graph1.bulletSize = 4;
            graph1.balloonText = "[[vmon]] V";

            // and add graph to the chart

            chart.addGraph(graph1);            

            // 2nd graph for current

            var graph2 = new AmCharts.AmGraph();
            graph2.valueAxis = valueAxis2;

            graph2.lineThickness = 0;
            graph2.title = "Current (uA)";
            graph2.valueField = "imon";
            graph2.bullet = "triangleDown";
            graph2.bulletSize = 4;
            graph2.balloonText = "[[imon]] uA";

            // and add graph to the chart

            chart.addGraph(graph2);            

            // 'chartdiv' is id of a container 
            // where our chart will be                        

            chart.write('chartdiv');

        }

        </script>

    </head>

    <body>

<!-- Get mainframe and chamber names from php (top) using a hidden form. Need to pass variables to javascript-->
 
      <form name="chname" method="post">
        <input  type="hidden" name="nsetup" value=<?php echo $mf_name; ?> id="n1" size="3">
        <input  type="hidden" name="ch" value=<?php echo $ch_name; ?>  id="n2" size="30">
      </form>

<!-- Place the chart -->
      <div id="chartdiv" style="width:100%; height:400px; background-color:#FFFFFF"></div>

      <br>

<!-- Form to select vertical axis range : ALL THIS PART COMMENTED
      <fieldset style='width: 30%'>
        <legend><b> Select vertical axis range [0=auto] </b></legend>
        <form name='v_axis' method='post' action='./plot_history.php'>

        <table>

          <tr>
            <td> Min Voltage </td>
            <td> <input name='y1min' type='number' value=9200 id='y1min' size=5> </td>
            <td> Max Voltage </td>
            <td> <input name='y1max' type='number' value=9800 id='y1max' size=5> </td>
          </tr>

          <tr>
            <td> Min Current </td>
            <td> <input name='y2min' type='number' value=0 id='y2min' size=5> </td>
            <td> Max Current </td>
            <td> <input name='y2max' type='number' value=10 id='y2max' size=5> </td>
          </tr>

          <tr> 
            <th colspan='4'> <input type='submit' value='PLOT AGAIN'> </th>
          </tr>

          </table>
        </form>


      </fieldset>
-->
