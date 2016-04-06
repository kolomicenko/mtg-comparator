<?php

require_once dirname(__FILE__) . "/../enum.php";

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

abstract class Client {
    private $_connection = null;
    private $_channel = null;
    private $_active_jobs = 0;
    private $_create_new_jobs = true;

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
        $page_nr = 1;

        $callback = function($msg) {
            switch ($msg->body) {
                case Enum::$CARDS_FOUND_MESSAGE:
                    break;
                case Enum::$CARDS_NOT_FOUND_MESSAGE:
                    // no more cards were found, so do not create any more jobs
                    $this->_create_new_jobs = false;
                    break;
                default:
                    warning('Unknown message was sent!');
            }

            // one job has just been finished
            $this->_active_jobs -= 1;

            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $this->_channel->basic_qos(null, 1, null);
        $this->_channel->basic_consume($this->_get_confirm_queue_name(), '', false, false, false, false, $callback);

        while (count($this->_channel->callbacks)) {
            // check if we still should create new jobs
            if ($this->_create_new_jobs) {
                // fill up to the limit of currently running jobs
                $this->_fill_up_available_jobs($page_nr);
            }

            if ($this->_active_jobs <= 0) {
                // there are no currently active jobs => we have finished
                break;
            }

            // wait for a confirm from a worker that a job has been finished
            $this->_channel->wait();
        }

        $this->_channel->close();
        $this->_connection->close();
    }

    // create jobs up to the limit and send them to the queue
    // also increase page_nr counter by the newly created job count
    private function _fill_up_available_jobs(&$page_nr) {
        while ($this->_active_jobs < Enum::$WORKER_COUNT) {
            $this->_send_job_to_queue(strval($page_nr));
            $page_nr += 1;
            $this->_active_jobs += 1;
        }
    }

    private function _send_job_to_queue($text) {
        $msg = new AMQPMessage($text);
        $this->_channel->basic_publish($msg, '', $this->get_queue_name());
    }

    private function _get_confirm_queue_name(){
        return $this->get_queue_name() . Enum::$CONFIRM_QUEUE_NAME_SUFFIX;
    }

    // template method
    protected abstract function get_queue_name();

}