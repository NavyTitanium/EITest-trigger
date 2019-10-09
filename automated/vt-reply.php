<?php

// Set the detection ratio for a given hash in the Database
function update($hash,$result,$mysqli)
{
    if(strlen($hash)<3){
        echo "Hash too short" . PHP_EOL;
    }else{
        $sql = "UPDATE payload set detection='" . $result . "' where sha256='" . $hash . "'";
        if ($mysqli->query($sql) === TRUE) {
            echo "Updated successfully: " . $hash . PHP_EOL;
        } else {
            echo "Error updating fo payload: " . $hash  . " cauz: " . $conn->error . PHP_EOL;
        }
    }
}

// Fetch the detection ratio on VT for a given hash
function vt($hash){
    $api_key = "Your Api Key";
    $post = array('apikey' => $api_key,'resource'=>$hash);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.virustotal.com/vtapi/v2/file/report');
    curl_setopt($ch, CURLOPT_POST,1);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate'); // please compress data
    curl_setopt($ch, CURLOPT_USERAGENT, "gzip, My php curl client");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER ,true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

    $result = curl_exec ($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($status_code == 200) { // OK
        $js = json_decode($result, true);
        echo $js["positives"] . "/" . $js["total"] . PHP_EOL;
        $resp = $js["positives"] . "/" . $js["total"];
        return $resp;
    } else {  // Error occured
        print($result);
    }
    curl_close ($ch);
}

// Get the payload's hash
function get_hash($mysqli)
{
    $urlarray = array();
    $sql = "select sha256 from payload where detection='' or detection is null";
    $result = $mysqli->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $url = $row["sha256"];
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

$urlarray = array();
$urlarray = get_hash($mysqli);

// Iterate on the array of payload's hash and perform the checks on VT
foreach($urlarray as $value){
    sleep(5);
    update($value,vt($value),$mysqli);
}

?>
