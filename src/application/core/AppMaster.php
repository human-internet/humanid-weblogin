<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class AppMaster extends CI_Controller {

    public $content;
    public $folder = '';
    public $data = array();
    public $master_name = 'master';

    function __construct($param=array())
    {
        parent::__construct();
        
        $this->content = (object) array(
                                'styles' => '',
                                'scripts' => '',
                                'view' => ''
                            );
        if(isset($param['master_name'])){
            $this->master_name = $param['master_name'];
        }
        if(isset($param['folder'])){
            $this->folder = $param['folder'].'/';
        }
    }

    public function render($master=TRUE,$view_name=FALSE)
    {
        //Load content
        $this->content($view_name);
        
        if($master)
        {
            $data = array();
            foreach($this->content as $label => $value){
                $data[$label] = $value;
            }

            $this->load->view($this->folder . $this->master_name, $data);
        }
        else
        {
            echo $this->content->view;
            exit;
        }
    }

    public function content($view_name=false)
    {
        if(!$view_name)
        {
            $template = strtolower($this->router->class.'_'.$this->router->method);
    	}
        else{
            $template = $view_name;
        }
        $this->content->view = $this->load->view($this->folder . $template, $this->data, TRUE);
    }

    /*
     * styles (CSS)
     * 
     * $data = Bisa berupa multi url (array) atau sigle url css. Ini berlaku jika type = inline
     * $type = inline (base url style) dan embed (manual style)
     * $assets = 1= Frontend, 2= Webtools
     * 
     * Modified by haris 19-07-2016
     */
    public function styles($data=null, $type='inline', $assets = 0)
    {
        $styles = '';
    	if($type=='embed'){
            $styles .= '<style type="text/css">'.$data.'</style>'; 
    	}
        else{
            $base = base_url();
            if($assets == 1){
                $base = assets_url();
            }
            elseif ($assets == 2) {
                $base = assets_url('', TRUE);
            }
            if(is_array($data)){
                foreach ($data as $r){
                    $styles .= '<link rel="stylesheet" type="text/css" href="'.$base.$r.'" />';
                }
            }
            else{
                $styles .= '<link rel="stylesheet" type="text/css" href="'.$base.$data.'" />';
            }
    	}

    	$this->content->styles .= $styles;
    }

    /*
     * scripts (Javascript)
     * 
     * $data = Bisa berupa multi url (array) atau sigle url js. Ini berlaku jika type = inline dan outer. Untuk type view $data adalah path view.
     * $type:
     *      inline  = base url javascript
     *      embed   = manual javascript
     *      outer   = full url javascript
     *      view    = javascript ada didalam view html
     *$parse = untuk parsing data jika type adalah view
     * 
     * Modified by haris 19-07-2016
     */
    public function scripts($data=null,$type='inline',$parse=array())
    {
        $scripts = '';
    	if($type == 'embed'){
            $scripts .= '<script>'.$data.'</script>';
        }
        else if($type == 'view'){
            $template = empty($data) ? strtolower($this->router->class.'_'.$this->router->method.'_js') : $data;
            $scripts .= $this->load->view($this->folder . $template,$parse,TRUE);
        }
        else if($type=='outer'){
            if(is_array($data)){
                foreach ($data as $r){
                    $scripts .= '<script src="'.$r.'"></script>';
                }
            }
            else{
                $scripts .= '<script src="'.$data.'"></script>';
            }
    	}
        else{
            $base = base_url();
            if($parse == 1){
                $base = assets_url();
            }
            elseif ($parse == 2) {
                $base = assets_url('', TRUE);
            }
            if(is_array($data)){
                foreach ($data as $r){
                    $scripts .= '<script src="'.$base.$r.'"></script>';
                }
            }
            else{
                $scripts .= '<script src="'.$base.$data.'"></script>';
            }
    	}

    	$this->content->scripts .= $scripts;
    }
    
    protected function set_nocache_headers()
    {
        $this->output->set_header('HTTP/1.0 200 OK');
        $this->output->set_header('HTTP/1.1 200 OK');
        $this->output->set_header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()).' GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache');
        $this->output->set_header('Pragma: no-cache, must-revalidate');
    }
}