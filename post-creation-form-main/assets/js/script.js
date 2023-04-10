jQuery(document).ready(function() {
    $form = jQuery("form[name='savePost']");
    jQuery($form).on('submit', function (event) {
        event.preventDefault();
        jQuery.ajax({
            type: 'post',
            url: ajaxurl.ajaxurl,
            data : $form.serialize() + "&action=save_post",
            success: function (response) {
                jQuery("form[name='savePost'] .display").html(response);
            }
        });
    });
});