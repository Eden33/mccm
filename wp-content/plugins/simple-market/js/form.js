jQuery(function ($) {
	$('#sm-submit-btn').click(function() {
		clear_errors();
		var form_data = $('#sm-form').serialize();
		form_data +=  '&action=sm_submit_form';
		form_data += '&sm_nonce=' + SMInject['nonce'];
		$.ajax({
			type: "POST",
			url: SMInject['url'],
			data: form_data,
			success: function(resp) {
				
				if(typeof resp['success'] !== 'undefined') {
					if(resp['success'] == true) {
						$('input[name=sm_submit_id]').val(resp['submit_id']);

						$('#sm-head-anchor').scrollTo(5000);

						$('#sm-head-anchor').scrollTo(3000, function() {
							$('#sm-market-div').html("");
							$('#sm-preview-div').html(resp['preview']);
							$('#sm-first-from-div').fadeOut(1500);
							
							$('#sm-head-anchor').fadeOut(1500, function() {
								var title = $('.entry-title').html();
								$('.entry-title').html(title + "- Inserat Vorschau");	
							});
						});					
					} else {
						var error = '';
						for(var i = 0; i < resp['errors'].length; i++) {
							error = '#' + resp['errors'][i];
							$(error).fadeIn('slow');
						}
						
						$('a[name=simplemarketform]').scrollTo(600);
					}
				}
			} 
		});
	});
	
	function clear_errors() {
		$('.sm-error-div').fadeOut('slow');
	}
});