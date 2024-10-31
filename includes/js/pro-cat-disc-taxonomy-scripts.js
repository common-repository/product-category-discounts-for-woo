// On document ready
jQuery(document).ready(function ($) {

    // When category added, than unset all fields, as default WP does
    // Code from WC
    $(document).ajaxComplete(function (event, request, options) {

        // If response is success
        if (request && 4 === request.readyState && 200 === request.status
                && options.data && 0 <= options.data.indexOf('action=add-tag')) {

            var res = wpAjax.parseAjaxResponse(request.responseXML, 'ajax-response');
            if (!res || res.errors) {
                return;
            }
            $('#pcd_disc_amt').val(''); // Unset Amount
            $('#pcd_sel_disc_type option:eq(0)').attr('selected', 'selected'); // Select first option
            $('#pcd_sel_disc_type').trigger('change');
            return;
        }
    });

    // Add select2
    $('.pcd-has-select2').each(function () {
        $(this).select2();
    });
});