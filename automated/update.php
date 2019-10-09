<?php

if ($argc != 4) {
	exit("Usage: program 'timestamp url type' \n ");
} else {
	$GLOBALS["time"] = $argv[1];
	$GLOBALS["url"] = $argv[2];
	$GLOBALS["type"] = $argv[3];
}

// Update the last_seen value for that URL
function update_url($type,$mysqli,$url,$current_date){
	$sql = "UPDATE $type SET last_seen='$current_date' where URL = '$url'";
	if ($mysqli->query($sql) === TRUE) {
		echo "URL updated successfully for " . $url . PHP_EOL;
	} else {
		echo "Error updating for: " . $url  . " cauz: " . $conn->error . PHP_EOL;
	}
}

// Verify if the last_seen date is present for the URL
function verify_date($type,$mysqli,$url){
	$sql = "SELECT last_seen from $type where URL = '$url'";
	$result = $mysqli->query($sql);
	$lastdate = '';
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$lastdate = $row["last_seen"];
		}
		return $lastdate;
	} else {
		return '';
	}
	mysqli_free_result($result);
}

function print_all($mysqli,$type){
	$sql = "SELECT * from $type";
	$result = $mysqli->query($sql);
	echo "table $type :" . PHP_EOL;
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			echo 'first:' . $row["first_seen"] . PHP_EOL;
			echo 'last:' . $row["last_seen"] . PHP_EOL;
			echo 'URL:' . $row["URL"] . PHP_EOL;
			echo '-----------------' . PHP_EOL;
		}

		mysqli_free_result($result);

	} else {
		echo "0 results" . PHP_EOL;
	}
}

// New URL has been found, adding it to the DB
function insert($type, $first,$last,$url,$mysqli){
	$sql = "INSERT INTO $type (first_seen, last_seen, URL) VALUES ('$first','$last','$url')";
	if(mysqli_query($mysqli, $sql)){
		  echo "Records inserted successfully.";
	} else{
		 echo "ERROR: Not able to execute $sql. " . mysqli_error($mysqli);
	}

}

$mysqli = new mysqli("localhost", "eitest", "Your Password", "eitest");
if ($mysqli->connect_error) {
	printf("Connect failed: %s\n", $mysqli->connect_error);
	exit();
}

$type=$GLOBALS["type"];
$url = $GLOBALS["url"];
$current_date = $GLOBALS["time"];
$last_date = verify_date($type,$mysqli,$url);
$command = "php /var/www/html/domains.php \"" . $current_date . "\" \"" . $url . "\"" . " $type";
exec($command);

if(strlen($last_date)>2){
	if($current_date > $last_date){
		echo 'Date more recent, updating..' . PHP_EOL;
		update_url($type,$mysqli,$url,$current_date);
	}else{
		echo 'no update'  . PHP_EOL;
	}
}else {
	echo 'new record' . PHP_EOL;
	insert($type,$current_date,$current_date,$url,$mysqli);
}

mysqli_close($mysqli);
?>
