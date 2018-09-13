
#include "../interface/utils.hpp"
#include "../interface/MYSQL.hpp"


void sendMail(string sub, string msg, string rec) {
    
    //system("php -r 'mail(\"\", \"\", \"\", \"\"));'");
    
}



string parseLogEntry(string entry, int line) {
    
    time_t timer;
    char buffer[26];
    struct tm* tm_info;
    char msg[1000];
    
    // Make time format
    time(&timer);
    tm_info = localtime(&timer);
    strftime(buffer, 26, "%Y-%m-%d.%H:%M:%S", tm_info);
    
    sprintf(msg, "%s.[%s][%d] %s\n", buffer, PROGRAM.c_str(), line, entry.c_str());
    return string(msg);
}

void logEntry(string file, string msg) {

    FILE *log;
    log = fopen(file.c_str(), "a");
    fprintf(log, "%s", msg.c_str());
    fflush(log);
    fclose(log);
    fflush(stdout);
}

void readRUN() {

    FILE *fp;
    fp = fopen (RUN_FILE.c_str(), "r");
    if(fp  != NULL) {
        char tmp[30];
        fscanf(fp, "%s", tmp);
        RUN = (string)tmp;
        fclose(fp);
    }
}

void setRUN(string msg) {

    FILE *run;
    run = fopen(RUN_FILE.c_str(), "w");
    fprintf(run, "%s", msg.c_str());
    fflush(run);
    fclose(run);
}


string timeFormat(string format, int timestamp) {
    
    #warning Need to convert time to time_t

    time_t t = time(0);
    tm now = *localtime(&t);
    char tmdescr[200] = {0};
    strftime(tmdescr, 200, format.c_str(), &now);
    return (string)tmdescr;
}


float beta() {
    
    ifstream infile("/var/operation/RUN/PTcorr");
    if (infile.good()) {
        string sLine;
        getline(infile, sLine);
        return stof(sLine);
    }
    infile.close();
}

float PTCorrection(float HVeff) { 
    return HVeff * beta(); 
}

float invPTCorrection(float HVeff) { 
    return HVeff / beta();
}
void sendMail(string subj, string msg) {
    
    system(("php /home/webdcs/software/webdcs/CORE/php/sendMail.php '" + subj + "' '" + msg + "'").c_str());
}

int PMONStatus(int id) {
    
    MYSQLDb *db;
    MYSQL_RES *res;
    MYSQL_ROW row;
    int status = -1;
    
    db = new MYSQLDb("root", "UserlabGIF++", "localhost", "webdcs"); // webdcs database
    db->connect();
    res = db->query("SELECT status FROM PMON WHERE id = " + to_string(id) + " LIMIT 1");
    if(mysql_num_rows(res) != 0) {
        
        row = mysql_fetch_row(res);
        status = atoi(row[0]);
    } 
    db->disconnect();

    delete db, res, row;
    return status;
}


