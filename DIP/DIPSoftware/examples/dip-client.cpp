#include <unistd.h>
#include "Dip.h"
#include "DipSubscription.h"
#include <stdio.h>
#include <string>
#include <iostream>
#include <sstream>


using namespace std;

class Client {

	private:
		DipSubscription **sub;
		DipFactory *dip;

	class GeneralDataListener:public DipSubscriptionListener {
		
		private:

			// allow us to access subscription objects
			Client * client;


		public:
		
			GeneralDataListener(Client *c):client(c){};

			void handleMessage(DipSubscription *subscription, DipData &message) {

				cout<<"Received data from "<<subscription->getTopicName()<<endl;
				cout<<"value :" <<message.extractFloat("__DIP_DEFAULT__") << endl;
				//received = true;
			}

			void connected(DipSubscription *arg0) {
				cout << "\nPublication source  " << arg0->getTopicName()<< " available\n";
			}

			void disconnected(DipSubscription *arg0, char *arg1) {
				printf("\nPublication source %s unavailable\n", arg0->getTopicName());
			}

			void handleException(DipSubscription* subscription, DipException& ex){
				printf("Subs %s has error %s\n", subscription->getTopicName(), ex.what());
			}

	};

	GeneralDataListener *handler;

	public: Client(const int argc, const char ** argv) {
		
		int numberOfPubs = 1;
		  
		  
		dip = Dip::create("");
		handler = new GeneralDataListener(this);
		sub = new DipSubscription*[numberOfPubs];
		dip->setDNSNode("dipnsgpn1,dipnsgpn2");
         
		  
		sub[0] = dip->createDipSubscription("dip/GIFFppGPN/Atmospheric_Pressure", handler);	
	 }

	~Client() {
	
		dip->destroyDipSubscription(sub[0]);
		delete handler;
		delete dip;
	}

};


int main(const int argc, const char ** argv) {

Client * theClient = new Client(argc,argv);

sleep(10);
		cout<<"Client's lifetime has expired, leaving... "<<endl;

		delete theClient;
		return(0);
}

