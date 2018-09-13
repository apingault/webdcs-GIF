#!/usr/bin/env python

#######################
# Script to plot the current and integrated charge
# Arguments:
#	- chambername (e.g. RE2-2-NPD-BARC-9)
#	- plot: QINT or CURR
#	- mode: RAW or CORR
#	- run ids: ALL or comma separated list 

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

db = MySQLdb.connect(host='localhost', user='root', passwd='UserlabGIF++', db='LONGEVITY')
cursor = db.cursor()

db1 = MySQLdb.connect(host='localhost', user='root', passwd='UserlabGIF++', db='webdcs')
cursor1 = db1.cursor()

# Parse arguments
if len(sys.argv) != 5: sys.exit(0)
chamber = str(sys.argv[1])
plot = str(sys.argv[2])
mode = str(sys.argv[3])
runids = str(sys.argv[4])


gaps = ["BOT", "TN", "TW", "TOT"]
qint_table = mode + "_QINT_" + chamber
output = "/var/operation/STABILITY/SUMMARY/" + chamber + "/"



if plot == "QINT":
	table = mode + "_QINT_" + chamber
	yAxisTitle = "Integrated Charge [mC/cm^{2}]"
	drawStyle = "AL"
elif plot == "CURR":
	table = mode + "_CURR_" + chamber
	yAxisTitle = "Current [#muA]"
	drawStyle = "AP"
else: sys.exit(0)


# Get latest integrated charge values
cursor.execute("SELECT * FROM `" + qint_table + "` ORDER BY timestamp DESC LIMIT 1")
qint_data = cursor.fetchall()
qint = {}
qint["BOT"] = qint_data[0][2]
qint["TN"] = qint_data[0][3]
qint["TW"] = qint_data[0][4]
qint["TOT"] = qint_data[0][5]


# Init graphs
graphs = {}
points = {} # needed for ordering TGraph
for gap in gaps:

	g = ROOT.TGraph()
	graphs[gap] = g
	points[gap] = 0

# Make correct WHERE clausule selecting the correct RUN IDs
# + change output
runids_sql = ""
if not "ALL" in runids:
	if not ',' in runids: # if single run ID --> store in corresponding directory
		runids_sql = "WHERE RUN_ID = " + runids
		output = "/var/operation/STABILITY/%06d/plots/"%int(runids)
	else: # if multiple id's --> store in summary directory
		runids_sql = "WHERE RUN_ID = " + runids
		runids_sql = "WHERE RUN_ID = " + runids.replace(",", " OR RUN_ID = ")
		output = "/var/operation/STABILITY/SUMMARY/%s/TMP/" % chamber


	
if not os.path.exists(output): os.makedirs(output)	

# Query the results
cursor.execute("SELECT * FROM `" + table + "` " + runids_sql + " ORDER BY timestamp ASC")
	
### INTEGRATED CHARGE
if plot == "QINT":

	for row in cursor.fetchall():

		timestamp = row[0]
		RUN_ID = row[1]
		QINT_BOT = row[2]
		QINT_TN = row[3]
		QINT_TW = row[4]
		QINT_TOT = row[5]

		graphs['BOT'].SetPoint(points['BOT'], float(timestamp), float(QINT_BOT))
		points['BOT'] += 1

		graphs['TN'].SetPoint(points['TN'], float(timestamp), float(QINT_TN))
		points['TN'] += 1

		graphs['TW'].SetPoint(points['TW'], float(timestamp), float(QINT_TW))
		points['TW'] += 1

		graphs['TOT'].SetPoint(points['TOT'], float(timestamp), float(QINT_TOT))
		points['TOT'] += 1
		
	
