#!/bin/sh

while [ true ]
do
	cf=$(cat /run/script/command_php.txt)
	if [ "$cf" == "reboot apache" ]
	then
		echo "" > /run/script/command_php.txt

		pid=$(ps -ef | grep '/usr/sbin/httpd' | grep -v grep | awk '{ print $1 }')
		for i in $pid
		do
			kill $i
		done

		/usr/sbin/httpd
	else
		sleep 10
	fi
done


