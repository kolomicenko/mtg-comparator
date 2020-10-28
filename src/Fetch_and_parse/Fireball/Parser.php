<?php

namespace MTG_Comparator\Fetch_and_parse\Fireball;

use MTG_Comparator\Fetch_and_parse\Enum;

class Parser extends \MTG_Comparator\Fetch_and_parse\Parser {

    private static $_QUALITY_MAP = array(
        "Near Mint" => 'MINT',
        "Lightly Played" => 'LIGHTLY',
        "Moderately Played" => 'HEAVILY',
        "Damaged" => 'DAMAGED',
        "Heavily Played" => 'DAMAGED',
    );

    function __construct($payload) {
        $this->_payload = json_decode($payload, true);

        $this->_matcher = new Matcher();
    }

    private function _process_variant($title, $edition, $variant) {
        $name = explode(' [', $title)[0];

        $quality = $variant['title'];
        $is_foil = substr($quality, -4) === 'Foil';
        if ($is_foil) {
            $quality = substr($quality, 0, strlen($quality) - 5);
        }

        if (!array_key_exists($quality, self::$_QUALITY_MAP)) {
            warning('Quality "' . $quality . '" is not supported');
            $quality = self::$_QUALITY_MAP['Damaged'];
        } else {
            $quality = self::$_QUALITY_MAP[$quality];        	
        }

        if ($variant['quantity'] <= 0) {
            warning('Illegal card quantity for: ' . $card_name);
            return false;
        }

        $edition_id = $this->_matcher->process_edition($edition);

        if ($this->_matcher->create_card($name, $is_foil, $edition_id, $quality, 'English', $variant['price'], $variant['quantity'])) {
            return true;
        }

        warning('Card could not be processed: ' . $title);

        return false;
    }

    public function parse_page() {
        $parsed_cards_count = 0;

        foreach ($this->_payload['products'] as $product) {
            foreach ($product["variants"] as $variant) {
                if ($this->_process_variant($product['title'], $product['setName'], $variant)) {
                	$parsed_cards_count ++;
                }
            }
        }

        return $parsed_cards_count;
    }
}