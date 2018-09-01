#!/bin/sh
/usr/sbin/httpd

sh -c /run/script/command_php.sh 2>/dev/null >/dev/null &

/bin/sh