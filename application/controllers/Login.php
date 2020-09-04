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
		$token = $this->_token();
		$app = $this->_app_info();

		$this->form_validation->set_rules('phone', 'Phone Number', 'required|numeric|min_length[4]|max_length[14]');
		$this->form_validation->set_rules('dialcode', 'Country Code', 'required|numeric');

		$phone = $this->input->post('phone', TRUE);
		$dialcode = $this->input->post('dialcode', TRUE);

		if($this->form_validation->run() == TRUE)
		{
			$res = $this->humanid->request_otp($dialcode,$phone,$token);
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

					redirect(site_url('login/verify?a='.$app['id'].'&t='.$token));
				}
				else{
					if($result['code'] == '500'){
						redirect($app['redirectUrlFail'] .'?code=500&err='.urlencode('The token has expired'));
					}
					$this->data['error_message'] = $result['message'];
				}
			}
			else{
				$this->data['error_message'] = 'An error occurred while sending data, please repeat';
			}
		}
		else{
			$this->_first_error_msg();
		}

		$login = $this->session->userdata('humanid_login');
		$set_number = '';
		if($dialcode && $phone)
		{
			$set_number .= '+';
			$set_number .= $dialcode;
			$set_number .= $this->_display_phone($phone, '-');
		}
		else if($login)
		{
			$set_number .= '+';
			$set_number .= $login['dialcode'];
			$set_number .= $this->_display_phone($login['phone'], '-');
			$phone = $login['phone'];
		}
		$this->data['phone'] = $phone;
		$this->data['app'] = $app;
		$this->scripts('humanid.formLogin("'.$set_number.'");','embed');
		$this->render();
	}

	public function verify()
	{
		$app = $this->_app_info();
		$login = $this->_login();
		$token = $this->_token($login['token']);

		$remaining = $this->input->post('remaining', TRUE);
		$remaining = ($remaining=='') ? 60 : intval($remaining);
		if($remaining <= 0){
			redirect(site_url('login?a='.$app['id'].'&t='.$token));
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

			$res = $this->humanid->verify_otp($login['dialcode'],$login['phone'],$otp_code,$login['result']['data']['session']['token']);
			if($res['send'])
			{
				$result = $res['result'];
				if($result['success'])
				{
					$this->session->sess_destroy();

					$success = 1;
					$this->data['redirectUrl'] = $result['data']['redirectUrl'];
				}
				else{
					if($result['code'] == 'ERR_13'){
						redirect(site_url('login?a='.$app['id'].'&t='.$token));
					}
					$this->data['error_message'] = $result['message'];
				}
			}
			else{
				$this->data['error_message'] = 'An error occurred while sending data, please repeat';
			}
		}
		else{
			$this->_first_error_msg();
		}
		$failAttemptLimit = ($success) ? 5 :  $remaining;
		$this->data['row'] = $login;
		$this->data['success'] = $success;
		$this->data['display_phone'] = $this->_display_phone($login['phone']);
		$this->data['app'] = $app;
		$this->scripts('humanid.formLoginVeriy('.$success.','.$failAttemptLimit.');','embed');
		$this->render();
	}

	public function resend()
	{
		$app = $this->_app_info();
		$login = $this->_login();
		$token = $this->_token($login['token']);

		$error_message = '';
		$res = $this->humanid->request_otp($login['dialcode'],$login['phone'],$token);
		if($res['send'])
		{
			$result = $res['result'];
			if($result['success'])
			{
				$data = array(
					'humanid_login' => array(
						'phone' => $login['phone'],
						'dialcode' => $login['dialcode'],
						'token' => $token,
						'result' => $result
					)
				);
				$this->session->set_userdata($data);
			}
			else{
				if($result['code'] == '500'){
					redirect($app['redirectUrlFail'] .'?code=500&err='.urlencode('The token has expired'));
				}
				$error_message = $result['message'];
			}
		}
		else{
			$error_message = 'An error occurred while sending data, please repeat';
		}
		$this->session->set_flashdata('error_message', $error_message);
		redirect(site_url('login/verify?a='.$app['id'].'&t='.$token));
	}

	private function _display_phone($phone=0,$text=" ")
	{
		$length = strlen($phone);
		if($length > 3 && $length <= 6)
		{
			$last = $length - 3;
			$phone = preg_replace("/^(\d{3})(\d{".$last."})$/", "$1".$text."$2", $phone);
		}
		else if($length > 6 && $length <= 10){
			$last = $length - 6;
			$phone = preg_replace("/^(\d{3})(\d{3})(\d{".$last."})$/", "$1".$text."$2".$text."$3", $phone);
		}
		else if($length > 10 && $length <= 14){
			$last = $length - 10;
			$phone = preg_replace("/^(\d{3})(\d{3})(\d{4})(\d{".$last."})$/", "$1".$text."$2".$text."$3".$text."$4", $phone);
		}

		return $phone;
	}

	private function _first_error_msg()
    {
        $error = validation_errors();
        $error = preg_split('/\r\n|\r|\n/', $error);
        if(count($error) > 0 && !empty($error[0])){
			$this->data['error_message'] = $error[0];
        }
	}
	
	private function _login()
	{
		$login = $this->session->userdata('humanid_login');
		if($login && !empty($login))
		{
			return $login;
		}
		else{
			$this->session->set_flashdata('error_message', 'The session has expired');
			redirect(site_url('error'));
		}
	}

	private function _app_info()
	{
		$app = $this->session->userdata('humanid_app');
		if($app && !empty($app))
		{
			return $app;
		}
		else{
			$appId = $this->input->get('a', TRUE);
			if($appId)
			{
				$res = $this->humanid->app_info($appId);
				if($res['send'])
				{
					$result = $res['result'];
					if($result['success'])
					{
						$app = $res['result']['data']['app'];
						$app['id'] = $appId;
						$this->session->set_userdata(array('humanid_app' => $app));
						return $app;
					}
					else{
						$this->session->set_flashdata('error_message', $result['message']);
						redirect(site_url('error'));
					}
				}
				else{
					$this->session->set_flashdata('error_message', 'An error occurred while sending data, please repeat');
					redirect(site_url('error'));
				}
			}
			else{
				$this->session->set_flashdata('error_message', 'The AppId not found');
				redirect(site_url('error'));
			}
		}
	}

	private function _token($session_token=FALSE)
	{
		$token = $this->input->get('t', TRUE);
		if($token){
			if($session_token)
			{
				if($session_token == $token)
				{
					return $token;
				}
				else{
					$this->session->set_flashdata('error_message', 'The token has expired');
					redirect(site_url('error'));
				}
			}
			else{
				return $token;
			}
		}
		else{
			$this->session->set_flashdata('error_message', 'The token has expired');
			redirect(site_url('error'));
		}
	}
}
