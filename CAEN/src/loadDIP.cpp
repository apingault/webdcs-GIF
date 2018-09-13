
#include "../interface/loadDIP.hpp"

void LoadDIP::readDIP() {

    int i=0;
    unsigned int t;
    t = time(NULL);
    
    // Store previous values
    Pressure[1] = Pressure[0];
    Temperature[1] = Temperature[0];
    Humidity[1] = Humidity[0];
    SourceON[1] = SourceON[0];
    attU[1] = attU[0];
    attD[1] = attD[0];
    C2H2F4[1] = C2H2F4[0];
    iC4H10[1] = iC4H10[0];
    SF6[1] = SF6[0];
    
    ifstream file(dipFile_.c_str());
    string str; 
    while(getline(file, str)) {
        
        if(i==0) dipTime = atoi(str.c_str()); // time
        else if(i==1) Pressure = atof(str.c_str()); // pressure
        else if(i==2) Temperature = atof(str.c_str()); // temp
        else if(i==3) Humidity = atof(str.c_str()); // humidity
        else if(i==4) SourceON = atoi(str.c_str()); // sourceON
        else if(i==5) attU = atoi(str.c_str()); // attenuator upstream
        else if(i==6) attD = atoi(str.c_str()); // attenuator upstream
        else if(i==7) C2H2F4 = atof(str.c_str()); // C2H2F4
        else if(i==8) iC4H10 = atof(str.c_str()); // iC4H10
        else if(i==9) SF6 = atof(str.c_str()); // SF6
        i++;
    }

    file.close();
    
    /*
    // Do check on time --> error if larger than 
    if((t - dipTime) > 900) { // abort program if difference larger than 15 min
        //sprintf(msg, "DIP values not updated for more than 15 min (last value at %d)", dipTime);
       // handleWarning(msg, 30, __LINE__);
    }
    else if((t - dipTime) > 600) { // warning message if difference larger than 5 min
        //sprintf(msg, "DIP values not updated for more than 5 min (last value at %d)", dipTime);
        handleWarning(msg, 20, __LINE__);
    }
    
    // Do check on PT
    if(Pressure > 1100 || Pressure < 900) {
        
        if(prevPressure > 1100 || prevPressure < 900) { // problem already known --> check time
            
        }
        else { // newly triggered --> set time
            
            sprintf(msg, "Pressure value out of range: %f", Pressure);
            handleWarning(msg, 20, __LINE__);
        }
        
        sprintf(msg, "Pressure value out of range: %f", Pressure);
        handleWarning(msg, 30, __LINE__);
    }
    if(Temperature > 50 || Temperature < 15) {
        sprintf(msg, "Temperature value out of range: %f", Temperature);
        handleWarning(msg, 30, __LINE__);
    }
    
    // Do check on Gas
    
    if(C2H2F4 < 0.001 || iC4H10 < 0.001 || SF6 < 0.001) {
        sprintf(msg, "Gas flow(s) too low (C2H2F4: %f l/h, iC4H10: %f l/h, SF6: %f l/h)", C2H2F4, iC4H10, SF6);
        //handleWarning(msg, 30, __LINE__);
    }
    */
    
}