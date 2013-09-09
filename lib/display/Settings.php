<?php
/**
 * Plugin Settings Display class
 *
 * This class is used to fetch data from the MySQL database for the 
 * plugin settings page. This data is subsequently made available to
 * the rest of the class for use to build the form input elements
 * for the settings page.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI\BE
 * @package   includes.form.display
 * @since     3.0
*/

namespace FFI\BE;

require_once(dirname(dirname(__FILE__)) . "/exceptions/No_Data_Returned.php");

class Settings {
/**
 * Hold the results of the SQL query.
 *
 * @access private
 * @type   object<mixed>
*/
	
	private $data;

/**
 * CONSTRUCTOR
 *
 * Grab the data from the database, and make that data available to the
 * rest of the class.
 * 
 * @access public
 * @return void
 * @since  3.0
*/

	public function __construct() {
		global $wpdb;
		
		$this->data = $wpdb->get_results("SELECT * FROM `ffi_be_settings");
		
	//SQL returned 0 results
		if (!count($this->data)) {
			throw new No_Data_Returned("The plugin settings table is corrupt. Please run the installer again.");
		}
	}
	
/**
 * Output a prefilled form element containing the "Months Before Book
 * Expires" form element.
 * 
 * @access public
 * @return string   A form item prefilled with a value from the database
 * @since  3.0
*/
	
	public function getExpire() {
		$options = "";
	
		for ($i = 1; $i <= 25; ++$i) {
			$options .= "<option value=\"" . $i . "\"" . ($this->data[0]->BookExpireMonths == $i ? " selected" : "") . ">" . $i . "</option>
	";
		}
	
		return "<select id=\"expire\" name=\"expire\">
" . $options . "</select>";
	}
	
/**
 * Output a prefilled form element containing the "Automated Email From
 * Name" form element.
 * 
 * @access public
 * @return string   A form item prefilled with a value from the database
 * @since  3.0
*/

	public function getEmailName() {
		return "<input autocomplete=\"off\" class=\"regular-text\" id=\"email-name\" name=\"email-name\" type=\"text\" value=\"" . $this->data[0]->EmailName . "\">";
	}
	
/**
 * Output a prefilled form element containing the "Automated Email From
 * Address" form element.
 * 
 * @access public
 * @return string   A form item prefilled with a value from the database
 * @since  3.0
*/

	public function getEmailAddress() {
		return "<input autocomplete=\"off\" class=\"regular-text\" id=\"email-address\" name=\"email-address\" type=\"text\" value=\"" . $this->data[0]->EmailAddress . "\">";
	}
	
/**
 * Output a prefilled form element containing the "Plugin Time Zone" form
 * element.
 * 
 * @access public
 * @return string   A form item prefilled with a value from the database
 * @since  3.0
*/

	public function getTimeZone() {
		$options = "";
		$zones = array(
			"America/New_York"    => "Eastern Time",
			"America/Chicago"     => "Central Time",
			"America/Denver"      => "Mountain Time",
			"Amercia/Los_Angeles" => "Pacific Time",
			"America/Anchorage"   => "Alaska Time",
			"Pacific/Honolulu"    => "Hawaii Time"
		);
		
		foreach($zones as $key => $value) {
			$options .= "<option" . ($key == $this->data[0]->TimeZone ? " selected" : "") . " value=\"" . $key . "\">" . $value . "</option>
";
		}
		
		return "<select id=\"timezone\" name=\"timezone\">
" . $options . "</select>";
	}
}
?>