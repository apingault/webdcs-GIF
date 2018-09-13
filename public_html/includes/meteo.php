<?php
// 5 mar 2014. Alessandro Braghieri
// added readout for both meteo stations
//
?>

<div class="content">
  
    <div style="display: inline">
        <h3 style="display: inline;">Meteo</h3> &nbsp;&nbsp;&mdash;&nbsp;&nbsp; <?php echo changeModule('meteo') ?> &nbsp;&nbsp;<div id="moduleStatus" style="display: inline; color: red;"></div>
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


<link rel="AMCHARTS/images/style.css" type="text/css">
<script src="./AMCHARTS/amcharts/amcharts.js" type="text/javascript"></script>
<script src="./AMCHARTS/amcharts/serial.js" type="text/javascript"></script>
<script type="text/javascript">

    // declaring variables
    var dataProvider;

    // this method called after all page contents are loaded
    window.onload = function() {

	// get mainframe name (GIF or CAEN904-2)from the html form
	var e = document.getElementById("changeCurrentMid");
	var mfname = e.options[e.selectedIndex].text;

        // pass name to html using a form
//        document.meteo.system.value = mfname;

        createChart(mfname);            

        // load data from external files
	fnam1="./online/meteo" +  mfname + ".dat";

        loadHV(fnam1,mfname);

       }


       // method which loads external data
       function loadHV(file1,mfname) {

           if (window.XMLHttpRequest) {
               // IE7+, Firefox, Chrome, Opera, Safari
               var request1 = new XMLHttpRequest();
           }
           else {
               // code for IE6, IE5
               var request1 = new ActiveXObject('Microsoft.XMLHTTP');
           }
           // load
           request1.open('GET', file1, false);
           request1.send();

           // check if file exists
           if (request1.status !=404) {
               parseHV(request1.responseText,mfname);
           } else{
               alert("Non-existent Data");
           }
       }


       // method which parses data
       function parseHV(data1,mfname){ 

           //replace UNIX new lines

           data1 = data1.replace (/\r\n/g, "\n");

           //replace MAC new lines

           data1 = data1.replace (/\r/g, "\n");

           // all the file content is in the string data.
           // split the string into an array

           var rows  = data1.split("\n");

           // create array which will hold our graph-data:

           dataProvider = [];

           rows.start=1;

        // set the length
        // rows.length=i+npts;

           // loop through all the selected rows
           for (var i = rows.start; i < rows.length-1; i++){
               // this line helps to skip empty rows
               if (rows[i]) {                    
                 // our columns are separated by comma

                 var column = rows[i].split(",");  
                 // column is array now 

	         // 1st  item is date
	         var mdate=column[0];

	         // 2nd item is time
	         var mtime=column[1];

        	 // extract year, month, day
	         var my =mdate.slice(0,4);
		 var mm =mdate.slice(5,7);
        	 var md =mdate.slice(8,10);

	         var mh =mtime.slice(0,2);
        	 var mf =mtime.slice(3,5);

	         // format time measument
	         var cdate = new Date(my, mm-1, md, mh, mf);
        
	         // from the second value check time to discover holes
        	 if (i !=rows.start) {
        	   // the difference in ms between actual and previous measurement
	           var tdif=cdate.getTime()-ndate.getTime();

	           // measurement every hour (3.600.000 ms)
        	   if (tdif != 0) {
            	     var nadd = Math.floor(tdif/3600000);
            	     var adate = ndate;
            
 	             // add null measurement
          	     for (var k = 1; k <= nadd; k++) {
                	var adate = new Date(adate.getTime() + (60 * 60 * 1000));
                	var dataObject = {mdate:cdate};
                	dataProvider.push(dataObject);
            	     }
        	   }

	         }

		 // 3th  item is temperature
		 var temp = 1.*column[2];

		 // 4th  item is humidity
		 var humd = 1.*column[3];

		 // 5th  item is pressure
		 var press = 1.*column[4];
                 // create object which contains all these items:

                    // for gif meteo statio also plot base data
                    if (mfname == "GIF") {
                      // 6th  item is temp of base station
                      var tc_base = 1.*column[5];
                      // 7th  item is humidity of base station
                      var rh_base = 1.*column[6];
                      var dataObject = {mdate:cdate, vmon:temp, imon:humd, press:press, tc0:tc_base, rh0:rh_base};
                    } else {
                      var dataObject = {mdate:cdate, vmon:temp, imon:humd, press:press};
                    }

                 // add object to dataProvider array

                 dataProvider.push(dataObject);

		 // this is the next expected time
		 var ndate = new Date(cdate.getTime() + (60 * 60 * 1000));

               }

           }

           // set data provider to the chart

           chart1.dataProvider = dataProvider;
           chart2.dataProvider = dataProvider;

           // this will force chart to rebuild using new data            

           chart1.validateData();
           chart2.validateData();

       }

       // method which creates chart

       function createChart(mfname){

	   // 1st chart: temp & hum

           chart1 = new AmCharts.AmSerialChart();
	   chart1.pathToImages ="./AMCHARTS/amcharts/images/";
           chart1.categoryField = "mdate";
//           chart1.marginTop = 50;
	   var categoryAxis = chart1.categoryAxis;
	   categoryAxis.parseDates = true; 
	   categoryAxis.minPeriod = "mm"; 

           // CURSOR
           chartCursor = new AmCharts.ChartCursor();
           chartCursor.cursorPosition = "mouse";
	   chartCursor.categoryBalloonDateFormat = "MMM DD,YYYY HH:NN";
           chart1.addChartCursor(chartCursor);

	   // SCROLLBAR 
	   var chartScrollbar = new AmCharts.ChartScrollbar(); 
	   chart1.addChartScrollbar(chartScrollbar);

           // key legend
           var legend = new AmCharts.AmLegend();
           legend.markerSize = 5;
           chart1.addLegend(legend);

           // first y axis for temp (on the left)
           var valueAxis1 = new AmCharts.ValueAxis();
           valueAxis1.title = 'Temp (Celsius)';
           valueAxis1.minimum =  0;
           valueAxis1.maximum = 30;
           chart1.addValueAxis(valueAxis1);

           // second y axis for hum (on the right)
           var valueAxis2 = new AmCharts.ValueAxis();
           valueAxis2.position = "right";
           valueAxis2.title = 'Humidity (%)';
           valueAxis2.minimum =  0;
           valueAxis2.maximum = 80;

           valueAxis2.gridAlpha = 0;
           chart1.addValueAxis(valueAxis2);

           // 1st graph for Temp

           var graph1 = new AmCharts.AmGraph();
           graph1.valueAxis = valueAxis1;

           graph1.lineThickness = 0;
           graph1.title = "Temp (C)";
           graph1.valueField = "vmon";
           graph1.bullet = "triangleUp";
           graph1.bulletSize = 4;
           graph1.balloonText = "RPC: T=[[vmon]] C";

           // and add graph to the chart

           chart1.addGraph(graph1);            

           // for gif meteo statio also plot base data
           if (mfname == "GIF") {
             var graph1b = new AmCharts.AmGraph();
             graph1b.valueAxis = valueAxis1;

             graph1b.lineThickness = 0;
             graph1b.title = "base Temp (C)";
             graph1b.valueField = "tc0";
             graph1b.bullet = "square";
             graph1b.bulletSize = 4;
             graph1b.balloonText = "base: T=[[tc0]] C";

             // and add graph to the chart
             chart1.addGraph(graph1b);            
           }

           // 2nd graph for Humidity

           var graph2 = new AmCharts.AmGraph();
           graph2.valueAxis = valueAxis2;

           graph2.lineThickness = 0;
           graph2.title = "Hum. (%)";
           graph2.valueField = "imon";
           graph2.bullet = "triangleDown";
           graph2.bulletSize = 4;
           graph2.balloonText = "RPC: RH=[[imon]] %";

           // and add graph to the chart

           chart1.addGraph(graph2);            

           // for gif meteo statio also plot base data
           if (mfname == "GIF") {
             var graph2b = new AmCharts.AmGraph();
             graph2b.valueAxis = valueAxis2;

             graph2b.lineThickness = 0;
             graph2b.title = "base Hum. (%)";
             graph2b.valueField = "rh0";
             graph2b.bullet = "square";
             graph2b.bulletSize = 4;
             graph2b.balloonText = "base: RH=[[rh0]] %";

             // and add graph to the chart
             chart1.addGraph(graph2b);
           }

           // 'chartdiv' is id of a container where our chart will be                        

           chart1.write('chartdiv1');

	   // 2nd chart: pressure

           chart2 = new AmCharts.AmSerialChart();
	   chart2.pathToImages ="./AMCHARTS/amcharts/images/";
           chart2.categoryField = "mdate";
//           chart2.marginTop = 50;
	   var categoryAxis = chart2.categoryAxis;
	   categoryAxis.parseDates = true; 
	   categoryAxis.minPeriod = "mm"; 

           // CURSOR
           chartCursor = new AmCharts.ChartCursor();
           chartCursor.cursorPosition = "mouse";
	   chartCursor.categoryBalloonDateFormat = "MMM DD,YYYY HH:NN";
           chart2.addChartCursor(chartCursor);

	   // SCROLLBAR 
	   var chartScrollbar = new AmCharts.ChartScrollbar(); 
	   chart2.addChartScrollbar(chartScrollbar);

           // key legend
           var legend = new AmCharts.AmLegend();
           legend.markerSize = 5;
           chart2.addLegend(legend);

           // first y axis for pressure (on the left)
           var valueAxis1 = new AmCharts.ValueAxis();
           valueAxis1.title = 'Pressure (mbar)';
           valueAxis1.minimum =  900;
           valueAxis1.maximum = 1000;
           chart2.addValueAxis(valueAxis1);

           // same  y axis on the rigth
           var valueAxis2 = new AmCharts.ValueAxis();
           valueAxis2.position = "right";
           valueAxis2.title = 'Pressure (mbar)';
//            valueAxis2.title = '';
           valueAxis2.minimum =  900;
           valueAxis2.maximum = 1000;
           chart2.addValueAxis(valueAxis2);

           // 3rd graph for pressure

           var graph3 = new AmCharts.AmGraph();
           graph3.valueAxis = valueAxis1;

           graph3.lineThickness = 0;
           graph3.title = "Pressure (mbar)";
           graph3.valueField = "press";
           graph3.bullet = "round";
           graph3.bulletSize = 4;
           graph3.balloonText = "Baro=[[press]] mbar";

           // and add graph to the chart

           chart2.addGraph(graph3);            

           // duplicate for rigth axis

//           var graph3 = new AmCharts.AmGraph();
//           graph3.valueAxis = valueAxis2;
//           graph3.lineThickness = 0;
//           graph3.title = "Pressure (mbar)";
//           graph3.valueField = "press";
//            graph3.bullet = "";
//           chart2.addGraph(graph3);            

           // 'chartdiv' is id of a container where our chart will be                        

           chart2.write('chartdiv2');

       }

       </script>

     <form name='meteo' method='get' action=''>
        <input type='hidden' name='system' size='8'>
     </form>

     <div id="chartdiv1" style="width: 98%; height: 350px; float: left;"></div>
     <div id="chartdiv2" style="width: 98%; height: 350px; float: left;"></div>
   
</div>

