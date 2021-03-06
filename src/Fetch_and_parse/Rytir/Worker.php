<?php

namespace MTG_Comparator\Fetch_and_parse\Rytir;

use MTG_Comparator\Fetch_and_parse\Async as Async;

class Worker extends Async\Worker {

    private $_downloader = null;

    function __construct() {
        $this->_downloader = new Downloader();

        parent::__construct(new Async\AmqpAdapter());
    }

    protected function get_downloader() {
        return $this->_downloader;
    }

    protected function get_queue_name() {
        return Enum::$QUEUE_NAME;
    }

}