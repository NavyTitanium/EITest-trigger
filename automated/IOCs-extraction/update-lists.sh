#!/bin/sh
cd /var/www/html/prod

function main {
	rm -f /var/www/html/prod/EITest-tools-scripts-IOCs/IOCs/*.txt

	php extract-all-30-days.php
	php extract-all-all.php
	php extract-chrome.php
	php extract-ie.php

	php extract-all-all-domains.php
	php extract-all-domains-30-days.php
	php extract-chrome-domains.php 
	php extract-ie-domains.php	

	php extract-chrome-hashs-30-days.php

	mv /var/www/html/prod/*.txt /var/www/html/prod/EITest-tools-scripts-IOCs/IOCs
	cd /var/www/html/prod/EITest-tools-scripts-IOCs/IOCs

	git add /var/www/html/prod/EITest-tools-scripts-IOCs/IOCs/*.txt
	git commit -m "Updating IOCs"
	git push origin master
	
	exit
}
main
