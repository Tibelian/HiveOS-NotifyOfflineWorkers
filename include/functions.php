<?php

use PHPMailer\PHPMailer\PHPMailer;

/**
 * execute curl - template from hiveos
 */
function doCurl($ch)
{
    $res = curl_exec($ch);
    if ($res === false) {
        die('CURL error: '.curl_error($ch));
    }
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    $res = json_decode($res, true);
    if ($code < 200 || $code >= 300) {
        throw new Exception($res['message'] ?? 'Response error', $code);
    }
    return $res;
}

/**
 * insert new error log
 */
function addError($err)
{
    $errors = [];
    try {
        $errors = json_decode(@file_get_contents('errors.json'), true);
    } catch(Exception $e) {}
    $errors[] = $err;
    file_put_contents('errors.json', json_encode($errors));
}

/**
 * check on if already notified in the last margin time
 * if not the just send the mail
 */
function notifyOfflineWorker($worker)
{
    $logs = [];
    try {
        $logs = json_decode(@file_get_contents('logs.json'), true);
    } catch(Exception $e) {}

    $alreadyNotified = false;
    if (is_array($logs) && sizeof($logs) > 0)
    {
        foreach ($logs as $log)
        {
            if ($log['worker'] != $worker['id']) 
                continue
            ;
            $lastDate = $log['datetime'] + strtotime(MARGIN_TIME_TO_NOTIFY);
            $alreadyNotified = ($lastDate > time());
            break;
        }
    }

    if ($alreadyNotified === false)
    {
        if (sendMail($worker)) {
            $logs[] = [
                'datetime' => time(),
                'worker' => $worker['id']
            ];
            file_put_contents('logs.json', json_encode($logs));
            return true;
        } else {
            echo '
                <p>couldn\'t notify because an error have occurred</p>
            ';
        }
    } else {
        echo '
            <p>
                Couldn\'t send the mail because already notified. <br/>
                The margin time to notify again is "'. MARGIN_TIME_TO_NOTIFY .'"
            </p>
        ';
    }
    return false;
}

/**
 * phpmailer send a message
 */
function sendMail($worker)
{
    // load outgoing mail configuration
    $mailer = [];
    try {
        $mailer = json_decode(@file_get_contents('include/mailer.json'), true);
    } catch(Exception $e) {};

    // init mailer
    $mail = new PHPMailer();

    // server settings
    $mail->SMTPDebug = 1;
    $mail->isSMTP();
    $mail->Host       = $mailer['host'];
    $mail->SMTPAuth   = $mailer['auth'];
    $mail->Username   = $mailer['username'];
    $mail->Password   = $mailer['password'];
    $mail->SMTPSecure = $mailer['encryption'];
    $mail->Port       = $mailer['port'];

    // recipients
    $mail->setFrom($mailer['username'], $mailer['name']);
    foreach ($mailer['recipients'] as $email) {
        $mail->addAddress($email);
    }

    // content
    $mail->isHTML(true);
    $mail->Subject = "Worker {$worker['name']} is OFFLINE";
    $mail->Body    = '
        <h1>Worker is <span style="color:red;">offline<span></h1>
        <p>
            Please check if the worker '. $worker['name'] .' 
            is <strong>turned on</strong> and the <strong>internet connection</strong>.
        </p>
        <p>Current worker configuration:</p>
        <code>' . json_encode($worker) . '</code>
    ';

    // send message and return if worked
    return !($mail->send() === false);
}
