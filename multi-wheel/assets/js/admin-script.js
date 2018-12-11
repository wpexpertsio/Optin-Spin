jQuery(document).ready(function() {

    setCookie('validate-submition',0);

	jQuery('input[name=_optinspin_mailchimp_get_list]').attr('value','Get Mailchimp Email List');
	jQuery('input[name=_optinspin_mailchimp_api_key]').addClass('mailchimp_api_key');
	
	jQuery('input[name=_optinspin_mailchimp_get_list]').click(function() {
		
		jQuery('#errorkey').remove();
		if(jQuery('input[name=_optinspin_mailchimp_api_key]').val()){
			var ajaxurl = php_data.ajaxurl;
			var data = {
				'action': 'optinspin_mailchimp_get_list',
				'_optinspin_mailchimp_api_key': jQuery("input[name='_optinspin_mailchimp_api_key']").val(),
			};
			jQuery.post(ajaxurl, data, function(response) {
				var obj = JSON.parse(response);
				
				if(obj.statuss && obj.response != null){
					jQuery('select[name=_crb_show_socials]').html(obj.response);
					jQuery('select[name=_crb_show_socials]').openSelect();
				} else {
					
					jQuery('input[name=_optinspin_mailchimp_api_key]').after('<div id="errorkey" style="color:red;">'+obj.error+'</div>');
				}
				
			   
			});
		} else {
			jQuery('input[name=_optinspin_mailchimp_api_key]').attr('placeholder','Please API KEY!');
		}
		
	});
	
	jQuery('input[name=_optinspin_zapier_webhook_url]').attr('value','Click here to Test Zapier Webhook!');
	jQuery('input[name=_optinspin_active_campaign_get_list]').attr('value','Get Active Campaign Email List');
	jQuery('input[name=_optinspin_active_campaign_api_key]').addClass('active_campaign_api_key');
	jQuery('input[name=_optinspin_active_campaign_get_list]').click(function() {
		
		jQuery('#errorkey').remove();
		if(jQuery('input[name=_optinspin_active_campaign_url]').val() && jQuery('input[name=_optinspin_active_campaign_api_key]').val()){
			var ajaxurl = php_data.ajaxurl;
			var data = {
				'action': 'optinspin_active_campaign_get_list',
				'_optinspin_active_campaign_api_key': jQuery("input[name='_optinspin_active_campaign_api_key']").val(),
				'_optinspin_active_campaign_url': jQuery("input[name='_optinspin_active_campaign_url']").val(),
			};
			
			
			 jQuery.post(ajaxurl, data, function(response) {
				var obj = JSON.parse(response);
				console.log(obj);
				if(obj.statuss && obj.response != null){
					jQuery('select[name=_crb_show_socials_active_campaign]').html(obj.response);
					jQuery('select[name=_crb_show_socials_active_campaign]').openSelect();
				} else {
					jQuery('input[name=_crb_show_socials_active_campaign]').after('<div id="errorkey" style="color:red;">'+obj.error+'</div>');
				}
				
			   
			}); 
		} else {
			jQuery('input[name=_optinspin_active_campaign_api_key]').attr('placeholder','API KEY Please!');
			jQuery('input[name=_optinspin_active_campaign_url]').attr('placeholder','URL Please!');
		}
		
	});
	
	
	
	//zapier test
	jQuery('input[name=_optinspin_zapier_webhook_url]').click(function() {
		
		jQuery('#errorkey').remove();
		if(jQuery('input[name=_optinspin_zapier_url]').val()){
			var ajaxurl = php_data.ajaxurl;
			var data = {
				'action': 'test_zapier_webhook',
				'_optinspin_zapier_url': jQuery("input[name='_optinspin_zapier_url']").val(),
			};
			
			
			 jQuery.post(ajaxurl, data, function(response) {
				
				if(response == 'Success'){
					jQuery('input[name=_optinspin_zapier_webhook_url]').attr('value','Zapier Linked Now test from Zapier Dashboard!');
					jQuery('input[name=_optinspin_zapier_webhook_url]').attr("disabled", "disabled");
				}
			   
			}); 
		} else {
			jQuery('input[name=_optinspin_zapier_url]').attr('placeholder','Please Submit Zapier Webhook URL');
			
		}
		
	});
	
	if(jQuery('input[name=_optinspin_mailsteractive]').val() == '0' ){
		jQuery('.custom_class_mailsteractive_Lists').css('display', 'none');
	}
(function($) {
"use strict";
$.fn.openSelect = function()
{
	return this.each(function(idx,domEl) {
		if (document.createEvent) {
			var event = document.createEvent("MouseEvents");
			event.initMouseEvent("mousedown", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
			domEl.dispatchEvent(event);
		} else if (element.fireEvent) {
			domEl.fireEvent("onmousedown");
		}
	});
}
}(jQuery));


	jQuery('#publishing-action input').click(function(e) {

		if( getCookie('validate-submition') == 0 ) {
            e.preventDefault();
			jQuery('.carbon-groups-holder.ui-sortable .carbon-row.carbon-group-row').each(function() {
				var unqiue_section_id = jQuery(this).attr('id');
				unqiue_section_id= unqiue_section_id.replace('carbon-complex-group-','');
				console.log(unqiue_section_id);
				jQuery(this).find('.fields-container .optinspin_section_class .field-holder input').val(unqiue_section_id);
			});
            setCookie('validate-submition',1);
            jQuery(this).click();
        }
    });

	jQuery('.optinspin_flush_availability input').click(function() {

		var section_id = jQuery(this).closest('.fields-container').find('.optinspin_section_class input').val();
		
        var ajaxurl = php_data.ajaxurl;

        var data = {
            'action': 'optinspin_coupon_request',
            'request_to': 'flush_availablity',
            'section_id': section_id,
        };

        jQuery.post(ajaxurl, data, function(response) {
        	alert('Data Cleared');
        });
	});

	jQuery('.optinspin_flush_availability input').val('Clear number of win');
});

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires="+d.toUTCString();
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
