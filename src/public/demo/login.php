<?php

require __DIR__ . '/../../vendor/autoload.php';

$env_path = $_SERVER['APP_DIR'] ?? __DIR__ . '/../..';

$dotenv = Dotenv\Dotenv::createImmutable($env_path);
$dotenv->load();

$lang = (isset($_GET['lang'])) ? $_GET['lang'] : 'en_US';
$priority_country = (isset($_GET['priority_country'])) ? $_GET['priority_country'] : 'us';
$base_url = $_ENV['HUMANID_URL'] . 'server/users/web-login?lang=' . $lang . '&priority_country=' . $priority_country;
$clientId = $_ENV['HUMANID_SERVER_ID'];
$clientSecret = $_ENV['HUMANID_SERVER_SECRET'];

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
