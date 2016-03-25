<?php

require_once '../core.php';
require_once 'rytir/downloader.php';
require_once 'rytir/matcher.php';

$matcher = new Rytir_Matcher();
$matcher->clear_cards();

$downloader = new Rytir_Downloader();
$downloader->download();

// TODO:
// Unknown variant "Beast" of card "Token".
// Unknown variant "non-english" of card "Chasm Skulker".
// Unknown variant "Liliana of the Dark Realms" of card "Emblem".
// Unknown variant "Extended Art)" of card "Swamp (#4".
// Unknown variant "left" of card "B.F.M. Big Furry Monster".
// Unknown variant "non-english" of card "Mana Crypt (white-bordered)".



