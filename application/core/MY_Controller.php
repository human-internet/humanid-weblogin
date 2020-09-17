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
        
        $this->init_logs();
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

    public function init_logs($res=array())
    {
        if(getenv('LOG_ACTIVITY'))
        {
            $token = $this->input->get('t', TRUE);
            if(!$token){
                $token = 'no-token-jwt';
            }
            $appId = $this->input->get('a', TRUE);
            if(!$appId){
                $appId = 'no-appId';
            }
            $lang = $this->input->get('lang', TRUE);
            if(!$lang){
                $lang = 'no-lang';
            }
            $error = (isset($res['error'])) ? $res['error'] : $this->session->flashdata('error_message');
            $url = (isset($res['url'])) ? $res['url'] : current_url();
            $method = ($_POST) ? 'post' : 'get';
            $this->load->library('user_agent');
            $data = array(
                'Token'     => $token,
                'AppId'     => $appId,
                'Language'  => $lang,
                'IPAddress' => $this->input->ip_address(),
                'Browser'   => $this->agent->browser(),
                'Version'   => $this->agent->version(),
                'Mobile'    => $this->agent->mobile(),
                'Url'       => $url,
                'method'	=> $method,
                'Error'     => $error,
                'Created'   => date('Y-m-d H:i:s'),
            );

            $path = getenv('LOG_PATH');
            $path = (empty($path)) ? str_replace('system','logs',BASEPATH) : $path;
            $file = $path . date('Ymd').'.csv';
            $csv = '';
            if(!file_exists($file))
            {
                $first = true;
                foreach ($data as $k => $v) {
                    if(!$first){
                        $csv .= ",";
                    }
                    $csv .= $k;
                    if($first){
                        $first = false;
                    }
                }
                $csv .= "\n";
            }
            $first = true;
            foreach ($data as $k => $v) {
                if(!$first){
                    $csv .= ",";
                }
                $csv .= $this->csv_field($v);
                if($first){
                    $first = false;
                }
            }
            $csv .= "\n";
            $csv_handler = fopen($file,'a+');
            fwrite($csv_handler,$csv);
            fclose($csv_handler);
        }
    }

    private function csv_field($string) 
    {
        if(strpos($string, ',') !== false || strpos($string, '"') !== false || strpos($string, "\n") !== false) {
            $string = '"' . str_replace('"', '""', $string) . '"';
        }
        return $string;
    }
}