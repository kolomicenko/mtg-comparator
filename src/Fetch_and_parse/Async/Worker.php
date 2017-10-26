<?php

namespace MTG_Comparator\Fetch_and_parse\Async;

use MTG_Comparator\Fetch_and_parse\Enum;

abstract class Worker {
    private $_channel = null;
    private $_adapter = null;

    function __construct($adapter) {
        $this->_adapter = $adapter;
        $this->_channel = $adapter->connect();

        $this->_channel->queue_declare($this->get_queue_name(), false, false, false, false);
        $this->_channel->queue_declare($this->_get_confirm_queue_name(), false, false, false, false);
    }

    public function process() {
        $callback = function($msg) {
            $downloader = $this->get_downloader();

            // the message contains the page_nr only
            $page_nr = intval($msg->body);
            $url = $downloader->get_url_by_page($page_nr);

            // parse the page and send back the result
            if ($parsed_cards = $downloader->get_and_parse_page($url)) {
                info("Page " . $page_nr . " processed.");
                $this->_confirm_back_to_client(sprintf(Enum::$CARDS_FOUND_MESSAGE, $parsed_cards));
            } else {
                info("No more cards found.");
                $this->_confirm_back_to_client(Enum::$CARDS_NOT_FOUND_MESSAGE);
            }

            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $this->_channel->basic_qos(null, 1, null);
        $this->_channel->basic_consume($this->get_queue_name(), '', false, false, false, false, $callback);

        while (count($this->_channel->callbacks)) {
            // wait until we get a job from the queue
            $this->_channel->wait();
        }

        $this->_adapter->close();
    }

    private function _confirm_back_to_client($text) {
        $msg = $this->_adapter->create_message($text);
        $this->_channel->basic_publish($msg, '', $this->_get_confirm_queue_name());
    }

    private function _get_confirm_queue_name(){
        return $this->get_queue_name() . Enum::$CONFIRM_QUEUE_NAME_SUFFIX;
    }

    // template method
    protected abstract function get_downloader();

    // template method
    protected abstract function get_queue_name();

}