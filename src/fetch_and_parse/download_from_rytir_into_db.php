<?php

require_once '../core.php';
require_once 'rytir/downloader.php';
require_once 'rytir/matcher.php';

require_once dirname(__FILE__) . "/enum.php";

// kill all existing workers
exec('pkill -f "php rytir/start_worker.php"');

// start workers for processing download jobs
for ($i = 0; $i < Enum::$WORKER_COUNT; $i++) {
    exec('php rytir/start_worker.php >> nohup_rytir.out 2>&1 &');
}

// clear cards
(new Rytir_Matcher())->clear_cards();

// start downloading
(new Rytir_Downloader())->download();

// TODO:
// Unknown variant "Beast" of card "Token".
// Unknown variant "non-english" of card "Chasm Skulker".
// Unknown variant "Liliana of the Dark Realms" of card "Emblem".
// Unknown variant "Extended Art)" of card "Swamp (#4".
// Unknown variant "left" of card "B.F.M. Big Furry Monster".
// Unknown variant "non-english" of card "Mana Crypt (white-bordered)".



