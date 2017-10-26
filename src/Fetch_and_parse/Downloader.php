<?php

namespace MTG_Comparator\Fetch_and_parse;

abstract class Downloader {

    public function get_page($url) {
        $attempts = 3;

        while ($attempts > 0) {
            $page = file_get_contents($url);

            if (strpos($http_response_header[0], '200') !== false) {
                return $page;
            }

            warning('Failed to load page. ' . var_export($http_response_header, true));

            $attempts -= 1;
        }

        return null;
    }

    public function get_and_parse_page($url) {
        $attempts = 3;

        while ($attempts > 0) {
            $page_content = $this->get_page($url);

            if ($page_content !== null) {
                $parsed_cards_count = $this->get_parser($page_content)->parse_page();

                if ($parsed_cards_count > 0) {
                    return $parsed_cards_count;
                }
            }

            $attempts -= 1;
        }

        warning($url . "\n");
        // warning($page_content . "\n");

        return false;
    }

    public function download() {
        $start_time = time();

        $total_parsed_cards = $this->get_client()->process();
        $total_time = time() - $start_time;

        info(sprintf("Total time in seconds: %d", $total_time));
        info(sprintf("Total parsed cards: %d", $total_parsed_cards));

        return [
            'time'  => $total_time,
            'cards' => $total_parsed_cards
        ];
    }

    public function clear_queues() {
        $this->get_client()->delete_queues();
    }

    // template method
    public abstract function get_url_by_page($page_nr);

    // template method
    protected abstract function get_parser($page_content);

    // template method
    protected abstract function get_client();

    // template method
    public abstract function get_shop_name();
}