<?php

require_once dirname(__FILE__) . '/../matcher.php';
require_once dirname(__FILE__) . "/../enum.php";

class Rytir_Matcher extends Matcher {
    private $_shop_id = '2';

    protected function get_shop_id() {
        return $this->_shop_id;
    }
    
    protected function get_direction() {
        return Enum::$DIRECTIONS[1]; // buy
    }
}