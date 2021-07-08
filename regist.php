<?php

require "access.php";
header('Content-Type: text/html; charset​=utf-8');
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, URL."oauth2/access_token");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
    "client_id" => CLIENT_ID,
    "client_secret" => CLIENT_SECRET,
    "grant_type" => "authorization_code",
    "code" => CODE,
    "redirect_uri" => REDIRECT_URL
)));

// Receive server response ...
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$server_output = curl_exec($ch);

curl_close ($ch);

$server_output = json_decode($server_output); 
echo "<pre>";
print_r($server_output);

if(isset($server_output->access_token)){
    $string = $server_output->access_token."|".$server_output->refresh_token;
    file_put_contents("access.txt", $string);
    echo "hello";
}
// Further processing ...


?>