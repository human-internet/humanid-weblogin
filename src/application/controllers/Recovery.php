<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Recovery extends MY_Controller
{
    var $_app;

    function __construct()
    {
        parent::__construct();
        $this->load->library('humanid');
    }

    public function add()
    {
        $this->_app = $this->_app_info();
        $this->data['app'] = $this->_app;
        $email = $this->input->post('email', true);
        if (!empty($email) && !strpos($email, '@')) {
            redirect('recovery/invalid');
        } else if (!empty($email)) {
            redirect('recovery/success');
        }
        $this->scripts('humanid.modal()', 'embed');
        $this->scripts('humanid.setEmail()', 'embed');
        $this->render(true, 'recovery/set-email');
    }

    public function success()
    {
        $this->_app = $this->_app_info();
        $this->data['app'] = $this->_app;
        $this->render(true, 'recovery/success');
    }

    public function invalid()
    {
        $this->_app = $this->_app_info();
        $this->data['app'] = $this->_app;
        $this->render(true, 'recovery/invalid');

    }

    private function _app_info($new_session = FALSE)
    {
        $app = $this->session->userdata('humanid_app');
        if (!$new_session && $app && !empty($app)) {
            return $app;
        } else {
            $appId = $this->input->get('a', TRUE);
            if ($appId) {
                $res = $this->humanid->app_info($appId, $this->source);
                if ($res['send']) {
                    $result = $res['result'];
                    if ($result['success']) {
                        $app = $res['result']['data']['app'];
                        $app['id'] = $appId;
                        $this->session->set_userdata(array('humanid_app' => $app));
                        return $app;
                    } else {
                        $this->session->set_flashdata('error_message', $result['message']);
                        redirect(site_url('error'));
                    }
                } else {
                    $this->session->set_flashdata('error_message', $this->lg->error->try);
                    redirect(site_url('error'));
                }
            } else {
                $this->session->set_flashdata('error_message', $this->lg->error->appId);
                redirect(site_url('error'));
            }
        }
        return null;
    }

}
