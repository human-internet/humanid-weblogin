<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends MY_Controller {

	function __construct()
    {
		parent::__construct();
		$this->load->library('session');
	}

	public function index()
	{
		$this->render();
	}

	public function error()
	{
		$error_message = $this->session->flashdata('error_message');
		if($error_message){
			$this->data['error_message'] = $error_message;
		}
		$this->render();
	}
}