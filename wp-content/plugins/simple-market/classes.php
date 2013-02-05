<?php
class SimpleMarketItem {
	private $first_name;
	private $last_name;
	private $mail;
	private $phone;
	private $zip_code;
	private $city;
	private $country;
	private $text;
	private $date_time;
		
	function __construct($first_name, $last_name, $mail, $phone, $zip_code, $city, $country, $text, $date_time) {
		$this->first_name = $first_name;
		$this->last_name = $last_name;
		$this->mail = $mail;
		$this->phone = $phone;
		$this->zip_code = $zip_code;
		$this->city = $city;
		$this->country  = $country;
		$this->text = $text;
		$this->date_time = $date_time;
	}
	function get_first_name() {
		return $this->first_name;
	}
	function get_last_name() {
		return $this->last_name;
	}
	function get_mail() {
		return $this->mail;
	}
	function get_zip_code() {
		return $this->zip_code;
	}
	function get_city() {
		return $this->city;
	}
	function get_country() {
		return $this->country;
	}
	function get_text() {
		return $this->text;	
	}
	function get_date_time() {
		return $this->date_time;	
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
	
	private $submit_id;
	private $market_item_renderer;
	
	private $resp = array();
	
	//general setters
	public function set_market_item_renderer($market_item_renderer) {
		$this->market_item_renderer = $market_item_renderer;
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
			$this->resp['success'] = true;
			$this->add_preview();
			$this->set_submit_id();
			$this->resp['submit_id'] = $this->submit_id;
		}
		
		return json_encode($this->resp);		
	}
	
	private function add_errors_array_if_not_exist() {
		if(!isset($this->resp['errors']))
			$this->resp['errors'] = array();
	}
	
	private function add_preview() {
		$this->resp['preview'] = $this->market_item_renderer->get_markup();
	}
	private function set_submit_id() {
		if(isset($_SESSION['sm_submit_id']) === false) {
			$_SESSION['sm_submit_id'] = uniqid(); //by default 13 chararcters
		}
		$this->submit_id = $_SESSION['sm_submit_id'];
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
						<tr><td>'.$this->market_item->get_first_name() .' '
							 .$this->market_item->get_last_name().'</td></tr>
						<tr><td>'.$this->market_item->get_zip_code().', '
							 .$this->market_item->get_city().', '
							 .$this->market_item->get_country().'</td></tr>
						<tr><td>Anzeige vom: '.$this->get_formated_date_time().'</td></tr>					
					</tbody>
				</table>
				<div>'.$this->market_item->get_text().'</div>
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
			</div>';
		return utf8_encode($markup);
	}
	
	private function get_formated_date_time() {
		$date_time = $this->market_item->get_date_time();
		return $date_time->format('Y-m-d H:i:s');
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
		return true;
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