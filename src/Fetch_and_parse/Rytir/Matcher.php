<?php

namespace MTG_Comparator\Fetch_and_parse\Rytir;

use MTG_Comparator\Fetch_and_parse\Enum;

class Matcher extends \MTG_Comparator\Fetch_and_parse\Matcher {
    private $_shop_id = '2';

    protected function get_shop_id() {
        return $this->_shop_id;
    }

    protected function get_direction() {
        return Enum::$DIRECTIONS[1]; // buy
    }
}