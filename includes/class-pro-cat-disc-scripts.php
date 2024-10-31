<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Scripts Class
 *
 * Handles adding scripts and styles
 * on needed pages
 *
 * @package Product Category Discounts for Woo
 * @since 1.0.0
 */
class PCD_Scripts {

    public function __construct() {
        
    }

    /**
     * Enqueue Scripts
     * 
     * Handles to enqueue script on 
     * needed pages
     * 
     * @package Product Category Discounts for Woo
     * @since 1.0.0
     */
    public function pcd_enqueue_disc_cat_scripts($hook_suffix) {

        // Get global variable
        global $woocommerce;

        // Get current screen
        $screen = get_current_screen();

        // Enqueue taxonomy scripts
        if (($hook_suffix == 'edit-tags.php' || $hook_suffix == 'term.php') && 
        	($screen->taxonomy == 'product_cat')) {
            
            //js directory url
            $js_dir = $woocommerce->plugin_url() . '/assets/js/';

            // Use minified libraries if SCRIPT_DEBUG is turned off
            $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

            // Select2 js
            wp_register_script('select2', $js_dir . 'select2/select2' . $suffix . '.js', array('jquery'), '3.5.2');
            wp_register_script('wc-enhanced-select', $woocommerce->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array('jquery', 'select2'), WC_VERSION);
            wp_enqueue_script('wc-enhanced-select');
            
            // Register script
            wp_enqueue_script('pcd_tax_scripts', PRO_CAT_DISC_INC_URL . '/js/pro-cat-disc-taxonomy-scripts.js', array('jquery'));
        }
    }

    /**
     * Enqueue Styles
     * 
     * Handles to enqueue styles on 
     * needed pages
     * 
     * @package Product Category Discounts for Woo
     * @since 1.0.0
     */
    public function pcd_enqueue_disc_cat_styles($hook_suffix) {

        // Get global variable
        global $woocommerce;

        // Get current screen
        $screen = get_current_screen();

        // Enqueue taxonomy scripts
        if (($hook_suffix == 'edit-tags.php' || $hook_suffix == 'term.php') && 
        	($screen->taxonomy == 'product_cat')) {

            // Register style
            wp_enqueue_style('pcd_tax_styles', PRO_CAT_DISC_INC_URL . '/css/pro-cat-disc-taxonomy-styles.css', array());
        }
    }

    /**
     * Adding Hooks
     *
     * Adding proper hoocks for the scripts.
     *
     * @package Product Category Discounts for Woo
     * @since 1.0.0
     */
    public function add_hooks() {

        // Add scripts for custom taxonomy page and product page
        add_action('admin_enqueue_scripts', array($this, 'pcd_enqueue_disc_cat_scripts'));

        // Add styles for custom taxonomy page and product page
        add_action('admin_enqueue_scripts', array($this, 'pcd_enqueue_disc_cat_styles'));
    }

}
