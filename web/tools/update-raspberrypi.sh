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
killall httpPrefetch

if [ $SQUID -eq 1 ] ; then
  /bin/systemctl stop squid3
  rm -rf /var/spool/squid3/*
  /usr/sbin/squid3 -Nz
fi

# Kweb updates overwrites configuration
if [ $(grep -c "^omxplayer_in_terminal_for_video = False" /usr/local/bin/kwebhelper_settings.py) -eq 0 ] ; then
echo "
omxplayer_in_terminal_for_video = False
omxplayer_in_terminal_for_audio = False
useAudioplayer = False
useVideoplayer = False
" >> /usr/local/bin/kwebhelper_settings.py
fi

# Squid updates may overwrite configuration
if [ $(grep -c "/etc/squid3/squid.local.conf" /etc/squid3/squid.conf) -eq 0 ] ; then
echo "include /etc/squid3/squid.local.conf" >> /etc/squid3/squid.conf
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
