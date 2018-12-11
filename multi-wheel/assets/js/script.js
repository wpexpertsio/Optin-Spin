jQuery(document).ready(function() {
    jQuery('.optinspin-optin-bar span.cancel').on('click', function() {
        jQuery('.optinspin-optin-bar').hide();
        setCookie_optinspin('optinspin-cross-couponbar', 1);
    });
});

function setCookie_optinspin(cname, cvalue, exptime, duration_type) {

    var d = new Date();
    if (duration_type === undefined) {
        d.setTime(d.getTime() + (exptime * 24 * 60 * 60 * 1000));
    } else if( duration_type == 'hour' ) {
        d.setTime(d.getTime() + (exptime * 60 * 60 * 1000) );
    } else if( duration_type == 'day' ) {
        d.setTime(d.getTime() + (exptime * 24 * 60 * 60 * 1000));
    }
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}