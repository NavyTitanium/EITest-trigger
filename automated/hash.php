<?php
date_default_timezone_set('UTC');

// Update the last seen date for a given payload's hash
function update_date($mysqli,$sha256,$current_date,$name){
    $path = "/var/www/html/payload/" . $name;
    $sql = "UPDATE payload SET last_seen='$current_date' where sha256 = '$sha256'";
    if ($mysqli->query($sql) === TRUE) {
        echo "Date updated successfully for " . $sha256 . PHP_EOL;

        // The payload is a duplicate.. we won't keep the copy
        $com = "rm -f " . $path;
        exec($com);
    } else {
        echo "Error updating Date for: " . $sha256  . " cauz: " . $conn->error . PHP_EOL;
    }
}

// Verify if a payload's hash has been inserted in the DB before
function verify_date($mysqli,$sha256){
    $sql = "SELECT last_seen from payload where sha256 = '$sha256'";
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

// Insert a new payload's hash in the DB and publish the payload to VT
function insert_hash($first,$last,$sha256,$md5,$from_url,$proxy,$size,$mysqli,$nom){
    $sql = "INSERT INTO payload (first_seen, last_seen, sha256, md5, from_url, with_ip,size,count) VALUES ('$first','$last','$sha256','$md5','$from_url','$proxy','$size','1')";

    if(mysqli_query($mysqli, $sql)){
        echo "Records inserted successfully." . PHP_EOL;

        // Call vt.php to upload the new payload
        $upload = "php /var/www/html/payload/vt.php '/var/www/html/payload/" . $nom . "'";
        exec($upload);

        // We need to wait for VT to process the file
        echo "Let's sleep before commenting...." . PHP_EOL;
        sleep(15);

        // Comment on the post
        $comment = "php /var/www/html/payload/comment.php " . $sha256 . " '#EITest push payload via " . $from_url . " . More IoCs at: https://github.com/NavyTitanium/EITest-tools-scripts-IOCs/tree/master/IOCs'";
        exec($comment);
        echo "Comment:" . $comment . PHP_EOL;

    } else{
        echo "ERROR: Could not able to execute $sql. " . mysqli_error($mysqli) . PHP_EOL;
    }
}

function hashme($name){
    $path = "/var/www/html/payload/" . $name;
    if (file_exists($path)) {
        $size = filesize($path);
        echo "Size payload:" . $size . PHP_EOL;
        if($size >1000){
            $md5= hash_file('md5', $path);
            $sha256= hash_file('sha256', $path);
            echo "md5:". $md5 . PHP_EOL;
            echo "sha256:" . $sha256 . PHP_EOL;
            return array('sha256' => $sha256, 'md5' => $md5, 'size' => $size);
        }else{
            echo "File too small..." . PHP_EOL;
            $com = "rm -f " . $path;
            exec($com);
            exit(0);
        }
    }else{
        echo "File doesnt exist..." . PHP_EOL;
        exit(0);
    }
}

?>
