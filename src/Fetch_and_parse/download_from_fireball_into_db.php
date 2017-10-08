<?php

require_once '../core.php';

use MTG_Comparator\Fetch_and_parse\Fireball as Fireball;
use MTG_Comparator\Fetch_and_parse\Enum;

$log_file = '../../log/nohup_fireball.out';
$worker_command = 'php Fireball/start_worker.php';

// kill all existing workers
exec('pkill -f "' . $worker_command . '"');

// start workers for processing download jobs
for ($i = 0; $i < Enum::$WORKER_COUNT; $i++) {
    exec($worker_command . ' >> ' . $log_file . ' 2>&1 &');
}

// clear cards
(new Fireball\Matcher())->clear_cards();

// start downloading
(new Fireball\Downloader())->download();

send_monitoring_mail("Fireball downloaded", file_get_contents($log_file));
