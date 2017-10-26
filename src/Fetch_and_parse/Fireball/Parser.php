<?php

namespace MTG_Comparator\Fetch_and_parse\Fireball;

use MTG_Comparator\Fetch_and_parse\Enum;

class Parser extends \MTG_Comparator\Fetch_and_parse\Parser {

    private static $_QUALITY_MAP = array(
        "NM-Mint" => 'MINT',
        "Slightly Played" => 'LIGHTLY',
        "Moderately Played" => 'HEAVILY',
        "Damaged" => 'DAMAGED'
    );

    function __construct($html) {
        parent::__construct($html);

        $this->_matcher = new Matcher();
    }

    private function _process_card_name($name) {
        $foil_identifier = ' - Foil';
        $is_foil = false;

        if (strpos($name, $foil_identifier) !== false) {
            $is_foil = true;
            $name = str_replace($foil_identifier, '', $name);
        }

        return array($name, $is_foil);
    }

    private function _process_variant($variant) {
        list ($quality, $language) = explode(', ', $variant);

        $quality = self::$_QUALITY_MAP[$quality];

        if (!in_array($quality, Enum::$QUALITIES)) {
            warning('Variant "' . $variant . '" is not supported');
            $quality = Enum::$QUALITIES[0];
        }

        if (!in_array($language, Enum::$LANGUAGES)) {
            warning('Variant "' . $variant . '" is not supported');
            $language = Enum::$LANGUAGES[0];
        }

        return array($quality, $language);
    }

    private function _adjust_price($price) {
        if ($price[0] == '.') {
            $price = '0' . $price;
        }

        return $price;
    }

    private function _process_row($name, $edition, $variant, $price, $pieces) {
        list ($quality, $language) = $this->_process_variant($variant);
        list($name, $is_foil) = $this->_process_card_name($name);

        $price = $this->_adjust_price($price);

        $edition_id = $this->_matcher->process_edition($edition);

        return $this->_matcher->create_card($name, $is_foil, $edition_id, $quality, $language, $price, $pieces);
    }

    private function _parse_card_variant($variant, $card_name) {
        if ($variant->getAttribute('class') != 'product-info-row variantRow data-setter') {
            return null;
        }

        $span_nodelist = $variant->getElementsByTagName('span');
        $spans = array();
        foreach ($span_nodelist as $span) {
            $spans[] = $span;
        }

        if (count($spans) < 3) {
            warning('Could not parse variant of card with name "' . $card_name . '"');
            return null;
        }

        $variant_info = trim($spans[0]->nodeValue);

        $variant_price = null;
        $price_elements = $spans[1]->getElementsByTagName('strong');
        foreach ($price_elements as $price_element) {
            // <strong> with class "msrp" indicates older (and higher) price
            if ($price_element->getAttribute('class') != 'msrp') {
                $variant_price = preg_replace("/([^0-9\\.])/i", "", $price_element->nodeValue);
            }
        }

        if ($variant_price === null) {
            warning('Could not parse price of card with name "' . $card_name . '"');
            return null;
        }

        $variant_stock = preg_replace('/[^0-9]/', '', $spans[2]->nodeValue);

        return array($variant_info, $variant_price, $variant_stock);

    }

    private function _parse_card($article) {
        $info_container = $article->firstChild->nextSibling->nextSibling;
        $info_container_h2 = $info_container->firstChild->nextSibling->firstChild->nextSibling;

        $card_name = $info_container_h2->firstChild->nodeValue;
        $card_edition = $info_container_h2->firstChild->nextSibling->nextSibling->nodeValue;

        $card_variants = $info_container->getElementsByTagName('div');

        $total_processed_count = 0;

        foreach ($card_variants as $variant) {
            $row = $this->_parse_card_variant($variant, $card_name);

            if ($row !== null) {
                if ($this->_process_row($card_name, $card_edition, $row[0], $row[1], $row[2]) === true) {
                    $total_processed_count += 1;
                }
            }
        }

        return $total_processed_count;
    }

    public function parse_page() {
        $articles = $this->dom->getElementsByTagName('article');

        $parsed_cards_count = 0;
        foreach ($articles as $article) {
            if ($article->getAttribute('class') != 'product_row   clearfix') {
                continue;
            }

            $parsed_cards_count += $this->_parse_card($article);
        }

        return $parsed_cards_count;
    }
}