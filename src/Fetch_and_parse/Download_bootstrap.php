<?php

namespace MTG_Comparator\Fetch_and_parse;

use MTG_Comparator\Fetch_and_parse\Enum;

class Download_bootstrap {

    private $_matcher = null;
    private $_downloader = null;

    private $_log_file = null;
    private $_worker_command = null;

    function __construct($matcher, $downloader) {
        $this->_matcher = $matcher;
        $this->_downloader = $downloader;

        $shop_name = $downloader->get_shop_name();

        $this->_log_file = '../../log/nohup_'.$shop_name.'.out';
        $this->_worker_command = 'php '.$shop_name.'/start_worker.php';
    }

    public function run() {
        $this->_terminate_workers();
        $this->_start_workers();

        // clear cards
        $this->_matcher->clear_cards();
        // start downloading
        $this->_downloader->download();

        $this->_send_result_info();
    }

    private function _terminate_workers() {
        // kill all existing workers
        exec('pkill -f "'.$this->_worker_command.'"');
    }

    private function _start_workers() {
        // start workers for processing download jobs
        for ($i = 0; $i < Enum::$WORKER_COUNT; $i++) {
            exec($this->_worker_command.' >> '.$this->_log_file.' 2>&1 &');
        }
    }

    private function _send_result_info() {
        send_monitoring_mail($this->_downloader->get_shop_name().' downloaded', file_get_contents($this->_log_file));
    }

}
