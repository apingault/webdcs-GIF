#include "../interface/Longevity.hpp"

int main(int argC, char* argv[]) {

    checkSystemStatus();
    // Get Stability ID
    if(argC != 2) {
        
        cout << "Wrong argument (stability ID)" << endl;
        return 0;
    }
    ID = atoi(argv[1]);
    
    // Make MYSQL Connection
    db = new MYSQLDb("root", "UserlabGIF++", "localhost", "webdcs"); // webdcs database
    db->connect();
    
    // Update current time stamp
    t = time(NULL); // current time
    
    initialize();
    handleWarning(("Initialize longevity run " + to_string(ID)), 10, __LINE__);

    // Connect to mainframes
    CAEN1 = new CAEN("CAEN HV", "admin", "admin", "128.141.77.111");
    CAEN2 = new CAEN("CAEN ADC", "admin", "admin", "10.11.29.14");
    CAEN1->connect();
    CAEN2->connect();   
    
    // Set DIP object and read DIP parameters
    dip = new DIP();
    dip->update();
    

    // Power on detectors and set voltages
    if(dip->SourceON[0] == 0) {
        sql = "SELECT d.CAEN_channel, d.CAEN_slot, v.HV, d.name, d.i0, d.RCURR, d.ADC_slot, d.ADC_channel, d.ADC_resistor, d.id FROM detectors d, stability_VOLTAGES v WHERE v.stabilityid = " + to_string(ID) + " AND v.detectorid = d.id AND v.attU = 000";
    }
    else {
        sql = "SELECT d.CAEN_channel, d.CAEN_slot, v.HV, d.name, d.i0, d.RCURR, d.ADC_slot, d.ADC_channel, d.ADC_resistor, d.id FROM detectors d, stability_VOLTAGES v WHERE v.stabilityid = " + to_string(ID) + " AND v.detectorid = d.id AND v.attU = " + to_string(dip->attU[0]);
    }
    res = db->query(sql);
    
    int i = 0;
    while((row = mysql_fetch_row(res))) {
        
        int slot = atoi(row[1]);
        int ch = atoi(row[0]);
        float HVeff = atof(row[2]);
        float i0 = atof(row[4]);
        float HVapp = PTCorrection(HVeff);
        int status;

        handleWarning("Power on detector " + string(row[3]), 10, __LINE__);
      
        CAEN1->setvalue("Pw", ch, slot, 1);
        CAEN1->setvalue("I0Set", ch, slot, i0);
        CAEN1->setvalue("V0Set", ch, slot, HVapp);
        CAEN1->getvalue("Status", ch, slot, status);
        statusCh[i] = status;

        // Set initial values to zero
        prev_Imon[i] = 0.0;
        prev_IADC[i] = 0.0;
        QInt[i] = 0.0;
        QInt_ADC[i] = 0.0;
        i++;
        
        // Set detector status to 1
        sql = "UPDATE detectors SET status = 1 WHERE id = " + (string)row[9];
        db->query(sql);
    }
    db->disconnect();
    
    handleWarning("Start monitoring", 10, __LINE__);
    //system(("chmod -R 775 " + (string)dir).c_str());
    
    
    // MAIN LOOP
    while(t <= time_max) {
        
        sql = ""; // Reset SQL variable
        
        // --------------------------------
        // Connect to mainframes (or reconnect)
        // --------------------------------
        CAEN1->connect();
        CAEN2->connect();
        
        
        // --------------------------------
        // Update DIP values 
        // --------------------------------
        dip->update();
        
        // --------------------------------
        // Update time step
        // --------------------------------
        prev_t = t;
        t = time(NULL);
        delta_t = t - prev_t;
        db->connect();
        sql = "UPDATE stability SET last_action = '" + to_string(t) + "' WHERE id = " + to_string(ID);
        db->query(sql);
        
        // --------------------------------
        // Check RUN status and select voltages
        // --------------------------------
        RUN_PREV = RUN;
        readRUN();
        if(RUN.compare("END") == 0) {

            handleWarning("Run ended by user", 10, __LINE__);

           // sql = "UPDATE stability SET status = 20 WHERE id = " + to_string(ID);
            //res = db->query(sql);
            //db->disconnect();
            
            break;
        }
        else if(RUN.compare("STANDBY") == 0) {
            
            selAttU = "999"; // 999 is the corresponding STANDBY voltage
            
            if(RUN_PREV.compare("STANDBY") != 0) {
                handleWarning("Run changed to status STANDBY", 10, __LINE__);
            }
        }
        else if(RUN.compare("RUN") == 0) {
            
            if(dip->SourceON[0] != dip->SourceON[1]) { // Trigger on source ON/OFF
             
                handleWarning("Source configuration changed from " + to_string(dip->SourceON[1]) + " to " + to_string(dip->SourceON[0]), 10, __LINE__);
            }
            else if(dip->attU[1] != dip->attU[0]) { // Trigger on attenuator
            
                handleWarning("Upstream attenuator configuration changed from " + to_string(dip->attU[1]) + " to " + to_string(dip->attD[0]), 10, __LINE__);
            }
            
            if(dip->SourceON[0] == 0) selAttU = "000";
            else selAttU = to_string(dip->attU[0]);

            if(RUN_PREV.compare("RUN") != 0) {
                
                handleWarning("Run changed to status RUN", 10, __LINE__);
            }
        }
        else if(RUN.compare("HVSCAN") == 0) {

            if(RUN_PREV.compare("HVSCAN") != 0) {
                
                handleWarning("Run changed to status HVSCAN", 10, __LINE__);
            }
        }

        // --------------------------------
        // Load voltages
        // --------------------------------
        sql = "SELECT d.CAEN_channel, d.CAEN_slot, v.HV, d.name, d.i0, d.RCURR, d.ADC_slot, d.ADC_channel, d.ADC_resistor, d.id FROM detectors d, stability_VOLTAGES v WHERE v.stabilityid = " + to_string(ID) + " AND v.detectorid = d.id AND v.attU = '" + selAttU + "'";
        res = db->query(sql);
        db->disconnect();
        
        
        // --------------------------------
        // Read values from mainframes and re-apply voltage
        // --------------------------------
        int i = 0;
        while((row = mysql_fetch_row(res)) != NULL) {
            
            FILE * File1, * File2;

            int slot = atoi(row[1]);
            int ch = atoi(row[0]);
            float HVeff = atof(row[2]);
            float HVapp = PTCorrection(HVeff);            
            int status;
            float Imon, IADC, HVmon;
            
            // Read values
            CAEN1->getvalue("Status", ch, slot, status);
            CAEN1->getvalue("VMon", ch, slot, HVmon);
            CAEN1->getvalue("IMon", ch, slot, Imon);
            
            
            // ADC (5:d.RCURR, d.ADC_slot, d.ADC_channel, d.ADC_resistor)
            if(atoi(row[5]) == 1) {
                    
                float Vmean;
             
                int slot_ADC = atoi(row[6]); 
                int ch_ADC = atoi(row[7]);
                CAEN2->getvalue("VMean", ch_ADC, slot_ADC, Vmean);
                IADC = 1000*Vmean/atof(row[8]);
            }
            else IADC = 0.0;

            // Re-apply voltage, only when RUN is not equal to HVSCAN
            if(RUN.compare("HVSCAN") != 0) {
                
                if(checkSystemStatus()) CAEN1->setvalue("Pw", ch, slot, 1); // power on always
                else CAEN1->setvalue("Pw", ch, slot, 0); // power OFF in case of problems

                CAEN1->setvalue("V0Set", ch, slot, HVapp);
                
            }
            else { // Else recompute HVeff from VMon
                
                CAEN1->getvalue("V0Set", ch, slot, HVapp);
                HVeff = invPTCorrection(HVapp);  
            }
                

            // Check if TRIP (512: tripped and off, 517: tripped (going down)
            if((status == 512 || status == 517) && statusCh[i] == 1) {

                handleWarning("Channel " + string(row[3]) + " tripped", 20, __LINE__);
            }
            else if(status == 1 && (statusCh[i] == 512 || statusCh[i] == 517)){

                handleWarning("Tripped channel " + string(row[3]) + " restored", 20, __LINE__);
            }
            statusCh[i] = status;

            // Calculate and update integrated charge
            if(status == 1 && dip->SourceON[0] == 1) {
            
                QInt[i] += delta_t*(prev_Imon[i] + Imon)/2.;
                QInt_ADC[i] += delta_t*(prev_IADC[i] + IADC)/2.;
            }
            
            prev_Imon[i] = Imon;
            prev_IADC[i] = IADC;
            
            // Write values to file 
            char filename[200];
            sprintf(filename, "%s/%s.dat", dir.c_str(), row[3]);
            File1 = fopen(filename, "a");
            fprintf(File1, "%d\t%.4f\t%.4f\t%.4f\t%.4f\t%.4f\t%d\t%.4f\t%.4f\t%.4f\t%d\t%d\t%d\n", t, HVeff, HVapp, HVmon, Imon, IADC, status, dip->Pressure[0], dip->Temperature[0], dip->Humidity[0], dip->SourceON[0], dip->attU[0], dip->attD[0]);
            fclose (File1);

            sprintf(filename, "%s/%s.qint", dir.c_str(), row[3]);
            File2 = fopen(filename, "a");
            fprintf(File2, "%d\t%.4f\t%.4f\t%.4f\t%.4f\t%.4f\t%.4f\t%.4f\t%.4f\n", t, Imon, IADC, Imon, IADC, QInt[i], QInt[i], QInt_ADC[i], QInt_ADC[i]);
            fclose (File2);

            i++;
        }
        
        
        
        //system(("chmod -R 775 " + (string)dir).c_str());
        sleep(measure_intval);
    }

    // Set run file to END and power off detectors
    setRUN("END"); 
    powerDown(true, HV_POWER_OFF);
    
    if(t > time_max) {
        handleWarning("Run ended (maximum duration of " + to_string(maxRunDays) + " days reached)", 10, __LINE__);
    }
    
    // Updatate DB: set status to 1 = FINISHED
    db->connect();
    sql = "UPDATE stability SET status = 1 WHERE id = " + to_string(ID);
    res = db->query(sql);
    
    // Set detector status to 1
    sql = "SELECT d.CAEN_channel, d.CAEN_slot, v.HV, d.name, d.i0, d.RCURR, d.ADC_slot, d.ADC_channel, d.ADC_resistor, d.id FROM detectors d, stability_VOLTAGES v WHERE v.stabilityid = " + to_string(ID) + " AND v.detectorid = d.id AND v.attU = 999";
    res = db->query(sql);
    while((row = mysql_fetch_row(res))) {
        
        sql = "UPDATE detectors SET status = 0 WHERE id = " + string(row[9]);
        db->query(sql);
    }
    db->disconnect();
    
    // Close CAEN connections
    CAEN1->disconnect();
    CAEN2->disconnect();
    
    return 0;
}

