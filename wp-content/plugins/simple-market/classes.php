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
	private $date_time;
	private $text;
	private $image_uuid;
	private $mail_approve;
	private $mail_approval_key;
	private $webmaster_approve;

		
	function __construct($assoc_array, $first_name = NULL, $last_name=NULL, $mail=NULL, $phone=NULL, $zip_code=NULL, 
							$city=NULL, $country=NULL, $text=NULL, $date_time=NULL, $image_uuid=NULL) {
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
					case 'date_time'			: 	$this->date_time = $value; break;
					case 'text'					: 	$this->text = $value; break;
					case 'image_uuid'			: 	$this->image_uuid = $value; break;
					case 'mail_approve'			: 	$this->mail_approve = $value; break;
					case 'mail_approval_key'	: 	$this->mail_approval_key = $value; break;
					case 'webmaster_approve'	: 	$this->webmaster_approve = $value; break;
					default						: 	throw new Exception("Column: $key not knwon!");
				}
			}
		} else {
			$this->first_name = $first_name;
			$this->last_name = $last_name;
			$this->mail = $mail;
			$this->phone = $phone;
			$this->zip_code = $zip_code;
			$this->city = $city;
			$this->country  = $country;
			$this->text = $text;
			$this->date_time = $date_time;
			$this->image_uuid = $image_uuid;
		}
	}
	function get_first_name() {
		return $this->first_name;
	}
	function get_first_name_html_encoded() {
		$encoded = htmlentities($this->first_name);
		return $encoded;
	}
	function get_last_name() {
		return $this->last_name;
	}
	function get_last_name_html_encoded() {
		$encoded = htmlentities($this->last_name);
		return $encoded;
	}
	function get_mail() {
		return $this->mail;
	}
	function get_mail_html_encoded() {
		$encoded = htmlentities($this->mail);
		return $encoded;
	}
	function get_phone() {
		return $this->phone;	
	}
	function get_phone_html_encoded() {
		$encoded = htmlentities($this->phone);
		return $phone;
	}
	function get_zip_code() {
		return $this->zip_code;
	}
	function get_zip_code_html_encoded() {
		$encoded = htmlentities($this->zip_code);
		return $encoded;
	}
	function get_city() {
		return $this->city;
	}
	function get_city_html_encoded() {
		$encoded = htmlentities($this->city);
		return $encoded;
	}
	function get_country() {
		return $this->country;
	}
	function get_country_html_encoded() {
		$encoded = htmlentities($this->country);
		return $encoded;
	}
	function get_text() {
		return $this->text;	
	}
	function get_text_html_encoded() {
		$encoded = htmlentities($this->text);
		$encoded = nl2br($encoded, false);
		return $encoded;
	}
	function get_date_time() {
		return $this->date_time;	
	}
	/**
	 * The tag images are marked with during submission process.
	 * Alias used in forms/posts is 'sm_submit_id'
	 */
	function get_image_uuid() {
		return $this->image_uuid;
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
	private $market_item = null;
	
	function __construct(&$market_item) {
		$this->market_item = $market_item;
	}
	
	function get_markup() {
		if(!isset($this->market_item))
			return '';
		
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
				<hr class="sm-h"/>
				<div>
					<a href="#" onClick="contactDetailsHint(); return false;">Kontaktinformationen</a>
					<script type="text/javascript">
						//<![CDATA[
							function contactDetailsHint() {
							alert("Nachdem das Inserat von uns geschalted wurde, können andere Benutzer über diesen Link Ihre angegebenen Kontaktdaten (Mail und Telefon) abfragen."
								  +"\r\nNur echte Personen können ihre Kontaktdaten einsehen. Über ein Captcha werden ihre Daten von so gennanten Robots geschützt.");
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
	
	private function get_formated_date_time() {
		$date_time = $this->market_item->get_date_time();
		$date_time_blog_time_zone = get_date_from_gmt($date_time);
		return $date_time_blog_time_zone;
	}
}
class UserInputValidator {

	static function is_first_name_valid($first_name) {
		return true;
	}
	static function is_last_name_valid($last_name) {
		return true;
	}
	static function is_mail_valid($mail) {
		//sanitize_email()
		if(is_email($mail) !== false)
			return true;
		return false;
	}
	static function is_phone_valid($phone) {
		return true;
	}
	static function is_zip_code_valid($zip_code) {
		return true;
	}
	static function is_city_valid($city) {
		return true;
	}
	static function is_country_valid($country) {
		return true;
	}
	static function is_text_valid($text) {
		return true;
	}
}

class UserInputPreprocessor {
	static function prepare_the_text(&$text) {
		$text = str_replace("\r\n", " <br /> ", $text);
		$text = str_replace("\n", " <br /> ", $text);
	}
}
?>