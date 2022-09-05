<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Controller $ci
 */

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Humanid
{
    private $ci;
    private $url;
    private $client_id;
    private $client_secret;
    private $webLoginClientId;
    private $webLoginClientSecret;
    private $server_id;
    private $server_secret;
    private $client;

    function __construct()
    {
        $this->ci =& get_instance();
        $humanIdConfig = $this->ci->config->item('humanid');
        $this->url = $humanIdConfig['url'];
        $this->client_id = $humanIdConfig['client_id'];
        $this->client_secret = $humanIdConfig['client_secret'];
        $this->webLoginClientId = $humanIdConfig['client_id'];
        $this->webLoginClientSecret = $humanIdConfig['client_secret'];
        $this->server_id = $humanIdConfig['server_id'];
        $this->server_secret = $humanIdConfig['server_secret'];

        $this->client = new Client([
            'base_uri' => $this->url,
            'timeout' => 10
        ]);
    }

    public function getAppInfo($appId, $source = 'w')
    {
        $uri = $this->url . "web-login/apps/$appId";
        try {
            $opt = [
                'headers' => [
                    'client-id' => $this->webLoginClientId,
                    'client-secret' => $this->webLoginClientSecret,
                ],
                'query' => [
                    's' => $source
                ]
            ];
            $response = $this->client->get($uri, $opt);
            $response = $response->getBody()->getContents();
        } catch (RequestException $e) {
            $response = $e->getResponse()->getBody()->getContents();
        }

        return json_decode($response);
    }

    public function app_info($appId, $source = "w")
    {
        $res = array(
            'send' => FALSE,
            'result' => 'No response!'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . 'web-login/apps/' . $appId . '?s=' . $source);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'client-id:' . $this->client_id, 'client-secret:' . $this->client_secret));
        $result = curl_exec($ch);
        if ($result === false) {
            $res['result'] = 'Curl failed: ' . curl_error($ch);
        } else {
            $res['send'] = TRUE;
            $res['result'] = json_decode($result, true);
        }
        curl_close($ch);

        return $res;
    }

    public function session($clientId, $clientSecret, $debug = false)
    {
        $res = array(
            'send' => FALSE,
            'result' => 'No response!'
        );

        $fields = array(
            'partnerClientId' => $clientId,
            'partnerClientSecret' => $clientSecret
        );
        $data_json = json_encode($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . 'web-login/sessions');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data_json), 'client-id:' . $this->client_id, 'client-secret:' . $this->client_secret));
        if ($debug) {
            $res['fields'] = json_encode($fields);
        }
        $result = curl_exec($ch);
        if ($result === false) {
            $res['result'] = 'Curl failed: ' . curl_error($ch);
        } else {
            $res['send'] = TRUE;
            $res['result'] = json_decode($result, true);
        }
        curl_close($ch);

        return $res;
    }

    public function userRequestOTP($countryCode, $phone, $requestOtpToken, $source, $lang = 'en')
    {
        $url = $this->url. 'web-login/users/request-otp';
        $body = [
            'countryCode' => $countryCode,
            'phone' => $phone,
            'token' => $requestOtpToken,
        ];
        $param = [
            'lang' => $lang,
            's' => $source,
        ];

        return $this->internalWebLogin($url, $body, $param);
    }

    public function userLogin($countryCode, $phone, $verificationCode, $token, $source)
    {
        $url = $this->url . 'web-login/users/login';
        $body = [
            'countryCode' => $countryCode,
            'phone' => $phone,
            'deviceId' => 'deviceId',
            'verificationCode' => $verificationCode,
            'notifId' => 'notifId',
            'token' => $token,
        ];
        $param = [
            's' => $source,
        ];
        return $this->internalWebLogin($url, $body, $param);
    }

    private function internalWebLogin($url, $body = [], $queryParam = [])
    {
        try {
            log_message('debug', "START Request: '$url' ");
            log_message('debug', " > Request Body : ". json_encode($body));
            $opt = [
                'headers' => [
                    'client-id' => $this->webLoginClientId,
                    'client-secret' => $this->webLoginClientSecret,
                ],
                'form_params' => $body,
                'query' => $queryParam,
            ];
            $response = $this->client->post($url, $opt);
            $response = $response->getBody()->getContents();
        } catch (RequestException $e) {
            $response = $e->getResponse()->getBody()->getContents();
        }

        $response = json_decode($response);

        $success = $response->success ? 'TRUE' : 'FALSE';
        log_message('debug', " > Success : " . $success);
        log_message('debug', " > Code    : ". $response->code);
        log_message('debug', " > Message : ". $response->message);
        if ($response->success) {
            log_message('debug', " > Data    : ". json_encode($response->data));
        }
        log_message('debug', "END Request: '$url'");

        return $response;
    }

    public function request_otp($countryCode, $phone, $token, $source = "w", $lang = 'en_US', $debug = false)
    {
        $res = array(
            'send' => FALSE,
            'result' => 'No response!'
        );

        $fields = array(
            'countryCode=' . $countryCode,
            'phone=' . $phone,
            'token=' . $token
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . 'web-login/users/request-otp?lang=' . $lang . '&s=' . $source);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $fields));
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'client-id:' . $this->client_id, 'client-secret:' . $this->client_secret));
        if ($debug) {
            $res['fields'] = json_encode($fields);
        }
        $result = curl_exec($ch);
        if ($result === false) {
            $res['result'] = 'Curl failed: ' . curl_error($ch);
        } else {
            $res['send'] = TRUE;
            $res['result'] = json_decode($result, true);
        }
        curl_close($ch);

        return $res;
    }

    public function verify_otp($countryCode, $phone, $verificationCode, $token, $source = "w", $debug = false)
    {
        $res = array(
            'send' => FALSE,
            'result' => 'No response!'
        );

        $fields = array(
            'countryCode=' . $countryCode,
            'phone=' . $phone,
            'deviceId=device-id',
            'verificationCode=' . $verificationCode,
            'notifId=NONE',
            'token=' . $token
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . 'web-login/users/login?s=' . $source);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $fields));
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'client-id:' . $this->client_id, 'client-secret:' . $this->client_secret));
        if ($debug) {
            $res['fields'] = json_encode($fields);
        }
        $result = curl_exec($ch);
        if ($result === false) {
            $res['result'] = 'Curl failed: ' . curl_error($ch);
        } else {
            $res['send'] = TRUE;
            $res['result'] = json_decode($result, true);
        }
        curl_close($ch);

        return $res;
    }

    public function userExchange($token, $clientId, $clientSecret)
    {
        try {
            $response = $this->client->post($this->url . 'server/users/exchange', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'client-id' => $clientId,
                    'client-secret' => $clientSecret,
                ],
                'json' => [
                    'exchangeToken' => $token,
                ],
            ]);
            $response = $response->getBody()->getContents();
        } catch (RequestException $e) {
            $response = $e->getResponse()->getBody()->getContents();
        }

        return json_decode($response);
    }

    private function contains($data = NULL, $key = FALSE, $explode = ",")
    {
        $status = FALSE;
        if (!empty ($data) && $key) {
            $rows = explode($explode, $data);
            foreach ($rows as $val) {
                if ($key == $val) {
                    $status = TRUE;
                    break;
                }
            }
        }

        return $status;
    }

    public function postApi($url, $data)
    {
        try {
            log_message('debug', "START Request: '$url' ");
            log_message('debug', " > Request Body : ". json_encode($data));
            $response = $this->client->post($url, [
                'auth' => [$this->client_id, $this->client_secret],
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                'json' => $data,
            ]);
            $response = $response->getBody()->getContents();
        } catch (RequestException $e) {
            $response = $e->getResponse()->getBody()->getContents();
        }
        $response = json_decode($response);

        $success = $response->success ? 'TRUE' : 'FALSE';
        log_message('debug', " > Success : " . $success);
        log_message('debug', " > Code    : ". $response->code);
        log_message('debug', " > Message : ". $response->message);
        if ($response->success) {
            log_message('debug', " > Data    : ". json_encode($response->data));
        }
        log_message('debug', "END Request: '$url'");

        return $response;
    }

    public function requestOtpForRecovery($data)
    {
        return $this->postApi('accounts/recovery/verify/otp', $data);
    }

    public function verifyOtpForVerifyNewPhone($data)
    {
        return $this->postApi('accounts/recovery/verify', $data);
    }

    public function requestOtpTransferAccount($data)
    {
        return $this->postApi('accounts/recovery/transfer/otp', $data);
    }

    public function verifyOtpTransferAccount($data)
    {
        return $this->postApi('accounts/recovery/transfer', $data);
    }

    public function setEmailRecovery($data)
    {
        return $this->postApi('accounts/recovery', $data);
    }

    public function accountLoginRecovery($data)
    {
        return $this->postApi('accounts/login/recovery', $data);
    }

    public function accountRecoveryLogin($data)
    {
        return $this->postApi('accounts/recovery/login', $data);
    }
}
