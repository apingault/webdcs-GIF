import sys,os,glob
import ROOT
import MySQLdb
import shutil
from optparse import OptionParser
from subprocess import call, check_output



ROOT.gROOT.SetBatch()
#ROOT.gStyle.SetOptStat(0)
#ROOT.gStyle.SetOptTitle(0)

parser = OptionParser()
parser.add_option("", "--id", dest='id', type='int', help="Run id")
(opts,args) = parser.parse_args()

if opts.id is None: parser.error('Please provide run ID')
dir = "/var/operation/HVSCAN/%06d" % opts.id



def plotHistos(file, dir):

    fIn = ROOT.TFile.Open(file)
    if not fIn: return
    fIn.cd()

    c = ROOT.TCanvas();
    #ROOT.gStyle.SetOptStat(000002211)
    #ROOT.gStyle.SetOptFit(1111);

    dirList = ROOT.gDirectory.GetListOfKeys()
    print dirList
    for k1 in dirList:

        hist = fIn.Get(k1.GetName())
        if "mask" in hist.GetName(): continue

        hist.SetFillColor(ROOT.kBlue)
        hist.Draw("HIST")
        hist.GetYaxis().SetRangeUser(0, 1.1*hist.GetMaximum())
        hist.SetMinimum(0)
        hist.SetLineWidth(0)


        if "Strip_Mean_Noise" in hist.GetName() or "Strip_Activity" in hist.GetName():

            hist1 = fIn.Get("mask_" + k1.GetName())
            hist1.SetFillColor(ROOT.kRed)
            hist1.SetLineWidth(0)
            hist1.Draw("SAME HIST")

            max1 = hist.GetMaximum()
            max2 = hist1.GetMaximum()
            hist.GetYaxis().SetRangeUser(0, 1.1*max(max1, max2))

        if "Strip_Mean_Noise" in hist.GetName():

            mean = 0
            noNonEmpty = 0
            for i in range(1, hist.GetNbinsX()+1):

                mean += hist.GetBinContent(i)
                if hist.GetBinContent(i) != 0: noNonEmpty += 1

                if noNonEmpty == 0: mean = 0
                else: mean = mean/noNonEmpty

            # top text right
            right = ROOT.TLatex()
            right.SetNDC()
            right.SetTextFont(43)
            right.SetTextSize(20)
            right.SetTextAlign(13)
            right.DrawLatex(.15,.85,"Mean rate: %.1f Hz/cm^{2}" % mean)


        c.SaveAs("%s/%s.pdf" % (dir, hist.GetName()))
        c.SaveAs("%s/%s.png" % (dir, hist.GetName()))
        c.Clear()

    c.Close()
    fIn.Close()




