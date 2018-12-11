jQuery(document).ready(function() {
    jQuery('#publishing-action').append('<div id="save_preview"><a href="#">Save & Preview</a></div>');

    jQuery('#save_preview').click(function() {

        setCookie('');
        jQuery('#publish').click();
    });

    jQuery('#optinspin_understand').click(function() {
        if (jQuery('input#optinspin_understand').prop('checked')) {
            jQuery('.optinspin-understand').fadeOut();
            jQuery('.optinspin-understand-btn').fadeIn();
        }
    });
});

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}