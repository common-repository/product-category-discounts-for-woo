<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Model Class
 * 
 * Handles generic plugin functionality.
 * 
 * @package Product Category Discounts for Woo
 * @since 1.0.0
 */
class PCD_Model {

    public function __construct() {
        
    }

    /**
     * Escape Tags & Strip Slashes From Array
     * 
     * @package Product Category Discounts for WooCommerc
     * @since 1.0.0
     */
    public function pcd_escape_slashes_deep($data = array(), $flag = false, $limited = false) {

        if ($flag != true) {
            $data = $this->pcd_nohtml_kses($data);
        } else {
            if ($limited == true) {
                $data = wp_kses_post($data);
            }
        }

        $data = esc_attr(stripslashes_deep($data));

        return $data;
    }

    /**
     * Strip Html Tags
     * 
     * It will sanitize text input (strip html tags, and escape characters)
     * 
     * @package Product Category Discounts for Woo
     * @since 1.0.0
     */
    public function pcd_nohtml_kses($data = array()) {

        if (is_array($data)) {
            $data = array_map(array($this, 'pcd_nohtml_kses'), $data);
        } elseif (is_string($data)) {
            $data = wp_filter_nohtml_kses($data);
        }

        return $data;
    }

    /**
     * Handles to return html for price range 
     * of variable products
     * 
     * @package Product Category Discounts for WooCommerc
     * @since 1.0.0
     */
    public function pcd_format_price_range($product, $min_price, $max_price, $pro_min_disc, $pro_max_disc, $disc_text = '') {

        // Create html for variable product
        $price = sprintf(_x('<del>%1$s &ndash; %2$s</del>', 'Price range: from-to', 'procatdisc'), is_numeric($min_price) ? wc_price($min_price) : $min_price, is_numeric($max_price) ? wc_price($max_price) : $max_price);
        $price .= sprintf(_x('%1$s &ndash; %2$s', 'Price range: from-to', 'procatdisc'), is_numeric($pro_min_disc) ? wc_price($pro_min_disc) : $pro_min_disc, is_numeric($pro_max_disc) ? wc_price($pro_max_disc) : $pro_max_disc) . sprintf('<small>%s</small>', $disc_text);

        // Return html
        return apply_filters('pcd_format_simple_var_price', $price, $product, $min_price, $max_price, $pro_min_disc, $pro_max_disc, $disc_text);
    }

    /**
     * Handles to return discunt types
     * 
     * @package Product Category Discounts for WooCommerc
     * @since 1.0.0
     */
    public function pcd_get_disc_types() {

        // Define discount type array
        $disc_types = array(
            'flat_disc' => __('Flat Discount', 'procatdisc'),
            'perc_disc' => __('Percent Discount', 'procatdisc'),
            'fix_price' => __('Fixed Price', 'procatdisc')
        );

        // Return array
        return $disc_types;
    }

    public function pcd_get_discounts_data($product_id, $variation_id = '', $disc_label = '', $disc_type = '', $disc_amt = '') {

        $data = array();

        // Get prefix
        $prefix = PRO_CAT_DISC_META_PREFIX;

        // Get product
        $product = wc_get_product($product_id);

        // Get discount label
        $data['disc_label'] = !empty($disc_label) ? $disc_label : '';

        // Get discount type
        $data['disc_type'] = !empty($disc_type) ? $disc_type : 'flat_disc';

        // If product type is variable
        if ($product->is_type('variable') && !empty($variation_id)) {

            // Get variation product
            $variation_pro = wc_get_product($variation_id);

            // Get price for variation
            $data['price'] = $price = $variation_pro->get_price();
        } else {

            // Get price for simple product
            $data['price'] = $price = $product->get_price();
        }

        // If discount type is percent discount
        if ($disc_type == 'perc_disc') {

            $disc_perc = $disc_amt;
            // If discount amount is set to more than 100
            if ($disc_perc > 100) {

                $data['disc_price'] = 0; // Make discount price to 0
                $data['disc_amt'] = $price; // And make discounted amount to total price
            } else {

                $data['disc_price'] = $price - round( ($price * $disc_perc / 100), 2); // Calculate price after discount
                $data['disc_amt'] = $price - $data['disc_price']; // Calculate total discount applied
            }
        } elseif ($disc_type == 'flat_disc') { // If discount is flat discount
            if ($disc_amt < $price) { // If discount amount is less than price
                $data['disc_price'] = $price - $disc_amt; // Calculate price after discount
                $data['disc_amt'] = $price - $data['disc_price']; // Calculate total discount applied
            } else {

                $data['disc_price'] = 0; // Make discount price to 0
                $data['disc_amt'] = $price; // And make discounted amount to total price
            }
        } elseif ($disc_type == 'fix_price') { // If discount is fix price
            $data['disc_price'] = $disc_amt; // Set discount applied and price to the discount amount
            if ($disc_amt < $price) { // If discount amount is less than price
                $data['disc_amt'] = $price - $data['disc_price']; // Calculate total discount applied
            } else {

                $data['disc_amt'] = $disc_amt; // And make discounted amount to total price
            }
        }

        return $data;
    }

