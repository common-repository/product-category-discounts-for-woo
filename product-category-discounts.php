<?php

/**
 * Plugin Name: Product Category Discounts for Woo
 * Description: Product Category Discounts allows you to manage discounts for your WooCommerce store in an intelligent yet simple ways.
 * Version: 1.0.2
 * Author: WildProgrammers
 * Author URI: http://wildprogrammers.com/
 * Text Domain: woocatdisc
 * Domain Path: languages
 *
 * WC tested up to: 6.3
 * 
 * @package Product Category Discounts for Woo
 * @category Core
 * @author WildProgrammers
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Basic plugin definitions
 * 
 * @package Product Category Discounts for Woo
 * @since 1.0.0
 */
if (!defined('PRO_CAT_DISC_PLUGIN_VERSION')) {
    define('PRO_CAT_DISC_PLUGIN_VERSION', '1.0.2'); //Plugin version number
}
if (!defined('PRO_CAT_DISC_DIR')) {
    define('PRO_CAT_DISC_DIR', dirname(__FILE__)); // plugin dir
}
if (!defined('PRO_CAT_DISC_URL')) {
    define('PRO_CAT_DISC_URL', plugin_dir_url(__FILE__)); // plugin url
}
if (!defined('PRO_CAT_DISC_INC_DIR')) {
    define('PRO_CAT_DISC_INC_DIR', PRO_CAT_DISC_DIR . '/includes'); // Plugin include dir
}
if (!defined('PRO_CAT_DISC_INC_URL')) {
    define('PRO_CAT_DISC_INC_URL', PRO_CAT_DISC_URL . 'includes'); // Plugin include url
}
if (!defined('PRO_CAT_DISC_ADMIN_DIR')) {
    define('PRO_CAT_DISC_ADMIN_DIR', PRO_CAT_DISC_INC_DIR . '/admin'); // plugin admin dir
}
if (!defined('PRO_CAT_DISC_PLUGIN_BASENAME')) {
    define('PRO_CAT_DISC_PLUGIN_BASENAME', basename(PRO_CAT_DISC_DIR)); //Plugin base name
}
if (!defined('PRO_CAT_DISC_META_PREFIX')) {
    define('PRO_CAT_DISC_META_PREFIX', '_pcd_'); // meta data box prefix
}

/**
 * Activation Hook
 * 
 * Register plugin activation hook.
 * 
 * @package Product Category Discounts for Woo
 * @since 1.0.0
 */
register_activation_hook(__FILE__, 'pcd_install');

/**
 * Plugin Setup (On Activation)
 * 
 * Does the initial setup,
 * stest default values for the plugin options.
 * 
 * @package Product Category Discounts for Woo
 * @since 1.0.0
 */
function pcd_install() {

    global $wpdb;
}

/**
 * Deactivation Hook
 * 
 * Register plugin deactivation hook.
 * 
 * @package Product Category Discounts for Woo
 *  @since 1.0.0
 */
register_deactivation_hook(__FILE__, 'pcd_uninstall');

/**
 * Plugin Setup (On Deactivation)
 * 
 * @package Product Category Discounts for Woo
 * @since 1.0.0
 */
function pcd_uninstall() {

    global $wpdb;
}

/**
 * Load Text Domain
 * 
 * This gets the plugin ready for translation.
 * 
 * @package Product Category Discounts for Woo
 * @since 1.0.0
 */
function pcd_load_text_domain() {

    // Set filter for plugin's languages directory
    $pcd_lang_dir = dirname(plugin_basename(__FILE__)) . '/languages/';
    $pcd_lang_dir = apply_filters('pcd_languages_directory', $pcd_lang_dir);

    // Traditional WordPress plugin locale filter
    $locale = apply_filters('plugin_locale', get_locale(), 'procatdisc');
    $mofile = sprintf('%1$s-%2$s.mo', 'procatdisc', $locale);

    // Setup paths to current locale file
    $mofile_local = $pcd_lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/' . PRO_CAT_DISC_PLUGIN_BASENAME . '/' . $mofile;

    if (file_exists($mofile_global)) { // Look in global /wp-content/languages/product-category-discounts folder
        load_textdomain('procatdisc', $mofile_global);
    } elseif (file_exists($mofile_local)) { // Look in local /wp-content/plugins/product-category-discounts/languages/ folder
        load_textdomain('procatdisc', $mofile_local);
    } else { // Load the default language files
        load_plugin_textdomain('procatdisc', false, $pcd_lang_dir);
    }
}

// Add action to load plugin
add_action('plugins_loaded', 'pcd_plugin_loaded');

/**
 * Load Plugin
 * 
 * Handles to load plugin after
 * dependent plugin is loaded
 * successfully
 * 
 * @package Product Category Discounts for Woo
 * @since 1.0.0
 */
function pcd_plugin_loaded() {

    //check Woocommerce is activated or not
     if (class_exists('Woocommerce')) {

        // load first plugin text domain
        pcd_load_text_domain();

        // Global variables
        global $pcd_scripts, $pcd_model, $pcd_admin, $pcd_public;

        // Script class handles most of script functionalities of plugin
        include_once(PRO_CAT_DISC_INC_DIR . '/class-pro-cat-disc-scripts.php');
        $pcd_scripts = new PCD_Scripts();
        $pcd_scripts->add_hooks();

        // Model class handles most of model functionalities of plugin
        include_once(PRO_CAT_DISC_INC_DIR . '/class-pro-cat-disc-model.php');
        $pcd_model = new PCD_Model();

        include_once(PRO_CAT_DISC_INC_DIR . '/class-pro-cat-disc-public.php');
        $pcd_public = new PCD_Public();
        $pcd_public->add_hooks();

        // Admin class handles most of admin panel functionalities of plugin
        include_once(PRO_CAT_DISC_ADMIN_DIR . '/class-pro-cat-disc-admin.php');
        $pcd_admin = new PCD_Admin();
        $pcd_admin->add_hooks();
     }
}

?>