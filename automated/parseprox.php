<?php
date_default_timezone_set('UTC');

// Insert a proxy into the DB
function set_prox($prox,$mysqli){
    $sql = "INSERT INTO proxies (ip) VALUES ('" . $prox . "')";
    if(mysqli_query($mysqli, $sql)){
        echo "Records inserted successfully." . PHP_EOL;
    } else{
        echo "ERROR: Could not able to execute $sql. " . mysqli_error($mysqli) . PHP_EOL;
    }
}

$mysqli = new mysqli("localhost", "eitest", "Your Password", "eitest");
if ($mysqli->connect_error) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}

// Parse a file named list.txt
function parseFile() {

    $urlarray = array();

    $fd = file("list.txt");
    foreach($fd as $row) {
        array_push($urlarray,$row);
    }
    return $urlarray;

}

$urlarray2 = array();
$urlarray2 = parseFile();

// Iterate on the array and validate the proxies, then call the function set_prox
foreach($urlarray2 as $value){
    $value = str_replace(array("\r", "\n"), '', $value);
    $pieces = explode(":", $value);

    if(filter_var($pieces[1], FILTER_VALIDATE_INT) and filter_var($pieces[0], FILTER_VALIDATE_IP))
    {
        #echo "Is valid";
        set_prox($value,$mysqli);
    }else{
        #echo("Is not valid");
        echo($value);
    }
}

mysqli_close($mysqli);

?>
