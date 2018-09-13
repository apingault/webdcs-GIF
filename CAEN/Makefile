
BIN_DIR  = bin
INT_DIR  = interface
SRC_DIR  = src
OBJ_DIR  = obj
LIB_DIR  = lib


CC = g++ -std=c++11

ROOT_INC     := $(ROOTSYS)/include
ROOTCFLAGS   := $(shell root-config --cflags)
ROOTLIBS     := $(shell root-config --libs)
	

LFLAGS       := -Llib -L/usr/lib \
                $(ROOTLIBS) \
		-L$(LIB_DIR) -lcaenhvwrapper \
		-lncurses `mysql_config --cflags --libs` -lpthread -lrt 

CFLAGS       := -ggdb -fPIC -DLINUX -Wall -funsigned-char \
                -I$(INC_DIR) -I$(ROOT_INC) -I$(ROOTCFLAGS) -I$(LIB_DIR) \
		-DUNIX -DLINUX -I$(INT_DIR)

all: $(BIN_DIR) $(OBJ_DIR) Longevity HVscan

Longevity:  Longevity.o CAEN.o MYSQL.o utils.o DIP.o
	    g++ $(CFLAGS) $(OBJ_DIR)/Longevity.o \
	    $(OBJ_DIR)/CAEN.o \
	    $(OBJ_DIR)/MYSQL.o \
	    $(OBJ_DIR)/utils.o \
	    $(OBJ_DIR)/DIP.o \
	    -o $(BIN_DIR)/Longevity \
	    $(LFLAGS) 
	
HVscan:  HVscan.o utils.o CAEN.o MYSQL.o
	    g++ $(CFLAGS) $(OBJ_DIR)/HVscan.o \
	    $(OBJ_DIR)/CAEN.o \
	    $(OBJ_DIR)/MYSQL.o \
	    $(OBJ_DIR)/utils.o \
	    -o $(BIN_DIR)/HVscan \
	    $(LFLAGS) 


Longevity.o:
	$(CC) $(CFLAGS) -c $(SRC_DIR)/Longevity.cpp -o $(OBJ_DIR)/Longevity.o
	
HVscan.o:
	$(CC) $(CFLAGS) -c $(SRC_DIR)/HVscan.cpp -o $(OBJ_DIR)/HVscan.o
	
CAEN.o:
	$(CC) $(CFLAGS) -c $(SRC_DIR)/CAEN.cpp -o $(OBJ_DIR)/CAEN.o
MYSQL.o:
	$(CC) $(CFLAGS) -c $(SRC_DIR)/MYSQL.cpp -o $(OBJ_DIR)/MYSQL.o
utils.o:
	$(CC) $(CFLAGS) -c $(SRC_DIR)/utils.cpp -o $(OBJ_DIR)/utils.o
DIP.o:
	$(CC) $(CFLAGS) -c $(SRC_DIR)/DIP.cpp -o $(OBJ_DIR)/DIP.o




$(BIN_DIR):
	mkdir -p $(BIN_DIR)/

$(OBJ_DIR):
	mkdir -p $(OBJ_DIR)/

clean:
	rm -rf $(BIN_DIR)/
	rm -rf $(OBJ_DIR)/

remove:
	rm -rf $(BIN_DIR)/
	rm -rf $(OBJ_DIR)/
