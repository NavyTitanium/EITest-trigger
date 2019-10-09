<?php

$api_key = "Your Key";
$file_name_with_full_path = $argv[1];

$post = array('apikey' => $api_key,'file'=>'@'.$file_name_with_full_path);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.virustotal.com/vtapi/v2/file/scan');
curl_setopt($ch, CURLOPT_POST, True);
curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate'); // please compress data
curl_setopt($ch, CURLOPT_USERAGENT, "gzip, My php curl client");
curl_setopt($ch, CURLOPT_RETURNTRANSFER ,True);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
 
$result=curl_exec ($ch);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
print("status = $status_code\n");
if ($status_code == 200) { // OK
  $js = json_decode($result, true);
  print_r($js);
} else {  // Error occured
  print($result);
}
curl_close ($ch);

?>
