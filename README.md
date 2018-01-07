# EITest-trigger
Trigger content injection on demand from the EITest C2
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
