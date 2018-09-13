#include "../interface/dip.hpp"

class GeneralDataListener:public DipSubscriptionListener {
	
    public:

	void handleMessage(DipSubscription *sub, DipData &message) {
	
            for(uint i=0; i<d->size(); i++) {
                
                string tn = (string)sub->getTopicName();
                
                if(tn == d->at(i).subscription) {
                    
                    for(uint j=0; j<d->at(i).types.size(); j++) {
                        
                        if(d->at(i).types.at(j) == "float") d->at(i).values.at(j) = message.extractFloat(d->at(i).identifiers.at(j).c_str());
                        if(d->at(i).types.at(j) == "int") d->at(i).values.at(j) = (float)message.extractInt(d->at(i).identifiers.at(j).c_str());
                        if(d->at(i).types.at(j) == "bool") d->at(i).values.at(j) = (float)message.extractBool(d->at(i).identifiers.at(j).c_str());
                    }
                }
            }
            
            // cout<<"Received data from "<<sub->getTopicName()<<endl;
            //cout<<"value :" <<message.extractFloat("__DIP_DEFAULT__") << endl;
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


string getSetting(string setting) {
    
    sql = "SELECT value FROM settings WHERE setting = '" + setting + "'";
    res = db->query(sql);
    row = mysql_fetch_row(res);
    return (string)row[0];
}

int main(const int argc, const char ** argv) {
    
    // Make database connection
    db = new MYSQLDb("DIP"); // DIP database
    db->connect();
    
    // Retrieve DIP settings
    string DNSnodes = getSetting("DNSnodes");
    
    // Setup DIP classes
    GeneralDataListener *handler;
    handler = new GeneralDataListener();
    DipFactory *dip;
    dip = Dip::create("");
    dip->setDNSNode(DNSnodes.c_str()); // 
    vector<DipSubscription*> DipSubscriptions;
    
    
    // Loop over all DIP parameters
    
    sql = "SELECT dip_subscription, table_name FROM subscriptions GROUP BY dip_subscription";
    res = db->query(sql);
    while((row = mysql_fetch_row(res))) {
        
        DIPParameter x; 
        x.subscription = (string)row[0];
        x.table_name = (string)row[1];

        sql = "SELECT dip_identifier, id_name, type FROM subscriptions WHERE dip_subscription = '" + (string)row[0] + "'";
        res1 = db->query(sql);
        while((row1 = mysql_fetch_row(res1))) {
            x.identifiers.push_back((string)row1[0]);
            x.names.push_back((string)row1[1]);
            x.types.push_back((string)row1[2]);
            x.values.push_back(0); // default value = 0
        }
        
        d->push_back(x);
    }
    
    for(uint i=0; i<d->size(); i++) {
        
        DipSubscriptions.push_back(dip->createDipSubscription(d->at(i).subscription.c_str(), handler));
        DipSubscriptions.at(i)->requestUpdate();
    }
    
    time_t  timev;
    sleep(1); // sleep one second to wait for DIP pubs

   
    
    // Construct insert queries
    // Type: INSERT INTO MyGuests (firstname, lastname, email) VALUES ('John', 'Doe', 'john@example.com')
    vector<string> column_names;
    vector<string> values;
    vector<string> table_names;
    for(uint i=0; i<d->size(); i++) {
        
        // search for correct index
        int j = -1;
        for(uint k=0; k<table_names.size(); k++) {
            if(table_names.at(k) == d->at(i).table_name) {
                j = k;
                break;
            }
        }

        if((j == -1)) { // new
            table_names.push_back(d->at(i).table_name);
            for(uint k=0; k<d->at(i).types.size(); k++) {
                column_names.push_back(", " + d->at(i).names.at(k));
                values.push_back(", " + to_string(d->at(i).values.at(k)));
            }
        }
        else {
            
            for(uint k=0; k<d->at(i).types.size(); k++) {
                column_names.at(j) += ", " + d->at(i).names.at(k);
                values.at(j) += ", " + to_string(d->at(i).values.at(k));
            }
        }
    }
    
    // Make queries and insert into database
    for(uint i=0; i<table_names.size(); i++) {
        
        string query = "INSERT INTO " + table_names.at(i) + " (timestamp";
        query += column_names.at(i) + ") VALUES (" + to_string(time(&timev));
        query += values.at(i) + ")";
        cout << query << endl;
        db->query(query);
    }
    
    // Make a dump to a txt file
    ofstream DIPFile;
    DIPFile.open("/var/operation/RUN/DIP");
    DIPFile << "Time" << "=" << time(&timev) << "\n";
    for(uint i=0; i<d->size(); i++) {

        for(uint k=0; k<d->at(i).types.size(); k++) {
            
            DIPFile << d->at(i).names.at(k) << "=" << d->at(i).values.at(k) << "\n";
        }
    }
    DIPFile.close();
    
    
    // Clean
    for(uint i=0; i<DipSubscriptions.size(); i++) dip->destroyDipSubscription(DipSubscriptions.at(i));
    delete handler;
    delete dip;
    
    db->disconnect();
    
    return 0;
}
