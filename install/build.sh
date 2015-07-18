#!/bin/sh

ABSOLUTE_FILENAME=`readlink -e "$0"`
DIRECTORY=`dirname "$ABSOLUTE_FILENAME"`

if [ ! -e "$DIRECTORY/../app/config/config.local.php" ]; then
    cp $DIRECTORY/../app/config/config.local.php.txt $DIRECTORY/../app/config/config.local.php
fi

chmod 777 $DIRECTORY/../_tmp
chmod 777 $DIRECTORY/../_run
chmod 777 $DIRECTORY/../app/config/config.local.php
chmod 777 $DIRECTORY/../app/plugins
chmod 777 $DIRECTORY/../app/plugins/plugins.dat
chmod 777 $DIRECTORY/../uploads
