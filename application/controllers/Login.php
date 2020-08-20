<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends MY_Controller {

    function __construct()
    {
		parent::__construct();
		$this->load->library('session');
		$this->load->library('humanid');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$token = $this->input->get('token', TRUE);
		if(!$token){
			//Arahkan ke client url
			redirect(site_url('/'));
		}

		$this->form_validation->set_rules('phone', 'Phone', 'required|numeric');
		$this->form_validation->set_rules('dialcode', 'Country Code', 'required|numeric');
		if($this->form_validation->run() == TRUE)
		{
			$phone = $this->input->post('phone', TRUE);
			$dialcode = $this->input->post('dialcode', TRUE);

			$res = $this->humanid->request_otp($dialcode,$phone);
			//echo '<pre>';print_r($res);exit;
			if($res['send'])
			{
				$result = $res['result'];
				if($result['success'])
				{
					$data = array(
						'humanid_login' => array(
							'phone' => $phone,
							'dialcode' => $dialcode,
							'token' => $token,
							'result' => $result
						)
					);
					$this->session->set_userdata($data);

					redirect(site_url('login/verify?token='.$token));
				}
				else{
					$this->data['error_message'] = $result['message'].' ['.$result['code'].']';
				}
			}
			else{
				$this->data['error_message'] = 'An error occurred while sending data, please repeat';
			}
		}
		$this->scripts('humanid.formLogin();','embed');
		$this->render();
	}

	public function verify()
	{
		$login = $this->session->userdata('humanid_login');
		if(!$login || empty($login)){
			//Arahkan ke client url
			redirect(site_url('/'));
		}
		$token = $this->input->get('token', TRUE);
		if(!$token || $token!=$login['token'])
		{
			//Arahkan ke client url
			redirect(site_url('/'));
		}
		$error_message = $this->session->flashdata('error_message');
		if($error_message){
			$this->data['error_message'] = $error_message;
		}
		$success = 0;
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

			$res = $this->humanid->verify_otp($login['dialcode'],$login['phone'],$otp_code);
			if($res['send'])
			{
				$result = $res['result'];
				if($result['success'])
				{
					$data = array(
						'humanid_login' => NULL
					);
					$this->session->set_userdata($data);

					$success = 1;
					$this->data['exchangeToken'] = base64_encode($result['data']['exchangeToken']);
				}
				else{
					$this->data['error_message'] = $result['message'].' ['.$result['code'].']';
				}
			}
			else{
				$this->data['error_message'] = 'An error occurred while sending data, please repeat';
			}
		}
		$failAttemptLimit = ($success) ? 5 :  ($login['result']['data']['config']['failAttemptLimit'] * 60);
		$this->data['row'] = $login;
		$this->data['success'] = $success;
		$this->scripts('var success = '.$success.'; var failAttemptLimit = '.$failAttemptLimit.'; humanid.formLoginVeriy();','embed');
		$this->render();
	}

	public function resend()
	{
		$login = $this->session->userdata('humanid_login');
		if(!$login || empty($login)){
			//Arahkan ke client url
			redirect(site_url('/'));
		}
		$token = $this->input->get('token', TRUE);
		if(!$token || $token!=$login['token'])
		{
			//Arahkan ke client url
			redirect(site_url('/'));
		}
		$error_message = '';
		$res = $this->humanid->request_otp($login['dialcode'],$login['phone']);
		if($res['send'])
		{
			$result = $res['result'];
			if($result['success'])
			{
				$data = array(
					'humanid_login' => array(
						'phone' => $login['phone'],
						'dialcode' => $login['dialcode'],
						'token' => $login['token'],
						'result' => $result
					)
				);
				$this->session->set_userdata($data);
			}
			else{
				$error_message = $result['message'].' ['.$result['code'].']';
			}
		}
		else{
			$error_message = 'An error occurred while sending data, please repeat';
		}
		$this->session->set_flashdata('error_message', $error_message);
		redirect(site_url('login/verify?token='.$login['token']));
	}

	private function _token($string)
	{
		return md5($string.'@#!$%&*@');
	}
}
