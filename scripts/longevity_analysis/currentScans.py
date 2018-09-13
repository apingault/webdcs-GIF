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
parser.add_option("", "--id", dest='id', type='int', help="Run id")
(opts,args) = parser.parse_args()

if opts.id is None: parser.error('Please provide input ID')
scanid = opts.id


xLabel = "HV_{eff} [kV]"
yLabel = "Current [#muA]"

chambers = ["RE2-2-NPD-BARC-8", "RE2-2-NPD-BARC-9", "RE4-2-CERN-165", "RE4-2-CERN-166"]
labels = ["RE2 non-irradiated", "RE2 irradiated", "RE4 non-irradiated", "RE4 irradiated"]


# Dict holding arrays of all the data
hv =  {}
curr_TOT = {}
curr_BOT = {}
curr_TN = {}
curr_TW = {}

for ch in chambers:

	hv[ch] = []
	curr_TOT[ch] = []
	curr_BOT[ch] = []
	curr_TN[ch] = []
	curr_TW[ch] = []
	
def makePlot(names, graphs, filename, textleft, textright):

	colors = [ROOT.kRed, ROOT.kBlack, ROOT.kBlue, ROOT.kGreen]

	if len(graphs) > 5: 
		print "Too many graphs"
		return

	c = ROOT.TCanvas("c", "c", 600, 600)
	c.SetTopMargin(0.06)
	c.SetRightMargin(.05)
	c.SetBottomMargin(1)
	c.SetLeftMargin(0.12)
	
	leg = ROOT.TLegend(.2, 0.85-len(graphs)*0.05, .6, .85)
	#leg.SetTextAlign(11)
	leg.SetBorderSize(0)
	leg.SetFillStyle(0)
	ROOT.gStyle.SetLegendTextSize(0.03)
		
	
	miny = 1e10
	maxy = -1e10
	for i in range(0, len(graphs)):
	
		if i == 0:
		
			graphs[i].GetXaxis().SetTitleSize(.04)
			graphs[i].GetXaxis().SetTitle(xLabel)

			graphs[i].GetYaxis().SetTitleOffset(1.3)
			graphs[i].GetYaxis().SetTitleSize(.04)
			graphs[i].GetYaxis().SetTitle(yLabel)
			

		graphs[i].SetMarkerStyle(21)
		graphs[i].SetMarkerSize(.6)
		graphs[i].SetLineWidth(2)
		graphs[i].SetLineColor(colors[i])
		graphs[i].SetMarkerColor(colors[i])
		
		leg.AddEntry(graphs[i], names[i], "L")

		# update the min/max values
		min = ROOT.TMath.MinElement(graphs[i].GetN(), graphs[i].GetY())
		if min < miny: miny = min
		max = 1.15*ROOT.TMath.MaxElement(graphs[i].GetN(), graphs[i].GetY())
		if max > maxy: maxy = max
		
	for i in range(0, len(graphs)):
	
		if i == 0:
		
			graphs[i].GetYaxis().SetRangeUser(0.95*miny, 1.05*maxy)
			graphs[i].SetMinimum(miny)
			graphs[i].SetMaximum(maxy)
			graphs[i].Draw("AL")
		
		else:
			
			graphs[i].Draw("SAME")


	# topText LEFT
	leftText = ROOT.TLatex()
	leftText.SetNDC()
	leftText.SetTextFont(43)
	leftText.SetTextSize(20)
	leftText.SetTextAlign(11)
	leftText.DrawLatex(.12, .95, "")

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
		
	c.SaveAs("%s.png" % (filename))
	c.SaveAs("%s.pdf" % (filename))
	c.Clear()
	leg.Clear()
	c.Close()
			


	
	



if __name__ == "__main__":

	print "Analyze run %d" % opts.id
	scan = GIFppLib() # load the scan object
	scan.loadScan(opts.id) # set the scan ID
	
	# Make directory
	dir = "/var/operation/HVSCAN/%06d/Longevity" % opts.id
	if not os.path.exists(dir): os.makedirs(dir)
	
	for ch in chambers:
		
		# loop over all the HV points in the scan and construct arrays of HV/currents/etc
		for i in scan.getHVPoints():

			print " - HVPoint %s" % i

			HV_BOT = scan.getHV(ch, "BOT", i)
			HV_TN  = scan.getHV(ch, "TN", i)
			HV_TW  = scan.getHV(ch, "TW", i)

			# Get currents
			I_BOT = scan.getADC(ch, "BOT", i)*11694.25
			I_TN = scan.getADC(ch, "TN", i)*6432.00
			I_TW = scan.getADC(ch, "TW", i)*4582.82
			I_TOT = I_BOT + I_TN + I_TW

			hv[ch].append(HV_BOT) # assume same HV for each gap
			curr_TOT[ch].append(I_TOT)
			curr_BOT[ch].append(I_BOT)
			curr_TN[ch].append(I_TN)
			curr_TW[ch].append(I_TW)

		# convert to TGraphs
		g_TOT = ROOT.TGraph(len(hv[ch]), array('d', hv[ch]), array('d', curr_TOT[ch]))
		g_BOT = ROOT.TGraph(len(hv[ch]), array('d', hv[ch]), array('d', curr_BOT[ch]))
		g_TN = ROOT.TGraph(len(hv[ch]), array('d', hv[ch]), array('d', curr_TN[ch]))
		g_TW = ROOT.TGraph(len(hv[ch]), array('d', hv[ch]), array('d', curr_TW[ch]))
			
		# make the plot for this chamber
		
		names = ["%s BOT" % labels[chambers.index(ch)], "%s TN" % labels[chambers.index(ch)], "%s TW" % labels[chambers.index(ch)]]
		plots = [g_BOT, g_TN, g_TW]
		filename = "%s/%s-ALL" % (dir, ch)
		makePlot(names, plots, filename, "", "")

		names = ["%s BOT" % labels[chambers.index(ch)]]
		plots = [g_BOT]
		filename = "%s/%s-BOT" % (dir, ch)
		makePlot(names, plots, filename, "", "")
		
		names = ["%s TN" % labels[chambers.index(ch)]]
		plots = [g_TN]
		filename = "%s/%s-TN" % (dir, ch)
		makePlot(names, plots, filename, "", "")
		
		names = ["%s TW" % labels[chambers.index(ch)]]
		plots = [g_TW]
		filename = "%s/%s-TW" % (dir, ch)
		makePlot(names, plots, filename, "", "")