<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Humanid $humanid
 * @property CI_Form_validation $form_validation
 * @property CI_Session $session
 */
class Login extends MY_Controller
{

    var $_app;

    function __construct()
    {
        parent::__construct();
        $this->load->library('humanid');
        $this->load->library('form_validation');
    }

    public function checkWebLoginToken()
    {
        // Get from query param
        $token = $this->input->get('t', TRUE);
        $hasLoginToken = $this->session->has_userdata('humanId__webLoginToken');
        if (!$hasLoginToken) {
            $this->session->set_userdata('humanId__webLoginToken', $token);
        }
        $webLoginToken = $this->session->userdata('humanId__webLoginToken');
        if ($webLoginToken !== $token) {
            $modal = (object)array(
                'title' => $this->lg->errorPage,
                'code' => '400',
                'message' => $this->lg->error->tokenExpired,
                'url' => $this->_app['redirectUrlFail'] ?? site_url('demo')
            );
            $this->session->set_flashdata('modal', $modal);
            $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
            redirect(site_url('error'));
        }
    }

    public function index()
    {
        $this->_app = $this->_app_info(TRUE);
        $this->checkWebLoginToken();
        $this->form_validation->set_rules('phone', $this->lg->phone, 'required|numeric|min_length[4]|max_length[14]', array(
            'required' => $this->lg->form->phoneRequired,
            'numeric' => $this->lg->form->phoneNumeric,
            'min_length' => $this->lg->form->phoneMin,
            'max_length' => $this->lg->form->phoneMax
        ));
        $this->form_validation->set_rules('dialcode', $this->lg->countryCode, 'required|numeric', array(
            'required' => $this->lg->form->countryCodeRequired,
            'numeric' => $this->lg->form->countryCodeNumeric
        ));

        $phone = $this->input->post('phone', TRUE);
        $dialcode = $this->input->post('dialcode', TRUE);

        if ($this->form_validation->run() == TRUE) {
            $webLoginToken = $this->session->userdata('humanId__webLoginToken');
            // Request OTP
            $result = $this->humanid->userRequestOTP($dialcode, $phone, $webLoginToken, $this->source, $this->lg->id);
            if ($result->success === false) {
                if ($result->message == "jwt expired") {
                    $modal = (object)array(
                        'title' => $this->lg->errorPage,
                        'code' => $result->code,
                        'message' => $this->lg->error->tokenExpired,
                        'url' => $this->_app['redirectUrlFail'] ?? site_url('demo'),
                    );
                    $this->session->set_flashdata('modal', $modal);
                    $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
                } else {
                    $this->data['error_message'] = $result->message;
                }
            }
            // Save phone and dial code to userdata
            $this->session->set_userdata([
                'humanId__phone' => [
                    'phone' => $phone,
                    'dialcode' => $dialcode,
                ],
            ]);
            $this->session->set_userdata('humanId__requestOtp', $result->data);
            redirect(site_url('verify?a=' . $this->_app['id'] . '&t=' . $webLoginToken . '&lang=' . $this->lg->id . "&s=" . $this->source));
        } else {
            $this->_first_error_msg();
        }
        if (isset($this->data['error_message'])) {
            $this->init_logs(array('error' => $this->data['error_message']));
        }
        $login = $this->session->userdata('humanId__phone');
        $set_number = '';
        if ($dialcode && $phone) {
            $set_number .= '+';
            $set_number .= $dialcode;
            $set_number .= $this->_display_phone($phone, '-');
        } else if ($login) {
            $set_number .= '+';
            $set_number .= $login['dialcode'] ?? '';
            $set_number .= $this->_display_phone($login['phone'], '-');
        }

        $this->data['phone'] = $phone;
        $this->data['app'] = $this->_app;
        $this->scripts('humanid.formLogin("' . $set_number . '", ' . $this->pc->code_js . ');', 'embed');
        $this->render();
    }

