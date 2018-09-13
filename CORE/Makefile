
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


	

MYSQL:
	$(CC) $(CFLAGS) -c $(SRC_DIR)/MYSQL.cpp -o $(OBJ_DIR)/MYSQL.o




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
