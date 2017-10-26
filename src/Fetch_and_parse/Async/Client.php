<?php

namespace MTG_Comparator\Fetch_and_parse\Async;

use MTG_Comparator\Fetch_and_parse\Enum;

abstract class Client {
    private $_adapter = null;
    private $_channel = null;
    private $_active_jobs = 0;
    private $_create_new_jobs = true;

    function __construct(Adapter $adapter) {
        $this->_adapter = $adapter;
        $this->_channel = $adapter->connect();

        $this->_channel->queue_declare($this->get_queue_name(), false, false, false, false);
        $this->_channel->queue_declare($this->_get_confirm_queue_name(), false, false, false, false);
    }

    public function process() {
        $page_nr = 1;
        $total_parsed_cards = 0;

        $callback = function($msg) use (&$total_parsed_cards) {
            if ($msg->body === Enum::$CARDS_NOT_FOUND_MESSAGE) {
                // no more cards were found, so do not create any more jobs
                $this->_create_new_jobs = false;
            } else {
                list($parsed_cards) = sscanf($msg->body, Enum::$CARDS_FOUND_MESSAGE);
                if (!$parsed_cards > 0) {
                    warning('Unknown message was sent!');
                } else {
                    $total_parsed_cards += $parsed_cards;
                }
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

        $this->_adapter->close();

        return $total_parsed_cards;
    }

    public function delete_queues() {
        $this->_channel->queue_delete($this->get_queue_name());
        $this->_channel->queue_delete($this->_get_confirm_queue_name());

        $this->_adapter->close();
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
        $msg = $this->_adapter->create_message($text);
        $this->_channel->basic_publish($msg, '', $this->get_queue_name());
    }

    private function _get_confirm_queue_name(){
        return $this->get_queue_name() . Enum::$CONFIRM_QUEUE_NAME_SUFFIX;
    }

    // template method
    protected abstract function get_queue_name();

}