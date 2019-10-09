<?php
date_default_timezone_set('UTC');

function get_date($type,$mysqli,$range){
	$urlarray = array();
	$sql = "SELECT $type from payload where last_seen >= '$range'";
	$result = $mysqli->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$url = $row[$type];
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
$list1 = get_date('md5',$mysqli,$back->format('Y-m-d H:i:s'));
$list2 = get_date('sha256',$mysqli,$back->format('Y-m-d H:i:s'));

mysqli_close($mysqli);

$myfile1= fopen("last-30-days-chrome-md5.txt", "w") or die("Unable to open file!");
$myfile2 = fopen("last-30-days-chrome-sha256.txt", "w") or die("Unable to open file!");

fwrite($myfile1, "# last updated on: " . date("Y-m-d H:i:s", time()) . " UTC \n");
fwrite($myfile2, "# last updated on: " . date("Y-m-d H:i:s", time()) . " UTC \n");

foreach($list1 as $item1) {
	fwrite($myfile1, $item1 . "\n");
}
fclose($myfile1);

foreach($list2 as $item2) {
	fwrite($myfile2, $item2 . "\n");
}
fclose($myfile2);

?>
