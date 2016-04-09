<?php

require_once '../core.php';

use MTG_Comparator\Fetch_and_parse\Fireball as Fireball;
use MTG_Comparator\Fetch_and_parse\Enum;

require_once dirname(__FILE__) . "/enum.php";

// kill all existing workers
exec('pkill -f "php Fireball/start_worker.php"');

// start workers for processing download jobs
for ($i = 0; $i < Enum::$WORKER_COUNT; $i++) {
    exec('php Fireball/start_worker.php >> ../../log/nohup_fireball.out 2>&1 &');
}

// clear cards
(new Fireball\Matcher())->clear_cards();

// start downloading
(new Fireball\Downloader())->download();
