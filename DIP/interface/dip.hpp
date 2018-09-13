#ifndef DIP_H
#define DIP_H

#include <string>
#include <string.h>
#include <stdlib.h>
#include <stdio.h>
#include <iostream>
#include <sys/stat.h>
#include <sys/types.h>
#include <fstream>
#include <time.h>
#include <vector>
#include <unistd.h>

#include "../DIPSoftware/include/Dip.h"
#include "../DIPSoftware/include/DipSubscription.h"
#include "../../CORE/interface/MYSQL.hpp"

using namespace std;

string sql;
MYSQL_RES *res, *res1;
MYSQL_ROW row, row1;

MYSQLDb *db;

string getSetting(string setting);

class DIPParameter {
    
    public:
        
        string subscription;
        string table_name;
        vector<string> names;
        vector<string> types;
        vector<string> identifiers;
        vector<double> values;
    
};

vector<DIPParameter> *d = new vector<DIPParameter>;


#endif