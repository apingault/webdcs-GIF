
#include "../interface/HVscan.hpp"

int main(int argC, char* argv[]) {

    // Get HVscan ID
    if(argC == 2) ID = atoi(argv[1]);
    else if(argC == 3) {
        
        ID = atoi(argv[1]);
        if(atoi(argv[2]) >= 0) HVscan_kill_voltage = atoi(argv[2]);
    }
    else {
        printf("Argument: scan ID\n");
        cout << "EXIT_FAIL" << endl;
        return 0;
    }
    
    
    // Make MYSQL Connection
    db = new MYSQLDb("root", "UserlabGIF++", "localhost", "webdcs"); // webdcs database
    db->connect();
    
    
    // Connect to mainframes
    CAEN1 = new CAEN("CAEN HV", "admin", "admin", "128.141.143.237"); // GIF++ 128.141.77.111 // 904: 128.141.143.237 // SHIP 128.141.143.206
    CAEN1->connect();

    
    // Check if kill
    if(HVscan_kill_voltage > 0 && HVscan_kill_voltage < 10000) {
        
        sql_det = "SELECT d.CAEN_channel, d.CAEN_slot FROM gaps d, hvscan_VOLTAGES v WHERE v.scanid = " + to_string(ID) + " AND v.gapid = d.id AND v.HVPoint = 1";
        res_det = db->query(sql_det);
        while((row = mysql_fetch_row(res_det)) != NULL) {
            
            int slot = atoi(row[1]);
            int ch = atoi(row[0]);
            
            float tmp = (float)HVscan_kill_voltage;
            CAEN1->setvalue("V0Set", ch, slot, tmp); // Set voltage 
            if(HVscan_kill_voltage < 100) CAEN1->setvalue("Pw", ch, slot, 0); // Turn on
        }
        CAEN1->disconnect();
        db->disconnect();
        
        cout << "EXIT_SUCCESS" << endl;
        return 0;
    }
    
    // Update current time stamp
    t = time(NULL); // current time
    
    initialize();
    handleWarning(("Initialize HVSCAN " + to_string(ID)), 10);
    
    // Loop over HV points
    for(int j = 1; j <= maxHVPoints; j++) {
        
        // check system status
        //checkSystemStatus();
        
        handleWarning(("Scanning point HV" + to_string(j)), 10);
        
        // Prepare the main SQL call
        db->connect();
        sql_det = "SELECT d.CAEN_channel, d.CAEN_slot, v.HV, d.name, c.name AS chambername, v.maxtriggers, d.id FROM gaps d, hvscan_VOLTAGES v, chambers c WHERE d.chamberid = c.id AND v.scanid = " + to_string(ID) + " AND v.gapid = d.id AND v.HVPoint = " + to_string(j) + " AND v.masked = 0";
        res_det = db->query(sql_det);
        
        // Check if all channels are masked (i.e. empty result)
        if((mysql_fetch_row(res_det)) == NULL) {

            handleWarning("HV" + to_string(j) + " masked, skip it", 10);
            continue;
        }
        else nGaps = (int)mysql_num_rows(res_det);

        
        string dir = BASEDIR + "/HV" + to_string(j);
        string dir_CAEN = dir + "/CAEN";
        string dir_DAQ = dir + "/DAQ";
        
        //string timeformat = timeFormat("%Y%m%d%H%M%S");
        string basefilename = BASEDIR + "/Scan" + IDSTRING + "_HV" + to_string(j);
        string filename_CAEN = basefilename + "_CAEN.root";
        string filename_DAQ = basefilename + "_DAQ.root";
        if(!HVscan_DAQ) filename_DAQ = ""; // empty if no DAQ scan
        
        // Make directories
        mkdir(dir.c_str(), 0775);
        //system(("chmod 775 " + dir).c_str());
        
        mkdir(dir_CAEN.c_str(), 0775);
        //system(("chmod 775 " + dir_CAEN).c_str());
      
        
        if(typescan.compare("daq") == 0) {
            mkdir(dir_DAQ.c_str(), 0775);
           // system(("chmod 775 " + dir_DAQ).c_str());
        }
       
        	
        // Prepare the ROOT histograms for storage of the CAEN data
        int l=0;
        string histname; 
        //TFile *outputFile = new TFile(filename_CAEN.c_str(), "recreate");
        TH1F *histos[500];
        mysql_data_seek(res_det, 0);
        while((row = mysql_fetch_row(res_det)) != NULL) {

            // HVeff
            histname = "HVeff_" + (string)row[4] + "-" + (string)row[3];
            histos[l] = new TH1F(histname.c_str(), histname.c_str(),1000,0,1);
            histos[l]->SetCanExtend(TH1::kAllAxes);
            l++;
            
            // HVapp
            histname = "HVapp_" + (string)row[4] + "-" + (string)row[3];
            histos[l] = new TH1F(histname.c_str(), histname.c_str(),1000,0,1);
            histos[l]->SetCanExtend(TH1::kAllAxes);
            l++;

            // HVmon
            histname = "HVmon_" + (string)row[4] + "-" + (string)row[3];
            histos[l] = new TH1F(histname.c_str(), histname.c_str(),1000,0,1);
            histos[l]->SetCanExtend(TH1::kAllAxes);
            l++;

            // Imon
            histname = "Imon_" + (string)row[4] + "-" + (string)row[3];
            histos[l] = new TH1F(histname.c_str(), histname.c_str(),1000,0,1);
            histos[l]->SetCanExtend(TH1::kAllAxes);
            l++;
                     
            if(HVscan_DAQ) {
                maxtriggers = atoi(row[5]);
            }
            
            // Set detector status to 1 (i.e. busy)
            sql = "UPDATE gaps SET status = 1 WHERE id = " + (string)row[6];
            db->query(sql);
            
        }
        db->disconnect();

        // If DAQ: generate the ini file
        if(HVscan_DAQ) {
                
            handleWarning("Generate daq.ini file", 10, __LINE__);
            system(("python /home/webdcs/software/webdcs/CAEN/python/generateDAQFiles.py --id " + to_string(ID) + " --daqini --HV " + to_string(j) + " --maxtriggers " + to_string(maxtriggers)).c_str());
            //system(("python /home/webdcs/software/webdcs/CAEN/python/DAQ.py --id " + to_string(ID) + " --reinit").c_str());

            //generateDAQIniFile(ID, j, maxtriggers, beam, runtype, trolleys);
        }

        
        // Turn channels on and set first voltage value 
        handleWarning("Set voltages, ramping...", 10);
        mysql_data_seek(res_det, 0);
        while((row = mysql_fetch_row(res_det)) != NULL) {
			
            int slot = atoi(row[1]);
            int ch = atoi(row[0]);
            float HVeff = atof(row[2]);
            
            CAEN1->setvalue("Pw", ch, slot, 1); // Turn on
            //#warning HV control disabled
            CAEN1->setvalue("V0Set", ch, slot, PTCorrection(HVeff)); // Set voltage 
        }


        // Wait for ramping up is completed (status = 1)
        int ramping = 1;
        while(ramping == 1) {
			
            handleWarning("Channels still ramping", 0);
            sleep(10); // check every 10 seconds
            mysql_data_seek(res_det, 0);
            while((row = mysql_fetch_row(res_det)) != NULL) {

                int slot = atoi(row[1]);
                int ch = atoi(row[0]);
                int status;
                CAEN1->getvalue("Status", ch, slot, status);
                float hvMon, hvSet; 
                CAEN1->getvalue("VMon", ch, slot, hvMon);
                CAEN1->getvalue("V0Set", ch, slot, hvSet);

                if((status == 3 || status == 5) && hvMon != hvSet) {			
                    ramping = 1;
                    break;
                }
                else ramping = 0;
            }
        }

        
        // Sleep for waiting time
        handleWarning("Ramping completed, wait for waiting time...", 10);
        sleep(waiting_time*60);
        handleWarning("Waiting time ended", 10);


        // Check communication with DAQ
        if(HVscan_DAQ) {

            // Update the run file (depends on type of DAQ [explicitly set triggers to -1 to indicate it is the beginning/initialization!]
            system(("python /home/webdcs/software/webdcs/CAEN/python/DAQ.py --id " + to_string(ID) + " --HV " + to_string(j) + " --refreshrun --maxtriggers -1").c_str()); //  > /dev/null 2>&1 &
        
            // Check if DAQ has no error
            readRUN();
            if(RUN.compare("DAQ_ERR") == 0) handleWarning("DAQ error received, stop the HVscan", 40);

            // Wait until DAQ is ready to take data (this is only important for the beginning..)
            handleWarning("Wait until DAQ is ready for data taking", 10);
            int DAQReady = 0;
            while(DAQReady == 0) {
		
                // update run file
                system(("python /home/webdcs/software/webdcs/CAEN/python/DAQ.py --id " + to_string(ID) + " --HV " + to_string(j) + " --refreshrun --maxtriggers -1 > /dev/null 2>&1 &").c_str());
                
                readRUN();
                cout << RUN << endl;
                if(RUN.compare("DAQ_RDY") == 0) {
                    DAQReady = 1;
                    break;
                }
                handleWarning("DAQ not ready...(retry in 5 s)", 0);
                sleep(5);
            }


            // Send START command to RUN file in order to start DAQ
            // At this point we are sure the DAQ is ready to take data!
            handleWarning("Received status READY from DAQ, start data taking", 10);
            handleWarning("Send DAQ START command", 0);
            
            setRUN("START");
            system(("python /home/webdcs/software/webdcs/CAEN/python/DAQ.py --id " + to_string(ID) + " --reinit --HV " + to_string(j) + " --maxtriggers " + to_string(maxtriggers)).c_str());

            
            sleep(3); // sleep in order for the DAQ to respond (i.e. change to RUNNING)
            // while(RUN.compare("RUNNING") == 1) {
            //     sleep(60);
            // }
        }

        // Get initial timestamp
        unsigned int t1 = time(NULL);

        // Start reading the currents
        handleWarning("Reset CAEN connection and start monitoring CAEN parameters", 10);
        
        // Reset CAEN connections --> needed because after long waiting time the CFE can go down
        CAEN1->disconnect();
        
        CAEN1->connect();
        
        int p = 0;
        int run = 1;
        while(run == 1) {
            
            // check system status
            //checkSystemStatus();
            
				
            // Re-apply the voltage
            mysql_data_seek(res_det, 0); // reset SQL pointer
            while((row = mysql_fetch_row(res_det)) != NULL) {
		
                int slot = atoi(row[1]);
                int ch = atoi(row[0]);
                float HVeff = atof(row[2]);
                //#warning HV control disabled
                CAEN1->setvalue("V0Set", ch, slot, PTCorrection(HVeff)); // Set voltage 
            }
				
            // Relax a second before data taking
            //sleep(1);

        
            l=0;
            mysql_data_seek(res_det, 0);
            while((row = mysql_fetch_row(res_det)) != NULL) {

                int slot = atoi(row[1]);
                int ch = atoi(row[0]);
                float HVeff = atof(row[2]);
                float HVmon, HVapp, Imon;
                
                CAEN1->getvalue("VMon", ch, slot, HVmon);
                CAEN1->getvalue("V0Set", ch, slot, HVapp);
                CAEN1->getvalue("IMon", ch, slot, Imon);
                
                histos[l]->Fill(HVeff);
                l++;
                histos[l]->Fill(HVapp);
                l++;
                histos[l]->Fill(HVmon);
                l++;
                histos[l]->Fill(Imon);
                l++;
            }
            
            
            sleep(measure_intval);
            p += measure_intval;
		
            // Re-set the run flag
            if(HVscan_CURRENT) { // CURRENT scan

                if(p < measure_time*60) run = 1;
                else run = 0;
                if (run == 0){
                    const std::string msgCurRun = "Running current scan, time spent = " + to_string(p) + "run Variable = " + to_string(run);
                   handleWarning(msgCurRun, 10);
            }
            }
            else if(HVscan_DAQ) { // DAQ scan --> driven by DAQ program or driven by measure_time
                
                if (run == 0){
                    const std::string msgDaqRun = "Running DAQ scan, time spent = " + to_string(p) + "run Variable = " + to_string(run);
                    handleWarning(msgDaqRun, 10);
                }
                // Update the run file (depends on type of DAQ)
                system(("python /home/webdcs/software/webdcs/CAEN/python/DAQ.py --id " + to_string(ID) + " --HV " + to_string(j) + " --refreshrun --maxtriggers " + to_string(maxtriggers) + " ").c_str()); // > /dev/null 2>&1 &
        
                
                readRUN();   
                if(RUN.compare("DAQ_ERR") == 0) handleWarning("DAQ error received, stop the HVscan", 40);
                else if(RUN.compare("DAQ_PAUSE") == 0) {
                    
                    run = 1;
                    p -= measure_intval; // remvove increment of time
                }
                else if(p < measure_time*60) run = 1; // run on time base
                else if(RUN.compare("RUNNING") == 0) run = 1; // run on DAQ base // added 
                else {
                    //cout << "STOP " << endl;
                    const std::string msgRun = "Stopping the run, time spent = " + to_string(p);
                    handleWarning(msgRun, 10);
                    run = 0;
                    system(("python /home/webdcs/software/webdcs/CAEN/python/DAQ.py --id " + to_string(ID) + " --HV " + to_string(j) + " --stop").c_str()); // > /dev/null 2>&1 &

                }
                if (run == 0){
                    const std::string msgDaqRunCheck = "Running DAQ scan - Checking status - time spent = " + to_string(p) + "run Variable = " + to_string(run);
                    handleWarning(msgDaqRunCheck, 10);
                }
                /*
                if(RUN.compare("RUNNING") == 0) run = 1;
                else if(RUN.compare("DAQ_ERR") == 0) handleWarning("DAQ error received, stop the HVscan", 40);
                else if(p < measure_time*60) run = 1;
                else run = 0;
            */
                //cout << run << " " << RUN << endl;
            }
            if (run == 0)
            {
                const std::string msgCurRun = "Running current scan, time spent = " + to_string(p) + "run Variable = " + to_string(run);
                handleWarning(msgCurRun, 10);
            }
        }
             	
        // Get final time
        unsigned int t2 = time(NULL);

        // Store t1 and t2 in database and update run status
        sql = "UPDATE hvscan_VOLTAGES SET time_start = " + to_string(t1) + ", time_end = "+ to_string(t2) + ", valid = 1 WHERE scanid = " + to_string(ID) +" AND HVPoint = " + to_string(j);
        db->connect();
        db->query(sql);
        db->disconnect();
        
        // Write CAEN histograms to file + plot
        TFile *outputFile = new TFile(filename_CAEN.c_str(), "recreate");
        l=0;
        mysql_data_seek(res_det, 0); // reset SQL pointer
        while((row = mysql_fetch_row(res_det)) != NULL) {
                
            histos[l]->Write(); // HVeff
            l++;
            
            histos[l]->Write(); // HVapp
            l++;
            
            histos[l]->Write(); // HVmon
            l++;
            
            histos[l]->Write(); // IMon
            l++;
        }
        
        outputFile->Close();
        
    }

    if(HVscan_DAQ) {
        
        handleWarning("Send STOP command to DAQ!", 10);
        setRUN("STOP");
        sleep(2);
    }
    
    // DQM
    handleWarning("Run DQM", 10, __LINE__);
    system(("python /home/webdcs/software/webdcs/CAEN/python/DQM.py --id " + to_string(ID) + "  > /dev/null 2>/dev/null &").c_str());
    

    // Lower the HV, depending on the lastHV value
    if(lastHV <= 20) {
        handleWarning("Set low HV on detectors and turn off", 10);
    }
    else if(lastHV == 99999) {
        handleWarning("Keep latest voltages on detectors", 10);
    }
    else {
        mysql_data_seek(res_det, 0); // reset SQL pointer
        db->connect();
        sql = "select value from settings where setting='standby_voltage'";
        db->query(sql);
        row = mysql_fetch_row(res_det);
        int standbyHV = atoi(row[0]);
        handleWarning("Set HV to " + to_string(standbyHV) + " V", 10);
    }

    mysql_data_seek(res_det, 0); // reset SQL pointer
    db->connect();
    while((row = mysql_fetch_row(res_det)) != NULL) {
       
        if(lastHV == 99999) continue;
        
        // Set status to 0
        sql = "UPDATE gaps SET status = 0 WHERE id = " + string(row[6]);
        db->query(sql);
					
        int slot = atoi(row[1]);
        int ch = atoi(row[0]);
        
        //#warning HV control disabled
        float tmp = (float)lastHV; // cast lastHV to float
        CAEN1->setvalue("V0Set", ch, slot, tmp);
        if(lastHV < 20) CAEN1->setvalue("Pw", ch, slot, 0);
    }


    sleep(10);

    
    // Write log entry to SQL database and set status to 0
    sql = "UPDATE hvscan SET status = 0, time_end = " + to_string(time(NULL)) + " WHERE id = " + to_string(ID);
    db->query(sql);

 
    // Disconnect...
    db->disconnect();
    CAEN1->disconnect();

    handleWarning("HVscan successfully ended!", 10);
    
    // Chmod all
    //system(("chmod -R 775 " + BASEDIR).c_str());
    
    cout << "EXIT_SUCCESS" << endl;
    return 0;
}


