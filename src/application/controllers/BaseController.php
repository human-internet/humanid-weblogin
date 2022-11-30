<?php

/**
 * @property Humanid            $humanid
 * @property CI_Form_validation $form_validation
 * @property CI_Session         $session
 */
class BaseController extends MY_Controller
{
    protected const ERR_USER_NOT_FOUND = 'ERR_33'; //User not found
    protected const ERR_EMAIL_RECOVERY_NOT_SETUP = 'ERR_34'; // Account Recovery has not been set-up
    protected const ERR_INVALID_EMAIL = 'ERR_35'; // Invalid email for account recovery
    protected const JWT_EXPIRED = 'jwt expired';

    protected const ERR_CANCELLED = 'CANCELLED';
    protected const MESSAGE_CANCELLED = 'Log-in is cancelled by User';

    protected const ERR_INTERNAL = '500';
    protected const MESSAGE_INTERNAL = 'Internal Error';

    protected $_app;

    function __construct()
    {
        parent::__construct();
        $this->load->library('humanid');
        $this->load->library('form_validation');
    }

    protected function getAppInfo($new = false)
    {
        $hasAppInfo = $this->session->has_userdata('humanId__appInfo');
        if (!$hasAppInfo || $new) {
            $this->getApp();
        }

        return $this->session->userdata('humanId__appInfo');
    }

    private function getApp() {
        $appId = $this->input->get('a', TRUE);
        if ($appId === null) {
            $this->session->set_flashdata('error_message', $this->lg->error->appId);
            redirect(site_url('error'));
        }
        $s = $this->input->get('s', TRUE);
        $source = $s;
        if (!in_array($s, ['m', 'w'])) {
            $source = 'w';
        }
        $result = $this->humanid->getAppInfo($appId, $source);
        if (!$result->success) {
            $this->session->set_flashdata('error_message', $result->message);
            redirect(site_url('error'));
        }
        $appInfo = $result->data->app;
        $appInfo->id = $appId;
        $appInfo->source = $source;
        // Save App Info to session
        log_message('debug', ' > set_userdata humanId__appInfo: '. json_encode($appInfo));
        $this->session->set_userdata('humanId__appInfo', $appInfo);
    }

    protected function checkUserLogin()
    {
        $this->_app = $this->getAppInfo();
        $this->data['app'] = $this->_app;
        $hasUserLogin = $this->session->has_userdata('humanId__userLogin');
        if (!$hasUserLogin) {
            $code = 'WSDK_01';
            $message = $this->lg->error->sessionExpired;
            $error_url = $this->_app->redirectUrlFail . '?code=' . $code . '&message=' . urlencode($message);
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

    protected function clearSessions()
    {
        // Login
        $this->session->unset_userdata('humanId__appInfo');
        $this->session->unset_userdata('humanId__phone');
        $this->session->unset_userdata('humanId__userLogin');
        $this->session->unset_userdata('humanId__loginRequestOtpToken');

        // Recovery
        $this->session->unset_userdata('humanId__requestOtpRecovery');
        $this->session->unset_userdata('humanId__verifyOtpRecovery');
        $this->session->unset_userdata('humanId__loginRequestOtpToken');
        $this->session->unset_userdata('humanId__loginRecovery');
        $this->session->unset_userdata('humanId__otpTransferAccount');
        $this->session->unset_userdata('humanId__verifyTransferAccount');
        $this->session->unset_userdata('humanId__otpEmail');

        $this->session->unset_userdata('humanId__newAccount');
    }

    protected function _first_error_msg()
    {
        $error = validation_errors();
        $error = preg_split('/\r\n|\r|\n/', $error);
        if (count($error) > 0 && !empty($error[0])) {
            $this->data['error_message'] = trim($error[0]);
        }
    }
}
