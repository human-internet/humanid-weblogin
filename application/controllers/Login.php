<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends MY_Controller {

	var $path;
    function __construct()
    {
		parent::__construct();
		$this->load->library('session');
		$this->load->library('humanid');
		$this->load->library('form_validation');
		$this->path = getenv('SESSION_PATH');
		$this->path = (empty($this->path)) ? str_replace('system','sessions',BASEPATH) : $this->path;
	}

	public function request_token()
	{
		$clientId = $this->input->post('clientId', true);
		$clientSecret = $this->input->post('clientSecret', true);
		//$clientId = getenv('HUMANID_SERVER_ID');
		//$clientSecret = getenv('HUMANID_SERVER_SECRET');
		if($clientId && $clientSecret)
		{
			$res = $this->humanid->session($clientId,$clientSecret);
			if($res['send'])
			{
				$result = $res['result'];
				if($result['success'])
				{
					$data = json_encode($result['data']);
					$token = $this->_token($result['data']['session']['token']);
					$path = $this->path;
					$session = fopen($path.$token.".txt", "w") or die("Unable to open file!");
					fwrite($session, $data);
					fclose($session);
					$json = array(
						'success' => 1,
						'code' => $result['code'],
						'message' => $result['message'],
						'login' => array(
							'url' => site_url('login?token='.$token),
							'token' => $token
						)
					);
				}
				else{
					$json = array(
						'success' => 0,
						'code' => $result['code'],
						'message' => $result['message']
					);
				}
			}
			else{
				$json = array(
					'success' => 0,
					'code' => '0',
					'message' => 'Error not found'
				);
			}
		}
		else{
			$json = array(
				'success' => 0,
				'code' => '1',
				'message' => 'Client Id and Secret are mandatory'
			);
		}
		
		header('Content-Type: application/json');
		echo json_encode($json);
		exit;
	}

	public function index()
	{
		$session = $this->_row_token();
		$token = $session['token'];

		$this->form_validation->set_rules('phone', 'Phone Number', 'required|numeric|min_length[4]|max_length[14]');
		$this->form_validation->set_rules('dialcode', 'Country Code', 'required|numeric');

		$phone = $this->input->post('phone', TRUE);
		$dialcode = $this->input->post('dialcode', TRUE);

		if($this->form_validation->run() == TRUE)
		{
			$res = $this->humanid->request_otp($dialcode,$phone,$session['session']['token']);
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
		$this->data['session'] = $session;
		$this->scripts('humanid.formLogin("'.$set_number.'");','embed');
		$this->render();
	}

	public function verify()
	{
		$session = $this->_row_token(FALSE);
		$login = $this->_row_login($session);

		$remaining = $this->input->post('remaining', TRUE);
		$remaining = ($remaining=='') ? 60 : intval($remaining);
		if($remaining <= 0){
			redirect(site_url('login?token='.$login['token']));
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
			//echo '<pre>';print_r($res);exit;
			if($res['send'])
			{
				$result = $res['result'];
				if($result['success'])
				{
					$data = array(
						'humanid_login' => NULL
					);
					$this->session->set_userdata($data);

					@unlink($this->path.$login['token'].'.txt');

					$success = 1;
					$this->data['redirectUrl'] = $result['data']['redirectUrl'];
				}
				else{
					if($result['code'] == 'ERR_13'){
						redirect(site_url('login?token='.$login['token']));
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
		$this->data['session'] = $session;
		$this->scripts('humanid.formLoginVeriy('.$success.','.$failAttemptLimit.');','embed');
		$this->render();
	}

	public function resend()
	{
		$session = $this->_row_token(FALSE);
		$login = $this->_row_login($session);

		$error_message = '';
		$res = $this->humanid->request_otp($login['dialcode'],$login['phone'],$session['session']['token']);
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
				$error_message = $result['message'];
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
	
	private function _row_token($expired = TRUE)
	{
		$token = $this->input->get('token', TRUE);
		if($token)
		{
			$path = $this->path;
			$file = $token.".txt";
			if(file_exists($path.$file))
			{
				$file_name = $path.$file;
				$myfile = fopen($file_name, "r") or die("Unable to open file!");
				$data = fread($myfile,filesize($file_name));
				fclose($myfile);
				$row = json_decode($data, TRUE);
				if(!empty($row))
				{
					if($expired)
					{
						$expiredAt = date('Y-m-d H:i:s', $row['session']['expiredAt']);
						$now = date('Y-m-d H:i:s');
						if($expiredAt >= $now)
						{
							$row['token'] = $token;
							return $row;
						}
						else{
							$this->session->set_flashdata('error_message', 'The token has expired');
							redirect(site_url('error'));
						}
					}
					else{
						$row['token'] = $token;
						return $row;
					}
				}
				else{
					$this->session->set_flashdata('error_message', 'The token has expired');
					redirect(site_url('error'));
				}
			}
			else{
				$this->session->set_flashdata('error_message', 'The token has expired');
				redirect(site_url('error'));
			}
		}
		else{
			$this->session->set_flashdata('error_message', 'The token has expired');
			redirect(site_url('error'));
		}
	}

	private function _row_login($session)
	{
		$login = $this->session->userdata('humanid_login');
		//echo '<pre>';print_r($login);print_r($session);exit;
		if($login && !empty($login))
		{
			if($session['token'] == $login['token'])
			{
				$expiredAt = date('Y-m-d H:i:s', $login['result']['data']['session']['expiredAt']);
				$now = date('Y-m-d H:i:s');
				//echo $expiredAt .' - '.$now;exit;
				if($expiredAt >= $now)
				{
					return $login;
				}
				else{
					$this->session->set_flashdata('error_message', 'The token has expired');
					redirect(site_url('error'));
				}
			}
			else{
				$this->session->set_flashdata('error_message', 'The token has expired');
				redirect(site_url('error'));
			}
		}
		else{
			$this->session->set_flashdata('error_message', 'The session has expired');
			redirect(site_url('error'));
		}
	}
}