void initialize() {
    
    char tmp[20];
    sprintf(tmp, "%06d", ID);
    IDSTRING = (string)tmp;
    
    // Get stability program information
    sql = "SELECT time_start FROM stability WHERE id = " + to_string(ID) + " LIMIT 1";
    res = db->query(sql);
    row = mysql_fetch_row(res); 
	
    if(mysql_num_rows(res) == 0) {
        cout << "Wrong argument (stability ID)" << endl;
        exit(0);
    }
    
    time_start = atoi(row[0]);
    time_max = time_start + maxRunDays*24*3600;
    
    dir = "/var/operation/STABILITY/" + IDSTRING;
    mkdir(dir.c_str(), 0775);
    //system(("chmod 775 " + (string)dir).c_str());
    LOGFILE = "/var/operation/STABILITY/" + IDSTRING + "/log.txt";
            
            
    // Set run file to RUN
    setRUN("RUN");
    readRUN();
    
    // Set status to 0 = ONGOING
    sql = "UPDATE stability set status = 0 WHERE id = " + to_string(ID);
    res = db->query(sql);           

      
}

void powerDown(bool off, float HV) {
    
    db->connect();
    sql = "SELECT d.CAEN_channel, d.CAEN_slot, v.HV, d.name, d.i0, d.RCURR, d.ADC_slot, d.ADC_channel, d.ADC_resistor, d.id FROM detectors d, stability_VOLTAGES v WHERE v.stabilityid = " + to_string(ID) + " AND v.detectorid = d.id AND v.attU = 000";
    res = db->query(sql);
    while((row = mysql_fetch_row(res))) {
        
        int slot = atoi(row[1]);
        int ch = atoi(row[0]);

        if(off) CAEN1->setvalue("Pw", ch, slot, 0);
        else CAEN1->setvalue("Pw", ch, slot, 1);
        CAEN1->setvalue("V0Set", ch, slot, HV);
        
    }
    
    db->disconnect();
}

