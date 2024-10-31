<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Admin Class
 *
 * Manage Admin Panel Class
 *
 * @package Product Category Discounts for Woo
 * @since 1.0.0
 */
class PCD_Public {

    public $model, $scripts, $default_message;

    // Class constructor
    function __construct() {

        global $pcd_model;

        $this->model = $pcd_model;
    }

    /**
     * Get discounted price from product
     *
     * @package Product Category Discounts for Woo
     * @since 1.0.0
     */
    public function pcd_price_html($price, $product) {
        $prefix = PRO_CAT_DISC_META_PREFIX; // Get prefix

        if ($product->is_type('simple') || ( $product->is_type( 'tour' ))) {

            $product_id = $product->get_id();
            $disc_data = $this->model->pcd_get_disc_details_from_productid($product_id); // Get discount data

            if (!empty($disc_data)) {

                $price = wc_get_price_to_display($product, array('price' => $disc_data['price']));
                $new_price = wc_get_price_to_display($product, array('price' => $disc_data['disc_price']));
                $disc_label = !empty( $disc_data['disc_label'] ) ? __(' ( ', 'procatdisc') . $disc_data['disc_label'] . __(' )', 'procatdisc') : '';

                $price = wc_format_sale_price($price, $new_price) . esc_html($disc_label);
            }
        } elseif ($product->is_type('variable')) {

            $product_id = $product->get_id();
            $prices = $product->get_variation_prices(true);

            if (empty($prices['price'])) {

                $price = esc_html( apply_filters('woocommerce_variable_empty_price_html', '', $product) );
            } else {

                $min_price = $max_price = $min_reg_price = $max_reg_price = '';
                $min_price = current($prices['price']);
                $min_price_key = key($prices['price']);
                $max_price = end($prices['price']);
                $max_price_key = key($prices['price']);

                $min_disc_data = $this->model->pcd_get_disc_details_from_productid($product_id, $min_price_key); // Get minimum discount data
                $max_disc_data = $this->model->pcd_get_disc_details_from_productid($product_id, $max_price_key); // Get maximum discount data

                if (!empty($min_disc_data) && !empty($max_disc_data)) {

                    $pro_min_disc = $min_disc_data['disc_price'];
                    $pro_max_disc = $max_disc_data['disc_price'];
                    $pro_max_reg_price = $max_disc_data['price'];
                    $disc_text = !empty( $min_disc_data['disc_label'] ) ? __(' ( ', 'procatdisc') . $min_disc_data['disc_label'] . __(' )', 'procatdisc') : '';

                    if ($min_price !== $max_price) {
                        $price = $this->model->pcd_format_price_range($product, $min_price, $max_price, $pro_min_disc, $pro_max_disc, esc_html($disc_text)) . $product->get_price_suffix();
                    } elseif ($product->is_on_sale() && $min_reg_price === $max_reg_price) {
                        $price = wc_format_sale_price(wc_price($pro_max_reg_price), wc_price($pro_min_disc)) . $product->get_price_suffix();
                    } else {
                        $price = wc_price($pro_min_disc) . $product->get_price_suffix();
                    }
                }
            }
        }

        return $price;
    }

    /**
     * Get to cart in item data to display in cart page
     *
     * @package Product Category Discounts for Woo
     * @since 1.0.0
     */
    public function pcd_woocommerce_get_item_data($data, $item) {
        $disc_data = $this->model->pcd_get_disc_details_from_productid($item['product_id'], $item['variation_id']); // Get discount data
        if (!empty($disc_data['disc_amt']) && !empty($disc_data['disc_label'])) {

            $data[] = array(
                'name' => $disc_data['disc_label'],
                'display' => wc_price($disc_data['disc_amt']),
                'hidden' => false,
                'value' => ''
            );
        }

        return $data;
    }

    /**
     * Handles to change cart product subtotal
     *
     * @package Product Category Discounts for Woo
     * @since 1.0.0
     */
    public function pcd_filter_cart_product_subtotal($subtotal, $_product, $quantity) {

        // Declare variables
        $product_id = $variation_id = '';

        // If product type is variation or variable
        if ($_product->is_type('variable') || $_product->is_type('variation')) {

            $variation_id = $_product->get_id(); // Get variation id
            $product_id = $_product->get_parent_id(); // Get product id
        } else if ($_product->is_type('simple')) { // If product type is simple
            $product_id = $_product->get_id(); // Get product id
        }

        $disc_data = $this->model->pcd_get_disc_details_from_productid($product_id, $variation_id); // Get discount data
        // If discount data is not empty than return discounted price
        if (!empty($disc_data)) {

            return wc_price($disc_data['disc_price']);
        } else { // Else return subtotal
            return $subtotal;
        }
    }

