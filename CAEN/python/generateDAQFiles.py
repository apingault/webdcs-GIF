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
from optparse import OptionParser
from subprocess import call
from time import gmtime, strftime



parser = OptionParser()
parser.add_option("", "--id", dest='id', type='int', help="Run id")
parser.add_option("", "--HV", dest='HV', type='int', help="HV")
parser.add_option("", "--maxtriggers", dest='maxtriggers', type='int', help="Max triggers")
parser.add_option("", "--dimensions", dest = "dimensions", action = 'store_true', help = "Generate dimensions.ini file", default = False)
parser.add_option("", "--mapping", dest = "mapping", action = 'store_true', help = "Generate mapping.csv file", default = False)
parser.add_option("", "--daqini", dest = "daqini", action = 'store_true', help = "Generate daq.ini file", default = False)
(opts,args) = parser.parse_args()

if opts.id is None: parser.error('Please provide input ID')
scanid = opts.id
 
now = time.strftime("%c")


LOG = "/var/operation/HVSCAN/%06d/log.txt" % opts.id

def log(msg):

    n = strftime("%Y-%m-%d.%H:%M:%S", gmtime())
    with open(LOG, "a") as myfile:
        myfile.write("%s.[HVscan] %s\n" % (n, msg))



db = MySQLdb.connect(host='localhost', user='root', passwd='UserlabGIF++', db='webdcs')
cursor = db.cursor()


# Get current DAQ
# cursor.execute("SELECT value FROM settings WHERE setting = 'daqtype'")
# res = cursor.fetchone()
# DAQTYPE = res[0]
DAQTYPE = 'default'

log("on en a gros")
log("opts.daqini")
log(opts)
if opts.daqini:

    if DAQTYPE == 'default':
        log("On est la")
        if opts.HV is None: parser.error('Please provide HV')
        if opts.maxtriggers is None: parser.error('Please provide maxtriggers')

        # get selected configuration
        # cursor.execute("SELECT d.content FROM daqini d, settings s WHERE s.setting = 'daqini_default' AND s.value = d.id")
        cursor.execute("SELECT d.content FROM daqini d, settings s WHERE s.setting = 'daqini' AND s.value = d.id")
        res = cursor.fetchone()

        # Load the scan details
        # cursor.execute("SELECT beam FROM hvscan WHERE id = %s" % scanid)
        # res0 = cursor.fetchone()
        cursor.execute("SELECT type, trigger_mode FROM hvscan_DAQ WHERE id = %s" % scanid)
        res1 = cursor.fetchone()
        
        # beam = "OFF" if int(res0[0]) == 0 else "ON"
        type = str(res1[0])
        if "random" in str(res1[1]): trigger = "random"
        # else: trigger = "beam"
        
        
        # open DAQ INI file
        file = open("/var/operation/RUN/daqgifpp.ini", "w") 

        for s in res[0].splitlines():
        
            line = s.strip()
            
            if "$scanid" in line: line = line.replace("$scanid", str(scanid))
            if "$HV" in line: line = line.replace("$HV", str(opts.HV))
            if "$maxtriggers" in line: line = line.replace("$maxtriggers", str(opts.maxtriggers))
            # if "$beam" in line: line = line.replace("$beam", beam)
            if "$runtype" in line: line = line.replace("$runtype", type)
            if "$trigger" in line: line = line.replace("$trigger", trigger)
                
            file.write("%s\n" % line)

        file.close()


    if DAQTYPE == 'digitizer':

        if opts.HV is None: parser.error('Please provide HV')
        if opts.maxtriggers is None: parser.error('Please provide maxtriggers')

        # get selected configuration
        cursor.execute("SELECT d.content FROM daqini d, settings s WHERE s.setting = 'daqini_digitizer' AND s.value = d.id")
        res = cursor.fetchone()


        # open DAQ INI file
        file = open("/var/operation/RUN/daq_digitizer.ini", "w") 

        for s in res[0].splitlines():
        
            line = s.strip()
            
            if "$scanid" in line: line = line.replace("$scanid", str(scanid))
            if "$HV" in line: line = line.replace("$HV", str(opts.HV))
            if "$maxtriggers" in line: line = line.replace("$maxtriggers", str(opts.maxtriggers))
            
            file.write("%s\n" % line)

        file.close()

        ### COPY TO SERVER
        # call("sshpass -p 'UserlabGIF++' scp \"/var/operation/RUN/daq_digitizer.ini\" webdcs@pccmsrpc-server01:webdcs/RUN/", shell=True)

