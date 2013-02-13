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
							$('#sm-form-images-submit-btn').click();	
							SMInject['preview'] = resp['preview'];
							
							//needed because 'fileuploadprogressall' is never executed in this case
							//-> user hasn't selected any files to upload for his ad
							if($('.template-upload').length == 0) {
								showAdPreview();
							}
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
	
	$(document).on('click', '#sm-preview-submit-btn', function() {
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
	
	$(document).on('click', '#sm-preview-abort-btn', function() {
		Recaptcha.destroy();
		//save the thumb container
		SMInject['thumb-container-html-abort-state'] = $('#sm-thumb-preview-container').html();
		$('#sm-preview-div').hide();

		//hide only
		//$('#sm-preview-div').html("");
		$('#sm-first-from-div').fadeIn(1500);
	});
	
	if($('#sm-form-images').length) {
	
		//http://blueimp.github.com/jQuery-File-Upload/
		//http://missioncriticallabs.com/blog/2012/04/lessons-learned-from-jquery-file-upload/
	    $('#sm-form-images').fileupload({
	        maxFileSize: 512000,
	        maxNumberOfFiles: 4,
	        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
	        url: SMInject['url']
	    });
	    
	    $('#sm-form-images').bind('fileuploaddone', function(e, data) {
	    	if(typeof data.result !== 'undefined' && $.isArray(data.result.files)) {
	    		var img_data = data.result.files[0];
	    		//if no error proceed
	    		if(typeof img_data !== 'undefined' && typeof img_data.error === 'undefined')  {
		    		//get a free "slot"
	    			$('.sm-thumb-preview').each(function(index){
		    			if($(this).html() == "") {
		    				var the_html = '<a href="'+img_data.url+'" target="_blank" title="Anzeigen Bild '+
		    								+ index + '" class="cboxElement">'
		    								+'<img title="" alt="" src="'+img_data.thumbnail_url+'" width="120" height="120" />'
		    								+'</a>';
		    				$(this).html(the_html);
		    				var img_map_obj = new Object();
		    				img_map_obj.id = $(this).attr('id');
		    				img_map_obj.name = img_data.name;
		    				SMInject['img_map'].push(img_map_obj);
		    				return false;
		    			}
		    		});
	    		}
	    	} else {
	    		alert(unescape("Ups. Da ist wohl etwas schief gegangen.... Dies h%E4tte nicht passieren d%FCfen"));
	    	}
	    });
	    
	    $('#sm-form-images').bind('fileuploaddestroy', function(e, data){
	    	//default is not good for our needs because sm_submit_id and action 
	    	//is not sent in POST request ....
	    	var default_url = data.url;
	    	
	    	//if present and hidden, user aborted preview and changed something .. probably removed an image?
	    	//this is handled here -> remove image also from preview container on removal from server
	    	if($('#sm-thumb-preview-container').length > 0 && SMInject['img_map'].length > 0) {
	    		//go over img map and remove html
	    		for(var i = 0; i < SMInject['img_map'].length; i++) {
	    			if(default_url.indexOf(SMInject['img_map'][i].name) !== -1) {
	    				$('#'+SMInject['img_map'][i].id).html("");
	    			}
	    		}
	    	}
	    	
	    	//TODO: pay attention to line 336 and 337 in query.fileupload-uis.js .. fix this ...
	    	
	    	data.url = undefined; //we can prevent default POST request by setting url to undefined
	    	var sm_submit_id = $('input[name=sm_submit_id]').val();
	    	
			var form_data =  '&action=sm_submit_form_images';
			form_data += '&sm_submit_id=' + sm_submit_id;
						
			$.ajax({
				type: "POST",
				url: default_url,
				data: form_data,
				success: function(resp) {
					if(typeof resp !== 'undefined') {
						//console.log("Yes we got an resonse.");
					}
				} 
			});	    	
	    });
	    
	    $('#sm-form-images').bind('fileuploaddone', function (e, data) {
	    	//alert("success");
	    });
	    $('#sm-form-images').bind('fileuploadprogressall', function (e, data) {
	    	var progress = parseInt(data.loaded / data.total * 100, 10);
	    	if(progress >= 100) {
	    		showAdPreview();
	    	}
	    	//console.log("Data progress: "+progress+" loaded: "+data.loaded+" total: "+data.total);
	    });
	}
	
	function showAdPreview() {
		$('#sm-head-anchor').scrollTo(1000, function() {
			$('#sm-market-div').html("");
			
			var old_thumbs = undefined;
			if($('#sm-thumb-preview-container').length > 0) {
				//preview allready present but hidden
				old_thumbs = $('#sm-thumb-preview-container').html();
			}
			$('#sm-preview-div').html(SMInject['preview']);
			if(typeof old_thumbs !== 'undefined') {
				$('#sm-thumb-preview-container').html(old_thumbs);
			}
			
			//go over and remove from abort state if user removed
			$('#sm-preview-div').show();
			$('#sm-first-from-div').hide();
			
			$('#sm-head-anchor').fadeOut(1500, function() {
				var title = $('.entry-title').html();
				if(title.indexOf("Inserat Vorschau") === -1) {
					$('.entry-title').html(title + "- Inserat Vorschau");										
				}
			});
		});
	}
});