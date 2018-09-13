#!/usr/bin/env python

#######################
# Script to calculate the integrated charge based on the currents table
# Arguments:
#	- chambername (e.g. RE2-2-NPD-BARC-9)
#	- mode: RAW or CORR

from array import array
import math
import string
from datetime import datetime					
import time
import os,sys
import glob		
import MySQLdb

import ROOT
ROOT.gROOT.SetBatch()
ROOT.gStyle.SetOptStat(0)
ROOT.gStyle.SetOptTitle(0)

db1 = MySQLdb.connect(host='localhost', user='root', passwd='UserlabGIF++', db='webdcs')
cursor1 = db1.cursor()

db2 = MySQLdb.connect(host='localhost', user='root', passwd='UserlabGIF++', db='LONGEVITY')
cursor2 = db2.cursor()

rootPath = os.path.dirname(os.path.abspath(__file__))


# Parse arguments
if len(sys.argv) != 3: sys.exit(0)
chamber = str(sys.argv[1])
mode = str(sys.argv[2])

table_current = mode + "_CURR_" + chamber
table_qint = mode + "_QINT_" + chamber

# Get all gaps in current chamber and store their area
area = {}
cursor1.execute("SELECT gap, area FROM detectors WHERE chamber = '" + chamber + "' ORDER BY gap")
g = cursor1.fetchall()
for gap in g: 
	area[gap[0]] = gap[1]

### OFFSETS ###
if chamber == "RE2-2-NPD-BARC-9":

	qint_BOT = 86958071.2 / 1000 / area['BOT']
	qint_TN = 44708832.0 / 1000 / area['TN']
	qint_TW = 52271644.92 / 1000 / area['TW']

else:

	qint_BOT = 0.0
	qint_TN = 0.0
	qint_TW = 0.0

qint_TOT = (qint_BOT*area['BOT'] + qint_TN*area['TN'] + qint_TW*area['TW']) / area['BOT']
	

# Select all RUN IDs
cursor2.execute("SELECT RUN_ID FROM `" + table_current + "` GROUP BY RUN_ID ORDER BY RUN_ID ASC")
ids = cursor2.fetchall()


# Open tmp file to store all the data
file = open(chamber + ".tmp", 'w')

for id in ids:
	
	# select all data for current RUN
	cursor2.execute("SELECT * FROM `" + table_current + "` WHERE SOURCE = 1 AND RUN_ID = " + str(id[0]) + " ORDER BY timestamp ASC")
	data = cursor2.fetchall()
	
	for i in range(0, len(data)-1):
	
		# BOT
		if data[i][9] == 1:
			qint_BOT += 1.0e-3 * (data[i+1][0] - data[i][0]) * (data[i+1][2] + data[i][2]) / 2 / area['BOT']

		# TN
		if data[i][10] == 1:
			qint_TN += 1.0e-3 * (data[i+1][0] - data[i][0]) * (data[i+1][3] + data[i][3]) / 2 / area['TN']

		# TW
		if data[i][11] == 1:
			qint_TW += 1.0e-3 * (data[i+1][0] - data[i][0]) * (data[i+1][4] + data[i][4]) / 2 / area['TW']

		# TOT
		qint_TOT = (qint_BOT*area['BOT'] + qint_TN*area['TN'] + qint_TW*area['TW']) / area['BOT']
	
		# Insert into DB
		s = str(data[i][0]) + "," + str(id[0]) + "," + str(qint_BOT) + "," + str(qint_TN) + "," + str(qint_TW) + "," + str(qint_TOT) + "\n"
		#if not i == (len(data) -1): s += 
		file.write(s)


file.close()
	

# Empty the DB
query = "TRUNCATE TABLE `" + table_qint + "`"
cursor2.execute(query)
db2.commit()

	
# Load file in DB
query = "LOAD DATA LOCAL INFILE '" + chamber + ".tmp' INTO TABLE `" + table_qint + "` FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'"
cursor2.execute(query)
db2.commit()

# Remove the file
os.remove(chamber + ".tmp")