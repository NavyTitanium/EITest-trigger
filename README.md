# EITest-trigger
**The domain stat-dns.com used in EITest's DGA algorithm [has been sinkholed](https://www.proofpoint.com/us/threat-insight/post/eitest-sinkholing-oldest-infection-chain). As a result, the EITest campaign has now been shutdown since 2018-03-15.** 

<strike>Trigger content injection on demand from the EITest C2.</strike>

This PHP script is based on the original malicious script, but deobfuscated and highly modified. It will fake a client browsing a website and ask for content injection to the EITest malware C2.

Can be used to track malicious campaigns with the bash script provided. See [the latest version] here(https://github.com/NavyTitanium/EITest-trigger/tree/master/automated)

## Usage
```
[root@localhost]# php eitest.php "User Agent string"
```
## Output
Faking Chrome browser:
```
[root@localhost]# php eitest.php "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36"
injected:
 <script>if (!!window.chrome && .....<output omitted>.....setTimeout(dy0,1000);}</script> 
```
Faking IE browser:
```
[root@localhost]# php eitest.php "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)"
injected:
 <script>function GetWindowHeight(){.....<output omitted>.....;initPu();</script> 
```
