PROGRESS_FILE=/tmp/dependancy_infoloc_in_progress
if [ ! -z $1 ]; then
	PROGRESS_FILE=$1
fi
touch ${PROGRESS_FILE}
echo 0 > ${PROGRESS_FILE}
echo "Launch install of InfoLoc dependancy"
apt-get update
echo 25 > ${PROGRESS_FILE}
apt-get install -y debianutils
apt-get install -y iputils-ping
echo 50 > ${PROGRESS_FILE}
apt-get install -y arping
echo 75 > ${PROGRESS_FILE}
apt-get install -y arp-scan
echo 100 > ${PROGRESS_FILE}
echo "Everything is successfully installed!"
rm ${PROGRESS_FILE}