<?php
date_default_timezone_set('UTC');

// Test a proxy by opening a socket
function test($proxy)
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

// Test a proxy by fetching a HTTP resource
function test2($proxy){
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

// When a proxy pass the tests, we mark it as valid in the DB
function proxy_ok($proxy,$mysqli)
{
    $sql = "UPDATE proxies SET valid='yes' where ip = '$proxy'";
    if ($mysqli->query($sql) === TRUE) {
        echo "Update successful for working proxy: " . $proxy . PHP_EOL;
    } else {
        echo "Error updating valid proxy: " . $proxy  . " cauz: " . $conn->error . PHP_EOL;
    }
}

//  When a proxy fail one of the test, we update the DB accordingly
function proxy_bad($proxy,$mysqli)
{
    $sql = "UPDATE proxies SET valid='no' where ip = '$proxy'";
    if ($mysqli->query($sql) === TRUE) {
        echo "Updated successful for invalid proxy: " . $proxy . PHP_EOL;
    } else {
        echo "Error updating an invalid proxy: " . $proxy  . " cauz: " . $conn->error . PHP_EOL;
    }
}

// Fetch the proxies from the DB that haven't been tested yet
function get_prox($mysqli)
{
    $urlarray = array();
    $sql = "select ip from proxies where valid='yes' and last_used is null";
    $result = $mysqli->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $url = $row["ip"];
            array_push($urlarray,$url);
        }
        return $urlarray;
    }
}


$mysqli = new mysqli("localhost", "eitest", "Your Password", "eitest");
if ($mysqli->connect_error) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}

$prox = array();
$prox = get_prox($mysqli);

// For each untested proxies, do the tests ...
foreach($prox as $value){
    if(test($value) && test2($value)){
        echo "Good prox..." . $value . PHP_EOL;
        proxy_ok($value,$mysqli);
    }else{
        echo "Bad prox..." . $value . PHP_EOL;
        proxy_bad($value,$mysqli);
    }

}

?>
