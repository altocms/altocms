#!/bin/sh

ABSOLUTE_FILENAME=`readlink -e "$0"`
DIRECTORY=`dirname "$ABSOLUTE_FILENAME"`

if [ ! -e "$DIRECTORY/../config/config.local.php" ]; then
    cp $DIRECTORY/../config/config.local.dist.php $DIRECTORY/../config/config.local.php
fi

chmod 777 $DIRECTORY/../_tmp
chmod 777 $DIRECTORY/../_run
chmod 777 $DIRECTORY/../config/config.local.php
chmod 777 $DIRECTORY/../uploads
chmod 777 $DIRECTORY/../plugins
