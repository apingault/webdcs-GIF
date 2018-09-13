#!/bin/bash

# SET ROOT ENVIROMENTAL VARIABLES
export ROOTSYS=/usr/local/root/
export PATH=$ROOTSYS/bin:$PATH
export PATH=~/bin:./bin:.:$PATH
export LD_LIBRARY_PATH=$ROOTSYS/lib:$LD_LIBRARY_PATH
export DYLD_LIBRARY_PATH=$ROOTSYS/lib:$DYLD_LIBRARY_PATH
export PYTHONPATH=$ROOTSYS/lib:$PYTHONPATH

echo "lol" > /var/operation/STABILITY/test

#{ /home/webdcs/software/CAEN/webdcs/HVscan $1 >> /home/webdcs/software/CAEN/log/$1.log; } 2>> /home/webdcs/software/CAEN/log/$1.err & echo $!
#/home/webdcs/software/CAEN/webdcs/Stability $1 > /home/webdcs/software/CAEN/log/stability/$1.log
# > /dev/null 2>&1
