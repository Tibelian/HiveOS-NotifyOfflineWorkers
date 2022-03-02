<?php

// load configuration
$config = [];
try {
    $config = json_decode(@file_get_contents('include/config.json'), true);
}
catch(Exception $e) {
    echo 'ERROR CONFIG FILE';
    exit;
}

// declare constants
define('TOKEN', $config['token']);
define('API_URL', $config['apiUrl']);
define('FARM_ID', $config['farmId']);
define('MARGIN_TIME_TO_NOTIFY', $config['timeToNotify']);
