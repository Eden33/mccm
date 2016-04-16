jQuery(function ($) {
	
	DEBUG_SM_JS = false;
	
	jQuery(document).ready(function($){
		if(typeof showSimpleMarketFormRecaptcha === 'function') {
			showSimpleMarketFormRecaptcha();		
		}
	});
	
	var sm_submit_btn_clicked = false;
	
	$('#sm-submit-btn').click(function() {
		
		if(sm_submit_btn_clicked)
			return;
		
		if(!document.getElementById("sm_terms_checkbox").checked) {
			alert("Du musst die Nutzungsbedingungen unseres MCCM Online Marktes akzeptieren bevor du ein Inserat aufgeben kannst.");
			return false;
		}
		
		sm_submit_btn_clicked = true;
		
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
							
							//success, now image uploads are executed, enable the button again
							//on user clicks on btn "Ich will noch etwas ändern or if error occured"
							
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
							sm_submit_btn_clicked = false;
						}
					}	
				}
			},
			error: function(resp) {
				alert("Sry - Server error occured.");
				sm_submit_btn_clicked = false;
			}
		});
	});
	
	function clear_errors() {
		$('.sm-error-div').fadeOut('slow');
	}
	
	var sm_preview_submit_btn_clicked = false;
	
	$(document).on('click', '#sm-preview-submit-btn', function() {
		
		if(sm_preview_submit_btn_clicked)
			return;
		
		sm_preview_submit_btn_clicked = true;
		
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
									+"welchen wir gerade an die von Ihnen angegeben E-Mail-Adresse gesendet haben. <br/>" 
									+"Bitte &uuml;berpr&uuml;fen Sie, falls Sie kein Mail erhalten haben, zus&auml;tzlich Ihren Junk-Mail Folder.");
							return;
						}
					}					
				}
				sm_preview_submit_btn_clicked = false;
				alert("Ups. Leider ist ein Fehler aufgetreten.");
			},
			error: function(resp) {
				sm_preview_submit_btn_clicked = false;
				alert("Sry - Server error occured.");
			}
		});
	});
	
	$(document).on('click', '#sm-preview-abort-btn', function() {
		
		//user clicked "Ich will noch etwas �ndern" btn, enable main submit form again
		sm_submit_btn_clicked = false;
		
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
	        maxFileSize: 2097152,
	        maxNumberOfFiles: 4,
	        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
	        url: SMInject['url']
	    });
	    
	    $('#sm-form-images').bind('fileuploaddone', function(e, data) {
	    	if(DEBUG_SM_JS) {
	    		console.log("file upload done event");
	    	}
	    	
	    	if(typeof data.result !== 'undefined' && $.isArray(data.result.files)) {
	    		var img_data = data.result.files[0];
	    		//if no error proceed
	    		if(typeof img_data !== 'undefined' && typeof img_data.error === 'undefined')  {
	    			
	    			if(DEBUG_SM_JS) {
	    				console.log("img_map length is: " + SMInject['img_map'].length + " ... push new file");
	    			}
	    			
    				var img_map_obj = new Object();
    				img_map_obj.id = $(this).attr('id');
    				img_map_obj.name = img_data.name;
    				img_map_obj.url = img_data.url;
    				img_map_obj.thumbnail_url = img_data.thumbnail_url;
    				SMInject['img_map'].push(img_map_obj);
    				
	    			if(DEBUG_SM_JS) {
	    				console.log("img_map length after pushing: " + SMInject['img_map'].length);
	    			}
	    			
	    		} else {
	    			if(DEBUG_SM_JS) {
	    				console.log("image upload error occured!");
	    			}
	    		}
	    	} else {
	    		alert(unescape("Ups. Da ist wohl etwas schief gegangen.... Dies h%E4tte nicht passieren d%FCfen"));
	    	}
	    });
	    
	    //Preview Mode Image poll
	    setInterval(function() { 
	    	
    		if($('#sm-preview-mode-container').length) { 			// if the preview div exists we poll
    	    	for(var i = 0; i < 4; i++) {
		    		if(SMInject['img_map'].length > i) {
			    		var the_markup = $('#sm-thumb-preview-'+i).html();
			    		var img_data = SMInject['img_map'][i];
			    		if(the_markup.indexOf(img_data.url) === -1) {
			    		
			    			if(DEBUG_SM_JS) {
			    				console.log("preview div - image at idx: " + i + " has changed, update .... ");
			    			}
			    			
			    			//file has changed, inject new markup
		    				var the_html = '<a href="'+img_data.url+'" target="_blank" title="Anzeigen Bild '+
							+ i +'">'
							+ '<img title="Anzeigen Bild '+ i +'" alt="Anzeige Bild ' + i + '" src="' + img_data.thumbnail_url + '" class="aligncenter size-medium wp-image-1210" >'
							+'</a>';
		    				$('#sm-thumb-preview-'+i).html(the_html);
			    		}
		    		} else {
		    			//clear html, currently no image to display in this container
		    			$('#sm-thumb-preview-'+i).html("");
		    		}	
    	    	}  	    	
    		} else {
    			if(DEBUG_SM_JS) {
    				console.log("Preview div currently not added to stage.");
    			}
    		}
	    }, 3000);
	    
	    $('#sm-form-images').bind('fileuploaddestroy', function(e, data){
	    	//default is not good for our needs because sm_submit_id and action 
	    	//is not sent in POST request ....
	    	var default_url = data.url;
	    	
	    	if(SMInject['img_map'].length > 0) {
	    		//go over img map and remove html
	    	    for (var i = 0; i < SMInject['img_map'].length; i++) {
	    	        var the_image_obj = SMInject['img_map'][i];
	    	        var encoded_file_name = escape(the_image_obj.name); //get it like it is in the default_url for comparison
	    			if(default_url.indexOf(encoded_file_name) !== -1) {

                        //remove the item from array map
	    			    if (DEBUG_SM_JS) {
	    			        console.log("Remove image from img_map array at index: " + i + " image name is: " + the_image_obj.name + " img_map lenght is: " + SMInject['img_map'].length);
	    			    }
	    			    SMInject['img_map'] = jQuery.grep(SMInject['img_map'], function (current_item) {
	    			        return the_image_obj != current_item;
	    			    });
	    			    if (DEBUG_SM_JS) {
	    			        if(i < SMInject['img_map'].length) {
	    			            console.log("New image item at that index is now the image: " + SMInject['img_map'][i].name + " new img_map length is: " + SMInject['img_map'].length);
	    			        }
	    			    }
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
	    		if(DEBUG_SM_JS) {
	    			console.log("progess all, show Ad Preview.");
	    		}
	    		showAdPreview();
	    	}
	    	//console.log("Data progress: "+progress+" loaded: "+data.loaded+" total: "+data.total);
	    });
	};
	
	function showAdPreview() {
		$('#sm-head-anchor').scrollTo(1000, function() {
			$('#sm-market-div').html("");
			
			$('#sm-preview-div').html(SMInject['preview']);
			
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