<?php

require_once '../core.php';

use MTG_Comparator\Fetch_and_parse\Rytir as Rytir;
use MTG_Comparator\Fetch_and_parse\Enum;

// kill all existing workers
exec('pkill -f "php Rytir/start_worker.php"');

// start workers for processing download jobs
for ($i = 0; $i < Enum::$WORKER_COUNT; $i++) {
    exec('php Rytir/start_worker.php >> ../../log/nohup_rytir.out 2>&1 &');
}

// clear cards
(new Rytir\Matcher())->clear_cards();

// start downloading
(new Rytir\Downloader())->download();

// TODO:
// Unknown variant "Beast" of card "Token".
// Unknown variant "non-english" of card "Chasm Skulker".
// Unknown variant "Liliana of the Dark Realms" of card "Emblem".
// Unknown variant "Extended Art)" of card "Swamp (#4".
// Unknown variant "left" of card "B.F.M. Big Furry Monster".
// Unknown variant "non-english" of card "Mana Crypt (white-bordered)".



