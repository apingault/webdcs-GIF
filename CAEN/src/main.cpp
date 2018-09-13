#include <string.h>
#include <stdlib.h>
#include <stdio.h>
#include <time.h>
#include <mysql/mysql.h>
#include <TROOT.h>
#include <string>
#include <iostream>
#include <fstream>
#include <sys/stat.h>
#include <sys/types.h>
#include <TFile.h>
#include <TH1D.h>
#include <TH1F.h>
#include <TCanvas.h>

#include "../interface/main.hpp"
#include "../interface/CAEN.hpp"


using namespace std;

int main(int argC, char* argv[]) {
    
    CAEN *caen1 = new CAEN("admin", "admin", "137.138.119.125", "log");
    caen1->connect();
    



    
    caen1->disconnect();
    
    std::cout << "test" << std::endl;

    return 0;
    
}