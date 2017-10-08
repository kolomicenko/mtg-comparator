<?php

namespace MTG_Comparator\Fetch_and_parse\Fireball;

class Downloader extends \MTG_Comparator\Fetch_and_parse\Downloader {

    public function get_url_by_page($page_nr) {
        $page_placeholder = '__PAGE__';
        $core_url = 'http://store.channelfireball.com/advanced_search?buylist_mode=0&commit=Search&page=' . $page_placeholder . '&search%5Bbuy_price_gte%5D=&search%5Bbuy_price_lte%5D=&search%5Bcategory_ids_with_descendants%5D%5B%5D=&search%5Bcategory_ids_with_descendants%5D%5B%5D=8&search%5Bdirection%5D=ascend&search%5Bfuzzy_search%5D=&search%5Bin_stock%5D=0&search%5Bsell_price_gte%5D=&search%5Bsell_price_lte%5D=&search%5Bsort%5D=name&search%5Btags_name_eq%5D=&search%5Bvariants_with_identifier%5D%5B14%5D%5B%5D=&search%5Bvariants_with_identifier%5D%5B15%5D%5B%5D=&search%5Bwith_descriptor_values%5D%5B10%5D=&search%5Bwith_descriptor_values%5D%5B11%5D=&search%5Bwith_descriptor_values%5D%5B13%5D=&search%5Bwith_descriptor_values%5D%5B255%5D=&search%5Bwith_descriptor_values%5D%5B290%5D=&search%5Bwith_descriptor_values%5D%5B366%5D=&search%5Bwith_descriptor_values%5D%5B6%5D=&search%5Bwith_descriptor_values%5D%5B7%5D=&search%5Bwith_descriptor_values%5D%5B9%5D=&utf8=%E2%9C%93';

        return str_replace($page_placeholder, strval($page_nr), $core_url);
    }

    protected function get_parser($page_content) {
        return new Parser($page_content);
    }

    protected function get_client() {
        return new Client();
    }

    protected function get_shop_name() {
        return Enum::$SHOP_NAME;
    }

}