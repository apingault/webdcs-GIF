#!/usr/bin/env python

#######################
# Script to produce the the necessary input files for the DAQ/DQM
# 

from array import array
import math
import string
from datetime import datetime                                
import time
import os,sys
import glob        
import MySQLdb
import time
import urllib2
import json
from optparse import OptionParser
from subprocess import call
from time import gmtime, strftime


parser = OptionParser()
parser.add_option("", "--id", dest='id', type='int', help="Run id")
parser.add_option("", "--HV", dest='HV', type='int', help="HV")
parser.add_option("", "--maxtriggers", dest='maxtriggers', type='int', help="Max triggers")
parser.add_option("", "--reinit", dest = "reinit", action = 'store_true', help = "DAQ reinitialization", default = False)
parser.add_option("", "--init", dest = "init", action = 'store_true', help = "Initialisation DAQ", default = False)
parser.add_option("", "--start", dest = "start", action = 'store_true', help = "Start DAQ", default = False)
parser.add_option("", "--stop", dest = "stop", action = 'store_true', help = "Stop DAQ", default = False)
parser.add_option("", "--refreshrun", dest = "refreshrun", action = 'store_true', help = "Refresh RUN file", default = False)
(opts,args) = parser.parse_args()

if opts.id is None: parser.error('Please provide input ID')
scanid = opts.id
 
now = time.strftime("%c")

db = MySQLdb.connect(host='localhost', user='root', passwd='UserlabGIF++', db='webdcs')
cursor = db.cursor()


# Get current DAQ
# cursor.execute("SELECT value FROM settings WHERE setting = 'daqtype'")
# res = cursor.fetchone()
# DAQTYPE = res[0]
DAQTYPE = 'default'

LOG = "/var/operation/HVSCAN/%06d/log.txt" % opts.id



class LYONDAC():


    # ssh acqilc@lyosdhcal8 Password: RPC_2008;
    host = "lyosdhcal11.cern.ch:45000"

    CMD_start = None
    CMD_startTrig = None
    CMD_stop = None
    CMD_status = None
    
    
    def __init__(self):
    
        self.CMD_start = "http://%s/FDAQ/FSM?command=START&content={}" % self.host
        self.CMD_startTrig = "http://%s/FDAQ/CMD?name=RESUME&content={}" % self.host
        self.CMD_stop = "http://%s/FDAQ/FSM?command=STOP&content={}" % self.host
        self.CMD_status = "http://%s/FDAQ/CMD?name=EVBSTATUS" % self.host
        self.CMD_TDCSTATUS = "http://%s/FDAQ/CMD?name=TDCSTATUS" % self.host
        
    
    def dummySet(self, cfg):
    
        file = open("/var/operation/RUN/dummy", "w") 
        file.write(cfg) 
        
    def dummyGet(self):
    
        with open('/var/operation/RUN/dummy') as f:
            out = f.readline()
        
        return out
        
        
    def cmd(self, cmd):

        response = json.loads(urllib2.urlopen(cmd).read())
        return response
        
    def noTriggers(self):
    
        
        response = self.cmd(self.CMD_TDCSTATUS)
        #print response        
        minTriggers = 1e99
        
        for r in response['answer']['tdclist'][0]: 
           
           if r['event'] < minTriggers: minTriggers = r['event']

        return minTriggers        
    
        
    def start(self):
    
        #self.dummySet("START")
        #self.dummySet("{\"answer\":{\"STATUS\":\"DONE\",\"event\":12,\"run\":123456},\"status\":\"OK\"}")
        self.cmd(self.CMD_start)
	time.sleep(2)
        self.cmd(self.CMD_startTrig)
        
    def stop(self):
    
        #self.dummySet("STOP")
        self.cmd(self.CMD_stop)
        
        # copy the log file to webdcs
        
    
    def eventInfo(self):
    
        response = self.cmd(self.CMD_status)
        #response = json.loads(self.dummyGet())
        # {"answer":{"STATUS":"DONE","event":499,"run":740033},"status":"OK"}
        return response['answer']['event'], response['answer']['run']
        
    def status(self):
    
        return 0 # always OK
        try: urllib2.urlopen(self.CMD_status).read()      
        except urllib2.HTTPError as e: return 2 # SECOND CHECK: 404
        except: return 1 # CHECK IF SERVER EXIST

        return 0 # ok

    
    
def readRun():

    with open('/var/operation/RUN/run', 'r') as myfile:
        data = myfile.read()
        
    return data

def setRun(r):

    cmd = "echo %s > /var/operation/RUN/run" % r
    os.system(cmd)
    

def log(msg):

    n = strftime("%Y-%m-%d.%H:%M:%S", gmtime())
    with open(LOG, "a") as myfile:
        myfile.write("%s.[HVscan] %s\n" % (n, msg))

if opts.init:

    if DAQTYPE == "default":
        
        log("Generate first daq.ini file")
        call("python /home/webdcs/software/webdcs/CAEN/python/generateDAQFiles.py --id " + str(opts.id) + " --daqini --HV 1 --maxtriggers 5000", shell=True)

        # Generate mapping
        log("Start TDC mapping file")
        call("python /home/webdcs/software/webdcs/CAEN/python/generateDAQFiles.py --id " + str(opts.id) + " --mapping", shell=True)
        
        # Generate dimensions
        log("Generate dimensions file")
        call("python /home/webdcs/software/webdcs/CAEN/python/generateDAQFiles.py --id " + str(opts.id) + " --dimensions", shell=True)
        

    if DAQTYPE == "digitizer":
    
        pass # nothing to do

