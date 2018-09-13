#ifndef HVSCAN_H
#define HVSCAN_H

#include "../interface/utils.hpp"
#include "../interface/MYSQL.hpp"
#include "../interface/CAEN.hpp"


#include <TROOT.h>
#include <TFile.h>
#include <TH1D.h>
#include <TH1F.h>
#include <TCanvas.h>

#define MAX_DETECTORS  100

string PROGRAM = "HVscan";
string LOGFILE;

// Define variables
int ID, time_start;
string IDSTRING, dir, sql, sql_det;
string selAttU; // selection of attU in the database
MYSQLDb *db;
MYSQL_RES *res, *res_det;
MYSQL_ROW row;
CAEN *CAEN1, *CAEN2;




bool HVscan_DAQ = false;
bool HVscan_CURRENT = false;
int HVscan_kill_voltage = -1; // By default: equal to minus one

string cmd;
string RUN, RUN_FILE;

string BASEDIR;
int lastHV;
unsigned int t;

int maxHVPoints, waiting_time, measure_time, measure_intval, beam, nGaps, maxtriggers = 10000, ramping = 1;
string typescan, runtype, trolleys;

void HVSCANWarning(string msg, int loglevel, int line = 0);
void generateDAQIniFile(int ID, int HV, int MT, int beam, string runtype, string trolleys);
void initialize();
bool checkSystemStatus();



void handleWarning(string msg, int loglevel, int line = 0);

#endif