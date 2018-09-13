
#include "../interface/MYSQL.hpp"

void MYSQLDb::connect() {

    if(ATTEMPTS_CONN > MAX_ATTEMPTS) {

        sprintf(msg_, "Cannot connect to the MYSQL database after %d attempts", MAX_ATTEMPTS);
        cout << msg_ << endl;
        //handleWarning(msg_, 40, __LINE__);
    }
    else {

        sprintf(msg_, "Try to connect to MYSQL database (%d/%d)", ATTEMPTS_CONN, MAX_ATTEMPTS);
        cout << msg_ << endl;
        //handleWarning(msg_, 0, __LINE__);

        conn_ = mysql_init(NULL);
        if(!mysql_real_connect(conn_, server_.c_str(), username_.c_str(), passwd_.c_str(), database_.c_str(), 0, NULL, 0)) {

            sprintf(msg_, "Database connection error: %s", mysql_error(conn_));
            cout << msg_ << endl;
            //handleWarning(msg_, 10, __LINE__);

            ATTEMPTS_CONN = ATTEMPTS_CONN+1;
            connect(); // Try again
        }
        else {
            ATTEMPTS_CONN = 0;
            sprintf(msg_, "Connected to database");
            cout << msg_ << endl;
            //handleWarning(msg_, 0, __LINE__);
        }
    }
}
    
void MYSQLDb::disconnect() {
        
    mysql_close(conn_);
    sprintf(msg_, "Database disconnected");
    cout << msg_ << endl;
    //handleWarning(msg_, 0, __LINE__);
}
    
MYSQL_RES* MYSQLDb::query(string query) {
        
    if(mysql_query(conn_, query.c_str())) {

        sprintf(msg_, "Query error: %s", mysql_error(conn_));
        
        //handleWarning(msg_, 10, __LINE__);
    }
    return mysql_store_result(conn_);
        
}
