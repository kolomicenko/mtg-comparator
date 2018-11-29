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
        return str_replace(',', '', ltrim($price, '$'));
    }

    private function _process_card($name, $edition, $variant, $price, $pieces) {
        list($quality, $language) = $this->_process_variant($variant);
        list($name, $is_foil) = $this->_process_card_name($name);

        $price = $this->_adjust_price($price);

        $edition_id = $this->_matcher->process_edition($edition);

        return $this->_matcher->create_card($name, $is_foil, $edition_id, $quality, $language, $price, $pieces);
    }

    private function _parse_card($form) {
		static $already_parsed = [];

		$unique_id = $form->getAttribute('data-vid');

		if ($unique_id == '') {
			return false; // the item is out of stock
		}

		if (isset($already_parsed[$unique_id])) {
			return false; // zero parsed cards (skipping this as a wrong multiple entry)
		}

		$already_parsed[$unique_id] = 1;

		$card_name = $form->getAttribute('data-name');
		$card_price = $form->getAttribute('data-price');
		$card_edition = $form->getAttribute('data-category');
		$card_variant = $form->getAttribute('data-variant');
		$card_pieces = 0;

		$inputs = $form->getElementsByTagName('input');
		foreach ($inputs as $input) {
			if ($input->getAttribute('class') == 'qty') {
				 $card_pieces = $input->getAttribute('max');
			}
		}

		if ($card_pieces <= 0) {
			warning('Illegal card quantity for: ' . $card_name);
			return false;
		}

        if ($this->_process_card($card_name, $card_edition, $card_variant, $card_price, $card_pieces)) {
        	return true;
        }

      	warning('Illegal card variant for: ' . $form->ownerDocument->saveXML($form));

        return false;
    }

    public function parse_page() {
        $forms = $this->dom->getElementsByTagName('form');

        $parsed_cards_count = 0;
        foreach ($forms as $form) {
            if ($form->getAttribute('class') != 'add-to-cart-form') {
                continue;
            }

            if ($this->_parse_card($form)) {
            	$parsed_cards_count ++;
            }
        }

        return $parsed_cards_count;
    }
}