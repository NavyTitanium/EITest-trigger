#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )";
nb_try=3;
log_duplicate=true
logfile="$DIR/logs-urls-ie.txt";
user_agent="Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)";
current_try=1;

function is_duplicate() {
	URL=$1
	FILE=$2
	if grep -q $URL $FILE ; then
		return 1
	else
		return 0
	fi
}

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
        if [ "$current_try" -le "$nb_try" ] ; then
                echo "try" $current_try;
                url=$(php eitest.php "$user_agent" | grep -Eo "(http|https)://[a-zA-Z0-9./?=_-]*")
                if [ ${#url} -ge 2 ];then
                        echo "Success" ;
			if $log_duplicate || (is_duplicate $url $logfile) ; then
                        	timestamp=`date "+%Y%m%d_%H-%M-%S"`
                        	echo $url ':' $timestamp >> $logfile
                        	exit
			else
				echo "Duplicate URL" ;
			fi
                else
                   let current_try++;
                   get_url
                fi
        fi
}

create_log_file $logfile
get_url
