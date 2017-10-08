<?php

namespace MTG_Comparator\Fetch_and_parse;

use MTG_Comparator\Fetch_and_parse\Rytir as Rytir;

(new Download_bootstrap(new Rytir\Matcher(), new Rytir\Downloader()))->run();