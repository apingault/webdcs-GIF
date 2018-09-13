#!/usr/bin/env python

#######################
# Script to plot the current, rate and charge deposition for a source scan
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
from optparse import OptionParser
import csv
from collections import defaultdict


import ROOT
ROOT.gROOT.SetBatch()
ROOT.gStyle.SetOptStat(0)
ROOT.gStyle.SetOptTitle(0)


chambers = ["RE2-2-NPD-BARC-8", "RE2-2-NPD-BARC-9", "RE4-2-CERN-165", "RE4-2-CERN-166"]
labels = ["RE2 non-irradiated", "RE2 irradiated", "RE4 non-irradiated", "RE4 irradiated"]
partitions = ["A", "B", "C", "TOT"]
gaps = ["BOT", "TN", "TW"]
plots = { "RE2": ["RE2-2-NPD-BARC-8", "RE2-2-NPD-BARC-9"], "RE4": ["RE4-2-CERN-165", "RE4-2-CERN-166"] }


parser = OptionParser()
parser.add_option("", "--id", dest='id', type='int', help="Run id")
parser.add_option("", "--abs", dest='abs', type='int', help="Absorption factor")
(opts,args) = parser.parse_args()

if opts.id is None: parser.error('Please provide input ID')
scanid = opts.id


# Function returns column based DICT of CSV file	
def importCSV(fName):

	# From http://stackoverflow.com/questions/16503560/read-specific-columns-from-a-csv-file-with-csv-module
	columns = defaultdict(list) # each value in each column is appended to a list
	with open(fName) as f:
		reader = csv.DictReader(f, dialect="excel-tab")
		for row in reader:
			for (k,v) in row.items():
				if v == "": v = 0
				columns[k].append(float(v))

	return columns
	
	
