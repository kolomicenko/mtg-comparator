<?php

require_once __DIR__ . '/../vendor/autoload.php';

function warning($string) {
    echo $string . "\n";
}

function info($string) {
    echo $string . "\n";
}

function send_monitoring_mail($subject, $body) {
    return mail(
        getenv('MTG_MONITORING_RECIPIENT'),
        $subject,
        $body,
        'From: ' . getenv('MTG_MONITORING_SENDER')
    );
}