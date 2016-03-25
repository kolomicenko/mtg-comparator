<?php

require_once dirname(__FILE__) . '/../matcher.php';
require_once dirname(__FILE__) . "/../enum.php";

class Fireball_Matcher extends Matcher {
    private $_shop_id = '1';

    protected function get_shop_id() {
        return $this->_shop_id;
    }
    
    protected function get_direction() {
        return Enum::$DIRECTIONS[0]; // sell
    }
}