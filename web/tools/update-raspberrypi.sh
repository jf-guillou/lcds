#!/bin/bash

sudo apt update
sudo apt upgrade -y
sudo rpi-update

# Updater configuration
DISP_USER=pi

. /home/$DISP_USER/config.sh

killall autorun.sh
killall $BROWSER
killall $VIDEO

if [ $SQUID -eq 1 ] ; then
  /bin/systemctl squid3 stop
  rm -rf /var/spool/squid3/*
  /usr/sbin/squid3 -Nz
fi

sudo -u $DISP_USER wget https://raw.githubusercontent.com/jf-guillou/lcds/master/web/tools/autorun.sh -O /home/$DISP_USER/autorun.sh
chmod u+x /home/$DISP_USER/autorun.sh

sudo -u $DISP_USER wget https://raw.githubusercontent.com/jf-guillou/lcds/master/web/tools/connectivity.sh -O /home/$DISP_USER/bin/connectivity.sh
chmod u+x /home/$DISP_USER/bin/connectivity.sh

sudo -u $DISP_USER wget https://raw.githubusercontent.com/jf-guillou/lcds/master/web/tools/omxplayer -O /home/$DISP_USER/bin/omxplayer
chmod u+x /home/$DISP_USER/bin/omxplayer

sudo -u $DISP_USER wget https://github.com/jf-guillou/httpPrefetch/releases/download/v0.1.0/httpPrefetch -O /home/$DISP_USER/bin/httpPrefetch
chmod u+x /home/$DISP_USER/bin/httpPrefetch

sudo -u $DISP_USER wget https://raw.githubusercontent.com/jf-guillou/lcds/master/web/tools/update-raspberrypi.sh -O /home/$DISP_USER/update-raspberrypi.sh
chmod u+x /home/$DISP_USER/update-raspberrypi.sh

reboot
