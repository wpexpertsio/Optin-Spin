jQuery(document).ready(function() {
/*    jQuery('.carbon-radio-list input[type="radio"]').click(function() {
        var optinspin_type = jQuery(this).val();
        console.log(optinspin_type);
        if( optinspin_type == 'string' ) {
            jQuery('.carbon-group-row:not(.collapsed) .optinspin_section_image').hide();
            jQuery('.carbon-group-row:not(.collapsed) .optinspin_section_label').show();
        } else if( optinspin_type == 'image' ) {
            jQuery('.carbon-group-row:not(.collapsed) .optinspin_section_image').show();
            jQuery('.carbon-group-row:not(.collapsed) .optinspin_section_label').hide();
        }
    });*/
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

	jQuery('.ui-sortable-handle').removeClass('carbon-drag-handle');
	
});