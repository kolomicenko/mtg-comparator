<?php

require_once dirname(__FILE__) . '/../async/client.php';
require_once dirname(__FILE__) . "/../enum.php";

class Fireball_Client extends Client {

    protected function get_queue_name() {
        return Enum::$FIREBALL_QUEUE_NAME;
    }

}