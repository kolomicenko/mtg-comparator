<?php

require_once '../core.php';
require_once 'fireball/downloader.php';
require_once 'fireball/matcher.php';

require_once dirname(__FILE__) . "/enum.php";

// kill all existing workers
exec('pkill -f "php fireball/start_worker.php"');

// start workers for processing download jobs
for ($i = 0; $i < Enum::$WORKER_COUNT; $i++) {
    exec('php fireball/start_worker.php >> ../../log/nohup_fireball.out 2>&1 &');
}

// clear cards
(new Fireball_Matcher())->clear_cards();

// start downloading
(new Fireball_Downloader())->download();
