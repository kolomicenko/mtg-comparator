<?php

namespace MTG_Comparator\Fetch_and_parse\Fireball;

use MTG_Comparator\Fetch_and_parse\Async as Async;

class Client extends Async\Client {

    public function __construct() {
        parent::__construct(new Async\AmqpAdapter());
    }

    protected function get_queue_name() {
        return Enum::$QUEUE_NAME;
    }

}