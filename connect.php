<?php

require "access.php";
header('Content-type: text/html; charset=utf-8');

function getToken(){
    $string = file_get_contents("access.txt");
    $object = explode("|", $string);
    $accesses = array(
        "access_token" => $object[0],
        "refresh_token" => $object[1]
    );
    return $accesses;
}


function updateToken(){
    $refresh_token = getToken()["refresh_token"];

    $link = URL.'oauth2/access_token';
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_POST => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $link,
        CURLOPT_POSTFIELDS => array(
            "client_id" => CLIENT_ID,
            "client_secret" => CLIENT_SECRET,
            "grant_type" => "refresh_token",
            "refresh_token" => $refresh_token,
            "redirect_uri" => REDIRECT_URL
            ) 
    ));
    $res = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    $accesses = json_decode($res, true);
    if((int)$code==200){
        $string = $accesses["access_token"]."|".$accesses["refresh_token"];
        file_put_contents("access.txt", $string);
    }else{
        print_r(json_encode($accesses,1));
    }
    
}

function execRest($method) {
    updateToken();
    $header = [
        'Authorization: Bearer ' . getToken()["access_token"]
    ];
    $link = URL.'api/v4/'.$method;
    
    $curl = curl_init();
    curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
    curl_setopt($curl,CURLOPT_URL, $link);
    curl_setopt($curl,CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl,CURLOPT_HEADER, false);
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
    
    $res = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    $code = (int)$code;

    if($code==401){
        return execRest($method);
    }
    
    return $res;
}

function execRestPost($method, $data) {
    updateToken();
    $header = [
        'Authorization: Bearer ' . getToken()["access_token"]
    ];
    $link = URL.'api/v4/'.$method;
    
    $curl = curl_init();
    curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
    curl_setopt($curl,CURLOPT_URL, $link);
    curl_setopt($curl,CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl,CURLOPT_HEADER, false);
    curl_setopt($curl,CURLOPT_POST, true);
    curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
    
    $res = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    $code = (int)$code;
    
    if($code==401){
        return execRestPost($method, $data);
    }
    
    return $res;
}

?>