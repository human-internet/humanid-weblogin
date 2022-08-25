<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Humanid $humanid
 * @property CI_Form_validation $form_validation
 * @property CI_Session $session
 */
class Recovery_exist extends MY_Controller
{
    var $_app;

    function __construct()
    {
        parent::__construct();
        $this->load->library('humanid');
    }

    public function login()
    {
        $this->_app = $this->_app_info();
        $this->data['app'] = $this->_app;
        $email = $this->input->post('email', true);
        if (!empty($email) && !strpos($email, '@')) {
            redirect('recovery/invalid');
        } else if (!empty($email)) {
            redirect('recovery/success');
        }
        $this->scripts('humanid.formLogin("", ' . $this->pc->code_js . ');', 'embed');
        $this->render(true, 'recovery-exist/login');
    }

    public function verification()
    {
        $this->_app = $this->_app_info();
        $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
        $this->data['app'] = $this->_app;
        $this->scripts('humanid.formLoginVeriy("", "60");', 'embed');
        $this->render(true, 'recovery-exist/verification');
    }

    public function confirmation_login()
    {
        $this->_app = $this->_app_info();
        $this->data['app'] = $this->_app;
        $this->render(true, 'recovery-exist/confirmation-login');
    }

    public function switch_number()
    {
        $this->_app = $this->_app_info();
        $this->scripts('humanid.formLogin("", ' . $this->pc->code_js . ');', 'embed');
        $this->scripts('humanid.modal()', 'embed');
        $this->render(true, 'recovery-exist/switch-number');
    }

    public function verification_switch_number()
    {
        $this->_app = $this->_app_info();
        $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
        $this->data['app'] = $this->_app;
        $this->scripts('humanid.formLoginVeriy("", "60");', 'embed');
        $this->render(true, 'recovery-exist/verification-switch-number');
    }

    public function block()
    {
        $this->_app = $this->_app_info();
        $this->scripts('humanid.formLogin("", ' . $this->pc->code_js . ');', 'embed');
        $this->scripts('humanid.modal()', 'embed');
        $this->render(true, 'recovery-exist/block');
    }

    public function disabled()
    {
        $this->_app = $this->_app_info();
        $this->scripts('humanid.formLogin("", ' . $this->pc->code_js . ');', 'embed');
        $this->scripts('humanid.modal()', 'embed');
        $this->render(true, 'recovery-exist/disabled');
    }

    public function change_number_success()
    {
        $this->_app = $this->_app_info();
        $this->render(true, 'recovery-exist/change-number-success');
    }

    public function identify_failure()
    {
        $this->_app = $this->_app_info();
        $this->scripts('humanid.modal()', 'embed');
        $this->render(true, 'recovery-exist/identify-failure');
    }

    public function recovery()
    {
        $this->_app = $this->_app_info();
        $this->scripts('humanid.modal()', 'embed');
        $this->render(true, 'recovery-exist/recovery');
    }

    public function instead_login()
    {
        $this->_app = $this->_app_info();
        if ($this->session->has_userdata('humanid_phone')) {
            $humanIdPhone = $this->session->userdata('humanid_phone');
            $this->data['phone'] = $humanIdPhone['phone'];
            $this->data['dialcode'] = $humanIdPhone['dialcode'];
        }
        $this->render(true, 'recovery-exist/instead-login');
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
