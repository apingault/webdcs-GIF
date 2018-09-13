#ifndef CAEN_H
#define CAEN_H

#include "../interface/CAENHVWrapper.h"
#include "../interface/utils.hpp"

class CAEN {
    
    private:
        string name_;
        string username_;
        string passwd_;
        string address_;
        string error_;
        char msg_[1000];
        
        int sysHndl_ = -1;
        int lk = LINKTYPE_TCPIP;
        int CAEN_CONN = 0;
        int MAX_ATTEMPTS = 3;
        int ATTEMPTS = 0;
        int ATTEMPTS_CONN = 1;
        
    public:
        CAEN(string name, string username, string passwd, string address) {
            
            name_ = name;
            username_ = username;
            passwd_ = passwd;
            address_ = address;
        };
        void connect();
        void disconnect();
        void setvalue(string param, int ch, int slot, int value);
        void setvalue(string param, int ch, int slot, float value);
        void getvalue(string param, int ch, int slot, int &value);
        void getvalue(string param, int ch, int slot, float &value);
        int getSysHndl() {
            return sysHndl_;
        }
};


#endif