void handleWarning(string msg, int loglevel, int line) {

    /* Log level indicator:
     00: display to screen
     10: display to log file
     20: send email/sms to notification_addresses
     30: critical error, abort the program (needed?)
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
        
        sendMail("WEBDCS GIF++", "entry");
    }
    
    /*
    if(loglevel >= 30) {
        
        entry = parseLogEntry("Go to standby mode", line);
        logEntry(LOGFILE, entry);
        setRUN("STANDBY");
        powerDown(true, HV_STANDBY);
        
        //sprintf(cmd, "source /home/webdcs/software/monitoring/sendMail.sh \"%s\"", entry);
        //system(cmd); 
    }
    if(loglevel >= 40) {
       
        entry = parseLogEntry("Exit stability program, turn off channels", line);
        logEntry(LOGFILE, entry);
        
        //cmd = "source /home/webdcs/software/monitoring/sendMail.sh \"" + msg + "\"";
        //system(cmd.c_str()); 

        powerDown(true, HV_POWER_OFF);
        
        exit(1);
    }
     */
}


// Check if the DIP server and the operational ranges are NOT in error state
bool checkSystemStatus() {
    
    // Store previous values
    PMON_DIP[1] = PMON_DIP[0];
    PMON_OPRANGES[1] = PMON_OPRANGES[0];
    
    PMON_DIP[0] = PMONStatus(7); // DIP SERVER
    PMON_OPRANGES[0] = PMONStatus(5); // OPERATIONAL_RANGES

    if(PMON_DIP[0] >= 20) {
        
        if(PMON_DIP[0] != PMON_DIP[1]) handleWarning("Cannot connect to DIP server, power OFF detectors", 20, __LINE__);
        return false;
    }
    if(PMON_DIP[1] >= 20 and PMON_DIP[0] < 20) {
        handleWarning("DIP server connection restored, power ON detectors", 10, __LINE__);
    }
    
    if(PMON_OPRANGES[0] >= 20) {
        
        if(PMON_OPRANGES[0] != PMON_OPRANGES[1]) handleWarning("Operational ranges error, power OFF detectors", 20, __LINE__);
        return false;
    }
    if(PMON_OPRANGES[1] >= 20 and PMON_OPRANGES[0] < 20) {
        handleWarning("Operational ranges restored, power ON detectors", 10, __LINE__);
    }

    return true;
}
