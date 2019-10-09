<?php

include('hash.php');
date_default_timezone_set('UTC');
ini_set('max_execution_time', 1000);

// Update the current proxy used with the timestamp in the DB to mark it as valid
function proxy_ok($proxy,$mysqli)
{
    $date = date("Y-m-d H:i:s");
    $sql = "UPDATE proxies SET last_used='" . $date . "' where ip = '$proxy'";
    $sql2 = "UPDATE proxies SET valid='yes' where ip = '$proxy'";

    if ($mysqli->query($sql) === TRUE) {
        echo "Date updated successfully for OK " . $proxy . PHP_EOL;
    } else {
        echo "Error updating DATE for OK: " . $proxy  . " cauz: " . $conn->error . PHP_EOL;
    }

    if ($mysqli->query($sql2) === TRUE) {
        echo "YES status successfully updated for " . $proxy . PHP_EOL;
    } else {
        echo "Error updating status for: " . $proxy  . " cauz: " . $conn->error . PHP_EOL;
    }
}

// Update the DB and mark the proxy as invalid
function prox_failed($proxy,$mysqli)
{
    $sql = "UPDATE proxies SET valid='no' where ip = '$proxy'";
    if ($mysqli->query($sql) === TRUE) {
        echo "Updated to no successfully for failing " . $proxy . PHP_EOL;
    } else {
        echo "Error updating Date for failing : " . $proxy  . " cauz: " . $conn->error . PHP_EOL;
    }
}

// Use the proxy and curl a website
// If the expected content is returned, the proxy is valid
function test_proxy_http($proxy){
    $url="http://perdu.com";
    $theHeader = curl_init($url);
    curl_setopt($theHeader, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($theHeader, CURLOPT_TIMEOUT, 10);
    curl_setopt($theHeader, CURLOPT_PROXY, $proxy);
    $curlResponse = curl_exec($theHeader);
    if ($curlResponse === false)
    {
        return false;
    }
    else
    {
        if(strlen($curlResponse) >  222 or strlen($curlResponse) < 200){
            echo " Fail: " . $curlResponse . PHP_EOL;
            return false;
        }
        else{
            return true;
        }
    }
}

// Opens a socket with the proxy
// If it works, the proxy is valid
function test_proxy_connect($proxy)
{
    $proxy=  explode(':', $proxy);
    $host = $proxy[0];
    $port = $proxy[1];
    $waitTimeoutInSeconds = 5;
    if($fp = @fsockopen($host,$port,$errCode,$errStr,$waitTimeoutInSeconds)){
        return true;
    } else {
        return false;
    }
    fclose($fp);
}

// Return a malicious URL that has been seen in the last 3 hours
function get_malicious_url($type,$mysqli){
    $now = new DateTime();
    $back = $now->sub(DateInterval::createFromDateString('3 hour'));
    $urlarray = array();
    $sql = "SELECT URL from $type where last_seen > '" . $back->format('Y-m-d H:i:s') . "' order by last_seen desc limit 1";
    $result = $mysqli->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            return $row["URL"];
        }
    } else {
        return $urlarray;
    }
    mysqli_free_result($result);
}

// When no more valid proxies are available ( doesn't work or has been used in the last 24 hours)
// We get all of the non-working proxies
function proxy_recuperation($mysqli){
    $now = new DateTime();
    $back = $now->sub(DateInterval::createFromDateString('24 hour'));
    $urlarray = array();
    $sql = "SELECT ip from proxies where valid='no' AND (last_used < '" . $back->format('Y-m-d H:i:s') . "')";
    echo "Recuperation of proxies started: " . $sql . PHP_EOL;
    $result = $mysqli->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $url = $row["ip"];
            array_push($urlarray,$url);
        }
        return $urlarray;
    } else {
        exit(1);
    }
    mysqli_free_result($result);
}

