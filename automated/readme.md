# EITest-trigger, the fully automated version.
Bypass the added security implemented on the C2 by using different proxies to fetch the payload. Was used to fetch the payloads automatically and post them on [VirusTotal](https://www.virustotal.com/gui/user/V1rgul3/)

EITest added an extra layer of validation by making sure that the source IP address downloading the final binary was indeed an IP address that had recently been triggering a content injection. The PHP script ***curlprox.php*** is feeded by a database containing hundreds of open, free proxies. It uses them to query the backend C2 and ask for content injection. If successful, that IP is used to perform the final download using ***wget -e use_proxy=yes -e http_proxy=$IP_of_the_proxy***, thus bypassing the extra validation steps put in place. 


## Running the script every 30 mins
```
*/30 * * * * curlprox.php
```
