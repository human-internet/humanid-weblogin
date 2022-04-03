<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Humanid $humanid
 * @property CI_Form_validation $form_validation
 * @property CI_Session $session
 */
class Recovery extends MY_Controller
{
    var $_app;

    function __construct()
    {
        parent::__construct();
        $this->load->library('humanid');
        $this->load->library('form_validation');
    }

    public function create()
    {
        $this->data['app'] = $this->session->userdata('humanid_app');
        $this->render(true, 'recovery/set-email');
    }

    public function confirmation()
    {
        $this->form_validation->set_rules('email', 'email', 'required|valid_email');
        $this->data['app'] = $this->session->userdata('humanid_app');
        $this->data['email'] = $this->input->post('email');
        $this->data['redirectSetRecoveryEmail'] = base_url('recovery/create');

        $this->render(true, 'recovery/confirmation');
    }

    public function confirmation_process()
    {
        $exchangeToken = $this->session->userdata('humanid_app')['exchangeToken'];
        $data = [
            'recoveryEmail' => $this->input->post('email'),
            'exchangeToken' => $exchangeToken,
            'source' => 'w',
        ];
        $response = $this->humanid->setEmailRecovery($data);

        if (!$response->success) {
            $code = $response->code;
            $modal = (object)array(
                'title' => $this->lg->errorPage,
                'code' => $code ?? '',
                'message' => $response->message ?? '',
                'url' => $this->config->item('humanid')['fe_url']
            );
            $this->session->set_flashdata('modal', $modal);
            $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
            $redirectUrl = site_url('error');
        } else {
            $redirectUrl = $response->data->redirectUrl;
        }

        redirect($redirectUrl);
    }

    public function add()
    {
        $login = $this->_login();
        $token = $this->_token($login['token']);
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

    private function _login()
    {
        $login = $this->session->userdata('humanid_login');
        if ($login && !empty($login)) {
            return $login;
        } else {
            $code = 'WSDK_01';
            $message = $this->lg->error->sessionExpired;
            $error_url = $this->_app['redirectUrlFail'] . '?code=' . $code . '&message=' . urlencode($message);
            $modal = (object)array(
                'title' => $this->lg->errorPage,
                'code' => $code,
                'message' => $message,
                'url' => $error_url
            );
            $this->session->set_flashdata('modal', $modal);
            $this->session->set_flashdata('error_message', $message);
            redirect(site_url('error'));
        }
        return null;
    }

    private function _token($session_token = FALSE)
    {
        $token = $this->input->get('t', TRUE);
        if ($token) {
            if ($session_token) {
                if ($session_token == $token) {
                    return $token;
                } else {
                    $code = 'WSDK_02';
                    $message = $this->lg->error->tokenExpired;
                    $error_url = $this->_app['redirectUrlFail'] . '?code=' . $code . '&message=' . urlencode($message);
                    $modal = (object)array(
                        'title' => $this->lg->errorPage,
                        'code' => $code,
                        'message' => $message,
                        'url' => $error_url
                    );
                    $this->session->set_flashdata('modal', $modal);
                    $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
                    redirect(site_url('error'));
                }
            } else {
                return $token;
            }
        } else {
            $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
            redirect(site_url('error'));
        }

        return null;
    }

}
