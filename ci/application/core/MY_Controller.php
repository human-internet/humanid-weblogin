<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'core/AppMaster.php';
class MY_Controller extends AppMaster {

    function __construct()
    {
        parent::__construct(array(
            'folder' => 'frontend'
        ));
    }
}