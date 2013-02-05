<?php

//http://net.tutsplus.com/tutorials/javascript-ajax/submit-a-form-without-page-refresh-using-jquery/

function get_the_form() {
	global $mysql_column_length;
	return 
	'	<div id="sm-preview-div"></div>
		<div id="sm-market-div"> Market Data </div>
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
				<input class="sm-form-input" type="text" maxlength="'.$mysql_column_length['first_name'].'" 
						value="Eduard" name="sm_first_name" />
				Vorname *
			</div>
			<div class="sm-form-div">
				<input class="sm-form-input" type="text" maxlength="'.$mysql_column_length['last_name'].'" 
						value="Gopp" name="sm_last_name" />
				Nachname *
			</div>
			<div class="sm-form-div">
				<input class="sm-form-input" type="text" maxlength="'.$mysql_column_length['mail'].'" 
						value="eduard.gopp@gmail.com" name="sm_mail" />
				E-Mail *
			</div>						
			<div class="sm-form-div">
				<input class="sm-form-input" type="text" maxlength="'.$mysql_column_length['phone'].'" 
						value="004369911223949" name="sm_phone" />
				Telefon
			</div>
			<div class="sm-form-div">
				<input class="sm-form-input" type="text" maxlength="'.$mysql_column_length['country'].'" 
						value="Austria" name="sm_country" />
				Land *
			</div>
			<div class="sm-form-div">
				<input class="sm-form-input" type="text" maxlength="'.$mysql_column_length['city'].'" 
						value="Feldkirch" name="sm_city" />
				Stadt *
			</div>							
			<div class="sm-form-div">
				<input class="sm-form-input" type="text" maxlength="'.$mysql_column_length['zip_code'].'" 
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
			<script type="text/javascript"
		    	src="http://www.google.com/recaptcha/api/challenge?k=6LdPfdwSAAAAAMsR2AWzAq9Bdidde6V1MD77xB2j">
		  	</script>
		  	<noscript>
		     	<iframe src="http://www.google.com/recaptcha/api/noscript?k=your_public_key"
		         	height="300" width="500" frameborder="0"></iframe><br>
		     	<textarea name="recaptcha_challenge_field" rows="3" cols="40">
		     	</textarea>
		    <input type="hidden" name="recaptcha_response_field" value="manual_challenge">
		  	</noscript>			
			
			<div class="sm-form-submit-div">
				<input class="sm-form-submit" type="submit" id="sm-submit-btn" value="Anzeigen Vorschau" />
			</div>
			<input type="hidden" name="sm_submit_id" value="'.$_SESSION['sm_submit_id'].'"> 
		</form>
	</div>';
}

$the_market = get_the_form();
?>