<?php

require_once dirname(__FILE__) . '/../async/worker.php';
require_once dirname(__FILE__) . "/../enum.php";
require_once dirname(__FILE__) . "/downloader.php";

class Fireball_Worker extends Worker {

    private $_downloader = null;

    function __construct() {
        $this->_downloader = new Fireball_Downloader();

        parent::__construct();
    }

    protected function get_downloader() {
        return $this->_downloader;
    }

    protected function get_queue_name() {
        return Enum::$FIREBALL_QUEUE_NAME;
    }

}