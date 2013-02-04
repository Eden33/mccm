<?php
class SimpleMarketItem {
	public $first_name;
	public $last_name;
	public $zip_code;
	public $city;
	public $country;
	public $text;
	public $date_time;
		
	function __construct($first_name, $last_name, $zip_code, $city, $country, $text, $date_time) {
		$this->first_name = $first_name;
		$this->last_name = $last_name;
		$this->zip_code = $zip_code;
		$this->city = $city;
		$this->country  = $country;
		$this->text = $text;
		$this->date_time = $date_time;
	}
}

class SimpleMarketFormResponse {
	//errors
	public $captcha_error = false;
	
	
	public $preview_mode = false;
	public $submit_id;
	public $market_item;
	
	private $resp = array();
	
	function get_json_response() {
		
		header("Content-Type: application/json");
		
		if($this->captcha_error) {
			$this->add_errors_array_if_not_exist();
			array_push($this->resp['errors'], 'captcha_error');
		}
		
		if(isset($this->resp['errors'])) {
			$this->resp['success'] = false;
		} else {
			$this->resp['success'] = true;
			if($this->preview_mode === true) {
				$this->add_preview();
				$this->resp['submit_id'] = $this->submit_id;
			}
		}
		
		return json_encode($this->resp);		
	}
	
	private function add_errors_array_if_not_exist() {
		if(!isset($this->resp['errors']))
			$this->resp['errors'] = array();
	}
	
	private function add_preview() {
		$this->resp['preview'] = '<div class="sm-top-div">Yeah baby<br/>Yeehhh!!</div>';
	}
}
?>