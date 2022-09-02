<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once 'BaseController.php';

class Recovery_exist extends BaseController
{
    public function switch_number()
    {
        $this->scripts('humanid.formLogin("", ' . $this->pc->code_js . ');', 'embed');
        $this->scripts('humanid.modal()', 'embed');
        $this->render(true, 'recovery-exist/switch-number');
    }

    public function verification_switch_number()
    {
        $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
        $this->data['app'] = $this->_app;
        $this->scripts('humanid.formLoginVeriy("", "60");', 'embed');
        $this->render(true, 'recovery-exist/verification-switch-number');
    }

    public function block()
    {
        $this->scripts('humanid.formLogin("", ' . $this->pc->code_js . ');', 'embed');
        $this->scripts('humanid.modal()', 'embed');
        $this->render(true, 'recovery-exist/block');
    }

    public function disabled()
    {
        $this->scripts('humanid.formLogin("", ' . $this->pc->code_js . ');', 'embed');
        $this->scripts('humanid.modal()', 'embed');
        $this->render(true, 'recovery-exist/disabled');
    }

    public function change_number_success()
    {
        $this->render(true, 'recovery-exist/change-number-success');
    }

    public function identify_failure()
    {
        $this->scripts('humanid.modal()', 'embed');
        $this->render(true, 'recovery-exist/identify-failure');
    }

    public function recovery()
    {
        $this->_app = $this->getAppInfo();
        $this->checkUserLogin();
        if ($error_message = $this->session->flashdata('error_message')) {
            $this->data['error_message'] = $error_message;
        }

        $this->data['app'] = $this->_app;
        $this->scripts('humanid.modal()', 'embed');
        $this->render(true, 'recovery-exist/recovery');
    }

    public function recovery_process()
    {
        $this->checkUserLogin();
        $this->form_validation->set_rules('email', 'email', 'required|valid_email');
        if ($this->form_validation->run() === false) {
            $this->_first_error_msg();
            if (isset($this->data['error_message'])) {
                $this->session->set_flashdata('error_message', $this->data['error_message']);
            }
            redirect(site_url('recovery-exist/recovery'));
        }

        $sessionLogin = $this->session->userdata('humanId__phone');
        $phone = $sessionLogin['phone'];
        $dialcode = $sessionLogin['dialcode'];
        $email = $this->input->post('email', true);

        // Save to session for resend
        $this->session->set_userdata('humanId__otpEmail', [
            'email' => $email,
            'phone' => $phone,
            'dialcode' => $dialcode
        ]);

        // Get user login from OTP
        $userLogin = $this->session->userdata('humanId__userLogin');
        // Request login recovery
        $loginRecoveryResponse = $this->humanid->accountLoginRecovery([
            'exchangeToken' => $userLogin->exchangeToken,
            'source' => 'w',
        ]);
        if (!$loginRecoveryResponse->success) {
            $this->handleError($loginRecoveryResponse);
        }

        // Save login recovery session
        $this->session->set_userdata('humanId__loginRecovery', $loginRecoveryResponse->data);

        // Request OTP Transfer Account
        $response = $this->humanid->requestOtpTransferAccount([
            'recoveryEmail' => $email,
            'oldPhone' => "+{$dialcode}{$phone}",
            'token' => $loginRecoveryResponse->data->token,
            'source' => 'w',
        ]);
        if (!$response->success) {
            $this->handleErrorRequestOtpTransferAccount($response);
        }

        // Save response Request OTP Transfer
        $this->session->set_userdata('humanId__otpTransferAccount', $response->data);

        redirect('recovery/verify_email_code');
    }

    public function verification()
    {
        $this->_app = $this->getAppInfo();
        $this->form_validation->set_rules('email', 'email', 'required|valid_email');
        $this->styles('input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}input[type=number] {-moz-appearance:textfield;}', 'embed');
        $this->data['app'] = $this->_app;
        $this->scripts('humanid.formLoginVeriy("", "60");', 'embed');
        $this->render(true, 'recovery-exist/verification');
    }

    public function instead_login()
    {
        $this->_app = $this->getAppInfo();
        $this->checkUserLogin();
        $userLogin = $this->session->userdata('humanId__userLogin');
        $this->data['app'] = $this->_app;
        $user = $userLogin->user;
        $this->data['hasSetupRecovery'] = $user->hasSetupRecovery;
        if ($this->session->has_userdata('humanId__phone')) {
            $humanIdPhone = $this->session->userdata('humanId__phone');
            $this->data['phone'] = $humanIdPhone['phone'];
            $this->data['dialcode'] = $humanIdPhone['dialcode'];
        }
        $this->render(true, 'recovery-exist/instead-login');
    }

    private function handleErrorRequestOtpTransferAccount($response)
    {
        if ($response->code === self::WRONG_NUMBER || $response->code === self::WRONG_EMAIL) {
            $this->session->set_flashdata('email_or_phone_not_found', true);
            redirect(site_url('recovery-exist/recovery'));
        }

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

    private function handleError($response)
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
