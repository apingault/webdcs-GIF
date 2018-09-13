#ifndef LONGEVITY_H
#define LONGEVITY_H

#include "../interface/utils.hpp"
#include "../interface/MYSQL.hpp"
#include "../interface/CAEN.hpp"
#include "../interface/DIP.hpp"

#define MAX_DETECTORS  100

// Define variables
int ID, time_start;
string IDSTRING, dir, logfile, sql;
string selAttU; // selection of attU in the database
MYSQLDb *db;
MYSQL_RES *res;
MYSQL_ROW row;
CAEN *CAEN1, *CAEN2;
DIP *dip;

string PROGRAM = "Longevity";
string LOGFILE;

float QInt[MAX_DETECTORS];
float QInt_ADC[MAX_DETECTORS];
float prev_Imon[MAX_DETECTORS];
float prev_IADC[MAX_DETECTORS];
int delta_t, statusCh[MAX_DETECTORS];
unsigned t, prev_t, time_max; // current time

int maxRunDays = 7; // max run duration (in days)
int measure_intval = 20;
float HV_POWER_OFF = 20.;
float HV_STANDBY = 20.;
string RUN_FILE = "/var/operation/RUN_STABILITY/run";
string RUN = "";
string RUN_PREV = "";

int PMON_DIP[2];
int PMON_OPRANGES[2];




void initialize();
void powerDown(bool off = false, float HV = HV_STANDBY);
void handleWarning(string msg, int loglevel, int line = 0);
bool checkSystemStatus();


#endif