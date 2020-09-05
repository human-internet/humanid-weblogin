<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'core/AppMaster.php';
class MY_Controller extends AppMaster {

    var $lg;
    
    function __construct()
    {
        parent::__construct(array(
            'folder' => 'frontend'
        ));
        $this->load->library('session');

        $lang = $this->input->get('lang', TRUE);
		$this->lg = $this->init_language($lang);
		$this->data['lang'] = $this->lg;
    }

    public function init_language($lang='')
    {
        $lang = strtolower($lang);
        $lang_session = $this->session->userdata('humanid_language');
        if($lang_session && empty($lang)){
            $lang = $lang_session;
        }
        else{
            $this->session->set_userdata(array('humanid_language' => $lang));
        }

        $path = str_replace('system','language',BASEPATH);
        $filename = $path.$lang.'.json';
        if(!file_exists($filename)){
            $lang = 'en';
            $filename = $path.$lang.'.json';
            $this->session->set_userdata(array('humanid_language' => $lang));
        }

        $string = file_get_contents($filename);
        $row = json_decode($string, FALSE);
        $row->id = $lang;
        
        return $row;
    }
}