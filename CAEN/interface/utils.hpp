#ifndef UTILS_H
#define UTILS_H

#include <string>
#include <string.h>
#include <stdlib.h>
#include <stdio.h>
#include <iostream>
#include <sys/stat.h>
#include <sys/types.h>
#include <fstream>
#include <time.h>

using namespace std;

extern string RUN_FILE;
extern string RUN;
extern string PROGRAM;
extern string LOGFILE;


// PT correction
float beta();
float PTCorrection(float HVeff);
float invPTCorrection(float HVeff);




string parseLogEntry(string entry, int line);
void logEntry(string file, string msg);

extern void handleWarning(string msg, int loglevel, int line);


void readRUN();
void setRUN(string msg);

string timeFormat(string format, int timestamp = 0);
void sendMail(string subj, string msg);
int PMONStatus(int id);



#endif