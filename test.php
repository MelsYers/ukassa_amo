<?php
header('Content-type: text/html; charset=utf-8');

define("EMAIL", "info@it-dass.com");
define("PASSWORD", "info123");
define("HASHLINE", md5(EMAIL.PASSWORD));

$result = request("auth/login/", array(
    'email' => EMAIL,
    'password' => PASSWORD,
    'hashline' => HASHLINE
), null);

$auth_token_ukassa = $result["auth_token"];
$kassas = $result["user_kassas"]["kassa"];

$tranzactions = array();

foreach ($kassas as $key => $value) {
    $smenas = getReq("kassa/get_shift_list/?kassa=".$value["id"], $auth_token_ukassa);
    
    foreach ($smenas as $key => $smena) {
        $tranzaction_array = getReq("kassa/get_shift_operations/?shift=".$smena["id"], $auth_token_ukassa);
        foreach ($tranzaction_array as $key => $one_tranzaction_cell) {
            $tranzactions[] = getReq("operation/".$one_tranzaction_cell["id"]."/", $auth_token_ukassa);
        }
    }
}

$logout = request("auth/logout/", null, $auth_token_ukassa);

//pre($logout, "Logout");

function pre($text, $transcription){
    echo $transcription."\n";
    echo "<pre>";
    print_r($text);
    echo "</pre>";
}

function request($method, $params, $auth_token){
    $header = array();
    $header[] = 'Content-length: 0';
    $header[] = 'Content-type: application/json';
    $header[] = 'Authorization: Token '.$auth_token;

    $ch = curl_init();
    $curlConfig = array(
        CURLOPT_URL            => "https://test.ukassa.kz/api/".$method,
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => $params
    );
    curl_setopt_array($ch, $curlConfig);

    if(isset($auth_token)){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    
    $result = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if(!$result){
        return $httpcode;
    }
    return json_decode($result, 1);
}

function getReq($method, $auth_token){
    $header = array();
    $header[] = 'Content-length: 0';
    $header[] = 'Content-type: application/json';
    $header[] = 'Authorization: Token '.$auth_token;

    $ch = curl_init();
    $curlConfig = array(
        CURLOPT_URL            => "https://test.ukassa.kz/api/".$method,
        CURLOPT_RETURNTRANSFER => true
    );
    curl_setopt_array($ch, $curlConfig);

    if(isset($auth_token)){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    
    $result = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if(!$result){
        return $httpcode;
    }
    return json_decode($result, 1);
}

?>