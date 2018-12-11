setTimeout(function () {
    //document.getElementById('carbon_fields_container_wheel_settings').style = "display:none";
},1000);

var disabled_btn = 1; // Next Button is diabled by Default
var current_step = 0; // '0' is eqaul to step '1'
var eligible_step = 0; // '0' is eqaul to step '1'
var sidebar_controls = 0;

jQuery(document).ready(function() {

    if( opt_getUrlParameter('page') == 'crb_carbon_fields_container_optin_spin.php' ) {

            // PREPEND INFO TEXT ON PAGE LOAD
            jQuery('.carbon-container-carbon_fields_container_optin_spin').append('<div class="opt-errors"></div><div class="opt-next-btn">Next</div>');

            jQuery(document).ready(function() {
                jQuery('#wpbody-content').prepend('<div class="opt-steps"> <div class="opt-step general active"><span>1</span> <div class="opt-text">General</div></div> <div class="opt-step wheel-slices section"><span>2</span> <div class="opt-text">Wheel Slices</div></div> <div class="opt-step triggers"><span>3</span> <div class="opt-text">Triggers</div></div> <div class="opt-step integration"><span>4</span> <div class="opt-text">Integration</div></div>  <div class="opt-step winning-lossing"><span>6</span> <div class="opt-text">Coupon Style <i class="fas fa-paint-brush"></i></div></div> <div class="opt-step ready"><span>5</span> <div class="opt-text">Ready <i class="fas fa-check-circle"></i></div></div> </div>');
            });
    }
});

jQuery(window).load(function() {
    // SHOW FIELDS AFTER PAGE LOAD
    jQuery('div#titlediv').fadeIn();
    jQuery('div#post-body-content').addClass('hidden-load');
    jQuery('div#carbon_fields_container_wheel_settings').hide();

    opt_active_tab();

    // DISBALED BUTTON AS DEFAULT
    if( disabled_btn == 1 ) {
        //jQuery('.opt-next-btn').addClass('disabled');
    }

    // CHECK IF PREVIEW CLICK
    /*  if( getCookie('opt-view-preview') == '1' ) {
         document.cookie = "opt-view-preview=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
         var url = jQuery('.opt-act-btn.preview a').attr('href');
         setTimeout(function() {
             jQuery('.opt-step.ready').click();
         },2000);
         window.open( url, '_blank' );
     } */

    // DISPLAY SETTING BUTTON CHECKED BY DEFAULT
    if( typeof opt_getUrlVars()['post'] == 'undefined' ) {
        if( !jQuery('input[name="_optinspin_display_slider_wheel"]').is(':checked') )
            jQuery('input[name="_optinspin_display_slider_wheel"]').click();
        jQuery('input[name="_optinspin_enable_clickable_tab_desktop"]').click();
        jQuery('input[name="_optinspin_enable_clickable_tab_mobile"]').click();

        if( !jQuery('input[name="_optinspin_display_all_pages"]').is(':checked') )
            jQuery('input[name="_optinspin_display_all_pages"]').click();
    }

    // VIEW SETTING SIDEBAR IF POST WHEEL ALREADY PUBLISHED
    if( typeof opt_getUrlVars()['post'] != 'undefined' ) {
        opt_initiate_sidebar_option();
    }

    // DEFUALT GREEN TICK IF WHEEL IS PUBLISHED
    if( typeof opt_getUrlVars()['post'] != 'undefined' ) {
        jQuery('.opt-step.ready').addClass('completed');
    }

    jQuery('.opt-steps .opt-step').click(function(e) {

        if( opt_is_fine() ) {
            var step_num = jQuery(this).index();

            if( eligible_step < 4 && step_num > eligible_step && typeof opt_getUrlVars()['post'] == 'undefined' && jQuery('.carbon-groups-holder.ui-sortable .carbon-group-row').length < 8 )
                return;

            //opt_is_fine( e );

            jQuery('div#carbon_fields_container_wheel_settings').hide();

            jQuery('.opt-step').removeClass('active');
            jQuery(this).addClass('active');


            jQuery('ul.carbon-tabs-nav li a')[step_num].click();

            current_step = step_num;

            if( step_num > 0 ) {
                jQuery('#postbox').hide();
                jQuery('.postbox.carbon-box').fadeIn();
                jQuery('div#carbon_fields_container_wheel_settings').fadeIn();
            }

            opt_btn_disabled();
        }
    });

    jQuery('.opt-step.title').click(function() {
        jQuery('div#carbon_fields_container_wheel_settings').hide();
        jQuery('div#titlediv').fadeIn();
        jQuery('div#post-body-content').fadeIn();
    });

    jQuery('.opt-next-btn').on('click',function(e) {

        if( opt_is_fine() ) {

            eligible_step = eligible_step + 1;

            if( !jQuery(this).hasClass('disabled') )
                jQuery('.opt-steps .opt-step')[current_step+1].click();

            //jQuery('.opt-next-btn').addClass('disabled');

            opt_btn_disabled();

        }
    });

    jQuery('#titlewrap input#title').keyup(function() {
        if( jQuery(this).val() != '' )
            jQuery('.opt-next-btn').removeClass('disabled');
        else {
            //jQuery('.opt-next-btn').addClass('disabled');
        }
    });

    jQuery(".container-carbon_fields_container_wheel_settings").on('DOMNodeInserted', function(e) {
        var section_count = jQuery('.carbon-group-row').length;

        if( section_count > 0 )
            jQuery('.opt-next-btn').removeClass('disabled');
        else {
            //jQuery('.opt-next-btn').addClass('disabled');
        }
    });

    jQuery(".carbon-subcontainer").on('DOMNodeRemoved', function(e) {
        setTimeout(function () {
            var section_count = jQuery('.carbon-group-row').length;
            if( section_count > 0 )
                jQuery('.opt-next-btn').removeClass('disabled');
            else {
                //jQuery('.opt-next-btn').addClass('disabled');
            }
        },1000);
    });

    jQuery('.opt-adv-setting-btn').click(function() {
        jQuery('.opt-next-btn').click();
    });

    jQuery('.opt-sidebar-cross').click(function() {
        opt_close_sidebar_controls();
    });

    jQuery('.opt-pull-settings').click(function() {
        if( sidebar_controls == 0 )
            opt_open_sidebar_controls();
        else
            opt_close_sidebar_controls();
    });

    var sub_tabs_indexes = new Array(); var counter = 0;
    jQuery('.carbon-container-carbon_fields_container_wheel_settings .optinspin_separator').each(function() {
        sub_tabs_indexes[counter] = jQuery(this).index() + 1;
        counter++;
    });

    jQuery('ul.optinspin-sub-tabs li').click(function() {
        var jump_to = sub_tabs_indexes[ jQuery(this).index() ];

        $('html, body').animate({
            scrollTop: jQuery(".carbon-fields-collection.carbon-tab.active .optinspin_separator:nth-child("+jump_to+")").offset().top - 50
        }, 2000);

    });

    jQuery('#opt-publish').click(function() {
        jQuery('#publish').click();
    });

    jQuery('#opt-draft').click(function() {
        jQuery('#save-post').click();
    });

    jQuery('#opt-draft-preview').click(function() {
        setCookie('opt-view-preview',1);
        jQuery('#save-post').click();
    });

    jQuery('#opt-preview').click(function() {
        // jQuery('#publish')click();
    });

});