    public function verify()
    {
        // Load App info
        $this->_app = $this->_app_info();
        // Check session login
        $remaining = $this->input->post('remaining', TRUE);
        $remaining = ($remaining == '') ? 60 : intval($remaining);
        $webLoginToken = $this->session->userdata('humanId__webLoginToken');
        if ($remaining <= 0) {
            $this->init_logs(array('error' => $this->lg->error->verify));
            redirect(site_url('login?a=' . $this->_app['id'] . '&t=' . $webLoginToken . '&lang=' . $this->lg->id . '&priority_country=' . $this->pc->code . "&s=" . $this->source));
        }
        $modal = $this->session->flashdata('modal');
        if ($modal) {
            $this->data['modal'] = $modal;
        }
        $error_message = $this->session->flashdata('error_message');
        if ($error_message) {
            $this->data['error_message'] = $error_message;
        }
        $success = 0;
        $login = $this->session->userdata('humanId__phone');
        $login['token'] = $webLoginToken;
        $this->form_validation->set_rules('code_1', 'Code', 'required|numeric', array('required' => $this->lg->form->codeRequired, 'numeric' => $this->lg->form->codeNumeric));
        $this->form_validation->set_rules('code_2', 'Code', 'required|numeric', array('required' => $this->lg->form->codeRequired, 'numeric' => $this->lg->form->codeNumeric));
        $this->form_validation->set_rules('code_3', 'Code', 'required|numeric', array('required' => $this->lg->form->codeRequired, 'numeric' => $this->lg->form->codeNumeric));
        $this->form_validation->set_rules('code_4', 'Code', 'required|numeric', array('required' => $this->lg->form->codeRequired, 'numeric' => $this->lg->form->codeNumeric));
        if ($this->form_validation->run() == TRUE) {
            $code_1 = $this->input->post('code_1', TRUE);
            $code_2 = $this->input->post('code_2', TRUE);
            $code_3 = $this->input->post('code_3', TRUE);
            $code_4 = $this->input->post('code_4', TRUE);
            $otp_code = $code_1 . $code_2 . $code_3 . $code_4;

            // Verify the otp code
            $fromRequestOtp = $this->session->userdata('humanId__requestOtp');
            $sessionToken = $fromRequestOtp->session->token;
            $login = $this->session->userdata('humanId__phone');
            $result = $this->humanid->userLogin($login['dialcode'], $login['phone'], $otp_code, $sessionToken, $this->source);
            if ($result->success) {
                $success = 1;
                $this->session->unset_userdata(['humanid_login']);
                $this->session->set_userdata(['humanId__phone' => [
                    'phone' => $login['phone'],
                    'dialcode' => $login['dialcode'],
                ]]);
                $data = $result->data;
                $resultVerifyData = [
                    'exchangeToken' => $data->exchangeToken,
                    'redirectUrl' => $data->redirectUrl,
                    'expiredAt' => $data->expiredAt,
                    'user' => $data->user,
                ];
                $this->session->set_userdata('humanId__userLogin', $resultVerifyData);
                if ($data->user->isActive === false) {
                    redirect(base_url('recovery-exist/instead-login'));
                }

                if ($data->app->config->accountRecovery === true && $data->user->hasSetupRecovery === false) {
                    redirect(base_url('recovery/create'));
                }

                redirect('redirect_app');
            } else {
                if ($result->code == 'ERR_13') {
                    $this->init_logs(array('error' => 'ERR_13 - ' . $result->message));
                    redirect(site_url('login?a=' . $this->_app['id'] . '&t=' . $webLoginToken . '&lang=' . $this->lg->id . "&s=" . $this->source));
                }
                if ($result->message == "jwt expired") {
                    $modal = (object)array(
                        'title' => $this->lg->errorPage,
                        'code' => $result->code,
                        'message' => $this->lg->error->tokenExpired,
                        'url' => site_url('demo')
                    );
                    $this->session->set_flashdata('modal', $modal);
                    $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
                    redirect(site_url('error'));
                }
                $this->data['error_message'] = $result['message'];
            }
            $this->data['error_message'] = $this->lg->error->try;
        } else {
            $this->_first_error_msg();
        }
        if (isset($this->data['error_message'])) {
            $this->init_logs(array('error' => $this->data['error_message']));
        }
        $failAttemptLimit = ($success) ? 5 : $remaining;
        $this->data['row'] = $login;
        $this->data['success'] = $success;
        $this->data['display_phone'] = $this->_display_phone($login['phone']);
        $this->data['app'] = $this->_app;
        $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
        $this->scripts('humanid.formLoginVeriy(' . $success . ',' . $failAttemptLimit . ');', 'embed');
        $this->render();
    }

    public function redirect_app()
    {
        $this->_app = $this->_app_info();
        $this->data['app'] = $this->_app;
        // Handle from Recovery Account
        $isRecovery = $this->session->has_userdata('humanId__verifyOtpRecovery');
        if ($isRecovery) {
            // "Yes, Log me in instead" button clicked
            $sessionOtpRecovery = $this->session->userdata('humanId__verifyOtpRecovery');
            $recoveryLogin = $this->humanid->accountRecoveryLogin([
                'token' => $sessionOtpRecovery->token,
                'source' => 'w',
            ]);
            if (!$recoveryLogin->success) {
                $this->handleErrorRecoveryLogin($recoveryLogin);
            }
            $data = $recoveryLogin->data;
            $verify = $this->humanid->userExchange($data->exchangeToken);
            if (!$verify->success) {
                redirect($this->data['app']['redirectUrlFail']);
            }
            $this->session->set_userdata('humanId__userLogin', $data);
            if ($data->user->isActive === false) {
                redirect(base_url('recovery-exist/instead-login'));
            }
            if ($data->app->config->accountRecovery === true && $data->user->hasSetupRecovery === false) {
                redirect(base_url('recovery/create'));
            }

            $this->data['redirectUrl'] = $data->redirectUrl;

            $success = 1;
            $failAttemptLimit = 5;
            $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
            $this->scripts('humanid.formLoginVeriy(' . $success . ',' . $failAttemptLimit . ');', 'embed');
            $this->render(true, 'recovery/redirect_app');
            return;
        }

        // Handle from Login
        $userLogin = $this->session->userdata('humanId__userLogin');
        if (!isset($userLogin['redirectUrl'])) {
            $this->session->unset_userdata('humanId__webLoginToken');
            redirect($this->data['app']['redirectUrlFail']);
        }
        $this->data['redirectUrl'] = $userLogin['redirectUrl'];

        $success = 1;
        $failAttemptLimit = 5;
        $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
        $this->scripts('humanid.formLoginVeriy(' . $success . ',' . $failAttemptLimit . ');', 'embed');
        $this->render(true, 'recovery/redirect_app');
    }

