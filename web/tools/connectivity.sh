#!/bin/bash

if [ $# -gt 0 ] ; then

  INT=eth0

  # Init Wired connection if possible
  /usr/bin/sudo /sbin/dhclient $INT
  sleep 6

  if [ $(/usr/bin/sudo /sbin/ifconfig $INT | grep -c "inet addr") -eq 0 ] && [ $WIFI -eq 1 ] ; then
    INT=wlan0

    /usr/bin/sudo /sbin/wpa_supplicant -B -i $INT -c /etc/wpa_supplicant/wpa_supplicant-$INT.conf
    sleep 6

    fails=0
    while [[ $(/usr/bin/sudo /sbin/wpa_cli -i $INT status | grep -c "wpa_state=COMPLETED") -eq 0 ]]
    do
      sleep 10
      ((fails++))
      if [[ $fails -gt 60 ]] ; then
        /usr/bin/sudo reboot
      fi
    done

    /usr/bin/sudo /sbin/dhclient $INT

    fails=0
    while [[ $(/usr/bin/sudo /sbin/ifconfig $INT | grep -c "inet addr") -eq 0 ]]
    do
      sleep 10
      ((fails++))
      if [[ $fails -gt 60 ]] ; then
        /usr/bin/sudo reboot
      fi
    done

    fails=0
    while ! curl -s $LCDS > /dev/null
    do
      sleep 10
      ((fails++))
      if [[ $fails -gt 60 ]] ; then
        /usr/bin/sudo reboot
      fi
    done
  else
    fails=0
    while [[ $(/usr/bin/sudo /sbin/ifconfig $INT | grep -c "inet addr") -eq 0 ]]
    do
      sleep 10
      ((fails++))
      if [[ $fails -gt 60 ]] ; then
        /usr/bin/sudo reboot
      fi
    done

    fails=0
    while ! curl -s $LCDS > /dev/null
    do
      sleep 10
      ((fails++))
      if [[ $fails -gt 60 ]] ; then
        /usr/bin/sudo reboot
      fi
    done
  fi

else

  while true
  do

    fails=0
    while ! curl -s $LCDS > /dev/null
    do
      sleep 10
      ((fails++))
      if [[ $fails -gt 60 ]] ; then
        /usr/bin/sudo reboot
      fi
    done

    sleep 60
  done
fi
