#!/bin/bash

set -e

IFACE=wlan0
SEC=$1

IEEE80211bg="1 2 3 4 5 6 7 8 9 10 11"
IEEE80211bg_intl="$IEEE80211bg 12 13 14"
IEEE80211bg_eu="$IEEE80211bg 12 13"

IEEE80211a="36 40 44 48 52 56 60 64 149 153 157 161"
IEEE80211bga="$IEEE80211bg $IEEE80211a"
IEEE80211bga_intl="$IEEE80211bg_intl $IEEE80211a"

#ifconfig $IFACE down
#iwconfig $IFACE mode monitor
#ifconfig $IFACE up

while true ; do
    for CHAN in $IEEE80211bg_eu ; do
        echo "Switching to channel $CHAN"
        iwconfig $IFACE channel  $CHAN
        sleep $SEC
    done
done