    public function resend()
    {
        $this->_app = $this->_app_info();
        $login = $this->_login();
        $token = $this->_token($login['token']);

        $error_message = '';
        $res = $this->humanid->request_otp($login['dialcode'], $login['phone'], $token, $this->source);
        if ($res['send']) {
            $result = $res['result'];
            if ($result['success']) {
                $data = array(
                    'humanid_login' => array(
                        'phone' => $login['phone'],
                        'dialcode' => $login['dialcode'],
                        'token' => $token,
                        'result' => $result
                    )
                );
                $this->session->set_userdata($data);
            } else {
                $error_url = $this->_app['redirectUrlFail'] . '?code=' . $result['code'] . '&message=' . urlencode($result['message']);
                $modal = (object)array(
                    'title' => $this->lg->errorPage,
                    'code' => $result['code'],
                    'message' => $result['message'],
                    'url' => $error_url
                );
                $this->session->set_flashdata('modal', $modal);
                $error_message = $result['message'];
            }
        } else {
            $error_message = $this->lg->error->try;
        }
        $this->session->set_flashdata('error_message', $error_message);
        redirect(site_url('login/verify?a=' . $this->_app['id'] . '&t=' . $token . '&lang=' . $this->lg->id . '&priority_country=' . $this->pc->code . "&s=" . $this->source));
    }

    private function _display_phone($phone = 0, $text = " ")
    {
        $length = strlen($phone);
        if ($length > 3 && $length <= 7) {
            $last = $length - 3;
            $phone = preg_replace("/^(\d{3})(\d{" . $last . "})$/", "$1" . $text . "$2", $phone);
        } else if ($length > 7 && $length <= 10) {
            $last = $length - 6;
            $phone = preg_replace("/^(\d{3})(\d{3})(\d{" . $last . "})$/", "$1" . $text . "$2" . $text . "$3", $phone);
        } else if ($length > 10 && $length <= 14) {
            if ($length == 11) {
                $phone = preg_replace("/^(\d{3})(\d{4})(\d{4})$/", "$1" . $text . "$2" . $text . "$3", $phone);
            } else {
                $last = $length - 11;
                $phone = preg_replace("/^(\d{3})(\d{4})(\d{4})(\d{" . $last . "})$/", "$1" . $text . "$2" . $text . "$3" . $text . "$4", $phone);
            }
        }

        return $phone;
    }

    private function _first_error_msg()
    {
        $error = validation_errors();
        $error = preg_split('/\r\n|\r|\n/', $error);
        if (count($error) > 0 && !empty($error[0])) {
            $this->data['error_message'] = $error[0];
        }
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

    private function checkSessionToken($sessionToken = null)
    {
        $token = $this->input->get('t', TRUE);
        if ($token !== '') {
            $this->session->set_userdata('sessionToken', $token);
            return $token;
        }

        if ($sessionToken !== null) {
            if ($sessionToken === $token) {
                $this->session->set_userdata('sessionToken', $token);
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
                $this->session->unset_userdata('__sessionToken');
                redirect(site_url('error'));
            }
        }
    }

    private function handleErrorRecoveryLogin($response) {
        $code = $response->code;
        $modal = (object) [
            'title' => $this->lg->errorPage,
            'code' => $code ?? '',
            'message' => $response->message ?? '',
            'url' => site_url('recovery/new_number')
        ];

        if ($response->message == "jwt expired") {
            $modal = (object) [
                'title' => $this->lg->errorPage,
                'code' => $code ?? '',
                'message' => $this->lg->error->tokenExpired,
                'url' => $this->data['app']['redirectUrlFail'] ?? site_url('demo')
            ];
            $this->session->unset_userdata('humanId__phone');
            $this->session->set_flashdata('modal', $modal);
            $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
            redirect(site_url('error'));
        }

        $this->session->set_flashdata('modal', $modal);
        $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
        $redirectUrl = site_url('error');
        redirect($redirectUrl);
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
