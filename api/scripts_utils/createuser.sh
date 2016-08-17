#!/usr/bin/bash

#./createuser.sh  joys_a joys joys.tower@gmail.com uA

if [ "$#" -ne 4 ]; then
    echo "createuser.sh USER PASSWORD MAIL USERTYPE"
    exit 1
fi

USER=$1
PASS=$2
MAIL=$3
TYPE=$4

PWD=`echo $PASS | md5sum`
PWD=${PWD%-}
PWD=`echo $PWD | xargs`

URL=http://52.8.211.37/api.walmex.latlong.mx/oa/register

curl --data "u=$USER&pl=$PWD&ml=$MAIL&tu=$TYPE" $URL
