<?php

namespace MTG_Comparator\Fetch_and_parse\Fireball;

class Downloader extends \MTG_Comparator\Fetch_and_parse\Downloader {

    protected function download_page($page_nr) {
        $payload = str_replace(
            '__OFFSET__',
            strval(($page_nr - 1) * 1000),
            '{"storeUrl":"channel-fireball-store.myshopify.com","game":"mtg","sortTypes":[{"type":"price","asc":false,"order":1}],"instockOnly":"true","limit":1000,"offset":__OFFSET__}');

        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-Type: application/json',
                'content' => $payload
            )
        );

        $context  = stream_context_create($opts);

        return file_get_contents('https://advanced-search.binderpos.com/advancedSearch', false, $context);
    }

    protected function get_parser($page_content) {
        return new Parser($page_content);
    }

    protected function get_client() {
        return new Client();
    }

    public function get_shop_name() {
        return Enum::$SHOP_NAME;
    }

}