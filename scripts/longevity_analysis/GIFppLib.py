import os,sys, math,time,glob,csv,MySQLdb
from collections import defaultdict
import MySQLdb.cursors



class GIFppLib:

	scanid = 0
	basedir = ""
	scan_type = "" # DAQ or CURRENT
	mode = "" # DG (double gap) or SG (single gap)
	
	HV_POINTS = []
	CHAMBERS = []
	DETECTORS = []
	
	csvRate = None
	csvCurr = None
	
	db = None
	cu = None
	
	time_start = None
	time_end = None
	beam = None
	source = None
	attU = None
	attD = None
	mode = None
	
	
	def test(self):
	
		print "lol"
	

	def loadScan(self, id):
	
		self.scanid = int(id)
		self.basedir = "/var/operation/HVSCAN/%06d" % id
		
		# check if scan exists
		if not os.path.exists(self.basedir): sys.exit("Scan ID not found")
	
		
		# connect to db
		self.dbInit()
		
		# get scan type
		scan_type = "CURRENT"
		
		# load CSV files 
		self.csvCurr = self.importCSV("%s/Currents.csv" % self.basedir)
		
		
		if scan_type == "DAQ":
			self.csvRate = self.importCSV("%s/Rates.csv" % self.basedir)
		
		# get scan information
		self.cu.execute("SELECT * FROM hvscan WHERE id = %s" % self.scanid)
		tmp = self.cu.fetchone()
		self.time_start = tmp['time_start']
		self.time_end = tmp['time_end']
		self.beam = tmp['beam']
		self.source = tmp['source']
		self.attU = tmp['attU']
		self.attD = tmp['attD']
		self.mode = tmp['RPC_mode']
	
		
	# get all chamber information from DB
	def getChambers(self):
	
		self.cu.execute("SELECT c.* FROM chambers c, gaps g, hvscan_VOLTAGES h WHERE h.scanid = %d AND h.detectorid = g.id AND c.id = g.chamberid GROUP BY g.chamberid" % self.scanid)
		return self.cu.fetchall()
		
	# get all gap information from DB
	def getGaps(self, chamber):
	
		gaps = []
		self.cu.execute("SELECT g.* FROM gaps g, chambers c WHERE c.name = '%s' AND g.chamberid = c.id" % chamber)
		for g in self.cu.fetchall(): gaps.append(g['name'])
		
		return sorted(gaps) # sort alphabetically
		
 
	def dbInit(self):
	
		self.db = MySQLdb.connect(host='localhost', user='root', passwd='UserlabGIF++', db='webdcs', cursorclass=MySQLdb.cursors.DictCursor)
		self.cu = self.db.cursor()
		
		
	# Function returns column based DICT of CSV file	
	def importCSV(self, fName):

		# From http://stackoverflow.com/questions/16503560/read-specific-columns-from-a-csv-file-with-csv-module
		columns = defaultdict(list) # each value in each column is appended to a list
		with open(fName) as f:
			reader = csv.DictReader(f, dialect="excel-tab")
			for row in reader:
				for (k,v) in row.items():
					if v == "": v = 0
					columns[k].append(float(v))

		return columns
	
		
	def getHV():
	
		pass
		
	def getCurrents(self, chamber, f):
	
		pass
	
	
	def getCurrent(self):
	
		pass
	
		
	def getHVPoints(self):

		return map(int, self.csvCurr['HVstep'])
		

	# Current getters
	def getHV(self, ch, gap, i):
	
		if i == -1: return map(float, self.csvCurr["HVeff_%s-%s" % (ch, gap)])
		else: return float(self.csvCurr["HVeff_%s-%s" % (ch, gap)][i-1]) # minus 1
		
	def getImon(self, ch, gap, i):
	
		if i == -1: return map(float, self.csvCurr["Imon_%s-%s" % (ch, gap)])
		else: return float(self.csvCurr["Imon_%s-%s" % (ch, gap)][i-1]) # minus 1
		
	def getImonErr(self, ch, gap, i):
	
		if i == -1: return map(float, self.csvCurr["Imon_%s-%s-err" % (ch, gap)])
		else: return float(self.csvCurr["Imon_%s-%s-err" % (ch, gap)][i-1]) # minus 1

	def getADC(self, ch, gap, i):
	
		if i == -1: return map(float, self.csvCurr["ADC_%s-%s" % (ch, gap)])
		else: return float(self.csvCurr["ADC_%s-%s" % (ch, gap)][i-1]) # minus 1
		
	# Rate getters
	def getRate(self, ch, partition, i):
	
		if i == -1: return map(float, self.csvRate["Rate-%s-%s" % (ch, partition)])
		else: return float(self.csvRate["Rate-%s-%s" % (ch, partition)][i-1]) # minus 1
		
		
	def effAttenuation(self, k):
    
		attA = ["", "", ""]
		attA[0] = 1
		attA[1] = 10
		attA[2] = 100
    
		attB = ["", "", ""]
		attB[0] = 1
		attB[1] = 1.5
		attB[2] = 100
    
		attC = ["", "", ""]
		attC[0] = 1
		attC[1] = 2.2
		attC[2] = 4.6
    
		s = map(int, list(str(k)))
		return attA[s[0]-1] * attB[s[1]-1] * attC[s[2]-1];
