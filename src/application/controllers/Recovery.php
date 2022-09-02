<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once 'BaseController.php';

class Recovery extends BaseController
{
    public function new_number()
    {
        $this->_app = $this->getAppInfo();
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

            $webLoginToken = $this->session->userdata('humanId__loginRequestOtpToken');
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

    public function create()
    {
        $this->_app = $this->getAppInfo();
        $this->checkUserLogin();
        $this->data['app'] = $this->_app;
        $userLogin = $this->session->userdata('humanId__userLogin');
        $this->data['newAccount'] = $userLogin->user->newAccount;
        $error_message = $this->session->flashdata('error_message');
        if ($error_message) {
            $this->data['error_message'] = $error_message;
        }

        $this->scripts('humanid.modal()', 'embed');
        $this->render(true, 'recovery/set-email');
    }

    public function confirmation()
    {
        $this->_app = $this->getAppInfo();
        $this->checkUserLogin();
        $this->data['app'] = $this->_app;
        $this->data['email'] = $this->input->post('email');
        $this->form_validation->set_rules('email', 'email', 'required|valid_email');
        if ($this->form_validation->run() == false) {
            $this->_first_error_msg();
            if (isset($this->data['error_message'])) {
                $this->session->set_flashdata('error_message', $this->data['error_message']);
            }
            redirect(base_url('recovery/create'));
        }
        // Set email recovery flow
        $this->data['redirectSetRecoveryEmail'] = base_url('recovery/create');

        $this->render(true, 'recovery/confirmation');
    }

    public function confirmation_process()
    {
        $this->_app = $this->getAppInfo();
        $this->checkUserLogin();
        $userLogin = $this->session->userdata('humanId__userLogin');
        $data = [
            'recoveryEmail' => $this->input->post('email'),
            'exchangeToken' => $userLogin->exchangeToken,
            'source' => 'w',
        ];
        $response = $this->humanid->setEmailRecovery($data);
        if (!$response->success) {
            $this->handleErrorSetEmailRecovery($response);
        }
        $this->session->unset_userdata('humanId__requestOtpRecovery');
        $this->session->unset_userdata('humanId__verifyOtpRecovery');
        redirect($response->data->redirectUrl);
    }

    public function verify_otp()
    {
        $this->_app = $this->getAppInfo();
        $this->_checkRequestOtpSession();

        $this->data['app'] = $this->_app;
        $sessionPhone = $this->session->userdata('humanId__phone');
        $code = $this->input->post('code');
        if (isset($code) && $code[count($code) - 1] !== '') {
            $redirectUrl = site_url('recovery/verify_email');
            $fromRequestOtp = $this->session->userdata('humanId__requestOtpRecovery');
            $sessionToken = $fromRequestOtp->session->token;
            $data = [
                'phone' => "+{$sessionPhone['dialcode']}{$sessionPhone['phone']}",
                'otpCode' => implode('', $code),
                'source' => 'w',
                'token' => $sessionToken
            ];

            // Verify OTP For New Phone
            $verifyOtpResponse = $this->humanid->verifyOtpForVerifyNewPhone($data);
            if (!$verifyOtpResponse->success) {
                $this->handleErrorVerifyOtp($verifyOtpResponse);
            }

            // Save to session
            $this->session->set_userdata('humanId__verifyOtpRecovery', $verifyOtpResponse->data);
            if ($verifyOtpResponse->data->hasAccount === false) {
                // When OTP Verified then recovery login with exchange token
                $recoveryLogin = $this->humanid->accountRecoveryLogin([
                    'token' => $verifyOtpResponse->data->token,
                    'source' => 'w',
                ]);
                $this->session->set_userdata('humanId__userLogin', $recoveryLogin->data);
                $redirectUrl = site_url('recovery/verify_email');
            }

            // Redirect
            redirect($redirectUrl);
        }

        $otpSession = $this->session->userdata('humanId__requestOtpRecovery');
        $otpConfig = $otpSession->otp->config;
        $this->data['otpLength'] = $otpSession->otp->config->otpCodeLength;
        $this->data['phone'] = $sessionPhone['phone'];
        $this->data['dialcode'] = $sessionPhone['dialcode'];

        $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
        $this->scripts('humanid.formLoginVeriy("", '. $otpConfig->nextResendDelay .');', 'embed');
        $this->render(true, 'recovery/verify');
    }

    public function confirmation_login()
    {
        $this->_app = $this->getAppInfo();
        $sessionPhone = $this->session->userdata('humanId__phone');
        $this->data['phone'] = "+{$sessionPhone['dialcode']}{$sessionPhone['phone']}";

        $this->render(true, 'recovery/confirmation_switch_email');
    }

    public function request_otp()
    {
        $webLoginToken = $this->session->userdata('humanId__loginRequestOtpToken');
        $sessionPhone = $this->session->userdata('humanId__phone');
        $data = [
            'phone' => "+{$sessionPhone['dialcode']}{$sessionPhone['phone']}",
            'lang' => 'en',
            'source' => 'w',
            'token' => $webLoginToken
        ];

        $requestOtp = $this->humanid->requestOtpForRecovery($data);
        // If not success
        if (!$requestOtp->success) {
            $this->handleErrorRequestOtp($requestOtp);
        }

        // Save to session
        $this->session->set_userdata('humanId__requestOtpRecovery', $requestOtp->data);

        redirect(site_url('recovery/verify_otp'));
    }

    public function verify_email()
    {
        $this->_app = $this->getAppInfo();
        $this->_checkRequestOtpSession();
        $this->data['app'] = $this->_app;

        if ($error_message = $this->session->flashdata('error_message')) {
            $this->data['error_message'] = $error_message;
        }

        $this->data['redirectUrl'] = site_url('redirect_app');
        $this->data['wrongNumberAndEmail'] = false;
        $isWrongPhoneOrNumber = $this->session->flashdata('email_or_phone_not_found');
        if ($isWrongPhoneOrNumber) {
            $this->data['wrongNumberAndEmail'] = $isWrongPhoneOrNumber;
        }
        $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
        $this->scripts('humanid.formLogin("", ' . $this->pc->code_js . ');', 'embed');
        $this->scripts('humanid.modal()', 'embed');
        $this->render(true, 'recovery/verify-email');
    }

    public function verify_email_process()
    {
        // Validate
        $this->form_validation->set_rules('email', 'email', 'required|valid_email');
        $this->form_validation->set_rules('phone', $this->lg->phone, 'required|numeric|min_length[4]|max_length[14]', array(
            'required' => $this->lg->form->phoneRequired,
            'numeric' => $this->lg->form->phoneNumeric,
            'min_length' => $this->lg->form->phoneMin,
            'max_length' => $this->lg->form->phoneMax
        ));
        if ($this->form_validation->run() === false) {
            $this->_first_error_msg();
            if (isset($this->data['error_message'])) {
                $this->session->set_flashdata('error_message', $this->data['error_message']);
            }
            redirect(site_url('recovery/verify_email'));
        }

        $redirectUrl = 'recovery/verify_email_code';
        $phone = $this->input->post('phone', true);
        $dialcode = $this->input->post('dialcode', true);
        $email = $this->input->post('email', true);

        $recoveryVerifySession = $this->session->userdata('humanId__verifyOtpRecovery');

        $this->session->set_userdata('humanId__otpEmail', [
            'email' => $email,
            'phone' => $phone,
            'dialcode' => $dialcode
        ]);
        $data = [
            'recoveryEmail' => $email,
            'oldPhone' => "+{$dialcode}{$phone}",
            'token' => $recoveryVerifySession->token,
            'source' => 'w',
        ];

        $userLogin = $this->session->userdata('humanId__userLogin');
        // Check account is inactive then login with exchange token
        if ($userLogin !== null && $userLogin->user->isActive === false) {
            $loginRecoveryResult = $this->humanid->accountLoginRecovery([
                'exchangeToken' => $userLogin->exchangeToken,
                'source' => 'w',
            ]);

            if (!$loginRecoveryResult->success) {
                $modal = (object) array(
                    'title' => $this->lg->errorPage,
                    'code' => $loginRecoveryResult->code,
                    'message' => $this->lg->error->tokenExpired,
                    'url' => $this->_app->redirectUrlFail ?? site_url('demo'),
                );
                $this->session->set_flashdata('modal', $modal);
                $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
                redirect(site_url('error'));
            }
            $data['token'] = $loginRecoveryResult->data->token;
            $this->session->set_userdata('humanId__loginRecovery', $loginRecoveryResult->data);
        }

        // Request OTP Transfer Account
        $response = $this->humanid->requestOtpTransferAccount($data);
        if ($response->code === self::WRONG_NUMBER || $response->code === self::WRONG_EMAIL) {
            $this->session->set_flashdata('email_or_phone_not_found', true);
            redirect(site_url('recovery/verify_email'));
        }
        if (!$response->success) {
            $this->handleErrorRequestOtpTransferAccount($response);
        }

        $this->session->set_userdata('humanId__otpTransferAccount', $response->data);

        redirect($redirectUrl);
    }

    public function verify_email_code()
    {
        $this->_app = $this->getAppInfo();
        $code = $this->input->post('code');
        if (isset($code) && $code[count($code) - 1] !== '') {
            $session = $this->session->userdata('humanId__userLogin');
            $verifyOtpRecovery = $this->session->userdata('humanId__verifyOtpRecovery');
            $loginRecovery = $this->session->userdata('humanId__loginRecovery');
            $data = [
                'otpCode' => implode('', $code),
                'token' => $verifyOtpRecovery->token ?? $loginRecovery->token,
                'source' => "w"
            ];
            $user = $session->user;
            if ($user->isActive === false) {
                $loginRecovery = $this->session->userdata('humanId__loginRecovery');
                $data['token'] = $loginRecovery->token;
            }
            $response = $this->humanid->verifyOtpTransferAccount($data);
            if (!$response->success) {
                $redirectUrl = site_url('error');
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

                if ($response->code == 'ERR_5') {
                    $this->session->set_flashdata('error_otp', 'Incorrect code. Please try again.');
                    $redirectUrl = 'recovery/verify_email_code';
                }

                $this->session->set_flashdata('modal', $modal);
                $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
                redirect($redirectUrl);
            }

            $this->session->set_userdata('humanId__verifyTransferAccount', $response->data);

            redirect(site_url('recovery/change_number_success'));
        }

        $otpTransferAccount = $this->session->userdata('humanId__otpTransferAccount');
        $otpConfig = $otpTransferAccount->config;
        $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
        $this->data['app'] = $this->_app;
        $this->data['otpLength'] = $otpConfig->otpCodeLength;
        $this->scripts('humanid.formLoginVeriy("", "60");', 'embed');
        $this->render(true, 'recovery/verify-email-code');
    }

    public function request_email()
    {
        $sessionPhone = $this->session->userdata('humanId__otpEmail');
        $dialcode = $sessionPhone['dialcode'];

        $phone = $sessionPhone['phone'];
        $email = $sessionPhone['email'];
        $recoveryVerifySession = $this->session->userdata('humanId__verifyOtpRecovery');

        $redirectUrl = 'recovery/verify_email_code';
        $data = [
            'recoveryEmail' => $email,
            'oldPhone' => "+{$dialcode}{$phone}",
            'token' => $recoveryVerifySession->token,
            'source' => 'w'
        ];
        // Request OTP Transfer Account
        $response = $this->humanid->requestOtpTransferAccount($data);
        if ($response->code === self::WRONG_NUMBER || $response->code === self::WRONG_EMAIL) {
            $this->session->set_flashdata('email_or_phone_not_found', true);
            redirect(site_url('recovery/verify_email'));
        }
        if (!$response->success) {
            $this->handleErrorRequestOtpTransferAccount($response);
        }

        $this->session->set_userdata('humanId__otpTransferAccount', $response->data);

        redirect($redirectUrl);
    }

    public function change_number_success()
    {
        $transferAccountData = $this->session->userdata('humanId__verifyTransferAccount');
        $this->_app = $this->getAppInfo();
        $this->data['app'] = $this->_app;
        if ($this->input->post('redirect')) {
            $redirectUrl = $transferAccountData->redirectUrl;
            $this->session->unset_userdata('humanId__phone');
            $this->session->unset_userdata('humanId__appInfo');
            redirect($redirectUrl);
        }
        $this->data['appName'] = $transferAccountData->app->name;
        $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
        $this->render(true, 'recovery/change-number-success');
    }

    public function skip()
    {
        $userLogin = $this->session->userdata('humanId__userLogin');
        $redirectUrl = $userLogin->redirectUrl;
        $this->session->unset_userdata('humanId__userLogin');
        redirect($redirectUrl);
    }

    public function add()
    {
        $this->_app = $this->getAppInfo();
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
        $this->_app = $this->getAppInfo();
        $this->data['app'] = $this->_app;
        $this->render(true, 'recovery/success');
    }

    public function invalid()
    {
        $this->_app = $this->getAppInfo();
        $this->data['app'] = $this->_app;
        $this->scripts('humanid.modal()', 'embed');
        $this->render(true, 'recovery/invalid');
    }

    private function _checkRequestOtpSession()
    {
        $session = $this->session->has_userdata('humanId__requestOtpRecovery');
        $phone = $this->session->has_userdata('humanId__phone');
        if ($session === false || $phone === false) {
            $this->session->unset_userdata('humanId__phone');
            redirect(site_url('recovery/new_number'));
        }
    }

    private function _checkVerifyOtpSession()
    {
        $session = $this->session->has_userdata('humanId__verifyOtpRecovery');
        if ($session === false) {
            $this->session->unset_userdata('humanId__phone');
            redirect(site_url('recovery/new_number'));
        }
    }

    private function handleErrorVerifyOtp($response)
    {
        // Validate if expired
        if ($response->message == "jwt expired") {
            $modal = (object) array(
                'title' => $this->lg->errorPage,
                'code' => $response->code,
                'message' => $this->lg->error->tokenExpired,
                'url' => $this->_app->redirectUrlFail ?? site_url('demo'),
            );
            $this->session->unset_userdata('humanId__phone');
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

        if ($response->code == 'ERR_15') {
            // OTP Session has expired
            $modal->url = $this->_app->redirectUrlFail;
        }

        if ($response->code == 'ERR_13') {
            // Failed attempt has reached limit
            $modal->url = $this->_app->redirectUrlFail;
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

    private function handleErrorRequestOtp($response)
    {
        $code = $response->code;
        $modal = (object) [
            'title' => $this->lg->errorPage,
            'code' => $code ?? '',
            'message' => $response->message ?? '',
            'url' => site_url('recovery/new_number')
        ];


        if ($response->code == "ERR_10") {
            $this->data['error_message'] = $response->message;
        }

        if ($response->message == "jwt expired") {
            $modal = (object) [
                'title' => $this->lg->errorPage,
                'code' => $code ?? '',
                'message' => $this->lg->error->tokenExpired,
                'url' => $this->_app->redirectUrlFail ?? site_url('demo')
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

    public function handleErrorRequestOtpTransferAccount($response)
    {
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

        if ($response->message === "jwt expired") {
            $modal = (object) [
                'title' => $this->lg->errorPage,
                'code' => $code ?? '',
                'message' => $this->lg->error->tokenExpired,
                'url' => $this->_app->redirectUrlFail ?? site_url('demo')
            ];
            $this->session->unset_userdata('humanId__phone');
            $this->session->set_flashdata('modal', $modal);
            $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
            redirect(site_url('error'));
        }

        $this->session->set_flashdata('modal', $modal);
        $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
        redirect(site_url('error'));
    }

    private function handleErrorSetEmailRecovery($response)
    {
        $code = $response->code;
        $redirectUrl = site_url('error');
        $modal = (object) [
            'title' => $this->lg->errorPage,
            'code' => $code ?? '',
            'message' => $response->message ?? '',
            'url' => $this->_app->redirectUrlFail,
        ];

        if ($response->code === "500") {
            $modal->url = site_url('error');
        }

        if ($response->message === "jwt expired") {
            $modal->message = $this->lg->error->tokenExpired;
            $this->session->unset_userdata('humanId__appInfo');
            $redirectUrl = site_url('error');
        }

        $this->session->unset_userdata('humanId__appInfo');
        $this->session->unset_userdata('humanId__phone');
        $this->session->set_flashdata('modal', $modal);
        $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
        redirect($redirectUrl);
    }
}
