<?php

namespace MTG_Comparator\Fetch_and_parse\Rytir;

use MTG_Comparator\Fetch_and_parse\Download_bootstrap as Download_bootstrap;

(new Download_bootstrap(new Matcher(), new Downloader()))->run();