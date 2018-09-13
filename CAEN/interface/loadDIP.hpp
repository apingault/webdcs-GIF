#ifndef LOADDIP_H
#define LOADDIP_H


#include "../interface/utils.hpp"

class LoadDIP {
    
    private:
        string dipFile_ = "/var/operation/RUN/pt";
        int t;
    
    public:
              
        float Pressure[2], Temperature[2], Humidity[2];
        int SourceON[2], attU[2], attD[2], dipTime[2];
        float C2H2F4[2], iC4H10[2], SF6[2];
        
        void loadDIP(string dipFile) {

            dipFile_ = dipFile;
        }
        void readDIP();
        
};

#endif