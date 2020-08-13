<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends MY_Controller {

	public function index()
	{
		redirect(site_url('client'));
		//$this->render();
	}
}