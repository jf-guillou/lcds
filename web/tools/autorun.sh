#!/bin/bash
cd "$(dirname "$0")"

# Load configuration
. ./config.sh

AR_LOG=$LOGS/autorun.log
PF_LOG=$LOGS/prefetch.log
TURNMEOFF=/tmp/turnoff_display

echo "$(date "+%F %T") : Start" > $AR_LOG

export PATH="/home/pi/bin:$PATH"
if [ $SQUID -eq 1 ]; then
  export http_proxy="http://localhost:3128"
fi

# Init network and wait for connectivity
./bin/connectivity.sh INIT &> $AR_LOG

# Continuous slow HTTP checks
./bin/connectivity.sh &

if [ $PREFETCHER -eq 1 ]; then
  echo "$(date "+%F %T") : Starting prefetcher" >> $AR_LOG
  ./bin/httpPrefetch &> $PF_LOG &
fi

# Move cursor out of the way
xwit -root -warp $( cat /sys/module/*fb*/parameters/fbwidth ) $( cat /sys/module/*fb*/parameters/fbheight )

# Disable DPMS / Screen blanking
xset s off

rm $TURNMEOFF

while true; do
  if [ -f $TURNMEOFF ]
  then
    if pgrep $BROWSER
    then
      echo "$(date "+%F %T") : Killing $BROWSER now" >> $AR_LOG
      pgrep $VIDEO && kill -9 $(pidof $VIDEO) # Kill player first if necessary
      kill -1 $(pidof $BROWSER) # Kill browser
      xset dpms force off # Disable X
      tvservice -o # Turn off screen
    fi
  else
    if ! pgrep $BROWSER
    then
      echo "$(date "+%F %T") : Start $BROWSER now" >> $AR_LOG
      tvservice -p # Turn on screen
      sleep 5
      xset -dpms
      sleep 5
      $BROWSER "$LCDS/frontend" & # Start browser on frontend
    fi
  fi
  sleep 10
done;