if opts.stop:

    if DAQTYPE == "lyondaq":
        
        LDAC = LYONDAC()
        status = LDAC.status()
        if status != 0: # 404
        
            log("LYONDAC 404 ERROR")
            setRun("DAQ_ERR")
            
        else:
        
            LDAC.stop()
            log("Send stop command to LYON DAQ")
            setRun("DAQ_RDY")
        
if opts.start:

    if DAQTYPE == "default":
    
        call("/home/webdcs/software/GIF_DAQ/bin/daq " + LOG + " > /dev/null 2>&1 &", shell=True)
   
        print "/home/webdcs/software/GIF_DAQ/bin/daq " + LOG  

    if DAQTYPE == "digitizer":
        print "DIGITIZER"    
        # start remote DAQ script
        #call("sshpass -p 'UserlabGIF++' ssh webdcs@pccmsrpc-server01.cern.ch 'python /home/webdcs/webdcs/DAQ/DAQ.py > /dev/null 2>&1 &'", shell=True)
        call("sshpass -p 'UserlabGIF++' ssh webdcs@pccmsrpc-server01.cern.ch './webdcs/DAQ/DAQ.sh'", shell=True)
        #print "sshpass -p 'UserlabGIF++' ssh webdcs@pccmsrpc-server01.cern.ch './webdcs/DAQ/DAQ.sh'"
        
        
    if DAQTYPE == "lyondaq":
    
        print "DO NOTHING"
        
if opts.reinit:

    if DAQTYPE == "default":
    
        pass # nothing to do --> DAQ takes care by itself
        
        
    if DAQTYPE == "digitizer":
    
        # Start again the digitizer
        pass
        
        
    if DAQTYPE == "lyondaq":
    
        LDAC = LYONDAC()
        LDAC.start()
        setRun("RUNNING")
        log("Send start command to LYON DAQ")
        
        # READ THE RUN NUMBER AND STORE IT (overwrite)
        event, run = LDAC.eventInfo()
        log("Run %d started" % run)
        with open("/var/operation/HVSCAN/%06d/LYONDAQ_HV%d.cfg" % (opts.id, opts.HV), "w") as myfile:
            myfile.write("%d" % run)

# called every measure_intval      
if opts.refreshrun:

    if DAQTYPE == "default":
    
        pass # nothing to do --> DAQ takes care by itself
        
        
    if DAQTYPE == "digitizer":
    
        # Start again the digitizer
        pass
        
        
    if DAQTYPE == "lyondaq":
    
        
        runStat = readRun()
        if "DAQ_INIT_PAUSE" in runStat:
        
            # PAUSE INITIATED BY USER
            log("Run paused by user")
            setRun("DAQ_PAUSE")
            sys.exit()
            
        elif "DAQ_PAUSE" in runStat:
        
            sys.exit()
            
        elif "DAQ_INIT_RESUME" in runStat:
        
            log("Run resumed by user")
            setRun("RUNNING") # switch from DAQ_PAUSE to RUNNING
            sys.exit()
            
        # check on triggers
        LDAC = LYONDAC()

        if opts.maxtriggers > 0:
            
            triggers = LDAC.noTriggers()
            perc = int(100*triggers/opts.maxtriggers)
            if triggers > opts.maxtriggers:
                
                readRun()
                if not "DAQ_STOP" in readRun():
                    setRun("DAQ_STOP")
                    log("LYONDAC triggers collected, stop the DAQ")
                    
                sys.exit()
            elif perc == 0 or perc % 2 == 0:
            
                setRun("RUNNING") 
                toWrite = "Taking data... %d%%" % perc
                if not toWrite in open(LOG).read():
        
                    log(toWrite)
                
       

        # CHECK THE STATUS (always) 
        status = LDAC.status()
        if status == 2: # 404
        
            log("LYONDAC 404 ERROR")
            setRun("DAQ_ERR")
        
        elif status == 1: # connection problem
        
            log("LYONDAC cannot connect")
            setRun("DAQ_ERR")
        

        else: # alive
        
            
        
            
            if opts.maxtriggers == -1: # initial stage
        
                setRun("DAQ_RDY")
                
            else:
            
                pass
                #log("Taking data...")
                #setRun("RUNNING") # update time 
            
            '''
            elif opts.maxtriggers > 0:
            
                event, run = LDAC.eventInfo()
                perc = int(100*event/opts.maxtriggers)
                #print opts.maxtriggers, perc, event, run
                if event >= opts.maxtriggers:
                
                    log("Run finished. Waiting for the next signal...")
                    setRun("DAQ_RDY") # OK
                    
                    # SEND STOP COMMAND
                    
                elif perc == 0 or perc % 5 == 0:
                    
                    log("Taking data... %d%%" % perc)
                    setRun("RUNNING") 
            '''        
                    
                
                

            
            
            
