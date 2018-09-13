#!/bin/bash

export ROOTSYS=/usr/local/root/
export PATH=$ROOTSYS/bin:$PATH
export PATH=~/bin:./bin:.:$PATH
export LD_LIBRARY_PATH=$ROOTSYS/lib:$LD_LIBRARY_PATH
export DYLD_LIBRARY_PATH=$ROOTSYS/lib:$DYLD_LIBRARY_PATH
export PYTHONPATH=$ROOTSYS/lib:$PYTHONPATH

export LD_LIBRARY_PATH=/home/webdcs/software/webdcs/DIP/DIPSoftware/lib64:$LD_LIBRARY_PATH


cd /home/webdcs/software/webdcs/PMON/php
php pmon.php
