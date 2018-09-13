
#include "../interface/DIP.hpp"


void DIP::update() {

    // Store previous values
    dipTime[1] = dipTime[0];
    Pressure[1] = Pressure[0];
    Temperature[1] = Temperature[0];
    Humidity[1] = Humidity[0];
    SourceON[1] = SourceON[0];
    attU[1] = attU[0];
    attD[1] = attD[0];  
    
    db_->connect();
    
    
    sql = "SELECT timestamp, P, TIN, RHIN FROM environmental ORDER BY timestamp DESC LIMIT 1";
    res = db_->query(sql);
    row = mysql_fetch_row(res);
    dipTime[0] = atof(row[0]);
    Pressure[0] = atof(row[1]);
    Temperature[0] = atof(row[2]);
    Humidity[0] = atof(row[3]);
    
    sql = "SELECT SourceON FROM source ORDER BY timestamp DESC LIMIT 1";
    res = db_->query(sql);
    row = mysql_fetch_row(res);
    SourceON[0] = atoi(row[0]);
    
    sql = "SELECT AttUA, AttUB, AttUC, AttDA, AttDB, AttDC  FROM attenuator ORDER BY timestamp DESC LIMIT 1";
    res = db_->query(sql);
    row = mysql_fetch_row(res);
    attU[0] = atoi(((string)row[0] + (string)row[1] + (string)row[2]).c_str());
    attD[0] = atoi(((string)row[3] + (string)row[4] + (string)row[5]).c_str());
    
    db_->disconnect();
}

