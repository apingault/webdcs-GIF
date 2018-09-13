#ifndef MYSQL_H
#define MYSQL_H

#include <mysql/mysql.h>
#include "../interface/utils.hpp"

class MYSQLDb {
    
    private:
        string username_;
        string passwd_;
        string server_;
        string database_;
        string error_;
        char msg_[1000];
        
        int MAX_ATTEMPTS = 3;
        int ATTEMPTS = 1;
        int ATTEMPTS_CONN = 1;
        MYSQL *conn_;
        
    public:
        MYSQLDb(string username, string passwd, string server, string database) {
            username_ = username;
            passwd_ = passwd;
            server_ = server;
            database_ = database;
        }
        void connect();
        void disconnect();
        MYSQL_RES* query(string query);
};

#endif