    /**
     * Handles to calculate cart total
     *
     * @package Product Category Discounts for Woo
     * @since 1.0.0
     */
    public function pcd_action_before_calculate($cart) {
        $cart_contents = $cart->cart_contents; // Get cart contents
        // If cart contents are nit empty
        if (!empty($cart_contents)) {

            foreach ($cart->cart_contents as $cart_item_key => $values) {

                $disc_data = $this->model->pcd_get_disc_details_from_productid($values['product_id'], $values['variation_id']); // Get discount data

                if (!empty($disc_data)) { // If not empty discount data than set price
                    $values['data']->set_price($disc_data['disc_price']);
                }
            }
        }
    }

    /**
     * Handles to add discount category name and amount
     * to order line item meta
     *
     * @package Product Category Discounts for Woo
     * @since 1.0.0
     */
    public function pcd_add_order_item_meta($item, $cart_item_key, $values, $order) {

        $prefix 		= PRO_CAT_DISC_META_PREFIX; // Get prefix
        $variation_id 	= $values['variation_id']; // Get variation id
        $product_id 	= $values['product_id']; // Get product id
        $disc_data 		= $this->model->pcd_get_disc_details_from_productid($product_id, $variation_id); // Get applied discount data

        // If discount data is not empty
        if (!empty($disc_data)) {

            // Add serialized discount meta data
            $item->add_meta_data($prefix . 'disc_cat_details', array(
                'label' => $disc_data['disc_label'],
                'value' => wc_price($disc_data['disc_amt'])
                    ), true);

            // Add meta to show on order page
            $item->add_meta_data($disc_data['disc_label'], wc_price($disc_data['disc_amt']), true);
        }
    }

    /**
     * Handles to show discounted variation price
     * on single product page
     *
     * @package Product Category Discounts for Woo
     * @since 1.0.0
     */
    public function pcd_available_variation($variation_arr, $product, $variation) {
        $prefix = PRO_CAT_DISC_META_PREFIX; // Get prefix
        $product_id = $product->get_id();
        $variation_id = $variation->get_id();

        $disc_data = $this->model->pcd_get_disc_details_from_productid($product_id, $variation_id); // Get applied discount

        if (!empty($disc_data)) {

            $price = wc_get_price_to_display($product, array('price' => $disc_data['price']));
            $new_price = wc_get_price_to_display($product, array('price' => $disc_data['disc_price']));
            $disc_label = !empty( $disc_data['disc_label'] ) ? __(' ( ', 'procatdisc') . $disc_data['disc_label'] . __(' )', 'procatdisc') : '';
            $variation_arr['price_html'] = '<span class="price">' . wc_format_sale_price($price, $new_price) . '</span>' . esc_html($disc_label);
        }

        return $variation_arr;
    }

    /**
     * Handles to show sale bubble for if
     * discount category is applicable
     *
     * @package Product Category Discounts for Woo
     * @since 1.0.0
     */
    public function pcd_product_is_on_sale($on_sale, $product) {

        if (!empty($product)) { // If product is not empty
            $product_id = $product->get_id(); // Get product id
            $disc_data = $this->model->pcd_get_disc_details_from_productid($product_id); // Get applied discount
            // If discount data is not empty
            if (!empty($disc_data)) {

                return true; // Return true
            }
        }

        // Else return default
        return $on_sale;
    }

    /**
     * Handles to store discount method while order is getting placed
     *
     * @package Product Category Discounts for Woo
     * @since 1.0.0
     */
    public function pcd_add_order_meta($order_id, $data) {
    	$prefix = PRO_CAT_DISC_META_PREFIX; // Get prefix
        $pcd_order_discount_method = get_post_meta($order_id, $prefix . 'discount_method', true);

        if (empty($pcd_order_discount_method))
            update_post_meta($order_id, $prefix . 'discount_method', 'before_purchase');
    }

    /**
     * Adding Hooks
     *
     * @package Product Category Discounts for Woo
     * @since 1.0.0
     */
    function add_hooks() {

        // Add action to change woocommerce price on single product page
        add_filter('woocommerce_get_price_html', array($this, 'pcd_price_html'), 100, 2);

        // Get to cart in item data to display in cart page
        add_filter('woocommerce_get_item_data', array($this, 'pcd_woocommerce_get_item_data'), 10, 2);

        // Add filter to calculate cart totals
        add_action('woocommerce_before_calculate_totals', array($this, 'pcd_action_before_calculate'), 10, 1);

        // Add action to add cart item to the order.
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'pcd_add_order_item_meta'), 10, 4);

        // Add action to display variable price on single product page
        add_filter('woocommerce_available_variation', array($this, 'pcd_available_variation'), 10, 3);

        // Add filter to show sale bubble
        add_filter('woocommerce_product_is_on_sale', array($this, 'pcd_product_is_on_sale'), 10, 2);

        // Add action when payment completed
        add_action('woocommerce_order_status_completed_notification', array($this, 'pcd_send_user_notification'), 100);
        add_action('woocommerce_order_status_pending_to_processing_notification', array($this, 'pcd_send_user_notification'), 100);

        // Add action to store discount method when order gets created
        add_action('woocommerce_checkout_update_order_meta', array($this, 'pcd_add_order_meta'), 10, 2);
    }

}