if opts.mapping:

    # Get all chambers in the current scan
    cursor.execute("SELECT c.* FROM hvscan_VOLTAGES v, gaps g, chambers c WHERE v.scanid = %d AND v.gapid = g.id AND g.chamberid = c.id GROUP BY c.id" % scanid)
    res = cursor.fetchall()
    
    filename = "/var/operation/HVSCAN/%06d/Mapping.csv" % scanid
    
    # Make mapping only if the file does not exist
    if os.path.isfile(filename): sys.exit()
    
    file = open(filename, "w") 
    
    for x in res: # loop over all the chambers
    
        print "Add mapping for %s" % x[1]
        # array index according to DB
        mapping = str(x[9])
        for s in mapping.splitlines():
            
            if "#" in s: continue
            else: file.write("%s\n" % s.strip().replace(":", "\t"))

    file.close()
        

if opts.dimensions:

    print "Generate dimensions.ini file"
    
    filename = "/var/operation/HVSCAN/%06d/Dimensions.ini" % scanid
    
    # Make file only if the file does not exist
    # Overwrite the Dimensions.ini file!
    #if os.path.isfile(filename): sys.exit()

    # Get all chambers in the current scan
    cursor.execute("SELECT c.* FROM hvscan_VOLTAGES v, gaps g, chambers c WHERE v.scanid = %d AND v.gapid = g.id AND g.chamberid = c.id GROUP BY c.id" % scanid)
    res = cursor.fetchall()

    # Get all unique trolleys and their slots
    # If no trolleys, consider only slots
    trolleys = {} # store trolleys, for each trolley an array of slots is created
    
    
    entries = {}

    for x in res: # loop over all the chambers

        # array index according to DB
        id = int(x[0])
        name = str(x[1])
        trolley = int(str(x[2]))
        slot = int(x[3])
        nGaps = int(x[4])
        nPartitions = int(x[5])
        entryips = int(x[6])
        area = float(x[7])
        dimensions = str(x[8])
        mapping = str(x[9])


        # make position string based on trolley and slot (e.g. T1S3 or S4)
        if trolley == 0: pos = "S%s" % (slot)
        else: pos = "T%dS%s" % (trolley, slot)

        # Get gaps in current chamber
        cursor.execute("SELECT name, area FROM gaps WHERE chamberid = %d" % id)
        gaps = cursor.fetchall()

        # Create entrying chamber for entry in Dimensions.ini file
        entry = ""
        entry += "[%s]\n" % pos
        entry += "Name=%s\n" % name
        entry += "Gaps=%d\n" % nGaps
        entry += "Partitions=%d\n" % nPartitions
        entry += "Strips=%d\n" % int(entryips/nPartitions) # entryips per partition

        for i,gap in enumerate(gaps):
            entry += "Gap%d=%s\n" % (i+1, gaps[i][0])
            entry += "AreaGap%d=%s\n" % (i+1, gaps[i][1])


        entry += dimensions # add dimensions information
        entry += "\n"
        entries[pos] = entry

        # add trolley/slot to the list
        if not trolley in trolleys: trolleys[trolley] = [slot]
        else: trolleys[trolley].append(slot)



    file = open(filename, "w") 
    file.write("# DAQ Dimensions.ini\n")
    file.write("# Generated on %s\n" % now)
    file.write("# SCAN ID %d\n" % scanid)
    file.write("[General]\n")

    file.write("nSlots=%d\n" % len(trolleys[0]))


    g = ""
    for s in trolleys[trolley]: g += str(s)
    file.write("SlotsID=%s\n" % g) # concatenation of slots

    for slot in trolleys[trolley]:
        pos = "S%s" % slot
        file.write(entries[pos])

    file.close()
    
    print "Done"
