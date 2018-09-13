import sys,os,glob
import ROOT
import MySQLdb
import shutil
from optparse import OptionParser
from subprocess import call, check_output
from array import array

import ROOT
ROOT.gROOT.SetBatch()
ROOT.gStyle.SetOptStat(0)
ROOT.gStyle.SetOptTitle(0)


# load the GIFPP library
execfile("GIFppLib.py")

parser = OptionParser()
parser.add_option("", "--chamber", dest='chamber', type='string', help="Chamber name")
(opts,args) = parser.parse_args()

if opts.chamber is None: parser.error('Please provide chamber name')


# Definition of the CMS RE scan modes
# WP > 8500, STBY < 8500, OFF < 2000
scan_modes = ["DG_WP", "SG_BOT_WP", "SG_TOP_WP", "SG_TN_WP", "SG_TW_WP", "DG_STBY", "SG_BOT_STBY", "SG_TN_STBY", "SG_TW_STBY"]
scan_labels = ["Double gap - working point", "Single gap BOT - working point", "Single gap TN+TW - working point", "Single gap TN - working point", "Single gap TW - working point", "Double gap - standby", "Single gap BOT - standby", "Single gap TN - standby", "Single gap TW - standby"]

'''
 	longevity_daily
'''

# select all runs

HVbound1 = 8500
HVbound2 = 2000

# Dict holding arrays of all the data
curr = {}
rate = {}
xTime = []
xQint = []
for mode in scan_modes:

	curr[mode] = []
	rate[mode] = []


# Select all run IDs for daily scan
db = MySQLdb.connect(host='localhost', user='root', passwd='UserlabGIF++', db='webdcs', cursorclass=MySQLdb.cursors.DictCursor)
cu = db.cursor()

runids = []
cu.execute("SELECT id FROM hvscan WHERE label = 'longevity_daily' AND id > 1935 AND id")
for l in cu.fetchall():

	if 1947 == int(l['id']): continue
	runids.append(int(l['id']))


if __name__ == "__main__":


	for id in runids:

		print "Analyze run %d" % id
		scan = GIFppLib() # load the scan object
		scan.loadScan(id) # set the scan ID
		
		pointFound = False
		#xQint.append()
		
		# loop over all the HV points in the scan
		for i in scan.getHVPoints():
		
			print " - HVPoint %s" % i

			HV_BOT = scan.getHV(opts.chamber, "BOT", i)
			HV_TN  = scan.getHV(opts.chamber, "TN", i)
			HV_TW  = scan.getHV(opts.chamber, "TW", i)

			scan_mode = ""
			if HV_BOT > HVbound2 and HV_TN > HVbound2 and HV_TW > HVbound2: scan_mode = "DG"
			if HV_BOT > HVbound2 and HV_TN < HVbound2 and HV_TW < HVbound2: scan_mode = "SG_BOT"
			if HV_BOT < HVbound2 and HV_TN > HVbound2 and HV_TW > HVbound2: scan_mode = "SG_TOP"
			if HV_BOT < HVbound2 and HV_TN > HVbound2 and HV_TW < HVbound2: scan_mode = "SG_TN"
			if HV_BOT < HVbound2 and HV_TN < HVbound2 and HV_TW > HVbound2: scan_mode = "SG_TW"

			if scan_mode == "": continue
			if max([HV_BOT, HV_TN, HV_TW]) > HVbound1: scan_mode += "_WP"
			else: scan_mode += "_STBY"
			
			if not (scan_mode == "DG_WP" or scan_mode == "DG_STBY"): continue
			
			print scan_mode

			# Get currents
			I_BOT = scan.getADC(opts.chamber, "BOT", i)*11694.25
			I_TN = scan.getADC(opts.chamber, "TN", i)*6432.00
			I_TW = scan.getADC(opts.chamber, "TW", i)*4582.82
			I_TOT = I_BOT + I_TN + I_TW
			


			# Get rates
			R_A = scan.getRate(opts.chamber, "A", i)
			R_B = scan.getRate(opts.chamber, "B", i)
			R_C = scan.getRate(opts.chamber, "C", i)
			R_TOT = scan.getRate(opts.chamber, "TOT", i)

			# Calculate charge deposition
			#area = ch['area'] # chamber area in cm2
			#charge_dep = 1e6 * I_TOT / ( R_TOT)
			#print charge_dep
			#charge_dep_err = 0
				
			# Fill data
			curr[scan_mode].append(I_TOT)
			rate[scan_mode].append(R_TOT)
			pointFound = True
			
		if pointFound:
			xTime.append(scan.time_start)



	# make the plots
	
	print len(rate["DG_STBY"])
	print len(xTime)
	#sys.exit()


	
	c = ROOT.TCanvas("c", "c", 600, 600)
	c.SetTopMargin(0.06)
	c.SetRightMargin(.05)
	c.SetBottomMargin(1)
	c.SetLeftMargin(0.12)
	
	for mode in ["DG_WP", "DG_STBY"]:
		
		for param in ["rate", "curr"]:
		
			for t in ["time", "qint"]:
			
				if t == "qint": 
					
					continue
					xLabel = "Integrated charge [mC/cm#{2}]"
					x = None
					
				else: 
				
					xLabel = "Date"
					x = xTime
	
				if param == "rate": 
				
					yLabel = "Rate [Hz/cm]"
					y = rate[mode]
				else: 
					yLabel = "Current [#muA]"
					y = curr[mode]
	
					
				g = ROOT.TGraph(len(x), array('d', x), array('d', y))
				
				g.GetXaxis().SetTitleSize(.04);
				g.GetXaxis().SetTitle(xLabel)

				if t == "time":
					g.GetXaxis().SetTimeDisplay(1);
					g.GetXaxis().SetNdivisions(-505);
					g.GetXaxis().SetTimeFormat("%d/%m %F 1970-01-01 00:00:00");	

				g.GetYaxis().SetTitleOffset(1.3)
				g.GetYaxis().SetTitleSize(.04)
				g.GetYaxis().SetTitle(yLabel)

				g.SetMarkerStyle(21)
				g.SetMarkerSize(.8)
				g.SetLineWidth(2)

				g.SetMarkerStyle(21)
				g.SetMarkerSize(.8)
				g.SetLineWidth(2)
				g.SetLineColor(ROOT.kRed)
				g.SetMarkerColor(ROOT.kRed)

				miny = .95*ROOT.TMath.MinElement(g.GetN(), g.GetY())
				maxy = 1.15*ROOT.TMath.MaxElement(g.GetN(), g.GetY())
				g.GetYaxis().SetRangeUser(miny, maxy)
				g.SetMinimum(miny)
				g.SetMaximum(maxy)

				g.Draw("ALP")


				# topText LEFT
				leftText = ROOT.TLatex()
				leftText.SetNDC()
				leftText.SetTextFont(43)
				leftText.SetTextSize(20)
				leftText.SetTextAlign(11)
				leftText.DrawLatex(.12, .95, scan_labels[scan_modes.index(mode)])

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

				c.SaveAs("/var/operation/STABILITY/SUMMARY/%s/Daily_Scan/%s_%s_%s.pdf" % (opts.chamber, param, mode, t))
				c.SaveAs("/var/operation/STABILITY/SUMMARY/%s/Daily_Scan/%s_%s_%s.png" % (opts.chamber, param, mode, t))

				c.Clear()
				