function opt_active_tab() {
    setTimeout(function () {
        var window_hash = window.location.hash;
        window_hash = window_hash.replace('#!','');
        if( window_hash != '' ) {
            jQuery('.opt-step').removeClass('active');
            jQuery('.opt-steps .opt-step.'+window_hash).click();
        }
    },1000);
}

function opt_getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    var results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
};

function opt_is_fine( e ) {
    opt_remove_error();
    if( current_step == 1 ) {
        var counter = 0; var  error = 0;
        if( jQuery('.carbon-groups-holder.ui-sortable .carbon-group-row').length != 8 ) {
            opt_display_error('Please Add 8 slices');
            return false;
        } else {
            jQuery('.carbon-groups-holder.ui-sortable .carbon-group-row').each(function() {
                var probability = jQuery(this).find('input[name="_crb_section['+counter+'][_optinspin_probability]"]').val();
                if( probability == '' ) {
                    //jQuery('.opt-next-btn').addClass('disabled');
                    error = 1;
                } else {
                    jQuery('.opt-next-btn').removeClass('disabled');
                }
                counter++;
            });
        }

        if( error == 1 ) {
            opt_display_error('Please enter probability value ( 0 - 100 )');
            return false;
        }
    }

    return true;
}

function opt_remove_error() {
    jQuery('.opt-errors').hide();
}

function opt_getUrlVars() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

function opt_btn_disabled() {

    if( current_step == 5 ) {
        jQuery('.opt-next-btn').hide();
    } else {
        jQuery('.opt-next-btn').show();
    }

    if( current_step == 0 ) {
        if( jQuery('#titlewrap input#title').val() == '' ) {
            //jQuery('.opt-next-btn').addClass('disabled');
        } else
            jQuery('.opt-next-btn').removeClass('disabled');
    } else if( current_step == 2 ) {
        jQuery('.opt-next-btn').removeClass('disabled');
    } else if( current_step == 3 ) {
        jQuery('.opt-next-btn').removeClass('disabled');
    } else if( current_step == 5 && typeof opt_getUrlVars()['post'] == 'undefined' ) {
        setTimeout(function() {
            opt_open_sidebar_controls();
        },1000);
        jQuery('.opt-next-btn').removeClass('disabled');
        jQuery('.opt-step.ready').addClass('completed');
    }
}

function opt_open_sidebar_controls() {
    sidebar_controls = 1;
    jQuery( ".opt-pull-settings" ).show();
    jQuery( ".opt-pull-settings" ).animate({
        right: "23%",
    },500);
    jQuery( ".opt-action-sidebar" ).show();
    jQuery( ".opt-action-sidebar" ).animate({
        opacity: "1",
        right: "0%",
    },500);
}

function opt_close_sidebar_controls() {
    sidebar_controls = 0;
    jQuery( ".opt-pull-settings" ).animate({
        right: "0%",
    },500);
    jQuery( ".opt-action-sidebar" ).animate({
        opacity: "1",
        right: "-23%",
    },500,function() {
        jQuery( ".opt-action-sidebar" ).hide();
    });
}

function opt_initiate_sidebar_option() {
    jQuery('.opt-pull-settings').show();
    jQuery( ".opt-pull-settings" ).animate({
        right: "0%",
    },500);
}

function opt_display_error( text ) {
    jQuery('.opt-errors').show();
    jQuery('.opt-errors').html('<p>'+text+'</p>');
}

jQuery(document).on('DOMNodeInserted', function(e) {
    if ( $(e.target).hasClass('carbon-groups-holder') ) {
        alert('test2');
    }
});