if __name__ == "__main__":

    '''
    DQM Tasks:

    1) clean the current DQM folder: remove HVnn directories, remove rate file, remove lines in log file
    2) loop over all the valid runs (HVpoints) and make directories
    3) for DAQ runs, generate the Dimensions.ini file
    4) for each run, execute the offline to produce the DAQ-Rate file
    5) for each run, loop over all histos in DAQ-rate and CAEN files + make plots of each histo
    6)

    '''

    db = MySQLdb.connect(host='localhost', user='root', passwd='UserlabGIF++', db='webdcs')
    cursor = db.cursor()

    # Get scan details
    cursor.execute("SELECT type FROM hvscan WHERE id = %d LIMIT 1" % opts.id)
    g = cursor.fetchone()
    HVscan_type = g[0] # daq or current

    # Clean the log file, remove lines starting with [Offline
    call("sed '/\[Offline/d' %s/log.txt > %s/log.tmp" % (dir, dir), shell = True)
    call("mv %s/log.tmp %s/log.txt" % (dir, dir), shell = True)

    # Delete DAQ-Rate files
    if HVscan_type == "daq":
        call("rm %s/*_DAQ-Rate.root" % (dir), shell = True) # remove OLD Daq-rate root files
        call("rm %s/*_Offline.root" % (dir), shell = True)

    # Generate Dimensions.ini (will be generated only if it does not exist)
    # for both DAQ and current scan
    call("python /home/webdcs/software/webdcs/CAEN/python/generateDAQFiles.py --id %d --dimensions" % opts.id, shell = True)

    # Generate mapping (will be generated only if it does not exist)
    if HVscan_type == "daq":
        call("python /home/webdcs/software/webdcs/CAEN/python/generateDAQFiles.py --id %d --mapping" % opts.id, shell = True)



    # TODO add status flag
    cursor.execute("SELECT HVpoint FROM hvscan_VOLTAGES WHERE scanid = %d GROUP BY HVpoint" % opts.id)
    g = cursor.fetchall()
    HVpoints = []
    for i in g: HVpoints.append(int(i[0]))


    # loop over all points
    for HV in HVpoints:

        HVdir = "%s/HV%d" % (dir, HV)
        file = "%s/Scan%06d_HV%d" % (dir, opts.id, HV)

        # Remove HVdir
        # if os.path.exists(HVdir): shutil.rmtree(HVdir)
        if os.path.exists(HVdir) and not os.path.exists("%s_backup/" % HVdir):
            shutil.copytree(HVdir, "%s_backup/" % HVdir)
        if os.path.exists("%s/CAEN" % HVdir):
            shutil.rmtree("%s/CAEN" % HVdir)

        # Make new dir
        # os.makedirs(HVdir)

        # Make CAEN plots
        os.makedirs("%s/CAEN" % HVdir)
        plotHistos("%s_CAEN.root" % file, "%s/CAEN" % HVdir)



        # Make Rate plots plots
        if HVscan_type == "daq":

            cursor.execute("SELECT daqtype FROM hvscan_DAQ WHERE id = %d LIMIT 1" % opts.id)
            g = cursor.fetchone()
            daqtype = g[0]

            if daqtype == "default":

                # Make new DAQ-Rate file and make CSV files
                cmd = "cd /home/webdcs/software/GIF_OfflineAnalysis;"
                # cmd = "cd /home/onanalysis/software/GIF_OfflineAnalysis && "
                cmd += "./bin/offlineanalysis /var/operation/HVSCAN/%06d/Scan%06d_HV%d > dqmOfflineLog.txt 2>&1" % (opts.id, opts.id, HV)
                # cmd += "./bin/offlineanalysis /var/webdcs/HVSCAN/%06d/Scan%06d_HV%d > /dev/null 2>&1 " % (opts.id, opts.id, HV)
                # cmd += "./bin/offlineanalysis /var/operation/HVSCAN/%06d/Scan%06d_HV%d > /dev/null 2>&1 " % (opts.id, opts.id, HV)

                call(cmd, shell=True)

                # Plot histograms
                if os.path.exists("%s/DAQ" % HVdir): shutil.rmtree("%s/DAQ" % HVdir)
                os.makedirs("%s/DAQ" % HVdir)
                plotHistos("%s_Offline.root" % file, "%s/DAQ" % HVdir)

            elif daqtype == "lyondaq":

                # get run number LYONDAQ_HV1.cfg
                fname = "/var/operation/HVSCAN/%06d/LYONDAQ_HV%d.cfg" % (opts.id, HV)
                f = open(fname, "r")
                runid = f.read()
                f.close()

                daqfile = "WR_%d_%s.root" % (opts.id, runid)
                print daqfile
                cmd = "sshpass -p 'RPC_2008' scp -P 22  acqilc@lyosdhcal8:/home/shchablo/dataQuest/results/webdcs/%s /var/operation/HVSCAN/%06d" % (daqfile, opts.id)

                print cmd
                if os.path.exists("%s/DAQ" % HVdir): shutil.rmtree("%s/DAQ" % HVdir)
                os.makedirs("%s/DAQ" % HVdir)
                call(cmd, shell = True)
                plotHistos("/var/operation/HVSCAN/%06d/%s" % (opts.id, daqfile), "%s/DAQ" % HVdir)

                sys.exit()


    # Merge the CSV files
    call("rm %s/Currents.csv" % (dir), shell = True)
    call("cat %s/Offline-Current-Header.csv %s/Offline-Current.csv > %s/test.csv" % (dir, dir, dir), shell = True)
    call("mv %s/test.csv %s/Currents.csv" % (dir, dir), shell = True)
    if HVscan_type == "daq":
        call("rm %s/Rates.csv" % (dir), shell = True)
        call("cat %s/Offline-Rate-Header.csv %s/Offline-Rate.csv > %s/test.csv" % (dir, dir, dir), shell = True)
        call("mv %s/test.csv %s/Rates.csv" % (dir, dir), shell = True)

        #call("rm %s/Offline-L0-EffCl.csv" % (dir), shell = True)
        call("cat %s/Offline-L0-EffCl-Header.csv %s/Offline-L0-EffCl.csv > %s/test.csv" % (dir, dir, dir), shell = True)
        call("mv %s/test.csv %s/L0-EffCl.csv" % (dir, dir), shell = True)


    call("rm %s/Offline*.csv" % (dir), shell = True) # delete old..

    ## Make general plots