    /**
     * Handles to get applicable discount from category id and product id
     * 
     * @package Product Category Discounts for WooCommerc
     * @since 1.0.0
     */
    public function pcd_get_applicable_discount($category_id, $product_id, $variation_id = '') {
    	
        $disc_label = $disc_type = $disc_amt = $discounted_price = '';
        $term_meta = get_option("taxonomy_$category_id"); // retrieve the existing value(s) for this meta field. This returns an array

        if(!$term_meta) {
        	return;
        }

        extract($term_meta);
        $taxonomy = get_term_by('id', $category_id, 'product_cat'); // Get taxonomy
        $disc_cat_name = $taxonomy->name;

        // Get product
        $product = wc_get_product($product_id);

        // Get discount label
        $data['disc_label'] = isset($disc_label) && !empty($disc_label) ? $disc_label : $disc_cat_name;

        // Get discount type
        $data['disc_type'] = !empty($disc_type) ? $disc_type : 'flat_disc';

        // If product type is variable
        if ($product->is_type('variable') && !empty($variation_id)) {

            // Get variation product
            $variation_pro = wc_get_product($variation_id);

            // Get price for variation
            $data['price'] = $price = $variation_pro->get_price();
        } else {

            // Get price for simple product
            $data['price'] = $price = $product->get_price();
        }

        // If discount type is percent discount
        if ($disc_type == 'perc_disc') {

            $disc_perc = $disc_amt;
            // If discount amount is set to more than 100
            if ($disc_perc > 100) {

                $discounted_price = $price; // And make discounted amount to total price
            } else {

                $discounted_price = ($price * $disc_perc / 100); // Calculate total discount applied
            }
        } elseif ($disc_type == 'flat_disc') { // If discount is flat discount
            if ($disc_amt < $price) { // If discount amount is less than price
                $discounted_price = $disc_amt; // Calculate total discount applied
            } else {

                $discounted_price = $price; // And make discounted amount to total price
            }
        } elseif ($disc_type == 'fix_price') { // If discount is fix price
            if ($disc_amt < $price) { // If discount amount is less than price
                $discounted_price = $price - $disc_amt; // Calculate total discount applied
            } else {

                $discounted_price = $disc_amt; // And make discounted amount to total price
            }
        }

        return $discounted_price;
    }

    /**
     * Handles to get discount, given product_id and variation_id
     * 
     * @package Product Category Discounts for WooCommerc
     * @since 1.0.0
     */
    public function pcd_get_disc_details_from_productid($product_id, $variation_id = '') {

        // Get prefix
        $prefix = PRO_CAT_DISC_META_PREFIX;

        // Get product
        $product = wc_get_product($product_id);

        // Initialize variable
        $data = $possible_cat_disc_arr = array();

        $terms_arr = wp_get_post_terms($product_id, 'product_cat');
        if (!empty($terms_arr)) {
            foreach ($terms_arr as $term) {
                $t_id = $term->term_id; // put the term ID into a variable
                $term_meta = get_option("taxonomy_$t_id"); // retrieve the existing value(s) for this meta field. This returns an array
                $disc_amt = $this->pcd_get_applicable_discount($t_id, $product_id, $variation_id);
                if( !is_null($disc_amt) ) {
                    $possible_cat_disc_arr[$t_id] = $disc_amt;
                }
            }

            if (!empty($possible_cat_disc_arr)) {

                asort($possible_cat_disc_arr);
                reset($possible_cat_disc_arr); // Move the internal pointer to the start of the array
                $category_id = key($possible_cat_disc_arr); // Get first key (minimum discount) to get the category id
                $term_meta = get_option("taxonomy_$category_id"); // retrieve the existing value(s) for this meta field. This returns an array
                extract($term_meta);
                $data = $this->pcd_get_discounts_data($product_id, $variation_id, $disc_label, $disc_type, $disc_amt);
            }
        }

        // Return data
        return apply_filters('pcd_disc_data_from_productid', $data, $product_id, $variation_id);
    }
}

?>