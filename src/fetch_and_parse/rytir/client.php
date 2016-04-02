<?php

require_once dirname(__FILE__) . '/../async/client.php';
require_once dirname(__FILE__) . "/../enum.php";

class Rytir_Client extends Client {

    protected function get_queue_name() {
        return Enum::$RYTIR_QUEUE_NAME;
    }

}