### CURRENT
if plot == "CURR":
	
	for row in cursor.fetchall():

		timestamp = row[0]
		RUN_ID = row[1]
		I_BOT = row[2]
		I_TN = row[3]
		I_TW = row[4]
		I_TOT = row[5]
		HVEFF_BOT = row[6]
		HVEFF_TN = row[7]
		HVEFF_TW = row[8]
		STAT_BOT = row[9]
		STAT_TN = row[10]
		STAT_TW = row[11]
		SOURCE = row[12]

		if SOURCE != 1: continue; # source status must be 1!

		# BOT
		if STAT_BOT == 1 and HVEFF_BOT > 8000:

			graphs['BOT'].SetPoint(points['BOT'], float(timestamp), float(I_BOT))
			points['BOT'] += 1

		# TN
		if STAT_TN == 1 and HVEFF_TN > 8000:

			graphs['TN'].SetPoint(points['TN'], float(timestamp), float(I_TN))
			points['TN'] += 1

		# TW
		if STAT_TW == 1 and HVEFF_TW > 8000:

			graphs['TW'].SetPoint(points['TW'], float(timestamp), float(I_TW))
			points['TW'] += 1

		# TOT
		if STAT_BOT == 1 and HVEFF_BOT > 8000 and STAT_TW == 1 and HVEFF_TW > 8000 and STAT_TW == 1 and HVEFF_TW > 8000:

			graphs['TOT'].SetPoint(points['TOT'], float(timestamp), float(I_TOT))
			points['TOT'] += 1

		
###############################################

c=ROOT.TCanvas("c", "c", 600, 600)
c.SetTopMargin(0.06)
c.SetRightMargin(.05)
c.SetBottomMargin(1)
c.SetLeftMargin(0.12)

for gap in gaps:

	# get full gap name
	if not "TOT" in gap:
	
		cursor1.execute("SELECT name FROM `detectors` WHERE gap = '" + gap + "' AND chamber = '" + chamber + "' ")
		tmp = cursor1.fetchall()
		gapname = tmp[0][0]
	
	else: gapname = chamber + "-TOT"
	
	if mode == "RAW": gapname += " (raw)"
	else: gapname += " (corrected)"
	
	print gapname

	graphs[gap].GetXaxis().SetTimeDisplay(1);
	graphs[gap].GetXaxis().SetNdivisions(-506);
	graphs[gap].GetXaxis().SetTimeFormat("%d/%m %F 1970-01-01 00:00:00");
	graphs[gap].GetXaxis().SetTitleSize(.04);
	graphs[gap].GetXaxis().SetTitle("Date");

	graphs[gap].GetYaxis().SetTitleOffset(1.3);
	graphs[gap].GetYaxis().SetTitleSize(.04);
	graphs[gap].GetYaxis().SetTitle(yAxisTitle);
	
	graphs[gap].SetFillStyle(1001);
	#graphs['BOT'].SetMarkerStyle(0.1);
	graphs[gap].SetLineColor(2);
	graphs[gap].SetLineWidth(2);
	graphs[gap].SetMarkerColor(ROOT.kRed);

	# Ranges for CURRENT plots
	if plot == "CURR":
		ymin = 0.8*graphs[gap].GetHistogram().GetMinimum();
		ymax = 1.2*graphs[gap].GetHistogram().GetMaximum();
		print ymax,ymin
		graphs[gap].GetYaxis().SetRangeUser(ymin, ymax);

	graphs[gap].Draw(drawStyle)

	# topText LEFT
	leftText = ROOT.TLatex()
	leftText.SetNDC()
	leftText.SetTextFont(43)
	leftText.SetTextSize(20)
	leftText.SetTextAlign(11)
	leftText.DrawLatex(.12, .95, gapname)

	# topText RIGHT
	right = ROOT.TLatex()
	right.SetNDC()
	right.SetTextFont(43)
	right.SetTextSize(20)
	right.SetTextAlign(31)
	right.DrawLatex(.95, .95, "%d mC/cm^{2}"%qint[gap])
	
	# CMS flag
	text1 = ROOT.TLatex()
	text1.SetTextFont(42);
	text1.SetNDC();
	text1.DrawLatex(c.GetLeftMargin()+ 0.02, 1-c.GetTopMargin()- 0.05, "#bf{CMS},#scale[0.75]{ #it{Work in progress}}");

	if not "ALL" in runids:
		text2 = ROOT.TLatex()
		text2.SetNDC();
		text2.SetTextFont(43);
		text2.SetTextSize(20);
		text2.DrawLatex(c.GetLeftMargin()+ 0.02, 1-c.GetTopMargin()- 0.10, "RUNID: %s"%runids);

	
	
	
	c.SaveAs(output + chamber + "-" + gap + "_" + plot + "_" + mode + ".pdf")
	c.SaveAs(output + chamber + "-" + gap + "_" + plot + "_" + mode + ".png")

	c.Clear()
	
c.Close()
