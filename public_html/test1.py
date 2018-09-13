import glob
import os
import sys
import ROOT

for i in range(30, 3000):

	dir = "/var/operation/HVSCAN/%06d" % i
	if not os.path.exists(dir): continue
	
	files = glob.glob("%s/Scan*DAQ.root" % dir)
	CAEN = glob.glob("%s/Scan*CAEN.root" % dir)
	
	if len(files) != len(CAEN) and len(files) > 0: 

		print i
		#print 
		#print files
		#print
		#print CAEN
		
		#print "------------------------------"

	# Old format:  Scan001326_Run20160607180535_HV2_DIP.root 
	# New format Scan001326_HV2_DIP.root
	for f in files:
		
		fOut = ROOT.TFile.Open(f)
		hist = fOut.Get("ID")
		if hist == None: print "Cannot open file",f
		
	
		pass
		
