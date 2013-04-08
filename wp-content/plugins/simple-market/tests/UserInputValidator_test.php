<?php
class UserInputValidatorTest extends PHPUnit_Framework_TestCase {
	
	function __construct() {
		require_once '../classes.php';
	}
	
	//First Name ------------------------------------
	
	public function test_first_name_valid_1() {
		$name = "Eduard";
		$this->assertTrue(UserInputValidator::is_first_name_valid($name, 6), "Eduard is 6 characters long and should be valid.");
	}
	public function test_first_name_valid_2() {
		$name = "    Eduard		"; //whitespaces and tabulator			//to long
		$this->assertTrue(UserInputValidator::is_first_name_valid($name, 6), "Eduard is 6 characters long and should be valid, whitespaces are trimmed.");
		$this->assertEquals(strlen($name), 6);
	}
	public function test_first_name_invalid_1() {
		$name = " Eduardd   ";
		$this->assertFalse(UserInputValidator::is_first_name_valid($name, 6), "Eduardd is longer then 6 characters and not valid.");
		$this->assertEquals(strlen($name), 7);
	}	
	public function test_first_name_invalid_2() {
		$name = "	 ";	//to short
		$this->assertFalse(UserInputValidator::is_first_name_valid($name, 6), "Empty is invalid");
		$this->assertEquals(strlen($name), 0);
	}
	//Last Name -------------------------
	
	public function test_last_name_valid_1() {
		$name = "Bauer ";
		$this->assertTrue(UserInputValidator::is_last_name_valid($name, 5), "Should be valid.");
		$this->assertEquals(strlen($name), 5);
	}
	public function test_last_name_valid_2() {
		$name = "	Bauer"; //tabulator
		$this->assertTrue(UserInputValidator::is_last_name_valid($name, 5), "Should be valid.");
		$this->assertEquals(strlen($name), 5);
	}
	public function test_last_name_invalid_1() {
		$name = "	Bauer r "; //tabulator			//to long
		$this->assertFalse(UserInputValidator::is_last_name_valid($name, 5), "Should be invalid, to long");
		$this->assertEquals(strlen($name), 7);		
	}
	public function test_last_name_invalid_2() {
		$name = "	    "; //tabulator 		//to short
		$this->assertFalse(UserInputValidator::is_last_name_valid($name, 5), "Empty is invalid");
		$this->assertEquals(strlen($name), 0);
	}
	
	//Mail Address - Currently I am not familiar with WP bootstrap so we dont test the internals ----------------------
	
	public function test_mail_valid_1() {
		$mail = "valid length";
		$this->assertTrue(UserInputValidator::is_mail_valid($mail, 15, 1, false), "Should be valid.");
		$this->assertEquals(strlen($mail), 12);
	}
	
	public function test_mail_valid_2() {
		$mail = "	valid length too";
		$this->assertTrue(UserInputValidator::is_mail_valid($mail, 16, 1, false), "Should be valid.");
		$this->assertEquals(strlen($mail), 16);
	}	

	public function test_mail_invalid_1() {
		$mail = "invalid length"; //to long
		$this->assertFalse(UserInputValidator::is_mail_valid($mail, 10, 1, false), "Should be invalid, to long");
		$this->assertEquals(strlen($mail), 14); //real length
	}

	public function test_mail_invalid_2() {
		$mail = "			   ";	//to short
		$this->assertFalse(UserInputValidator::is_mail_valid($mail, 10, 1, false), "Empty is invalid");
		$this->assertEquals(strlen($mail), 0); //real length
	}
	
	//Test phone
	
	public function test_phone_valid_1() {
		$phone = " +43 5522 / 122 332 322";
		$this->assertTrue(UserInputValidator::is_phone_valid($phone, 22), "Should be valid.");
		$this->assertEquals("+43 5522 / 122 332 322", $phone);		
	}
	
	public function test_phone_valid_2() {
		$phone = " 43 5522 / 122 332 322";
		$this->assertTrue(UserInputValidator::is_phone_valid($phone, 22), "Should be valid.");
		$this->assertEquals("43 5522 / 122 332 322", $phone);
	}

