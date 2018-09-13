import sys,os,glob
import ROOT
import MySQLdb
import shutil
from optparse import OptionParser
from subprocess import call, check_output


# load the GIFPP library
execfile("GIFppLib.py")

parser = OptionParser()
parser.add_option("", "--id", dest='id', type='int', help="Run id")
(opts,args) = parser.parse_args()

if opts.id is None: parser.error('Please provide run ID')



# Definition of the CMS RE scan modes
# WP > 8500, STBY < 8500, OFF < 2000
scan_modes = ["DG_WP", "SG_BOT_WP", "SG_TOP_WP", "SG_TN_WP", "SG_TW_WP", "DG_STBY", "SG_BOT_STBY", "SG_TN_STBY", "SG_TW_STBY"]
HVbound1 = 8500
HVbound2 = 2000

if __name__ == "__main__":

	scan = GIFppLib() # load the scan object
	scan.loadScan(opts.id) # set the scan ID

	chambers = scan.getChambers() # get all the chambers in current scan
	for ch in chambers:
	
		print "Parse chamber %s" % ch['name']
		
		# loop over all the HV points in the scan
		for i in scan.getHVPoints():
		
			HV_BOT = scan.getHV(ch['name'], "BOT", i)
			HV_TN  = scan.getHV(ch['name'], "TN", i)
			HV_TW  = scan.getHV(ch['name'], "TW", i)
		
			scan_mode = ""
			if HV_BOT > HVbound2 and HV_TN > HVbound2 and HV_TW > HVbound2: scan_mode = "DG"
			if HV_BOT > HVbound2 and HV_TN < HVbound2 and HV_TW < HVbound2: scan_mode = "SG_BOT"
			if HV_BOT < HVbound2 and HV_TN > HVbound2 and HV_TW > HVbound2: scan_mode = "SG_TOP"
			if HV_BOT < HVbound2 and HV_TN > HVbound2 and HV_TW < HVbound2: scan_mode = "SG_TN"
			if HV_BOT < HVbound2 and HV_TN < HVbound2 and HV_TW > HVbound2: scan_mode = "SG_TW"
			
			if scan_mode == "": continue
			if max([HV_BOT, HV_TN, HV_TW]) > HVbound1: scan_mode += "_WP"
			else: scan_mode += "_STBY"
			
			
			# Get currents
			I_BOT = scan.getADC(ch['name'], "BOT", i)
			I_TN = scan.getADC(ch['name'], "TN", i)
			I_TW = scan.getADC(ch['name'], "TW", i)
			I_TOT = I_BOT + I_TN + I_TW

			# Get rates
			R_A = scan.getRate(ch['name'], "A", i)
			R_B = scan.getRate(ch['name'], "B", i)
			R_C = scan.getRate(ch['name'], "C", i)
			R_TOT = scan.getRate(ch['name'], "TOT", i)

			
			# Calculate charge deposition
			area = ch['area'] # chamber area in cm2
			charge_dep = 1e6 * I_TOT / ( R_TOT)
			#print charge_dep
			charge_dep_err = 0
			
			
			# store in DB
			db = MySQLdb.connect(host='localhost', user='root', passwd='UserlabGIF++', db='webdcs')
			cursor = db.cursor()
			print "Store in db"
			query = "INSERT INTO `RES_LONG_CMS-RE` VALUES ('', '%s', '%s', %s, %s, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')"

			try:
				cursor = db.cursor()

				# delete if entry exist
				cursor.execute("DELETE FROM `RES_LONG_CMS-RE` WHERE chamber = %s AND scan_mode = %s AND REF_scanid = %s", (ch['name'], scan_mode, opts.id))

				cursor.execute(query, (opts.id, i, ch['name'], scan_mode, R_TOT, R_A, R_B, R_C, I_TOT, I_BOT, I_TN, I_TW, charge_dep, charge_dep_err))
				db.commit()

			except (MySQLdb.Error, MySQLdb.Warning) as e:
				print(e)