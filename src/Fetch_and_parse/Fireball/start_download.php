<?php

namespace MTG_Comparator\Fetch_and_parse\Fireball;

require_once '../core.php';

use MTG_Comparator\Fetch_and_parse\Download_bootstrap as Download_bootstrap;

(new Download_bootstrap(new Matcher(), new Downloader()))->run();