if __name__ == "__main__":

	fRate = "/var/operation/HVSCAN/%06d/Rates.csv" % scanid
	fCurr = "/var/operation/HVSCAN/%06d/Currents.csv" % scanid

	if (not os.path.isfile(fRate)) or (not os.path.isfile(fCurr)):

		print "Rate or current file not found"
		sys.exit()


	csvRate = importCSV(fRate)
	csvCurr = importCSV(fCurr)
	
	# make directory
	dir = "/var/operation/HVSCAN/%06d/Longevity" % opts.id
	if not os.path.exists(dir): os.makedirs(dir)
	else: # remove all in directory
		filelist = glob.glob("%s/*" % dir)
		for f in filelist: os.remove(f)
	
	
	# Make canvas
	c = ROOT.TCanvas("c", "c", 600, 600)
	c.SetTopMargin(0.06)
	c.SetRightMargin(.05)
	c.SetBottomMargin(1)
	c.SetLeftMargin(0.12)
	
	leg = ROOT.TLegend(.2, 0.7, .6, .85)
	leg.SetBorderSize(0)
	leg.SetFillStyle(0)

	for ch,chs in plots.items():
	
		# Print rates
		for p in partitions:
		
			if p != "TOT": title = "Rate partition %s (ABS %s)" % (p, opts.abs)
			else: title = "Total rate (ABS %s)" % opts.abs
			
			filename = "%s_rate_partition_%s" % (ch, p)
			
			x1 = [x / 1000 for x in csvRate["HVeff-%s" % chs[0]]]
			x2 = [x / 1000 for x in csvRate["HVeff-%s" % chs[1]]]
			y1 = csvRate["Rate-%s-%s" % (chs[0], p)]
			y2 = csvRate["Rate-%s-%s" % (chs[1], p)]
			
			g1 = ROOT.TGraph(len(x1), array('d', x1), array('d', y1))
			g2 = ROOT.TGraph(len(x2), array('d', x2), array('d', y2))
	
			leg.AddEntry(g1, labels[chambers.index(chs[0])], "LP")
			leg.AddEntry(g2, labels[chambers.index(chs[1])], "LP")
			
			g1.GetXaxis().SetTitleSize(.04);
			g1.GetXaxis().SetTitle("HV_{eff} [kV]")

			g1.GetYaxis().SetTitleOffset(1.5)
			g1.GetYaxis().SetTitleSize(.04)
			g1.GetYaxis().SetTitle("Rate [Hz/cm^{2}]")
			
			g1.SetMarkerStyle(21)
			g1.SetMarkerSize(.8)
			g1.SetLineWidth(2)
			
			g2.SetMarkerStyle(21)
			g2.SetMarkerSize(.8)
			g2.SetLineWidth(2)
			g2.SetLineColor(ROOT.kRed)
			g2.SetMarkerColor(ROOT.kRed)
			
			
			#g1.GetYaxis().SetRangeUser(miny, maxy)
			g1.SetMinimum(0.5*min(min(y1), min(y2)))
			g1.SetMaximum(1.1*max(max(y1), max(y2)))
			
			g1.GetYaxis().SetLimits(0.99*min(min(y1), min(y2)), 1.01*max(max(y1), max(y2)))
			g1.GetXaxis().SetLimits(0.99*min(min(x1), min(x2)), 1.01*max(max(x1), max(x2)))
			
			g1.Draw("ALP")
			g2.Draw("SAME LP")
			

			# topText LEFT
			leftText = ROOT.TLatex()
			leftText.SetNDC()
			leftText.SetTextFont(43)
			leftText.SetTextSize(20)
			leftText.SetTextAlign(11)
			leftText.DrawLatex(.12, .95, title)

			# topText RIGHT
			right = ROOT.TLatex()
			right.SetNDC()
			right.SetTextFont(43)
			right.SetTextSize(20)
			right.SetTextAlign(31)
			right.DrawLatex(.95, .95, "")

			# CMS flag
			text1 = ROOT.TLatex()
			text1.SetTextFont(42)
			text1.SetNDC()
			text1.DrawLatex(c.GetLeftMargin()+ 0.02, 1-c.GetTopMargin()- 0.05, "#bf{CMS},#scale[0.75]{ #it{Work in progress}}");

			leg.Draw()
			
			c.SaveAs("%s/%s.png" % (dir, filename))
			c.SaveAs("%s/%s.pdf" % (dir, filename))
			c.Clear()
			leg.Clear()
		
		
		# Print currents
		for g in gaps:
		
			if g != "TOT": title = "Current gap %s" % g
			else: title = "Total current"
			
			'''
			if p != "TOT": title = "Rate partition %s (ABS %s)" % (p, opts.abs)
			else: title = "Total rate (ABS %s)" % opts.abs
			'''
			filename = "%s_current_gap_%s" % (ch, g)
			
			area = 1
			if g == "BOT": area = 11694.2
			if g == "TW": area = 4582.82
			if g == "TN": area = 6432.0
			
			x1 = [x / 1000 for x in csvCurr["HVeff_%s-%s" % (chs[0], g)]]
			x2 = [x / 1000 for x in csvCurr["HVeff_%s-%s" % (chs[1], g)]]
			y1 = [x*area for x in csvCurr["Imon_%s-%s" % (chs[0], g)]]
			y2 = [x*area for x in csvCurr["Imon_%s-%s" % (chs[1], g)]]
			
			g1 = ROOT.TGraph(len(x1), array('d', x1), array('d', y1))
			g2 = ROOT.TGraph(len(x2), array('d', x2), array('d', y2))
	
			leg.AddEntry(g1, labels[chambers.index(chs[0])], "LP")
			leg.AddEntry(g2, labels[chambers.index(chs[1])], "LP")
			
			g1.GetXaxis().SetTitleSize(.04);
			g1.GetXaxis().SetTitle("HV_{eff} [kV]")

			g1.GetYaxis().SetTitleOffset(1.5)
			g1.GetYaxis().SetTitleSize(.04)
			g1.GetYaxis().SetTitle("Current [uA]")
			
			g1.SetMarkerStyle(21)
			g1.SetMarkerSize(.8)
			g1.SetLineWidth(2)
			
			g2.SetMarkerStyle(21)
			g2.SetMarkerSize(.8)
			g2.SetLineWidth(2)
			g2.SetLineColor(ROOT.kRed)
			g2.SetMarkerColor(ROOT.kRed)
			
			
			#g1.GetYaxis().SetRangeUser(miny, maxy)
			g1.SetMinimum(0.5*min(min(y1), min(y2)))
			g1.SetMaximum(1.1*max(max(y1), max(y2)))
			
			g1.GetYaxis().SetLimits(0.99*min(min(y1), min(y2)), 1.01*max(max(y1), max(y2)))
			g1.GetXaxis().SetLimits(0.99*min(min(x1), min(x2)), 1.01*max(max(x1), max(x2)))
			
			g1.Draw("ALP")
			g2.Draw("SAME LP")
			

			# topText LEFT
			leftText = ROOT.TLatex()
			leftText.SetNDC()
			leftText.SetTextFont(43)
			leftText.SetTextSize(20)
			leftText.SetTextAlign(11)
			leftText.DrawLatex(.12, .95, title)

			# topText RIGHT
			right = ROOT.TLatex()
			right.SetNDC()
			right.SetTextFont(43)
			right.SetTextSize(20)
			right.SetTextAlign(31)
			right.DrawLatex(.95, .95, "")

			# CMS flag
			text1 = ROOT.TLatex()
			text1.SetTextFont(42)
			text1.SetNDC()
			text1.DrawLatex(c.GetLeftMargin()+ 0.02, 1-c.GetTopMargin()- 0.05, "#bf{CMS},#scale[0.75]{ #it{Work in progress}}");

			leg.Draw()
			
			c.SaveAs("%s/%s.png" % (dir, filename))
			c.SaveAs("%s/%s.pdf" % (dir, filename))
			c.Clear()
			leg.Clear()	
		
		
		
		
		
		
		
		
		
		
		
	
	sys.exit()
	
	rateGraphs = {}
	currGraphs = {}
	chDepGraphs = {}


	for ch in chambers:

		# load the rates
		HV = csvRate["HVeff-%s" % ch]
		for p in partitions:
			n = "Rate-%s-%s" % (ch, p)
			r = csvRate[n]
			gr = ROOT.TGraph(len(HV), array('d', HV), array('d', r))
			rateGraphs[n] = gr


		# load the currents
		for g in gaps:
			HV = csvCurr["HVeff_%s-%s" % (ch, g)]
			n = "Imon_%s-%s" % (ch, g)
			r = csvCurr[n]
			print r
			gr = ROOT.TGraph(len(HV), array('d', HV), array('d', r))
			currGraphs[n] = gr


		# calculate the charge deposition (only for TOT rate and TOT current
		
		
		
	# Make the plots
	
	c = ROOT.TCanvas("c", "c", 600, 600)
	c.SetTopMargin(0.06)
	c.SetRightMargin(.05)
	c.SetBottomMargin(1)
	c.SetLeftMargin(0.12)

	for p,chs in plots.items():
	
		for p in partitions:
		
			if p != "TOT": title = "Mean rate partition %s" % p
			else: title = "Total mean rate"
		
			leg = ROOT.TLegend(.2, 0.7, .6, .85)
			leg.SetBorderSize(0)
			leg.SetFillStyle(0)
		
			n1 = "Rate-%s-%s" % (chs[0], p)
			n2 = "Rate-%s-%s" % (chs[1], p)
			
			leg.AddEntry(g1, labels[chambers.index(chs[0])], "LP")
			leg.AddEntry(rateGraphs[n2], labels[chambers.index(chs[1])], "LP")
			
			g1.GetXaxis().SetTitleSize(.04);
			g1.GetXaxis().SetTitle("HV_{eff} [V]")

			g1.GetYaxis().SetTitleOffset(1.3)
			g1.GetYaxis().SetTitleSize(.04)
			g1.GetYaxis().SetTitle("Rate [Hz/cm]")
			
			g1.SetMarkerStyle(21)
			g1.SetMarkerSize(.8)
			g1.SetLineWidth(2)
			
			rateGraphs[n2].SetMarkerStyle(21)
			rateGraphs[n2].SetMarkerSize(.8)
			rateGraphs[n2].SetLineWidth(2)
			rateGraphs[n2].SetLineColor(ROOT.kRed)
			rateGraphs[n2].SetMarkerColor(ROOT.kRed)
			
			miny = min(ROOT.TMath.MinElement(rateGraphs[n2].GetN(), rateGraphs[n2].GetY()), ROOT.TMath.MinElement(g1.GetN(), g1.GetY()))
			maxy = max(ROOT.TMath.MaxElement(rateGraphs[n2].GetN(), rateGraphs[n2].GetY()), ROOT.TMath.MaxElement(g1.GetN(), g1.GetY()))
			rateGraphs[n2].GetYaxis().SetRangeUser(miny, maxy)
			rateGraphs[n2].SetMinimum(miny)
			rateGraphs[n2].SetMaximum(maxy)
			
			g1.Draw("ALP")
			rateGraphs[n2].Draw("SAME LP")
			

			# topText LEFT
			leftText = ROOT.TLatex()
			leftText.SetNDC()
			leftText.SetTextFont(43)
			leftText.SetTextSize(20)
			leftText.SetTextAlign(11)
			leftText.DrawLatex(.12, .95, title)

			# topText RIGHT
			right = ROOT.TLatex()
			right.SetNDC()
			right.SetTextFont(43)
			right.SetTextSize(20)
			right.SetTextAlign(31)
			right.DrawLatex(.95, .95, "")

			# CMS flag
			text1 = ROOT.TLatex()
			text1.SetTextFont(42);
			text1.SetNDC();
			text1.DrawLatex(c.GetLeftMargin()+ 0.02, 1-c.GetTopMargin()- 0.05, "#bf{CMS},#scale[0.75]{ #it{Work in progress}}");

			leg.Draw()
			
			c.SaveAs("/var/operation/HVSCAN/test.pdf")
			c.Clear()
			
		continue
			
		for g in gaps:
		
			if g != "TOT": title = "Currtne gap %s" % p
			else: title = "Total current"
		
			leg = ROOT.TLegend(.2, 0.7, .6, .85)
			leg.SetBorderSize(0)
			leg.SetFillStyle(0)
		
			n1 = "Imon_%s-%s" % (chs[0], g)
			n2 = "Imon_%s-%s" % (chs[1], g)
			
			leg.AddEntry(currGraphs[n1], labels[chambers.index(chs[0])], "LP")
			leg.AddEntry(currGraphs[n2], labels[chambers.index(chs[1])], "LP")
			
			currGraphs[n1].GetXaxis().SetTitleSize(.04);
			currGraphs[n1].GetXaxis().SetTitle("HV_{eff} [V]")

			currGraphs[n1].GetYaxis().SetTitleOffset(1.3)
			currGraphs[n1].GetYaxis().SetTitleSize(.04)
			currGraphs[n1].GetYaxis().SetTitle("Current [uA/cm]")
			
			currGraphs[n1].SetMarkerStyle(21)
			currGraphs[n1].SetMarkerSize(.8)
			currGraphs[n1].SetLineWidth(2)
			
			currGraphs[n2].SetMarkerStyle(21)
			currGraphs[n2].SetMarkerSize(.8)
			currGraphs[n2].SetLineWidth(2)
			currGraphs[n2].SetLineColor(ROOT.kRed)
			currGraphs[n2].SetMarkerColor(ROOT.kRed)
			
			# Set min/max
			miny = min(ROOT.TMath.MinElement(currGraphs[n2].GetN(), currGraphs[n2].GetY()), ROOT.TMath.MinElement(currGraphs[n1].GetN(), currGraphs[n1].GetY()))
			maxy = max(ROOT.TMath.MaxElement(currGraphs[n2].GetN(), currGraphs[n2].GetY()), ROOT.TMath.MaxElement(currGraphs[n1].GetN(), currGraphs[n1].GetY()))
			currGraphs[n2].GetYaxis().SetRangeUser(miny, maxy)
			currGraphs[n2].SetMinimum(miny)
			currGraphs[n2].SetMaximum(maxy)
			
			
			currGraphs[n1].Draw("ALP")
			currGraphs[n2].Draw("SAME LP")
			

			# topText LEFT
			leftText = ROOT.TLatex()
			leftText.SetNDC()
			leftText.SetTextFont(43)
			leftText.SetTextSize(20)
			leftText.SetTextAlign(11)
			leftText.DrawLatex(.12, .95, title)

			# topText RIGHT
			right = ROOT.TLatex()
			right.SetNDC()
			right.SetTextFont(43)
			right.SetTextSize(20)
			right.SetTextAlign(31)
			right.DrawLatex(.95, .95, "")

			# CMS flag
			text1 = ROOT.TLatex()
			text1.SetTextFont(42);
			text1.SetNDC();
			text1.DrawLatex(c.GetLeftMargin()+ 0.02, 1-c.GetTopMargin()- 0.05, "#bf{CMS},#scale[0.75]{ #it{Work in progress}}");

			leg.Draw()
			
			c.SaveAs("/var/operation/HVSCAN/test.pdf")
			c.Clear()