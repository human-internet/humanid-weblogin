<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Humanid            $humanid
 * @property CI_Form_validation $form_validation
 * @property CI_Session         $session
 */
class Recovery extends MY_Controller
{
    var $_app;

    const WRONG_NUMBER = 'ERR_33'; //User not found
    const WRONG_EMAIL = 'ERR_34'; //Account Recovery has not been set-up


    function __construct()
    {
        parent::__construct();
        $this->load->library('humanid');
        $this->load->library('form_validation');
    }

    public function create()
    {
        $this->_app = $this->_app_info();
        $this->data['app'] = $this->_app;
        $this->_check_session();
        $userLogin = $this->session->userdata('humanId__userLogin');
        $this->data['newAccount'] = $userLogin->user->newAccount;
        $this->scripts('humanid.modal()', 'embed');
        $this->render(true, 'recovery/set-email');
    }

    public function skip()
    {
        $userLogin = $this->session->userdata('humanId__userLogin');
        $redirectUrl = $userLogin->redirectUrl;
        $this->session->unset_userdata('humanId__userLogin');
        redirect($redirectUrl);
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
            $modal = (object) array(
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

    private function _checkOtpSession()
    {
        $session = $this->session->has_userdata('humanId__requestOtpRecovery');
        $phone = $this->session->has_userdata('humanId__phone');
        if ($session === null || $phone === null) {
            $this->session->unset_userdata('humanId__phone');
            redirect(site_url('recovery/new_number'));
        }
    }

    public function new_number()
    {
        $this->_app = $this->_app_info();
        $webLoginToken = $this->session->userdata('humanId__webLoginToken');
        $this->data['app'] = $this->_app;
        $this->scripts('humanid.formLogin("", ' . $this->pc->code_js . ');', 'embed');

        if (isset($_POST['dialcode'])) {
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

            if ($this->form_validation->run() == false) {
                $this->_first_error_msg();
                $this->render(true, 'recovery/new-number');
                return;
            }
            $phone = $this->input->post('phone', TRUE);
            $dialcode = $this->input->post('dialcode', TRUE);

            $this->session->set_userdata('humanId__phone', [
                'dialcode' => $dialcode,
                'phone' => $phone
            ]);

            $data = [
                'phone' => "+{$dialcode}{$phone}",
                'token' => $webLoginToken,
                'lang' => 'en',
                'source' => 'w',
            ];

            $requestOtp = $this->humanid->requestOtpForRecovery($data);
            // If not success
            if (!$requestOtp->success) {
                $this->handleErrorRequestOtp($requestOtp);
            }

            $this->session->set_userdata([
                'humanId__phone' => [
                    'phone' => $phone,
                    'dialcode' => $dialcode,
                ],
            ]);

            // Save to session
            $this->session->set_userdata('humanId__requestOtpRecovery', $requestOtp->data);

            redirect(site_url('recovery/verify_otp'));
        }
        $this->render(true, 'recovery/new-number');
    }

    public function verify_otp()
    {
        $this->_app = $this->_app_info();
        $this->_checkOtpSession();
        $this->data['app'] = $this->_app;

        $code = $this->input->post('code');
        if (isset($code) && $code[count($code) - 1] !== '') {
            $redirectUrl = site_url('recovery/verify_email');
            $sessionPhone = $this->session->userdata('humanId__phone');
            $fromRequestOtp = $this->session->userdata('humanId__requestOtpRecovery');
            $sessionToken = $fromRequestOtp->session->token;
            $data = [
                'phone' => "+{$sessionPhone['dialcode']}{$sessionPhone['phone']}",
                'otpCode' => implode('', $code),
                'source' => 'w',
                'token' => $sessionToken
            ];
            $verifyOtpResponse = $this->humanid->verifyOtpForVerifyNewPhone($data);
            if (!$verifyOtpResponse->success) {
                $this->handleErrorVerifyOtp($verifyOtpResponse);
            }

            if ($verifyOtpResponse->data->hasAccount === true) {
                // Save session to userdata
                $this->session->set_userdata('humanId__verifyOtpRecovery', $verifyOtpResponse->data);
                $redirectUrl = site_url('confirmation-login');
            }

            if ($verifyOtpResponse->data->hasAccount === false) {
                // When OTP Verified then recovery login with exchange token
                $recoveryLogin = $this->humanid->accountRecoveryLogin([
                    'token' => $verifyOtpResponse->data->token,
                    'source' => 'w',
                ]);
                $this->session->set_userdata('humanId__userLogin', $recoveryLogin->data);

                $redirectUrl = site_url('recovery/confirmation_switch_email');
            }
            // Remove otp session if otp code is valid
            $this->session->unset_userdata('humanId__requestOtpRecovery');

            // Redirect
            redirect($redirectUrl);
        }

        $otpSession = $this->session->userdata('humanId__requestOtpRecovery');
        $this->data['otpLength'] = $otpSession->otp->config->otpCodeLength;
        $this->data['phone'] = $this->session->userdata('humanId__phone')['phone'];

        $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
        $this->scripts('humanid.formLoginVeriy("", "60");', 'embed');
        $this->render(true, 'recovery/verify');
    }

    public function confirmation_login()
    {
        $this->_app = $this->_app_info();
        $sessionPhone = $this->session->userdata('humanId__phone');
        $this->data['phone'] = "+{$sessionPhone['dialcode']}{$sessionPhone['phone']}";

        $this->render(true, 'recovery/confirmation_switch_email');
    }

    public function request_otp()
    {
        $webLoginToken = $this->session->userdata('humanId__webLoginToken');
        $sessionPhone = $this->session->userdata('humanId__phone');
        $redirectUrl = 'recovery/verify_otp';
        $data = [
            'phone' => "+{$sessionPhone['dialcode']}{$sessionPhone['phone']}",
            'lang' => 'en',
            'source' => 'w',
            'token' => $webLoginToken
        ];
        $response = $this->humanid->requestOtpForRecovery($data);
        if (!$response->success) {
            $code = $response->code;
            $modal = (object) array(
                'title' => $this->lg->errorPage,
                'code' => $code ?? '',
                'message' => $response->message ?? '',
                'url' => site_url($redirectUrl)
            );

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

            if ($response->code == 500) {
                $modal->url = site_url('error');
            }

            $this->session->set_flashdata('modal', $modal);
            $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
            $redirectUrl = site_url('error');
            redirect($redirectUrl);
        }

        $this->session->set_userdata('humanId__requestOtp', $response->data);

        redirect($redirectUrl);
    }

    public function verify_email()
    {
        $this->_app = $this->_app_info();
        $this->_check_session();
        $this->data['app'] = $this->_app;
        $phone = $this->input->post('phone');
        $email = $this->input->post('email');
        $wrongNumberAndEmail = false;
        if ($phone && $email) {
            $redirectUrl = 'recovery/verify_email_code';
            $phone = "+{$this->input->post('dialcode')}{$this->input->post('phone')}";
            $session = $this->session->userdata('humanId__userLogin');
            $data = [
                'recoveryEmail' => $email,
                'oldPhone' => $phone,
                'token' => $session['exchangeToken'],
                'source' => 'w',
            ];

            // Set user
            $user = $session['user'];
            // Check account is inactive then login with exchange token
            if ($user->isActive === false) {
                $loginRecoveryResult = $this->humanid->accountLoginRecovery([
                    'exchangeToken' => $session['exchangeToken'],
                    'source' => 'w',
                ]);
                if (!$loginRecoveryResult->success) {
                    $modal = (object) array(
                        'title' => $this->lg->errorPage,
                        'code' => $loginRecoveryResult->code,
                        'message' => $this->lg->error->tokenExpired,
                        'url' => $this->_app['redirectUrlFail'] ?? site_url('demo'),
                    );
                    $this->session->set_flashdata('modal', $modal);
                    $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
                    redirect(site_url('error'));
                }
                $data['token'] = $loginRecoveryResult->data->token;
                $this->session->set_userdata('humanId__loginRecovery', $loginRecoveryResult->data);
            }

            $response = $this->humanid->requestOtpTransferAccount($data);
            if ($response->code == self::WRONG_NUMBER || $response->code == self::WRONG_EMAIL) {
                $wrongNumberAndEmail = true;
            }

            if ((!$response->success && $response->code !== self::WRONG_NUMBER) || $response->code !== self::WRONG_EMAIL) {
                $code = $response->code;
                $modal = (object) array(
                    'title' => $this->lg->errorPage,
                    'code' => $code ?? '',
                    'message' => $response->message ?? '',
                    'url' => site_url('recovery/verify_email')
                );

                if ($response->code == 500) {
                    $modal->url = site_url('error');
                }

                $this->session->set_flashdata('modal', $modal);
                $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
            }

            if ($response->success) {
                $response->data->phone = $phone;
                $response->data->email = $email;
                $this->session->set_userdata(['humanid_email_otp' => $response->data]);
                redirect($redirectUrl);
            }
        }
        $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
        $this->data['wrongNumberAndEmail'] = $wrongNumberAndEmail;
        $this->scripts('humanid.formLogin("", ' . $this->pc->code_js . ');', 'embed');
        $this->scripts('humanid.modal()', 'embed');
        $this->render(true, 'recovery/verify-email');
    }

    public function verify_email_code()
    {
        $code = $this->input->post('code');
        if (isset($code) && $code[count($code) - 1] !== '') {
            $session = $this->session->userdata('humanId__userLogin');
            $redirectUrl = site_url('recovery/change_number_success');
            $data = [
                "otpCode" => implode('', $code),
                "token" => $this->session->userdata('humanid_verification_new_phone')->token,
                "source" => "w"
            ];
            $user = $session['user'];
            if ($user->isActive === false) {
                $loginRecovery = $this->session->userdata('humanId__loginRecovery');
                $data['token'] = $loginRecovery->token;
            }
            $response = $this->humanid->transferAccount($data);
            if (!$response->success) {
                $code = $response->code;
                $modal = (object) array(
                    'title' => $this->lg->errorPage,
                    'code' => $code ?? '',
                    'message' => $response->message ?? '',
                    'url' => site_url('recovery/verify_email_code')
                );

                if ($response->code == 500) {
                    $modal->url = site_url('error');
                }

                $this->session->set_flashdata('modal', $modal);
                $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
                $redirectUrl = site_url('error');
            } else {
                $this->session->set_userdata(['success_new_recovery_account' => $response->data]);
            }


            if ($response->code == 'ERR_5') {
                $this->session->set_flashdata('error_otp', 'Incorrect code. Please try again.');
                $redirectUrl = 'recovery/verify_email_code';
            }

            redirect($redirectUrl);
        }
        $otpLength = $this->session->userdata('humanid_email_otp')->config->otpCodeLength;
        $this->_app = $this->_app_info();
        $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
        $this->data['app'] = $this->_app;
        $this->data['otpLength'] = $otpLength;
        $this->scripts('humanid.formLoginVeriy("", "60");', 'embed');
        $this->render(true, 'recovery/verify-email-code');
    }

    public function request_email()
    {
        $phone = $this->session->userdata('humanid_email_otp')->phone;
        $email = $this->session->userdata('humanid_email_otp')->email;
        $redirectUrl = 'recovery/verify_email_code';
        $data = [
            "recoveryEmail" => $email,
            "oldPhone" => $phone,
            "token" => $this->session->userdata('humanid_verification_new_phone')->token,
            "source" => "w"
        ];
        $response = $this->humanid->requestOtpTransferAccount($data);
        if (!$response->success) {
            $code = $response->code;
            $modal = (object) array(
                'title' => $this->lg->errorPage,
                'code' => $code ?? '',
                'message' => $response->message ?? '',
                'url' => site_url($redirectUrl)
            );

            if ($response->code == 500) {
                $modal->url = site_url('error');
            }

            $this->session->set_flashdata('modal', $modal);
            $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
            $redirectUrl = site_url('error');
        } else {
            $response->data->phone = $phone;
            $response->data->email = $email;
            $this->session->set_userdata(['humanid_email_otp' => $response->data]);
        }
        redirect($redirectUrl);
    }

    public function change_number_success()
    {
        if ($this->input->post('redirect')) {
            session_destroy();
            redirect($this->session->userdata('success_new_recovery_account')->redirectUrl);
        }
        $this->_app = $this->_app_info();
        $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
        $this->data['appName'] = $this->session->userdata('success_new_recovery_account')->app->name;
        $this->render(true, 'recovery/change-number-success');
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
        $this->data['app'] = $this->session->userdata('humanid_app');
        $this->scripts('humanid.modal()', 'embed');
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
            $modal = (object) array(
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
                    $modal = (object) array(
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

    private function handleErrorVerifyOtp($response)
    {
        $this->session->unset_userdata('humanId__phone');
        // Validate if expired
        if ($response->message == "jwt expired") {
            $modal = (object) array(
                'title' => $this->lg->errorPage,
                'code' => $response->code,
                'message' => $this->lg->error->tokenExpired,
                'url' => $this->_app['redirectUrlFail'] ?? site_url('demo'),
            );
            $this->session->set_flashdata('modal', $modal);
            $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
            redirect(site_url('error'));
        }

        $code = $response->code;
        $modal = (object) array(
            'title' => $this->lg->errorPage,
            'code' => $code ?? '',
            'message' => $response->message ?? '',
            'url' => site_url('recovery/verify_otp')
        );

        if ($response->code == 'ERR_5') {
            $this->session->set_flashdata('error_otp', 'Incorrect code. Please try again.');
            $redirectUrl = 'recovery/verify_otp';
            redirect($redirectUrl);
        }

        if ($response->code == 500) {
            $modal->url = site_url('error');
        }

        $this->session->set_flashdata('modal', $modal);
        $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
        $redirectUrl = site_url('error');

        $this->init_logs(array('error' => "{$response->code} - {$response->message}"));

        redirect($redirectUrl);
    }

    private function _check_session()
    {
        $hasUserLogin = $this->session->has_userdata('humanId__userLogin');
        if (!$hasUserLogin) {
            $code = 'WSDK_01';
            $message = $this->lg->error->sessionExpired;
            $error_url = $this->_app['redirectUrlFail'] . '?code=' . $code . '&message=' . urlencode($message);
            $modal = (object) [
                'title' => $this->lg->errorPage,
                'code' => $code,
                'message' => $message,
                'url' => $error_url
            ];
            $this->session->unset_userdata('humanId__phone');
            $this->session->set_flashdata('modal', $modal);
            $this->session->set_flashdata('error_message', $message);
            redirect(site_url('error'));
        }
    }

    private function handleErrorRequestOtp($requestOtpResponse)
    {
        $code = $requestOtpResponse->code;
        $modal = (object) [
            'title' => $this->lg->errorPage,
            'code' => $code ?? '',
            'message' => $requestOtpResponse->message ?? '',
            'url' => site_url('recovery/new_number')
        ];

        if ($requestOtpResponse->code == "ERR_10") {
            $this->data['error_message'] = $requestOtpResponse->message;
            $this->render(true, 'recovery/new-number');
            return;
        }

        if ($requestOtpResponse->message == "jwt expired") {
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

    private function _first_error_msg()
    {
        $error = validation_errors();
        $error = preg_split('/\r\n|\r|\n/', $error);
        if (count($error) > 0 && !empty($error[0])) {
            $this->data['error_message'] = trim($error[0]);
        }
    }
}
