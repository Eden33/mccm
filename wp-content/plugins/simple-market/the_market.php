<?php

function get_the_ads_paging_bar($ads_per_page, $total_ads_count, $requested_page) {
		
	$pages = ceil($total_ads_count / $ads_per_page);
	$market_permalink = get_market_permalink();	
	
	$the_bar = '<div class="sm-paging-div" style="text-align: center;">';
	for($i = 1; $i <= $pages; $i++) {
		$anchor_class = 'sm-paging-a';
		if($i == $requested_page) {
			$anchor_class .= ' sm-paging-a-selected';
		}
		$the_bar .= '<a class="'.$anchor_class.'" href="'.$market_permalink.'?market_page='.$i.'">'.$i.'</a>';
	}
	$the_bar .= '</div><br/>';
	
	return $the_bar;
}

/**
 * @return string Current market page -> only the ads
 */
function get_the_ads() {
	global $sm_options;
	global $wpdb;
	global $sm_table_name;
	$ads_per_page = 10;
	
	$the_ads_markup = "";
	
	if(!isset($sm_options))
		$sm_options = get_site_option('simple_market');
	
	$ad_max_active = $sm_options['ad_max_active_in_days'];
	$past_day_barrierer = date('Y-m-d H:i:s', strtotime("-$ad_max_active days"));
		
	$count = $wpdb->get_var("SELECT COUNT(*) FROM $sm_table_name WHERE keep_alive_date_time >= '$past_day_barrierer' 
			and mail_approve = 1 and webmaster_approve = 1");

	if(is_null($count)) {
		$count = 1; 
	}
	
	$requested_page = intval($_GET['market_page']);
	if($requested_page == 0) {
		$requested_page = 1;
	}
	
	$start = ($requested_page - 1) * $ads_per_page;
	
 	$the_ads = $wpdb->get_results(
 			"SELECT * FROM $sm_table_name WHERE keep_alive_date_time >= '$past_day_barrierer' 
 						 and mail_approve = 1 and webmaster_approve = 1 ORDER BY keep_alive_date_time DESC LIMIT $ads_per_page OFFSET $start", ARRAY_A
 			);

 	$paging_bar = get_the_ads_paging_bar($ads_per_page, $count, $requested_page);
 	$the_ads_markup .= $paging_bar;
 	
 	//used for debugging on client
 	//$the_ads_markup .= "<div>Adds per page: $ads_per_page All active ads count: $count Start: $start</div>";
 	
 	if($the_ads) {
 		$renderer = new MarketItemRenderer();
 		foreach ($the_ads as $ad_data) {
 			$sm_item = new SimpleMarketItem($ad_data);
  			$renderer->set_market_item($sm_item);
 			$the_ads_markup .= $renderer->get_markup();
 		}
 		$the_ads_markup .= $renderer->get_contact_details_retrieval_javascript();
 	}
 	
 	$the_ads_markup .= $paging_bar;
 	
 	return $the_ads_markup;
}

//http://net.tutsplus.com/tutorials/javascript-ajax/submit-a-form-without-page-refresh-using-jquery/

/**
 * @return Current market page (with ads) and the form at the bottom of the page.
 */
function sm_get_the_market_page() {
	global $sm_mysql_column_length;
	global $sm_options;
	
	$returnVal =
	'	<div id="sm-preview-div"></div>
		<div id="sm-market-div">
		';
	$returnVal .= get_the_ads();
	$returnVal .= '</div>
		<div id="sm-first-from-div" class="sm-top-div" style="padding:0px 10px;">
		
		<a id="sm-form-a"></a>
			
		<div class="sm-error-div" id="first_name_error" style="display:none;">Vorname pr&uuml;fen</div>
		<div class="sm-error-div" id="last_name_error" style="display:none;">Nachname pr&uuml;fen</div>			
		<div class="sm-error-div" id="mail_error" style="display:none;">Mail pr&uuml;fen</div>	
		<div class="sm-error-div" id="phone_error" style="display:none;">Telefonnummer pr&uuml;fen</div>	
		<div class="sm-error-div" id="country_error" style="display:none;">Land pr&uuml;fen</div>	
		<div class="sm-error-div" id="city_error" style="display:none;">Stadt pr&uuml;fen</div>
		<div class="sm-error-div" id="zip_code_error" style="display:none;">Postleitzahl pr&uuml;fen</div>		
		<div class="sm-error-div" id="text_error" style="display:none;">Anzeige Text pr&uuml;fen</div>		
		<div class="sm-error-div" id="captcha_error" style="display:none;">Eingegebens Captcha war falsch. Bitte erneut versuchen.</div>
						
		<form method="post" id="sm-form" onsubmit="return false;">
			<div class="sm-form-div">
				<input class="sm-form-input" type="text" maxlength="'.$sm_mysql_column_length['first_name'].'" 
						value="'.$_POST['sm_first_name'].'" name="sm_first_name" />
				Vorname *
			</div>
			<div class="sm-form-div">
				<input class="sm-form-input" type="text" maxlength="'.$sm_mysql_column_length['last_name'].'" 
						value="'.$_POST['sm_last_name'].'" name="sm_last_name" />
				Nachname *
			</div>
			<div class="sm-form-div">
				<input class="sm-form-input" type="text" maxlength="'.$sm_mysql_column_length['mail'].'" 
						value="'.$_POST['sm_mail'].'" name="sm_mail" />
				E-Mail *
			</div>						
			<div class="sm-form-div">
				<input class="sm-form-input" type="text" maxlength="'.$sm_mysql_column_length['phone'].'" 
						value="'.$_POST['sm_phone'].'" name="sm_phone" />
				Telefon
			</div>
			<div class="sm-form-div">
				<input class="sm-form-input" type="text" maxlength="'.$sm_mysql_column_length['country'].'" 
						value="'.$_POST['sm_country'].'" name="sm_country" />
				Land *
			</div>
			<div class="sm-form-div">
				<input class="sm-form-input" type="text" maxlength="'.$sm_mysql_column_length['city'].'" 
						value="'.$_POST['sm_city'].'" name="sm_city" />
				Stadt *
			</div>							
			<div class="sm-form-div">
				<input class="sm-form-input" type="text" maxlength="'.$sm_mysql_column_length['zip_code'].'" 
						value="'.$_POST['sm_zip_code'].'" name="sm_zip_code" />
				Postleitzahl *
			</div>
			<div class="sm-form-div" style="margin-bottom: 5px;">
				<textarea class="sm-form-textarea" name="sm_text">'.$_POST['sm_text'].'</textarea>
				<span style="white-space:nowrap">Ihr Anzeigen Text *</span>
			</div>
			<div style="text-align: left; margin-top:15px;">
				Bitte alle Felder die mit einem * markiert sind ausf&uuml;llen
			</div>	
						
			<div id="sm_recaptcha_inject_div"></div>
			<script type="text/javascript">
				var showSimpleMarketFormRecaptcha;
				var destroySimpleMarketFormRecaptcha;
						
				jQuery(function ($) {
			  		 showSimpleMarketFormRecaptcha = function() {
						$(SMInject[\'responsive_recaptcha_widget\']).insertAfter(\'#sm_recaptcha_inject_div\');
			        	Recaptcha.create("6LdPfdwSAAAAAMsR2AWzAq9Bdidde6V1MD77xB2j", "responsive_recaptcha_widget", {
			            theme: "custom"
			            });						
			         }
					 destroySimpleMarketFormRecaptcha = function() {
						Recaptcha.destroy();
						$(\'#responsive_recaptcha_widget\').remove();
					 }
				});
			</script>

			<div style="margin-top:10px;">
				<input id="sm_terms_checkbox" type="checkbox" style="margin:0px;"></input><span style="margin-left:5px;">Ich habe die <a href="'. get_permalink( $sm_options['terms_post_id'] ).'" target="_blank">Nutzungssbedingungen des MCCM Online Marktes</a> gelesen und akzeptiere diese.</span>		
			</div>
						
			<div class="sm-form-submit-div">
				<input class="sm-form-submit" type="submit" id="sm-submit-btn" value="Anzeigen Vorschau" />
			</div>
			<input type="hidden" name="sm_submit_id" value="" />
		</form>
						
		<!-- the images form -->
		<form id="sm-form-images" method="POST" enctype="multipart/form-data"
						action="http://www.mccm-feldkirch.at/wp-admin/admin-ajax.php" style="width: 100%;">
						
			<!-- take care - it is also set in form.js for image delete requests -->
						
						
			<input type="hidden" name="action" value="sm_submit_form_images" />
			<input type="hidden" name="sm_submit_id" value="" />
			<div class="row fileupload-buttonbar">
						
	            <div class="span7" style="left: 0px; width: 100%">
	                <!-- The fileinput-button span is used to style the file input field as button -->
	                <span class="btn btn-success fileinput-button">
	                    <i class="icon-plus icon-white"></i>
	                    <span>Bilder f&uuml;r Inserat ausw&auml;hlen: </span>
	                    <input type="file" name="files[]" multiple>
	                </span>
					<button id="sm-form-images-submit-btn" style="display:none;" 
								type="submit" class="btn btn-primary start">
                    	<i class="icon-upload icon-white"></i>
                    
                </button>
	            </div>
	            <!-- The global progress information -->
	            <div class="span5 fileupload-progress fade" style="width: 90%; left: 0px;">
	                <!-- The global progress bar -->
	                <div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
	                    <div class="bar" style="width:0%;"></div>
	                </div>
	                <!-- The extended global progress information -->
	                <div class="progress-extended">&nbsp;</div>
	            </div>
						
	        </div>
	        <!-- The loading indicator is shown during file processing -->
	        <div class="fileupload-loading"></div>
			<br/>
			<!-- The table listing the files available for upload/download -->
        	<table role="presentation" class="table table-striped"><tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery"></tbody></table>
        </form>
	</div>
						
<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td class="preview"><span class="fade"></span></td>
        <td class="name"><span>{%=file.name%}</span></td>
        <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
        {% if (file.error) { %}
            <td class="error" colspan="2"><span class="label label-important">Error</span> {%=file.error%}</td>
        {% } else if (o.files.valid && !i) { %}
            <td>
                <div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar" style="width:0%;"></div></div>
            </td>
						

						
			<!-- TODO only for testing ... display none -->
			
            <td class="start">{% if (!o.options.autoUpload) { %}
                <button class="btn btn-primary" style="display:none;">
                    <i class="icon-upload icon-white"></i>
       
                </button>
            {% } %}</td>
        
        
        
        {% } else { %}
            <td colspan="2"></td>
        {% } %}
        <td class="cancel">{% if (!i) { %}
            <button class="btn btn-warning">
                <i class="icon-ban-circle icon-white"></i>
                <span>Remove</span>
            </button>
        {% } %}</td>
    </tr>
{% } %}
</script>
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download">
        {% if (file.error) { %}
            <td></td>
            <td class="name"><span>{%=file.name%}</span></td>
            <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td class="error" colspan="2"><span class="label label-important">Error</span> {%=file.error%}</td>
        {% } else { %}
            <td class="preview">{% if (file.thumbnail_url) { %}
                <a href="{%=file.url%}" target="_blank" title="{%=file.name%}" data-gallery="gallery" download="{%=file.name%}"><img src="{%=file.thumbnail_url%}"></a>
            {% } %}</td>
            <td class="name">
                <a href="{%=file.url%}" target="_blank" title="{%=file.name%}" data-gallery="{%=file.thumbnail_url&&\'gallery\'%}" download="{%=file.name%}">{%=file.name%}</a>
            </td>
            <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td colspan="2"></td>
        {% } %}
        <td class="delete">
            <button class="btn btn-danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}"{% if (file.delete_with_credentials) { %} data-xhr-fields=\'{"withCredentials":true}\'{% } %}>
                <i class="icon-trash icon-white"></i>
                <span>Remove</span>
            </button>
        </td>
    </tr>
{% } %}
</script>					
';
return $returnVal;
}

function sm_get_admin_preview() {
	global $admin_key_received;
	$the_markup = '';
	
	if(sm_check_key($admin_key_received) === true) {
		global $wpdb;
		global $sm_table_name;
		
		$prepared_stmt = $wpdb->prepare("SELECT * FROM $sm_table_name WHERE webmaster_approval_key = %s", $admin_key_received);
		$row = $wpdb->get_row($prepared_stmt, ARRAY_A);
		
		if($row) {
			$sm_item = new SimpleMarketItem($row);
			$renderer = new MarketItemRenderer();
			$renderer->set_market_item($sm_item);
			$the_markup = $renderer->get_markup();
			$the_markup .= $renderer->get_contact_details_retrieval_javascript();			
		} else {
			$the_markup = get_sm_mail_ups_message();
		}
		
	} else {
		$the_markup = get_sm_mail_ups_message();
	}
	return $the_markup;
}
?>