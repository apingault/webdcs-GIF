
#include "../interface/CAEN.hpp"


void CAEN::connect() {
        
    if(ATTEMPTS_CONN > MAX_ATTEMPTS) {

        sprintf(msg_, "Cannot connect to mainframe %s after %d attempts", name_.c_str(), MAX_ATTEMPTS);
        handleWarning(msg_, 40, __LINE__);
    }
    else {

        sprintf(msg_, "Try to connect to mainframe %s (%d/%d)", name_.c_str(), ATTEMPTS_CONN, MAX_ATTEMPTS);
        handleWarning(msg_, 0, __LINE__);

        int sysHndl = -1;
        CAENHVRESULT ret = -1;
        ret = CAENHV_InitSystem((CAENHV_SYSTEM_TYPE_t)0, lk, (char *)address_.c_str(), username_.c_str(), passwd_.c_str(), &sysHndl);
        if(ret == CAENHV_OK) {

            // Store connection handlers
            sysHndl_ = sysHndl;
            
            sprintf(msg_, "Mainframe %s connection established (sysHndl %d)", name_.c_str(), sysHndl_);
            handleWarning(msg_, 0, __LINE__);
            CAEN_CONN = 1; // Set flag
            ATTEMPTS_CONN = 1;
        }
        else if(ret == CAENHV_DEVALREADYOPEN) { // Both connections were already open

            sprintf(msg_, "Mainframe %s connection already established", name_.c_str());
            handleWarning(msg_, 0, __LINE__);
            CAEN_CONN = 1;  // Set flag
            ATTEMPTS_CONN = 1;
        }
        else { // Problem with connection, close and try again

            disconnect();

            sleep(1);
            ATTEMPTS_CONN = ATTEMPTS_CONN+1;
            CAEN_CONN = 0;
            connect();
        }
    }
}
    

void CAEN::disconnect() {
    
    sprintf(msg_, "Close mainframe %s connection (sysHndl %d)", name_.c_str(), sysHndl_);
    handleWarning(msg_, 0, __LINE__);
    
    
    CAENHVRESULT ret = -1;
    /*/
    int sysHndl = -1;
    ret = CAENHV_InitSystem((CAENHV_SYSTEM_TYPE_t)0, lk, (char *)address_.c_str(), username_.c_str(), passwd_.c_str(), &sysHndl);
    if(ret == CAENHV_DEVALREADYOPEN) CAENHV_DeinitSystem(sysHndl_);
     */
    ret = CAENHV_DeinitSystem(sysHndl_);
}

// Set integer value
void CAEN::setvalue(string param, int ch, int slot, int value) {

    if(ATTEMPTS > MAX_ATTEMPTS) {
        
        sprintf(msg_, "Cannot set %s after %d attempts", param.c_str(), MAX_ATTEMPTS);
        handleWarning(msg_, 30, __LINE__);
    }
    else {

        unsigned short *ChList;
        ChList = (unsigned short*)malloc(sizeof(unsigned short));
        ChList[0] = ch;

        int *HVCAEN = NULL;
        HVCAEN = (int*)malloc(sizeof(int));
        HVCAEN[0] = value;
        CAENHVRESULT ret = -1;
        
        ret = CAENHV_SetChParam(sysHndl_, slot, param.c_str(), 1, ChList, HVCAEN);
        if(ret != CAENHV_OK) {

            sprintf(msg_, "CAEN error: num. %d", ret);
            handleWarning(msg_, 10, __LINE__);
            disconnect();
            
            ATTEMPTS = ATTEMPTS+1;
            connect();
            setvalue(param, ch, slot, value);
        }
        else ATTEMPTS = 0;
     }   
}


// Set float value
void CAEN::setvalue(string param, int ch, int slot, float value) {

    if(ATTEMPTS > MAX_ATTEMPTS) {
        
        sprintf(msg_, "Cannot set %s after %d attempts", param.c_str(), MAX_ATTEMPTS);
        handleWarning(msg_, 30, __LINE__);
    }
    else {

        unsigned short *ChList;
        ChList = (unsigned short*)malloc(sizeof(unsigned short));
        ChList[0] = ch;

        float *HVCAEN = NULL;
        HVCAEN = (float*)malloc(sizeof(float));
        HVCAEN[0] = value;
        CAENHVRESULT ret = -1;
        
        ret = CAENHV_SetChParam(sysHndl_, slot, param.c_str(), 1, ChList, HVCAEN);
        if(ret != CAENHV_OK) {

            sprintf(msg_, "CAEN error: num. %d", ret);
            handleWarning(msg_, 10, __LINE__);
            disconnect();
            
            ATTEMPTS = ATTEMPTS+1;
            connect();
            setvalue(param, ch, slot, value);
        }
        else ATTEMPTS = 0;
     }   
}
    
    
void CAEN::getvalue(string param, int ch, int slot, int &value) {
    
    if(ATTEMPTS > MAX_ATTEMPTS) {

        sprintf(msg_, "Cannot get %s after %d attempts", param.c_str(), MAX_ATTEMPTS);
        handleWarning(msg_, 30, __LINE__);
    }
    else {

        unsigned short *ChList;
        ChList = (unsigned short*)malloc(sizeof(unsigned short));
        ChList[0] = ch;
        
        int *lParValList = NULL;
        lParValList = (int*)malloc(sizeof(int));
        CAENHVRESULT ret = -1;

        ret = CAENHV_GetChParam(sysHndl_, slot, param.c_str(), 1, ChList, lParValList);
        if(ret != CAENHV_OK) {

            sprintf(msg_, "CAEN error: num. %d", ret);
            handleWarning(msg_, 10, __LINE__);
            disconnect();
            
            ATTEMPTS = ATTEMPTS+1;
            connect();
            getvalue(param, ch, slot, value);
        }
        else {
            value = lParValList[0];
            ATTEMPTS = 0;
        }
    }
}


void CAEN::getvalue(string param, int ch, int slot, float &value) {
    
    if(ATTEMPTS > MAX_ATTEMPTS) {

        sprintf(msg_, "QCannot get %s after %d attempts", param.c_str(), MAX_ATTEMPTS);
        handleWarning(msg_, 30, __LINE__);
    }
    else {
        
        unsigned short *ChList;
        ChList = (unsigned short*)malloc(sizeof(unsigned short));
        ChList[0] = ch;
        
        float *lParValList = NULL;
        lParValList = (float*)malloc(sizeof(float));
        CAENHVRESULT ret = -1;
        
        ret = CAENHV_GetChParam(sysHndl_, slot, param.c_str(), 1, ChList, lParValList);
        if(ret != CAENHV_OK) {

            sprintf(msg_, "CAEN error: num. %d", ret);
            handleWarning(msg_, 10, __LINE__);
            disconnect();
            
            ATTEMPTS = ATTEMPTS+1;
            connect();
            getvalue(param, ch, slot, value);
        }
        else {
            value = lParValList[0];
            ATTEMPTS = 0;
        }
    }
}