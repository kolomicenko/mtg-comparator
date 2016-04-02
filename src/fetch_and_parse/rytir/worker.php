<?php

require_once dirname(__FILE__) . '/../async/worker.php';
require_once dirname(__FILE__) . "/../enum.php";
require_once dirname(__FILE__) . "/downloader.php";

class Rytir_Worker extends Worker {

    private $_downloader = null;

    function __construct() {
        $this->_downloader = new Rytir_Downloader();

        parent::__construct();
    }

    protected function get_downloader() {
        return $this->_downloader;
    }

    protected function get_queue_name() {
        return Enum::$RYTIR_QUEUE_NAME;
    }

}