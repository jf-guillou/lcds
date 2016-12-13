#!/bin/bash
LCDS_FRONTEND=$(whiptail --inputbox "Please input your webserver frontend address (ie: 'https://lcds-webserver/frontend')" 0 0 --nocancel 3>&1 1>&2 2>&3)

echo "Install and update packages"
apt update
apt install -y apt-utils raspi-config
raspi-config nonint do_memory_split 128
raspi-config nonint do_change_timezone
apt install -y keyboard-configuration console-data
apt upgrade -y
apt install -y rpi-update nano sudo lightdm spectrwm xwit python python-tk lxterminal squid3

echo "Create autorun user"
useradd -m -s /bin/bash -G sudo -G video pi

echo "Install browser"
wget -qO - "http://bintray.com/user/downloadSubjectPublicKey?username=bintray" | sudo apt-key add -
echo "deb http://dl.bintray.com/kusti8/chromium-rpi jessie main" | sudo tee -a /etc/apt/sources.list
apt update
apt install -y omxplayer kweb youtube-dl

echo "Configure display"
sed -i s/#autologin-user=/autologin-user=pi/ /etc/lightdm/lightdm.conf
echo "
disable_border        = 1
bar_enabled           = 0
autorun               = ws[1]:/home/pi/autorun.sh
" > /home/pi/.spectrwm.conf
chown pi: /home/pi/.spectrwm.conf

echo "Setup scripts"
mkdir /home/pi/bin
echo '#!/bin/bash

###
# Set this value according to your installation
###
LCDS_FRONTEND="'$LCDS_FRONTEND'"

# Move cursor out of the way
xwit -root -warp $( cat /sys/module/*fb*/parameters/fbwidth ) $( cat /sys/module/*fb*/parameters/fbheight )

# Disable DPMS / Screen blanking
xset s off

TURNMEOFF=/tmp/turnoff_display
rm $TURNMEOFF

export PATH="/home/pi/bin:$PATH"
export http_proxy="http://localhost:3128"

BROWSER="kweb3"
LOG=/home/pi/autorun.log
VIDEO="omxplayer.bin"
while true; do
  if [ -f $TURNMEOFF ]
  then
    if pgrep $BROWSER
    then
      echo "$(date "+%F %T") : Killing $BROWSER now" >> $LOG
      pgrep $VIDEO && kill -9 $(pidof $VIDEO) # Kill player first if necessary
      kill -1 $(pidof $BROWSER) # Kill browser
      xset dpms force off # Disable X
      tvservice -o # Turn off screen
    fi
  else
    if ! pgrep $BROWSER
    then
      echo "$(date "+%F %T") : Start $BROWSER now" >> $LOG
      tvservice -p # Turn on screen
      sleep 5
      xset -dpms
      sleep 5
      $BROWSER $LCDS_FRONTEND & # Start browser on frontend
    fi
  fi
  sleep 10
done;
' > /home/pi/autorun.sh

chown pi: /home/pi/autorun.sh
chmod u+x /home/pi/autorun.sh

echo "Configure browser in kiosk mode"
echo "-JEKR+-zbhrqfpoklgtjneduwxyavcsmi#?!.," > /home/pi/.kweb.conf

chown pi: /home/pi/.kweb.conf

echo "Configure media player"
echo "
omxplayer_in_terminal_for_video = False
omxplayer_in_terminal_for_audio = False
useAudioplayer = False
useVideoplayer = False
" >> /usr/local/bin/kwebhelper_settings.py

wget https://raw.githubusercontent.com/jf-guillou/lcds/master/tools/omxplayer -O /home/pi/bin/omxplayer

chown pi: /home/pi/bin/omxplayer
chmod u+x /home/pi/bin/omxplayer

echo "Configure local proxy"
echo "http_port 127.0.0.1:3128

acl localhost src 127.0.0.1

http_access allow localhost
http_access deny all

cache_dir aufs /var/spool/squid3 1024 16 256
maximum_object_size 256 MB

cache_store_log /var/log/squid3/store.log
read_ahead_gap 1 MB

refresh_pattern -i (\.mp4|\.jpg|\.jpeg) 43200 100% 129600 reload-into-ims

strip_query_terms off
range_offset_limit none
" >> /etc/squid3/squid.local.conf
echo "include /etc/squid3/squid.local.conf" >> /etc/squid3/squid.conf

echo "Configure auto-shutdown"
echo "0 18 * * 1-5 touch /tmp/turnoff_display >> /home/pi/autorun.log 2>&1
0 7 * * 1-5 /usr/bin/sudo /sbin/reboot >> /home/pi/autorun.log 2>&1
" >> /var/spool/cron/crontabs/root

echo "Firmware update. This will reboot the pi!"
rpi-update && reboot
