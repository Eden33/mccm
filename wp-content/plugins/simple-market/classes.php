<?php
class SimpleMarketItem {
	private $id;
	private $first_name;
	private $last_name;
	private $mail;
	private $phone;
	private $zip_code;
	private $city;
	private $country;
	private $ip;
	private $submit_date_time;
	private $keep_alive_date_time;
	private $text;
	private $image_uuid;
	private $mail_approve;
	private $mail_approval_key;
	private $webmaster_approve;
	private $webmaster_approval_key;

		
	function __construct($assoc_array, $first_name = NULL, $last_name=NULL, $mail=NULL, $phone=NULL, $zip_code=NULL, 
							$city=NULL, $country=NULL, $text=NULL, $submit_date_time=NULL, $image_uuid=NULL) {
		if(isset($assoc_array) === true && is_array($assoc_array)) {
			foreach ($assoc_array as $key => $value) {
				switch ($key) {
					case 'id'					:	$this->id = $value; break;
					case 'first_name'			:	$this->first_name = $value; break;
					case 'last_name'			: 	$this->last_name = $value; break;
					case 'mail'					: 	$this->mail = $value; break;
					case 'phone'				: 	$this->phone = $value; break;
					case 'zip_code'				: 	$this->zip_code = $value; break;
					case 'city'					: 	$this->city = $value; break;
					case 'country'				: 	$this->country = $value; break;
					case 'ip'					: 	$this->ip = $value; break;
					case 'submit_date_time'		: 	$this->submit_date_time = $value; break;
					case 'keep_alive_date_time'	: 	$this->keep_alive_date_time = $value; break;
					case 'text'					: 	$this->text = $value; break;
					case 'image_uuid'			: 	$this->image_uuid = $value; break;
					case 'mail_approve'			: 	$this->mail_approve = $value; break;
					case 'mail_approval_key'	: 	$this->mail_approval_key = $value; break;
					case 'webmaster_approve'	: 	$this->webmaster_approve = $value; break;
					case 'webmaster_approval_key':	$this->webmaster_approval_key = $value; break;
					default						: 	throw new Exception("Column: $key not knwon!");
				}
			}
		}
	}
	function get_id() {
		return $this->id;
	}
	function get_first_name() {
		return $this->first_name;
	}
	function get_first_name_html_encoded() {
		$encoded = htmlentities($this->first_name, ENT_NOQUOTES, "UTF-8");
		return $encoded;
	}
	function get_last_name() {
		return $this->last_name;
	}
	function get_last_name_html_encoded() {
		$encoded = htmlentities($this->last_name, ENT_NOQUOTES, "UTF-8");
		return $encoded;
	}
	function get_mail() {
		return $this->mail;
	}
	function get_mail_html_encoded() {
		$encoded = htmlentities($this->mail, ENT_NOQUOTES, "UTF-8");
		return $encoded;
	}
	function get_phone() {
		return $this->phone;	
	}
	function get_phone_html_encoded() {
		$encoded = htmlentities($this->phone, ENT_NOQUOTES, "UTF-8");
		return $encoded;
	}
	function get_zip_code() {
		return $this->zip_code;
	}
	function get_zip_code_html_encoded() {
		$encoded = htmlentities($this->zip_code, ENT_NOQUOTES, "UTF-8");
		return $encoded;
	}
	function get_city() {
		return $this->city;
	}
	function get_city_html_encoded() {
		$encoded = htmlentities($this->city, ENT_NOQUOTES, "UTF-8");
		return $encoded;
	}
	function get_country() {
		return $this->country;
	}
	function get_country_html_encoded() {
		$encoded = htmlentities($this->country, ENT_NOQUOTES, "UTF-8");
		return $encoded;
	}
	function get_text() {
		return $this->text;	
	}
	function get_webmaster_approval_key() {
		return $this->webmaster_approval_key;
	}
	function get_text_html_encoded() {
		$encoded = htmlentities($this->text, ENT_NOQUOTES, "UTF-8");
		$encoded = str_replace(array("\r\n", "\r", "\n"), "<br/>", $encoded);
		return $encoded;
	}
	function get_submit_date_time() {
 		return $this->submit_date_time;
	}
	function get_keep_alive_date_time() {
		return $this->keep_alive_date_time;	
	}
	/**
	 * The tag images are marked with during submission process.
	 * Alias used in forms/posts is 'sm_submit_id'
	 */
	function get_image_uuid() {
		return $this->image_uuid;
	}
	function get_image_folder_name() {
		$dt = $this->get_submit_date_time();
		if(!isset($dt))
			throw new Exception("submit date not set in get_image_folder getter");
		
		$tmp = explode(" ", $this->get_submit_date_time());
		if(!is_array($tmp) || !isset($tmp[0]) || count($tmp) != 2)
			throw new Exception("get_image_folder wrong date format: ".$this->get_submit_date_time());

		return $tmp[0];
	}
	function is_approved_by_mail() {
		if(isset($this->mail_approve) && $this->mail_approve == 1) {
			return true;
		}
		return false;
	}
	function is_approved_by_webmaster() {
		if(isset($this->webmaster_approve) && $this->webmaster_approve == 1) {
			return true;
		}
		return false;
	}
}

