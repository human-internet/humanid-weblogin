<?php
$path = str_replace("public\demo", "", dirname(__FILE__));
$string = file_get_contents($path.".env");
$data = explode(PHP_EOL, $string);
$env = array();
foreach($data as $r)
{
    $row = explode("=",$r);
    $env[$row[0]] = str_replace('"',"",$row[1]);
}
$base_url = $env['HUMANID_URL'].'server/users/web-login';
$clientId = $env['HUMANID_SERVER_ID'];
$clientSecret = $env['HUMANID_SERVER_SECRET'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $base_url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_HEADER, FALSE); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','client-id:'. $clientId,'client-secret:'.$clientSecret));
$result = curl_exec($ch);
if($result===false){
    echo 'Curl failed: ' . curl_error($ch);
}
else {
    $res = json_decode($result);
    if($res->success){
        header("Location:".$res->data->webLoginUrl);
    }
    else{
        echo $res->message;
    }
}
curl_close($ch);
?>