bool checkSystemStatus() {

    // check DIP server
    sql = "SELECT status FROM PMON WHERE id = 7 LIMIT 1";
    res = db->query(sql);
    row = mysql_fetch_row(res);
    if(atoi(row[0]) >= 20 ) {

        handleWarning("Cannot connect to DIP server, abort scan", 30, __LINE__);
        return false;
    }

    // check operational ranges
    sql = "SELECT status FROM PMON WHERE id = 5 LIMIT 1";
    res = db->query(sql);
    row = mysql_fetch_row(res);
    if(atoi(row[0]) >= 20 ) {

        handleWarning("Operational ranges in ERROR state, abort scan", 30, __LINE__);
        return false;
    }
    return true;
}




void initialize() {

    char tmp[20];
    sprintf(tmp, "%06d", ID);
    IDSTRING = (string)tmp;
    BASEDIR = "/var/operation/HVSCAN/" + IDSTRING;
    RUN_FILE = "/var/operation/RUN/run";
    LOGFILE = BASEDIR + "/log.txt";

    // Get the general scan information
    sql = "SELECT maxHVPoints, waiting_time, type, measure_time, measure_intval, lastHV FROM hvscan WHERE id = " + to_string(ID) + " LIMIT 1";
    res = db->query(sql);
    row = mysql_fetch_row(res);
    if(mysql_num_rows(res) == 0) {
        //handleWarning(("HVscan ID " + to_string(ID) + " not found"), 40, __LINE__); --> dir not ready!
    }
    maxHVPoints = atoi(row[0]);
    waiting_time = atoi(row[1]);
    typescan = row[2];
    measure_time = atoi(row[3]);
    measure_intval = atoi(row[4]);
    lastHV = atoi(row[5]);

    // Make directory
    mkdir(BASEDIR.c_str(), 0775);
    system(("chmod 775 " + BASEDIR).c_str());

    // Touch the log file
    //system(("touch " + LOGFILE + " && chmod 775 " + LOGFILE).c_str());
    
    // Get the specific scan information and prepare variables/directories/...
    if(typescan.compare("current") == 0) {
    
        HVscan_CURRENT = true;
        handleWarning(("Start HVScan CURRENT"), 10, __LINE__);
    }
    else if(typescan.compare("daq") == 0) {
	
        HVscan_DAQ = true;
        //sql = "SELECT type FROM hvscan_DAQ WHERE id = " + to_string(ID) + " LIMIT 1";
        //res = db->query(sql);
        //row = mysql_fetch_row(res);
        //runtype = (string)row[0];
        

        system(("python /home/webdcs/software/webdcs/CAEN/python/DAQ.py --id " + to_string(ID) + " --init  > /dev/null 2>&1 &").c_str());
        
        // Generate mapping
        /*
        handleWarning("Start TDC mapping file", 10, __LINE__);
        system(("python /home/webdcs/software/webdcs/CAEN/python/generateDAQFiles.py --id " + to_string(ID) + " --mapping").c_str());

        // Generate dimensions
        handleWarning("Generate dimensions file", 10, __LINE__);
        system(("python /home/webdcs/software/webdcs/CAEN/python/generateDAQFiles.py --id " + to_string(ID) + " --dimensions").c_str());


        handleWarning("Generate first daq.ini file", 10, __LINE__);
        system(("python /home/webdcs/software/webdcs/CAEN/python/generateDAQFiles.py --id " + to_string(ID) + " --daqini --HV 1 --maxtriggers 5000").c_str());
        */

        // Start DAQ program
        handleWarning("Start HVScan DAQ", 10, __LINE__);
        system(("python /home/webdcs/software/webdcs/CAEN/python/DAQ.py --id " + to_string(ID) + " --start").c_str());
        //system(("/home/daq/software/GIF_DAQ/bin/daq " + LOGFILE + " > /dev/null 2>&1 &").c_str());
    }

    setRUN("INIT"); // WAS START BEFORE, CHECK WITH TDC DAQ
}

void handleWarning(string msg, int loglevel, int line) {

    /* Log level indicator:
     00: display to screen
     10: display to log file
     20: send email/sms to notification_addresses
     30: critical error, abort the program
     */

    string entry, cmd;

    // Parse message
    entry = parseLogEntry(msg, line);

    // Execute log actions
    if(loglevel >= 0) {
        cout << msg << endl;
        fflush(stdout);
    }
    if(loglevel >= 10) {
        logEntry(LOGFILE, entry);
    }
    if(loglevel >= 20) {

    }
    if(loglevel >= 30) {

    }
    if(loglevel >= 40) {


        cout << "EXIT_FAIL" << endl;
        return;
    }
}
