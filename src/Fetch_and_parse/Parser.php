<?php

namespace MTG_Comparator\Fetch_and_parse;

abstract class Parser {

    protected $dom = null;

    function __construct($html) {
        $this->dom = new \DOMDocument;
        @$this->dom->loadHTML($html);
    }

    // template method
    public abstract function parse_page();

}