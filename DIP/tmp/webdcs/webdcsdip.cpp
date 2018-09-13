#include <unistd.h>
#include "Dip.h"
#include "DipSubscription.h"
#include <stdio.h>
#include <string>
#include <iostream>
#include <sstream>
#include <mysql/mysql.h>
#include <ctime>
#include <fstream>


using namespace std;
bool received;


// Source variables
int EmergencyStop, Moving, Siren, SourceOFF, SourceON, Veto;

// Attenuator variables
int AttDA, AttDB, AttDC, AttUA, AttUB, AttUC;
float AttUEff, AttDEff;

// Radmon variables
float D1, T1, D2, T2, D3, T3, D4, T4, D5, T5, D6, T6, D7, T7, D8, T8;

// Environmental variables
float P201, T201, RH201, P202, T202, RH202, P203, T203, RH203, P, TIN, TOUT, RHIN, RHOUT;

// Gas variables
float P101, T101, RH101, P102, T102, RH102, P103, T103, RH103, P105, T105, RH105, P106, T106, RH106, RPC_MFC_Humidity, iC4H10_BINOS1, iC4H10_BINOS2, C2H2F4, iC4H10, mixture_with_water, mixture_without_water, SF6;



class GeneralDataListener:public DipSubscriptionListener {
	
	
	public:
	
