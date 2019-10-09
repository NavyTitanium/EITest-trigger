# EITest-trigger, the fully automated version.
Bypass the added security implemented on the C2 by using different proxies to fetch the payloads. Was used to upload the malicious binaries automatically on [VirusTotal](https://www.virustotal.com/gui/user/V1rgul3/) from 2018-02-02 until the sinkhole operation, on 2018-03-16.

EITest added an extra layer of validation by making sure that the source IP address downloading the final binary was indeed an IP address that had recently been triggering a content injection. The PHP script ***curlprox.php*** was fed by a database containing hundreds of open, free proxies. It uses them to query the backend C2 and ask for content injection. If successful, that IP is used to perform the final download using ***wget -e use_proxy=yes -e http_proxy=$IP_of_the_proxy***, thus bypassing the extra validation steps put in place. 

More than 285 new unique malicious payloads were uploaded to VT, sometimes just an hour after their compilation timestamp.


## Cron jobs
```
*/26 * * * * /var/www/html/ie-track2.sh > /dev/null
*/27 * * * * /var/www/html/chrome-track2.sh > /dev/null
*/30 * * * * /var/www/html/prod/update-lists.sh > /dev/null
*/45 * * * * /var/www/html/payload/trigger-virus.sh > /dev/null
0 */1 * * * /var/www/html/payload/payload2/trigger-virus.sh > /dev/null
*/30 * * * * /usr/local/bin/python3.6 /var/www/html/prod/update_feed.py > /dev/null
```
## Files

| File name     | Description           |
| ------------- | -------------         |
| comment.php   | Given a file hash and a message, will post on VT the comment |
| curlprox.php  | Main logic for fetching the payloads |
| eitest-test.php | Similar to eitest.php. Trigger content injection on demand from the EITest C2 for a given IP |
| hash.php | Verify the payloads, hash them and save the results |
| parseprox.php | Feed the DB with new proxies from text files |
| schema-eitest.sql | Database schema |
| validprox.php | Perform validation on the proxies in the DB |
| vt-reply.php | Monitor binaries detection ratio on VT and update the BD |
| vt.php | Publish binaries to VT |

## Some dependencies
```
sudo apt-get install php-curl
sudo apt-get install php-mysqlnd
```
