<?php
date_default_timezone_set('UTC');

function get_date($type,$mysqli,$range){
    $urlarray = array();
    $sql = "SELECT URL from $type where last_seen >= '$range'";
    $result = $mysqli->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $url = $row["URL"];
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

$now = new DateTime();
$back = $now->sub(DateInterval::createFromDateString('30 days'));
$list1 = get_date('ie',$mysqli,$back->format('Y-m-d H:i:s'));
$list2 = get_date('chrome',$mysqli,$back->format('Y-m-d H:i:s'));
$list3 = get_date('scam_redirected',$mysqli,$back->format('Y-m-d H:i:s'));

mysqli_close($mysqli);

$myfile = fopen("last-30-days-all-campaigns-URLs.txt", "w") or die("Unable to open file!");

fwrite($myfile, "# last updated on: " . date("Y-m-d H:i:s", time()) . " UTC \n");

foreach($list1 as $item1) {
    fwrite($myfile, $item1 . "\n");
}
foreach($list2 as $item2) {
    fwrite($myfile, $item2 . "\n");
}

foreach($list3 as $item3) {
    fwrite($myfile, $item3 . "\n");
}
fclose($myfile);

?>
