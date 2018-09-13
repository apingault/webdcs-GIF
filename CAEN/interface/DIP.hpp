#ifndef DIP_H
#define DIP_H


#include "../interface/utils.hpp"
#include "../interface/MYSQL.hpp"
#include <TROOT.h>
#include <TFile.h>
#include <TH1D.h>
#include <TH1F.h>
#include <TCanvas.h>

class DIP {
    
    private:
        
	string hostname = "webdcsdip.cern.ch";
	string user = "root";
	string password = "UserlabDIP++";
	string dbname = "dip";
        
        MYSQLDb *db_ = new MYSQLDb(user, password, hostname, dbname);
        MYSQL_RES *res;
        MYSQL_ROW row;
        string sql;
        int t;
    
    public:
              
        // add here the needed variables
        float Pressure[2], Temperature[2], Humidity[2];
        int SourceON[2], attU[2], attD[2], dipTime[2];
        
        void update();     
};

#endif