class SimpleMarketFormResponse {
	//errors
	private $captcha_error = false;	
	private $first_name_error = false;
	private $last_name_error = false;
	private $mail_error = false;
	private $phone_error = false;
	private $zip_code_error = false;
	private $city_error = false;
	private $country_error = false;
	private $text_error = false;
	
	private $market_item_renderer;
	private $market_item;
	
	private $resp = array();
		
	//general setters
	public function set_market_item_renderer($market_item_renderer) {
		$this->market_item_renderer = $market_item_renderer;
	}
	public function set_market_item($market_item) {
		$this->market_item = $market_item;
	}
	
	//error setters
	public function set_captcha_error($bool_val) {
		$this->captcha_error = $bool_val;
	}
	public function set_first_name_error($bool_val) {
		$this->first_name_error = $bool_val;
	}
	public function set_last_name_error($bool_val) {
		$this->last_name_error = $bool_val;
	}
	public function set_mail_error($bool_val) {
		$this->mail_error = $bool_val;
	}
	public function set_phone_error($bool_val) {
		$this->phone_error = $bool_val;
	}
	public function set_zip_code_error($bool_val) {
		$this->zip_code_error = $bool_val;
	}
	public function set_city_error($bool_val) {
		$this->city_error = $bool_val;
	}
	public function set_country_error($bool_val) {
		$this->country_error = $bool_val;
	}
	public function set_text_error($bool_val) {
		$this->text_error = $bool_val;
	}

	
	function get_json_response() {
		
		header("Content-Type: application/json");
		
		if($this->captcha_error) {
			$this->add_errors_array_if_not_exist();
			array_push($this->resp['errors'], 'captcha_error');
		}
		if($this->first_name_error) {
			$this->add_errors_array_if_not_exist();
			array_push($this->resp['errors'], 'first_name_error');
		}
		if($this->last_name_error) {
			$this->add_errors_array_if_not_exist();
			array_push($this->resp['errors'], 'last_name_error');
		}
		if($this->mail_error) {
			$this->add_errors_array_if_not_exist();
			array_push($this->resp['errors'], 'mail_error');			
		}
		if($this->phone_error)  {
			$this->add_errors_array_if_not_exist();
			array_push($this->resp['errors'], 'phone_error');			
		}
		if($this->country_error) {
			$this->add_errors_array_if_not_exist();
			array_push($this->resp['errors'], 'country_error');
		}
		if($this->city_error) {
			$this->add_errors_array_if_not_exist();
			array_push($this->resp['errors'], 'city_error');
		}
		if($this->zip_code_error) {
			$this->add_errors_array_if_not_exist();
			array_push($this->resp['errors'], 'zip_code_error');
		}
		if($this->text_error) {
			$this->add_errors_array_if_not_exist();
			array_push($this->resp['errors'], 'text_error');
		}
		
		
		if(isset($this->resp['errors'])) {
			$this->resp['success'] = false;
		} else {
			$this->resp['success'] = true;			//all data user passed to the script are OK

			$_SESSION['market_item_to_submit'] = null;
			unset($_SESSION['market_item_to_submit']);
			$_SESSION['market_item_to_submit'] = $this->market_item; //store data in SESSION
			$this->resp['submit_id'] = $this->market_item->get_image_uuid();
			
			$this->add_preview();
		}
		
		return json_encode($this->resp);	//return preview of the ad to the user
	}
	