	void handleMessage(DipSubscription *sub, DipData &message) {
		
		// Source parameters
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Irradiator/EmergencyStop") EmergencyStop =  message.extractBool("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Irradiator/Moving") Moving = message.extractBool("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Irradiator/Siren") Siren = message.extractBool("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Irradiator/SourceOFF") SourceOFF = message.extractBool("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Irradiator/SourceON") SourceON = message.extractBool("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Irradiator/Veto") Veto = message.extractBool("__DIP_DEFAULT__");

		// Attenuator parameters
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Attenuators/DownStreamPos/A") AttDA = message.extractInt("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Attenuators/DownStreamPos/B") AttDB = message.extractInt("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Attenuators/DownStreamPos/C") AttDC = message.extractInt("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Attenuators/DownStreamPos/EffectiveAttenuation") AttDEff = message.extractFloat("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Attenuators/UpStreamPos/A") AttUA = message.extractInt("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Attenuators/UpStreamPos/B") AttUB = message.extractInt("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Attenuators/UpStreamPos/C") AttUC = message.extractInt("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Attenuators/UpStreamPos/EffectiveAttenuation") AttUEff = message.extractFloat("__DIP_DEFAULT__");
		
		// Radmon parameters
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Radmon/1/Dose") D1 = message.extractFloat("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Radmon/1/Temperature") T1 = message.extractFloat("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Radmon/2/Dose") D2 = message.extractFloat("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Radmon/2/Temperature") T2 = message.extractFloat("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Radmon/3/Dose") D3 = message.extractFloat("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Radmon/3/Temperature") T3 = message.extractFloat("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Radmon/4/Dose") D4 = message.extractFloat("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Radmon/4/Temperature") T4 = message.extractFloat("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Radmon/5/Dose") D5 = message.extractFloat("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Radmon/5/Temperature") T5 = message.extractFloat("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Radmon/6/Dose") D6 = message.extractFloat("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Radmon/6/Temperature") T6 = message.extractFloat("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Radmon/7/Dose") D7 = message.extractFloat("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Radmon/7/Temperature") T7 = message.extractFloat("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Radmon/8/Dose") D8 = message.extractFloat("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Radmon/8/Temperature") T8 = message.extractFloat("__DIP_DEFAULT__");

		// Environmental parameters
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Atmospheric_Pressure") P = message.extractFloat("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Temp_Inside_Bunker") TIN = message.extractFloat("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Temp_Outside_Bunker") TOUT = message.extractFloat("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Humidity_Inside_Bunker") RHIN = message.extractFloat("__DIP_DEFAULT__");
		if((string)sub->getTopicName() == "dip/GIFFppGPN/Humidity_Outside_Bunker") RHOUT = message.extractFloat("__DIP_DEFAULT__");
		
		if((string)sub->getTopicName() == "EnvSensors/203/ZONE5") {		
			P203 = message.extractFloat("P");
			T203 = message.extractFloat("T");
			RH203 = message.extractFloat("RH");
		}

		if((string)sub->getTopicName() == "EnvSensors/202/ZONE1") {		
			P202 = message.extractFloat("P");
			T202 = message.extractFloat("T");
			RH202 = message.extractFloat("RH");
		}

		if((string)sub->getTopicName() == "EnvSensors/201/ZONE2") {		
			P201 = message.extractFloat("P");
			T201 = message.extractFloat("T");
			RH201 = message.extractFloat("RH");
		}

		// Gas parameters
		if((string)sub->getTopicName() == "dip/GIFFppGPN/gas/RPC_MFC_Humidity") RPC_MFC_Humidity = message.extractFloat("H2O");

		if((string)sub->getTopicName() == "dip/GIFFppGPN/gas/IRsensor") {
			iC4H10_BINOS1 = message.extractFloat("iC4H10_BINOS1");
			iC4H10_BINOS2 = message.extractFloat("iC4H10_BINOS2");
		}

		if((string)sub->getTopicName() == "dip/GIFFppGPN/gas/RPC_MFC") {		
			C2H2F4 = message.extractFloat("C2H2F4");
			iC4H10 = message.extractFloat("iC4H10");
			mixture_with_water = message.extractFloat("mixture_with_water");
			mixture_without_water = message.extractFloat("mixture_without_water");
			SF6 = message.extractFloat("SF6");
		}

		if((string)sub->getTopicName() == "GasSensors/106/ZONE5") {		
			P106 = message.extractFloat("P");
			T106 = message.extractFloat("T");
			RH106 = message.extractFloat("RH");
		}

		if((string)sub->getTopicName() == "GasSensors/105/ZONE2") {		
			P105 = message.extractFloat("P");
			T105 = message.extractFloat("T");
			RH105 = message.extractFloat("RH");
		}

		if((string)sub->getTopicName() == "GasSensors/103/ZONE5") {		
			P103 = message.extractFloat("P");
			T103 = message.extractFloat("T");
			RH103 = message.extractFloat("RH");
		}

		if((string)sub->getTopicName() == "GasSensors/102/ZONE2") {		
			P102 = message.extractFloat("P");
			T102 = message.extractFloat("T");
			RH102 = message.extractFloat("RH");
		}

		if((string)sub->getTopicName() == "GasSensors/101/ZONE3") {		
			P101 = message.extractFloat("P");
			T101 = message.extractFloat("T");
			RH101 = message.extractFloat("RH");
		}

	}
	
	void connected(DipSubscription *arg0) {
		//cout << "\nPublication source  " << arg0->getTopicName()<< " available\n";
	}

	void disconnected(DipSubscription *arg0, char *arg1) {
		printf("\nPublication source %s unavailable\n", arg0->getTopicName());
	}

	void handleException(DipSubscription* subscription, DipException& ex){
		printf("Subs %s has error %s\n", subscription->getTopicName(), ex.what());
	}
};


