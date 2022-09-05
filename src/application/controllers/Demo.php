<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Humanid $humanid
 */
class Demo extends MY_Controller
{
    private const DEMO_ID_2 = "2";

    function __construct()
    {
        parent::__construct();
        $this->load->library('humanid');
    }

    public function success($id)
    {
        if ($id === self::DEMO_ID_2) {
            $redirectUri = site_url('demo2');
            $clientId = $_ENV['DEMO_2_CLIENT_ID'];
            $clientSecret = $_ENV['DEMO_2_CLIENT_SECRET'];
        } else {
            $redirectUri = site_url('demo');
            $clientId = $_ENV['DEMO_1_CLIENT_ID'];
            $clientSecret = $_ENV['DEMO_1_CLIENT_SECRET'];
        }

        $exchangeToken = $this->input->get('et');
        $response = $this->humanid->userExchange($exchangeToken, $clientId, $clientSecret);
        if (!$response->success) {
            redirect($redirectUri);
        }
        $data = $response->data ?? (object) [];
        $this->data['appUserId'] = $data->appUserId ?? "-";
        $this->data['countryCode'] = $data->countryCode ?? "-";

        $this->render(false, 'demo_success');
    }

    public function failed($id)
    {
        if ($id === self::DEMO_ID_2) {
            $url = site_url('demo2');
        } else {
            $url = site_url('demo');
        }

        $this->data['demoUrl'] = $url;
        $this->render(false, 'demo_failed');
    }

    public function error($id)
    {
        if ($id === self::DEMO_ID_2) {
            $url = site_url('demo2');
        } else {
            $url = site_url('demo');
        }

        $this->data['demoUrl'] = $url;
        $this->render(false, 'demo_error');
    }
}
