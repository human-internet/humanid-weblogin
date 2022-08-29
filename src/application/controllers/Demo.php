<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property Humanid $humanid
 */
class Demo extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->library('humanid');
    }

    public function success()
    {
        $exchangeToken = $this->input->get('et');
        $response = $this->humanid->exchange($exchangeToken);
        if (!$response->success) {
            redirect(base_url('demo'));
        }
        $data = $response->data ?? (object)[];
        $this->data['appUserId'] = $data->appUserId ?? "-";
        $this->data['countryCode'] = $data->countryCode ?? "-";

        $this->render(false, 'demo_success');
    }

    public function failed()
    {
        $this->render(false, 'demo_failed');
    }

    public function error()
    {
        $this->render(false, 'demo_error');
    }
}
