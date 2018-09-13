#ifndef MYSQL_H
#define MYSQL_H

#include <string>
#include <string.h>
#include <stdlib.h>
#include <stdio.h>
#include <iostream>
#include <sys/stat.h>
#include <sys/types.h>
#include <fstream>
#include <time.h>
#include <mysql/mysql.h>
//#include "../interface/utils.hpp"

using namespace std;

class MYSQLDb {
    
    private:
        string username_ = "root";
        string passwd_ = "UserlabGIF++";
        string server_ = "localhost";
        string database_;
        string error_;
        char msg_[1000];
        
        int MAX_ATTEMPTS = 3;
        int ATTEMPTS = 1;
        int ATTEMPTS_CONN = 1;
        MYSQL *conn_;
        
    public:
        MYSQLDb(string database) {
            database_ = database;
        }
        void connect();
        void disconnect();
        MYSQL_RES* query(string query);
};

#endif