<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once 'BaseController.php';

class Login extends BaseController
{
    public function index()
    {
        $this->_app = $this->getAppInfo(true);
        $webLoginToken = $this->input->get('t', true);
        $this->session->set_userdata('humanId__loginRequestOtpToken', $webLoginToken);
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
            // Request OTP
            $response = $this->humanid->userRequestOTP($dialcode, $phone, $webLoginToken, $this->source, $this->lg->id);
            if (!$response->success) {
                $this->handleErrorRequestOtpLogin($response);
            }
            // Save phone and dial code to userdata
            $this->session->set_userdata([
                'humanId__phone' => [
                    'phone' => $phone,
                    'dialcode' => $dialcode,
                ],
            ]);
            $this->session->set_userdata('humanId__requestOtpLogin', $response->data);
            redirect(site_url('verify?a=' . $this->_app->id . '&t=' . $webLoginToken . '&lang=' . $this->lg->id . "&s=" . $this->source));
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
        $this->_checkRequestOtpSession();
        // Load App info
        $this->_app = $this->getAppInfo();
        $this->data['app'] = $this->_app;
        $loginToken = $this->session->userdata('humanId__loginRequestOtpToken');
        $sessionToken = $this->session->userdata('humanId__requestOtpLogin');
        $remaining = $this->input->post('remaining', TRUE);
        $remaining = ($remaining == '') ? 60 : intval($remaining);
        if ($remaining <= 0) {
            $this->init_logs(array('error' => $this->lg->error->verify));
            redirect(site_url('login?a=' . $this->_app->id . '&t=' . $loginToken . '&lang=' . $this->lg->id . '&priority_country=' . $this->pc->code . "&s=" . $this->source));
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
        $login['token'] = $loginToken;
        $this->data['row'] = $login;
        $this->data['display_phone'] = $this->_display_phone($login['phone']);

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
            $requestOtpLogin = $this->session->userdata('humanId__requestOtpLogin');
            $token = $requestOtpLogin->session->token;
            $login = $this->session->userdata('humanId__phone');
            $response = $this->humanid->userLogin($login['dialcode'], $login['phone'], $otp_code, $token, $this->source);
            if ($response->success) {
                $success = 1;
                $this->session->set_userdata(['humanId__phone' => [
                    'phone' => $login['phone'],
                    'dialcode' => $login['dialcode'],
                ]]);
                $data = $response->data;
                $resultVerifyData = (object)[
                    'exchangeToken' => $data->exchangeToken,
                    'redirectUrl' => $data->redirectUrl,
                    'expiredAt' => $data->expiredAt,
                    'user' => $data->user,
                ];
                $this->session->set_userdata('humanId__userLogin', $resultVerifyData);
                if ($data->user->isActive === false) {
                    redirect(base_url('recovery/instead-login'));
                }

                if ($data->app->config->accountRecovery === true && $data->user->hasSetupRecovery === false) {
                    redirect(base_url('recovery/create'));
                }

                redirect('redirect_app');
            } else {
                if ($response->code == 'ERR_11') {
                    $modal = (object) [
                        'title' => $this->lg->errorPage,
                        'code' => $response->code,
                        'message' => $this->lg->error->sessionExpired,
                        'url' => site_url('login?a=' . $this->_app->id . '&t=' . $sessionToken . '&lang=' . $this->lg->id . "&s=" . $this->source)
                    ];
                    $this->session->set_flashdata('modal', $modal);
                    $this->session->set_flashdata('error_message', $this->lg->error->sessionExpired);
                    redirect(site_url('error'));
                }
                if ($response->code == 'ERR_13') {
                    $this->init_logs(array('error' => 'ERR_13 - ' . $response->message));
                    redirect(site_url('login?a=' . $this->_app->id . '&t=' . $loginToken . '&lang=' . $this->lg->id . "&s=" . $this->source));
                }
                if ($response->message == "jwt expired") {
                    $modal = (object) [
                        'title' => $this->lg->errorPage,
                        'code' => $response->code,
                        'message' => $this->lg->error->tokenExpired,
                        'url' => site_url('demo')
                    ];
                    $this->session->set_flashdata('modal', $modal);
                    $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
                    redirect(site_url('error'));
                }

                $this->data['error_message'] = $response->message;
            }
        } else {
            $this->_first_error_msg();
        }
        if (isset($this->data['error_message'])) {
            $this->init_logs(array('error' => $this->data['error_message']));
        }
        $failAttemptLimit = ($success) ? 5 : $remaining;
        $this->data['success'] = $success;
        $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
        $this->scripts('humanid.formLoginVeriy(' . $success . ',' . $failAttemptLimit . ');', 'embed');
        $this->render();
    }

