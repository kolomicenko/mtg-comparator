<?php

require_once '../core.php';
require_once 'fireball/downloader.php';
require_once 'fireball/matcher.php';

(new Fireball_Matcher())->clear_cards();

(new Fireball_Downloader())->download();