<?php

namespace MTG_Comparator\Fetch_and_parse\Rytir;

use MTG_Comparator\Fetch_and_parse\Enum;
use MTG_Comparator\Fetch_and_parse\Async as Async;

class Client extends Async\Client {

    protected function get_queue_name() {
        return Enum::$RYTIR_QUEUE_NAME;
    }

}