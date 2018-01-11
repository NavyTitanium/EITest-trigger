#!/bin/sh

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )";
nb_try=3;
logfile="$DIR/logs-urls-chrome.txt";
user_agent="Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36";

function create_log_file() {

	if [ ! -e "$1" ] ; then
    		touch "$1"
	fi

	if [ ! -w "$1" ] ; then
    		echo cannot write to $1
    		exit 1
	fi
}

function get_url() {
        if [ "$nb_try" -ge 1 ] ; then
                echo "try" $nb_try;
                url=$(php eitest.php "$user_agent" | grep -Eo "(http|https)://[a-zA-Z0-9./?=_-]*")
                if [ ${#url} -ge 2 ];then
                        echo "Success" ;
                        timestamp=`date "+%Y%m%d_%H-%M-%S"`
                        echo $url ':' $timestamp >> $logfile
                        exit
                else
                   nb_try=$((nb_try - 1));
                   get_url
                fi
        fi
}

create_log_file $logfile
get_url
