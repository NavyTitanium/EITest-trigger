#!/bin/sh
cd /var/www/html
try=1;
logfile="/var/www/html/logs-chrome.txt"

function main {
        if [ "$try" -le 3 ] ; then
			echo "try" $try;
			url=$(php eitest.php "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36" | grep -Eo "(http|https)://[a-zA-Z0-9./?=_-]*")
			if [ ${#url} -ge 2 ];then
				echo "Success" ;
				timestamp=`date "+%Y-%m-%d %H:%M:%S"`
				php update.php "$timestamp" "$url" "chrome"
				exit
			else
				try=$((try + 1));
				main
			fi
        fi
}
main