	public function test_phone_valid_3() {
		$phone = " 43 5522    122 332 322";
		$this->assertTrue(UserInputValidator::is_phone_valid($phone, 22), "Should be valid.");
		$this->assertEquals("43 5522    122 332 322", $phone);
	}
	
	public function test_phone_invalid_1() {
		$phone = " +43 5522 a 122 332 322";
		$this->assertFalse(UserInputValidator::is_phone_valid($phone, 22), "Should be invalid, contains invalid character.");
	}

	public function test_phone_invalid_2() {
		$phone = " +43 5522      122 332 322 2"; //to long
		$this->assertFalse(UserInputValidator::is_phone_valid($phone, 22), "Should be invalid, to long.");
	}

	public function test_phone_invalid_3() {
		$phone = " ++43 5522 122 332 322 2";
		$this->assertFalse(UserInputValidator::is_phone_valid($phone, 22), "Should be invalid, contains because of ++.");
	}
	
	public function test_phone_invalid_4() {
		$phone = " +3222"; //to short
		$this->assertFalse(UserInputValidator::is_phone_valid($phone, 22), "Phone number should be to short.");
	}
	
	//Test Zip Code ------------------------------------------------------------------------------

	public function test_zip_code_valid_1() {
		$zip = "PO1 1AA";
		$this->assertTrue(UserInputValidator::is_zip_code_valid($zip, 8),  "Should be valid.");
	}
	
	public function test_zip_code_invalid_1() {
		$zip = "		  "; //to short
		$this->assertFalse(UserInputValidator::is_zip_code_valid($zip, 8),  "Empty is invalid.");
		$this->assertEquals("", $zip);
	}
	public function test_zip_code_invalid_2() {
		$zip = "PO1 1AA /";
		$this->assertFalse(UserInputValidator::is_zip_code_valid($zip, 8),  "Should be invalid.");
	}

	public function test_zip_code_invalid_3() {
		$zip = "PO1 1AA AAAAAAAAAAAAAAA"; //to long
		$this->assertFalse(UserInputValidator::is_zip_code_valid($zip, 8),  "Should be invalid.");
	}
	
	//Test City -------------------------

	public function test_city_valid_1() {
		$city = "  Feldkirch 	";
		$this->assertTrue(UserInputValidator::is_city_valid($city, 9), "Should be valid.");
		$this->assertEquals("Feldkirch", $city);
	}
	public function test_city_invalid_1() {
		$city = " 			 "; //to short
		$this->assertFalse(UserInputValidator::is_city_valid($city, 9), "Should be invalid.");
		$this->assertEquals("", $city);
	}
	public function test_city_invalid_2() {
		$city = "Feldkirchhhhhhhhhhhhhhhh";  //to long
		$this->assertFalse(UserInputValidator::is_city_valid($city, 10), "Should be invalid.");
		$this->assertEquals("Feldkirchhhhhhhhhhhhhhhh", $city);
	}

	//Test Country -------------------------------
	public function test_country_valid_1() {
		$country = "  Österreich     	";
		$this->assertTrue(UserInputValidator::is_city_valid($country, 10), "Should be valid.");
		$this->assertEquals("Österreich", $country);
	}
	public function test_country_invalid_1() {
		$country = "  Österreich     	";	//to long
		$this->assertFalse(UserInputValidator::is_city_valid($country, 8), "Should be invalid, to long");
		$this->assertEquals("Österreich", $country);
	}
	public function test_country_invalid_2() {
		$country = "  Ö ";	//to short
		$this->assertFalse(UserInputValidator::is_city_valid($country, 10, 2), "Should be invalid, to short");
		$this->assertEquals("Ö", $country);
	}
	
	//Test Text ------------------------------------------
	public function test_text_valid_1() {
		$text = "   Ich verkaufe \r\n bla und kecks è		";
		$this->assertTrue(UserInputValidator::is_text_valid($text, 32));
	}
	public function test_text_invalid_1() {
		$text = "   		";	//to short
		$this->assertFalse(UserInputValidator::is_text_valid($text, 32));
	}
	public function test_text_valid_2() {
		$text = "   Ich verkaufe \r\n bla und kecks è		"; //to long
		$this->assertFalse(UserInputValidator::is_text_valid($text, 30));
	}
	
	
}
?>