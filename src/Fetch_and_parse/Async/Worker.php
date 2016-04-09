<?php

namespace MTG_Comparator\Fetch_and_parse\Async;

use MTG_Comparator\Fetch_and_parse\Enum;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

abstract class Worker {
    private $_channel = null;
    private $_connection = null;

    function __construct() {
        $host = getenv('MTG_RABBITMQ_HOST');
        $user = getenv('MTG_RABBITMQ_USER');
        $pass = getenv('MTG_RABBITMQ_PASS');

        $this->_connection = new AMQPStreamConnection($host, 5672, $user, $pass);
        $this->_channel = $this->_connection->channel();

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
            if ($downloader->get_and_parse_page($url)) {
                info("Page " . $page_nr . " processed.");
                $this->_confirm_back_to_client(Enum::$CARDS_FOUND_MESSAGE);
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

        $this->_channel->close();
        $this->_connection->close();
    }

    private function _confirm_back_to_client($text) {
        $msg = new AMQPMessage($text);
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