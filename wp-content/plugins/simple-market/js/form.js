jQuery(function ($) {
	
	jQuery(document).ready(function($){
		showRecaptcha();
	});
	
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
				if(typeof resp !== 'undefined') {
					if(typeof resp['success'] !== 'undefined') {
						if(resp['success'] == true) {
							$('input[name=sm_submit_id]').val(resp['submit_id']);

							$('#sm-head-anchor').scrollTo(1000, function() {
								$('#sm-market-div').html("");
								$('#sm-preview-div').html(resp['preview']);
								$('#sm-preview-div').show();
								$('#sm-first-from-div').hide();
								
								$('#sm-head-anchor').fadeOut(1500, function() {
									var title = $('.entry-title').html();
									if(title.indexOf("Inserat Vorschau") === -1) {
										$('.entry-title').html(title + "- Inserat Vorschau");										
									}
								});
							});					
						} else {
							var error = '';
							for(var i = 0; i < resp['errors'].length; i++) {
								error = '#' + resp['errors'][i];
								$(error).fadeIn('slow');
							}
							
							$('#sm-form-a').scrollTo(600);
							Recaptcha.reload();
						}
					}	
				}
			} 
		});
	});
	
	function clear_errors() {
		$('.sm-error-div').fadeOut('slow');
	}
	
	$('#sm-preview-submit-btn').live('click', function() {
		var form_data = $('#sm-preview-form').serialize();
		form_data += "&action=sm_preview_submit";
		$.ajax({
			type: "POST",
			url: SMInject['url'],
			data: form_data,
			success: function(resp) {
				if(typeof resp !== 'undefined') {
					if(typeof resp['success'] !== 'undefined') {
						if(resp['success'] == true) {
							$('#sm-preview-div').html("Noch einen Schritt zur Inseratsfreischaltung: <br/><br/>"
									+"Bitte aktivieren Sie Ihren Inseratstext mit dem Aktivierungslink "
									+"welchen wir gerade an die von Ihnen angegeben E-Mail-Adresse gesendet haben.");
							return;
						}
					}					
				}
				alert("Ups. Leider ist ein Fehler aufgetreten.");
			}
		});
	});
	
	$('#sm-preview-abort-btn').live('click', function() {
		Recaptcha.destroy();
		$('#sm-preview-div').hide();
		$('#sm-preview-div').html("");
		$('#sm-first-from-div').fadeIn(1500);
	});
});