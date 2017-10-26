<?php

namespace MTG_Comparator\Fetch_and_parse\Async;

interface Adapter {
    public function connect();

    public function close();

    public function create_message($text);
}