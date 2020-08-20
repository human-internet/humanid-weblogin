<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends MY_Controller {

	public function index()
	{
		$token = $this->input->get('token', TRUE);

		$this->load->library('form_validation');
		$this->form_validation->set_rules('phone', 'Phone', 'required|numeric');
		$this->form_validation->set_rules('dialcode', 'Country Code', 'required|numeric');
		if($this->form_validation->run() == TRUE)
		{
			$phone = $this->input->post('phone', TRUE);
			$dialcode = $this->input->post('dialcode', TRUE);
			
			redirect(site_url('login/verify?token='.md5(time())));
		}
		$this->scripts('humanid.formLogin();','embed');
		$this->render();
	}

	public function verify()
	{
		$token = $this->input->get('token', TRUE);
		$success = 0;

		$this->load->library('form_validation');
		$this->form_validation->set_rules('code_1', 'Code', 'required|numeric');
		$this->form_validation->set_rules('code_2', 'Code', 'required|numeric');
		$this->form_validation->set_rules('code_3', 'Code', 'required|numeric');
		$this->form_validation->set_rules('code_4', 'Code', 'required|numeric');
		if($this->form_validation->run() == TRUE)
		{
			$code_1 = $this->input->post('code_1', TRUE);
			$code_2 = $this->input->post('code_2', TRUE);
			$code_3 = $this->input->post('code_3', TRUE);
			$code_4 = $this->input->post('code_4', TRUE);
			$otp_code = $code_1 . $code_2 . $code_3 . $code_4;

			$success = 1;
		}

		$this->data['success'] = $success;
		$this->scripts('var success = '.$success.'; humanid.formLoginVeriy();','embed');
		$this->render();
	}

	public function resend()
	{
		redirect(site_url('login/verify?token='.md5(time())));
	}
}
