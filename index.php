<?php

/**
 * @author Tibelian
 * @see https://github.com/Tibelian/HiveOS-NotifyOfflineWorkers
 */

require 'vendor/autoload.php';
require 'include/bootstrap.php';
require 'include/functions.php';

// create CURL
$checkWorkerUrl = API_URL .'/farms/'. FARM_ID .'/workers';
$ch = curl_init($checkWorkerUrl);
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . TOKEN
    ],
    CURLOPT_RETURNTRANSFER => true
]);


try {
    // execute CURL
    $res = doCurl($ch);

    // iterate each worker
    $notified = 0;
    foreach($res['data'] as $worker)
    {
        // check if is online
        if ($worker['stats']['online'] == false) {
            if (notifyOfflineWorker($worker)) {
                $notified++;
            }
        }
        echo $worker['name'] . ' is ' . ($worker['stats']['online'] ? 'online' : 'offline');
    }

    // result
    echo "<br/> Offline workers notified: $notified";
}

catch(Exception $e) {

    // in case if error occurs
    // then add an error log
    addError([
        'datetime' => date('d/m/Y H:i'),
        'code' => $e->getCode(),
        'message' => $e->getMessage()
    ]);

}
