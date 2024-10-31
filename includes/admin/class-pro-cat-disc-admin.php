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
class PCD_Admin {

    public $model, $scripts;

    // Class constructor
    function __construct() {

        global $pcd_model, $pcd_scripts;

        $this->model = $pcd_model;
        $this->scripts = $pcd_scripts;
    }

    /**
     * Add action to add meta field 
     * while adding new taxonomy
     * 
     * @package Product Category Discounts for Woo
     * @since 1.0.0
     */
    function pcd_add_meta_field($taxonomy) {

        // Include html for adding new Discount Category
        include(PRO_CAT_DISC_ADMIN_DIR . '/discount-categories/html-add-discount-category.php');
    }

    /**
     * Add action to add meta field
     * while editing new taxonomy
     *
     * @package Product Category Discounts for Woo
     * @since 1.0.0
     */
    public function pcd_edit_meta_field($term, $taxonomy) {

        // Include html for editing Discount Category
        include(PRO_CAT_DISC_ADMIN_DIR . '/discount-categories/html-edit-discount-category.php');
    }

    /**
     * Add action to save meta data while taxonomy saves
     * 
     * @package Product Category Discounts for Woo
     * @since 1.0.0
     */
    public function pcd_save_taxonomy_custom_meta($term_id) {
        $santitized_post = $this->model->pcd_nohtml_kses($_POST); // Sanitize $_POST
        // If $_POST contains our meta, while saving discount category
        if (isset($santitized_post['pcd_term_meta'])) {

            $term_meta = get_option("taxonomy_$term_id"); // Get term meta
            $cat_keys = array_keys($santitized_post['pcd_term_meta']); // Get keys in array

            // Loop on keys array
            foreach ($cat_keys as $key) {

                $value = '';
                // If our key is set
                if (isset($santitized_post['pcd_term_meta'][$key])) {

                    $value = $santitized_post['pcd_term_meta'][$key];
                    $term_meta[$key] = is_array($santitized_post['pcd_term_meta'][$key]) ? serialize($value) : $value; // Create an array
                }
            }

            // Save the option array.
            update_option("taxonomy_$term_id", $term_meta);
        }
    }

    /**
     * Adding Hooks
     *
     * @package Product Category Discounts for Woo
     * @since 1.0.0
     */
    function add_hooks() {
    	// Add action to add product select field under our taxonomy - add new page
        add_action('product_cat_add_form_fields', array($this, 'pcd_add_meta_field'), 15);
        
        // Add action to add product select field under our taxonomy - edit page
        add_action('product_cat_edit_form_fields', array($this, 'pcd_edit_meta_field'), 15, 2);

        // Add action to save data entered in our custom field for product taxonomy
        add_action('edited_product_cat', array($this, 'pcd_save_taxonomy_custom_meta'), 10, 2);
        add_action('create_product_cat', array($this, 'pcd_save_taxonomy_custom_meta'), 10, 2);
    }

}

?>