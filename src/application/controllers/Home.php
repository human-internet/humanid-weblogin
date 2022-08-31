<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Humanid            $humanid
 * @property CI_Form_validation $form_validation
 * @property CI_Session         $session
 */
class Home extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->render();
    }

    public function error()
    {
        $modal = $this->session->flashdata('modal');
        if ($modal) {
            $this->data['modal'] = $modal;
        }
        $error_message = $this->session->flashdata('error_message');
        if ($error_message) {
            $this->data['error_message'] = $error_message;
        }

        // Reset sessions
        $this->session->unset_userdata('humanId__webLoginToken');

        $this->render();
    }
}
