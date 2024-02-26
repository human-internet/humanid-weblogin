<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['humanid'] = [
    'fe_url' => $_ENV['HUMANID_FE_URL'] ?? '',
    'url' => $_ENV['HUMANID_URL'] ?? '',
    'client_id' => $_ENV['HUMANID_CLIENT_ID'] ?? '',
    'client_secret' => $_ENV['HUMANID_CLIENT_SECRET'] ?? '',
    'server_id' => $_ENV['HUMANID_SERVER_ID'] ?? '',
    'server_secret' => $_ENV['HUMANID_SERVER_SECRET'] ?? '',
    'protocol' => $_ENV['PROTOCOL'] ?? 'http',
];
