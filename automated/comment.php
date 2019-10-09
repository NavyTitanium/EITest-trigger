<?php

$api_key = "Your API Key";

$hash=$argv[1];
$message=$argv[2];
echo "hash:" . $hash;

$post = array('apikey' => $api_key,'resource'=> $hash, 'comment'=> $message);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.virustotal.com/vtapi/v2/comments/put');
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
