jQuery(document).ready(function() {
    jQuery('#optinspin_understand').click(function() {
        jQuery('.optinspin-confirm-btns').fadeOut();
        setTimeout(function () {
            jQuery('.optinspin-get-ready').fadeIn(500);
        },500);
        setTimeout(function () {
            jQuery('.optinspin-get-ready').fadeOut(500);
        },1000);
        setTimeout(function () {
            jQuery('.optinspin-get-ready').fadeIn(500);
        },1500);
        setTimeout(function () {
            jQuery('.optinspin-get-ready').fadeOut(500);
        },2000);
        setTimeout(function() {
            jQuery('.optinspin-understand-btn').fadeIn();
        },2500);
    });

    jQuery('#optinspin_not_understand').click(function() {
        var global_url = window.location.href.split('?')[0];
        window.open(global_url+'?page=crb_carbon_fields_container_optin_spin.php#!general','_self');
    });
});