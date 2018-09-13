#!/usr/bin/env python

#######################
# Script to calculate the integrated charge based on the currents table
# Arguments:
#	- chambername (e.g. RE2-2-NPD-BARC-9)
#	- mode: RAW or CORR

from array import array
import math
import string
import datetime				
import time
import os,sys
import glob		
import MySQLdb
from calendar import monthrange



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




tot_time = 0;
prev_qint = 0
prev_time = 0;
prevt = -1;

# prev month needs to be the starting month of the irradiation
cursor2.execute("SELECT timestamp FROM `%s` LIMIT 1" % table_qint)
first_time = cursor2.fetchall()[0][0]
prev_month = int(datetime.datetime.fromtimestamp(first_time).strftime('%m'))


qint_month = {"%d/16" % (prev_month-1) : 0} # start first month-1 as zero integrated charge (needed for calculation at the end)
time_month = {"%d/16" % (prev_month-1) : 0}
ordered_keys = [] # list of ordered keys, i.e. 10/16, 11/16, 12/16, 01/17, etc.



cursor2.execute("SELECT timestamp, QINT_TOT FROM `%s`" % table_qint)
rows = cursor2.fetchall()

for row in rows:

	t = int(row[0])
	q = float(row[1])

	if (t - prevt) < 22: tot_time += (t - prevt)
	else: pass

	prevt = t
	month = int(datetime.datetime.fromtimestamp(t).strftime('%m'))
	year = int(datetime.datetime.fromtimestamp(t).strftime('%y'))
	
	
	if month != prev_month: # if change of month --> update previous month
	
		key = "%d/%d" % (month-1, year)
		qint_month[key] = q - prev_qint
		time_month[key] = tot_time - prev_time
		ordered_keys.append(key)
		
		prev_qint = q
		prev_month = month
		prev_time = tot_time
	
	
	
# add the latest month
key = "%d/%d" % (prev_month, year)
ordered_keys.append(key)
qint_month[key] = q - prev_qint
time_month[key] = tot_time - prev_time



# write to file and screen
print "-----------------------"
f = open("/var/operation/STABILITY/SUMMARY/%s/%s.stat" % (chamber, chamber), 'w')
g = open("/var/operation/STABILITY/SUMMARY/%s/%s.time.chart" % (chamber, chamber), 'w')
h = open("/var/operation/STABILITY/SUMMARY/%s/%s.qint.chart" % (chamber, chamber), 'w')
i = open("/var/operation/STABILITY/SUMMARY/%s/%s.eff.chart" % (chamber, chamber), 'w')
print "Total irradiation time: " + str(float(1.0*tot_time/3600./24.)) + " days"
f.write("Irr_time\t%f\n" % float(1.0*tot_time/3600./24.))


for key in ordered_keys:

	print key
	
	days_in_month = monthrange(2016, int(key.split("/")[0]))[1]

	irr_time =  time_month[key]/3600./24.
	irr_qint = qint_month[key]
	irr_eff = 100.*(irr_time) / days_in_month

	f.write("%s\t%f\t%f\t%d\t%f\n" % (key, irr_time, irr_qint, days_in_month, irr_eff))
	#print "%s\t%f\t%f\t%d\t%f" % (key, irr_time, irr_qint, days_in_month, irr_eff)
	
	
	g.write('{label: "%s", y: %.1f},\n' % (key, irr_time))
	h.write('{label: "%s", y: %.1f},\n' % (key, irr_qint))
	i.write('{label: "%s", y: %.1f},\n' % (key, irr_eff))

f.close()
g.close()
h.close()
i.close()