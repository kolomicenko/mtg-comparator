<?php

namespace MTG_Comparator\Fetch_and_parse\Rytir;

use MTG_Comparator\Fetch_and_parse\Enum;

class Parser extends \MTG_Comparator\Fetch_and_parse\Parser {

    private $_dom = null;

    private static $_QUALITY_MAP = array(
        "lightly played" => 'LIGHTLY',
        "moderately played" => 'HEAVILY',
        "played" => 'HEAVILY',
    );

    function __construct($html) {
        $this->_dom = new \DOMDocument;
        @$this->_dom->loadHTML($html);

        $this->_matcher = new Matcher();
    }

    private function _process_card_name($name) {
        $foil_identifier = 'foil';
        $is_foil = false;
        $language = Enum::$LANGUAGES[0];
        $quality = Enum::$QUALITIES[0];

        if (strpos($name, ' - ') === false) {
            $variant = null;
        } else {
            list ($name, $variant) = explode(' - ', $name);
        }

        if ($variant != null) {
            $variants = explode(' / ', $variant);

            foreach ($variants as $variant) {
                if ($variant == $foil_identifier) {
                    $is_foil = true;
                    continue;
                }

                foreach (self::$_QUALITY_MAP as $possible_quality_key => $possible_quality_value) {
                    if ($variant == $possible_quality_key) {
                        $quality = $possible_quality_value;
                        continue 2;
                    }
                }

                foreach (Enum::$LANGUAGES as $possible_language) {
                    if (strcasecmp($variant, $possible_language) === 0) {
                        $language = $possible_language;
                        continue 2;
                    }
                }

                warning('Unknown variant "' . $variant . '" of card "' . $name . '".');
                return null;
            }
        }

        return array($name, $is_foil, $quality, $language);
    }

    private function _process_row($name, $edition, $pieces, $price) {
        $card_name_parsed = $this->_process_card_name($name);

        if ($card_name_parsed === null) {
            return null;
        }

        list($name, $is_foil, $quality, $language) = $card_name_parsed;

        $edition_id = $this->_matcher->process_edition($edition);

        return $this->_matcher->create_card($name, $is_foil, $edition_id, $quality, $language, $price, $pieces);
    }

    private function _parse_card_link($link) {
        $card_name = str_replace('´', "'", $link->nodeValue);

        $edition_td = $link->parentNode->nextSibling;
        $card_edition = str_replace('´', "'", trim($edition_td->nodeValue));

        $pieces_td = $edition_td->nextSibling;
        $card_pieces = preg_replace('/[^0-9]/', '', $pieces_td->nodeValue);

        if (!$card_pieces) {
            warning('Unknown count: ' . $pieces_td->nodeValue);
            return null;
        }

        $price_td = $pieces_td->nextSibling;
        $card_price = preg_replace('/[^0-9]/', '', $price_td->nodeValue);

        if (!$card_price) {
            warning('Unknown price: ' . $price_td->nodeValue);
            return null;
        }

        return $this->_process_row($card_name, $card_edition, $card_pieces, $card_price);
    }

    public function parse_page() {
        $links = $this->_dom->getElementsByTagName('a');

        $parsed_cards_count = 0;
        foreach ($links as $link) {
            #echo $link->getAttribute('class'); // on prod, all anchors have "menulink" class. Even though the string of the page says otherwise

            if ($link->getAttribute('class') != 'highslide') {
                continue;
            }

            if ($this->_parse_card_link($link)) {
                $parsed_cards_count += 1;
            }
        }

        return $parsed_cards_count;
    }
}
