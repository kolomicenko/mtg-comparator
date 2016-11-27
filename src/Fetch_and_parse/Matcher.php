<?php

namespace MTG_Comparator\Fetch_and_parse;

use MTG_Comparator\DB;

abstract class Matcher {

    public function create_card($name, $is_foil, $edition_id, $quality, $language, $price, $pieces) {
        if ($edition_id == null) {
            warning('Could not process row "' . $name . '" because of edition with null id"');
            return null;
        }

        try {
            $result = DB::query('INSERT INTO card(shop_id, name, is_foil, edition_id, quality, language, price, pieces, direction)
                VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)',
                $this->get_shop_id(), $name, $is_foil ? 1 : 0, $edition_id, $quality, $language, $price, $pieces, $this->get_direction()
            );
        } catch (PDOException $e) {
            warning('Could not create new card with name "' . $name . '", error: ' . $e->getMessage());
            return null;
        }

        // do not return anything (it is not needed now)
    }

    public function create_pairs_with_edition($edition_name, $edition_id) {
        $result = DB::query('SELECT id FROM edition WHERE name = ? AND shop_id != ?', $edition_name, $this->get_shop_id());

        while ($row = $result->fetch()) {
            // process the same editions in other shops
            try {
                DB::query('INSERT INTO editions_pair(edition1, edition2) VALUES(?, ?), (?, ?)',
                    $edition_id, $row['id'], $row['id'], $edition_id);
            } catch (PDOException $e) {
                warning('Could not create pairs with edition name: "' . $edition_name . '", error: ' . $e->getMessage());
            }
        }
    }

    public function create_edition($edition) {
        try {
            $result = DB::query('INSERT INTO edition(shop_id, name) VALUES(?, ?)', $this->get_shop_id(), $edition);
        } catch (PDOException $e) {
            warning('Could not create new edition with name "' . $edition . '", error: ' . $e->getMessage());
            return null;
        }

        $edition_id = DB::lastInsertId();
        $this-> create_pairs_with_edition($edition, $edition_id);

        return $edition_id;
    }

    public function process_edition($edition) {
        $edition_id = null;

        $result = DB::query('SELECT id FROM edition WHERE name = ? AND shop_id = ?', $edition, $this->get_shop_id())->fetch();

        if (!$result) {
            $edition_id = $this->create_edition($edition);
        } else {
            $edition_id = $result['id'];
        }

        return $edition_id;
    }

    public function clear_cards() {
        $result = DB::query('DELETE FROM card WHERE shop_id = ?', $this->get_shop_id());

        info('Deleted all ' . $result->rowCount() . ' cards in shop ' . $this->get_shop_id());
    }

    // template method
    protected abstract function get_shop_id();

    // template method
    protected abstract function get_direction();
}