	private function add_errors_array_if_not_exist() {
		if(!isset($this->resp['errors']))
			$this->resp['errors'] = array();
	}
	
	private function add_preview() {
		$this->resp['preview'] = $this->market_item_renderer->get_markup();
	}
}
class MarketItemRenderer {
	protected $market_item = null;
	
	function __construct(&$market_item = NULL) {
		$this->market_item = $market_item;
	}
	
	public function set_market_item(SimpleMarketItem $market_item) {
		$this->market_item = $market_item;
	}
	
	public function get_markup() {
		if(!isset($this->market_item))
			return '';

		$options = array('get_image_urls_of_passed_sm_item' => true, 'check_for_malicious' => true);
		$the_images = perform_action_on_uploaded_images($this->market_item, $options);
		
		$markup = 
			'<div class="sm-top-div">
				 <table>
					<tbody>
						<tr><td>'.$this->market_item->get_first_name_html_encoded() .' '
							 .$this->market_item->get_last_name_html_encoded().'</td></tr>
						<tr><td>'.$this->market_item->get_zip_code_html_encoded().', '
							 .$this->market_item->get_city_html_encoded().', '
							 .$this->market_item->get_country_html_encoded().'</td></tr>
						<tr><td>Anzeige vom: '.$this->get_formated_date_time().'</td></tr>					
					</tbody>
				</table>
				<div>'.$this->market_item->get_text_html_encoded().'</div>
				<div id="sm-thumb-preview-container" class="clearfix">';
		for($i = 0; $i < count($the_images); $i++) {
			$markup .= '<div class="sm-thumb-preview" id="sm-thumb-preview-'.$i.'">
				<a href="'.$the_images[$i]['url'].'" rel="lightbox[simple-market-item-'.$this->market_item->get_id().']">
					<img class="aligncenter size-medium wp-image-1210"
						src="'.$the_images[$i]['thumb'].'"
						alt="">
				</a>
			</div>';
		}				
				
		$markup .=	'</div>
				<div>
					<a href="#" onClick="getContactDetails('.$this->market_item->get_id().')">Kontaktinformationen</a>
				</div>
			</div>
			<br><br>';
		
		return utf8_encode($markup);
	}
	
