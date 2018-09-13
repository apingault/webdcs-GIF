#!/bin/bash
echo "tt" > test1

id=$(cat /var/operation/RUN_STABILITY/id)
# Check stability script
runfile=$(cat /var/operation/RUN_STABILITY/run)
if ( [ "$runfile" != "CRASHED" ]  && [ "$runfile" != "KILL" ] && [ "$runfile" != "END" ] && (! pgrep Longevity)) 
then
    /home/webdcs/software/monitoring/sendMail.sh "Stability program crashed, power down detectors"
    #/home/webdcs/software/CAEN/webdcs/StandbyStability.sh $id 10
    #echo "CRASHED" > /var/operation/RUN_STABILITY/run
    #echo `date +"%Y-%m-%d.%H.%M.%S"`.[SUPERVISOR][0] Stability program crashed, power down detectors >> "/var/operation/STABILITY/`printf "%06d" "$id" `/log.txt"
fi


# Check if computer904 is running
function tryConnect {

    ((count = 3))
    while [[ $count -ne 0 ]] ; do
        ping -c 1 $1 > /dev/null 2>&1
        rc=$?
	if [[ $rc -eq 0 ]] ; then
            ((count = 1))
        fi
	((count = count - 1))
     done

    if [[ $rc -eq 0 ]] ; then
        # Connection OK, update status file
        echo "1" > /home/webdcs/software/monitoring/status/$2
    else
        # Connection not OK, send email if previous status was ON, update status file
        if [[ "`cat /home/webdcs/software/monitoring/status/$2`" == "1" ]]; then
            source /home/webdcs/software/monitoring/sendMail.sh "Cannot connect to device $2 [IP ADDR $1]"
        fi

	echo "0" > /home/webdcs/software/monitoring/status/$2
    fi
}

# Try to connect to COMPUTER904
tryConnect 137.138.13.240 COMPUTER904
