<?php

$base_url = 'https://web-login.human-id.org/request-token';
$clientId = '***REMOVED***';
$clientSecret = '***REMOVED***';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $base_url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, "clientId=$clientId&clientSecret=$clientSecret");
curl_setopt($ch, CURLOPT_HEADER, FALSE); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data_json)));
$result = curl_exec($ch);
if($result===false){
    echo 'Curl failed: ' . curl_error($ch);
}
else {
    $res = json_decode($result);
    if($res->success){
        header("Location:".$res->login->url);
    }
    else{
        echo $res->message;
    }
}
curl_close($ch);
?>