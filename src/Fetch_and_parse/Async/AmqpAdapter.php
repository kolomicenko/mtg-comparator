<?php

namespace MTG_Comparator\Fetch_and_parse\Async;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpAdapter {
    private $_connection = null;
    private $_channel = null;

    public function connect() {
        $host = getenv('MTG_RABBITMQ_HOST');
        $user = getenv('MTG_RABBITMQ_USER');
        $pass = getenv('MTG_RABBITMQ_PASS');

        $this->_connection = new AMQPStreamConnection($host, 5672, $user, $pass);
        $this->_channel = $this->_connection->channel();

        return $this->_channel;
    }

    public function close() {
        $this->_channel->close();
        $this->_connection->close();
    }

    public function create_message($text) {
        return new AMQPMessage($text);
    }
}