#
# Makefile for c++ examples
#

CC = g++
CFLAGS = -Wall -ansi -I ../include

DLIB = ../lib64/libdip.so -lpthread -ldl

all:	Server Client

Client:	dip-client.cpp ../lib64/libdip.so
	$(CC) $(CFLAGS) dip-client.cpp -L../lib64 $(DLIB) -o dip-client $(LIBS) 
	
Server:	dip-server.cpp ../lib64/libdip.so
	$(CC) $(CFLAGS) dip-server.cpp -L../lib64 $(DLIB) -o dip-server $(LIBS) 
	
