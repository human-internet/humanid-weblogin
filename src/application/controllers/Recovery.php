<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once 'BaseController.php';

class Recovery extends BaseController
{
    public function new_number()
    {
        log_message('debug', "  > IM HERE - " . __METHOD__ . ' page');
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
            $requestOtp = $this->humanid->requestOtpForRecovery([
                'phone' => "+{$dialcode}{$phone}",
                'token' => $webLoginToken,
                'lang' => 'en',
                'source' => 'w',
            ]);
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

            log_message('debug', "  > REDIRECT to recovery/verify_otp");
            redirect(site_url('recovery/verify_otp'));
        }
        $this->render(true, 'recovery/new-number');
    }

    public function verify_otp()
    {
        log_message('debug', "  > IM HERE - " . __METHOD__ . ' page');
        $this->_app = $this->getAppInfo();
        $this->_checkRequestOtpSession();

        $this->data['app'] = $this->_app;
        $sessionPhone = $this->session->userdata('humanId__phone');
        $code = $this->input->post('code');
        if (isset($code) && $code[count($code) - 1] !== '') {
            $phoneE164 = "+{$sessionPhone['dialcode']}{$sessionPhone['phone']}";
            log_message('debug', "  > OTP Code submit for $phoneE164");

            $fromRequestOtp = $this->session->userdata('humanId__requestOtpRecovery');
            $sessionToken = $fromRequestOtp->session->token;
            // Verify OTP For New Phone
            $verifyOtpResponse = $this->humanid->verifyOtpForVerifyNewPhone([
                'phone' => $phoneE164,
                'otpCode' => implode('', $code),
                'source' => 'w',
                'token' => $sessionToken
            ]);
            if (!$verifyOtpResponse->success) {
                $this->handleErrorVerifyOtp($verifyOtpResponse);
            }

            $redirectUri = 'recovery/verify_email';

            // Save to session
            $this->session->set_userdata('humanId__verifyOtpRecovery', $verifyOtpResponse->data);
            if ($verifyOtpResponse->data->hasAccount === false) {
                // When OTP Verified then recovery login with exchange token (Login From Recovery)
                $recoveryLogin = $this->humanid->accountRecoveryLogin([
                    'token' => $verifyOtpResponse->data->token,
                    'source' => 'w',
                ]);
                if (!$recoveryLogin->success) {
                    $this->handleErrorRecoveryLogin($recoveryLogin);
                }
                $this->session->set_userdata('humanId__newAccount', $recoveryLogin->data->user->newAccount);
                $this->session->set_userdata('humanId__userLogin', $recoveryLogin->data);
            }

            if ($verifyOtpResponse->data->hasAccount === true) {
                $redirectUri = 'confirmation-login';
            }

            log_message('debug', "  > THEN REDIRECT to : " . $redirectUri);
            // Redirect
            redirect(site_url($redirectUri));
        }

        $otpSession = $this->session->userdata('humanId__requestOtpRecovery');
        $otpConfig = $otpSession->otp->config;
        $this->data['otpLength'] = $otpSession->otp->config->otpCodeLength;
        $this->data['phone'] = $sessionPhone['phone'];
        $this->data['dialcode'] = $sessionPhone['dialcode'];

        $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
        $this->scripts('humanid.formLoginVeriy("", ' . $otpConfig->nextResendDelay . ');', 'embed');
        $this->render(true, 'recovery/verify');
    }

    public function create()
    {
        $this->_app = $this->getAppInfo();
        $this->checkUserLogin();
        $this->data['app'] = $this->_app;
        $newAccount = $this->session->userdata('humanId__newAccount');
        $this->data['newAccount'] = $newAccount ?? false;
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
        $response = $this->humanid->setEmailRecovery([
            'recoveryEmail' => $this->input->post('email'),
            'exchangeToken' => $userLogin->exchangeToken,
            'source' => 'w',
        ]);
        if (!$response->success) {
            $this->handleErrorSetEmailRecovery($response);
        }
        $this->clearSessions();
        redirect($response->data->redirectUrl);
    }

    public function confirmation_login()
    {
        log_message('debug', "  > IM HERE - " . __METHOD__ . ' page');

        $this->_app = $this->getAppInfo();
        $sessionPhone = $this->session->userdata('humanId__phone');
        $this->data['phone'] = "+{$sessionPhone['dialcode']}{$sessionPhone['phone']}";

        $this->render(true, 'recovery/confirmation_switch_email');
    }

    public function request_otp()
    {
        log_message('debug', "  > Recovery > Resend OTP");
        $webLoginToken = $this->session->userdata('humanId__loginRequestOtpToken');
        $sessionPhone = $this->session->userdata('humanId__phone');
        $requestOtp = $this->humanid->requestOtpForRecovery([
            'phone' => "+{$sessionPhone['dialcode']}{$sessionPhone['phone']}",
            'lang' => 'en',
            'source' => 'w',
            'token' => $webLoginToken
        ]);
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

        log_message('debug', "  > IM HERE - " . __METHOD__ . ' page');

        if ($error_message = $this->session->flashdata('error_message')) {
            $this->data['error_message'] = $error_message;
        }

        $this->data['redirectUrl'] = site_url('redirect_app');
        $this->data['wrongNumberAndEmail'] = false;
        $isWrongPhoneOrNumber = $this->session->flashdata('email_or_phone_not_found');
        if ($isWrongPhoneOrNumber) {
            log_message('debug', "  > Oh no, the email or phone is not found :(");
            $this->data['wrongNumberAndEmail'] = $isWrongPhoneOrNumber;
        }
        $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
        $this->scripts('humanid.formLogin("", ' . $this->pc->code_js . ');', 'embed');
        $this->scripts('humanid.modal()', 'embed');
        $this->render(true, 'recovery/verify-email');
    }

    public function verify_email_process()
    {
        log_message('debug', "  > `Send email with verification Code` button clicked");

        $this->_app = $this->getAppInfo();
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

        $redirectUri = 'recovery/verify_email_code';

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
                $message = urlencode($loginRecoveryResult->message);
                $modal = (object) array(
                    'title' => $this->lg->errorPage,
                    'code' => $loginRecoveryResult->code,
                    'message' => $this->lg->error->tokenExpired,
                    'url' => "{$this->_app->redirectUrlFail}?code={$loginRecoveryResult->code}&message={$message}"
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
        if (!$response->success) {
            $this->handleErrorRequestOtpTransferAccount($response);
        }

        $this->session->set_userdata('humanId__otpTransferAccount', $response->data);

        log_message('debug', "  > Success Request otp Then redirect to {$redirectUri}");
        redirect(site_url($redirectUri));
    }

    public function verify_email_code()
    {
        log_message('debug', "  > IM HERE - " . __METHOD__ . ' page');

        $this->_app = $this->getAppInfo();
        $code = $this->input->post('code');
        if (isset($code) && $code[count($code) - 1] !== '') {
            $verifyOtpRecovery = $this->session->userdata('humanId__verifyOtpRecovery');
            $loginRecovery = $this->session->userdata('humanId__loginRecovery');
            $data = [
                'otpCode' => implode('', $code),
                'token' => $verifyOtpRecovery->token ?? $loginRecovery->token,
                'source' => 'w'
            ];
            $userLogin = $this->session->userdata('humanId__userLogin');
            if ($userLogin !== null && $userLogin->user->isActive === false) {
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

                if ($response->code === "500") {
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

            $redirectUri = 'recovery/change_number_success';
            log_message('debug', "  > Success for verifyOtpTransferAccount");
            log_message('debug', "  > Then redirect to {$redirectUri}");
            redirect(site_url($redirectUri));
        }

        $otpTransferAccount = $this->session->userdata('humanId__otpTransferAccount');
        $otpConfig = $otpTransferAccount->config;
        $this->data['app'] = $this->_app;
        $this->data['otpLength'] = $otpConfig->otpCodeLength;

        $this->scripts('humanid.formLoginVeriy("", ' . $otpConfig->nextResendDelay . ');', 'embed');
        $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
        $this->render(true, 'recovery/verify-email-code');
    }

    public function request_email()
    {
        log_message('debug', "  > Resend otp transfer account to Email");

        $this->_app = $this->getAppInfo();
        $sessionPhone = $this->session->userdata('humanId__otpEmail');
        $dialcode = $sessionPhone['dialcode'];
        $phone = $sessionPhone['phone'];
        $email = $sessionPhone['email'];
        $recoveryVerifySession = $this->session->userdata('humanId__verifyOtpRecovery');

        // Request OTP Transfer Account
        $response = $this->humanid->requestOtpTransferAccount([
            'recoveryEmail' => $email,
            'oldPhone' => "+{$dialcode}{$phone}",
            'token' => $recoveryVerifySession->token,
            'source' => 'w'
        ]);
        if (!$response->success) {
            $this->handleErrorRequestOtpTransferAccount($response);
        }
        $this->session->set_userdata('humanId__otpTransferAccount', $response->data);

        $redirectUri = 'recovery/verify_email_code';

        log_message('debug', "  > Resend otp transfer account to Email: Success");
        log_message('debug', "  > Redirect to {$redirectUri}");

        redirect(site_url($redirectUri));
    }

    public function change_number_success()
    {
        log_message('debug', "  > IM HERE - " . __METHOD__ . ' page');

        $this->_app = $this->getAppInfo();
        $transferAccountData = $this->session->userdata('humanId__verifyTransferAccount');
        $this->_app = $this->getAppInfo();
        $this->data['app'] = $this->_app;
        if ($this->input->post('redirect')) {
            $redirectUrl = $transferAccountData->redirectUrl;
            $this->session->unset_userdata('humanId__phone');
            redirect($redirectUrl);
        }
        $this->data['appName'] = $transferAccountData->app->name;
        $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
        $this->render(true, 'recovery/change-number-success');
    }

    public function skip()
    {
        log_message('debug', "  > Skip & Risk Losing Account button clicked");
        $userLogin = $this->session->userdata('humanId__userLogin');
        $redirectUrl = $userLogin->redirectUrl;
        redirect($redirectUrl);
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

    private function handleErrorVerifyOtp($response)
    {
        // Validate if expired
        if ($response->message === self::JWT_EXPIRED) {
            $modal = (object) array(
                'title' => $this->lg->errorPage,
                'code' => $response->code,
                'message' => $this->lg->error->tokenExpired,
                'url' => $this->_app->redirectUrlFail ?? site_url('error'),
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
                'url' => $this->_app->redirectUrlFail ?? site_url('error')
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
        if (
            $response->code === self::ERR_USER_NOT_FOUND ||
            $response->code === self::ERR_EMAIL_RECOVERY_NOT_SETUP ||
            $response->code === self::ERR_INVALID_EMAIL
        ) {
            $this->session->set_flashdata('email_or_phone_not_found', true);
            redirect(site_url('recovery/verify_email'));
        }

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
                'url' => $this->_app->redirectUrlFail ?? site_url('error')
            ];
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
        $code = $response->code ?? '';
        $message = $response->message ?? '';
        $redirectUrl = site_url('error');
        $modal = (object) [
            'title' => $this->lg->errorPage,
            'code' => $code,
            'message' => $message,
            'url' => $this->_app->redirectUrlFail,
        ];

        $error_url = $this->_app->redirectUrlFail . '?code=' . $code . '&message=' . urlencode($message);

        if ($response->code === "500") {
            $modal->url = $error_url;
        }

        if ($message === self::JWT_EXPIRED) {
            $modal->message = $this->lg->error->tokenExpired;
            $modal->url = $error_url;
            $redirectUrl = site_url('error');
        }

        $this->session->set_flashdata('modal', $modal);
        $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
        redirect($redirectUrl);
    }

    private function handleErrorRecoveryLogin($response)
    {
        $code = $response->code ?? '';
        $message = $response->message ?? '';
        $modal = (object) [
            'title' => $this->lg->errorPage,
            'code' => $code,
            'message' => $message,
            'url' => site_url('recovery/new_number')
        ];

        if ($message === self::JWT_EXPIRED) {
            $error_url = $this->_app->redirectUrlFail . '?code=' . $code . '&message=' . urlencode($message);
            $modal->url = $error_url;
            $this->session->unset_userdata('humanId__phone');
            redirect(site_url('error'));
        }

        $this->session->set_flashdata('modal', $modal);
        $this->session->set_flashdata('error_message', $this->lg->error->tokenExpired);
        $redirectUrl = site_url('error');
        redirect($redirectUrl);
    }
}
