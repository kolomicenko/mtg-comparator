<?php

namespace MTG_Comparator\Fetch_and_parse;

use MTG_Comparator\Fetch_and_parse\Fireball as Fireball;

(new Download_bootstrap(new Fireball\Matcher(), new Fireball\Downloader()))->run();