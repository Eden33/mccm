<?php

//http://net.tutsplus.com/tutorials/javascript-ajax/submit-a-form-without-page-refresh-using-jquery/

function get_the_form() {
	global $mysql_column_length;
	return 
	'<div class="css_form_embedded">

		<div class="css_form_errormessage" for=sm_first_name" id="first_name_error" style="display:none;">Vorname pr&uuml;fen</div>
		<div class="css_form_errormessage" for="sm_last_name" id="last_name_error" style="display:none;">Nachname pr&uuml;fen</div>			
		<div class="css_form_errormessage" for="sm_last_name" id="last_name_error" style="display:none;">Mail pr&uuml;fen</div>	
		<div class="css_form_errormessage" for="sm_last_name" id="last_name_error" style="display:none;">Telefonnummer pr&uuml;fen</div>	
		<div class="css_form_errormessage" for="sm_last_name" id="last_name_error" style="display:none;">Land pr&uuml;fen</div>	
		<div class="css_form_errormessage" for="sm_last_name" id="last_name_error" style="display:none;">Stadt pr&uuml;fen</div>
		<div class="css_form_errormessage" for="sm_last_name" id="last_name_error" style="display:none;">Postleitzahl pr&uuml;fen</div>		
		<div class="css_form_errormessage" for="sm_last_name" id="last_name_error" style="display:none;">Anzeige Text pr&uuml;fen</div>		
			
		<a name="simplemarketform"></a>
		<form method="post" action="'.$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"].'">
			<div class="css_form_textfieldspace">
				<input class="css_form_namefield" type="text" maxlength="'.$mysql_column_length['first_name'].'" 
						value="" name="sm_first_name" />
				Vorname *
			</div>
			<div class="css_form_textfieldspace">
				<input class="css_form_namefield" type="text" maxlength="'.$mysql_column_length['last_name'].'" 
						value="" name="sm_last_name" />
				Nachname *
			</div>
			<div class="css_form_textfieldspace">
				<input class="css_form_namefield" type="text" maxlength="'.$mysql_column_length['mail'].'" 
						value="" name="sm_mail" />
				E-Mail *
			</div>						
			<div class="css_form_textfieldspace">
				<input class="css_form_namefield" type="text" maxlength="'.$mysql_column_length['phone'].'" 
						value="" name="sm_phone" />
				Telefon
			</div>
			<div class="css_form_textfieldspace">
				<input class="css_form_namefield" type="text" maxlength="'.$mysql_column_length['country'].'" 
						value="" name="sm_country" />
				Land *
			</div>
			<div class="css_form_textfieldspace">
				<input class="css_form_namefield" type="text" maxlength="'.$mysql_column_length['city'].'" 
						value="" name="sm_city" />
				Stadt *
			</div>							
			<div class="css_form_textfieldspace">
				<input class="css_form_namefield" type="text" maxlength="'.$mysql_column_length['zip_code'].'" 
						value="" name="sm_zip_code" />
				Postleitzahl *
			</div>
			<div class="css_form_textfieldspace">
				<textarea class="css_form_messagefield" cols="0" rows="0" name="sm_text"></textarea>
				Ihr Anzeigen Text *
			</div>
			<div style="text-align: left;">
				* Eingabe erforderlich
			</div>
			
			<!-- Google Captcha -->
						
			<div class="css_form_submit_position">
				<input class="css_form_submit" type="submit" value="Anzeigen Vorschau" />
			</div>
			
		</form>		
	</div>';
}

$the_market .= get_the_form();
?>