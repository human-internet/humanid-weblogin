<?php

/**
 * @property Humanid            $humanid
 * @property CI_Form_validation $form_validation
 * @property CI_Session         $session
 */
class BaseController extends MY_Controller
{
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
        if (!$appId) {
            $this->session->set_flashdata('error_message', $this->lg->error->appId);
            redirect(site_url('error'));
        }
        $result = $this->humanid->getAppInfo($appId);
        if (!$result->success) {
            $this->session->set_flashdata('error_message', $result->message);
            redirect(site_url('error'));
        }
        $appInfo = $result->data->app;
        $appInfo->id = $appId;
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

    protected function _first_error_msg()
    {
        $error = validation_errors();
        $error = preg_split('/\r\n|\r|\n/', $error);
        if (count($error) > 0 && !empty($error[0])) {
            $this->data['error_message'] = trim($error[0]);
        }
    }
}