// Get all of the proxies that hasn't been:
// 1) marked as invalid
// 2) used in the last 24 hours
function gimmie_proxies($mysqli) {
    $now = new DateTime();
    $back = $now->sub(DateInterval::createFromDateString('24 hour'));
    echo "time:" .  $back->format('Y-m-d H:i:s') . PHP_EOL;
    $urlarray = array();
    $sql = "SELECT ip from proxies where valid='yes' AND (last_used < '" . $back->format('Y-m-d H:i:s') . "' OR last_used is NULL)";
    echo "Fetching:" . $sql . PHP_EOL;
    $result = $mysqli->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $url = $row["ip"];
            array_push($urlarray,$url);
        }
        return $urlarray;
    } else {
        echo "No more valid proxies!!!" . PHP_EOL;
    }
    mysqli_free_result($result);
}

// If the script is restarted, we will use a proxy that has been used in the last 7 minutes
// This allows to launch the script many times in a row without burning too many proxies
function recent_prox($mysqli)
{
    $now = new DateTime();
    $back = $now->sub(DateInterval::createFromDateString('7 minute'));
    echo "Time minus 10:" .  $back->format('Y-m-d H:i:s') . PHP_EOL;
    $sql = "select ip from proxies where valid='yes' AND last_used > '" . $back->format('Y-m-d H:i:s') .  "' LIMIT 1";
    echo "Old prox:" . $sql . PHP_EOL;
    $result = $mysqli->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $url = $row["ip"];
            return $url;
        }
    } else {
        return "";
    }
}

// Iterate through the array of proxies until one pass the tests
function getvalidprox($urlarray2,$mysqli)
{
    if(sizeof($urlarray2)<1){
        return "";
    }

    $proxy="";
    $status="false";
    while($status=="false"){
        $proxy= $urlarray2[array_rand($urlarray2)];
        if(test_proxy_connect($proxy) && test_proxy_http($proxy) && strlen($proxy) > 2){
            echo "Proxy tests passed.." . PHP_EOL;
            echo "Returning valid proxy:" . $proxy . PHP_EOL;

            proxy_ok($proxy,$mysqli);
            $status="true";
            return $proxy;
        }else{
            echo "Proxy DID NOT pass tests..." . PHP_EOL;
            prox_failed($proxy,$mysqli);
        }
    }
    return $proxy;
}

// Initiate Database connection
$mysqli = new mysqli("localhost", "eitest", "Your Password :)", "eitest");
if ($mysqli->connect_error) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}

/********* Starts by fetch a malicious URL from the DB *********/
$url = get_malicious_url("chrome",$mysqli);
/*********
 * Other scripts were running frequently to grep out the malicious redirection URL from the EITest' injected content and insert it in the DB
 * and if we haven't seen a malicious URL recently, we won't try to trigger EITest (the campaign is probably sleeping)
 *********/
if(strlen($url)<5){
    echo "No url valid URL to crawl... " . PHP_EOL;
    exit(0);
}
echo "Url is: " . $url . PHP_EOL;

$proxies_array = array();
$proxies_array = gimmie_proxies($mysqli);

$date=gmdate("Y-m-d.H:i:s");

// Number of consecutive tries to trigger the content injection from the proxy's IP
$try=0;
$GLOBALS["prox"] ="";

// If the script has been launched recently, we will re-use the last working proxy
$recent = recent_prox($mysqli);
if(strlen($recent)>2){
    echo "Using a recent proxy " . PHP_EOL;
    $GLOBALS["prox"]= $recent;
}

// Loop to retry the content injection giving the proxy's IP address
a:

$recentprox = False;
$injection_successful = False;
$tempo="";

// Are we using a recent proxy?
if(strlen($GLOBALS["prox"])<4){
    $GLOBALS["prox"] = getvalidprox($proxies_array,$mysqli);

    // If no valid proxies were found for use
    if(strlen($GLOBALS["prox"])<2){
        // We iterate through the non-working proxies and re-try them until one works
        $proxies_array = proxy_recuperation($mysqli);
        $GLOBALS["prox"] = getvalidprox($proxies_array,$mysqli);
        if(strlen($GLOBALS["prox"])<2){
            echo "No proxy.. Exiting" . PHP_EOL;
            exit(0);
        }else{
            echo "Proxy recuperation!" . PHP_EOL;
        }
    }
}else{
    $recentprox = True;
}