	public function get_contact_details_retrieval_javascript() {
		if(!isset($this->market_item))
			return '';
		

		return '<script type="text/javascript">
					function getContactDetails(id) {
						jQuery(function ($) {
		
							//first remove recaptcha from market main page bevore create new one within colorbox
							destroySimpleMarketFormRecaptcha();
							
							var colorboxResizePoll;
				
							$.colorbox({
								html:
									"<div id=\"contact-details-div\"><div>Nach dieser kurzen Authentifizierung bekommst <br/> die die Kontaktdaten zu diesem Inserat</div>"
									+"<div class=\"sm-error-div\" id=\"contact-form-captcha_error\" style=\"display:none;\"><br/>Eingegebens Captcha war falsch. Bitte erneut versuchen.</div>"
									+"<form id=\"contact-details-form\"method=\"POST\" onsubmit=\"return false;\">"
									+"<input type=\"hidden\" name=\"action\" value=\"sm_get_contact\" />"
									+"<input type=\"hidden\" name=\"contact_id\" value=\""+id+"\" />"
									+"<br/>"+ SMInject[\'responsive_recaptcha_widget\'] +"<br/>"
									+"<input id=\"get-contact-submit\" name=\"submit\" type=\"submit\" value=\"Anfrage Kontaktdaten\" /></form></div>"
								,onComplete:function() {
									
									Recaptcha.create("6LdPfdwSAAAAAMsR2AWzAq9Bdidde6V1MD77xB2j", "responsive_recaptcha_widget", {theme: "custom"});
									colorboxResizePoll = setInterval(function() { 
											$.colorbox.resize();
											if(DEBUG_SM_JS) {
									    		console.log("Contact colorbox resize poll occured.");
									    	} 
									}, 1000);
									
									$("#get-contact-submit").click(function(resp) {
				
										$(\'.sm-error-div\').fadeOut(\'slow\');
				
										var form_data = $("#contact-details-form").serializeArray();
										$.ajax({
											type: "POST",
											url: "'.admin_url( 'admin-ajax.php' ).'",
											data: form_data
											,success: function(resp) {
												if(typeof resp !== \'undefined\') {
													if(typeof resp[\'errors\'] !==  \'undefined\') {
														var error = \'\';
														for(var i = 0; i < resp[\'errors\'].length; i++) {
															error = \'#contact-form-\' + resp[\'errors\'][i];
															$(error).fadeIn(\'slow\');
														}
														Recaptcha.reload();
													} else {
														$(\'#contact-details-div\').html(resp[\'markup\']);
													}
													$.colorbox.resize();
												}
											}
										});
									});
								}
								,onClosed:function() {
								 	clearInterval(colorboxResizePoll);
									showSimpleMarketFormRecaptcha(); //create again captcha on market main page
								}
							});
						});
					  }
				</script>';
	}
	
	protected function get_formated_date_time() {
		$date_time = $this->market_item->get_keep_alive_date_time();
		$date_time_blog_time_zone = get_date_from_gmt($date_time);
		return $date_time_blog_time_zone;
	}
}
class PreviewMarketItemRenderer extends MarketItemRenderer {
	
	public function get_markup() {
		if(!isset($this->market_item))
			return '';
	
		$markup =
		'<div class="sm-top-div" id="sm-preview-mode-container">
				 <table>
					<tbody>
						<tr><td>'.$this->market_item->get_first_name_html_encoded() .' '
								 .$this->market_item->get_last_name_html_encoded().'</td></tr>
						<tr><td>'.$this->market_item->get_zip_code_html_encoded().', '
								 .$this->market_item->get_city_html_encoded().', '
								 		.$this->market_item->get_country_html_encoded().'</td></tr>
						<tr><td>Anzeige vom: '.$this->get_formated_date_time().'</td></tr>
					</tbody>
				</table>
				<div>'.$this->market_item->get_text_html_encoded().'</div>
				<div id="sm-thumb-preview-container" class="clearfix">
					<div class="sm-thumb-preview" id="sm-thumb-preview-0">
						
					</div>
					<div class="sm-thumb-preview" id="sm-thumb-preview-1">
						
					</div>
					<div class="sm-thumb-preview" id="sm-thumb-preview-2">
						
					</div>
					<div class="sm-thumb-preview" id="sm-thumb-preview-3">
						
					</div>
				</div>
				<div>
					<a href="#" onClick="contactDetailsHint(); return false;">Kontaktinformationen</a>
					<script type="text/javascript">
						//<![CDATA[
							function contactDetailsHint() {
							alert("Nachdem das Inserat von uns geschaltet wurde, können andere Benutzer über diesen Link Ihre angegebenen Kontaktdaten (Mail und Telefon) abfragen."
								  +"\r\nNur echte Personen können ihre Kontaktdaten einsehen. Über ein Captcha werden ihre Daten von so gennanten Robots und Spammern geschützt.");
							}
						//]]>
					</script>
				</div>
			</div>
			<div>
			<div style="width: 95%; text-align: center; margin-top: 6px;">
				<form id="sm-preview-form" action="#" method="post" onsubmit="return false;">
					<input type="submit" id="sm-preview-submit-btn" value="OK - Inserat abschicken" /></td>
					<input type="reset" id="sm-preview-abort-btn" value="Ich will noch etwas ändern" /></td>
					<input type="hidden" name="sm_submit_id" value="'.$this->market_item->get_image_uuid().'" />
				</form>
			</div>';
	
		return utf8_encode($markup);
	}
	
}
class ContactDetailsMarketItemRenderer extends MarketItemRenderer {
	
	public function get_markup() {
		if(!isset($this->market_item))
			return '';
	
		$markup =
		'<table><tbody>
			<tr><td>Inserent: </td><td>'.$this->market_item->get_last_name_html_encoded().' '.$this->market_item->get_first_name_html_encoded().'</td></tr>
			<tr><td>E-Mail: </td><td>'.$this->market_item->get_mail_html_encoded().'</td></tr>
			<tr><td>Telefon: </td><td>'.$this->market_item->get_phone_html_encoded().'</td></tr>
		</tbody></table>';
	
		return utf8_encode($markup);
	}
}

class UserInputValidator {
	
	static function is_first_name_valid(&$first_name, $max_length = 0, $min_length = 3) {
		if(self::is_to_long($first_name, $max_length))
			return false;
		
		if(self::is_to_short($first_name, $min_length))
			return false;
		
		return true;
	}
	static function is_last_name_valid(&$last_name, $max_length = 0, $min_length = 3) {
		if(self::is_to_long($last_name, $max_length))
			return false;
		
		if(self::is_to_short($last_name, $min_length))
			return false;
				
		return true;
	}
	static function is_mail_valid(&$mail, $max_length = 0, $min_length = 1, $use_wp_internals = true) {
		if(self::is_to_long($mail, $max_length))
			return false;
		
		if(self::is_to_short($mail, $min_length))
			return false;
		
		if($use_wp_internals) {
			if(is_email($mail) === false)
				return false;
		}
		
		return true;
	}
	
	/**
	 * Accepted examples:
	 * +43 000 / 0000
	 * 43 000   / 0000
	 * 05522 / 810233
	 **/
	static function is_phone_valid(&$phone, $max_length = 0, $min_length = 6) {
		if(self::is_to_long($phone, $max_length))
			return false;
		
		if(self::is_to_short($phone, $min_length)) {
			return false;
		}
		
		if(!preg_match('/^\+?[\s\d\/]*$/', $phone))
			return false;
		
		return true;
	}
	
	/**
	 * Accepted are unique numeric or alphanumeric codes
	 * containing characters like "PO1 1AA"
	 **/
	static function is_zip_code_valid(&$zip_code, $max_length = 0, $min_length = 3) {
		if(self::is_to_long($zip_code, $max_length))
			return false;
		
		if(self::is_to_short($zip_code, $min_length))
			return false;
		
		if(!preg_match('/^[\sA-Za-z0-9]*$/', $zip_code))
			return false;
		
		return true;
	}
	static function is_city_valid(&$city, $max_length = 0, $min_length = 3) {
		if(self::is_to_long($city, $max_length))
			return false;
		
		if(self::is_to_short($city, $min_length))
			return false;
		
		return true;
	}
	static function is_country_valid(&$country, $max_length = 0, $min_length = 4) {
		if(self::is_to_long($country, $max_length))
			return false;
		
		if(self::is_to_short($country, $min_length))
			return false;
		
		return true;
	}
	static function is_text_valid(&$text, $max_length = 0, $min_length = 10) {
		
		if(self::is_to_long($text, $max_length))
			return false;
		
		if(self::is_to_short($text, $min_length))
			return false;
		
		return true;
	}

	private static function trim(&$the_string) {
		$the_string = trim($the_string);
	}
	
	private static function is_to_long(&$the_string, $max_length = 0) {
		self::trim($the_string);
		if(strlen($the_string) > $max_length) {
			return true;
		}
		return false;
	}
	
	private static function is_to_short(&$the_string, $min_length = 0) {
		self::trim($the_string);
		if(strlen($the_string) < $min_length) {
			return true;
		}
		return false;
	}
}
?>