    public function redirect_app()
    {
        $this->_app = $this->getAppInfo();
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
            $this->session->set_userdata('humanId__newAccount', $data->user->newAccount);
            $this->session->set_userdata('humanId__userLogin', $data);
            if ($data->user->isActive === false) {
                redirect(base_url('recovery/instead-login'));
            }
            if ($data->app->config->accountRecovery === true && $data->user->hasSetupRecovery === false) {
                redirect(base_url('recovery/create'));
            }

            $this->data['redirectUrl'] = $data->redirectUrl;
            $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
            $this->scripts('humanid.countdownFormSubmit(5, ".timer-text", "#redirect-app");', 'embed');
            $this->render(true, 'recovery/redirect_app');
            return;
        }

        // Handle from Login
        $userLogin = $this->session->userdata('humanId__userLogin');
        if (!isset($userLogin->redirectUrl)) {
            $this->clearSessions();
            redirect($this->_app->redirectUrlFail);
        }

        $this->data['redirectUrl'] = $userLogin->redirectUrl;
        $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
        $this->scripts('humanid.countdownFormSubmit(5, ".timer-text", "#redirect-app");', 'embed');
        $this->render(true, 'recovery/redirect_app');
    }

    public function redirect_now()
    {
        if ($post = $this->input->post()) {
            $this->clearSessions();
            redirect($post['redirectUrl']);
        }

        redirect('redirect_app');
    }

    public function resend()
    {
        $this->_app = $this->getAppInfo();
        $session = $this->session->userdata('humanId__phone');
        $loginToken = $this->session->userdata('humanId__loginRequestOtpToken');
        $phone = $session['phone'];
        $dialcode = $session['dialcode'];
        // Request OTP
        $response = $this->humanid->userRequestOTP($dialcode, $phone, $loginToken, $this->source, $this->lg->id);
        if (!$response->success) {
            $this->handleErrorRequestOtpLogin($response);
        }
        // Save phone and dial code to userdata
        $this->session->set_userdata([
            'humanId__phone' => [
                'phone' => $phone,
                'dialcode' => $dialcode,
            ],
        ]);
        $this->session->set_userdata('humanId__requestOtpLogin', $response->data);

        redirect(site_url('verify?a=' . $this->_app->id . '&t=' . $loginToken . '&lang=' . $this->lg->id . '&priority_country=' . $this->pc->code . "&s=" . $this->source));
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

    private function _checkRequestOtpSession()
    {
        $session = $this->session->has_userdata('humanId__requestOtpLogin');
        $phone = $this->session->has_userdata('humanId__phone');
        if ($session === false || $phone === false) {
            $this->session->unset_userdata('humanId__phone');
            redirect(site_url('recovery/new_number'));
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
                'url' => $this->data->redirectUrlFail ?? site_url('error')
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

    private function handleErrorRequestOtpLogin($response)
    {
        $code = $response->code;
        $modal = (object) [
            'title' => $this->lg->errorPage,
            'code' => $code ?? '',
            'message' => $response->message ?? '',
            'url' => site_url('login?a=' . $this->_app->id . '&t=' . $this->session->userdata('humanId__loginRequestOtpToken') . '&lang=' . $this->lg->id . '&priority_country=' . $this->pc->code . "&s=" . $this->source)
        ];

        if ($response->message == "jwt expired") {
            $modal = (object)array(
                'title' => $this->lg->errorPage,
                'code' => $response->code,
                'message' => $this->lg->error->tokenExpired,
                'url' => $this->_app->redirectUrlFail ?? site_url('error'),
            );
            $this->session->set_flashdata('modal', $modal);
            $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
        }

        $this->session->set_flashdata('modal', $modal);
        $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
        $redirectUrl = site_url('error');
        redirect($redirectUrl);
    }
}
