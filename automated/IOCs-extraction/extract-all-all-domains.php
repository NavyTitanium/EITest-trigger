<?php

date_default_timezone_set('UTC');

function get_date($type,$mysqli){
    $urlarray = array();
    $sql = "SELECT DOMAIN from $type";
    $result = $mysqli->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $url = $row["DOMAIN"];
            array_push($urlarray,$url);
        }

        return $urlarray;
    } else {
        return $urlarray;
    }
    mysqli_free_result($result);
}

$mysqli = new mysqli("localhost", "eitest", "Your Password", "eitest");
/* check connection */
if ($mysqli->connect_error) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}

$list1 = get_date('domains',$mysqli);
mysqli_close($mysqli);

$myfile = fopen("all-campaigns-all-time-domains.txt", "w") or die("Unable to open file!");
fwrite($myfile, "# last updated on: " . date("Y-m-d H:i:s", time()) . " UTC \n");
foreach($list1 as $item1) {
    fwrite($myfile, $item1 . "\n");
}
fclose($myfile);

?>
