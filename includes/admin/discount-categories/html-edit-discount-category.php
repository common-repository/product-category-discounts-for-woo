<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

global $pcd_model;

$option_val = '';

$t_id = $term->term_id;
$term_meta = get_option("taxonomy_$t_id"); // retrieve the existing value(s) for this meta field. This returns an array
extract($term_meta);

// Get static data
$disc_types = $pcd_model->pcd_get_disc_types();

do_action('pcd_disc_cat_html_before');

do_action('pcd_edit_disc_cat_html_before', $term);

?>

<tr class="form-field">
	<td colspan="2">
		<b><?php esc_html_e('Discount Category Fields', 'procatdisc'); ?></b>
		<p class="description"><?php esc_html_e('Use below fields to setup this category for discounts.', 'procatdisc');?></p>
	</td>
</tr>

<tr class="form-field wcd-product-add-cat-desc">
    <td colspan="2">
	    <?php esc_html_e('Enjoying the product? Check the PRO version features like discounts based on user roles, specific time, after purchase discount and a lot more...', 'woocatdisc');?>
	    <a href="https://codecanyon.net/item/woocommerce-category-discount/20332051"><?php esc_html_e('Upgrade to Pro >>', 'woocatdisc'); ?></a>
    </td>
</tr>

<tr class="form-field">
    <th scope="row" valign="top"><label for="pcd_disc_label"><?php
            esc_html_e('Discount Label', 'procatdisc');
            ?></label></th>
    <td>
        <input type="text" size="40" class="pcd-cat-disc-disc-label" id="pcd_disc_label" value="<?php
               echo !empty($disc_label) ? esc_attr($disc_label) : '';
               ?>" name="pcd_term_meta[disc_label]">
        <p class="description"><?php
            esc_html_e('Enter Discount label. This will be shown on frontend when discount will be applied. Leave it empty to show "Name"', 'procatdisc');
            ?></p>
    </td>
</tr>

<tr class="form-field">
    <th scope="row" valign="top"><label for="pcd_sel_disc_type"><?php
            esc_html_e('Select Discount Type', 'procatdisc');
            ?></label></th>
    <td>
        <?php
        echo '<select id="pcd_sel_disc_type" class="pcd-cat-disc-sel-disc-type pcd-has-select2" name="pcd_term_meta[disc_type]">';
        foreach ($disc_types as $disc_type_key => $disc_type_val) {

            $option_val = '';

            $option_val .= '<option value="' . esc_attr($disc_type_key) . '"';
            if (!empty($disc_type) && $disc_type == $disc_type_key) {
                $option_val .= 'selected="selected"';
            }
            $option_val .= '>' . esc_html($disc_type_val) . '</option>';

            echo $option_val;
        }
        echo '</select>';
        ?>
        <p class="description"><?php esc_html_e('Select Discount Type.', 'procatdisc'); ?></p>
    </td>
</tr>

<tr class="form-field form-required">
    <th scope="row" valign="top"><label for="pcd_disc_amt"><?php
            esc_html_e('Discount Amount', 'procatdisc');
            ?></label></th>
    <td>
        <input type="number" class="pcd-cat-disc-disc-amt" id="pcd_disc_amt" min="0"
               name="pcd_term_meta[disc_amt]" value="<?php esc_html_e($disc_amt); ?>" aria-required="true">
        <p class="description"><?php esc_html_e('Enter Discount Amount.', 'procatdisc'); ?></p>
    </td>
</tr>

<?php
do_action('pcd_disc_cat_html_after');

do_action('pcd_edit_disc_cat_html_after', $term);
?>