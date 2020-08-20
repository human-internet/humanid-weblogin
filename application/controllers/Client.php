<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client extends CI_Controller {

	public function index()
	{
		$this->load->view('client');
	}

	public function success()
	{
		$token = $this->input->get('token', TRUE);
		$token = base64_decode($token);
		$this->load->library('humanid');
		$res = $this->humanid->exchange($token);
		//echo '<pre>';
		//print_r($res);
		//exit;
		$data = array(
			'error_message' => '',
			'appUserId' => 0,
			'exchangeToken' => $token
		);
		if($res['send'])
		{
			$result = $res['result'];
			if($result['success'])
			{
				$data['appUserId'] = $result['data']['appUserId'];
			}
			else{
				$data['error_message'] = $result['message'].' ['.$result['code'].']';
			}
		}
		else{
			$data['error_message'] = 'An error occurred while sending data, please repeat';
		}
		
		$this->load->view('client_success',$data);
	}
}
