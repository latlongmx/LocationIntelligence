#!/bin/bash

# ./createuser.sh  joys_a joys joys.tower@gmail.com uA
# ./createuser.sh  mario_a flores mario@latlong.mx uA
# ./createuser.sh  carlos_a gonzalez carlos@latlong.mx uA
# ./createuser.sh  andres_a ortiz admin@latlong.mx uA

# delete from users where username in ('joys_a','mario_a','carlos_a','andres_a');
# delete from oauth_clients where name in ('joys_a','mario_a','carlos_a','andres_a');

if [ "$#" -ne 4 ]; then
    echo "createuser.sh USER PASSWORD MAIL USERTYPE"
    exit 1
fi

USER=$1
PASS=$2
MAIL=$3
TYPE=$4

PWD=`echo -n "$PASS" | md5sum`
PWD=${PWD%-}
PWD=`echo $PWD | xargs`

URL=http://52.8.211.37/api.walmex.latlong.mx/oa/register

curl --data "u=$USER&p=$PWD&ml=$MAIL&tu=$TYPE" $URL
