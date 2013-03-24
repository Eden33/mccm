<?php

//$mysql_date_time_now_gmt = current_time('mysql', 1);
//$mysql_date_time_now_gmt . " and ".date('Y-m-d H:i:s', strtotime("-$ad_max_active days"));

function get_the_ads() {
	global $sm_options;
	global $wpdb;
	global $sm_table_name;
	
	$the_ads_markup = "";
	
	if(!isset($sm_options))
		$sm_options = get_site_option('simple_market');
	
	$ad_max_active = $sm_options['ad_max_active_in_days'];
	$past_day_barrierer = date('Y-m-d H:i:s', strtotime("-$ad_max_active days"));
	
 	$the_ads = $wpdb->get_results(
 			"SELECT * FROM $sm_table_name WHERE keep_alive_date_time >= '$past_day_barrierer' and webmaster_approve = 1", ARRAY_A
 			);
 	if($the_ads) {
 		$renderer = new MarketItemRenderer();
 		foreach ($the_ads as $ad_data) {
 			$sm_item = new SimpleMarketItem($ad_data);
  			$renderer->set_market_item($sm_item);
 			$the_ads_markup .= $renderer->get_markup();
 		}
 		$the_ads_markup .= $renderer->get_contact_details_retrieval_javascript();
 	}
 	return $the_ads_markup;
}

//http://net.tutsplus.com/tutorials/javascript-ajax/submit-a-form-without-page-refresh-using-jquery/

function get_the_form() {
	global $sm_mysql_column_length;
	
	return 
	'	<div id="sm-preview-div"></div>
		<div id="sm-market-div"> '.get_the_ads().'</div>
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
						value="Eduard" name="sm_first_name" />
				Vorname *
			</div>
			<div class="sm-form-div">
				<input class="sm-form-input" type="text" maxlength="'.$sm_mysql_column_length['last_name'].'" 
						value="Gopp" name="sm_last_name" />
				Nachname *
			</div>
			<div class="sm-form-div">
				<input class="sm-form-input" type="text" maxlength="'.$sm_mysql_column_length['mail'].'" 
						value="e.gopp@gmail.com" name="sm_mail" />
				E-Mail *
			</div>						
			<div class="sm-form-div">
				<input class="sm-form-input" type="text" maxlength="'.$sm_mysql_column_length['phone'].'" 
						value="004369911223949" name="sm_phone" />
				Telefon
			</div>
			<div class="sm-form-div">
				<input class="sm-form-input" type="text" maxlength="'.$sm_mysql_column_length['country'].'" 
						value="Austria" name="sm_country" />
				Land *
			</div>
			<div class="sm-form-div">
				<input class="sm-form-input" type="text" maxlength="'.$sm_mysql_column_length['city'].'" 
						value="Feldkirch" name="sm_city" />
				Stadt *
			</div>							
			<div class="sm-form-div">
				<input class="sm-form-input" type="text" maxlength="'.$sm_mysql_column_length['zip_code'].'" 
						value="6800" name="sm_zip_code" />
				Postleitzahl *
			</div>
			<div class="sm-form-div">
				<textarea class="sm-form-textarea" name="sm_text">Ich verkaufe mein Fahrrad, da ich es nicht mehr brauche.
						
				Preis Verhandlungssache.
						
				Bitte erst ab 18 Uhr anrufen.</textarea>
				Ihr Anzeigen Text *
			</div>
			<div style="text-align: left;">
				* Eingabe erforderlich
			</div>			
			
			<!-- Google Captcha -->
			<script type="text/javascript" src="http://www.google.com/recaptcha/api/js/recaptcha_ajax.js"></script>
			<div id="captchadiv"></div>
			<script type="text/javascript">
				//<![CDATA[
			  		function showRecaptcha(element) {
			        	Recaptcha.create("6LdPfdwSAAAAAMsR2AWzAq9Bdidde6V1MD77xB2j", "captchadiv", {
			            theme: "red"
			            });
			         }
				//]]>
			</script>
			<!-- Google Catpcha END -->
						
			<div class="sm-form-submit-div">
				<input class="sm-form-submit" type="submit" id="sm-submit-btn" value="Anzeigen Vorschau" />
			</div>
			<input type="hidden" name="sm_submit_id" value="" />
		</form>
						
		<!-- the images form -->
		<form id="sm-form-images" method="POST" enctype="multipart/form-data"
						action="http://www.mccm-feldkirch.at/wp-admin/admin-ajax.php">
						
			<!-- take care - it is also set in form.js for image delete requests -->
						
						
			<input type="hidden" name="action" value="sm_submit_form_images" />
			<input type="hidden" name="sm_submit_id" value="" />
			<div class="row fileupload-buttonbar">
	            <div class="span7">
	                <!-- The fileinput-button span is used to style the file input field as button -->
	                <span class="btn btn-success fileinput-button">
	                    <i class="icon-plus icon-white"></i>
	                    <span>Bilder f&uuml;r Inserat ausw&auml;hlen: </span>
	                    <input type="file" name="files[]" multiple>
	                </span>
					<button id="sm-form-images-submit-btn" style="visiblity:hidden;" 
								type="submit" class="btn btn-primary start">
                    	<i class="icon-upload icon-white"></i>
                    <span>Start upload</span>
                </button>
	            </div>
	            <!-- The global progress information -->
	            <div class="span5 fileupload-progress fade">
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
						

						
			<!-- TODO only for testing ... remove afterwards -->
			
            <td class="start">{% if (!o.options.autoUpload) { %}
                <button class="btn btn-primary">
                    <i class="icon-upload icon-white"></i>
                    <span>Start</span>
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
                <a href="{%=file.url%}" title="{%=file.name%}" data-gallery="gallery" download="{%=file.name%}"><img src="{%=file.thumbnail_url%}"></a>
            {% } %}</td>
            <td class="name">
                <a href="{%=file.url%}" title="{%=file.name%}" data-gallery="{%=file.thumbnail_url&&\'gallery\'%}" download="{%=file.name%}">{%=file.name%}</a>
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
}

$the_market = get_the_form();
?>