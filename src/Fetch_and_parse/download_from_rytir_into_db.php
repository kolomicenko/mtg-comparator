<?php

require_once '../core.php';

use MTG_Comparator\Fetch_and_parse\Rytir as Rytir;
use MTG_Comparator\Fetch_and_parse\Enum;

$log_file = '../../log/nohup_fireball.out';
$worker_command = 'php Rytir/start_worker.php';

// kill all existing workers
exec('pkill -f "' . $worker_command . '"');

// start workers for processing download jobs
for ($i = 0; $i < Enum::$WORKER_COUNT; $i++) {
    exec($worker_command . ' >> ' . $log_file . ' 2>&1 &');
}

// clear cards
(new Rytir\Matcher())->clear_cards();

// start downloading
(new Rytir\Downloader())->download();

send_monitoring_mail("Rytir downloaded", file_get_contents($log_file));

// TODO:
// Unknown variant "Beast" of card "Token".
// Unknown variant "non-english" of card "Chasm Skulker".
// Unknown variant "Liliana of the Dark Realms" of card "Emblem".
// Unknown variant "Extended Art)" of card "Swamp (#4".
// Unknown variant "left" of card "B.F.M. Big Furry Monster".
// Unknown variant "non-english" of card "Mana Crypt (white-bordered)".



