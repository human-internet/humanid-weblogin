<?php defined('BASEPATH') or exit('No direct script access allowed');

class Humanid
{
    var $url;
    var $client_id;
    var $client_secret;
    var $server_id;
    var $server_secret;
    
    function __construct()
    {
        $this->ci =& get_instance();
        
        $this->url = getenv('HUMANID_URL');
        $this->client_id = getenv('HUMANID_CLIENT_ID');
        $this->client_secret = getenv('HUMANID_CLIENT_SECRET');
        $this->server_id = getenv('HUMANID_SERVER_ID');
        $this->server_secret = getenv('HUMANID_SERVER_SECRET');

    }

    public function request_otp($countryCode,$phone,$debug=false) 
    {
        $res = array(
            'send' => FALSE,
            'result' => 'No response!'
        );

        $fields = array(
            'countryCode='. $countryCode,
            'phone='. $phone,
            'appId='. $this->client_id,
            'appSecret='. $this->client_secret
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url .'mobile/users/login/request-otp');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $fields));
        curl_setopt($ch, CURLOPT_HEADER, FALSE); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded','client-id:'. $this->client_id,'client-secret:'.$this->client_secret));
        if($debug){
            $res['fields'] = json_encode($fields);
        }
        $result = curl_exec($ch);
        if($result===false){
            $res['result'] = 'Curl failed: ' . curl_error($ch);
        }
        else {
            $res['send'] = TRUE;
            $res['result'] = json_decode($result, true);
        }
        curl_close($ch);

        return $res;
    }

    public function verify_otp($countryCode,$phone,$verificationCode,$debug=false) 
    {
        $res = array(
            'send' => FALSE,
            'result' => 'No response!'
        );

        $fields = array(
            'countryCode='. $countryCode,
            'phone='. $phone,
            'deviceId=device-id',
            'verificationCode='. $verificationCode,
            'notifId=NONE'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url .'mobile/users/login');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $fields));
        curl_setopt($ch, CURLOPT_HEADER, FALSE); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded','client-id:'. $this->client_id,'client-secret:'.$this->client_secret));
        if($debug){
            $res['fields'] = json_encode($fields);
        }
        $result = curl_exec($ch);
        if($result===false){
            $res['result'] = 'Curl failed: ' . curl_error($ch);
        }
        else {
            $res['send'] = TRUE;
            $res['result'] = json_decode($result, true);
        }
        curl_close($ch);

        return $res;
    }

    public function exchange($token,$debug=false) 
    {
        $res = array(
            'send' => FALSE,
            'result' => 'No response!'
        );

        $fields = array(
            'exchangeToken' => $token
        );
        $data_json = json_encode($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url .'server/users/exchange');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_HEADER, FALSE); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data_json),'client-id:'. $this->server_id,'client-secret:'.$this->server_secret));
        if($debug){
            $res['fields'] = json_encode($fields);
        }
        $result = curl_exec($ch);
        if($result===false){
            $res['result'] = 'Curl failed: ' . curl_error($ch);
        }
        else {
            $res['send'] = TRUE;
            $res['result'] = json_decode($result, true);
        }
        curl_close($ch);

        return $res;
    }
    
    private function contains($data = NULL, $key = FALSE, $explode = ",")
    {
        $ststus = FALSE;
        if(!empty ($data) && $key)
        {
            $rows = explode($explode, $data);
            foreach ($rows as $val)
            {
                if($key == $val){
                    $ststus = TRUE;
                    break;
                }
            }
        }

        return $ststus;
    }
}