int main(const int argc, const char ** argv) {
	
	GeneralDataListener *handler;
	DipSubscription **sub;
	DipFactory *dip;
	
	dip = Dip::create("");
	handler = new GeneralDataListener();
	sub = new DipSubscription*[46];
	dip->setDNSNode("dipnsgpn1,dipnsgpn2");
        
	// Source variables
	sub[0]  = dip->createDipSubscription("dip/GIFFppGPN/Irradiator/EmergencyStop", handler);
	sub[1]  = dip->createDipSubscription("dip/GIFFppGPN/Irradiator/Moving", handler);
	sub[2]  = dip->createDipSubscription("dip/GIFFppGPN/Irradiator/Siren", handler);
	sub[3]  = dip->createDipSubscription("dip/GIFFppGPN/Irradiator/SourceOFF", handler);
	sub[4]  = dip->createDipSubscription("dip/GIFFppGPN/Irradiator/SourceON", handler);
	sub[5]  = dip->createDipSubscription("dip/GIFFppGPN/Irradiator/Veto", handler);

	// Attenuator variables
	sub[6]  = dip->createDipSubscription("dip/GIFFppGPN/Attenuators/DownStreamPos/A", handler);
	sub[7]  = dip->createDipSubscription("dip/GIFFppGPN/Attenuators/DownStreamPos/B", handler);
	sub[8]  = dip->createDipSubscription("dip/GIFFppGPN/Attenuators/DownStreamPos/C", handler);
	sub[9]  = dip->createDipSubscription("dip/GIFFppGPN/Attenuators/DownStreamPos/EffectiveAttenuation", handler);
	sub[10] = dip->createDipSubscription("dip/GIFFppGPN/Attenuators/UpStreamPos/A", handler);
	sub[11] = dip->createDipSubscription("dip/GIFFppGPN/Attenuators/UpStreamPos/B", handler);
	sub[12] = dip->createDipSubscription("dip/GIFFppGPN/Attenuators/UpStreamPos/C", handler);
	sub[13] = dip->createDipSubscription("dip/GIFFppGPN/Attenuators/UpStreamPos/EffectiveAttenuation", handler);

	// Radmon variables
	sub[14] = dip->createDipSubscription("dip/GIFFppGPN/Radmon/1/Dose", handler);
	sub[15] = dip->createDipSubscription("dip/GIFFppGPN/Radmon/1/Temperature", handler);
	sub[16] = dip->createDipSubscription("dip/GIFFppGPN/Radmon/2/Dose", handler);
	sub[17] = dip->createDipSubscription("dip/GIFFppGPN/Radmon/2/Temperature", handler);
	sub[18] = dip->createDipSubscription("dip/GIFFppGPN/Radmon/3/Dose", handler);
	sub[19] = dip->createDipSubscription("dip/GIFFppGPN/Radmon/3/Temperature", handler);
	sub[20] = dip->createDipSubscription("dip/GIFFppGPN/Radmon/4/Dose", handler);
	sub[21] = dip->createDipSubscription("dip/GIFFppGPN/Radmon/4/Temperature", handler);
	sub[22] = dip->createDipSubscription("dip/GIFFppGPN/Radmon/5/Dose", handler);
	sub[23] = dip->createDipSubscription("dip/GIFFppGPN/Radmon/5/Temperature", handler);
	sub[24] = dip->createDipSubscription("dip/GIFFppGPN/Radmon/6/Dose", handler);
	sub[25] = dip->createDipSubscription("dip/GIFFppGPN/Radmon/6/Temperature", handler);
	sub[26] = dip->createDipSubscription("dip/GIFFppGPN/Radmon/7/Dose", handler);
	sub[27] = dip->createDipSubscription("dip/GIFFppGPN/Radmon/7/Temperature", handler);
	sub[28] = dip->createDipSubscription("dip/GIFFppGPN/Radmon/8/Dose", handler);
	sub[29] = dip->createDipSubscription("dip/GIFFppGPN/Radmon/8/Temperature", handler);
	
	// Environmental variables
	sub[30] = dip->createDipSubscription("EnvSensors/201/ZONE2", handler);
	sub[31] = dip->createDipSubscription("EnvSensors/202/ZONE1", handler);
	sub[32] = dip->createDipSubscription("EnvSensors/203/ZONE5", handler);
	sub[33] = dip->createDipSubscription("dip/GIFFppGPN/Atmospheric_Pressure", handler);
	sub[34] = dip->createDipSubscription("dip/GIFFppGPN/Temp_Inside_Bunker", handler);
	sub[35] = dip->createDipSubscription("dip/GIFFppGPN/Temp_Outside_Bunker", handler);
	sub[36] = dip->createDipSubscription("dip/GIFFppGPN/Humidity_Inside_Bunker", handler);
	sub[37] = dip->createDipSubscription("dip/GIFFppGPN/Humidity_Outside_Bunker", handler);

	// Gas Variables
	sub[38] = dip->createDipSubscription("GasSensors/101/ZONE3", handler);
	sub[39] = dip->createDipSubscription("GasSensors/102/ZONE2", handler);
	sub[40] = dip->createDipSubscription("GasSensors/103/ZONE5", handler);
	sub[41] = dip->createDipSubscription("GasSensors/105/ZONE2", handler);
	sub[42] = dip->createDipSubscription("GasSensors/106/ZONE5", handler);
	sub[43] = dip->createDipSubscription("dip/GIFFppGPN/gas/RPC_MFC_Humidity", handler);
	sub[44] = dip->createDipSubscription("dip/GIFFppGPN/gas/IRsensor", handler);
	sub[45] = dip->createDipSubscription("dip/GIFFppGPN/gas/RPC_MFC", handler);
	



	
	int i=0;
	for(i=0; i< 46; i++) sub[i]->requestUpdate();

	sleep(1);

	time_t  timev;

 	// Write PT values for PT-correction P, TIN, TOUT, RHIN
	ofstream file;
	file.open ("/var/operation/RUN/pt");
	file << time(&timev);
	file << "\n";
	file << P; // WAS P201
	file << "\n";	
	file << TIN; // WAS T201
	file << "\n";	
	file << RHIN; // WAS RH201
	file << "\n";	
	file << SourceON;
	file << "\n";	
	file << AttUA << AttUB << AttUC;
	file << "\n";
	file << AttDA << AttDB << AttDC;
        file << "\n";	
	file << C2H2F4;
	file << "\n";	
	file << iC4H10;
	file << "\n";	
	file << SF6;
	file.close();
  
	
	// Write values to DB
	MYSQL *conn;
	char *server = "localhost";
	char *user = "DIP";
	char *password = "UserlabGIF++";
	char *database = "DIP";
	conn = mysql_init(NULL);
	
	if(!mysql_real_connect(conn, server, user, password, database, 0, NULL, 0)) {
		
        printf("Database error: %s", mysql_error(conn));
        return 0;
    }
    else {
		char sql[10000]; // SHOULD BE LONG ENOUGH!!!!!!!!!!!!!!!!!!!!!


		sprintf(sql, "INSERT INTO source (timestamp, EmergencyStop, Moving, Siren, SourceOFF, SourceON, Veto) VALUES (%d, %d, %d, %d, %d, %d, %d)", time(&timev), EmergencyStop, Moving, Siren, SourceOFF, SourceON, Veto);
		if(mysql_query(conn, sql)) {
            //fprintf(log, "%s,database error (3): %s\n", dateformat, mysql_error(conn));
            fprintf(stderr, "%s\n", mysql_error(conn));
            cout << "error" << endl;
			return 0; // ERROR HANDLING!!!!!!
        }


		sprintf(sql, "INSERT INTO attenuator (timestamp, AttDA, AttDB, AttDC, AttUA, AttUB, AttUC, AttUEff, AttDEff) VALUES (%d, %d, %d, %d, %d, %d, %d, %f, %f)", time(&timev), AttDA, AttDB, AttDC, AttUA, AttUB, AttUC, AttUEff, AttDEff);
		if(mysql_query(conn, sql)) {
            //fprintf(log, "%s,database error (3): %s\n", dateformat, mysql_error(conn));
            fprintf(stderr, "%s\n", mysql_error(conn));
            cout << "error" << endl;
			return 0; // ERROR HANDLING!!!!!!
        }


		sprintf(sql, "INSERT INTO radmon (timestamp,  D1, T1, D2, T2, D3, T3, D4, T4, D5, T5, D6, T6, D7, T7, D8, T8) VALUES (%d,  %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f)", time(&timev),  D1, T1, D2, T2, D3, T3, D4, T4, D5, T5, D6, T6, D7, T7, D8, T8);
		if(mysql_query(conn, sql)) {
            //fprintf(log, "%s,database error (3): %s\n", dateformat, mysql_error(conn));
            fprintf(stderr, "%s\n", mysql_error(conn));
            cout << "error" << endl;
			return 0; // ERROR HANDLING!!!!!!
        }


		sprintf(sql, "INSERT INTO environmental (timestamp, P201, T201, RH201, P202, T202, RH202, P203, T203, RH203, P, TIN, TOUT, RHIN, RHOUT) VALUES (%d, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f)", time(&timev), P201, T201, RH201, P202, T202, RH202, P203, T203, RH203, P, TIN, TOUT, RHIN, RHOUT);
		if(mysql_query(conn, sql)) {
            //fprintf(log, "%s,database error (3): %s\n", dateformat, mysql_error(conn));
            fprintf(stderr, "%s\n", mysql_error(conn));
            cout << "error" << endl;
			return 0; // ERROR HANDLING!!!!!!
        }


		sprintf(sql, "INSERT INTO gas (timestamp, P101, T101, RH101, P102, T102, RH102, P103, T103, RH103, P105, T105, RH105, P106, T106, RH106, RPC_MFC_Humidity, iC4H10_BINOS1, iC4H10_BINOS2, C2H2F4, iC4H10, mixture_with_water, mixture_without_water, SF6) VALUES (%d, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f, %f) ", time(&timev), P101, T101, RH101, P102, T102, RH102, P103, T103, RH103, P105, T105, RH105, P106, T106, RH106, RPC_MFC_Humidity, iC4H10_BINOS1, iC4H10_BINOS2, C2H2F4, iC4H10, mixture_with_water, mixture_without_water, SF6);
		if(mysql_query(conn, sql)) {
            //fprintf(log, "%s,database error (3): %s\n", dateformat, mysql_error(conn));
            fprintf(stderr, "%s\n", mysql_error(conn));
            cout << "error" << endl;
			return 0; // ERROR HANDLING!!!!!!
        }


		mysql_close(conn);
	}
	if(false) {
		/*
		cout << Atmospheric_Pressure << endl;
		cout << Temp_Inside_Bunker << endl;
		cout << Temp_Outside_Bunker << endl;
		cout << Humidity_Inside_Bunker << endl;
		cout << Humidity_Outside_Bunker << endl;
		cout << "ATTENUATOR:" << endl;
		cout << attDA << attDB << attDC << endl;
		cout << effD << endl;
		cout << attUA << attUB << attUC << endl;
		cout << effU << endl;
		cout << "SOURCE" << endl;
		cout << sourceON << endl;
		cout << sourceOFF << endl;
		cout << EmergencyStop << endl;
		cout << Moving << endl;
		cout << Siren << endl;
		cout << Veto << endl;
		cout << "GAS:" << endl;
		cout << RPC_MFC_Humidity << endl;
		cout << TGC_CO2 << endl;
		cout << iC4H10_BINOS1 << endl;
		cout << iC4H10_BINOS2 << endl;
		cout << C2H2F4 << endl;
		cout << iC4H10 << endl;
		cout << mixture_with_water << endl;
		cout << mixture_without_water << endl;
		cout << SF6 << endl;
		cout << "RADMON:" << endl;
		cout << Radmon1 << endl;
		cout << Radmon2 << endl;
		cout << Radmon3 << endl;
		cout << Radmon4 << endl;
		cout << Radmon5 << endl;
		cout << Radmon6 << endl;
		cout << Radmon7 << endl;
		cout << Radmon8 << endl;
		*/
	}
	

	dip->destroyDipSubscription(sub[0]);
	delete handler;
	delete dip;
	return(0);

}
