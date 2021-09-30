<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'core/AppMaster.php';

/**
 * @property CI_Input $input
 * @property CI_User_agent $agent
 */
class MY_Controller extends AppMaster
{

    var $lg, $pc, $source;

    function __construct()
    {
        parent::__construct(array(
            'folder' => 'frontend'
        ));
        $this->load->library('session');

        $lang = $this->input->get('lang', TRUE);
        $this->lg = $this->init_language($lang);
        $this->data['lang'] = $this->lg;

        $prio = $this->input->get('priority_country', TRUE);
        $this->pc = $this->init_priority_country($prio);
        $this->data['pc'] = $this->pc;

        $this->source = $this->input->get('s', TRUE);

        $this->init_logs();
    }

    public function init_language($lang = '')
    {
        $lang_session = $this->session->userdata('humanid_language');
        if ($lang_session && empty($lang)) {
            $lang = $lang_session;
        } else {
            $this->session->set_userdata(array('humanid_language' => $lang));
        }

        $path = empty(getenv('LANG_PATH')) ? str_replace('system', 'language', BASEPATH) : getenv('LANG_PATH');
        $filename = $path . $lang . '.json';
        if (!file_exists($filename)) {
            $lang = getenv('LANG_CODE');
            $filename = $path . $lang . '.json';
            $this->session->set_userdata(array('humanid_language' => $lang));
        }

        $string = file_get_contents($filename);
        $row = json_decode($string, FALSE);
        $row->id = $lang;

        return $row;
    }

    private function init_priority_country($code = '')
    {
        $priority_country = $this->session->userdata('humanid_priority_country');
        if ($priority_country && empty($code)) {
            $code = $priority_country;
        } else {
            $this->session->set_userdata(array('humanid_priority_country' => $code));
        }
        $_code = 'us';
        $row = array(
            'code' => $_code,
            'code_js' => '["' . $_code . '"]'
        );
        if ($code) {
            $_code = array();
            $_code_js = array();
            $codes = explode(',', $code);
            foreach ($codes as $c) {
                if ($c) {
                    $c = strtolower($c);
                    $_code[] = $c;
                    $_code_js[] = '"' . $c . '"';
                }
            }
            if (!empty($_code)) {
                $row['code'] = implode(',', $_code);
                $row['code_js'] = '[' . implode(',', $_code_js) . ']';
            }
        }

        return (object)$row;
    }

    public function init_logs($res = array())
    {
        if (getenv('LOG_ACTIVITY')) {
            $token = $this->input->get('t', TRUE);
            if (!$token) {
                $token = 'no-token-jwt';
            }
            $appId = $this->input->get('a', TRUE);
            if (!$appId) {
                $appId = 'no-appId';
            }
            $lang = $this->input->get('lang', TRUE);
            if (!$lang) {
                $lang = 'no-lang';
            }
            $error = (isset($res['error'])) ? $res['error'] : $this->session->flashdata('error_message');
            $url = (isset($res['url'])) ? $res['url'] : current_url();
            $method = ($_POST) ? 'post' : 'get';
            $this->load->library('user_agent');
            $data = array(
                'Token' => $token,
                'AppId' => $appId,
                'Language' => $lang,
                'IPAddress' => $this->input->ip_address(),
                'Browser' => $this->agent->browser(),
                'Version' => $this->agent->version(),
                'Mobile' => $this->agent->mobile(),
                'Url' => $url,
                'method' => $method,
                'Error' => $error,
                'Created' => date('Y-m-d H:i:s'),
            );

            $path = getenv('LOG_PATH');
            $path = (empty($path)) ? str_replace('system', 'logs', BASEPATH) : $path;
            $file = $path . date('Ymd') . '.csv';
            $csv = '';
            if (!file_exists($file)) {
                $first = true;
                foreach ($data as $k => $v) {
                    if (!$first) {
                        $csv .= ",";
                    }
                    $csv .= $k;
                    if ($first) {
                        $first = false;
                    }
                }
                $csv .= "\n";
            }
            $first = true;
            foreach ($data as $k => $v) {
                if (!$first) {
                    $csv .= ",";
                }
                $csv .= $this->csv_field($v);
                if ($first) {
                    $first = false;
                }
            }
            $csv .= "\n";
            $csv_handler = fopen($file, 'a+');
            fwrite($csv_handler, $csv);
            fclose($csv_handler);
        }
    }

    private function csv_field($string)
    {
        if (strpos($string, ',') !== false || strpos($string, '"') !== false || strpos($string, "\n") !== false) {
            $string = '"' . str_replace('"', '""', $string) . '"';
        }
        return $string;
    }
}