echo "using: " . $GLOBALS["prox"] . PHP_EOL;


/*********
 * At this stage we have found a valid working proxy IP address to use.
 * Since EITest won't allow us to download the payload if our source IP Address hasn't been asking for content to inject,
 * We will pretend that we are an infected website, asking for content to inject for our proxy's IP address
 *********/
$pieces = explode(':', $GLOBALS["prox"]);
$line = "php eitest-test.php " . $pieces[0];
echo "trying: " . $line . PHP_EOL;
$tempo = exec($line);

// If content has been returned by the C2
if((strlen($tempo)>65) or $recentprox){
    echo "EITest injected: " . strlen($tempo)  . PHP_EOL;
    echo "Injection on IP " . $pieces[0] . PHP_EOL;

    // Before fetching the final payload, let's wait for the C2 backend to sync :)
    echo "Sleep"  . PHP_EOL;
    sleep(5);

    $user_agent = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36";

     /********* Fetch the payload with wget
        * Sets the referer to a known infected website
        * Set the proxy to use for wget
        * Write payload to disk
        * Spoof the User-Agent header
        * Timeout after 2 mins
        * Try only once
     *********/
    $command = "wget --referer='http://www.galwayairport.com/' -e use_proxy=yes -e http_proxy=" . $GLOBALS["prox"] . " -O /var/www/html/payload/" . $date . " --user-agent='". $user_agent ."' " . $url . " -T 120 -t 1";
    echo "Command:" . $command . PHP_EOL;
    exec($command . ' 2>&1', $result1, $result2);
    $injection_successful = True;
    $wget_result= "";
    $wget_result = var_export($result1, true);

    if($result2 != 0){
        echo "Problem occured" . $wget_result . PHP_EOL;
        $path = "/var/www/html/payload/" . $date;
        $del = "rm -f " . $path;
        exec($del);
        $injection_successful = False;
    }else{
        if (strpos($wget_result,"200 OK") !== false) {
            echo "Returned 200 OK " . PHP_EOL;
            $path = "/var/www/html/payload/" . $date;
            $resp= exec('grep MZ ' . $path);

            // Verify if the file downloaded contains binary magic "MZ" and is at least 100 bytes
            if ((filesize($path)<100) or !$resp) {
                echo "Payload is not valid or too small: " . $date .  PHP_EOL;
                echo "Error:" . $wget_result .  PHP_EOL;
                $del = "rm -f " . $path;
                exec($del);
                $injection_successful = False;
            }else{
                $injection_successful = True;
            }
        }else{
            echo "Returned something else:"  . $wget_result .  PHP_EOL;
            $injection_successful = False;
        }
    }
}else{
    // No content has been returned by the C2
    echo 'no injection' . PHP_EOL;
    $injection_successful = False;
    $try=$try+1;
}

// If the payload was obtained successfully
if ($injection_successful) {

    $current_date = $date;
    $payload_hash = hashme($date);

    $md5 = $payload_hash['md5'];
    $sha256 = $payload_hash['sha256'];
    $size = $payload_hash['size'];

    if($size>900){

        //  Verify the last date that payload's hash was downloaded
        $last_date = verify_date($mysqli,$sha256);

        if(strlen($last_date)>2){
            if($current_date > $last_date){
                echo 'Date more recent, updating..' . PHP_EOL;
                update_date($mysqli,$sha256,$current_date,$date);
            }else{
                echo "No update" . PHP_EOL;
            }
        }else {
            // The payload hasn't been seen before
            echo 'New entry !:' . $sha256  . PHP_EOL;
            // Store the information related to this payload
            insert_hash($current_date,$current_date,$sha256,$md5,$url,$GLOBALS["prox"],$size,$mysqli,$date);
        }
    }
}else {
    $injection_successful = False;
}

// No content has been returned by the C2, let's try gain
if (!$injection_successful && $try<2) {
    $GLOBALS["prox"]="";
    goto a;
}

mysqli_close($mysqli);

?>
