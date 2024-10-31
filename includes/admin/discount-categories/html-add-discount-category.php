<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

$disc_types = $this->model->pcd_get_disc_types(); // Get discount types
$option_val = '';

do_action('pcd_disc_cat_html_before');

do_action('pcd_add_disc_cat_html_before');
?>

<h2><?php esc_html_e('Discount Category Fields', 'procatdisc'); ?></h2>
<p class="description"><?php esc_html_e('Use below fields to setup this category for discounts.', 'procatdisc');?></p>
<div class="form-field">
    <label for="pcd_disc_label"><?php esc_html_e('Discount Label', 'procatdisc'); ?></label>
    <input type="text" class="pcd-cat-disc-disc-label" id="pcd_disc_label" name="pcd_term_meta[disc_label]">
    <p class="description"><?php esc_html_e('Enter Discount label. This will be shown on frontend when discount will be applied. Leave it empty if you want to show "Name" of category.', 'procatdisc');
?></p>
</div>

<div class="form-field">
    <label for="pcd_sel_disc_type"><?php esc_html_e('Select Discount Type', 'procatdisc');
?></label>
    <?php
    echo '<select id="pcd_sel_disc_type" class="pcd-cat-disc-sel-disc-type pcd-has-select2" name="pcd_term_meta[disc_type]">';
    foreach ($disc_types as $disc_type_key => $disc_type_val) {
        $option_val .= '<option value="' . esc_attr($disc_type_key) . '">' . esc_html($disc_type_val) . '</option>';
    }
    echo $option_val;
    echo '</select>';
    ?>
    <p class="description"><?php esc_html_e('Select Discount Type.', 'procatdisc'); ?></p>
</div>

<div class="form-field form-required">
    <label for="pcd_disc_amt"><?php esc_html_e('Discount Amount', 'procatdisc'); ?></label>
    <input type="number" class="pcd-cat-disc-disc-amt" id="pcd_disc_amt" min="0" name="pcd_term_meta[disc_amt]">
    <p class="description"><?php esc_html_e('Enter Discount Amount.', 'procatdisc'); ?></p>
</div>
<?php
do_action('pcd_disc_cat_html_after');

do_action('pcd_add_disc_cat_html_after');
?>